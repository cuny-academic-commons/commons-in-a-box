<?php
/**
 * Set up plugin management
 *
 * @package Commons_In_A_Box
 * @subpackage Plugins
 * @since 0.1
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class CIAB_Plugins {

	/**
	 * Static variable to hold our various plugins
	 *
	 * @var array
	 */
	private static $plugins = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		// includes
		$this->includes();

		// setup globals
		$this->setup_globals();

		// setup our hooks
		$this->setup_hooks();
	}

	/**
	 * Includes.
	 */
	private function includes() {
		// add the Plugin Dependencies plugin
		if ( ! class_exists( 'Plugin_Dependencies' ) )
			require_once( CIAB_LIB_DIR . 'wp-plugin-dependencies/plugin-dependencies.php' );

		// make sure to include the WP Plugin API if it isn't available
		//if ( ! function_exists( 'get_plugins' ) )
		//	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

		// make sure to include the WP Update API if it isn't available
		//if ( ! function_exists( 'get_plugin_updates' ) )
		//	require( ABSPATH . '/wp-admin/includes/update.php' );
	}

	/**
	 * Setup globals.
	 */
	private function setup_globals() {
		$this->register_required_plugins();
		$this->register_recommended_plugins();
		$this->register_optional_plugins();
		$this->register_dependency_plugins();
	}

	/**
	 * Setup our hooks.
	 */
	private function setup_hooks() {
		// add our plugins table to the Cbox dashboard
		add_action( 'cbox_dashboard_form',                   array( &$this, 'cbox_dashboard' ) );

		// validate any settings changes submitted from the Cbox dashboard
		add_action( 'admin_init',                            array( &$this, 'validate_cbox_dashboard' ) );
		
		// load PD on the "Dashboard > Updates" page so we can filter out our CBox plugins
		add_action( 'load-update-core.php',                  create_function( '', 'Plugin_Dependencies::init();' ) );

		// filter PD's dependency list
		add_filter( 'scr_plugin_dependency_before_parse',    array( &$this, 'filter_pd_dependencies' ) );

		// if an admin wants to have full control over the Cbox plugins, set $this->is_override to true
		// otherwise, we prevent Cbox plugins from being seen in the regular Plugins table and from WP updates
		if (  ! $this->is_override() ) {
			// exclude Cbox plugins from the "Plugins" list table
			add_filter( 'all_plugins',                   array( &$this, 'exclude_cbox_plugins' ) );

			// remove Cbox plugins from WP's update plugins routine
			add_filter( 'site_transient_update_plugins', array( &$this, 'remove_cbox_plugins_from_updates' ) );
		}
	}

	/**
	 * For expert site managers, we allow them to override Cbox's settings.
	 *
	 * @return bool
	 * @todo this needs to be fleshed out... until then, just manually toggle the boolean!
	 */
	public function is_override() {
		return false;
	}

	/** PLUGINS-SPECIFIC **********************************************/

	/**
	 * CBox requires several popular plugins.
	 *
	 * We register these plugins here for later use.
	 *
	 * @return array
	 */
	private function register_required_plugins() {

		// BuddyPress
		self::register_plugin( array(
			'plugin_name'      => 'BuddyPress',
			'cbox_name'        => 'BuddyPress',
			'cbox_description' => 'Social networking FTW!',
			'version'          => '1.6.1'
		) );
	}

	/**
	 * CBox recommends several popular plugins.
	 *
	 * We register these plugins here for later use.
	 *
	 * @return array
	 */
	private function register_recommended_plugins() {

		// BuddyPress Docs
		self::register_plugin( array(
			'plugin_name'      => 'BuddyPress Docs',
			'type'             => 'recommended',
			'cbox_name'        => 'Docs',
			'cbox_description' => "Adds collaborative Docs to BuddyPress.",
			'version'          => '1.1.25',
			'depends'          => 'BuddyPress (>=1.5)',
			'download_url'     => 'http://downloads.wordpress.org/plugin/buddypress-docs.1.1.25.zip',
		) );

		// BuddyPress Group Email Subscription
		self::register_plugin( array(
			'plugin_name'      => 'BuddyPress Group Email Subscription',
			'type'             => 'recommended',
			'cbox_name'        => 'Group Email Subscription',
			'cbox_description' => 'Allows group members to receive email notifications of activity within a group.',
			'depends'          => 'BuddyPress (>=1.5)',
			'version'          => '3.2.1',
			'download_url'     => 'http://downloads.wordpress.org/plugin/buddypress-group-email-subscription.3.2.1.zip',
		) );

		// Invite Anyone
		self::register_plugin( array(
			'plugin_name'      => 'Invite Anyone',
			'type'             => 'recommended',
			'cbox_name'        => 'Invite Anyone',
			'cbox_description' => "Makes BuddyPress's invitation features more powerful. ",
			'version'          => '1.0.15',
			'depends'          => 'BuddyPress (>=1.5)',
			'download_url'     => 'http://downloads.wordpress.org/plugin/invite-anyone.1.0.15.zip',
		) );

		// Custom Profile Filters for BuddyPress
		self::register_plugin( array(
			'plugin_name'      => 'Custom Profile Filters for BuddyPress',
			'type'             => 'recommended',
			'cbox_name'        => 'Custom Profile Filters for BuddyPress',
			'cbox_description' => 'Allows users to take control of the way the links in their Buddypress profiles are handled.',
			'depends'          => 'BuddyPress (>=1.2)',
			'version'          => '3.0.1',
			'download_url'     => 'http://downloads.wordpress.org/plugin/custom-profile-filters-for-buddypress.0.3.1.zip',
		) );

		// bbPress
		self::register_plugin( array(
			'plugin_name'      => 'bbPress',
			'type'             => 'recommended',
			'cbox_name'        => 'bbPress',
			'cbox_description' => 'Forums made the WordPress way.',
			'version'          => '2.1.2',
			'download_url'     => 'http://downloads.wordpress.org/plugin/bbpress.2.1.2.zip'
		) );

		// More Privacy Options
		// @todo Do a multisite check before adding
		self::register_plugin( array(
			'plugin_name'      => 'More Privacy Options',
			'type'             => 'recommended',
			'cbox_name'        => 'More Privacy Options',
			'cbox_description' => 'Add more blog privacy options for your users.',
			'version'          => '3.2.1.5',
			'download_url'     => 'http://downloads.wordpress.org/plugin/more-privacy-options.3.2.1.5.zip'
		) );

		// CAC Featured Content
		self::register_plugin( array(
			'plugin_name'      => 'CAC Featured Content',
			'type'             => 'recommended',
			'cbox_name'        => 'Featured Content',
			'cbox_description' => 'Provides a widget that allows you to select among five different content types to feature in a widget area.',
			'version'          => '0.8.4',
			'download_url'     => 'http://downloads.wordpress.org/plugin/cac-featured-content.0.8.4.zip'
		) );


		// Other plugins for later:
		// BP Group Documents (whatever replacement version we've got by that time)
		// Forum Attachments (ditto);
	}

	/**
	 * CBox also lists a few other plugins that can be used to extend functionality.
	 *
	 * Although, they are slightly below the recommended level, we've tested these
	 * plugins and think they're cool enough to be a part of CBox.
	 *
	 * We register these plugins here for later use.
	 *
	 * @return array
	 */
	private function register_optional_plugins() {

		// BuddyPress GroupBlog
		self::register_plugin( array(
			'plugin_name'      => 'BuddyPress Groupblog',
			'type'             => 'optional',
			'cbox_name'        => 'BuddyPress Groupblog',
			'cbox_description' => 'Enable a BuddyPress group to have a single blog associated with it.',
			'depends'          => 'BuddyPress (>=1.6)',
			'version'          => '1.8',
			'download_url'     => 'http://downloads.wordpress.org/plugin/bp-groupblog.1.8.zip',
		) );

		// BuddyPress GroupBlog
		self::register_plugin( array(
			'plugin_name'      => 'BP External Group Blogs',
			'type'             => 'optional',
			'cbox_name'        => 'BuddyPress External Group RSS',
			'cbox_description' => 'Give BuddyPress group creators and administrators the ability to attach external RSS feeds to groups.',
			'depends'          => 'BuddyPress (>=1.2)',
			'version'          => '1.2.1',
			'download_url'     => 'http://downloads.wordpress.org/plugin/external-group-blogs.1.2.1.zip',
		) );

		// BuddyPress Reply By Email
		// @todo Add this back when RBE is added in the WP.org plugins repo
		/*
		self::register_plugin( array(
			'plugin_name'      => 'BuddyPress Reply By Email',
			'type'             => 'optional',
			'cbox_name'        => 'Reply By Email',
			'cbox_description' => "Reply to various emails from the comfort of your email inbox",
			'version'          => '1.0',
			'depends'          => 'BuddyPress (>=1.5)',
			'download_url'     => '',
		) );
		*/

		// WP Better Emails
		/*
		self::register_plugin( array(
			'plugin_name'      => 'WP Better Emails',
			'type'             => 'optional',
			'cbox_name'        => 'HTML Email',
			'cbox_description' => 'Enable and design HTML emails',
			'version'          => '0.2.4',
			'download_url'     => 'http://downloads.wordpress.org/plugin/wp-better-emails.0.2.4.zip'
		) );
		*/
	}

	/**
	 * Register CBox's dependency plugins internally.
	 *
	 * The reason why this is done is Plugin Dependencies (PD) does not know the download URL for dependent plugins.
	 * So if a dependent plugin is deemed incompatible by PD (either not installed or incompatible version),
	 * we can easily install or upgrade that plugin.
	 *
	 * This is designed to avoid pinging the WP.org Plugin Repo API multiple times to grab the download URL,
	 * and is much more efficient for our usage.
	 *
	 * @return array
	 */
	private function register_dependency_plugins() {

		// BuddyPress
		self::register_plugin( array(
			'plugin_name'  => 'BuddyPress',
			'type'         => 'dependency',
			'download_url' => 'http://downloads.wordpress.org/plugin/buddypress.1.6.1.zip'
		) );
	}

	/**
	 * Register a required or dependent plugin for CBox.
	 *
	 * @see CIAB_Plugins::required_plugins()
	 * @see CIAB_Plugins::dependeny_plugins()
	 */
	private function register_plugin( $args = '' ) {
		$defaults = array(
			'plugin_name'      => false, // the name of the plugin as in the plugin header
			'type'             => 'required', // types include 'required', 'dependency'
			'cbox_name'        => false, // CBox's label for the plugin
			'cbox_description' => false, // CBox's short description of the plugin
			'depends'          => false, // our own defined dependencies for the plugin; uses same syntax as PD
			                             // maybe we *don't* want to use PD's syntax because if a plugin isn't installed we'll have to ping the WP.org Plugin Repo API and search for it...
			'version'          => false, // the version number of the plugin we want to compare the installed version with (if applicable)
			'download_url'     => false  // the download URL of the plugin used if the active version is not compatible with our version
		);

		$r = wp_parse_args( $args, $defaults );

		extract( $r );

		if ( empty( $plugin_name ) )
			return false;

		switch( $type ) {
			case 'required' :
			case 'recommended' :
			case 'optional' :
				self::$plugins[$type][$plugin_name]['cbox_name']        = $cbox_name;
				self::$plugins[$type][$plugin_name]['cbox_description'] = $cbox_description;
				self::$plugins[$type][$plugin_name]['depends']          = $depends;
				self::$plugins[$type][$plugin_name]['version']          = $version;
				self::$plugins[$type][$plugin_name]['download_url']     = $download_url;

				break;

			case 'dependency' :
				self::$plugins[$type][$plugin_name]['download_url']     = $download_url;

				break;
		}

	}

	/**
	 * Helper method to grab all CBox plugins of a certain type.
	 *
	 * @param string $type Type of CBox plugin. Either 'all', 'required', 'recommended', 'optional', 'dependency'.
	 * @return mixed Array of plugins on success. Boolean false on failure.
	 */
	public static function get_plugins( $type = 'all' ) {
		// if type is 'all', we want all CBox plugins regardless of type
		if ( $type == 'all' ) {
			$plugins = self::$plugins;
			
			// okay, I lied, we want all plugins except dependencies!
			unset( $plugins['dependency'] );

			// flatten associative array
			return call_user_func_array( 'array_merge', $plugins );
		}

		if ( empty( self::$plugins[$type] ) )
			return false;

		return self::$plugins[$type];
	}

	/** HOOKS *********************************************************/

	/**
	 * Filter PD's dependencies to add our own specs.
	 *
	 * @return array
	 */
	public function filter_pd_dependencies( $plugins ) {
		$plugins_by_name = Plugin_Dependencies::$plugins_by_name;

		foreach( self::get_plugins() as $plugin => $data ) {
			// try and see if our required plugin is installed
			$loader = ! empty( $plugins_by_name[ $plugin ] ) ? $plugins_by_name[ $plugin ] : false;

			// if plugin is installed and if the plugin doesn't already have predefined dependencies, add our custom deps!
			if( ! empty( $loader ) && ! empty( $data['depends'] ) && empty( $plugins[ $loader ]['Depends'] ) ) {
				$plugins[ $loader ]['Depends'] = $data['depends'];
			}
		}

		return $plugins;
	}

	/**
	 * Exclude CBox's plugins from the "Plugins" list table.
	 *
	 * @return array
	 */
	public function exclude_cbox_plugins( $plugins ) {
		$plugins_by_name = Plugin_Dependencies::$plugins_by_name;

		foreach( self::get_plugins() as $plugin => $data ) {
			$loader = ! empty( $plugins_by_name[ $plugin ] ) ? $plugins_by_name[ $plugin ] : false;

			if( ! empty( $loader ) && ! empty( $plugins[ $loader ] ) )
				unset( $plugins[ $loader ] );
		}

		return $plugins;
	}


	/**
	 * Cbox plugins should be removed from WP's update plugins routine.
	 */
	public function remove_cbox_plugins_from_updates( $plugins ) {
		$i = 0;

		foreach ( self::get_plugins() as $plugin => $data ) {
			// get the plugin loader file
			$plugin_loader = Plugin_Dependencies::get_pluginloader_by_name( $plugin );

			if ( ! empty( $plugins->response[ $plugin_loader ] ) ) {
				unset( $plugins->response[ $plugin_loader ] );
				++$i;
			}
		}

		/* this might be unnecessary...
		if ( $i > 0 ) {
			set_site_transient( 'update_plugins', $plugins );
		}
		*/

		return $plugins;
	}

	/** CBOX DASHBOARD-SPECIFIC ***************************************/

	/**
	 * Before the Cbox dashboard page is rendered, do any validation and checks
	 * from form submissions or action links.
	 */
	public function validate_cbox_dashboard() {
		if ( empty( $_REQUEST['page'] ) )
			return;

		if ( $_REQUEST['page'] != 'cbox' )
			return;

		// form submission
		if ( ! empty( $_REQUEST['cbox-update'] ) ) {
			// verify nonce
			check_admin_referer( 'cbox_update' );

			// see if any plugins were submitted
			// if so, set a reference variable to note that CBox is updating
			if ( ! empty( $_REQUEST['cbox_plugins'] ) ) {
				cbox()->update = true;
			}
		}

		// deactivate a single plugin from the Cbox dashboard
		// basically a copy and paste of the code available in /wp-admin/plugins.php
		if ( ! empty( $_REQUEST['cbox-action'] ) && ! empty( $_REQUEST['plugin'] ) ) {
			$plugin = $_REQUEST['plugin'];

			if ( ! current_user_can('activate_plugins') )
				wp_die(__('You do not have sufficient permissions to deactivate plugins for this site.'));

			switch( $_REQUEST['cbox-action'] ) {
				case 'deactivate' :
					check_admin_referer('deactivate-plugin_' . $plugin);
					if ( ! is_network_admin() && is_plugin_active_for_network( $plugin ) ) {
						wp_redirect( self_admin_url("admin.php?page=cbox") );
						exit;
					}
					else {
						// let PD do it's thing
						// deactivate any dependent plugins for the plugin in question
						Plugin_Dependencies::init();

						$deactivated = call_user_func( array( 'Plugin_Dependencies', 'deactivate_cascade' ), (array) $plugin );
						set_transient( "pd_deactivate_cascade", $deactivated );

						// now deactivate the plugin
						deactivate_plugins( $plugin );
						update_option('recently_activated', array($plugin => time()) + (array)get_option('recently_activated'));

						wp_redirect( self_admin_url("admin.php?page=cbox&deactivate=true") );
						exit;
					}

					break;
			}
		}

		// admin notices
		if ( ! empty( $_REQUEST['deactivate'] ) ) {
			add_action( 'admin_notices', create_function( '', "
				echo '<div class=\'updated\'><p>' . __( 'Plugin deactivated.', 'cbox' ) . '</p></div>';
			" ) );

			// if PD deactivated any other dependent plugins, show admin notice here
			// basically a copy-n-paste of Plugin_Dependencies::generate_dep_list()
			$deactivated = get_transient( 'pd_deactivate_cascade' );
			delete_transient( 'pd_deactivate_cascade' );

			// if no other plugins were deactivated, stop now!
			if ( empty( $deactivated ) )
				return;

			Plugin_Dependencies::init();

			$text = __( 'The following plugins have also been deactivated:', 'cbox' );

			// render each plugin as a list item
			$all_plugins = Plugin_Dependencies::$all_plugins;
			$dep_list = '';
			foreach ( $deactivated as $dep ) {
				$plugin_ids = Plugin_Dependencies::get_providers( $dep );

				if ( empty( $plugin_ids ) ) {
					$name = html( 'span', esc_html( $dep['Name'] ) );
				} else {
					$list = array();
					foreach ( $plugin_ids as $plugin_id ) {
						$name = isset( $all_plugins[ $plugin_id ]['Name'] ) ? $all_plugins[ $plugin_id ]['Name'] : $plugin_id;
						//$list[] = html( 'a', array( 'href' => '#' . sanitize_title( $name ) ), $name );
						$list[] = $name;
					}
					$name = implode( ' or ', $list );
				}

				$dep_list .= html( 'li', $name );
			}

			// now add the admin notice for any other deactivated plugins by PD
			add_action( 'admin_notices', create_function( '', "
				echo
				html( 'div', array( 'class' => 'updated' ),
					html( 'p', '$text', html( 'ul', array( 'class' => 'dep-list' ), '$dep_list' ) )
				);
			" ) );
		}
	}

	/**
	 * The plugin table that gets displayed on the Cbox dashboard.
	 */
	public function cbox_dashboard() {

		// get unfulfilled requirements for all plugins
		//$requirements = Plugin_Dependencies::get_requirements();
	?>
		<h3><?php _e( 'Plugins', 'cbox' ); ?></h3>

		<p><?php _e( 'Commons in a Box recommends the following plugins for use with your WordPress site.', 'cbox' ); ?></p>

		<table class="widefat fixed plugins" cellspacing="0">
			<thead>
				<tr>
					<th scope="col" class="manage-column check-column"><input type="checkbox" id="plugins-select-all" /></th>
					<th scope="col" id="name" class="manage-column column-name" style="width: 190px;"><?php _e( 'Plugin', 'cbox' ); ?></th>
					<th scope="col" id="description" class="manage-column column-description"><?php _e( 'Description', 'cbox' ); ?></th>
				</tr>
			</thead>

			<tfoot>
				<tr>
					<th scope="col" class="manage-column check-column"><input type="checkbox" id="plugins-select-all-2" /></th>
					<th scope="col" class="manage-column column-name" style="width: 190px;"><?php _e( 'Plugin', 'cbox' ); ?></th>
					<th scope="col" class="manage-column column-description"><?php _e( 'Description', 'cbox' ); ?></th>
				</tr>
			</tfoot>

			<tbody>

			<?php
				foreach ( self::get_plugins() as $plugin => $data ) :
					// attempt to get the plugin loader file
					$loader = Plugin_Dependencies::get_pluginloader_by_name( $plugin );

					// get the required plugin's state
					$state  = $this->get_plugin_state( $loader, $data );
			?>
				<tr>
					<th scope='row' class='check-column'>
						<?php if ( $state != 'deactivate' ) : ?>
							<input type="checkbox" id="cbox_plugins_<?php echo sanitize_key( $plugin ); ?>" name="cbox_plugins[<?php echo $state; ?>][]" value="<?php echo esc_attr( $plugin ); ?>" />
						<?php else : ?>
							<img src="<?php echo self_admin_url( 'images/yes.png' ); ?>" alt="" title="Plugin is already active!" style="margin-left:7px;" />
						<?php endif; ?>
					</th>

					<td class="plugin-title" style="width: 190px;">
						<strong><?php echo $data['cbox_name']; ?></strong>
						<div class="row-actions-visible">
						<?php if ( $state == 'deactivate' ) : ?>
							<a href="<?php $this->deactivate_link( $loader ); ?>"><?php _e( 'Deactivate', 'cbox' ); ?></a>
						<?php elseif ( $state == 'upgrade' ) : ?>
							<div class="plugin-update-tr"><p class="update-message" style="margin:5px 0;"><?php _e( 'Update available.', 'cbox' ); ?></p></div>
						<?php endif; ?>
						</div>
					</td>

					<td class="column-description desc">
						<div class="plugin-description">
							<p><?php echo $data['cbox_description']; ?></p>

							<?php
								// parse dependencies if available
								// @todo this needs to reference PD's list instead of our internal one...
								if( $data['depends'] ) :
									$deps = array();

									echo '<p>';
									_e( 'Requires: ', 'cbox' );

									foreach( $this->parse_dependency_str( $data['depends'] ) as $dependency ) :
										// a dependent name can contain a version number, so let's get just the name
										$plugin_name = rtrim( strtok( $dependency, '(' ) );

										$dep_str    = $dependency;
										$dep_loader = Plugin_Dependencies::get_pluginloader_by_name( $plugin_name );

										if ( $dep_loader && $this->is_plugin_active( $dep_loader ) )
											$dep_str .= ' <span class="enabled">' . __( '(enabled)', 'cbox' ) . '</span>';
										elseif( $dep_loader )
											$dep_str .= ' <span class="disabled">' . __( '(disabled)', 'cbox' ) . '</span>';
										else
											$dep_str .= ' <span class="not-installed">' . __( '(not installed)', 'cbox' ) . '</span>';
										$deps[] = $dep_str;
									endforeach;

									echo implode( ', ', $deps ) . '</p>';
								endif;
							?>
						</div>

					</td>
				</tr>

			<?php endforeach; ?>

			</tbody>
		</table>
	<?php
	}

	/**
	 * Screen that shows during an update.
	 */
	public function update_screen() {

		// if we're not in the middle of an update, stop now!
		if ( empty( cbox()->update ) )
			return;

		$plugins = $_REQUEST['cbox_plugins'];

		// include the CBox Plugin Upgrade and Install API
		if ( ! class_exists( 'CBox_Plugin_Upgrader' ) )
			require( CIAB_PLUGIN_DIR . 'admin/plugin-install.php' );

		// some HTML markup!
		echo '<div class="wrap">';
		screen_icon('plugins');
		echo '<h2>' . esc_html__('Update CBox', 'cbox' ) . '</h2>';

		// start the upgrade!
		$installer = new CBox_Updater( $plugins );

		echo '</div>';
	}

	/** HELPERS *******************************************************/

	/**
	 * Helper method to parse a comma-delimited dependency string.
	 *
	 * eg. "BuddyPress (>=1.5), BuddyPress Docs, Invite Anyone"
	 *
	 * @param string $dependency_str Comma-delimited list of plugins. Can include version dependencies. See PHPDoc.
	 * @uses Plugin_Dependencies::parse_field()
	 * @return array
	 */
	private function parse_dependency_str( $dependency_str ) {
		return Plugin_Dependencies::parse_field( $dependency_str );
	}

	/**
	 * Helper method to see if a plugin is active.
	 *
	 * This is a resource-friendly version that already references the active
	 * plugins in the Plugin Dependencies variable.
	 *
	 * Might remove this entirely...
	 *
	 * @param string $loader Plugin loader filename.
	 * @return bool
	 */
	public function is_plugin_active( $loader ) {
		return in_array( $loader, Plugin_Dependencies::$active_plugins ) || is_plugin_active_for_network( $loader );
	}

	/**
	 * Helper method to get the Cbox required plugin's state.
	 *
	 * @param str $loader The required plugin's loader filename
 	 * @param array $data The required plugin's data. See $this->register_required_plugins().
	 */
	public function get_plugin_state( $loader, $data ) {
		$state = false;

		// plugin exists
		if ( $loader ) {
			// if plugin is active, set state to 'deactivate'
			if ( $this->is_plugin_active( $loader ) )
				$state = 'deactivate';
			else
				$state = 'activate';

			// a required version was passed
			if ( ! empty( $data['version'] ) ) {
				// get the current, installed plugin's version
				$current_version = Plugin_Dependencies::$all_plugins[$loader]['Version'];

				// if current plugin is older than required plugin version, set state to 'upgrade'
				if ( version_compare( $current_version, $data['version'] ) < 0  )
					$state = 'upgrade';
			}
		}
		// plugin doesn't exist
		else {
			$state = 'install';
		}

		return $state;
	}

	/**
	 * Helper method to output the deactivation URL for a plugin in the CBox dashboard.
	 *
	 * @param str $loader The plugin's loader filename
	 */
	private function deactivate_link( $loader ) {
		echo self_admin_url( 'admin.php?page=cbox&cbox-action=deactivate&plugin=' . urlencode( $loader ) . '&_wpnonce=' . wp_create_nonce( 'deactivate-plugin_' . $loader ) );
	}

}

?>