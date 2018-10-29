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
		$parsed_args = wp_parse_args( $args, array(
			'clear_update_cache' => true,
		) );

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

			/*
			 * Special case for root-blog plugins.
			 *
			 * BuddyPress supports a different root blog ID, so if BuddyPress is activated
			 * we need to switch to that blog to get the correct active plugins list.
			 */
			if ( false === $current[ $plugin ]['network'] && 1 !== cbox_get_main_site_id() ) {
				switch_to_blog( cbox_get_main_site_id() );
			}

			$is_active = is_plugin_active( $plugin_loader );

			if ( false === $current[ $plugin ]['network'] && 1 !== cbox_get_main_site_id() ) {
				restore_current_blog();
			}

			// Only activate Maintenance mode if a plugin is active.
			$maintenance = $maintenance || $is_active;
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

			/*
			 * Special case for root-blog plugins.
			 *
			 * BuddyPress supports a different root blog ID, so if BuddyPress is activated
			 * we need to switch to that blog to get the correct active plugins list.
			 */
			if ( false === $current[ $plugin ]['network'] && 1 !== cbox_get_main_site_id() ) {
				switch_to_blog( cbox_get_main_site_id() );
			}

			$this->skin->plugin_active = is_plugin_active( $plugin_loader );

			if ( false === $current[ $plugin ]['network'] && 1 !== cbox_get_main_site_id() ) {
				restore_current_blog();
			}

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

		// Force refresh of plugin update information.
		wp_clean_plugins_cache( $parsed_args['clear_update_cache'] );

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

		// Force refresh of plugin update information.
		wp_clean_plugins_cache();

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

		$current    = CBox_Plugins::get_plugins();
		$dependency = CBox_Plugins::get_plugins( 'dependency' );

		foreach ( $plugins as $i => $plugin ) {
			// Do not activate if plugin is install-only.
			if ( true === CBox_Plugins::is_plugin_type( $plugin, 'install-only' ) ) {
				continue;
			}

			$plugin_loader = Plugin_Dependencies::get_pluginloader_by_name( $plugin );

			if ( ! is_multisite() ) {
				$network_activate = false;
			} elseif ( isset( $current[ $plugin ] ) ) {
				$network_activate = $current[ $plugin ]['network'];
			} else {
				$network_activate = $dependency[ $plugin ]['network'];
			}

			/*
			 * Special case for root-blog plugins.
			 *
			 * BuddyPress supports a different root blog ID, so if BuddyPress is activated
			 * we need to switch to that blog to get the correct active plugins list.
			 */
			if ( false === $network_activate && 1 !== cbox_get_main_site_id() ) {
				switch_to_blog( cbox_get_main_site_id() );
			}

			$is_active = is_plugin_active( $plugin_loader );

			// if already active, skip!
			if ( ! empty( $plugin_loader ) && $is_active ) {
				unset( $plugins[ $i ] );

				// Remember to restore blog, if we're skipping!
				if ( false === $network_activate && 1 !== cbox_get_main_site_id() ) {
					restore_current_blog();
				}

				continue;
			}

			// activate the plugin
			activate_plugin( $plugin_loader, '', $network_activate );

			if ( false === $network_activate && 1 !== cbox_get_main_site_id() ) {
				restore_current_blog();
			}
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

			$this->upgrader->strings['skin_upgrade_start']        = __( 'The installation process is starting. This process may take a while, so please be patient.', 'cbox' );
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

			<p><?php self::after_updater( $this->options ); ?></p>
 		<?php
		}

		// process is completed!
		// show link to CBOX dashboard
		else {
			usleep(500000);

			$args = ! empty( $this->options ) ? $this->options : array();
			self::after_updater( $args );
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
		$redirect_link = $redirect_text = '';

		// if a redirect link is passed, use it.
		if ( ! empty( $args ) ) {
			$redirect_link = ! empty( $args['redirect_link'] ) ? $args['redirect_link'] : '';
			$redirect_text = ! empty( $args['redirect_text'] ) ? $args['redirect_text'] : '';

		// if a redirect link is passed during the class constructor, use it
		} elseif ( ! self::_is_static() && ! empty( $this->options['redirect_link'] ) && ! empty( $this->options['redirect_text'] ) ) {
			$redirect_link = $this->options['redirect_link'];
			$redirect_text = $this->options['redirect_text'];
		}

		// CBOX hasn't been installed ever.
		if ( ! cbox_get_installed_revision_date() && empty( $redirect_link ) ) {
			$redirect_text = __( 'Continue to the CBOX dashboard', 'cbox' );
			$redirect_link = self_admin_url( 'admin.php?page=cbox' );
		}

		// default fallback
		if ( '' === $redirect_link ) {
			$redirect_link = self_admin_url( 'admin.php?page=cbox-plugins' );
			$redirect_text = __( 'Return to the CBOX Plugins page', 'cbox' );

			if ( ! empty( $_GET['type'] ) ) {
				$redirect_link = add_query_arg( 'type', esc_attr( $_GET['type'] ), $redirect_link );
			}
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
		$skin_args = array();

		if ( ! empty( $plugins['upgrade'] ) ) {
			self::$is_upgrade  = true;
		}

		if ( ! empty( $plugins['install'] ) ) {
			self::$is_install  = true;
		}

		if ( ! empty( $plugins['activate'] ) ) {
			self::$is_activate = true;
		}

		if ( ! empty( $settings['redirect_link'] ) ) {
			$skin_args['redirect_link'] = $settings['redirect_link'];
		}

		if ( ! empty( $settings['redirect_text'] ) ) {
			$skin_args['redirect_text'] = $settings['redirect_text'];
		}

		// if no plugins passed, stop the updater now!
		if ( ! $plugins ) {
			_doing_it_wrong( __METHOD__, 'first argument, (array) $plugins, cannot be empty.' );
			return false;
		}

		// dependency-time!
		// flatten the associative array to make dependency checks easier
		$plugin_list = call_user_func_array( 'array_merge', $plugins );

		// get requirements
		$requirements = (array) Plugin_Dependencies::get_requirements();

		/*
		 * If a plugin is not installed, but has dependencies, we have to parse those
		 * dependencies before looping through the rest of the plugins.
		 *
		 * This is done because the Plugin Dependencies library cannot parse plugins
		 * it doesn't know about.
		 */
		if ( ! empty( $plugins['install'] ) ) {
			$cbox_plugins = CBox_Plugins::get_plugins();
			$dependencies = array_flip( array_keys( CBox_Plugins::get_plugins( 'dependency' ) ) );

			foreach ( $plugins['install'] as $plugin ) {
				if ( ! isset( $cbox_plugins[ $plugin ]['depends'] ) ) {
					continue;
				}

				foreach ( Plugin_Dependencies::parse_field( $cbox_plugins[ $plugin ]['depends'] ) as $dep ) {
					// a dependent name can contain a version number, so let's get just the name
					$plugin_name = rtrim( strtok( $dep, '(' ) );

					// see if plugin has any requirements
					$requirement = Plugin_Dependencies::parse_requirements( $dep );
					if ( empty( $requirement ) ) {
						continue;
					}

					if ( isset( $requirement['not-installed'] ) ) {
						// Check if uninstalled plugin is part of our CBOX plugin spec.
						foreach ( $requirement['not-installed'] as $i => $_plugin ) {
							if ( ! isset( $dependencies[ $_plugin ] ) ) {
								unset( $requirement['not-installed'][$i] );
							}
						}
					}

					// We've found the dependent plugin in our spec; add it to our requirements.
					if ( ! empty( $requirement['not-installed'] ) ) {
						$requirements[ $plugin ] = $requirement;
					}
				}
			}
		}

		// loop through each submitted plugin and check for any dependencies
		foreach ( $plugin_list as $plugin ) {
			// we have dependents!
			if ( ! empty( $requirements[$plugin] ) ) {

				// now loop through each dependent plugin state and add that plugin to our list
				// before we start the whole process!
				foreach ( $requirements[$plugin] as $dep_state => $dep_plugins ) {
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

		foreach ( $plugins as $state => $p ) {
			$plugins[$state] = array_unique( $p );
		}

		/**
		 * Hook to do something before the CBOX updater fires.
		 *
		 * @since 1.1.0
		 */
		do_action( 'cbox_before_updater' );

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

		// if no upgrades are available, move on to installs
		} elseif( self::$is_install ) {
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

		// if no upgrades or installs are available, move on to activations
		} elseif( self::$is_activate ) {
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
		$plugin = '';
		$network_activate = $install_only = false;

		// activates a plugin post-upgrade
		if ( ! empty( $hook_extra['plugin'] ) ) {
			$plugin = $hook_extra['plugin'];

			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
			$plugin_name = $plugin_data['Name'];

		// activates a plugin post-install
		} elseif ( ! empty( $result['destination_name'] ) ) {
			// Fetch data for plugin.
			$plugin_data = get_plugins( '/' . $result['destination_name'] );

			// When a plugin is installed, we need to find the plugin loader file
			$plugin_loader = key( $plugin_data );

			// Grab the plugin name.
			$plugin_name = $plugin_data[ $plugin_loader ]['Name'];

			// this makes sure that validate_plugin() works in activate_plugin()
			if ( ! wp_using_ext_object_cache() ) {
				wp_cache_flush();
			}

			$plugin = $result['destination_name'] . '/' . $plugin_loader;
		}

		// Do not activate if plugin is install-only.
		if ( true === CBox_Plugins::is_plugin_type( $plugin_name, 'install-only' ) ) {
			// Allow activation if this is also a dependent plugin.
			$dependency = CBox_Plugins::get_plugins( 'dependency' );
			if ( empty( $dependency[ $plugin_name ] ) ) {
				return $bool;
			}
		}

		if ( '' !== $plugin ) {
			$cbox_plugins = CBox_Plugins::get_plugins();

			// If CBOX plugin manifest is empty, must load package data again.
			if ( empty( $cbox_plugins ) ) {
				/** This hook is documented in admin/plugins-loader.php */
				do_action( 'cbox_plugins_loaded', cbox()->plugins );

				$cbox_plugins = CBox_Plugins::get_plugins();
			}

			if ( isset( $cbox_plugins[ $plugin_name ] ) ) {
				$network_activate = $cbox_plugins[ $plugin_name ]['network'];
			} else {
				$dependency = CBox_Plugins::get_plugins( 'dependency' );
				$network_activate = $dependency[ $plugin_name ]['network'];
			}

			/*
			 * Special case for root-blog plugins.
			 *
			 * BuddyPress supports a different root blog ID, so if BuddyPress is activated
			 * we need to switch to that blog to get the correct active plugins list.
			 */
			if ( false === $network_activate && 1 !== cbox_get_main_site_id() ) {
				switch_to_blog( cbox_get_main_site_id() );
			}

			// activate the plugin
			activate_plugin( $plugin, '', $network_activate );

			if ( false === $network_activate && 1 !== cbox_get_main_site_id() ) {
				restore_current_blog();
			}
		}

		return $bool;
	}

}
