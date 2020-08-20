<?php
/**
 * OpenLab: Save upgrade flags into DB for fresh installations.
 *
 * @since 1.2.0
 */

/**
 * Set DB flags for fresh CBOX-OL installations.
 *
 * Fresh installations do not require upgrades, only required for older
 * CBOX-OL installs.
 *
 * @since 1.2.0
 */
add_action(
    'activated_plugin',
	function( $plugin ) {
        if ( 'cbox-openlab-core/cbox-openlab-core.php' !== $plugin ) {
            return;
        }

        // If CBOX-OL is installed already, bail.
        $ver = get_site_option( 'cboxol_ver' );
        if ( ! empty( $ver ) ) {
            return;
        }

        // Include autoloader.
        if ( ! interface_exists( '\CBOX\OL\ItemType', false ) ) {
            include_once CBOXOL_PLUGIN_DIR . 'autoload.php';
        }

        require_once CBOXOL_PLUGIN_DIR . 'includes/upgrades.php';

        $items = CBOX\Upgrades\Upgrade_Registry::get_instance()->get_all_registered();
        foreach ( $items as $item ) {
            if ( ! get_option( $item::FLAG, false ) ) {
                $item->finish();
            }
        }
    }
);
