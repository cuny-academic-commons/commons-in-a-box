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
		self::set_props();

		// Custom init method.
		$this->custom_init();

		// Deactivation routine.
		add_action( 'cbox_package_' . cbox_get_current_package_id() . '_deactivation', array( get_called_class(), 'deactivate' ) );

		// Handle plugin registration.
		add_action( 'cbox_plugins_loaded', array( get_called_class(), 'plugin_registrar' ) );

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

		// Plugin defaults.
		add_action( 'cbox_before_updater', function() {
			add_action( 'activate_plugin', function( $plugin_file ) {
				$plugin_dir = substr( $plugin_file, 0, strpos( $plugin_file, '/' ) );
				$class = new ReflectionClass( get_called_class() );

				/*
				 * Handle individual plugin activation routine if available.
				 */
				$file = dirname( $class->getFileName() ) . '/defaults/' . $plugin_dir . '.php';
				if ( file_exists( $file ) ) {
					require $file;
				}
			} );
		} );
	}

	/**
	 * Set miscellaneous props.
	 *
	 * @since 1.1.0
	 */
	public static function set_props() {
		static::$config  = array_merge( (array) self::config(),  (array) static::config() );
		static::$strings = array_merge( (array) self::strings(), (array) static::strings() );
		static::$theme   = array_merge( (array) self::theme(),   (array) static::theme() );
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
	 * Plugin registrar method.
	 *
	 * @since 1.1.0
	 */
	public static function plugin_registrar( $i ) {
		$class = get_called_class();
		if ( false === strpos( $class, 'CBox_Package_' ) ) {
			return;
		}

		// Try to automatically load plugins manifest class if found.
		$plugins_class = 'CBox_Plugins_' . substr( $class, 13 );

		if ( class_exists( $plugins_class ) ) {
			call_user_func( array( $plugins_class, 'init' ), array( $i, 'register_plugin' ) );

		// Else, use the register_plugins() method to do plugin registration.
		} else {
			static::register_plugins( array( $i, 'register_plugin' ) );
		}
	}

	/**
	 * Alternate method to register plugins for a package.
	 *
	 * @since 1.1.0
	 *
	 * @param callable $instance {@see CBox_Plugins::register_plugin()}.
	 */
	protected static function register_plugins( $instance ) {}

	/**
	 * Get list of plugins for the package, sorted by type.
	 *
	 * @since 1.1.0
	 *
	 * @return array
	 */
	public static function get_plugins() {
		$packages = cbox_get_packages();

		// If we're already done this before, load existing plugin list.
		if ( $packages[ cbox_get_current_package_id() ] === get_called_class() ) {
			$plugins = CBox_Plugins::get_plugins( '' );

		// Fetch the plugin list for the package.  This isn't elegant...
		} else {
			global $wp_filter;

			// Backup current plugins list and package registrar.
			CBox_Plugins::backup();
			$backup = $wp_filter['cbox_plugins_loaded']->callbacks[10];
			unset( $wp_filter['cbox_plugins_loaded']->callbacks[10] );

			// Load up plugin registrar.
			add_action( 'cbox_plugins_loaded', array( get_called_class(), 'plugin_registrar' ) );

			// Perform plugin registration.
			$instance = clone cbox()->plugins;
			/** This hook is documented in /commons-in-a-box/includes/plugins.php */
			do_action_ref_array( 'cbox_plugins_loaded', array( $instance ) );

			// Fetch package plugins.
			$plugins = $instance::get_plugins( '' );

			// Clean-up and restore.
			$wp_filter['cbox_plugins_loaded']->callbacks[10] = $backup;
			unset( $instance, $backup );
			remove_action( 'cbox_plugins_loaded', array( get_called_class(), 'plugin_registrar' ) );
			CBox_Plugins::restore();
		}

		return $plugins;
	}

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
	 *     @var bool   $force_install  Whether to force theme install during initial package install.
	 * }
	 */
	protected static function theme() {
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
	 *     @var string $badge_url         Optional. Shows on dashboard's "Welcome" block.
	 *     @var string $badge_url_2x      Optional. Double-sized version of $badge_url for retina-sized displays.
	 * }
	 */
	protected static function config() {
		return array(
			'template_path' => CBOX_PLUGIN_DIR . 'admin/templates/' . sanitize_file_name( strtolower( static::$name ) ) . '/',
			'icon_url'      => includes_url( 'images/crystal/archive.png' ),
			'badge_url'     => cbox()->plugin_url( 'admin/images/logo-cbox_vert.png' ),
			'badge_url_2x'  => cbox()->plugin_url( 'admin/images/logo-cbox_vert-2x.png' ),
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
			'tab_plugin_install'  => __( 'Member Site Plugins', 'cbox' ),
			'dashboard_header'    => sprintf( esc_html__( 'Welcome to Commons In A Box %s', 'cbox' ), cbox_get_package_prop( 'name' ) )
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

	/**
	 * Deactivation method, extend if necessary.
	 *
	 * Do something when the current package is being reset.
	 *
	 * @since 1.1.0
	 */
	public static function deactivate() {}
}