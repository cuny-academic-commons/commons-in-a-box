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

class CBox_Plugins {

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
			require_once( CBOX_LIB_DIR . 'wp-plugin-dependencies/plugin-dependencies.php' );

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
		// setup the CBox plugin menu
		add_action( 'cbox_admin_menu',                       array( $this, 'setup_plugins_page' ) );

		// load PD on the "Dashboard > Updates" page so we can filter out our CBox plugins
		add_action( 'load-update-core.php',                  array( 'Plugin_Dependencies', 'init' ) );

		// filter PD's dependency list
		add_filter( 'scr_plugin_dependency_before_parse',    array( $this, 'filter_pd_dependencies' ) );

		// prevent Cbox plugins from being seen in the regular Plugins table and from WP updates
		if ( ! $this->is_override() ) {
			// exclude Cbox plugins from the "Plugins" list table
			add_filter( 'all_plugins',                   array( $this, 'exclude_cbox_plugins' ) );

			// remove Cbox plugins from WP's update plugins routine
			add_filter( 'site_transient_update_plugins', array( $this, 'remove_cbox_plugins_from_updates' ) );

			// do not show PD's pre-activation warnings if admin cannot override CBox plugins
			add_filter( 'pd_show_preactivation_warnings', '__return_false' );
		}
	}

	/**
	 * For expert site managers, we allow them to view Cbox plugins in the
	 * regular Plugins table and on the WP Updates page.
	 *
	 * To do this, add the following code snippet to wp-config.php
	 *
	 * 	define( 'CBOX_OVERRIDE_PLUGINS', true );
	 *
	 * @return bool
	 */
	public function is_override() {
		return defined( 'CBOX_OVERRIDE_PLUGINS' ) && constant( 'CBOX_OVERRIDE_PLUGINS' ) === true;
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
			'cbox_description' => 'Build a social network for your company, school, sports team or niche community.',
			'version'          => '1.6.1',
			'admin_settings'   => 'options-general.php?page=bp-components',
			'network_settings' => 'settings.php?page=bp-components'
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
			'admin_settings'   => 'edit.php?post_type=bp_doc',
			'network_settings' => 'root-blog-only'
		) );

		// BuddyPress Group Email Subscription
		self::register_plugin( array(
			'plugin_name'      => 'BuddyPress Group Email Subscription',
			'type'             => 'recommended',
			'cbox_name'        => 'Group Email Subscription',
			'cbox_description' => 'Allows BuddyPress group members to receive email notifications of activity from their groups.',
			'depends'          => 'BuddyPress (>=1.5)',
			'version'          => '3.2.1',
			'download_url'     => 'http://downloads.wordpress.org/plugin/buddypress-group-email-subscription.3.2.1.zip',
			'admin_settings'   => 'admin.php?page=ass_admin_options', // this doesn't work for BP_ENABLE_MULTIBLOG
			'network_settings' => 'admin.php?page=ass_admin_options'
		) );

		// Invite Anyone
		self::register_plugin( array(
			'plugin_name'      => 'Invite Anyone',
			'type'             => 'recommended',
			'cbox_name'        => 'Invite Anyone',
			'cbox_description' => "Makes BuddyPress' invitation features more powerful.",
			'version'          => '1.0.15',
			'depends'          => 'BuddyPress (>=1.5)',
			'download_url'     => 'http://downloads.wordpress.org/plugin/invite-anyone.1.0.15.zip',
			'admin_settings'   => 'admin.php?page=invite-anyone',
			'network_settings' => 'admin.php?page=invite-anyone'
		) );

		// Custom Profile Filters for BuddyPress
		self::register_plugin( array(
			'plugin_name'      => 'Custom Profile Filters for BuddyPress',
			'type'             => 'recommended',
			'cbox_name'        => 'Custom Profile Filters for BuddyPress',
			'cbox_description' => 'Allows users to take control of how their profile links in Buddypress are handled.',
			'depends'          => 'BuddyPress (>=1.2)',
			'version'          => '0.3.1',
			'download_url'     => 'http://downloads.wordpress.org/plugin/custom-profile-filters-for-buddypress.0.3.1.zip',
		) );

		// bbPress
		// @todo if network-activated, this is enabled across all sub-sites... need to limit this
		self::register_plugin( array(
			'plugin_name'      => 'bbPress',
			'type'             => 'recommended',
			'cbox_name'        => 'bbPress',
			'cbox_description' => 'Forums made the WordPress way.',
			'version'          => '2.1.2',
			'download_url'     => 'http://downloads.wordpress.org/plugin/bbpress.2.1.2.zip',
			'admin_settings'   => 'options-general.php?page=bbpress',
			'network_settings' => 'root-blog-only'
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

		// only show the following plugins in network mode
		if ( is_network_admin() ) :
			// More Privacy Options
			self::register_plugin( array(
				'plugin_name'      => 'More Privacy Options',
				'type'             => 'recommended',
				'cbox_name'        => 'More Privacy Options',
				'cbox_description' => 'Add more blog privacy options for your users.',
				'version'          => '3.2.1.5',
				'download_url'     => 'http://downloads.wordpress.org/plugin/more-privacy-options.zip',
				'network_settings' => 'settings.php#menu'
			) );

			// BuddyPress GroupBlog
			self::register_plugin( array(
				'plugin_name'      => 'BP Groupblog',
				'type'             => 'recommended',
				'cbox_name'        => 'BuddyPress Groupblog',
				'cbox_description' => 'Enable a BuddyPress group to have a single blog associated with it.',
				'depends'          => 'BuddyPress (>=1.6)',
				'version'          => '1.8',
				'download_url'     => 'http://downloads.wordpress.org/plugin/bp-groupblog.1.8.zip',
				'network_settings' => 'settings.php?page=bp_groupblog_management_page'
			) );

		endif;


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

		// BuddyPress External Group Blogs
		self::register_plugin( array(
			'plugin_name'      => 'External Group Blogs',
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
			'admin_settings'   => is_multisite() ? 'options-general.php?page=bp-rbe' : 'admin.php?page=bp-rbe',
			'network_settings' => 'root-blog-only
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
	 * Register a plugin in CBox.
	 *
	 * @see CBox_Plugins::register_required_plugins()
	 * @see CBox_Plugins::register_recommended_plugins()
	 * @see CBox_Plugins::register_optional_plugins()
	 * @see CBox_Plugins::register_dependency_plugins()
	 */
	private function register_plugin( $args = '' ) {
		$defaults = array(
			'plugin_name'      => false, // (required) the name of the plugin as in the plugin header
			'type'             => 'required', // types include 'required', 'recommended', 'optional', dependency'
			'cbox_name'        => false, // CBox's label for the plugin
			'cbox_description' => false, // CBox's short description of the plugin
			'depends'          => false, // our own defined dependencies for the plugin; uses same syntax as PD
			'version'          => false, // the version number of the plugin we want to compare the installed version with (if applicable)
			'download_url'     => false, // the download URL of the plugin used if the active version is not compatible with our version
			'admin_settings'   => false, // if applicable, where does the admin settings page reside? should be relative to /wp-admin/
			'network_settings' => false, // if plugin is network-only and has a settings page, where does the network admin settings page reside?
			                             // should be relative to /wp-admin/; if plugin's settings resides on the BP_ROOT_BLOG only, mark this as 'root-blog-only'
			'network'          => true   // should the plugin be activated network-wide? not used at the moment
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
				self::$plugins[$type][$plugin_name]['admin_settings']   = $admin_settings;
				self::$plugins[$type][$plugin_name]['network_settings'] = $network_settings;

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
	public static function get_plugins( $type = 'all', $omit_type = false ) {
		// if type is 'all', we want all CBox plugins regardless of type
		if ( $type == 'all' ) {
			$plugins = self::$plugins;

			// okay, I lied, we want all plugins except dependencies!
			unset( $plugins['dependency'] );

			if ( ! empty( $omit_type ) )
				unset( $plugins[$omit_type] );

			// flatten associative array
			return call_user_func_array( 'array_merge', $plugins );
		}

		if ( empty( self::$plugins[$type] ) )
			return false;

		return self::$plugins[$type];
	}

	/**
	 * Organize plugins by state.
	 *
	 * @param Array of plugins.
	 * @return Associative array with plugin state as array key
	 * @since 0.3
	 */
	public static function organize_plugins_by_state( $plugins ) {
		$organized_plugins = array();

		foreach ( $plugins as $plugin => $data ) {
			// attempt to get the plugin loader file
			$loader = Plugin_Dependencies::get_pluginloader_by_name( $plugin );

			// get the required plugin's state
			$state  = self::get_plugin_state( $loader, $data );

			$organized_plugins[$state][] = esc_attr( $plugin );
		}

		return $organized_plugins;
	}

	/**
	 * Get settings links for our installed CBox plugins.
	 *
	 * @return Assosicate array with CBox plugin name as key and admin settings URL as the value.
	 * @since 0.3
	 */
	public static function get_settings() {
		// get all installed cbox plugins
		$cbox_plugins = self::get_plugins();

		// get active CBox plugins
		$active = self::organize_plugins_by_state( $cbox_plugins );

		if ( empty( $active ) )
			return false;

		$active = $active['deactivate'];

		$settings = array();

		foreach ( $active as $plugin ) {
			// network CBox install and CBox plugin has a network settings page
			if ( is_network_admin() && ! empty( $cbox_plugins[$plugin]['network_settings'] ) ) {
				// if network plugin's settings resides on the root blog,
				// then make sure we use the root blog's domain to generate the admin settings URL
				if ( $cbox_plugins[$plugin]['network_settings'] == 'root-blog-only' ) {
					// sanity check!
					// make sure BP is active so we can use bp_core_get_root_domain()
					if ( in_array( 'BuddyPress', $active ) ) {
						$settings[$plugin] = bp_core_get_root_domain() . '/wp-admin/' . $cbox_plugins[$plugin]['admin_settings'];
					}
				}
				// if the network plugin resides in the network area, use network_admin_url()!
				else {
					$settings[$plugin] = network_admin_url( $cbox_plugins[$plugin]['network_settings'] );
				}
			}

			// single-site CBox install and CBox plugin has an admin settings page
			elseif( ! is_network_admin() && ! empty( $cbox_plugins[$plugin]['admin_settings'] ) ) {
				$settings[$plugin] = admin_url( $cbox_plugins[$plugin]['admin_settings'] );
			}

		}

		return $settings;
	}

	/**
	 * Get plugins that require upgrades.
	 *
	 * @param string $type The type of plugins to get upgrades for. Either 'all' or 'active'.
	 * @return array of CBox plugin names that require upgrading
	 * @since 0.3
	 */
	public static function get_upgrades( $type = 'all' ) {
		// get all CBox plugins that require upgrades
		$upgrades = self::organize_plugins_by_state( self::get_plugins() );

		if ( empty( $upgrades['upgrade'] ) )
			return false;

		$upgrades = $upgrades['upgrade'];

		switch ( $type ) {
			case 'all' :
				return $upgrades;

				break;

			case 'active' :
				// get all active plugins
				$active_plugins = array_flip( Plugin_Dependencies::$active_plugins );

				$plugins = array();

				foreach ( $upgrades as $plugin ) {
					// attempt to get the plugin loader file
					$loader = Plugin_Dependencies::get_pluginloader_by_name( $plugin );

					// if the plugin is active, add it to our plugin array
					if ( ! empty( $active_plugins[$loader] ) )
						$plugins[] = $plugin;
				}

				if ( empty( $plugins ) )
					return false;

				return $plugins;

				break;
		}

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
			// try and see if our required plugin is installed
			$loader = ! empty( $plugins_by_name[ $plugin ] ) ? $plugins_by_name[ $plugin ] : false;

			// if our cbox plugin is found, get rid of it
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

			// if our cbox plugin is found, get rid of it
			if ( ! empty( $plugins->response[ $plugin_loader ] ) ) {
				unset( $plugins->response[ $plugin_loader ] );
				++$i;
			}
		}

		/*
		// @todo If we want the "Dashboard > Updates" count to be accurate,
		// we should resave the 'update_plugins' transient
		if ( $i > 0 ) {
			set_site_transient( 'update_plugins', $plugins );
		}
		*/

		return $plugins;
	}

	/** ADMIN-SPECIFIC ************************************************/

	/**
	 * Setup CBox's plugin menu item.
	 *
	 * The "Plugins" menu item only appears once CBox is completely setup.
	 *
	 * @uses cbox_is_setup() To see if CBox is completely setup.
	 * @since 0.3
	 */
	public function setup_plugins_page() {
		// see if cbox is fully setup
		if ( cbox_is_setup() ) {
			// add our plugins page
			$plugin_page = add_submenu_page(
				'cbox',
				__( 'Commons in a Box Plugins', 'cbox' ),
				__( 'Plugins', 'cbox' ),
				'install_plugins', // todo - map cap?
				'cbox-plugins',
				array( $this, 'admin_page' )
			);

			// validate any settings changes submitted from the Cbox plugins page
			add_action( "load-{$plugin_page}",       array( $this, 'validate_cbox_dashboard' ) );

			// load Plugin Dependencies plugin on the Cbox plugins page
			add_action( "load-{$plugin_page}",       array( 'Plugin_Dependencies', 'init' ) );

			// inline CSS
			add_action( "admin_head-{$plugin_page}", array( 'CBox_Admin', 'inline_css' ) );
			add_action( "admin_head-{$plugin_page}", array( $this, 'inline_css' ) );
		}
	}

	/**
	 * Before the Cbox plugins page is rendered, do any validation and checks
	 * from form submissions or action links.
	 *
	 * @since 0.2
	 */
	public function validate_cbox_dashboard() {
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
					if ( self::is_plugin_active( $plugin ) ) {
						wp_redirect( self_admin_url("admin.php?page=cbox") );
						exit;
					}
					else {
						$set_transient = is_network_admin() ? 'set_site_transient' : 'set_transient';

						$deactivated = call_user_func( array( 'Plugin_Dependencies', "deactivate_cascade" ), (array) $plugin );
						$set_transient( "pd_deactivate_cascade", $deactivated );

						// now deactivate the plugin
						deactivate_plugins( $plugin, false, is_network_admin() );

						if ( ! is_network_admin() )
							update_option('recently_activated', array($plugin => time()) + (array)get_option('recently_activated'));

						wp_redirect( self_admin_url("admin.php?page=cbox-plugins&deactivate=true") );
						exit;
					}

					break;
			}
		}

		// admin notices
		if ( ! empty( $_REQUEST['deactivate'] ) ) {
			// add an admin notice
			$prefix = is_network_admin() ? 'network_' : '';
			add_action( $prefix . 'admin_notices', create_function( '', "
				echo '<div class=\'updated\'><p>' . __( 'Plugin deactivated.', 'cbox' ) . '</p></div>';
			" ) );

			// if PD deactivated any other dependent plugins, show admin notice here
			// basically a copy-n-paste of Plugin_Dependencies::generate_dep_list()

			$get_transient = is_network_admin() ? 'get_site_transient' : 'get_transient';
			$deactivated = $get_transient( "pd_deactivate_cascade" );

			$delete_transient = is_network_admin() ? 'delete_site_transient' : 'delete_transient';
			$delete_transient( "pd_deactivate_cascade" );

			// if no other plugins were deactivated, stop now!
			if ( empty( $deactivated ) )
				return;

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
			add_action( $prefix . 'admin_notices', create_function( '', "
				echo
				html( 'div', array( 'class' => 'updated' ),
					html( 'p', '$text', html( 'ul', array( 'class' => 'dep-list' ), '$dep_list' ) )
				);
			" ) );
		}
	}

	/**
	 * Renders the CBox plugins page.
	 *
	 * @since 0.3
	 */
	public function admin_page() {
		// show this page during update
		if ( self::is_update() ) {
			$this->update_screen();
		}

		// if upgrade process is finished, show upgrade screen
		else {
	?>
			<div class="wrap">
				<?php screen_icon( 'plugins' ); ?>
				<h2><?php _e( 'Commons in a Box Plugins', 'cbox' ); ?></h2>

				<form method="post" action="<?php echo self_admin_url( 'admin.php?page=cbox-plugins' ); ?>">
					<div class="welcome-panel">
						<h2><?php _e( 'Required Plugins', 'cbox' ); ?></h2>

						<p><?php _e( 'Commons in a Box requires the following plugins for use with your WordPress site.', 'cbox' ); ?></p>

						<?php $this->render_plugin_table(); ?>
					</div>

					<div class="welcome-panel">
						<h2><?php _e( 'Recommended Plugins', 'cbox' ); ?></h2>

						<p><?php _e( "The following are plugins we automatically install for you during initial Commons in a Box setup.  We like them, but feel free to deactivate them if you don't need certain functionality.", 'cbox' ); ?></p>

						<?php $this->render_plugin_table( 'type=recommended' ); ?>
					</div>

					<div class="welcome-panel">
						<h2><?php _e( '&Agrave; la carte!', 'cbox' ); ?></h2>

						<p><?php _e( "The following are plugins we do not automatically install for you because they might require a bit more setup than the usual plugins.", 'cbox' ); ?></p>
						<p><?php _e( "However, we have tested these plugins and they're cool in our books as well!", 'cbox' ); ?></p>

						<?php $this->render_plugin_table( 'type=optional' ); ?>
					</div>

					<?php wp_nonce_field( 'cbox_update' ); ?>
				</form>
			</div>
	<?php
		}
	}

	/**
	 * Are we updating?
	 *
	 * @see CBox_Plugins::validate_cbox_dashboard()
	 * @return bool
	 */
	public static function is_update() {
		if ( ! empty( cbox()->update ) )
			return true;

		return false;
	}

	/**
	 * Screen that shows during an update.
	 *
	 * @since 0.2
	 */
	private function update_screen() {

		// if we're not in the middle of an update, stop now!
		if ( empty( cbox()->update ) )
			return;

		$plugins = $_REQUEST['cbox_plugins'];

		// include the CBox Plugin Upgrade and Install API
		if ( ! class_exists( 'CBox_Plugin_Upgrader' ) )
			require( CBOX_PLUGIN_DIR . 'admin/plugin-install.php' );

		// some HTML markup!
		echo '<div class="wrap">';
		screen_icon('plugins');
		echo '<h2>' . esc_html__('Update CBox', 'cbox' ) . '</h2>';

		// start the upgrade!
		$installer = new CBox_Updater( $plugins );

		echo '</div>';
	}

	/**
	 * Inline CSS used on the CBox plugins page.
	 *
	 * @since 0.3
	 */
	public function inline_css() {
	?>
		<style type="text/css">
			.welcome-panel {border-top:0; margin-top:0; padding:15px 10px 20px;}

			tr.cbox-plugin-row-active th, tr.cbox-plugin-row-active td {background-color:#fff;}
			tr.cbox-plugin-row-action-required th, tr.cbox-plugin-row-action-required td {background-color:#F4F4F4;}
		</style>
	<?php
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
	 * @since 0.2
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
	 * @param string $loader Plugin loader filename.
	 * @return bool
	 * @since 0.2
	 */
	public static function is_plugin_active( $loader ) {
		$active_plugins = (array) Plugin_Dependencies::$active_plugins;

		return in_array( $loader, $active_plugins );
	}

	/**
	 * Helper method to get the Cbox required plugin's state.
	 *
	 * @param str $loader The required plugin's loader filename
 	 * @param array $data The required plugin's data. See $this->register_required_plugins().
 	 * @since 0.2
	 */
	public static function get_plugin_state( $loader, $data ) {
		$state = false;

		// plugin exists
		if ( $loader ) {
			// if plugin is active, set state to 'deactivate'
			if ( self::is_plugin_active( $loader ) )
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
	 * @since 0.2
	 */
	private function deactivate_link( $loader ) {
		echo self_admin_url( 'admin.php?page=cbox-plugins&amp;cbox-action=deactivate&amp;plugin=' . urlencode( $loader ) . '&amp;_wpnonce=' . wp_create_nonce( 'deactivate-plugin_' . $loader ) );
	}

	/**
	 * Renders a plugin table for CBox's plugins.
	 *
	 * @param mixed $args Querystring or array of parameters. See inline doc for more details.
	 * @since 0.3
	 */
	public function render_plugin_table( $args = '' ) {
		$defaults = array(
			'type'           => 'required', // 'required' (default), 'recommended', 'optional', 'dependency'
			'omit_activated' => false,      // if set to true, this omits activated plugins from showing up in the plugin table
			'check_all'      => false,      // if set to true, this will mark all the checkboxes in the plugin table as checked
		);

		$r = wp_parse_args( $args, $defaults );
		extract( $r );

		// get unfulfilled requirements for all plugins
		//$requirements = Plugin_Dependencies::get_requirements();
	?>

		<table class="widefat fixed plugins">
			<thead>
				<tr>
					<th scope="col" class="manage-column check-column"><input type="checkbox" id="plugins-select-all" /></th>
					<th scope="col" id="<?php _e( $type ); ?>-name" class="manage-column column-name column-cbox-plugin-name"><?php _e( 'Plugin', 'cbox' ); ?></th>
					<th scope="col" id="<?php _e( $type ); ?>-description" class="manage-column column-description"><?php _e( 'Description', 'cbox' ); ?></th>
				</tr>
			</thead>

			<tfoot>
				<tr>
					<th scope="col" class="manage-column check-column"><input type="checkbox" id="plugins-select-all-2" /></th>
					<th scope="col" class="manage-column column-name column-cbox-plugin-name"><?php _e( 'Plugin', 'cbox' ); ?></th>
					<th scope="col" class="manage-column column-description"><?php _e( 'Description', 'cbox' ); ?></th>
				</tr>
			</tfoot>

			<tbody>

			<?php
				foreach ( self::get_plugins( $type ) as $plugin => $data ) :
					// attempt to get the plugin loader file
					$loader = Plugin_Dependencies::get_pluginloader_by_name( $plugin );
					$settings = self::get_settings();

					// get the required plugin's state
					$state  = self::get_plugin_state( $loader, $data );

					if ( $omit_activated && $state == 'deactivate' )
						continue;
			?>
				<tr class="cbox-plugin-row-<?php echo $state == 'deactivate' ? 'active' : 'action-required'; ?>">
					<th scope='row' class='check-column'>
						<?php if ( $state != 'deactivate' ) : ?>
							<input title="<?php esc_attr_e( 'Check this box to install the plugin.', 'cbox' ); ?>" type="checkbox" id="cbox_plugins_<?php echo sanitize_key( $plugin ); ?>" name="cbox_plugins[<?php echo $state; ?>][]" value="<?php echo esc_attr( $plugin ); ?>" <?php checked( $check_all ); ?>/>
						<?php else : ?>
							<img src="<?php echo admin_url( 'images/yes.png' ); ?>" alt="" title="<?php esc_attr_e( 'Plugin is already active!', 'cbox' ); ?>" style="margin-left:7px;" />
						<?php endif; ?>
					</th>

					<td class="plugin-title">
						<?php if ( $state != 'deactivate' ) : ?>
							<label for="cbox_plugins_<?php echo sanitize_key( $plugin ); ?>">
						<?php endif; ?>

						<strong><?php echo $data['cbox_name']; ?></strong>

						<?php if ( $state != 'deactivate' ) : ?>
							</label>
						<?php endif; ?>

						<div class="row-actions-visible">
						<?php if ( ! empty( $settings[$plugin] ) ) : ?>
							<a href="<?php echo $settings[$plugin]; ?>"><?php _e( 'Settings', 'cbox' ); ?></a>

							<?php if ( $type != 'required' || $this->is_override() ) : ?>|<?php endif; ?>
						<?php endif; ?>

						<?php if ( $state == 'deactivate' ) : if ( $type != 'required' || $this->is_override() ) : ?>
							<a href="<?php $this->deactivate_link( $loader ); ?>"><?php _e( 'Deactivate', 'cbox' ); ?></a>
						<?php endif; elseif ( $state == 'upgrade' ) : ?>
							<div class="plugin-update-tr"><p class="update-message"><?php _e( 'Update available.', 'cbox' ); ?></p></div>
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

										if ( $dep_loader && self::is_plugin_active( $dep_loader ) )
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

		<p><input type="submit" value="<?php _e( 'Update', 'cbox' ); ?>" class="button-primary" id="cbox-update-<?php echo esc_attr( $type ); ?>" name="cbox-update" /></p>
	<?php
	}

}
