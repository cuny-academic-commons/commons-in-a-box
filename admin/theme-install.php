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

		$package_theme = cbox_get_package_prop( 'theme' );
		$this->options['url'] = $package_theme['download_url'];

		$this->run( array(
			// get download URL for the CBOX theme
			'package'           => $package_theme['download_url'],

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
	 * @param str $upgrades The value from cbox_get_theme_to_update()
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

		// @todo Potential to have multiple themes attached to a CBOX package...
		$themes[] = cbox_get_package_prop( 'theme' );

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
		$theme = cbox_get_package_prop( 'theme' );

		if ( ! empty( $result['destination_name'] ) && $result['destination_name'] == $theme['directory_name'] ) {
			// if BP_ROOT_BLOG is defined and we're not on the root blog, switch to it
			if ( ! bp_is_root_blog() ) {
				switch_to_blog( bp_get_root_blog_id() );
			}

			// switch the theme
			switch_theme( $theme['directory_name'], $theme['directory_name'] );

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
