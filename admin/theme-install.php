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
	 * Setup our theme info.
	 *
	 * We offer the PressCrew-developed CBox Theme to be installed during CBox setup.
	 *
	 * @static
	 */
	public static $cbox_theme = array(
		'name'         => 'Commons In A Box Theme',
		'version'      => '1.0',

		// @todo need a tagged version... until then, we use the bleeding version
		'download_url' => 'https://github.com/cuny-academic-commons/cbox-theme/zipball/master'
	);

	/**
	 * Setup our parent theme info.
	 *
	 * The CBox theme uses the Infinity theme as a parent.
	 * This variable is referenced if Infinity isn't already installed.
	 *
	 * @see CBox_Theme_Upgrader::check_parent_theme_filter()
	 * @static
	 */
	public static $infinity = array(
		'name'         => '&#8734; Infinity',
		'version'      => '1.1a',

		// this is set to use the bleeding 'buddypress' branch to get the latest
		// Infinity bug fixes
		// @todo When 1.1 hits, use tagged version
		'download_url' => 'https://github.com/PressCrew/infinity/zipball/buddypress'
	);

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

		// Override parent theme search string
		$this->strings['parent_theme_search'] = sprintf( __( 'Installing required parent theme, <strong>%s</strong>.', 'cbox' ), self::$infinity['name'] . ' ' . self::$infinity['version'] );
		$this->skin->feedback( 'parent_theme_search' );

		// Backup strings we're going to override:
		$child_success_message = $this->strings['process_success'];

		// Override theme success string
		$this->strings['process_success'] = __('Successfully installed the parent theme.', 'cbox' );
		$this->skin->feedback('parent_theme_prepare_install', self::$infinity['name'], self::$infinity['version'] );

		// Don't show any actions after installing the theme.
		add_filter( 'install_theme_complete_actions', '__return_false', 999 );

		// Install the parent theme
		$parent_result = $this->run( array(
			'package'           => self::$infinity['download_url'],
			'destination'       => WP_CONTENT_DIR . '/themes',
			'clear_destination' => true,
			'clear_working'     => true
		) );

		if ( is_wp_error( $parent_result ) )
			add_filter('install_theme_complete_actions', array( &$this, 'hide_activate_preview_actions' ) );

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

		add_filter( 'upgrader_source_selection',      array( &$this, 'rename_github_folder' ),      1,  2 );
		add_filter( 'upgrader_source_selection',      array( &$this, 'check_package' ) );
		add_filter( 'upgrader_post_install',          array( &$this, 'check_parent_theme_filter' ), 10, 3 );
		add_filter( 'upgrader_post_install',          array( &$this, 'activate_post_install' ),     99, 3 );
		add_action( 'http_request_args',              array( &$this, 'disable_ssl_verification' ) );
		add_filter( 'install_theme_complete_actions', array( &$this, 'remove_theme_actions' ) );

		$this->run( array(
			'package'           => self::$cbox_theme['download_url'],
			'destination'       => WP_CONTENT_DIR . '/themes',
			'clear_destination' => false, //Do not overwrite files.
			'clear_working'     => true
		) );

		remove_filter( 'upgrader_source_selection',      array( &$this, 'rename_github_folder' ),     1,  2 );
		remove_filter( 'upgrader_source_selection',      array( &$this, 'check_package' ) );
		remove_filter( 'upgrader_post_install',          array( &$this, 'check_parent_theme_filter'), 10, 3 );
		remove_filter( 'upgrader_post_install',          array( &$this, 'activate_post_install' ),    99, 3 );
		remove_action( 'http_request_args',              array( &$this, 'disable_ssl_verification' ) );
		remove_filter( 'install_theme_complete_actions', array( &$this, 'remove_theme_actions' ) );

		if ( ! $this->result || is_wp_error($this->result) )
			return $this->result;

		// Force refresh of theme update information
		delete_site_transient( 'update_themes' );
		search_theme_directories( true );

		foreach ( wp_get_themes() as $theme )
			$theme->cache_delete();

		return true;
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
		// setup our parameters depending if we're installing
		// the cbox or infinity theme
		if ( strpos( $source, 'cuny' ) !== false ) {
			$lookup  = 'cuny';
			$new_dir = 'cbox-theme';
		} else {
			$lookup  = 'PressCrew';
			$new_dir = 'infinity';
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
		if ( ! empty( $result['destination_name'] ) &&
			( $result['destination_name'] == 'cbox-theme' || $result['destination_name'] == 'infinity' )
		) {
			// switch the theme to the cbox theme!
			switch_theme( 'infinity', 'cbox-theme' );
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
