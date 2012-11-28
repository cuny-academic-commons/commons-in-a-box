<?php
/**
 * BuddyPress Group Email Subscription Mods
 *
 * If GES settings are toggled under the CBOX admin settings page,
 * setup the code for each setting here.
 *
 * @package Commons_In_A_Box
 * @subpackage Frontend
 * @since 1.0-beta2
 */

// setup globals for GES
cbox()->frontend->ges = new stdClass;
cbox()->frontend->ges->is_setup = function_exists( 'ass_loader' );

/**
 * Changes the default group subscription to "All Mail" from "No Email"
 *
 * @since 1.0-beta2
 * @todo Not working properly... see inline doc.
 */
class CBox_GES_All_Mail {
	public static function &init() {
		new self();
	}

	public function __construct() {
		// changes the default group subscription level
		// doesn't work! :(
		add_filter( 'ass_default_subscription_level', array( $this, 'default_group_email_setting' ) );
		
		// this works, but only changes the setting under a group's "Admin > Members"
		// specifically the "Site Admin Only" block
		// this is here temporarily just to prove that filters work!
		add_filter( 'ass_get_default_subscription',   array( $this, 'default_group_email_setting' ) );
	}

	/**
	 * This isn't working at the moment and I have no idea why!
	 *
	 * Probably has something to do with the action / code firing order.
	 * Works in bp-custom.php though...
	 */
	public function default_group_email_setting( $setting ) {
		return 'supersub';
	}
}

