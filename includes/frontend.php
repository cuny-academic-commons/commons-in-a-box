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
		// setup globals
		$this->setup_globals();

		// if no settings exist, stop now!
		if ( empty( $this->settings ) )
			return;

		// setup our CBOX plugins object
		// this will hold some plugin-specific references
		cbox()->plugins = new stdClass;

		// setup includes
		$this->includes();

		// setup our hooks
		$this->setup_hooks();
	}


	/**
	 * Setup globals.
	 *
	 * @since 1.0.1
	 */
	private function setup_globals() {
		// get our CBOX admin settings
		$this->settings = (array) get_option( cbox()->settings_key );

		// setup autoload classes
		$this->setup_autoload();

		// merge admin settings with autoloaded ones
		$this->settings = array_merge_recursive( $this->settings, $this->autoload );
	}

	/**
	 * Setup autoload classes.
	 *
	 * @since 1.0.1
	 */
	private function setup_autoload() {
		// setup internal autoload variable
		// will hold plugins and classes that need to be autoloaded by CBOX
		$this->autoload = array();

		// bbPress
		$this->autoload['bbpress']   = array();
		$this->autoload['bbpress'][] = 'CBox_BBP_Site_Public';

		// Group Email Subscription
		$this->autoload['ges']   = array();
		$this->autoload['ges'][] = 'CBox_GES_All_Mail';
	}

	/**
	 * Includes.
	 *
	 * We conditionally load up specific PHP files depending if a setting was
	 * saved under the CBOX admin settings page.
	 */
	private function includes() {
		// get plugins from CBOX settings
		$plugins = array_keys( $this->settings );

		foreach ( $plugins as $plugin ) {
			if ( file_exists( cbox()->plugin_dir . "includes/frontend-{$plugin}.php" ) ) {
				require( cbox()->plugin_dir . "includes/frontend-{$plugin}.php" );
			}
		}
	}

	/**
	 * Setup our hooks.
	 *
	 * We conditionally add our hooks depending if a setting was saved under the
	 * CBOX admin settings page or if it is explicitly autoloaded by CBOX.
	 */
	private function setup_hooks() {

		foreach( $this->settings as $plugin => $classes ) {
			// if our plugin is not setup, stop loading hooks now!
			if ( empty( cbox()->plugins->$plugin->is_setup ) )
				continue;

			// sanity check
			$classes = array_unique( $classes );

			// load our classes
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
