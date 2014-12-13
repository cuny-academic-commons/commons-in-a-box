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

		add_action( 'plugins_loaded', array( $this, 'includes' ), 99 );
		add_action( 'plugins_loaded', array( $this, 'setup_hooks' ), 100 );
		add_action( 'init', array( $this, 'includes' ), 99 );
		add_action( 'init', array( $this, 'setup_hooks' ), 100 );
	}

	/**
	 * Setup globals.
	 *
	 * @since 1.0.1
	 */
	private function setup_globals() {
		// get our CBOX admin settings
		$this->settings = (array) bp_get_option( cbox()->settings_key );

		// setup autoload classes
		$this->setup_autoload();

		// merge admin settings with autoloaded ones
		$this->settings = array_merge_recursive( $this->settings, $this->autoload );

		// setup plugins
		$this->setup_plugins();
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

		// WordPress
		$this->autoload['wp']   = array();
		$this->autoload['wp'][] = 'CBox_WP_Toolbar_Updates';

		// bbPress
		$this->autoload['bbpress']   = array();
		$this->autoload['bbpress'][] = 'CBox_BBP_Autoload';

		// Group Email Subscription
		$this->autoload['ges']   = array();
		$this->autoload['ges'][] = 'CBox_GES_All_Mail';

		// Custom Profile Filters for BuddyPress
		$this->autoload['cpf']   = array();
		$this->autoload['cpf'][] = 'CBox_CPF_Rehook_Social_Fields';
	}

	/**
	 * Setup plugins.
	 *
	 * What we're doing here is adding some properties so we can check for the
	 * plugin's existence later on in CBOX_Frontend::includes().
	 *
	 * @since 1.0.9.1
	 */
	private function setup_plugins() {
		// setup our CBOX plugins object
		// this will hold some plugin-specific references
		$this->plugins = new stdClass;

		$plugins = array_keys( $this->settings );

		// we're adding the string of the function name to each plugin so we can check
		// for its existence later on a hook that is specified below.
		foreach ( $plugins as $plugin ) {
			switch ( $plugin ) {
				// buddypress
				case 'bp' :
					$this->plugins->$plugin = new stdClass;
					$this->plugins->$plugin->active_check = 'bp_include';
					$this->plugins->$plugin->on_action    = 'plugins_loaded';
					break;

				// bbpress
				case 'bbpress' :
					$this->plugins->$plugin = new stdClass;
					$this->plugins->$plugin->active_check = 'bbp_activation';
					$this->plugins->$plugin->on_action    = 'plugins_loaded';
					break;

				// custom profile filters for buddypress
				// this doesn't work since cpf loads its code on 'bp_init' instead of
				// 'plugins_loaded'
				case 'cpf' :
					$this->plugins->$plugin = new stdClass;
					$this->plugins->$plugin->active_check = 'cpfb_add_social_networking_links';
					$this->plugins->$plugin->on_action    = 'init';
					break;

				// group email subscription
				case 'ges' :
					$this->plugins->$plugin = new stdClass;
					$this->plugins->$plugin->active_check = 'ass_activate_extension';
					$this->plugins->$plugin->on_action    = 'plugins_loaded';
					break;

				// wordpress
				case 'wp' :
					$this->plugins->$plugin = new stdClass;
					$this->plugins->$plugin->active_check = 'wp';
					$this->plugins->$plugin->on_action    = 'plugins_loaded';
					break;
			}
		}
	}

	/**
	 * Includes.
	 *
	 * We conditionally load up specific PHP files depending if a setting was
	 * saved under the CBOX admin settings page.
	 */
	public function includes() {
		foreach ( $this->settings as $plugin => $classes ) {
			// do not do this if we're not firing on the plugin's specific hook
			if ( $this->plugins->$plugin->on_action !== current_filter() ) {
				continue;
			}

			// if our plugin is not setup, do not load file
			if ( ! function_exists( $this->plugins->$plugin->active_check ) ) {
				continue;
			}

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
	public function setup_hooks() {
		foreach( $this->settings as $plugin => $classes ) {
			// do not do this if we're not firing on the plugin's specific hook
			if ( $this->plugins->$plugin->on_action !== current_filter() ) {
				continue;
			}

			// if our plugin is not setup, stop loading hooks now!
			if ( ! function_exists( $this->plugins->$plugin->active_check ) ) {
				continue;
			}

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
				call_user_func( array( $class, 'init' ) );
			}
		}
	}

}
