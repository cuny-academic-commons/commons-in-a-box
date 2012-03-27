<?php
/*
Plugin Name: Commons In A Box
Plugin URI: http://github.com/cuny-academic-commons/
Description: A suite of community and collaboration tools for WordPress, designed especially for academic communities
Version: 0.1
Author: CUNY Academic Commons
Author URI: http://commons.gc.cuny.edu
Licence: GPLv3
Network: true
*/

class Commons_In_A_Box {
	var $plugin_dir;
	
	/**
	 * Static bootstrapping init method
	 *
	 * @since 0.1
	 */
	public static function &init() {
		global $instance;
		
		if ( empty( $instance ) ) {
			$instance = new Commons_In_A_Box; 
		}
		
		return $instance;
	}
	
	/**
	 * Constructor
	 *
	 * @since 0.1
	 */
	public function __construct() {
		$this->setup_globals();
		$this->setup_hooks();
		$this->includes();
	}
	
	/**
	 * Sets up some class data
	 *
	 * @since 0.1
	 * @todo Rename. These aren't globals
	 * @todo Figure out a reliable way to use plugin_dir_path()
	 */
	private function setup_globals() {
		$this->plugin_dir = trailingslashit( dirname( __FILE__ ) );
		define( 'CIAB_PLUGIN_DIR', $this->plugin_dir );
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
		require( $this->plugin_dir . 'plugins/plugins-loader.php' );
		$this->plugins = new CIAB_Plugins;
		
		require( $this->plugin_dir . 'api/class.api-server.php' );
	}
	
	public function get_plugin_dir() {
		return $this->plugin_dir;
	}
}
add_action( 'plugins_loaded', array( 'Commons_In_A_Box', 'init' ), 1 ); 

?>