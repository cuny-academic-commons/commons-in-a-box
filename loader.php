<?php
/*
Plugin Name: Commons In A Box
Plugin URI: http://commonsinabox.org
Description: A suite of community and collaboration tools for WordPress, designed especially for academic communities
Version: 1.0.12
Author: CUNY Academic Commons
Author URI: http://commons.gc.cuny.edu
Licence: GPLv3
Network: true
Core: >=4.1.1
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class Commons_In_A_Box {
	/**
	 * Holds the single-running CBOX object
	 *
	 * @var Commons_In_A_Box
	 */
	private static $instance = false;

	/**
	 * Static bootstrapping init method
	 *
	 * @since 0.1
	 */
	public static function &init() {
		if ( ! self::$instance ) {
			self::$instance = new self();
			self::$instance->constants();
			self::$instance->setup_globals();
			self::$instance->includes();
			self::$instance->setup_actions();
			self::$instance->load_components();
		}

		return self::$instance;
	}

	/**
	 * Private constructor. Intentionally left empty.
	 *
	 * Instantiate this class by using {@link cbox()} or {@link Commons_In_A_Box::init()}.
	 *
	 * @since 0.1
	 */
	private function __construct() {}

	/**
	 * Sets up our constants
	 *
	 * @since 0.2
	 *
	 * @todo Figure out a reliable way to use plugin_dir_path()
	 */
	private function constants() {
		if ( ! defined( 'CBOX_PLUGIN_DIR' ) )
			define( 'CBOX_PLUGIN_DIR', trailingslashit( dirname( __FILE__ ) ) );

		if ( ! defined( 'CBOX_LIB_DIR' ) )
			define( 'CBOX_LIB_DIR',    trailingslashit( CBOX_PLUGIN_DIR . 'lib' ) );

	}

	/**
	 * Sets up some class data
	 *
	 * @since 0.1
	 */
	private function setup_globals() {

		/** VERSION ***********************************************************/

		// CBOX version
		$this->version       = '1.0.12';

		// UTC date of CBOX version release
		$this->revision_date = '2016-02-23 20:00 UTC';

		/** FILESYSTEM ********************************************************/

		// the absolute directory CBOX is running from
		$this->plugin_dir    = constant( 'CBOX_PLUGIN_DIR' );

		// the URL to the CBOX directory
		$this->plugin_url    = plugin_dir_url( __FILE__ );

		/** SETTINGS **********************************************************/

		// the settings options key used on the "CBOX Settings" page
		$this->settings_key  = '_cbox_admin_settings';
	}

	/**
	 * Includes necessary files
	 *
	 * @since 0.1
	 */
	private function includes() {
		// pertinent functions used everywhere
		require( $this->plugin_dir . 'includes/functions.php' );

		// admin area
		if ( cbox_is_admin() ) {
			require( $this->plugin_dir . 'admin/admin-loader.php' );
			require( $this->plugin_dir . 'admin/plugins-loader.php' );
			require( $this->plugin_dir . 'admin/settings-loader.php' );

		// frontend
		} else {
			require( $this->plugin_dir . 'includes/frontend.php' );
		}
	}

	/**
	 * Setup actions.
	 *
	 * @since 1.0-beta4
	 */
	private function setup_actions() {
		// Add actions to plugin activation and deactivation hooks
		add_action( 'activate_'   . plugin_basename( __FILE__ ), create_function( '', "do_action( 'cbox_activation' );"   ) );
		add_action( 'deactivate_' . plugin_basename( __FILE__ ), create_function( '', "do_action( 'cbox_deactivation' );" ) );

		// localization
		// we only fire this in the admin area, since we have no strings to localize
		// on the frontend... yet!
		add_action( 'admin_init', array( $this, 'localization' ), 0 );
	}

	/**
	 * Load up our components.
	 *
	 * @since 1.0-beta2
	 */
	private function load_components() {
		// admin area
		if ( cbox_is_admin() ) {
			$this->admin    = new CBox_Admin;
			$this->plugins  = new CBox_Plugins;
			$this->settings = new CBox_Settings;

		// frontend
		} else {
			$this->frontend = new CBox_Frontend;
		}
	}

	/** HOOKS *********************************************************/

	/**
	 * Custom textdomain loader.
	 *
	 * Checks WP_LANG_DIR for the .mo file first, then the plugin's language folder.
	 * Allows for a custom language file other than those packaged with the plugin.
	 *
	 * @since 1.0-beta4
	 *
	 * @uses get_locale() To get the current locale
	 * @uses load_textdomain() Loads a .mo file into WP
	 * @return bool True on success, false on failure
	 */
	public function localization() {
		// Use the WP plugin locale filter from load_plugin_textdomain()
		$locale        = apply_filters( 'plugin_locale', get_locale(), 'cbox' );
		$mofile        = sprintf( '%1$s-%2$s.mo', 'cbox', $locale );

		$mofile_global = trailingslashit( constant( 'WP_LANG_DIR' ) ) . $mofile;
		$mofile_local  = $this->plugin_dir . 'languages/' . $mofile;

		// look in /wp-content/languages/ first
		if ( is_readable( $mofile_global ) ) {
			return load_textdomain( 'cbox', $mofile_global );

		// if that doesn't exist, check for bundled language file
		} elseif ( is_readable( $mofile_local ) ) {
			return load_textdomain( 'cbox', $mofile_local );

		// no language file exists
		} else {
			return false;
		}
	}

	/** HELPERS *******************************************************/

	/**
	 * Get the plugin URL for CBOX.
	 *
	 * @since 1.0-beta1
	 *
	 * @param str $path Path relative to the CBOX plugin URL.
	 * @return str CBOX plugin URL with optional path appended.
	 */
	public function plugin_url( $path = '' ) {
		if ( ! empty( $path ) && is_string( $path ) )
			return esc_url( cbox()->plugin_url . $path );
		else
			return cbox()->plugin_url;
	}

}

/**
 * The main function responsible for returning the Commons In A Box instance
 * to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $cbox = cbox(); ?>
 *
 * @return Commons_In_A_Box
 */
function cbox() {
	return Commons_In_A_Box::init();
}

// Vroom!
cbox();
