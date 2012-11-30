<?php
/**
 * CBOX Frontend.
 *
 * @since 1.0-beta2
 *
 * @package Commons_In_A_Box
 * @subpackage Frontend
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Things CBOX does on the frontend of the site.
 *
 * @since 1.0-beta2
 */
class CBox_Frontend {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// get our CBOX admin settings
		$this->settings = get_option( cbox()->settings_key );

		// if no settings exist, stop now!
		if ( empty( $this->settings ) )
			return;

		// setup includes
		$this->includes();

		// setup our hooks
		$this->setup_hooks();
	}

	/**
	 * Includes.
	 *
	 * We conditionally load up specific PHP files depending if a setting was
	 * saved under the CBOX admin settings page.
	 */
	private function includes() {
		$plugins = array_keys( $this->settings );

		foreach ( $plugins as $plugin ) {
			require( cbox()->plugin_dir . "includes/frontend-{$plugin}.php" );
		}
	}

	/**
	 * Setup our hooks.
	 *
	 * We conditionally add our hooks depending if a setting was saved under the
	 * CBOX admin settings page.
	 */
	private function setup_hooks() {
		foreach( $this->settings as $plugin => $classes ) {
			// if our plugin is not setup, stop loading hooks now!
			if ( empty( cbox()->frontend->$plugin->is_setup ) )
				continue;

			foreach ( $classes as $class ) {
				// sanity check!
				// make sure our hook is available
				if ( ! is_callable( array( $class, 'init' ) ) )
					continue;

				// load our hook
				// @todo this hook might need to be configured at the settings level
				add_action( 'bp_include', array( $class, 'init' ), 20 );
			}
		}
	}

}

/**
 * If we were using PHP 5.3, each of our custom classes would extend this one.
 *
 * However, we can't rely on hosts using PHP 5.3+ :(
 * So at the moment, each class needs to define an init() method.
 */
class CBox_Frontend_Init {
	public static function &init() {
		new static();
	}
}
