<?php
/**
 * OpenLab: Set defaults for the BuddyPress plugin during initial activation.
 *
 * @since 1.1.0
 */

/**
 * Return an array of all BP components to be activated by default.
 *
 * Since BuddyPress 1.7, BP has only activated the Profile and Activity
 * components on new installations. For Commons In A Box, we want to
 * keep the old behavior of turning all components on.
 *
 * @since 1.1.0 This logic was moved out of the CBOX plugins.php file.
 */
add_filter( 'bp_new_install_default_components', function( $retval ) {
	return array(
		'activity'      => 1,
		'blogs'         => 1,
		'friends'       => 1,
		'groups'        => 1,
		'members'       => 1,
		'messages'      => 1,
		'notifications' => 1,
		'settings'      => 1,
		'xprofile'      => 1,
	);
} );

/**
 * Don't let BuddyPress redirect to its about page after activating.
 *
 * @since 1.1.0 This logic was moved out of the CBOX plugin-install.php file.
 */
add_action( 'activated_plugin', function( $plugin ) {
	if ( 'buddypress/bp-loader.php' !== $plugin ) {
		return;
	}

	delete_transient( '_bp_activation_redirect' );
} );