<?php
/**
 * CBOX's Plugin Upgrade and Install API
 *
 * @package Commons_In_A_Box
 * @subpackage Plugins
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// require the WP_Upgrader class so we can extend it!
require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );

// add the Plugin Dependencies plugin; just in case this file is called outside the admin area
if ( ! class_exists( 'Plugin_Dependencies' ) )
	require_once( CBOX_LIB_DIR . 'wp-plugin-dependencies/plugin-dependencies.php' );

/**
 * CBOX's custom plugin upgrader.
 *
 * Extends the {@link Plugin_Upgrader} class to allow for our custom required spec.
 *
 * @since 0.2
 *
 * @package Commons_In_A_Box
 * @subpackage Plugins
 */
class CBox_Plugin_Upgrader extends Plugin_Upgrader {

	/**
	 * Overrides the parent {@link Plugin_Upgrader::bulk_upgrader()} method.
	 *
	 * Uses CBOX's own registered upgrade links.
	 *
	 * @param str $plugins Array of plugin names
	 */
	function bulk_upgrade( $plugins, $args = array() ) {

		$this->init();
		$this->bulk = true;
		$this->upgrade_strings();

		// download URLs for each plugin should be registered in either the following:
		$dependency = CBox_Plugins::get_plugins( 'dependency' );
		$current    = CBox_Plugins::get_plugins();

		add_filter( 'upgrader_source_selection',  'cbox_rename_github_folder',         1,  3 );
		add_filter( 'upgrader_clear_destination', array( $this, 'delete_old_plugin' ), 10, 4 );
		add_filter( 'http_request_args',          'cbox_disable_ssl_verification',     10, 2 );

		$this->skin->header();

		// Connect to the Filesystem first.
		$res = $this->fs_connect( array(WP_CONTENT_DIR, WP_PLUGIN_DIR) );
		if ( ! $res ) {
			$this->skin->footer();
			return false;
		}

		$this->skin->bulk_header();

		// Only start maintenance mode if running in Multisite OR the plugin is in use
		$maintenance = is_multisite(); // @TODO: This should only kick in for individual sites if at all possible.
		foreach ( $plugins as $plugin ) {
			$plugin_loader = Plugin_Dependencies::get_pluginloader_by_name( $plugin );
			$maintenance = $maintenance || (is_plugin_active($plugin_loader) ); // Only activate Maintenance mode if a plugin is active
		}

		if ( $maintenance )
			$this->maintenance_mode(true);

		$results = array();

		$this->update_count = count($plugins);
		$this->update_current = 0;
		foreach ( $plugins as $plugin ) {
			$this->update_current++;
			$this->skin->plugin_info['Title'] = $plugin;

			// set the download URL
			if ( ! empty( $dependency[$plugin]['download_url'] ) )
				$download_url = $dependency[$plugin]['download_url'];
			elseif ( ! empty( $current[$plugin]['download_url'] ) )
				$download_url = $current[$plugin]['download_url'];
			else
				$download_url = false;

			$this->skin->options['url'] = $download_url;

			// see if plugin is active
			$plugin_loader = Plugin_Dependencies::get_pluginloader_by_name( $plugin );
			$this->skin->plugin_active = is_plugin_active( $plugin_loader );

			$result = $this->run( array(
				'package'           => $download_url,
				'destination'       => WP_PLUGIN_DIR,
				'clear_destination' => true,
				'clear_working'     => true,
				'is_multi'          => true,
				'hook_extra'        => array( 'plugin' => $plugin_loader )
			) );

			$results[$plugin_loader] = $this->result;

			// Prevent credentials auth screen from displaying multiple times
			if ( false === $result )
				break;
		} //end foreach $plugins

		$this->maintenance_mode(false);

		$this->skin->bulk_footer();

		$this->skin->footer();

		// Cleanup our hooks, in case something else does a upgrade on this connection.
		remove_filter( 'upgrader_source_selection',  'cbox_rename_github_folder',     1,  3 );
		remove_filter( 'upgrader_clear_destination', array( $this, 'delete_old_plugin' ) );
		remove_filter( 'http_request_args',          'cbox_disable_ssl_verification', 10, 2 );

		// Force refresh of plugin update information
		delete_site_transient('update_plugins');
		wp_cache_delete( 'plugins', 'plugins' );

		return $results;
	}

	/**
	 * Bulk install plugins.
	 *
	 * Download links for plugins are listed in {@link CBox_Plugins::get_plugins()}.
	 *
	 * @param str $plugins Array of plugin names
	 */
	function bulk_install( $plugins ) {

		$this->init();
		$this->bulk = true;
		$this->install_strings();

		// download URLs for each plugin should be registered in either the following:
		$dependency = CBox_Plugins::get_plugins( 'dependency' );
		$required = CBox_Plugins::get_plugins();

		add_filter( 'upgrader_source_selection', 'cbox_rename_github_folder',     1,  3 );
		add_filter( 'upgrader_source_selection', array( $this, 'check_package' ) );
		add_filter( 'http_request_args',         'cbox_disable_ssl_verification', 10, 2 );

		$this->skin->header();

		// Connect to the Filesystem first.
		$res = $this->fs_connect( array(WP_CONTENT_DIR, WP_PLUGIN_DIR) );
		if ( ! $res ) {
			$this->skin->footer();
			return false;
		}

		$this->skin->bulk_header();

		$results = array();

		$this->update_count = count( $plugins);
		$this->update_current = 0;
		foreach ( $plugins as $plugin ) {
			$this->update_current++;
			$this->skin->plugin_info['Title'] = $plugin;

			// set the download URL
			if ( ! empty( $dependency[$plugin]['download_url'] ) )
				$download_url = $dependency[$plugin]['download_url'];
			elseif ( ! empty( $required[$plugin]['download_url'] ) )
				$download_url = $required[$plugin]['download_url'];
			else
				$download_url = false;

			$this->skin->options['url'] = $download_url;

			$result = $this->run( array(
				'package'           => $download_url,
				'destination'       => WP_PLUGIN_DIR,
				'clear_destination' => false, //Do not overwrite files.
				'clear_working'     => true,
				'is_multi'          => true,
				'hook_extra'        => array()
			) );

			//$results[$plugin_loader] = $this->result;

			// Prevent credentials auth screen from displaying multiple times
			if ( false === $result )
				break;
		} //end foreach $plugins

		$this->skin->bulk_footer();

		$this->skin->footer();

		// Cleanup our hooks, in case something else does a upgrade on this connection.
		remove_filter( 'upgrader_source_selection', 'cbox_rename_github_folder',     1,  3 );
		remove_filter( 'upgrader_source_selection', array( $this, 'check_package' ) );
		remove_filter( 'http_request_args',         'cbox_disable_ssl_verification', 10, 2 );

		// Force refresh of plugin update information
		delete_site_transient('update_plugins');
		wp_cache_delete( 'plugins', 'plugins' );

		return $results;
	}

	/**
	 * Bulk activates plugins.
	 *
	 * @param str $plugins Array of plugin names
	 */
	public static function bulk_activate( $plugins ) {

		if ( empty( $plugins ) )
			return false;

		// Only activate plugins which are not already active.
		$check = is_multisite() ? 'is_plugin_active_for_network' : 'is_plugin_active';

		foreach ( $plugins as $i => $plugin ) {
			$plugin_loader = Plugin_Dependencies::get_pluginloader_by_name( $plugin );

			// if already active, skip!
			if ( ! empty( $plugin_loader ) && $check( $plugin ) ) {
				unset( $plugins[ $i ] );
				continue;
			}

			// activate the plugin
			activate_plugin( $plugin_loader, '', is_network_admin() );
		}

		if ( ! is_network_admin() ) {
			$recent = (array) get_option('recently_activated' );

			foreach ( $plugins as $plugin )
				unset( $recent[ $plugin ] );

			update_option( 'recently_activated', $recent );
		}

		return true;
	}

}

/**
 * The UI for CBOX's updater.
 *
 * Extends the {@link Bulk_Plugin_Upgrader_Skin} class.
 *
 * @package Commons_In_A_Box
 * @subpackage Plugins
 */
class CBox_Bulk_Plugin_Upgrader_Skin extends Bulk_Plugin_Upgrader_Skin {


	/**
	 * Setup our custom strings.
	 *
	 * Needed when bulk-installing to prevent the default bulk-upgrader strings to be used.
	 */
	function add_strings() {
		parent::add_strings();

		// if first step is bulk-upgrading, then stop string overrides!
		if ( ! empty( $this->options['step_one'] ) && $this->options['step_one'] == 'upgrade' )
			return;

		// if we're bulk-installing, switch up the strings!
		if ( ! empty( $this->options['install_strings'] ) ) {
			$this->upgrader->strings['skin_before_update_header'] = __( 'Installing Plugin %1$s (%2$d/%3$d)', 'cbox' );

			$this->upgrader->strings['skin_upgrade_start']        = __( 'The installation process is starting. This process may take a while on some hosts, so please be patient.', 'cbox' );
			$this->upgrader->strings['skin_update_failed_error']  = __( 'An error occurred while installing %1$s: <strong>%2$s</strong>.', 'cbox' );
			$this->upgrader->strings['skin_update_failed']        = __( 'The installation of %1$s failed.', 'cbox' );
			$this->upgrader->strings['skin_update_successful']    = __( '%1$s installed successfully.', 'cbox' ) . ' <a onclick="%2$s" href="#" class="hide-if-no-js"><span>' . __( 'Show Details', 'cbox' ) . '</span><span class="hidden">' . __( 'Hide Details', 'cbox' ) . '</span>.</a>';
			$this->upgrader->strings['skin_upgrade_end']          = __( 'Plugins finished installing.', 'cbox' );
		}
	}

	/**
	 * After the bulk-upgrader has completed, do some extra stuff.
	 *
	 * We try to upgrade plugins first.  Next, we install plugins that are not available.
	 * Lastly, we activate any plugins needed.
	 */
	function bulk_footer() {
		// install plugins after the upgrader is done if available
		if ( ! empty( $this->options['install_plugins'] ) ) {
			if (! empty( $this->options['activate_plugins'] ) ) {
				$skin_args['activate_plugins'] = $this->options['activate_plugins'];
			}

			if (! empty( $this->options['redirect_link'] ) ) {
				$skin_args['redirect_link'] = $this->options['redirect_link'];
			}

			if (! empty( $this->options['redirect_text'] ) ) {
				$skin_args['redirect_text'] = $this->options['redirect_text'];
			}

			$skin_args['install_strings'] = true;

			echo '<p>' . __( 'Plugins updated.', 'cbox' ) . '</p>';

			usleep(500000);

			echo '<h3>' . __( 'Now Installing Plugins...', 'cbox' ) . '</h3>';

			usleep(500000);

 			$installer = new CBox_Plugin_Upgrader(
 				new CBox_Bulk_Plugin_Upgrader_Skin( $skin_args )
 			);

 			$installer->bulk_install( $this->options['install_plugins'] );
		}

		// activate plugins after the upgrader / installer is done if available
		elseif ( ! empty( $this->options['activate_plugins'] ) ) {
			usleep(500000);

			echo '<h3>' . __( 'Now Activating Plugins...', 'cbox' ) . '</h3>';

			usleep(500000);

 			$activate = CBox_Plugin_Upgrader::bulk_activate( $this->options['activate_plugins'] );
 		?>

			<p><?php _e( 'Plugins activated.', 'cbox' ); ?></p>

			<p><?php self::after_updater(); ?></p>
 		<?php
		}

		// process is completed!
		// show link to CBOX dashboard
		else {
			usleep(500000);

			self::after_updater();
		}
	}

	/**
	 * Overriding this so we can change the ID of the DIV so toggling works... sigh...
	 */
	function before( $title = '' ) {
		$title = $this->plugin_info['Title'];

		if ( ! empty( $this->options['install_plugins'] ) ) {
			$current = $this->upgrader->update_current;
			$this->upgrader->update_current = 'install-' . $current;
		}

		$this->in_loop = true;
		printf( '<h4>' . $this->upgrader->strings['skin_before_update_header'] . ' <img alt="" src="' . admin_url( 'images/wpspin_light.gif' ) . '" class="hidden waiting-' . $this->upgrader->update_current . '" style="vertical-align:middle;" /></h4>',  $title, $this->upgrader->update_current, $this->upgrader->update_count);
		echo '<script type="text/javascript">jQuery(\'.waiting-' . esc_js($this->upgrader->update_current) . '\').show();</script>';
		echo '<div class="update-messages hide-if-js" id="progress-' . esc_attr($this->upgrader->update_current) . '"><p>';

		if ( ! empty( $this->options['install_plugins'] ) ) {
			$this->upgrader->update_current = $current;
		}

		$this->flush_output();
	}

	/**
	 * Overriding this so we can change the ID of the DIV so toggling works... sigh...
	 */
	function after( $title = '' ) {
		$title = $this->plugin_info['Title'];

		if ( ! empty( $this->options['install_plugins'] ) ) {
			$current = $this->upgrader->update_current;
			$this->upgrader->update_current = 'install-' . $current;
		}

		echo '</p></div>';
		if ( $this->error || ! $this->result ) {
			if ( $this->error )
				echo '<div class="error"><p>' . sprintf($this->upgrader->strings['skin_update_failed_error'], $title, $this->error) . '</p></div>';
			else
				echo '<div class="error"><p>' . sprintf($this->upgrader->strings['skin_update_failed'], $title) . '</p></div>';

			echo '<script type="text/javascript">jQuery(\'#progress-' . esc_js($this->upgrader->update_current) . '\').show();</script>';
		}
		if ( !empty($this->result) && !is_wp_error($this->result) ) {
			echo '<div class="updated"><p>' . sprintf($this->upgrader->strings['skin_update_successful'], $title, 'jQuery(\'#progress-' . esc_js($this->upgrader->update_current) . '\').toggle();jQuery(\'span\', this).toggle(); return false;') . '</p></div>';
			echo '<script type="text/javascript">jQuery(\'.waiting-' . esc_js($this->upgrader->update_current) . '\').hide();</script>';
		}

		if ( ! empty( $this->options['install_plugins'] ) ) {
			$this->upgrader->update_current = $current;
		}

		$this->reset();
		$this->flush_output();
	}

	/**
	 * Do some stuff after the updater has finished running.
	 *
	 * @param string $redirect_link Redirect link with anchor text. This is used if CBox_Bulk_Plugin_Upgrader_Skin doesn't have one.
	 * @since 0.3
	 */
	public static function after_updater( $args = array() ) {
		// if a redirect link is passed, use it.
		if ( ! empty( $args ) ) {
 			$redirect_link = ! empty( $args['redirect_link'] ) ? $args['redirect_link'] : false;
 			$redirect_text = ! empty( $args['redirect_text'] ) ? $args['redirect_text'] : false;

		// if a redirect link is passed during the class constructor, use it
		} elseif ( ! self::_is_static() && ! empty( $this->options['redirect_link'] ) && ! empty( $this->options['redirect_text'] ) ) {
			$redirect_link = $this->options['redirect_link'];
			$redirect_text = $this->options['redirect_text'];

		// default fallback
		} else {
			$redirect_link = self_admin_url( 'admin.php?page=cbox-plugins' );
			$redirect_text = __( 'Return to the CBOX Plugins page', 'cbox' );
		}

		echo '<br /><a class="button-primary" href="' . esc_url( $redirect_link ) . '">' . esc_attr( $redirect_text ) . '</a>';

		// extra hook to do stuff after the updater has run
		do_action( 'cbox_after_updater' );
	}

	/**
	 * Detect whether a class is called statically.
	 *
	 * Lighter than using Reflection to determine this. 
	 *
	 * @since 1.0.6
	 *
	 * @return bool
	 */
	protected static function _is_static() {
		$backtrace = debug_backtrace();

		// The 0th call is this method, so we need to check the next
		// call down the stack.
		return $backtrace[1]['type'] == '::';
	}
}

/**
 * CBOX Updater.
 *
 * Wraps the bulk-upgrading, bulk-installing and bulk-activating process into one!
 *
 * @since 0.2
 *
 * @package Commons_In_A_Box
 * @subpackage Plugins
 */
class CBox_Updater {

	private static $is_upgrade  = false;
	private static $is_install  = false;
	private static $is_activate = false;

	/**
	 * Constructor.
	 *
	 * @param array $plugins Associative array of plugin names
	 */
	function __construct( $plugins = false, $settings = array() ) {
		if ( ! empty( $plugins['upgrade'] ) )
			self::$is_upgrade  = true;

		if( ! empty( $plugins['install'] ) )
			self::$is_install  = true;

		if( ! empty( $plugins['activate'] ) )
			self::$is_activate = true;

		if ( ! empty( $settings['redirect_link'] ) )
			$skin_args['redirect_link'] = $settings['redirect_link'];

		if ( ! empty( $settings['redirect_text'] ) )
			$skin_args['redirect_text'] = $settings['redirect_text'];

		// if no plugins passed, stop the updater now!
		if ( ! $plugins ) {
			_doing_it_wrong( __METHOD__, 'first argument, (array) $plugins, cannot be empty.' );
			return false;
		}

		// dependency-time!
		// flatten the associative array to make dependency checks easier
		$plugin_list = call_user_func_array( 'array_merge', $plugins );

		// get requirements
		$requirements = Plugin_Dependencies::get_requirements();

		// loop through each submitted plugin and check for any dependencies
		foreach( $plugin_list as $plugin ) {
			// we have dependents!
			if ( ! empty( $requirements[$plugin] ) ) {

				// now loop through each dependent plugin state and add that plugin to our list
				// before we start the whole process!
				foreach( $requirements[$plugin] as $dep_state => $dep_plugins ) {
					switch( $dep_state ) {
						case 'inactive' :
							if ( ! self::$is_activate ) {
								$plugins['activate'] = array();
								self::$is_activate = true;
							}

							// push dependent plugins to the beginning of the activation plugins list
							$plugins['activate'] = array_merge( $dep_plugins, $plugins['activate'] );

							break;

						case 'not-installed' :
							if ( ! self::$is_install ) {
								$plugins['install'] = array();
								self::$is_install = true;
							}

							// push dependent plugins to the beginning of the installation plugins list
							$plugins['install'] = array_merge( $dep_plugins, $plugins['install'] );

							break;

						case 'incompatible' :
							if ( ! self::$is_upgrade ) {
								$plugins['upgrade'] = array();
								self::$is_upgrade = true;
							}

							$plugin_names = wp_list_pluck( $dep_plugins, 'name' );

							// push dependent plugins to the beginning of the upgrade plugins list
							$plugins['upgrade'] = array_merge( $plugin_names, $plugins['upgrade'] );

							break;
					}
				}
			}
		}

		// setup our plugin defaults
		CBox_Plugin_Defaults::init();

		// this tells WP_Upgrader to activate the plugin after any upgrade or successful install
		add_filter( 'upgrader_post_install', array( &$this, 'activate_post_install' ), 10, 3 );

 		// start the whole damn thing!
 		// We always try to upgrade plugins first.  Next, we install plugins that are not available.
 		// Lastly, we activate any plugins needed.

 		// let's see if upgrades are available; if so, start with that
 		if ( self::$is_upgrade ) {
			// if installs are available as well, this tells CBox_Plugin_Upgrader
			// to install plugins after the upgrader is done
			if ( self::$is_install ) {
				$skin_args['install_plugins'] = $plugins['install'];
				$skin_args['install_strings'] = true;
			}

			// if activations are available as well, this tells CBox_Plugin_Upgrader
			// to activate plugins after the upgrader is done
			if ( self::$is_activate ) {
				$skin_args['activate_plugins'] = $plugins['activate'];
			}

			// tell the installer that this is the first step
			$skin_args['step_one'] = 'upgrade';

			echo '<h3>' . __( 'Upgrading Existing Plugins...', 'cbox' ) . '</h3>';

 			// instantiate the upgrader
 			// we add our custom arguments to the skin
 			$installer = new CBox_Plugin_Upgrader(
 				new CBox_Bulk_Plugin_Upgrader_Skin( $skin_args )
 			);

 			// now start the upgrade!
 			$installer->bulk_upgrade( $plugins['upgrade'] );
 		}

		// if no upgrades are available, move on to installs
 		elseif( self::$is_install ) {
			// if activations are available as well, this tells CBox_Plugin_Upgrader
			// to activate plugins after the upgrader is done
			if ( self::$is_activate ) {
				$skin_args['activate_plugins'] = $plugins['activate'];
			}

			$skin_args['install_strings'] = true;

			echo '<h3>' . __( 'Installing Plugins...', 'cbox' ) . '</h3>';

 			// instantiate the upgrader
 			// we add our custom arguments to the skin
 			$installer = new CBox_Plugin_Upgrader(
 				new CBox_Bulk_Plugin_Upgrader_Skin( $skin_args )
 			);

 			// now start the install!
 			$installer->bulk_install( $plugins['install'] );
 		}

		// if no upgrades or installs are available, move on to activations
 		elseif( self::$is_activate ) {
			echo '<h3>' . __( 'Activating Plugins...', 'cbox' ) . '</h3>';

 			$activate = CBox_Plugin_Upgrader::bulk_activate( $plugins['activate'] );
 		?>

			<p><?php _e( 'Plugins activated.', 'cbox' ); ?></p>

			<p><?php CBox_Bulk_Plugin_Upgrader_Skin::after_updater( $settings ); ?></p>
 		<?php
 		}

	}

	/**
	 * Activates a plugin after upgrading or installing a plugin
	 */
	public function activate_post_install( $bool, $hook_extra, $result ) {

		// activates a plugin post-upgrade
		if ( ! empty( $hook_extra['plugin'] ) ) {
			activate_plugin( $hook_extra['plugin'], '', is_network_admin() );
		}
		// activates a plugin post-install
		elseif ( ! empty( $result['destination_name'] ) ) {
			// when a plugin is installed, we need to find the plugin loader file
			$plugin_loader = array_keys( get_plugins( '/' . $result['destination_name'] ) );
			$plugin_loader = $plugin_loader[0];

			// this makes sure that validate_plugin() works in activate_plugin()
			wp_cache_flush();

			// now activate the plugin
			activate_plugin( $result['destination_name'] . '/' . $plugin_loader, '', is_network_admin() );
		}

		return $bool;
	}

}

/**
 * Set some defaults for certain CBOX plugins after their activation.
 *
 * Not currently used at the moment.
 *
 * @since 1.0-beta2
 *
 * @package Commons_In_A_Box
 * @subpackage Plugins
 */
class CBox_Plugin_Defaults {
	/**
	 * Alternate method to initialize the class.
	 */
	public static function init() {
		new self();
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		// setup our hooks
		$this->setup_hooks();
	}

	/**
	 * Setup our hooks.
	 */
	public function setup_hooks() {
		add_action( 'activated_plugin', array( $this, 'plugin_defaults' ), 999, 2 );
	}

	/**
	 * At the moment, we hardcode any defaults for our CBOX plugins here and fire
	 * them after that plugin is activated.
	 *
	 * We are not doing anything special here like looking for plugins by name b/c
	 * that would require parsing that plugin's header metadata and this might not
	 * be efficient when activating plugins in a loop.
	 *
	 * Instead, we just take the plugin loader file as-is and do our checks there.
	 *
	 * @todo Might be nice to separate each plugin's defaults into its own PHP file.
	 *       We'll cross that bridge once we have a ton of defaults!
	 */
	public function plugin_defaults( $plugin, $network_wide ) {
		switch ( $plugin ) {
			// BuddyPress
			case 'buddypress/bp-loader.php' :
				// don't let BP redirect to its about page after activating
				delete_transient( '_bp_activation_redirect' );

				break;

			// bbPress
			case 'bbpress/bbpress.php' :
				// don't let bbPress redirect to its about page after activating
				delete_transient( '_bbp_activation_redirect' );

				/** If BP bundled forums exists, stop now! *********************/

				// do check for multisite
				if ( is_multisite() ) {
					$bp_root_blog = defined( 'BP_ROOT_BLOG' ) ? constant( 'BP_ROOT_BLOG' ) : 1;

					$option = get_blog_option( $bp_root_blog, 'bb-config-location' );

				// single WP
				} else {
					$option = get_option( 'bb-config-location' );
				}

				// stop if our bb-config-location was found
				if ( false !== $option )
					return;

				/** See if a bbPress forum named 'Group Forums' exists *********/

				// add a filter to WP_Query so we can search by post title
				add_filter( 'posts_where', array( $this, 'search_by_post_title' ), 10, 2 );

				// do our search
				$search = new WP_Query( array(
					'post_type'       => bbp_get_forum_post_type(),
					'cbox_post_title' => __( 'Group Forums', 'bbpress' )
				) );

				/** No match, create our forum! ********************************/

				if ( ! $search->have_posts() ) {
					// create a forum for BP groups
					$forum_id = bbp_insert_forum( array(
						'post_title'   => __( 'Group Forums', 'bbpress' ),
						'post_content' => __( 'All forums created in groups can be found here.', 'cbox' )
					) );

					// update the bbP marker for group forums
					if ( is_multisite() ) {
						update_blog_option( $bp_root_blog, '_bbp_group_forums_root_id', $forum_id );
					} else {
						update_option( '_bbp_group_forums_root_id', $forum_id );
					}
				}

				break;
		}
	}

	/** HELPERS *******************************************************/

	/**
	 * Filter WP_Query to allow searching by post title.
	 *
	 * @since 1.0-beta4
	 */
	public function search_by_post_title( $where, $wp_query ) {
		global $wpdb;

		if ( $post_title = $wp_query->get( 'cbox_post_title' ) ) {
			$where .= " AND {$wpdb->posts}.post_title = '" . esc_sql( $post_title ) . "'";
		}

		return $where;
	}
}
