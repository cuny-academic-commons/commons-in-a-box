<?php
/**
 * Set up plugin management
 *
 * @package Commons_In_A_Box
 * @subpackage Plugins
 * @since 0.1
 */

class CIAB_Plugins {
	var $manifest_directory;

	var $ciab_plugins      = array();
	var $installed_plugins = array();

	public function __construct() {

		// add the TGMPA library
		require_once( CIAB_LIB_DIR . 'tgm-plugin-activation/class-tgm-plugin-activation.php' );

		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$this->manifest_directory = trailingslashit( CIAB_PLUGIN_DIR . 'plugins/manifests' );
		$this->setup_hooks();
	}

	private function setup_hooks() {
		add_action( 'plugins_loaded', array( &$this, 'check_dependencies' ) );

		// add required plugins with the TGMPA library
		add_action( 'tgmpa_register', array( &$this, 'register_required_plugins' ) );
	}

	/**
	 * Register the required plugins with the TGMPA library.
	 *
	 * The variable passed to tgmpa_register_plugins() should be an array of plugin
	 * arrays.
	 *
	 * This function is hooked into tgmpa_init, which is fired within the
	 * TGM_Plugin_Activation class constructor.
	 */
	public function register_required_plugins() {

		/**
		 * Array of plugin arrays. Required keys are name and slug.
		 */
		$plugins = array(

			// an example of how to include a plugin from the WordPress Plugin Repository
			array(
				'name'         => 'Jigoshop',
				'slug'         => 'jigoshop',
				//'version'      => '1.2',    // the active plugin must be this version or higher, otherwise a notice is presented
				'required'     => false,
			),

			// an example of using an older version of a plugin from the WP.org plugin repo
			array(
				'name'         => 'BuddyPress Docs',
				'slug'         => 'buddypress-docs',
				'external_url' => 'http://downloads.wordpress.org/plugin/buddypress-docs.1.1.19.zip',
				'required'     => true,
			),
		);

		// Our textdomain
		$textdomain = 'cbox';

		/**
		 * Array of configuration settings. Amend each line as needed.
		 * If you want the default strings to be available under your own theme domain,
		 * leave the strings uncommented.
		 * Some of the strings are added into a sprintf, so see the comments at the
		 * end of each line for what each argument will be.
		 */
		$config = array(
			'domain'           => $textdomain,                     // Text domain
			'default_path'     => '',                              // Default absolute path to pre-packaged plugins
			'parent_menu_slug' => 'ciab',                          // Default parent menu slug
			'parent_url_slug'  => 'admin.php?page=ciab',           // Default parent URL slug
			'menu'             => 'ciab-install-required-plugins', // Menu slug
			'has_notices'      => true,                            // Show admin notices or not
			'is_automatic'     => false,                            // Automatically activate plugins after installation or not
			'message'          => '',                              // Message to output right before the plugins table
			'strings'          => array(
				'page_title'                     => __( 'Commons in a Box - Install Required Plugins', $textdomain ),
				'menu_title'                     => __( 'Install Plugins', $textdomain ),
				'installing'                     => __( 'Installing Plugin: %s', $textdomain ), // %1$s = plugin name
				'notice_can_install_required'    => _n_noop( 'Commons in a Box requires the following plugin: %1$s.', 'Commons in a Box requires the following plugins: %1$s.' ), // %1$s = plugin name(s)
				'notice_can_install_recommended' => _n_noop( 'Commons in a Box recommends the following plugin: %1$s.', 'Commons in a Box recommends the following plugins: %1$s.' ), // %1$s = plugin name(s)
				'install_link'                   => _n_noop( 'Begin installing plugin', 'Begin installing plugins' ),
				'activate_link'                  => _n_noop( 'Activate installed plugin', 'Activate installed plugins' ),
				'return'                         => __( 'Return to Required Plugins Installer', $textdomain ),
				'plugin_activated'               => __( 'Plugin activated successfully.', $textdomain ),
				'complete'                       => __( 'All plugins installed and activated successfully. %s', $textdomain ), // %1$s = dashboard link
				'nag_type'                       => 'updated' // Determines admin notice type - can only be 'updated' or 'error'
			)
		);

		tgmpa( $plugins, $config );

	}

	/**
	 * @todo This is heavy duty. Should only run in admin
	 * @todo This must be cached somehow
	 */
	public function check_dependencies() {
		if ( $handle = opendir( $this->manifest_directory ) ) {

			while ( false !== ( $entry = readdir( $handle ) ) ) {
				if ( 'info' == substr( strrchr( $entry, '.' ), 1 ) ) {
					$plugin_data = $this->parse_info_file( file_get_contents( $this->manifest_directory . $entry ) );

					if ( isset( $plugin_data['file'] ) ) {
						$this->ciab_plugins[$plugin_data['file']] = $plugin_data;
					}

				}
			}

			closedir($handle);
		}

		// Set up installed plugins with a similar data structure
		$this->installed_plugins = $this->setup_installed_plugins();

		// Map dependencies. WP core dependencies can probably be separate
		// Organize by plugins w/dependencies, or by plugins required?

		// Look for dependencies that are not met by existing items:
		// - WP core version
		// - existence of parent plugins
		// - version of parent plugins

		// How do we prevent regresses?

		// Load user preferences (activated components)
		// Re-index dependencies based on active/non-active items
		// - existence of parent plugins
		// - version of parent plugins
		// - whether parent plugins are activated/CIAB setting is configured

		//var_dump( $this );
	}

	/**
	 * Pretty much stolen from Drupal's drupal_parse_info_file()
	 */
	private function parse_info_file( $data ) {
		$info = array();
		$constants = get_defined_constants();

		if (preg_match_all('
		@^\s*                           # Start at the beginning of a line, ignoring leading whitespace
		((?:
		[^=;\[\]]|                    # Key names cannot contain equal signs, semi-colons or square brackets,
		\[[^\[\]]*\]                  # unless they are balanced and not nested
		)+?)
		\s*=\s*                         # Key/value pairs are separated by equal signs (ignoring white-space)
		(?:
		("(?:[^"]|(?<=\\\\)")*")|     # Double-quoted string, which may contain slash-escaped quotes/slashes
		(\'(?:[^\']|(?<=\\\\)\')*\')| # Single-quoted string, which may contain slash-escaped quotes/slashes
		([^\r\n]*?)                   # Non-quoted string
		)\s*$                           # Stop at the next end of a line, ignoring trailing whitespace
		@msx', $data, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				// Fetch the key and value string.
				$i = 0;
				foreach (array('key', 'value1', 'value2', 'value3') as $var) {
					$$var = isset($match[++$i]) ? $match[$i] : '';
				}
				$value = stripslashes(substr($value1, 1, -1)) . stripslashes(substr($value2, 1, -1)) . $value3;

				// Parse array syntax.
				$keys = preg_split('/\]?\[/', rtrim($key, ']'));
				$last = array_pop($keys);
				$parent = &$info;

				// Create nested arrays.
				foreach ($keys as $key) {
					if ( 'dependencies' == $key ) {
						//var_dump( 'hr' );
					}

					if (!isset($parent[$key]) || !is_array($parent[$key])) {
						$parent[$key] = array();
					}
					$parent = &$parent[$key];
				}

				// Handle PHP constants.
				if (isset($constants[$value])) {
					$value = $constants[$value];
				}

				// Insert actual value.
				if ($last == '') {
					$last = count($parent);
				}

				if ( 'dependencies' == $last ) {
					// todo: do a better job at this
					$value = preg_split( '|\s+|', $value );

				}

				$parent[$last] = $value;
			}
		}

		return $info;
	}

	private function setup_installed_plugins() {
		$installed_plugins = get_plugins();
		$ip = array();

		foreach( (array)$installed_plugins as $file => $plugin_data ) {
			$ip[$file] = array(
				'id' => $file, // todo - parse this out? or strtolower? ugh
				'name' => $plugin_data['Name'],
				'file' => $file,
				'version' => $plugin_data['Version']
			);
		}

		return $ip;
	}
}

?>