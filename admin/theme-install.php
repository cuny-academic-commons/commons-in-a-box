<?php
/**
 * CBOX's Theme Installer
 *
 * @package Commons_In_A_Box
 * @subpackage Themes
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// require the WP_Upgrader class so we can extend it!
if ( ! class_exists( 'Plugin_Upgrader' ) )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );

/**
 * Sets up our CBOX theme requirements.
 *
 * In this class, we setup our required specs for the CBOX Default theme.
 *
 * Use the init() method to construct the class.
 *
 * Is chainable, so you can do something like this:
 *      // get infinity theme specs
 *      CBox_Theme_Specs::init()->get( 'cbox-theme' );
 *
 *      // get our version for infinity'
 *      CBox_Theme_Specs::init()->get( 'cbox-theme', 'version' );
 *
 * @package Commons_In_A_Box
 * @subpackage Themes
 */
class CBox_Theme_Specs {

	/**
	 * Setup our theme info.
	 *
	 * We offer the PressCrew-developed CBOX Theme to be installed during CBOX setup.
	 *
	 * @static
	 */
	private static $cbox_theme = array(
		'name'           => 'Commons In A Box Theme',
		'version'        => '1.0.14',
		'directory_name' => 'cbox-theme'
	);

	/**
	 * Static bootstrapping init method.
	 */
	public static function init() {
		self::$cbox_theme['download_url'] = 'http://github.com/cuny-academic-commons/cbox-theme/archive/1.0.14.zip';
		return new self();
	}

	/**
	 * Fetch our theme info depending on the passed variables.
	 *
	 * @param str $theme The theme we want specs for. Only 'cbox_theme' will work.
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
	 * Check to see if our CBOX themes need to be upgraded.
	 *
	 * @param WP_Theme $current_theme The current running theme.
	 * @uses wp_get_theme() If not passed, will grab the current running theme.
	 * @uses CBox_Theme_Specs::init() To initialize our CBOX theme specs
	 * @uses version_compare() To compare the current CBOX theme with our internal specs.
	 * @return mixed String of what themes to update. Boolean false on failure.
	 */
	public static function get_upgrades( WP_Theme $current_theme = NULL ) {
		// get current theme if not passed
		if ( empty( $current_theme ) || ! is_object( $current_theme ) )
			$current_theme = wp_get_theme();

		// get our CBOX theme specs
		$cbox_theme_specs = self::$cbox_theme;

		// if current theme is not the CBOX theme, no need to proceed!
		if ( $current_theme->get_template() != $cbox_theme_specs['directory_name'] )
			return false;

		// child theme support
		// if child theme, we need to grab the CBOX parent theme's data
		if ( $current_theme->get_stylesheet() != $cbox_theme_specs['directory_name'] ) {
			$current_theme = wp_get_theme( $cbox_theme_specs['directory_name'] );
		}

		// version checking
		$retval = false;

		// check if current CBOX theme is less than our internal spec
		// if so, we want to update it!
		if ( version_compare( $current_theme->Version, $cbox_theme_specs['version'] ) < 0 )
			$retval = $cbox_theme_specs['directory_name'];

		return $retval;
	}

}

/**
 * CBOX's custom theme upgrader.
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
	 * Overrides the {@link Theme_Upgrader::install()} method.
	 *
	 * Why? So we can use our custom download URLs from Github.
	 */
	function install( $package = false, $args = array() ) {
		$this->init();
		$this->install_strings();

		add_filter( 'upgrader_source_selection',      'cbox_rename_github_folder',                 1,  3 );
		add_filter( 'upgrader_source_selection',      array( $this, 'check_package' ) );
		add_filter( 'upgrader_post_install',          array( $this, 'activate_post_install' ),     99, 3 );
		add_filter( 'http_request_args',              'cbox_disable_ssl_verification',             10, 2 );
		add_filter( 'install_theme_complete_actions', array( $this, 'remove_theme_actions' ) );

		$this->options['url'] = CBox_Theme_Specs::init()->get( 'cbox_theme', 'download_url' );

		$this->run( array(
			// get download URL for the CBOX theme
			'package'           => CBox_Theme_Specs::init()->get( 'cbox_theme', 'download_url' ),

			'destination'       => WP_CONTENT_DIR . '/themes',

			// do not overwrite files
			'clear_destination' => false,

			'clear_working'     => true
		) );

		remove_filter( 'upgrader_source_selection',      'cbox_rename_github_folder',                 1,  3 );
		remove_filter( 'upgrader_source_selection',      array( $this, 'check_package' ) );
		remove_filter( 'upgrader_post_install',          array( $this, 'activate_post_install' ),     99, 3 );
		remove_filter( 'http_request_args',              'cbox_disable_ssl_verification',             10, 2 );
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
	 * Why? So we can use our custom download URLs from Github.
	 *
	 * @param str $upgrades The value from CBox_Theme_Specs::get_upgrades()
	 */
	function bulk_upgrade( $upgrades = false, $args = array() ) {
		if ( empty( $upgrades ) )
			return false;

		$this->init();
		$this->bulk = true;
		$this->upgrade_strings();

		add_filter( 'upgrader_source_selection',  'cbox_rename_github_folder',        1,  3 );
		add_filter( 'upgrader_pre_install',       array( $this, 'current_before' ),   10, 2 );
		add_filter( 'upgrader_post_install',      array( $this, 'current_after' ),    10, 2 );
		add_filter( 'upgrader_clear_destination', array( $this, 'delete_old_theme' ), 10, 4 );
		add_filter( 'http_request_args',          'cbox_disable_ssl_verification',    10, 2 );

		$this->skin->header();

		// Connect to the Filesystem first.
		$res = $this->fs_connect( array( WP_CONTENT_DIR ) );
		if ( ! $res ) {
			$this->skin->footer();
			return false;
		}

		$this->skin->bulk_header();

		// Start maintenance mode because CBOX theme is already active by the time
		// we're checking this
		$this->maintenance_mode( true );

		$results = $themes = array();

		// initialize our theme specs
		$theme_specs = CBox_Theme_Specs::init();

		// setup our themes to upgrade
		switch ( $upgrades ) {
			case 'cbox-theme' :
				$themes[] = $theme_specs->get( 'cbox_theme' );

				break;
		}

		$this->update_count   = count( $themes );
		$this->update_current = 0;

		foreach ( $themes as $theme ) {
			$this->update_current++;
			$this->skin->theme_info = $this->theme_info( $theme['directory_name'] );
			$this->options['url']   = $theme['download_url'];

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
		remove_filter( 'upgrader_source_selection',  'cbox_rename_github_folder',        1,  3 );
		remove_filter( 'upgrader_pre_install',       array( $this, 'current_before' ),   10, 2 );
		remove_filter( 'upgrader_post_install',      array( $this, 'current_after' ),    10, 2 );
		remove_filter( 'upgrader_clear_destination', array( $this, 'delete_old_theme' ), 10, 4 );
		remove_filter( 'http_request_args',          'cbox_disable_ssl_verification',    10, 2 );

		// Force refresh of theme update information
		delete_site_transient('update_themes');
		search_theme_directories( true );

		foreach ( wp_get_themes() as $theme )
			$theme->cache_delete();

		return $results;
	}

	/** CUSTOM HOOKS **************************************************/

	/**
	 * Activates the CBOX theme post-install.
	 *
	 * @uses switch_theme() To switch the current theme to something else.
	 */
	public function activate_post_install( $bool, $hook_extra, $result ) {
		// get our theme directory names
		$theme_specs        = CBox_Theme_Specs::init();
		$cbox_theme_dir     = $theme_specs->get( 'cbox_theme', 'directory_name' );

		if ( ! empty( $result['destination_name'] ) && $result['destination_name'] == $cbox_theme_dir ) {
			// if BP_ROOT_BLOG is defined and we're not on the root blog, switch to it
			if ( ! bp_is_root_blog() ) {
				switch_to_blog( bp_get_root_blog_id() );
			}

			// switch the theme to the CBOX theme!
			switch_theme( $cbox_theme_dir, $cbox_theme_dir );

			// restore blog after switching
			if ( is_multisite() ) {
				restore_current_blog();
			}

			// Mark the theme as having just been activated
			// so that we can run the setup on next pageload
			bp_update_option( '_cbox_theme_activated', '1' );
		}

		return $bool;
	}

	/**
	 * Modifies the theme action links that get displayed after theme
	 * installation is complete.
	 */
	public function remove_theme_actions( $actions ) {
		unset( $actions );

		$actions['theme_page'] = '<a href="' . self_admin_url( 'admin.php?page=cbox' ) . '" class="button-primary">' . __( 'Return to CBOX Dashboard &rarr;', 'cbox' ) . '</a>';
		return $actions;
	}
}

/**
 * The UI for CBOX's Theme Installer.
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
