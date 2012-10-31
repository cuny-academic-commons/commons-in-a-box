<?php
/**
 * CBox's Theme Installer
 *
 * @package Commons_In_A_Box
 * @subpackage Themes
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// require the WP_Upgrader class so we can extend it!
require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );

/**
 * Sets up our CBox theme requirements.
 *
 * In this class, we setup our required specs for the CBox Default theme
 * and its parent theme, Infinity.
 *
 * Use the init() method to construct the class.
 *
 * Is chainable, so you can do something like this:
 *      // get infinity theme specs
 *      CBox_Theme_Specs::init()->get( 'infinity' );
 *
 *      // get our version for infinity'
 *      CBox_Theme_Specs::init()->get( 'infinity', 'version' );
 *
 * @package Commons_In_A_Box
 * @subpackage Themes
 */
class CBox_Theme_Specs {

	/**
	 * Setup our theme info.
	 *
	 * We offer the PressCrew-developed CBox Theme to be installed during CBox setup.
	 *
	 * @static
	 */
	private static $cbox_theme = array(
		'name'           => 'Commons In A Box Theme',
		'version'        => '1.0',
		'directory_name' => 'cbox-theme',

		// @todo need a tagged version... until then, we use the bleeding version
		'download_url' => 'https://github.com/cuny-academic-commons/cbox-theme/zipball/master'
	);

	/**
	 * Setup our parent theme info.
	 *
	 * The CBox theme uses the Infinity theme as a parent.
	 * This variable is referenced if Infinity isn't already installed.
	 *
	 * @static
	 */
	private static $infinity = array(
		'name'           => '&#8734; Infinity',
		'version'        => '1.1a',
		'directory_name' => 'infinity',

		// this is set to use the bleeding 'buddypress' branch to get the latest
		// Infinity bug fixes
		// @todo When 1.1 hits, use tagged version
		'download_url'   => 'https://github.com/PressCrew/infinity/zipball/buddypress'
	);

	/**
	 * Static bootstrapping init method.
	 */
	public static function &init() {
		return new self();
	}

	/**
	 * Fetch our theme info depending on the passed variables.
	 *
	 * @param str $theme The theme we want specs for. Only 'infinity', 'cbox_theme' will work.
	 * @param str $param The theme parameter we want to fetch. 'name', 'version', 'directory_name', 'download_url' will work.
	 * @return mixed Array of theme specs if $param isn't passed. String if $param is successfully passed.
	 */
	public function get( $theme_name, $param = '' ) {
		if ( empty( self::$$theme_name ) )
			return false;

		$theme = self::$$theme_name;

		if ( ! empty( $theme[$param] ) )
			return $theme[$param];

		return $theme;
	}


	/**
	 * Check to see if our CBox themes need to be upgraded.
	 *
	 * @param WP_Theme $current_theme The current running theme.
	 * @uses wp_get_theme() If not passed, will grab the current running theme.
	 * @uses CBox_Theme_Specs::init() To initialize our CBox theme specs
	 * @uses version_compare() To compare the current CBox theme with our internal specs.
	 * @return mixed String of what themes to update. Boolean false on failure.
	 */
	public static function get_upgrades( WP_Theme $current_theme = NULL ) {
		// get current theme if not passed
		if ( empty( $current_theme ) || ! is_object( $current_theme ) )
			$current_theme = wp_get_theme();

		// if current theme is not the cbox theme, no need to proceed!
		if ( $current_theme->get_stylesheet() != 'cbox-theme' )
			return false;

		// get our internal theme specs
		$cbox_theme_specs = self::$cbox_theme;
		$infinity_specs   = self::$infinity;

		// get parent infinity theme info
		$infinity_current = wp_get_theme( 'infinity' );

		// version checking
		$retval = $cbox_theme_update = $infinity_update = false;

		// check if current cbox theme is less than our internal spec
		if ( version_compare( $current_theme->Version, $cbox_theme_specs['version'] ) < 0 )
			$cbox_theme_update = true;

		// check if infinity theme is less than our internal spec
		if ( version_compare( $infinity_current->Version, $infinity_specs['version'] ) < 0 )
			$infinity_update = true;

		// setup our $retval variable
		if ( $cbox_theme_update && $infinity_update ) {
			$retval = 'all';

		// only one of the themes has an update
		} else {
			if ( $cbox_theme_update ) {
				$retval = 'cbox';
			} elseif ( $infinity_update ) {
				$retval = 'infinity';
			}
		}

		return $retval;
	}

}

/**
 * CBox's custom theme installer.
 *
 * Extends the {@link Theme_Upgrader} class to allow for our custom required spec.
 *
 * @since 0.3
 *
 * @package Commons_In_A_Box
 * @subpackage Themes
 */
class CBox_Theme_Installer extends Theme_Upgrader {

	/**
	 * Overrides the {@link Theme_Upgrader::parent check_parent_theme_filter()} method.
	 *
	 * Why? So we can use our custom download URL for the Infinity parent theme.
	 */
	function check_parent_theme_filter( $install_result, $hook_extra, $child_result ) {
		// Check to see if we need to install a parent theme
		$theme_info = $this->theme_info();

		// if no theme errors, stop now!
		if ( ! $theme_info->errors() )
			return $install_result;

		// get child theme errors
		// doing something different than the parent class method since that didn't work properly
		$errors = $theme_info->errors()->errors;

		// if parent theme is installed, stop now!
		if ( empty( $errors['theme_no_parent'] ) )
			return $install_result;

		// get our specs for Infinity
		$infinity = CBox_Theme_Specs::init()->get( 'infinity' );

		// Override parent theme search string
		$this->strings['parent_theme_search'] = sprintf( __( 'Installing required parent theme, <strong>%s</strong>.', 'cbox' ), $infinity['name'] . ' ' . $infinity['version'] );
		$this->skin->feedback( 'parent_theme_search' );

		// Backup strings we're going to override:
		$child_success_message = $this->strings['process_success'];

		// Override theme success string
		$this->strings['process_success'] = __('Successfully installed the parent theme.', 'cbox' );
		$this->skin->feedback('parent_theme_prepare_install', $infinity['name'], $infinity['version'] );

		// Don't show any actions after installing the theme.
		add_filter( 'install_theme_complete_actions', '__return_false', 999 );

		// Install the parent theme
		$parent_result = $this->run( array(
			'package'           => $infinity['download_url'],
			'destination'       => WP_CONTENT_DIR . '/themes',
			'clear_destination' => true,
			'clear_working'     => true
		) );

		if ( is_wp_error( $parent_result ) )
			add_filter('install_theme_complete_actions', array( $this, 'hide_activate_preview_actions' ) );

		// Start cleaning up after the parents installation
		remove_filter( 'install_theme_complete_actions', '__return_false', 999 );

		// Reset child's result and data
		$this->result = $child_result;
		$this->strings['process_success'] = $child_success_message;

		return $install_result;
	}

	/**
	 * Overrides the {@link Theme_Upgrader::install()} method.
	 *
	 * Why? So we can use our custom download URLs for the CBox and
	 * Infinity themes from Github.
	 */
	function install( $package = false ) {
		$this->init();
		$this->install_strings();

		add_filter( 'upgrader_source_selection',      array( $this, 'rename_github_folder' ),      1,  2 );
		add_filter( 'upgrader_source_selection',      array( $this, 'check_package' ) );
		add_filter( 'upgrader_post_install',          array( $this, 'check_parent_theme_filter' ), 10, 3 );
		add_filter( 'upgrader_post_install',          array( $this, 'activate_post_install' ),     99, 3 );
		add_action( 'http_request_args',              array( $this, 'disable_ssl_verification' ) );
		add_filter( 'install_theme_complete_actions', array( $this, 'remove_theme_actions' ) );

		$this->run( array(
			// get download URL for the Cbox theme
			'package'           => CBox_Theme_Specs::init()->get( 'cbox_theme', 'download_url' ),

			'destination'       => WP_CONTENT_DIR . '/themes',

			// do not overwrite files
			'clear_destination' => false,

			'clear_working'     => true
		) );

		remove_filter( 'upgrader_source_selection',      array( $this, 'rename_github_folder' ),      1,  2 );
		remove_filter( 'upgrader_source_selection',      array( $this, 'check_package' ) );
		remove_filter( 'upgrader_post_install',          array( $this, 'check_parent_theme_filter' ), 10, 3 );
		remove_filter( 'upgrader_post_install',          array( $this, 'activate_post_install' ),     99, 3 );
		remove_action( 'http_request_args',              array( $this, 'disable_ssl_verification' ) );
		remove_filter( 'install_theme_complete_actions', array( $this, 'remove_theme_actions' ) );

		if ( ! $this->result || is_wp_error($this->result) )
			return $this->result;

		// Force refresh of theme update information
		delete_site_transient( 'update_themes' );
		search_theme_directories( true );

		foreach ( wp_get_themes() as $theme )
			$theme->cache_delete();

		return true;
	}

	/**
	 * Overrides the {@link Theme_Upgrader::bulk_upgrade()} method.
	 *
	 * Why? So we can use our custom download URLs for the CBox and
	 * Infinity themes from Github.
	 *
	 * @param str $upgrades The value from CBox_Theme_Specs::get_upgrades()
	 */
	function bulk_upgrade( $upgrades = false ) {
		if ( empty( $upgrades ) )
			return false;

		$this->init();
		$this->bulk = true;
		$this->upgrade_strings();

		add_filter( 'upgrader_source_selection',  array( $this, 'rename_github_folder' ),      1,  2 );
		add_filter( 'upgrader_pre_install',       array( $this, 'current_before' ),            10, 2 );
		add_filter( 'upgrader_post_install',      array( $this, 'current_after' ),             10, 2 );
		add_filter( 'upgrader_clear_destination', array( $this, 'delete_old_theme' ),          10, 4 );
		add_action( 'http_request_args',          array( $this, 'disable_ssl_verification' ) );

		$this->skin->header();

		// Connect to the Filesystem first.
		$res = $this->fs_connect( array( WP_CONTENT_DIR ) );
		if ( ! $res ) {
			$this->skin->footer();
			return false;
		}

		$this->skin->bulk_header();

		// Start maintenance mode because CBox theme is already active by the time
		// we're checking this
		$this->maintenance_mode( true );

		$results = $themes = array();

		// initialize our theme specs
		$theme_specs = CBox_Theme_Specs::init();

		// setup our themes to upgrade
		switch ( $upgrades ) {
			case 'all' :
				$themes[] = $theme_specs->get( 'infinity' );
				$themes[] = $theme_specs->get( 'cbox_theme' );

				break;

			case 'cbox' :
				$themes[] = $theme_specs->get( 'cbox_theme' );

				break;

			case 'infinity' :
				$themes[] = $theme_specs->get( 'infinity' );

				break;
		}

		$this->update_count   = count( $themes );
		$this->update_current = 0;

		foreach ( $themes as $theme ) {
			$this->update_current++;
			$this->skin->theme_info = $this->theme_info( $theme['directory_name'] );

			$result = $this->run( array(
				'package'           => $theme['download_url'],
				'destination'       => WP_CONTENT_DIR . '/themes',
				'clear_destination' => true,
				'clear_working'     => true,
				'hook_extra'        => array( 'theme' => $theme['directory_name'] )
			) );

			$results[ $theme['directory_name'] ] = $this->result;

			// Prevent credentials auth screen from displaying multiple times
			if ( false === $result )
				break;
		} //end foreach $plugins

		$this->maintenance_mode( false );

		$this->skin->bulk_footer();

		$this->skin->footer();

		// Cleanup our hooks, in case something else does a upgrade on this connection.
		remove_filter( 'upgrader_source_selection',  array( $this, 'rename_github_folder' ),      1,  2 );
		remove_filter( 'upgrader_pre_install',       array( $this, 'current_before' ),            10, 2 );
		remove_filter( 'upgrader_post_install',      array( $this, 'current_after' ),             10, 2 );
		remove_filter( 'upgrader_clear_destination', array( $this, 'delete_old_theme' ),          10, 4 );
		remove_action( 'http_request_args',          array( $this, 'disable_ssl_verification' ) );

		// Force refresh of theme update information
		delete_site_transient('update_themes');
		search_theme_directories( true );

		foreach ( wp_get_themes() as $theme )
			$theme->cache_delete();

		return $results;
	}

	/** CUSTOM HOOKS **************************************************/

	/**
	 * Make sure we turn off SSL certificate verification when downloading.
	 *
	 * Github uses HTTPS links, so we need to turn off SSL verification
	 * otherwise WP kills the download.
	 */
	public function disable_ssl_verification( $args ) {
		$args['sslverify'] = false;
		return $args;
	}

	/**
	 * Renames downloaded Github folder to a cleaner directory name.
	 *
	 * Why? Because Github names their directories with the Github username,
	 * repo name and a hash. So we want to rename the theme directory so
	 * WP can pick up the parent theme and so it's more palatable.
	 *
	 * @uses rename() To rename a file or directory.
	 */
	public function rename_github_folder( $source, $remote_source ) {
		// get our preferred theme directory names
		$theme_specs        = CBox_Theme_Specs::init();
		$cbox_theme_dir     = $theme_specs->get( 'cbox_theme', 'directory_name' );
		$infinity_theme_dir = $theme_specs->get( 'infinity',   'directory_name' );

		// setup our parameters depending if we're installing
		// the cbox or infinity theme
		if ( strpos( $source, 'cuny' ) !== false ) {
			$lookup  = 'cuny';
			$new_dir = $cbox_theme_dir;
		} else {
			$lookup  = 'PressCrew';
			$new_dir = $infinity_theme_dir;
		}

		// setup the new location
		$pos = strpos( $source, $lookup );
		$new_location = substr( $source, 0, $pos ) . $new_dir . '/';

		// now rename the folder
		@rename( $source, $new_location );

		// and return the new location
		return $new_location;
	}


	/**
	 * Activates the CBox theme post install.
	 *
	 * @uses switch_theme() To switch the current theme to something else.
	 */
	public function activate_post_install( $bool, $hook_extra, $result ) {
		// get our theme directory names
		$theme_specs        = CBox_Theme_Specs::init();
		$cbox_theme_dir     = $theme_specs->get( 'cbox_theme', 'directory_name' );
		$infinity_theme_dir = $theme_specs->get( 'infinity',   'directory_name' );

		if ( ! empty( $result['destination_name'] ) &&
			( $result['destination_name'] == $cbox_theme_dir || $result['destination_name'] == $infinity_theme_dir )
		) {
			// switch the theme to the cbox theme!
			switch_theme( $infinity_theme_dir, $cbox_theme_dir );
		}

		return $bool;
	}

	/**
	 * Modifies the theme action links that get displayed after theme
	 * installation is complete.
	 */
	public function remove_theme_actions( $actions ) {
		unset( $actions );

		$actions['theme_page'] = '<a href="' . self_admin_url( 'admin.php?page=cbox' ) . '" class="button-primary">' . __( 'Return to CBox Dashboard &rarr;', 'cbox' ) . '</a>';
		return $actions;
	}
}

/**
 * The UI for CBox's Theme Installer.
 *
 * Extends the {@link Theme_Installer_Skin} class just to change an icon!
 *
 * @since 0.3
 *
 * @package Commons_In_A_Box
 * @subpackage Themes
 */
class CBox_Theme_Installer_Skin extends Theme_Installer_Skin {

	/**
	 * Overrides the parent {@link WP_Upgrader_Skin::header()} method.
	 *
	 * Why? Just to change a lousy icon! :)
	 */
	function header() {
		if ( $this->done_header )
			return;

		$this->done_header = true;

		echo '<div class="wrap">';

		// and here's the lousy change!
		echo screen_icon( 'themes' );

		echo '<h2>' . $this->options['title'] . '</h2>';
	}
}
