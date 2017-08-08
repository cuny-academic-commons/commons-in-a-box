<?php
/**
 * Packages API: Base class
 *
 * @package    Commons_In_A_Box
 * @subpackage Package
 * @since      1.1.0
 */

/**
 * Base class to register a CBOX package.
 *
 * @since 1.1.0
 */
abstract class CBox_Package {
	/**
	 * Name of your package. Required. Must be extended.
	 *
	 * @since 1.1.0
	 *
	 * @var string
	 */
	public static $name = '';

	/**
	 * Configuration holder. Required. Must be in extended class.
	 *
	 * Why? Because extended classes need to ensure their data is stored in their
	 * class and not in the parent due to the usage of late static binding. For
	 * more info, read: {@link https://bkcore.com/blog/code/php-late-static-binding-child-attribute-declaration.html}
	 *
	 * Copy the commented line and put it in your extended class. Intentionally
	 * commented out so a fatal error is thrown if this isn't declared in the
	 * extended class and because PHP doesn't support abstract class properties.
	 *
	 * @since 1.1.0
	 */
	//protected static $config = array();

	/**
	 * Theme properties.
	 *
	 * @since 1.1.0
	 *
	 * @var array See {@link CBox_Package::register_theme()} for parameters.
	 */
	public static $theme = array();

	/**
	 * String holder.
	 *
	 * @since 1.1.0
	 *
	 * @var array See {@link CBox_Package::register_strings()} for parameters.
	 */
	public static $strings = array();

	/**
	 * Starts the extended class.
	 *
	 * @since 1.1.0
	 */
	public static function init() {
		return new static();
	}

	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 */
	final protected function __construct() {
		// Set props.
		$this->set_theme();
		self::set_props();

		// Custom init method.
		$this->custom_init();

		// Handle plugin registration here.
		add_action( 'cbox_plugins_loaded', function( $i ) {
			if ( false === strpos( get_called_class(), 'CBox_Package_' ) ) {
				return;
			}

			// Try to automatically load plugins manifest class if found.
			$plugins_class = 'CBox_Plugins_' . substr( get_called_class(), 13 );
			if ( class_exists( $plugins_class ) ) {
				call_user_func( array( $plugins_class, 'init' ), array( $i, 'register_plugin' ) );

			// Else, use the register_plugins() method to do plugin registration.
			} else {
				$this->register_plugins( array( $i, 'register_plugin' ) );
			}
		} );

		// Automatically handle settings registration here.
		add_action( 'cbox_load_components', function( $i ) {
			if ( ! cbox_is_admin() || false === strpos( get_called_class(), 'CBox_Package_' ) ) {
				return;
			}

			$settings_class = 'CBox_Settings_' . substr( get_called_class(), 13 );
			if ( class_exists( $settings_class ) ) {
				$i->settings = new $settings_class;
			}
		} );
	}

	/**
	 * Set theme.
	 *
	 * @since 1.1.0
	 */
	final protected function set_theme() {
		$_theme = $this->register_theme();
		if ( ! empty( $_theme ) && is_array( $_theme ) ) {
			static::$theme = $_theme;
		}
	}

	/**
	 * Set miscellaneous props.
	 *
	 * @since 1.1.0
	 */
	public static function set_props() {
		static::$config  = array_merge( (array) self::config(),  (array) static::config() );
		static::$strings = array_merge( (array) self::strings(), (array) static::strings() );
	}

	/**
	 * Get props.
	 *
	 * @since 1.1.0
	 */
	public static function get_props() {
		return static::$config;
	}

	/**
	 * Register plugins in this method for 3rd-party packages.
	 *
	 * @since 1.1.0
	 *
	 * @param callable $instance {@see CBox_Plugins::register_plugin()}.
	 */
	protected function register_plugins( $instance ) {}

	/**
	 * Register theme, only extend if your package requires a theme
	 *
	 * @since 1.1.0
	 *
	 * @return array {
	 *     Array of parameters.
	 *     @var string $name           Name of the theme
	 *     @var string $version        Theme version number
	 *     @var string $directory_name Theme directory slug
	 *     @var string $download_url   Download location for theme, either URL or absolute filepath to .zip file.
	 *     @var string $admin_settings Relative admin path to your theme's settings. eg. 'themes.php?page=X'
	 *     @var string $screenshot_url Optional. URL to screenshot.
	 * }
	 */
	protected function register_theme() {
		return array();
	}

	/**
	 * Package configuration, extend if necessary.
	 *
	 * @since 1.1.0
	 *
	 * @return array {
	 *     Array of parameters.
	 *     @var bool   $network           Whether the package requires multisite. Default: false.
	 *     @var string $template_path     Absolute filepath for custom admin template parts. If your package is not
	 *                                    bundled with CBOX and you need to override the default admin templates,
	 *                                    then override this parameter.
	 *     @var string $settings_key      Used to fetch settings with {@link get_option()}.
	 *     @var string $documentation_url Optional. Documentation URL. Currently used in package selection screen's
	 *                                    "More Details" link.
	 *     @var string $icon_url          Optional. Icon URL.
	 * }
	 */
	protected static function config() {
		return array(
			'template_path' => CBOX_PLUGIN_DIR . 'admin/templates/' . sanitize_file_name( strtolower( static::$name ) ) . '/',
			'icon_url'      => includes_url( 'images/crystal/archive.png' ),
			'network'       => false
		);
	}

	/**
	 * Strings setter method, extend if necessary.
	 *
	 * @since 1.1.0
	 *
	 * @return array
	 */
	protected static function strings() {
		return array(
			'tab_plugin_required' => __( 'Core Plugins', 'cbox' ),
			'tab_plugin_optional' => __( 'Optional Plugins', 'cbox' ),
			'tab_plugin_install'  => __( 'Member Site Plugins', 'cbox' )
		);
	}

	/**
	 * Custom init method, extend if necessary.
	 *
	 * Handy if you need to include files or setup custom hooks.
	 *
	 * @since 1.1.0
	 */
	protected function custom_init() {}
}