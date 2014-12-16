<?php
/**
 * BuddyPress Group Email Subscription (GES) Mods
 *
 * The following are modifications that CBOX does to GES.
 *
 * @since 1.0-beta2
 *
 * @package Commons_In_A_Box
 * @subpackage Frontend
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// setup globals for GES
cbox()->plugins->ges = new stdClass;
cbox()->plugins->ges->is_setup = function_exists( 'ass_loader' );

/**
 * Changes the default group subscription to "All Mail" from "No Email".
 *
 * This class is autoloaded.
 *
 * @since 1.0-beta2
 */
class CBox_GES_All_Mail {
	public static function init() {
		new self();
	}

	public function __construct() {
		// changes the default group subscription level
		add_filter( 'ass_default_subscription_level', array( $this, 'default_group_email_setting' ), 20 );

		// changes the setting under a group's "Admin > Members"
		// specifically the "Site Admin Only" block
		add_filter( 'ass_get_default_subscription',   array( $this, 'default_group_email_setting' ) );
	}

	/**
	 * Changes the group subscription level to "All Mail" if group does not have
	 * a default subscription level set.
	 *
	 * ("supersub" = "All Mail")
	 */
	public function default_group_email_setting( $setting ) {
		if ( empty( $setting ) ) {
			return 'supersub';
		} else {
			return $setting;
		}
	}
}

/**
 * When activity is created by bbPress 2.x, swap out the BP activity content
 * with the reply/topic full text
 *
 * This class is loaded if enabled from the CBOX Settings page.
 *
 * @since 1.0-beta4
 */
class CBox_GES_bbPress2_Full_Text {
	public static function init() {
		new self();
	}

	public function __construct() {
		// Use the full text of forum topics and replies in bbPress 2.x
		add_filter( 'bp_ass_activity_notification_content', array( $this, 'full_text_bbpress2' ), 10, 2 );
	}

	/**
	 * When activity is created by bbPress 2.x, swap out the BP activity
	 * content with the reply/topic full text
	 *
	 * @since 1.0
	 *
	 * @param string $content The default content, from the BP activity item
	 * @param object $activity The BP_Activity_Activity data object
	 * @return string $content The updated content to be used in the email
	 */
	public function full_text_bbpress2( $content, $activity ) {
		// Sanity check: is bbPress running?
		if ( ! function_exists( 'bbp_get_reply_id' ) ) {
			return $content;
		}

		if ( 'bbp_reply_create' == $activity->type ) {
			$reply_id = bbp_get_reply_id( $activity->secondary_item_id );
			$content = get_post_field( 'post_content', $reply_id );
		} else if ( 'bbp_topic_create' == $activity->type ) {
			$topic_id = bbp_get_topic_id( $activity->secondary_item_id );
			$content = get_post_field( 'post_content', $topic_id );
		}

		return $content;
	}
}
