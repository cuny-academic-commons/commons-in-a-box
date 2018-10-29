<?php
/**
 * OpenLab: Set defaults for the BuddyPress plugin during initial activation.
 *
 * @since 1.1.0
 */

/**
 * Things to do after BuddyPress is activated.
 *
 * - Don't let BuddyPress redirect to its about page after activating.
 * - Install all BuddyPress components.
 *
 * @since 1.1.0 This logic was moved out of the CBOX plugin-install.php file.
 */
add_action( 'activated_plugin', function( $plugin ) {
	if ( 'buddypress/bp-loader.php' !== $plugin ) {
		return;
	}

	// Don't let BuddyPress redirect to its about page after activating.
	delete_transient( '_bp_activation_redirect' );

	// Register email taxonomy manually, since it's needed for BP emails.
	register_taxonomy(
		/** This filter is documented in /plugins/buddypress/class-buddypress.php */
		apply_filters( 'bp_email_tax_type', 'bp-email-type' ),
		/** This filter is documented in /plugins/buddypress/class-buddypress.php */
		apply_filters( 'bp_email_post_type', 'bp-email' )
	);

	// Run BP version updater routine with all BP components installed.
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

	require_once buddypress()->plugin_dir . '/bp-members/bp-members-template.php';
	require_once buddypress()->plugin_dir . '/bp-core/bp-core-update.php';
	bp_version_updater();
} );