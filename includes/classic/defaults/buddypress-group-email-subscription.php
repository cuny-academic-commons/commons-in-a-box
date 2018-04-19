<?php
/**
 * Classic: Set defaults for the GES plugin during initial activation.
 *
 * @since 1.1.0
 */

/*
 * BuddyPress' email taxonomy doesn't exist, so register it now.
 *
 * Because CBOX can bulk-activate plugins at the same time, we need to ensure
 * that we register BP's email taxonomy so GES can save its email situations
 * properly.  This issue only occurs when installing BuddyPress and GES during
 * the initial CBOX installation routine.
 */
if ( function_exists( 'bp_get_email_tax_type' ) && ! taxonomy_exists( bp_get_email_tax_type() ) ) {
	register_taxonomy(
		/** This filter is documented in /plugins/buddypress/class-buddypress.php */
		apply_filters( 'bp_email_tax_type', 'bp-email-type' ),
		/** This filter is documented in /plugins/buddypress/class-buddypress.php */
		apply_filters( 'bp_email_post_type', 'bp-email' )
	);
}
