<?php
namespace CBOX\CLI;

use WP_CLI;

/**
 * Commands applicable to updating CBOX.
 *
 * ## EXAMPLES
 *
 *     # Updates the CBOX theme.
 *     $ wp cbox update theme
 *
 * @package cbox
 */
class Update extends \WP_CLI_Command {
	/**
	 * Updates the CBOX plugins and theme, if applicable.
	 *
	 * ## EXAMPLES
	 *
	 *     # Updates the CBOX plugins and theme.
	 *     $ wp cbox update all
	 */
	public function all( $args, $assoc_args ) {
		WP_CLI::runcommand( 'cbox update plugins --yes' );

		WP_CLI::line( 'Updating theme...' );
		WP_CLI::runcommand( 'cbox update theme' );
	}

	/**
	 * Updates the CBOX theme.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp cbox update theme
	 *     Downloading install package from http://github.com/cuny-academic-commons/cbox-theme/archive/1.0.15.zip...
	 *     Unpacking the package...
	 *     Installing the theme...
	 *     Renamed Github-based project from 'cbox-theme-1.0.15' to 'cbox-theme'.
	 *     Removing the old version of the theme...
	 *     Theme updated successfully.
	 *     Success: Installed 1 of 1 themes.
	 */
	public function theme( $args, $assoc_args ) {
		// check for theme upgrades
		$theme = cbox_get_theme_to_update();
		if ( empty( $theme ) ) {
			$cbox_theme_name    = cbox_get_theme_prop( 'name' );
			$current_theme_name = cbox_get_theme()->get( 'Name' );
			if ( $cbox_theme_name && $cbox_theme_name === $current_theme_name ) {
				WP_CLI::success( 'You are already running the latest version of the theme, ' . cbox_get_theme_prop( 'directory_name' ) );
			} else {
			}

			return;
		}

		// Sanity check.
		if ( $theme !== cbox_get_theme_prop( 'directory_name' ) ) {
			WP_CLI::error( 'Package theme does not match' );
		}

		// Run the update, using WP-CLI's native 'theme' command.
		WP_CLI::runcommand( 'theme install ' . cbox_get_theme_prop( 'download_url' ) . ' --force' );
	}

	/**
	 * Updates all active CBOX plugins.
	 *
	 * [--yes]
	 * : Answer yes to the confirmation message.
	 *
	 * ## EXAMPLES
	 *
	 *     # Updates the CBOX plugins, but will ask for confirmation before doing so.
	 *     $ wp cbox update plugins
	 *     Attempting to update the following plugins:
	 *     +----------------------+-------------+-------------+
	 *     | Plugin               | Old Version | New Version |
	 *     +----------------------+-------------+-------------+
	 *     | BP Group Documents   | 1.12.0      | 1.12.1      |
	 *     | CAC Featured Content | 1.0.8       | 1.0.9       |
	 *     +----------------------+-------------+-------------+
	 *     Do you want to continue? [y/n] y
	 *     Downloading installation package from http://downloads.wordpress.org/plugin/bp-group-documents.1.12.1.zip...
	 *     Unpacking the package...
	 *     Installing the plugin...
	 *     Removing the old version of the plugin...
	 *     Plugin updated successfully.
	 *     Downloading installation package from http://downloads.wordpress.org/plugin/cac-featured-content.1.0.9.zip...
	 *     Unpacking the package...
	 *     Installing the plugin...
	 *     Removing the old version of the plugin...
	 *     Plugin updated successfully.
	 *     Success: Installed 2 of 2 plugins.
	 *
	 *     # Updates the CBOX plugins, without confirmation.
	 *     $ wp cbox update plugins --yes
	 */
	public function plugins( $args, $assoc_args ) {
		$plugins = \CBox_Admin_Plugins::get_upgrades( 'active' );

		if ( empty( $plugins ) ) {
			WP_CLI::line( 'All active plugins are already up-to-date.' );
			return;
		}

		WP_CLI::line( 'Attempting to update the following plugins:' );

		$cbox_plugins = \CBox_Plugins::get_plugins();
		$dependencies = \CBox_Plugins::get_plugins( 'dependency' );
		$urls = array();

		foreach ( $plugins as $plugin ) {
			$loader = \Plugin_Dependencies::get_pluginloader_by_name( $plugin );
			$items[$plugin] = array(
				'Plugin'      => $plugin,
				'Old Version' => \Plugin_Dependencies::$all_plugins[$loader]['Version'],
				'New Version' => isset( $cbox_plugins[$plugin]['version'] ) ? $cbox_plugins[$plugin]['version'] : $dependencies[$plugin]['version']
			);
			$urls[] = $cbox_plugins[$plugin]['download_url'];
		}

		// Output plugin table.
		WP_CLI\Utils\format_items( 'table', $items, array( 'Plugin', 'Old Version', 'New Version' ) );

		// Confirmation prompt, if necessary.
		WP_CLI::confirm( 'Do you want to continue?', $assoc_args );

		// Run the updater.
		WP_CLI::runcommand( 'plugin install ' . implode( ' ', $urls ) . ' --force' );
	}
}