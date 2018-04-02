<?php
namespace CBOX\CLI;

use WP_CLI;

/**
 * Commands applicable to a CBOX package.
 *
 * ## EXAMPLES
 *
 *     # List the available CBOX packages.
 *     $ wp cbox package list
 *
 * @package cbox
 */
class Package extends \WP_CLI_Command {
	/**
	 * Lists all available CBOX packages.
	 *
	 * ## OPTIONS
	 *
	 * [--fields=<fields>]
	 * : Limit the output to specific fields.
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - csv
	 *   - json
	 *   - yaml
	 * ---
	 *
	 * ## AVAILABLE FIELDS
	 *
	 * These fields will be displayed by default for each CBOX package:
	 *
	 * * Package
	 * * Name
	 * * Theme
	 * * Active
	 *
	 * These fields are optionally available:
	 *
	 * * Description
	 *
	 * ## EXAMPLES
	 *
	 *     # Lists all available CBOX packages.
	 *     $ wp cbox package list
	 *     +---------+---------+---------------+--------+
	 *     | Package | Name    | Theme         | Active |
	 *     +---------+---------+---------------+--------+
	 *     | classic | Classic | cbox-theme    | No     |
	 *     | openlab | OpenLab | openlab-theme | Yes    |
	 *     +---------+---------+---------------+--------+
	 *
	 * @subcommand list
	 */
	public function list_( $args, $assoc_args ) {
		$packages = cbox_get_packages();

		$r = array_merge( array(
			'format' => 'table',
			'fields' => array( 'Package', 'Name', 'Theme', 'Active' )
		), $assoc_args );

		if ( ! is_array( $r['fields'] ) ) {
			$r['fields'] = explode( ',', $r['fields'] );
		}

		// Rare that this will happen, but sanity check!
		if ( empty( $packages ) ) {
			WP_CLI::error( 'No CBOX packages are available.' );
		}

		$items = array();
		$i = 0;
		$description_enabled = array_search( 'Description', $r['fields'] );
		foreach ( $packages as $package => $class ) {
			$theme = cbox_get_theme_prop( 'directory_name', $package );

			$items[$i] = array(
				'Package'     => $package,
				'Name'        => cbox_get_package_prop( 'name', $package ),
				'Theme'       => $theme ? $theme : 'No theme available',
				'Active'      => cbox_get_current_package_id() === $package ? 'Yes' : 'No'
			);

			if ( $description_enabled ) {
				// Description is stored in template part.
				ob_start();
				cbox_get_template_part( 'description', $package );
				$description = ob_get_clean();
				$description = strip_tags( $description );
				$description = str_replace( array( "\t", "\n" ), ' ', $description );
				$description = trim( $description );

				$items[$i]['Description'] = $description;
			}

			++$i;
		}

		WP_CLI\Utils\format_items( $r['format'], $items, $r['fields'] );
	}
}
