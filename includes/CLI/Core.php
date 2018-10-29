<?php
namespace CBOX\CLI;

use WP_CLI;

/**
 * Updates and manages a CBOX installation.
 *
 * ## EXAMPLES
 *
 *     # Display the CBOX version
 *     $ wp cbox version
 *     1.0.15
 *
 * @package cbox
 */
class Core extends \WP_CLI_Command {
	/**
	 * Displays current CBOX status.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp cbox status
	 *     Current CBOX package: Classic
	 *
	 *     Current theme: Commons In A Box. No update available.
	 *
	 *     Active CBOX plugins are all up-to-date.
	 */
	public function status( $args, $assoc_args ) {
		if ( ! cbox_get_current_package_id() ) {
			// @todo Add 'wp cbox install' command of some sort.
			WP_CLI::error( 'A CBOX package is not active on the site.  Please install CBOX before running this command.' );
		}

		WP_CLI::line( 'Current CBOX package: ' . cbox_get_package_prop( 'name' ) );
		WP_CLI::line( '' );

		// Theme status.
		$theme = cbox_get_theme_to_update();
		if ( ! empty( $theme ) ) {
			WP_CLI::line( 'The theme has an update. Run "wp cbox update theme" to update the theme.' );
		} else {
			$cbox_theme_name    = cbox_get_theme_prop( 'name' );
			$current_theme_name = cbox_get_theme()->get( 'Name' );
			if ( $cbox_theme_name && $cbox_theme_name === $current_theme_name ) {
				WP_CLI::line( 'Current theme: ' . $current_theme_name . '. No update available.' );
			} elseif ( $cbox_theme_name ) {
				WP_CLI::line( 'Current theme: ' . $current_theme_name . '. The CBOX bundled theme, ' . $cbox_theme_name . ', is available, but not activated.' );
				WP_CLI::line( 'You can activate the theme by running "wp theme activate ' .  cbox_get_theme_prop( 'directory_name' ) . '"' );
			}
		}

		// Active plugin status.
		$plugins = \CBox_Admin_Plugins::get_upgrades( 'active' );
		if ( ! empty( $plugins ) ) {
			$items = array();

			WP_CLI::line( '' );
			WP_CLI::line( 'The following active plugins have an update available:' );

			$cbox_plugins = \CBox_Plugins::get_plugins();
			$dependencies = \CBox_Plugins::get_plugins( 'dependency' );

			foreach ( $plugins as $plugin ) {
				$loader = \Plugin_Dependencies::get_pluginloader_by_name( $plugin );
				$items[] = array(
					'Plugin'          => $plugin,
					'Current Version' => \Plugin_Dependencies::$all_plugins[$loader]['Version'],
					'New Version'     => isset( $cbox_plugins[$plugin]['version'] ) ? $cbox_plugins[$plugin]['version'] : $dependencies[$plugin]['version']
				);
			}

			WP_CLI\Utils\format_items( 'table', $items, array( 'Plugin', 'Current Version', 'New Version' ) );
			WP_CLI::line( '' );
			WP_CLI::line( 'Run "wp cbox update plugins" to update the plugins.' );
		} else {
			WP_CLI::line( '' );
			WP_CLI::line( 'Active CBOX plugins are all up-to-date.' );
		}
	}

	/**
	 * Displays the CBOX version.
	 *
	 * ## EXAMPLES
	 *
	 *     # Display the WordPress version
	 *     $ wp cbox version
	 *     1.0.15
	 */
	public function version( $args, $assoc_args ) {
		WP_CLI::line( cbox()->version );
	}
}
