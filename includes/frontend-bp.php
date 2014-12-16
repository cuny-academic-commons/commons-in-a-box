<?php
/**
 * BuddyPress Mods
 *
 * If BuddyPress settings are toggled under the CBOX admin settings page,
 * setup the code for each setting here.
 *
 * @since 1.0-beta2
 *
 * @package Commons_In_A_Box
 * @subpackage Frontend
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// setup globals for BuddyPress
cbox()->plugins->bp = new stdClass;
cbox()->plugins->bp->is_setup = function_exists( 'bp_include' );

/**
 * Changes the default tab on a BP member page from 'Activity' to 'Profile'
 *
 * @since 1.0-beta2
 */
class CBox_BP_Profile_Tab {
	public static function init() {
		new self();
	}

	public function __construct() {
		if ( ! defined( 'BP_DEFAULT_COMPONENT' ) )
			define( 'BP_DEFAULT_COMPONENT', 'profile' );
	}
}

/**
 * Changes the default tab on a BP group page from 'Activity' to 'Forum'
 *
 * @since 1.0.5
 */
class CBox_BP_Group_Forum_Tab {
	public static function init() {
		new self();
	}

	public function __construct() {
		add_filter( 'bp_groups_default_extension', array( $this, 'set_group_default_tab' ), 99 );
	}

	/**
	 * Set the group default tab to 'forum' if the current group has a forum
	 * attached to it.
	 */
	public function set_group_default_tab( $retval ) {
		// check if bbPress or legacy forums are active and configured properly
		if ( ( function_exists( 'bbp_is_group_forums_active' ) && bbp_is_group_forums_active() ) ||
			( function_exists( 'bp_forums_is_installed_correctly' ) && bp_forums_is_installed_correctly() ) ) {

			// if current group does not have a forum attached, stop now!
			if ( ! bp_group_is_forum_enabled( groups_get_current_group() ) ) {
				return $retval;
			}

			// reconfigure the group's nav
			add_action( 'bp_actions', array( $this, 'config_group_nav' ) );

			// finally, use 'forum' as the default group tab
			return 'forum';
		}

		return $retval;
	}

	/**
	 * On the current group page, reconfigure the group nav when a forum is
	 * enabled for the group.
	 *
	 * What we do here is:
	 *  - move the 'Forum' tab to the beginning of the nav
	 *  - rename the 'Home' tab to 'Activity'
	 */
	public function config_group_nav() {
		$group_slug = bp_current_item();

		buddypress()->bp_options_nav[$group_slug]['forum']['position'] = 0;
		buddypress()->bp_options_nav[$group_slug]['home']['name']      = __( 'Activity', 'buddypress' );

	}
}
