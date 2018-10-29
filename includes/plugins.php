<?php
/**
 * Core plugin class.
 *
 * @since 0.1
 *
 * @package Commons_In_A_Box
 * @subpackage Plugins
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Core plugin class for CBOX.
 *
 * @since 0.1
 */
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
		// Admin code.
		add_action( 'cbox_plugins_loaded', function() {
			if ( ! cbox_is_admin() ) {
				return;
			}

			// Add the Plugin Dependencies plugin
			if ( ! class_exists( 'Plugin_Dependencies' ) ) {
				require_once CBOX_LIB_DIR . 'wp-plugin-dependencies/plugin-dependencies.php';
			}

			// Load up admin plugins code.
			require_once CBOX_PLUGIN_DIR . 'admin/plugins.php';
			CBox_Admin_Plugins::init();
		}, 90 );

		/**
		 * Hook to declare when the CBOX plugins code is loaded at its earliest.
		 *
		 * @since 1.1.0
		 *
		 * @param CBox_Plugins $this
		 */
		do_action( 'cbox_plugins_loaded', $this );
	}

	/**
	 * Register a plugin in CBOX.
	 *
	 * Updates our private, static $plugins variable in the process.
	 *
	 * @since 1.1.0 Added $network and $hide as parameters. Added 'install-only' as an option
	 *              for $type.
	 *
	 * @param array $args {
	 *     Array of parameters.
	 *     @type string $plugin_name       Required. Name of the plugin as in the WP plugin header.
	 *     @type string $type              Required. Either 'required', 'recommended', 'optional', 'install-only' or
	 *                                     'dependency'. If set to 'install-only', $network and $hide are
	 *                                     always set to boolean false.
	 *     @type string $cbox_name         Custom name for the plugin.
	 *     @type string $cbox_description  Custom short description for the plugin.
	 *     @type string $depends           Defined plugin dependencies for the plugin. See
	 *                                     {@link Plugin_Dependencies::parse_requirements()} for syntax.
	 *     @type string $version           Plugin version number.
	 *     @type string $download_url      Plugin download URL. Used to downlaod the plugin if not installed.
	 *     @type string $documentation_url Plugin documentation URL.
	 *     @type string $admin_settings    Relative wp-admin link to plugin's admin settings page, if applicable.
	 *     @type string $network_settings  Relative wp-admin link to plugin's network admin settings page, if
	 *                                     applicable. If plugin's settings resides on the root blog, set this value
	 *                                     to 'root-blog-only'.
	 *     @type bool   $network           Should the plugin be activated network-wide? Default: true.
	 *     @type mixed  $hide              Hides plugin from the admin dashboard's "Plugins" page.  Needs to be set
	 *                                     explicitly to boolean false to hide the plugin. Default: null.
	 * }
	 */
	public function register_plugin( $args = '' ) {
		$defaults = array(
			'plugin_name'       => false,
			'type'              => 'required',
			'cbox_name'         => false,
			'cbox_description'  => false,
			'depends'           => false,
			'version'           => false,
			'download_url'      => false,
			'documentation_url' => false,
			'admin_settings'    => false,
			'network_settings'  => false,
			'network'           => true,
			'hide'              => null
		);

		$r = wp_parse_args( $args, $defaults );

		if ( empty( $r['plugin_name'] ) ) {
			return false;
		}

		switch( $r['type'] ) {
			case 'required' :
			case 'recommended' :
			case 'optional' :
			case 'install-only' :
			case 'dependency' :
				self::$plugins[ $r['type'] ][ $r['plugin_name'] ] = $r;
				unset( self::$plugins[ $r['type'] ][ $r['plugin_name'] ]['plugin_name'] );

				if ( 'install-only' === $r['type'] ) {
					self::$plugins[ $r['type'] ][ $r['plugin_name'] ]['network'] = false;
					self::$plugins[ $r['type'] ][ $r['plugin_name'] ]['hide']    = false;
				}

				break;
		}

	}

	/**
	 * Helper method to grab all CBOX plugins of a certain type.
	 *
	 * @since 1.1.0 $type can be passed as '' to return all plugins sorted by type as key.
	 *
	 * @param string $type Type of CBOX plugin. Either 'all', 'required', 'recommended', 'optional',
	 *                     'install-only', 'dependency'. If empty, all plugins are returned by type.
	 * @param string $omit_type The type of CBOX plugin to omit from returning
	 * @return mixed Array of plugins on success. Boolean false on failure.
	 */
	public static function get_plugins( $type = 'all', $omit_type = false ) {
		// if type is 'all', we want all CBOX plugins regardless of type
		if ( $type == 'all' ) {
			$plugins = self::$plugins;
			if ( empty( $plugins ) ) {
				return $plugins;
			}

			// okay, I lied, we want all plugins except dependencies!
			unset( $plugins['dependency'] );

			// if $omit_type was passed, use it to remove the plugin type we don't want
			if ( ! empty( $omit_type ) )
				unset( $plugins[$omit_type] );

			// flatten associative array
			return call_user_func_array( 'array_merge', $plugins );
		}

		// Return plugins as-is if $type is empty.
		if ( empty( $type ) ) {
			return self::$plugins;
		}

		if ( empty( self::$plugins[$type] ) )
			return false;

		return self::$plugins[$type];
	}

	/**
	 * Helper method to check if a CBOX plugin is a certain type.
	 *
	 * @since 1.1.0
	 *
	 * @param  string $plugin_name Full plugin name.
	 * @param  string $type        Type of CBOX plugin. Either 'all', 'required', 'recommended', 'optional',
	 *                            'install-only', 'dependency'.
	 * @return bool
	 */
	public static function is_plugin_type( $plugin_name = '', $type = '' ) {
		if ( '' === $plugin_name || '' === $type ) {
			return false;
		}

		if ( isset( self::$plugins[ $type ][ $plugin_name ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Backup the current plugins list.
	 *
	 * @since 1.1.0
	 */
	public static function backup() {
		cbox()->temp_plugins = self::$plugins;
		self::$plugins = array();
	}

	/**
	 * Restore plugins list from a previous backup.
	 *
	 * @since 1.1.0
	 */
	public static function restore() {
		if ( isset( cbox()->temp_plugins ) ) {
			self::$plugins = cbox()->temp_plugins;
			unset( cbox()->temp_plugins );
		}
	}
}
