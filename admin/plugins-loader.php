<?php
/**
 * Set up plugin management
 *
 * @since 0.1
 *
 * @package Commons_In_A_Box
 * @subpackage Plugins
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
		// setup the CBOX plugin menu
		add_action( 'cbox_admin_menu',                       array( $this, 'setup_plugins_page' ) );

		// load PD on the "Dashboard > Updates" page so we can filter out our CBOX plugins
		add_action( 'load-update-core.php',                  array( 'Plugin_Dependencies', 'init' ) );

		// filter PD's dependency list
		add_filter( 'scr_plugin_dependency_before_parse',    array( $this, 'filter_pd_dependencies' ) );

		// Make sure BuddyPress is installed with all components enabled
		add_filter( 'bp_new_install_default_components',     array( $this, 'bp_default_components' ) );

		// prevent CBOX plugins from being seen in the regular Plugins table and from WP updates
		if ( ! $this->is_override() ) {
			// exclude CBOX plugins from the "Plugins" list table
			add_filter( 'all_plugins',                   array( $this, 'exclude_cbox_plugins' ) );

			// remove CBOX plugins from WP's update plugins routine
			add_filter( 'site_transient_update_plugins', array( $this, 'remove_cbox_plugins_from_updates' ) );

			// do not show PD's pre-activation warnings if admin cannot override CBOX plugins
			add_filter( 'pd_show_preactivation_warnings', '__return_false' );
		}
	}

	/**
	 * For expert site managers, we allow them to view CBOX plugins in the
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
	 * CBOX requires several popular plugins.
	 *
	 * We register these plugins here for later use.
	 *
	 * @see CBox_Plugins::register_plugin()
	 */
	private function register_required_plugins() {

		// BuddyPress
		self::register_plugin( array(
			'plugin_name'       => 'BuddyPress',
			'cbox_name'         => __( 'BuddyPress', 'cbox' ),
			'cbox_description'  => __( 'BuddyPress provides the core functionality of Commons In A Box, including groups and user profiles.', 'cbox' ),
			'version'           => '2.4.3',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-plugin',
			'admin_settings'    => 'options-general.php?page=bp-components',
			'network_settings'  => 'settings.php?page=bp-components'
		) );
	}

	/**
	 * CBOX recommends several popular plugins.
	 *
	 * We register these plugins here for later use.
	 *
	 * @see CBox_Plugins::register_plugin()
	 */
	private function register_recommended_plugins() {

		// BuddyPress Docs
		self::register_plugin( array(
			'plugin_name'       => 'BuddyPress Docs',
			'type'              => 'recommended',
			'cbox_name'         => __( 'Docs', 'cbox' ),
			'cbox_description'  => __( 'Allows your members to collaborate on wiki-style Docs.', 'cbox' ),
			'version'           => '1.8.8',
			'depends'           => 'BuddyPress (>=1.5)',
			'download_url'      => 'http://downloads.wordpress.org/plugin/buddypress-docs.1.8.8.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-docs',
			'admin_settings'    => 'edit.php?post_type=bp_doc',
			'network_settings'  => 'root-blog-only'
		) );

		// BuddyPress Docs Wiki
		self::register_plugin( array(
			'plugin_name'       => 'BuddyPress Docs Wiki add-on',
			'type'              => 'recommended',
			'cbox_name'         => __( 'Wiki', 'cbox' ),
			'cbox_description'  => __( 'A sitewide wiki, powered by BuddyPress Docs', 'cbox' ),
			'version'           => '1.0.9',
			'depends'           => 'BuddyPress (>=1.5), BuddyPress Docs (>=1.2)',
			'download_url'      => CBOX_PLUGIN_DIR . 'includes/zip/buddypress-docs-wiki-1.0.9.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-docs-wiki',
			'network_settings'  => 'root-blog-only'
		) );

		// BuddyPress Group Email Subscription
		self::register_plugin( array(
			'plugin_name'       => 'BuddyPress Group Email Subscription',
			'type'              => 'recommended',
			'cbox_name'         => __( 'Group Email Subscription', 'cbox' ),
			'cbox_description'  => __( 'Allows your community members to receive email notifications of activity within their groups.', 'cbox' ),
			'depends'           => 'BuddyPress (>=1.5)',
			'version'           => '3.6.0',
			'download_url'      => 'http://downloads.wordpress.org/plugin/buddypress-group-email-subscription.3.6.0.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-group-email-subscription',
			'admin_settings'    => 'admin.php?page=ass_admin_options', // this doesn't work for BP_ENABLE_MULTIBLOG
			'network_settings'  => 'admin.php?page=ass_admin_options'
		) );

		// Invite Anyone
		self::register_plugin( array(
			'plugin_name'       => 'Invite Anyone',
			'type'              => 'recommended',
			'cbox_name'         => __( 'Invite Anyone', 'cbox' ),
			'cbox_description'  => __( 'An enhanced interface for inviting existing community members to groups, as well as a powerful tool for sending invitations, via email, to potential members.', 'cbox' ),
			'version'           => '1.3.9',
			'depends'           => 'BuddyPress (>=1.5)',
			'download_url'      => 'http://downloads.wordpress.org/plugin/invite-anyone.1.3.9.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/invite-anyone',
			'admin_settings'    => 'admin.php?page=invite-anyone',
			'network_settings'  => 'admin.php?page=invite-anyone'
		) );

		// Custom Profile Filters for BuddyPress
		self::register_plugin( array(
			'plugin_name'       => 'Custom Profile Filters for BuddyPress',
			'type'              => 'recommended',
			'cbox_name'         => __( 'Custom Profile Filters', 'cbox' ),
			'cbox_description'  => __( 'Let your members specify what profile content they\'d like to become search links, by wrapping the content in brackets.', 'cbox' ),
			'depends'           => 'BuddyPress (>=1.2)',
			'version'           => '0.3.1',
			'download_url'      => 'http://downloads.wordpress.org/plugin/custom-profile-filters-for-buddypress.0.3.1.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/custom-profile-filters-for-buddypress',
		) );

		// bbPress
		// @todo if network-activated, this is enabled across all sub-sites... need to limit this
		self::register_plugin( array(
			'plugin_name'       => 'bbPress',
			'type'              => 'recommended',
			'cbox_name'         => __( 'bbPress Forums', 'cbox' ),
			'cbox_description'  => __( 'Sitewide and group-specific discussion forums.', 'cbox' ),
			'version'           => '2.5.8',
			'download_url'      => 'http://downloads.wordpress.org/plugin/bbpress.2.5.8.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/bbpress',
			'admin_settings'    => 'options-general.php?page=bbpress',
			'network_settings'  => 'root-blog-only'
		) );

		// CAC Featured Content
		self::register_plugin( array(
			'plugin_name'       => 'CAC Featured Content',
			'type'              => 'recommended',
			'cbox_name'         => __( 'Featured Content Widget', 'cbox' ),
			'cbox_description'  => __( 'Provides a widget that allows you to select among five different content types to feature in a widget area.', 'cbox' ),
			'version'           => '1.0.6',
			'download_url'      => 'http://downloads.wordpress.org/plugin/cac-featured-content.1.0.6.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/cac-featured-content',
		) );

		// BuddyPress Group Email Subscription
		self::register_plugin( array(
			'plugin_name'       => 'BP Group Announcements',
			'type'              => 'recommended',
			'cbox_name'         => __( 'Group Announcements', 'cbox' ),
			'cbox_description'  => __( 'Repurposes group activity updates, using an Announcements tab to groups.', 'cbox' ),
			'depends'           => 'BuddyPress (>=1.5)',
			'version'           => '1.0.5',
			'download_url'      => CBOX_PLUGIN_DIR . 'includes/zip/bp-group-announcements-1.0.5.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/bp-group-announcements',
		) );

		// only show the following plugins in network mode
		if ( is_network_admin() ) :
			// More Privacy Options
			self::register_plugin( array(
				'plugin_name'       => 'More Privacy Options',
				'type'              => 'recommended',
				'cbox_name'         => __( 'More Privacy Options', 'cbox' ),
				'cbox_description'  => __( 'Adds more blog privacy options for your users.', 'cbox' ),
				'version'           => '4.1.1',
				'download_url'      => 'http://downloads.wordpress.org/plugin/more-privacy-options.zip',
				'documentation_url' => 'http://commonsinabox.org/documentation/plugins/more-privacy-options',
				'network_settings'  => 'settings.php#menu'
			) );

			// BP MPO Activity Filter
			self::register_plugin( array(
				'plugin_name'       => 'BP MPO Activity Filter',
				'type'              => 'recommended',
				'cbox_name'         => __( 'Activity Privacy', 'cbox' ),
				'cbox_description'  => __( 'Works with More Privacy Options to keep private blog content out of public activity feeds.', 'cbox' ),
				'version'           => '1.2',
				'download_url'      => 'http://downloads.wordpress.org/plugin/bp-mpo-activity-filter.1.2.zip',
				'documentation_url' => 'http://commonsinabox.org/documentation/plugins/bp-mpo-activity-filter',
			) );

			// BuddyPress GroupBlog
			self::register_plugin( array(
				'plugin_name'       => 'BP Groupblog',
				'type'              => 'recommended',
				'cbox_name'         => __( 'Group Blogs', 'cbox' ),
				'cbox_description'  => 'Enables a BuddyPress group to be associated with a blog, by placing a Blog link in the group navigation and, optionally, syncing group membership with blog roles.',
				'depends'           => 'BuddyPress (>=1.6)',
				'version'           => '1.8.12',
				'download_url'      => 'http://downloads.wordpress.org/plugin/bp-groupblog.1.8.12.zip',
				'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-groupblog',
				'network_settings'  => 'settings.php?page=bp_groupblog_management_page'
			) );

		endif;


		// Other plugins for later:
		// BP Group Documents (whatever replacement version we've got by that time)
		// Forum Attachments (ditto);
	}

	/**
	 * CBOX also lists a few other plugins that can be used to extend functionality.
	 *
	 * Although, they are slightly below the recommended level, we've tested these
	 * plugins and think they're cool enough to be a part of CBOX.
	 *
	 * We register these plugins here for later use.
	 *
	 * @see CBox_Plugins::register_plugin()
	 */
	private function register_optional_plugins() {

		// BuddyPress External Group Blogs
		self::register_plugin( array(
			'plugin_name'       => 'External Group Blogs',
			'type'              => 'optional',
			'cbox_name'         => __( 'External RSS Feeds for Groups', 'cbox' ),
			'cbox_description'  => __( 'Gives group creators and administrators the ability to attach external RSS feeds to groups.', 'cbox' ),
			'depends'           => 'BuddyPress (>=1.5)',
			'version'           => '1.6.0',
			'download_url'      => CBOX_PLUGIN_DIR . 'includes/zip/external-group-blogs-1.6.0.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-external-group-rss',
		) );

		// BuddyPress Reply By Email
		// @todo Still need to add it in the wp.org plugin repo! Using Github for now.
		self::register_plugin( array(
			'plugin_name'       => 'BuddyPress Reply By Email',
			'type'              => 'optional',
			'cbox_name'         => __( 'Reply By Email', 'cbox' ),
			'cbox_description'  => __( "Reply to content from all over the community from the comfort of your email inbox", 'cbox' ),
			'version'           => '1.0-RC3',
			'depends'           => 'BuddyPress (>=1.5)',
			'download_url'      => CBOX_PLUGIN_DIR . 'includes/zip/bp-reply-by-email-1.0-RC3.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-reply-by-email',
			'admin_settings'    => is_multisite() ? 'options-general.php?page=bp-rbe' : 'admin.php?page=bp-rbe',
			'network_settings'  => 'root-blog-only'
		) );

		// WP Better Emails
		/*
		self::register_plugin( array(
			'plugin_name'      => 'WP Better Emails',
			'type'             => 'optional',
			'cbox_name'        => __( 'HTML Email' ),
			'cbox_description' => __( 'Enable and design HTML emails', 'cbox' ),
			'version'          => '0.2.4',
			'download_url'     => 'http://downloads.wordpress.org/plugin/wp-better-emails.0.2.4.zip'
		) );
		*/
	}

	/**
	 * Register CBOX's dependency plugins internally.
	 *
	 * The reason why this is done is Plugin Dependencies (PD) does not know the download URL for dependent plugins.
	 * So if a dependent plugin is deemed incompatible by PD (either not installed or incompatible version),
	 * we can easily install or upgrade that plugin.
	 *
	 * This is designed to avoid pinging the WP.org Plugin Repo API multiple times to grab the download URL,
	 * and is much more efficient for our usage.
	 *
	 * @see CBox_Plugins::register_plugin()
	 */
	private function register_dependency_plugins() {

		// BuddyPress
		self::register_plugin( array(
			'plugin_name'  => 'BuddyPress',
			'type'         => 'dependency',
			'download_url' => 'http://downloads.wordpress.org/plugin/buddypress.2.4.3.zip'
		) );
	}

	/**
	 * Register a plugin in CBOX.
	 *
	 * Updates our private, static $plugins variable in the process.
	 *
	 * @see CBox_Plugins::register_required_plugins()
	 * @see CBox_Plugins::register_recommended_plugins()
	 * @see CBox_Plugins::register_optional_plugins()
	 * @see CBox_Plugins::register_dependency_plugins()
	 */
	private function register_plugin( $args = '' ) {
		$defaults = array(
			'plugin_name'       => false, // (required) the name of the plugin as in the plugin header
			'type'              => 'required', // types include 'required', 'recommended', 'optional', dependency'
			'cbox_name'         => false, // CBOX's label for the plugin
			'cbox_description'  => false, // CBOX's short description of the plugin
			'depends'           => false, // our own defined dependencies for the plugin; uses same syntax as PD
			'version'           => false, // the version number of the plugin we want to compare the installed version with (if applicable)
			'download_url'      => false, // the download URL of the plugin used if the active version is not compatible with our version
			'documentation_url' => false, // the documentation URL for the plugin
			'admin_settings'    => false, // if applicable, where does the admin settings page reside? should be relative to /wp-admin/
			'network_settings'  => false, // if plugin is network-only and has a settings page, where does the network admin settings page reside?
			                              // should be relative to /wp-admin/; if plugin's settings resides on the BP_ROOT_BLOG only, mark this as 'root-blog-only'
			'network'           => true   // should the plugin be activated network-wide? not used at the moment
		);

		$r = wp_parse_args( $args, $defaults );

		if ( empty( $r['plugin_name'] ) )
			return false;

		switch( $r['type'] ) {
			case 'required' :
			case 'recommended' :
			case 'optional' :
				self::$plugins[ $r['type'] ][ $r['plugin_name'] ]['cbox_name']         = $r['cbox_name'];
				self::$plugins[ $r['type'] ][ $r['plugin_name'] ]['cbox_description']  = $r['cbox_description'];
				self::$plugins[ $r['type'] ][ $r['plugin_name'] ]['depends']           = $r['depends'];
				self::$plugins[ $r['type'] ][ $r['plugin_name'] ]['version']           = $r['version'];
				self::$plugins[ $r['type'] ][ $r['plugin_name'] ]['download_url']      = $r['download_url'];
				self::$plugins[ $r['type'] ][ $r['plugin_name'] ]['documentation_url'] = $r['documentation_url'];
				self::$plugins[ $r['type'] ][ $r['plugin_name'] ]['admin_settings']    = $r['admin_settings'];
				self::$plugins[ $r['type'] ][ $r['plugin_name'] ]['network_settings']  = $r['network_settings'];

				break;

			case 'dependency' :
				self::$plugins[ $r['type'] ][ $r['plugin_name'] ]['download_url']     = $r['download_url'];

				break;
		}

	}

	/**
	 * Helper method to grab all CBOX plugins of a certain type.
	 *
	 * @param string $type Type of CBOX plugin. Either 'all', 'required', 'recommended', 'optional', 'dependency'.
	 * @param string $omit_type The type of CBOX plugin to omit from returning
	 * @return mixed Array of plugins on success. Boolean false on failure.
	 */
	public static function get_plugins( $type = 'all', $omit_type = false ) {
		// if type is 'all', we want all CBOX plugins regardless of type
		if ( $type == 'all' ) {
			$plugins = self::$plugins;

			// okay, I lied, we want all plugins except dependencies!
			unset( $plugins['dependency'] );

			// if $omit_type was passed, use it to remove the plugin type we don't want
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
	 * @since 0.3
	 *
	 * @return Associative array with plugin state as array key
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
	 * Get settings links for our installed CBOX plugins.
	 *
	 * @since 0.3
	 *
	 * @return Assosicate array with CBOX plugin name as key and admin settings URL as the value.
	 */
	public static function get_settings() {
		// get all installed CBOX plugins
		$cbox_plugins = self::get_plugins();

		// get active CBOX plugins
		$active = self::organize_plugins_by_state( $cbox_plugins );

		if ( empty( $active ) )
			return false;

		$active = $active['deactivate'];

		$settings = array();

		foreach ( $active as $plugin ) {
			// network CBOX install and CBOX plugin has a network settings page
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

			// single-site CBOX install and CBOX plugin has an admin settings page
			elseif( ! is_network_admin() && ! empty( $cbox_plugins[$plugin]['admin_settings'] ) ) {
				$settings[$plugin] = admin_url( $cbox_plugins[$plugin]['admin_settings'] );
			}

		}

		return $settings;
	}

	/**
	 * Get plugins that require upgrades.
	 *
	 * @since 0.3
	 *
	 * @param string $type The type of plugins to get upgrades for. Either 'all' or 'active'.
	 * @return array of CBOX plugin names that require upgrading
	 */
	public static function get_upgrades( $type = 'all' ) {
		// get all CBOX plugins that require upgrades
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
					if ( isset( $active_plugins[$loader] ) )
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
	 * Exclude CBOX's plugins from the "Plugins" list table.
	 *
	 * @return array
	 */
	public function exclude_cbox_plugins( $plugins ) {
		$plugins_by_name = Plugin_Dependencies::$plugins_by_name;

		foreach( self::get_plugins() as $plugin => $data ) {
			// try and see if our required plugin is installed
			$loader = ! empty( $plugins_by_name[ $plugin ] ) ? $plugins_by_name[ $plugin ] : false;

			// if our CBOX plugin is found, get rid of it
			if( ! empty( $loader ) && ! empty( $plugins[ $loader ] ) )
				unset( $plugins[ $loader ] );
		}

		return $plugins;
	}


	/**
	 * CBOX plugins should be removed from WP's update plugins routine.
	 */
	public function remove_cbox_plugins_from_updates( $plugins ) {
		$i = 0;

		foreach ( self::get_plugins() as $plugin => $data ) {
			// get the plugin loader file
			$plugin_loader = Plugin_Dependencies::get_pluginloader_by_name( $plugin );

			// if our CBOX plugin is found, get rid of it
			if ( ! empty( $plugins->response[ $plugin_loader ] ) ) {
				unset( $plugins->response[ $plugin_loader ] );
				++$i;
			}
		}

		// update the "Dashboard > Updates" count to be accurate
		if ( $i > 0 ) {
			set_site_transient( 'update_plugins', $plugins );
		}

		return $plugins;
	}

	/**
	 * Return an array of all BP components to be activated by default
	 *
	 * Since BuddyPress 1.7, BP has only activated the Profile and Activity
	 * components on new installations. For Commons In A Box, we want to
	 * keep the old behavior of turning all components on.
	 *
	 * BuddyPress's filter 'bp_new_install_default_components' allows us to
	 * modify the standard BP behavior, but it's run on the admin pageload
	 * after BP is initially activated. That means that we have to add the
	 * filter here in the general plugin loader class.
	 *
	 * @since 1.0.2
	 */
	public function bp_default_components( $components ) {
		return array(
			'activity' => 1,
			'blogs'    => 1,
			'friends'  => 1,
			'groups'   => 1,
			'members'  => 1,
			'messages' => 1,
			'settings' => 1,
			'xprofile' => 1,
		);
	}

	/** ADMIN-SPECIFIC ************************************************/

	/**
	 * Setup CBOX's plugin menu item.
	 *
	 * The "Plugins" menu item only appears once CBOX is completely setup.
	 *
	 * @since 0.3
	 *
	 * @uses cbox_is_setup() To see if CBOX is completely setup.
	 */
	public function setup_plugins_page() {
		// see if CBOX is fully setup
		if ( cbox_is_setup() ) {
			// add our plugins page
			$plugin_page = add_submenu_page(
				'cbox',
				__( 'Commons In A Box Plugins', 'cbox' ),
				__( 'Plugins', 'cbox' ),
				'install_plugins', // todo - map cap?
				'cbox-plugins',
				array( $this, 'admin_page' )
			);

			// load Plugin Dependencies plugin on the CBOX plugins page
			add_action( "load-{$plugin_page}",       array( 'Plugin_Dependencies', 'init' ) );

			// validate any settings changes submitted from the CBOX plugins page
			add_action( "load-{$plugin_page}",       array( $this, 'validate_cbox_dashboard' ) );

			// inline CSS
			add_action( "admin_head-{$plugin_page}", array( 'CBox_Admin', 'dashboard_css' ) );
			add_action( "admin_head-{$plugin_page}", array( $this, 'inline_css' ) );
		}
	}

	/**
	 * Before the CBOX plugins page is rendered, do any validation and checks
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
			// if so, set a reference variable to note that CBOX is updating
			if ( ! empty( $_REQUEST['cbox_plugins'] ) ) {
				cbox()->update = true;
			}
		}

		// deactivate a single plugin from the CBOX dashboard
		// basically a copy and paste of the code available in /wp-admin/plugins.php
		if ( ! empty( $_REQUEST['cbox-action'] ) && ! empty( $_REQUEST['plugin'] ) ) {
			$plugin = $_REQUEST['plugin'];

			if ( ! current_user_can('activate_plugins') )
				wp_die(__('You do not have sufficient permissions to deactivate plugins for this site.'));

			switch( $_REQUEST['cbox-action'] ) {
				case 'deactivate' :
					check_admin_referer('deactivate-plugin_' . $plugin);

					// if plugin is already deactivated, redirect to CBOX dashboard and stop!
					if ( ! self::is_plugin_active( $plugin ) ) {
						wp_redirect( self_admin_url("admin.php?page=cbox") );
						exit;

					// start deactivating!
					} else {
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
			// not really a fan of the code below, but it's from Plugin Dependencies
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
	 * Renders the CBOX plugins page.
	 *
	 * @since 0.3
	 */
	public function admin_page() {
		// show this page during update
		if ( ! empty( cbox()->update ) ) {
			$this->update_screen();
		}

		// if upgrade process is finished, show regular plugins page
		else {
	?>
			<div class="wrap">
				<?php screen_icon( 'cbox' ); ?>

				<h2><?php _e( 'Commons In A Box Plugins', 'cbox' ); ?></h2>

				<form method="post" action="<?php echo self_admin_url( 'admin.php?page=cbox-plugins' ); ?>">
					<div id="required" class="cbox-plugins-section">
						<h2><?php _e( 'Required Plugins', 'cbox' ); ?></h2>

						<p><?php _e( 'Commons In A Box requires the following plugins.', 'cbox' ); ?></p>

						<?php $this->render_plugin_table(); ?>
					</div>

					<div id="recommended" class="cbox-plugins-section">
						<h2><?php _e( 'Recommended Plugins', 'cbox' ); ?></h2>

						<p><?php _e( "The following plugins are installed automatically during initial Commons In A Box setup.  We like them, but feel free to deactivate them if you don't need certain functionality.", 'cbox' ); ?></p>

						<?php $this->render_plugin_table( 'type=recommended' ); ?>
					</div>

					<div id="a-la-carte" class="cbox-plugins-section">
						<h2><?php _e( '&Agrave; la carte', 'cbox' ); ?></h2>

						<p><?php _e( "The following plugins work well with Commons In A Box, but they require a bit of additional setup, so we do not install them by default.", 'cbox' ); ?></p>
						<p><?php _e( "To install, check the plugins you want to install and click 'Update'.", 'cbox' ); ?></p>

						<?php $this->render_plugin_table( 'type=optional' ); ?>
					</div>

					<?php wp_nonce_field( 'cbox_update' ); ?>
				</form>
			</div>
	<?php
		}
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

		// include the CBOX Plugin Upgrade and Install API
		if ( ! class_exists( 'CBox_Plugin_Upgrader' ) )
			require( CBOX_PLUGIN_DIR . 'admin/plugin-install.php' );

		// some HTML markup!
		echo '<div class="wrap">';
		screen_icon( 'cbox' );
		echo '<h2>' . esc_html__('Update CBOX', 'cbox' ) . '</h2>';

		// start the upgrade!
		$installer = new CBox_Updater( $plugins );

		echo '</div>';
	}

	/**
	 * Inline CSS used on the CBOX plugins page.
	 *
	 * @since 0.3
	 */
	public function inline_css() {
	?>
		<style type="text/css">
			.dep-list li {list-style:disc; margin-left:1.5em;}
		</style>
	<?php
	}

	/** HELPERS *******************************************************/

	/**
	 * Helper method to parse a comma-delimited dependency string.
	 *
	 * eg. "BuddyPress (>=1.5), BuddyPress Docs, Invite Anyone"
	 *
	 * @since 0.2
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
	 * @since 0.2
	 *
	 * @param string $loader Plugin loader filename.
	 * @return bool
	 */
	public static function is_plugin_active( $loader ) {
		$active_plugins = (array) Plugin_Dependencies::$active_plugins;

		return in_array( $loader, $active_plugins );
	}

	/**
	 * Helper method to get the CBOX required plugin's state.
	 *
	 * @since 0.2
	 *
	 * @param str $loader The required plugin's loader filename
 	 * @param array $data The required plugin's data. See $this->register_required_plugins().
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
	 * Helper method to return the deactivation URL for a plugin on the CBOX
	 * plugins page.
	 *
	 * @since 0.2
	 *
	 * @param str $loader The plugin's loader filename
	 * @return str Deactivation link
	 */
	private function deactivate_link( $loader ) {
		return self_admin_url( 'admin.php?page=cbox-plugins&amp;cbox-action=deactivate&amp;plugin=' . urlencode( $loader ) . '&amp;_wpnonce=' . wp_create_nonce( 'deactivate-plugin_' . $loader ) );
	}

	/**
	 * Renders a plugin table for CBOX's plugins.
	 *
	 * @since 0.3
	 *
	 * @param mixed $args Querystring or array of parameters. See inline doc for more details.
	 */
	public function render_plugin_table( $args = '' ) {
		$defaults = array(
			'type'           => 'required', // 'required' (default), 'recommended', 'optional', 'dependency'
			'omit_activated' => false,      // if set to true, this omits activated plugins from showing up in the plugin table
			'check_all'      => false,      // if set to true, this will mark all the checkboxes in the plugin table as checked
		);

		$r = wp_parse_args( $args, $defaults );

		// get unfulfilled requirements for all plugins
		//$requirements = Plugin_Dependencies::get_requirements();
	?>

		<table class="widefat fixed plugins">
			<thead>
				<tr>
					<th scope="col" class="manage-column check-column"><input type="checkbox" id="plugins-select-all" /></th>
					<th scope="col" id="<?php _e( $r['type'] ); ?>-name" class="manage-column column-name column-cbox-plugin-name"><?php _e( 'Plugin', 'cbox' ); ?></th>
					<th scope="col" id="<?php _e( $r['type'] ); ?>-description" class="manage-column column-description"><?php _e( 'Description', 'cbox' ); ?></th>
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
				foreach ( self::get_plugins( $r['type'] ) as $plugin => $data ) :
					// attempt to get the plugin loader file
					$loader = Plugin_Dependencies::get_pluginloader_by_name( $plugin );
					$settings = self::get_settings();

					// get the required plugin's state
					$state  = self::get_plugin_state( $loader, $data );

					if ( $r['omit_activated'] && $state == 'deactivate' )
						continue;
			?>
				<tr id="<?php echo sanitize_title( $plugin ); ?>" class="cbox-plugin-row-<?php echo $state == 'deactivate' ? 'active' : 'action-required'; ?>">
					<th scope='row' class='check-column'>
						<?php if ( $state != 'deactivate' ) : ?>
							<input title="<?php esc_attr_e( 'Check this box to install the plugin.', 'cbox' ); ?>" type="checkbox" id="cbox_plugins_<?php echo sanitize_title( $plugin ); ?>" name="cbox_plugins[<?php echo $state; ?>][]" value="<?php echo esc_attr( $plugin ); ?>" <?php checked( $r['check_all'] ); ?>/>
						<?php else : ?>
							<img src="<?php echo admin_url( 'images/yes.png' ); ?>" alt="" title="<?php esc_attr_e( 'Plugin is already active!', 'cbox' ); ?>" style="margin-left:7px;" />
						<?php endif; ?>
					</th>

					<td class="plugin-title">
						<?php if ( $state != 'deactivate' ) : ?>
							<label for="cbox_plugins_<?php echo sanitize_title( $plugin ); ?>">
						<?php endif; ?>

						<strong><?php echo $data['cbox_name']; ?></strong>

						<?php if ( $state != 'deactivate' ) : ?>
							</label>
						<?php endif; ?>

						<!-- start - plugin row links -->
						<?php
							$plugin_row_links = array();

							// settings link
							if ( ! empty( $settings[ $plugin ] ) ) {
								$plugin_row_links[] = sprintf(
									'<a title="%s" href="%s">%s</a>',
									__( "Click here to view this plugin's settings page", 'cbox' ),
									$settings[ $plugin ],
									__( "Settings", 'cbox' )
								);
							}

							// info link
							if ( ! empty( $data['documentation_url'] ) && $state != 'upgrade' ) {
								$plugin_row_links[] = sprintf(
									'<a title="%s" href="%s" target="_blank">%s</a>',
									__( "Click here for documentation on this plugin, from commonsinabox.org", 'cbox' ),
									esc_url( $data['documentation_url'] ),
									__( "Info", 'cbox' )
								);
							}

							// deactivate link
							if ( $state == 'deactivate' ) {
								// If override is on, we must still not allow BuddyPress to be deactivated
								// through the CBOX plugins page. It can still be deactivated through the
								// regular WP plugins page though
								if ( $r['type'] != 'required' || ( $this->is_override() && $data['cbox_name'] != 'BuddyPress' ) ) {
									$plugin_row_links[] = sprintf(
										'<a title="%s" href="%s">%s</a>',
										__( "Deactivate this plugin.", 'cbox' ),
										$this->deactivate_link( $loader ),
										__( "Deactivate", 'cbox' )
									);
								}
							}
						?>

						<div class="row-actions-visible">
							<p><?php echo implode( ' | ', $plugin_row_links ); ?></p>

							<?php /* upgrade notice */ ?>
							<?php if ( $state == 'upgrade' ) : ?>
								<div class="plugin-update-tr"><p class="update-message" title="<?php _e( "Select the checkbox and click on 'Update' to upgrade this plugin.", 'cbox' ); ?>"><?php _e( 'Update available.', 'cbox' ); ?></p></div>
							<?php endif; ?>
						</div>
						<!-- end - plugin row links -->
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

		<p><input type="submit" value="<?php _e( 'Update', 'cbox' ); ?>" class="button-primary" id="cbox-update-<?php echo esc_attr( $r['type'] ); ?>" name="cbox-update" /></p>
	<?php
	}

}
