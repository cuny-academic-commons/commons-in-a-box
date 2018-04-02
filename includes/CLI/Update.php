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
		//WP_CLI::line( 'Updating plugins...' );
		//WP_CLI::runcommand( 'cbox update plugins' );

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
}