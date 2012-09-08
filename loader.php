<?php
/*
Plugin Name: Commons In A Box
Plugin URI: http://github.com/cuny-academic-commons/
Description: A suite of community and collaboration tools for WordPress, designed especially for academic communities
Version: 0.2
Author: CUNY Academic Commons
Author URI: http://commons.gc.cuny.edu
Licence: GPLv3
Network: true
*/

class Commons_In_A_Box {
	/**
	 * Holds the single-running CBox object
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
			self::$instance->setup_hooks();
			self::$instance->includes();
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
	 * @todo Figure out a reliable way to use plugin_dir_path()
	 */
	private function constants() {
		if ( ! defined( 'CIAB_PLUGIN_DIR' ) )
			define( 'CIAB_PLUGIN_DIR', trailingslashit( dirname( __FILE__ ) ) );

		if ( ! defined( 'CIAB_LIB_DIR' ) )
			define( 'CIAB_LIB_DIR',    trailingslashit( CIAB_PLUGIN_DIR . 'lib' ) );

	}

	/**
	 * Sets up some class data
	 *
	 * @since 0.1
	 */
	private function setup_globals() {
		/** VERSION ***********************************************************/

		// CBox version
		$this->version       = '0.3-bleeding';

		// UTC date of CBox version release
		$this->revision_date = '2012-09-07 18:00 UTC';

		/** FILESYSTEM ********************************************************/

		// the absolute directory CBox is running from
		$this->plugin_dir    = constant( 'CIAB_PLUGIN_DIR' );
	}

	/**
	 * Sets up WP hooks
	 *
	 * @since 0.1
	 */
	private function setup_hooks() {

	}

	/**
	 * Includes necessary files
	 *
	 * @since 0.1
	 * @todo Make this nice somehow
	 */
	private function includes() {
		if ( is_admin() ) {
			require( $this->plugin_dir . 'admin/admin-loader.php' );

			require( $this->plugin_dir . 'plugins/plugins-loader.php' );
			$this->plugins = new CIAB_Plugins;
		}

		// @todo temporary
		add_action( 'bp_include', create_function( '', '
			require( CIAB_PLUGIN_DIR . "api/class.api-server.php" );
			require( CIAB_PLUGIN_DIR . "api/class.api-client.php" );
		' ) );
	}

}
add_action( 'plugins_loaded', array( 'Commons_In_A_Box', 'init' ), 1 );

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
