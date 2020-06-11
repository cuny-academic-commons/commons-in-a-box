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
			$cbox_theme    = cbox_get_package_prop( 'theme' );
			$current_theme = cbox_get_theme();

			if ( $cbox_theme['name'] && $cbox_theme['directory_name'] === $current_theme->get_template() ) {
				WP_CLI::line( 'Current theme: ' . $cbox_theme['name'] . '. No update available.' );
			} elseif ( $cbox_theme['name'] ) {
				WP_CLI::line( 'Current theme: ' . $current_theme->get( 'Name' ) . '. The CBOX bundled theme, ' . $cbox_theme['name'] . ', is available, but not activated.' );
				WP_CLI::line( 'You can activate the theme by running "wp theme activate ' .  $cbox_theme['directory_name'] . '"' );
			}
		}

		// Active plugin status.
		$plugins = \CBox_Admin_Plugins::get_upgrades( 'active' );
		$show_plugin_notice = $show_active_notice = false;
		if ( ! empty( $plugins ) ) {
			$show_plugin_notice = true;

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
		} else {
			$show_active_notice = true;
		}

		// Required plugins check.
		if ( ! isset( $cbox_plugins ) ) {
			$cbox_plugins = \CBox_Plugins::get_plugins( 'required' );
		} else {
			$cbox_plugins = $cbox_plugins['required'];
		}

		$required = \CBox_Admin_Plugins::organize_plugins_by_state( $cbox_plugins );
		unset( $required['deactivate'] );

		if ( ! empty( $required ) ) {
			$show_plugin_notice = true;

			$items = array();

			WP_CLI::line( '' );
			WP_CLI::line( 'The following plugins are required and need to be either activated or installed:' );

			if ( ! isset( $dependencies ) ) {
				$dependencies = \CBox_Plugins::get_plugins( 'dependency' );
			}

			foreach ( $required as $state => $plugins ) {
				switch ( $state ) {
					case 'activate' :
						$action = 'Requires activation';
						break;

					case 'install' :
						$action = 'Requires installation';
						break;
				}
				foreach ( $plugins as $plugin ) {
					$loader = \Plugin_Dependencies::get_pluginloader_by_name( $plugin );
					$items[] = array(
						'Plugin'  => $plugin,
						'Version' => isset( $cbox_plugins[$plugin]['version'] ) ? $cbox_plugins[$plugin]['version'] : $dependencies[$plugin]['version'],
						'Action'  => $action
					);
				}
			}

			WP_CLI\Utils\format_items( 'table', $items, array( 'Plugin', 'Version', 'Action' ) );
		}

		if ( $show_plugin_notice ) {
			WP_CLI::line( '' );
			WP_CLI::line( 'Run "wp cbox update plugins" to update the plugins.' );
		} elseif ( $show_active_notice ) {
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
