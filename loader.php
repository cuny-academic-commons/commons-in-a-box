<?php
/*
Plugin Name: Commons In A Box
Plugin URI: http://github.com/cuny-academic-commons/
Description: A suite of community and collaboration tools for WordPress, designed especially for academic communities
Version: 1.0-beta3
Author: CUNY Academic Commons
Author URI: http://commons.gc.cuny.edu
Licence: GPLv3
Network: true
*/

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
		$this->version       = '1.0-beta3';

		// UTC date of CBOX version release
		$this->revision_date = '2012-12-04 03:00 UTC';

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

	/** HELPERS *******************************************************/

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
