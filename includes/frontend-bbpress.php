<?php
/**
 * bbPress Mods
 *
 * The following are modifications that CBOX does to the bbPress plugin.
 *
 * @since 1.0.1
 *
 * @package Commons_In_A_Box
 * @subpackage Frontend
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// setup globals for bbPress
cbox()->plugins->bbpress = new stdClass;
cbox()->plugins->bbpress->is_setup = function_exists( 'bbp_activation' );

/**
 * Hotfixes and workarounds for bbPress.
 *
 * This class is autoloaded.
 *
 * @since 1.0.3
 */
class CBox_BBP_Autoload {
	/**
	 * Init method.
	 */
	public static function init() {
		new self();
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->is_site_public();

		$this->remove_dynamic_role_setter();

		$this->enable_visual_editor();

		$this->fix_form_actions();

		$this->save_notification_meta();

		$this->allow_revisions_during_edit();

		$this->bypass_link_limit();

		$this->show_notice_for_moderated_posts();
	}

	/**
	 * Changes how bbPress checks if a site is public.
	 *
	 * This class is autoloaded.
	 *
	 * If a WP site disables search engine indexing, no forum-related activity
	 * is recorded in BuddyPress.  Therefore, we force bbP so it's always public.
	 *
	 * @see https://bbpress.trac.wordpress.org/ticket/2151
	 */
	public function is_site_public() {
		add_filter( 'bbp_is_site_public', '__return_true' );
	}

	/**
	 * bbPress 2.2.x forces blog creators from the Administrator role to
	 * Participant, or to have no role at all.
	 *
	 * This is a hotfix to address bbPress 2.2.x; bbPress 2.3 fixes this.
	 *
	 * @see https://bbpress.trac.wordpress.org/ticket/2103
	 */
	public function remove_dynamic_role_setter() {
		if ( version_compare( bbp_get_version(), '2.3' ) < 0 ) {
			remove_action( 'switch_blog', 'bbp_set_current_user_default_role' );
		}
	}

	/** VISUAL EDITOR **************************************************/

	/**
	 * Re-enable TinyMCE in the forum textarea.
	 *
	 * bbPress 2.3 removed TinyMCE by default due to quirks in code formatting.
	 * We want to bring it back for backpat and UX reasons.
	 *
	 * @see https://github.com/cuny-academic-commons/commons-in-a-box/issues/76
	 */
	public function enable_visual_editor() {
		// create function to re-enable TinyMCE
		$enable_tinymce = function( $retval ) {
			// enable tinymce
			$retval["tinymce"] = true;

			// set teeny mode to false so we can use some additional buttons
			$retval["teeny"]   = false;

			// also manipulate some TinyMCE buttons
			CBox_BBP_Autoload::tinymce_buttons();

			return $retval;
		};

		// add our function to bbPress
		add_filter( 'bbp_after_get_the_content_parse_args', $enable_tinymce );
	}

	/**
	 * Add / remove buttons to emulate WP's TinyMCE 'teeny' mode for bbPress.
	 *
	 * Since the 'pasteword' button can only be used if 'teeny' mode is false,
	 * we need to remove a bunch of buttons from WP's regular post editor to
	 * emulate teeny mode.
	 *
	 * @see https://github.com/cuny-academic-commons/commons-in-a-box/issues/91
	 */
	public static function tinymce_buttons() {
		// create function to add / remove some TinyMCE buttons
		$buttons = function( $retval ) {
			global $wp_version;

			// remove some buttons to emulate teeny mode
			$retval = array_diff( $retval, array(
				"wp_more",
				"underline",
				"justifyleft",
				"justifycenter",
				"justifyright",
				"wp_adv"
			) );

			// add the pasteword plugin
			$paste = ( version_compare( $wp_version, "3.9" ) >= 0 ) ? "paste" : "pasteword";

			// add back undo / redo from teeny mode
			// bbPress adds the image button so we should do it as well
			array_push( $retval, "image", $paste, "undo", "redo" );

			return $retval;
		};

		// add our function to bbPress
		add_filter( 'mce_buttons',   $buttons, 20 );

		// wipe out the second row of TinyMCE buttons
		add_filter( 'mce_buttons_2', '__return_empty_array' );
	}

	/** FORM ACTIONS ***********************************************/

	/**
	 * Workaround for bbPress group form actions being wrong on BP 2.1 for bp-default derivatives.
	 *
	 * @since 1.0.9
	 */
	public function fix_form_actions() {
		add_action( 'bbp_locate_template', array( $this, 'fix_group_forum_action' ), 10, 2 );

		add_action( 'bbp_theme_before_topic_form', array( $this, 'remove_the_permalink_override' ) );
		add_action( 'bbp_theme_before_reply_form', array( $this, 'remove_the_permalink_override' ) );
	}

	/**
	 * Conditionally filter the_permalink to fix bbPress form actions.
	 *
	 * BP 2.1 breaks this functionality on bp-default-derivative themes.
	 *
	 * @param string $located       The full filepath to the located template.
	 * @param string $template_name The filename for the template.
	 */
	public function fix_group_forum_action( $located, $template_name ) {
		if ( version_compare( BP_VERSION, '2.1.0' ) < 0 ) {
			return;
		}

		if ( 'form-reply.php' !== $template_name && 'form-topic.php' !== $template_name ) {
			return;
		}

		if ( bp_is_group() && bp_is_current_action( 'forum' ) && ! bp_is_action_variable( 'edit', 2 ) ) {
			add_filter( 'the_permalink', array( $this, 'override_the_permalink_with_group_permalink' ) );
		}
	}

	/**
	 * Callback added in CBox_BBP_Autoload::fix_group_forum_action().
	 *
	 * @since 1.0.9
	 *
	 * @param string $retval Permalink string.
	 * @return string
	 */
	public function override_the_permalink_with_group_permalink( $retval = '' ) {
		return bp_get_group_permalink() . 'forum/';
	}

	/**
	 * Remove the group permalink override just after it's been applied.
	 *
	 * @since 1.0.9
	 */
	public function remove_the_permalink_override() {
		remove_filter( 'the_permalink', array( $this, 'override_the_permalink_with_group_permalink' ) );
	}

	/**
	 * Save various forum data to notification meta.
	 *
	 * Used on multisite installs to format forum notifications on sub-sites.
	 *
	 * @since 1.1.0
	 */
	public function save_notification_meta() {
		add_action( 'bp_notification_after_save', function( $n ) {
			// Bail if not on our bbPress new reply action or if notification is empty.
			if ( 'bbp_new_reply' !== $n->component_action || empty( $n->id ) ) {
				return;
			}

			// Save some meta.
			bp_notifications_update_meta( $n->id, 'cbox_bbp_reply_permalink', bbp_get_reply_url( $n->item_id ) );
			bp_notifications_update_meta( $n->id, 'cbox_bbp_topic_title',     bbp_get_topic_title( $n->item_id ) );
			bp_notifications_update_meta( $n->id, 'cbox_bbp_reply_topic_id',  bbp_get_reply_topic_id( $n->item_id ) );
		} );
	}

	/** ALLOW REVISIONS ************************************************/

	/**
	 * Bring back forum post edits to BP activity publishing.
	 *
	 * Requires temporarily enabling revisions for the current post type.
	 *
	 * Hotfix for {@link https://bbpress.trac.wordpress.org/ticket/3328}.
	 *
	 * @since 1.2.0
	 */
	public function allow_revisions_during_edit() {
		add_action( 'edit_post', function( $post_id, $post ) {
			$post_type = '';

			if ( get_post_type( $post ) === bbp_get_topic_post_type() ) {
				$post_type = 'topic';
			} elseif ( get_post_type( $post ) === bbp_get_reply_post_type() ) {
				$post_type = 'reply';
			}

			if ( '' === $post_type ) {
				return;
			}

			// See https://bbpress.trac.wordpress.org/ticket/3328.
			$GLOBALS[ '_wp_post_type_features' ][ $post_type ][ 'revisions' ] = true;

			// Pass the first revision check.
			add_filter( 'bbp_allow_revisions', '__return_true' );

			// Remove hack.
			add_filter( "bp_is_{$post_type}_anonymous", function( $retval ) use ( $post_type ) {
				remove_filter( 'bbp_allow_revisions', '__return_true' );
				unset( $GLOBALS[ '_wp_post_type_features' ][ $post_type ][ 'revisions' ] );

				return $retval;
			} );
		}, 9, 2 );
	}

	/** BYPASS LINK LIMIT **********************************************/

	/**
	 * Bypass link limit for logged-in users for bbPress 2.6.
	 *
	 * Hotfix for {@link https://bbpress.trac.wordpress.org/ticket/3352}.
	 *
	 * @since 1.2.0
	 */
	public function bypass_link_limit() {
		add_filter( 'bbp_bypass_check_for_moderation', function( $bool, $anon_data, $user_id, $title, $content, $strict ) {
			// If not checking for links or anonymous user, bail.
			if ( true === $strict || ! empty( $anon_data ) || empty( $user_id ) ) {
				return $bool;
			}

			$max = function() {
				return PHP_INT_MAX;
			};

			// Allow a lot of links :)
			add_filter( 'option_comment_max_links', $max );

			// Remove our link bypasser during the next filter check.
			add_filter( 'bbp_moderation_keys', function( $retval ) use ( $max ) {
				remove_filter( 'option_comment_max_links', $max );
				return $retval;
			} );

			return $bool;
		}, 10, 6 );
	}

	/**
	 * Add error messaging for moderated forum posts.
	 *
	 * We have to use BP's template notice routine because the template
	 * notices by bbPress aren't shown due to the redirection code in
	 * bbPress's BuddyPress group module.
	 *
	 * @since 1.2.0
	 */
	public function show_notice_for_moderated_posts() {
		// Notice callback anonymous function.
		$notice = function( $retval ) {
			if ( ! bp_is_group() || bbp_get_pending_status_id() !== $retval['post_status'] ) {
				return $retval;
			}

			// Todo: Should we let the user know why their post was auto-moderated?
			$msg = esc_html__( 'Your forum post is pending moderation', 'cbox' );
			bp_core_add_message( $msg, 'error' );

			return $retval;
		};

		add_filter( 'bbp_new_topic_pre_insert', $notice );
		add_filter( 'bbp_new_reply_pre_insert', $notice );
	}
}
