<?php
/**
 * BuddyPress Group Email Subscription Mods
 *
 * If GES settings are toggled under the CBOX admin settings page,
 * setup the code for each setting here.
 *
 * @since 1.0-beta2
 *
 * @package Commons_In_A_Box
 * @subpackage Frontend
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// setup globals for GES
cbox()->frontend->ges = new stdClass;
cbox()->frontend->ges->is_setup = function_exists( 'ass_loader' );

/**
 * Changes the default group subscription to "All Mail" from "No Email"
 *
 * @since 1.0-beta2
 */
class CBox_GES_All_Mail {
	public static function init() {
		new self();
	}

	public function __construct() {
		// changes the default group subscription level
		add_filter( 'ass_default_subscription_level', array( $this, 'default_group_email_setting' ) );

		// changes the setting under a group's "Admin > Members"
		// specifically the "Site Admin Only" block
		add_filter( 'ass_get_default_subscription',   array( $this, 'default_group_email_setting' ) );
	}

	/**
	 * Changes the group subscription level to "All Mail".
	 * "supersub" = "All Mail"
	 */
	public function default_group_email_setting( $setting ) {
		return 'supersub';
	}
}

