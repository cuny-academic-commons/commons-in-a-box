<?php
/*
Plugin Name: Plugin Dependencies
Version: 1.3
Description: Prevent activating plugins that don't have all their dependencies satisfied
Author: scribu
Author URI: http://scribu.net/
Plugin URI: http://scribu.net/wordpress/plugin-dependencies
Text Domain: plugin-dependencies
Domain Path: /lang
*/

if ( !is_admin() )
	return;

add_filter( 'extra_plugin_headers', array( 'Plugin_Dependencies', 'extra_plugin_headers' ) );

class Plugin_Dependencies {
	private static $dependencies = array();
	private static $provides = array();
	private static $requirements = array();

	public static $all_plugins = array();
	public static $plugins_by_name = array();
	public static $active_plugins;

	private static $deactivate_cascade;
	private static $deactivate_conflicting;

	public static function extra_plugin_headers( $headers ) {
		$headers['Provides'] = 'Provides';
		$headers['Depends']  = 'Depends';
		$headers['Core']     = 'Core';

		return $headers;
	}

	public static function init() {
		global $wp_version;

		// setup $active_plugins variable
		// if we're in the network admin area, we check for sitewide plugins,
		// otherwise on single site, check the current site's plugins only
		self::$active_plugins = ! is_network_admin() ? get_option( 'active_plugins', array() ) : array_keys( get_site_option( 'active_sitewide_plugins', array() ) );

		// get all plugins
		self::$all_plugins = get_plugins();

		// setup associative array of plugins by name
		foreach ( self::$all_plugins as $plugin => $plugin_data ) {
			// we check for duplicate plugins here
			// and add only the plugin with the highest version number
			if ( ! empty( self::$plugins_by_name[ $plugin_data['Name'] ] ) ) {
				$self_plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . self::$plugins_by_name[ $plugin_data['Name'] ], false, false );

				// if plugin version in self::$plugins_by_name is older than the version in get_plugins()
				// replace it the newer version
				if ( version_compare( $self_plugin_data['Version'], $plugin_data['Version'] ) < 0 )
					self::$plugins_by_name[ $plugin_data['Name'] ] = $plugin;
			} else {
				self::$plugins_by_name[ $plugin_data['Name'] ] = $plugin;
			}

		}

		// parse dependencies for all installed plugins
		// note: the "scr_plugin_dependency_before_parse" filter is so plugins can inject
		//       their own dependencies before parsing begins
		foreach ( apply_filters( 'scr_plugin_dependency_before_parse', self::$all_plugins ) as $plugin => $plugin_data ) {
			// we check for duplicate plugin names here
			// and add only the plugin with the highest version number
			if ( ! empty( self::$plugins_by_name[ $plugin_data['Name'] ] ) ) {
				$self_plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . self::$plugins_by_name[ $plugin_data['Name'] ], false, false );

				if ( version_compare( $self_plugin_data['Version'], $plugin_data['Version'] ) < 0 )
					self::$plugins_by_name[ $plugin_data['Name'] ] = $plugin;
			} else {
				self::$plugins_by_name[ $plugin_data['Name'] ] = $plugin;
			}

			// parse "Provides" header from each plugin
			self::$provides[ $plugin ] = self::parse_field( $plugin_data['Provides'] );
			self::$provides[ $plugin ][] = $plugin;

			$deps = $requirements = array();

			// parse "Core" header from each plugin
			if ( ! empty( $plugin_data['Core'] ) ) {
				$requirement = false;

				// parse version dependency info
				$core_dependency = self::parse_dependency( 'Core (' . $plugin_data['Core'] . ')' );

				// see if the plugin's requested core version is incompatible
				$core_incompatible = self::check_incompatibility( $core_dependency, $wp_version );

				// if core is incompatible, add requirement
				if ( ! empty( $core_incompatible ) )  {
					$core_incompatible = rtrim( substr( $core_incompatible, 1 ), ')' );

					$requirements['core'] = $core_incompatible;
				}

			}

			// parse "Depends" header from each plugin
			foreach ( self::parse_field( $plugin_data['Depends'] ) as $dep ) {
				// a dependent name can contain a version number, so let's get just the name
				$plugin_name = rtrim( strtok( $dep, '(' ) );

				// see if plugin has any requirements
				$requirement = self::parse_requirements( $dep );

				// try to get the plugin loader file
				// perhaps remove the conditional?
				if ( self::get_pluginloader_by_name( $plugin_name ) )
					$dep = self::get_pluginloader_by_name( $plugin_name );

				if ( ! empty( $dep ) )
					$deps[] = $dep;

				if ( ! empty( $requirement ) )
					$requirements = array_merge_recursive( $requirements, $requirement );
			}

			if ( ! empty( $deps ) )
				self::$dependencies[ $plugin ] = $deps;

			if ( ! empty( $requirements ) )
				self::$requirements[ $plugin_data['Name'] ] = $requirements;
		}

		// allow plugins to filter dependencies and requirements
		self::$dependencies = apply_filters( 'scr_plugin_dependency_dependencies', self::$dependencies );
		self::$requirements = apply_filters( 'scr_plugin_dependency_requirements', self::$requirements );

		//var_dump( self::$dependencies );
		//var_dump( self::$requirements );
	}

	public static function parse_field( $str ) {
		return array_filter( preg_split( '/,\s*/', $str ) );
	}

	/**
	 * Parses a plugin name to see if requirements are needed.
	 *
	 * A plugin name can look like this:
	 * 	"BuddyPress"
	 *	"BuddyPress (>=1.5)"
	 *	"BuddyPress (>=1.2 / <1.6)"
	 *
	 * @param string $plugin_name The plugin name. Can include version dependencies. View PHPDoc for more info.
	 * @return mixed Array of requirements if plugin needs it. Boolean false if the plugin is good!
	 */
	public static function parse_requirements( $plugin_name = false, $active_check = false ) {
		// get full plugin before altering
		$full_plugin_name = $plugin_name;

		// a dependent plugin name can contain a version number, so let's get just the name
		$plugin_name = rtrim( strtok( $full_plugin_name, '(' ) );

		$requirements = false;

		// plugin is installed
		if ( isset( self::$plugins_by_name[ $plugin_name ] ) ) {

			// add loader file
			$loader = self::$plugins_by_name[ $plugin_name ];

			// parse version dependency info
			$dependency = self::parse_dependency( $full_plugin_name );

			// see if dependent plugin is incompatible
			$incompatible = self::check_incompatibility( $dependency, self::$all_plugins[ $loader ]['Version'] );

			// if dependent plugin is incompatible, add requirement
			if ( ! empty( $incompatible ) )  {

				$incompatible = rtrim( substr( $incompatible, 1 ), ')' );

				$requirements['incompatible'][0]['name'] = $plugin_name;
				$requirements['incompatible'][0]['compatible_version'] = $incompatible;
			}
			// else, check if dependent plugin is inactive; if so add requirement
			else {
				$active = true;

				// check network plugins first
				if ( is_multisite() && ! is_plugin_active_for_network( $loader ) ) {
					$active = false;
				}
				// single site
				elseif ( is_plugin_inactive( $loader ) ) {
					$active = false;
				}

				// add requirement if dependent plugin is inactive
				if ( ! $active ) {
					$requirements['inactive'][] = $plugin_name;
				}
			}
		}
		// plugin isn't installed
		else {

			// parse version dependency info
			$dependency = self::parse_dependency( $full_plugin_name );

			// add required version if available
			// this needs reworking
			if ( ! empty( $dependency ) && ! empty( $dependency['original_version'] ) ) {
				$requirements['version'] = rtrim( substr( $dependency['original_version'], 1 ), ')' );
			}

			$requirements['not-installed'][] = $plugin_name;
		}

		return $requirements;
	}

	/**
	 * Get a list of real or virtual dependencies for a plugin
	 *
	 * @param string $plugin_id A plugin basename
	 * @return array List of dependencies
	 */
	public static function get_dependencies( $plugin_id = false ) {
		if ( ! $plugin_id )
			return self::$dependencies;

		if( ! empty( self::$dependencies[ $plugin_id ] ) )
			return self::$dependencies[ $plugin_id ];

		return array();
	}

	/**
	 * Get a list of requirements
	 *
	 * @param string $plugin_name Plugin name
	 * @return array Associative array of requirements
	 */
	public static function get_requirements( $plugin_name = false ) {
		if ( ! $plugin_name )
			return self::$requirements;

		if ( ! empty( self::$requirements[ $plugin_name ] ) )
			return self::$requirements[ $plugin_name ];

		return array();
	}

	/**
	 * Get a list of dependencies provided by a certain plugin
	 *
	 * @param string $plugin_id A plugin basename
	 * @return array List of dependencies
	 */
	public static function get_provided( $plugin_id ) {
		return self::$provides[ $plugin_id ];
	}

	/**
	 * Get a list of plugins that provide a certain dependency
	 *
	 * @param string $dep Real or virtual dependency
	 * @return array List of plugins
	 */
	public static function get_providers( $dep ) {
		$plugin_ids = array();

		if ( isset( self::$provides[ $dep ] ) ) {
			$plugin_ids = array( $dep );
		} else {
			// virtual dependency
			foreach ( self::$provides as $plugin => $provides ) {
				if ( in_array( $dep, $provides ) ) {
					$plugin_ids[] = $plugin;
				}
			}
		}

		return $plugin_ids;
	}

	/**
	 * Get plugin loader by plugin name
	 *
	 * @param string $plugin_name A plugin name
	 * @return mixed String of loader on success, boolean false on failure
	 */
	public static function get_pluginloader_by_name( $plugin_name = false ) {
		if ( ! $plugin_name )
			return false;

		if ( isset( self::$plugins_by_name[ $plugin_name ] ) )
			return self::$plugins_by_name[ $plugin_name ];

		return false;
	}

	/**
	 * Deactivate plugins that would provide the same dependencies as the ones in the list
	 *
	 * @param array $plugin_ids A list of plugin basenames
	 * @return array List of deactivated plugins
	 */
	public static function deactivate_conflicting( $to_activate ) {
		$deps = array();
		foreach ( $to_activate as $plugin_id ) {
			$deps = array_merge( $deps, self::get_provided( $plugin_id ) );
		}

		$conflicting = array();

		$to_check = array_diff( self::$active_plugins, $to_activate );	// precaution

		foreach ( $to_check as $active_plugin ) {
			$common = array_intersect( $deps, self::get_provided( $active_plugin ) );

			if ( !empty( $common ) )
				$conflicting[] = $active_plugin;
		}

		// TODO: don't deactivate plugins that would still have all dependencies satisfied
		$deactivated = self::deactivate_cascade( $conflicting );

		deactivate_plugins( $conflicting, false, is_network_admin() );

		return array_merge( $conflicting, $deactivated );
	}

	/**
	 * Deactivate plugins that would have unmet dependencies
	 *
	 * @param array $plugin_ids A list of plugin basenames
	 * @return array List of deactivated plugins
	 */
	public static function deactivate_cascade( $to_deactivate ) {
		if ( empty( $to_deactivate ) )
			return array();

		self::$deactivate_cascade = array();

		self::_cascade( $to_deactivate );

		return self::$deactivate_cascade;
	}

	protected static function _cascade( $to_deactivate ) {
		$to_deactivate_deps = array();
		foreach ( $to_deactivate as $plugin_id )
			$to_deactivate_deps = array_merge( $to_deactivate_deps, self::get_provided( $plugin_id ) );

		$found = array();

		foreach ( self::$active_plugins as $dep ) {
			$matching_deps = array_intersect( $to_deactivate_deps, self::get_dependencies( $dep ) );
			if ( !empty( $matching_deps ) )
				$found[] = $dep;
		}

		$found = array_diff( $found, self::$deactivate_cascade ); // prevent endless loop
		if ( empty( $found ) )
			return;

		self::$deactivate_cascade = array_merge( self::$deactivate_cascade, $found );

		self::_cascade( $found );

		deactivate_plugins( $found, false, is_network_admin() );
	}

	/**
	 * Parses a dependency for comparison with {@link Plugin_Dependencies::check_incompatibility()}.
	 *
	 * @param $dependency
	 *   A dependency string, for example:
	 *    'foo (>=1.5)'
	 *    'foo (>=1.5 / <1.8)'
	 *
	 * @return
	 *   An associative array with three keys:
	 *   - 'name' includes the name of the thing to depend on (e.g. 'foo').
	 *   - 'original_version' contains the original version string (which can be
	 *     used in the UI for reporting incompatibilities).
	 *   - 'versions' is a list of associative arrays, each containing the keys
	 *     'op' and 'version'. 'op' can be one of: '=', '==', '!=', '<>', '<',
	 *     '<=', '>', or '>='. 'version' is one piece like '4.5-beta3'.
	 *   Callers should pass this structure to {@link Plugin_Dependencies::check_incompatibility()}.
	 *
	 * This function is based from Drupal's "drupal_parse_dependency()" function with a few mods.
	 * Drupal is licensed under the GPLv2 {@link http://api.drupal.org/api/drupal/LICENSE.txt/7}.
	 *
	 * @see {@link Plugin_Dependencies::check_incompatibility()}
	 */
	public static function parse_dependency( $dependency ) {
		// We use named subpatterns and support every op that version_compare
		// supports. Also, op is optional and defaults to equals.
		$p_op = '(?P<operation>!=|==|=|<|<=|>|>=|<>)?';

		$p_version = '(?P<version>[A-Za-z0-9_.-]+)';

		$value = array();
		$parts = explode( '(', $dependency, 2 );
		$value['name'] = trim( $parts[0] );

		if ( isset( $parts[1] ) ) {
			// version number with parentheses and operator
			// eg. (>=1.5)
			$value['original_version'] = '(' . $parts[1];

			foreach ( explode( '/', $parts[1] ) as $version ) {

				if ( preg_match( "/^\s*$p_op\s*$p_version/", $version, $matches ) ) {
					$op = !empty( $matches['operation'] ) ? $matches['operation'] : '=';

					$value['versions'][] = array(
						'op'      => $op,
						'version' => $matches['version']
					);
				}
			}
		}

		return $value;
	}

	/**
	 * Checks whether a version is compatible with a given dependency.
	 *
	 * @param $v
	 *   The parsed dependency structure from {@link Plugin_Dependencies::parse_dependency()}.
	 * @param $current_version
	 *   The version to check against (like 4.2).
	 *
	 * @return
	 *   NULL if compatible, otherwise the original dependency version string that
	 *   caused the incompatibility.
	 *
	 * This function is pretty much copied and pasted with love from Drupal's "drupal_check_incompatibility()" function.
	 * Drupal is licensed under the GPLv2 {@link http://api.drupal.org/api/drupal/LICENSE.txt/7}.
	 *
	 * @see Plugin_Dependencies::parse_dependency()
	 */
	public static function check_incompatibility( $v, $current_version ) {
		if ( !empty( $v['versions'] ) ) {
			foreach ( $v['versions'] as $required_version ) {
				if ( ( isset($required_version['op'] ) && !version_compare( $current_version, $required_version['version'], $required_version['op'] ) ) ) {
					return $v['original_version'];
				}
			}
		}
	}
}


add_action( 'load-plugins.php', array( 'Plugin_Dependencies_UI', 'init' ) );

class Plugin_Dependencies_UI {

	private static $msg;

	public static function init() {
		add_action( is_network_admin() ? 'network_' : '' . 'admin_notices', array( __CLASS__, 'admin_notices' ) );

		add_action( 'admin_print_styles', array( __CLASS__, 'admin_print_styles' ) );
		add_action( 'admin_print_footer_scripts', array( __CLASS__, 'footer_script' ), 20 );

		add_filter( is_network_admin() ? 'network_admin_' : '' . 'plugin_action_links', array( __CLASS__, 'plugin_action_links' ), 10, 4 );

		Plugin_Dependencies::init();

		// get requirements
		$requirements = Plugin_Dependencies::get_requirements();

		// add inline plugin error message if plugin hasn't met requirements yet
		if ( ! empty( $requirements ) ) {
			foreach ( $requirements as $plugin => $data ) {
				$loader = Plugin_Dependencies::get_pluginloader_by_name( $plugin );

				if ( ! empty( $loader ) ) {
					add_action( "after_plugin_row_{$loader}", array( __CLASS__, 'inline_plugin_error' ), 10, 3 );
				}
			}
		}

		// make sure you can't activate plugins that haven't met their requirements
		self::catch_bulk_activate();

		// localization
		load_plugin_textdomain( 'plugin-dependencies', '', dirname( plugin_basename( __FILE__ ) ) . '/lang' );

		self::$msg = array(
			array( 'deactivate', 'cascade', __( 'The following plugins have also been deactivated:', 'plugin-dependencies' ) ),
			array( 'activate', 'conflicting', __( 'The following plugins have been deactivated due to dependency conflicts:', 'plugin-dependencies' ) ),
		);

		if ( !isset( $_REQUEST['action'] ) )
			return;

		foreach ( self::$msg as $args ) {
			list( $action, $type ) = $args;

			$set_transient = is_network_admin() ? 'set_site_transient' : 'set_transient';

			if ( $action == $_REQUEST['action'] ) {
				$deactivated = call_user_func( array( 'Plugin_Dependencies', "deactivate_$type" ), (array) $_REQUEST['plugin'] );
				//var_dump( $deactivated ); var_dump( $_REQUEST['plugin'] ); die();
				$set_transient( "pd_deactivate_$type", $deactivated );
			}
		}
	}

	public static function admin_notices() {
		foreach ( self::$msg as $args ) {
			list( $action, $type, $text ) = $args;

			if ( !isset( $_REQUEST[ $action ] ) )
				continue;

			$get_transient = is_network_admin() ? 'get_site_transient' : 'get_transient';
			$deactivated = $get_transient( "pd_deactivate_$type" );

			$delete_transient = is_network_admin() ? 'delete_site_transient' : 'delete_transient';
			$delete_transient( "pd_deactivate_$type" );

			if ( empty( $deactivated ) )
				continue;

			echo
			html( 'div', array( 'class' => 'updated' ),
				html( 'p', $text, self::generate_dep_list( $deactivated ) )
			);
		}

		// do not show the block below if we return false for the 'pd_show_preactivation_warnings' hook
		if ( ! apply_filters( 'pd_show_preactivation_warnings', true ) )
			return;

		$requirements = Plugin_Dependencies::get_requirements();

		if ( ! empty( $requirements ) && empty( $_REQUEST['action'] ) ) {
			echo '<div class="plugin-warnings error"><h3>' . __( 'Pre-activation warnings', 'plugin-dependencies' ) . '</h3>';

			foreach ( $requirements as $plugin_name => $data ) {
				echo '<p id="warnings-' . sanitize_title( $plugin_name ) . '">' . sprintf( __( '%s requires the following issues to be addressed before it can be activated: ' , 'plugin-dependencies' ), "<strong>{$plugin_name}</strong>" ) . '</p><ul>';

				// @todo The following strings need to be better localized instead of using concatenation
				foreach ( $data as $state => $value ) {
					switch ( $state ) {
						case 'inactive' :
							foreach ( $value as $plugin ) {
								$loader = Plugin_Dependencies::get_pluginloader_by_name( $plugin );

								echo '<li>' . __( 'Unactivated plugin', 'plugin-dependencies' ) . ' - ' . $plugin;

								$activate_url = wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=' . $loader ), 'activate-plugin_' . $loader );

								echo ' <a href="' . $activate_url . '">' . __( '(Activate it now!)', 'plugin-dependencies' ) . '</a></li>';

							}

							break;

						case 'not-installed' :
							foreach ( $value as $plugin ) {
								echo '<li>' . __( 'Missing plugin', 'plugin-dependencies' ) . ' - ' . $plugin;

								echo ' ' . sprintf( __( '(<a href="%s">Try to find the plugin and install it here</a>)', 'plugin-dependencies' ), self_admin_url( 'plugin-install.php?tab=search&amp;type=term&s=' . $plugin ) );

								echo '</li>';

							}

							break;

						case 'incompatible' :
							foreach ( $value as $plugin ) {
								echo '<li>' . __( 'Incorrect plugin version installed', 'plugin-dependencies' ) . ' - ';

								if ( ! empty( $plugin['compatible_version'] ) )
									printf( __( '%s (Version %s required)', 'cbox' ), $plugin['name'], $plugin['compatible_version'] );
								else
									echo $plugin['name'];

								echo '</li>';

							}

							break;

						case 'core' :
							echo '<li>' . sprintf( __( 'WordPress version %s required', 'plugin-dependencies' ), $value );
							echo ' <a href="' . network_admin_url( 'update-core.php' ) . '">' . __( '(Upgrade now!)', 'plugin-dependencies' ) . '</a></li>';

							break;
					};
				}

				echo '</ul>';
			}

			echo '</div>';

		}
	}

	protected static function catch_bulk_activate() {
		$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );

		switch( $wp_list_table->current_action() ) {
			case 'activate-selected':

				check_admin_referer( 'bulk-plugins' );

				if( ! empty( $_POST['checked'] ) ) {
					// get requirements
					$requirements = Plugin_Dependencies::get_requirements();

					$loaders = array();

					if ( ! empty( $requirements ) ) {

						foreach ( $requirements as $plugin => $data ) {
							$loader = Plugin_Dependencies::get_pluginloader_by_name( $plugin );

							if ( ! empty( $loader ) ) {
								$loaders[] = $loader;
							}
						}
					}

					// only allow plugins that have met their requirements to be activated
					$_POST['checked'] = array_diff( $_POST['checked'], $loaders );
				}

				break;
		}
	}

	public static function inline_plugin_error( $plugin_file, $plugin_data, $status ) {
	?>
		<tr class="plugin-update-tr">
			<td class="plugin-update" colspan="3">
				<div class="update-message form-invalid">
					<?php printf( __( '"%s" cannot be activated. Before you can activate this plugin, please <a href="%s">address the issues listed here</a>.', 'plugin-dependencies' ), $plugin_data['Name'], '#warnings-' . sanitize_title( $plugin_data['Name'] ) ); ?>
				</div>
			</td>
		</tr>
	<?php
	}

	public static function admin_print_styles() {
?>
<style type="text/css">
.plugin-warnings ul {margin:.5em 0 1.5em 1em;}
.dep-list li, .plugin-warnings li { list-style: disc inside none }
span.deps li.unsatisfied { color: red }
span.deps li.unsatisfied_network { color: orange }
span.deps li.satisfied { color: green }
</style>
<?php
	}

	public static function footer_script() {
		$all_plugins = get_plugins();

		$hash = array();
		foreach ( $all_plugins as $file => $data ) {
			$name = isset( $data['Name'] ) ? $data['Name'] : $file;
			$hash[ $name ] = sanitize_title( $name );
		}

?>
<script type="text/javascript">
jQuery(function($) {
	var hash = <?php echo json_encode( $hash ); ?>

	$('table.widefat tbody tr').not('.second').each(function() {
		var $self = $(this), title = $self.find('.plugin-title').text();

		$self.attr('id', hash[title]);
	});
});
</script>
<?php
	}

	public static function plugin_action_links( $actions, $plugin_file, $plugin_data, $context ) {
		// get requirements
		$requirements = Plugin_Dependencies::get_requirements();

		// if current plugin has requirements that are unmet, then get rid of the activation link
		if ( !empty( $requirements[ $plugin_data['Name'] ] ) ) {
			unset( $actions['activate'] );
		}

		return $actions;
	}

	protected static function generate_dep_list( $deps, $unsatisfied = array(), $unsatisfied_network = array() ) {
		$all_plugins = Plugin_Dependencies::$all_plugins;

		$dep_list = '';
		foreach ( $deps as $dep ) {
			$plugin_ids = Plugin_Dependencies::get_providers( $dep );

			if ( in_array( $dep, $unsatisfied ) )
				$class = 'unsatisfied';
			elseif ( in_array( $dep, $unsatisfied_network ) )
				$class = 'unsatisfied_network';
			else
				$class = 'satisfied';

			if ( empty( $plugin_ids ) ) {
				$name = html( 'span', esc_html( $dep['Name'] ) );
			} else {
				$list = array();
				foreach ( $plugin_ids as $plugin_id ) {
					$name = isset( $all_plugins[ $plugin_id ]['Name'] ) ? $all_plugins[ $plugin_id ]['Name'] : $plugin_id;
					$list[] = html( 'a', array( 'href' => '#' . sanitize_title( $name ) ), $name );
				}
				$name = implode( ' or ', $list );
			}

			$dep_list .= html( 'li', compact( 'class' ), $name );
		}

		return html( 'ul', array( 'class' => 'dep-list' ), $dep_list );
	}
}


if ( ! function_exists( 'html' ) ):
function html( $tag ) {
	$args = func_get_args();

	$tag = array_shift( $args );

	if ( is_array( $args[0] ) ) {
		$closing = $tag;
		$attributes = array_shift( $args );
		foreach ( $attributes as $key => $value ) {
			if ( false === $value )
				continue;

			if ( true === $value )
				$value = $key;

			$tag .= ' ' . $key . '="' . esc_attr( $value ) . '"';
		}
	} else {
		list( $closing ) = explode( ' ', $tag, 2 );
	}

	if ( in_array( $closing, array( 'area', 'base', 'basefont', 'br', 'hr', 'input', 'img', 'link', 'meta' ) ) ) {
		return "<{$tag} />";
	}

	$content = implode( '', $args );

	return "<{$tag}>{$content}</{$closing}>";
}
endif;
