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

		$this->get_activity_id_hotfix();

		$this->fix_form_actions();
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
		$enable_tinymce = create_function( '$retval', '
			// enable tinymce
			$retval["tinymce"] = true;

			// set teeny mode to false so we can use some additional buttons
			$retval["teeny"]   = false;

			// also manipulate some TinyMCE buttons
			CBox_BBP_Autoload::tinymce_buttons();

			return $retval;
		' );

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
		$buttons = create_function( '$retval', '
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
		' );

		// add our function to bbPress
		add_filter( 'mce_buttons',   $buttons, 20 );

		// wipe out the second row of TinyMCE buttons
		add_filter( 'mce_buttons_2', create_function( '', "return array();" ) );
	}

	/**
	 * Hotfix for bbPress activity ID fetching.
	 *
	 * Changes to BuddyPress in v2.1 to remove the activity count from activity
	 * queries broke how bbPress determines what the existing activity ID is.
	 *
	 * This is a hotfix to add the new 'count_total' parameter added in BP 2.1, so
	 * bbPress can accurately fetch the correct activity ID.  This prevents
	 * duplicate activity entries when editing a bbPress forum post.
	 *
	 * This should hopefully be addressed in bbPress 2.6. Remove this in CBOX 1.1.
	 *
	 * @since 1.0.9
	 *
	 * @see BBP_BuddyPress_Activity::get_activity_id()
	 * @see https://bbpress.trac.wordpress.org/ticket/2690
	 */
	public function get_activity_id_hotfix() {
		// stop if BP is lower than 2.1
		if ( version_compare( BP_VERSION, '2.1.0' ) < 0 ) {
		        return;
		}

		// we're hoping a fix is going to be ready by bbP 2.6
		if ( version_compare( bbp_get_version(), '2.6' ) >= 0 ) {
		        return;
		}

		add_filter( 'get_post_metadata', array( $this, 'add_activity_get_specific_hotfix' ), 10, 3 );
		add_filter( 'bp_activity_total_activities_sql', array( $this, 'remove_activity_get_specific_hotfix' ) );
	}

	/**
	 * Add filter to bp_activity_get_specific() to add 'count_total' parameter.
	 *
	 * Before bbPress determines what the activity ID is, it tries to fetch the
	 * activity ID from its post meta.  This is a good place to add in our
	 * bp_activity_get_specific() filter before the function is called.
	 *
	 * Remove this in CBOX 1.1.
	 *
	 * @see BBP_BuddyPress_Activity::get_activity_id()
	 */
	public function add_activity_get_specific_hotfix( $retval, $object_id, $meta_key ) {
		if ( '_bbp_activity_id' === $meta_key ) {
			add_filter( 'bp_activity_get_specific', array( $this, 'add_count_total_to_get_specific' ), 10, 3 );
		}

		return $retval;
	}

	/**
	 * Add back the 'count_total' parameter when using bp_activity_get_specific().
	 *
	 * Because activity counts were removed by default in BuddyPress 2.1, this
	 * broke how bbPress determines what an existing activity ID is.  This method
	 * adds back activity counts when using bp_activity_get_specific() so bbPress
	 * will function like before.
	 *
	 * Remove this in CBOX 1.1.
	 */
	public function add_count_total_to_get_specific( $retval, $orig_args, $r ) {
		$r['count_total'] = true;
		return BP_Activity_Activity::get( $r );
	}

	/**
	 * Remove filter to bp_activity_get_specific() to add 'count_total' parameter.
	 *
	 * Remove this in CBOX 1.1.
	 *
	 * @see CBox_BBP_Autoload::add_activity_get_specific_hotfix()
	 */
	public function remove_activity_get_specific_hotfix( $retval ) {
		remove_filter( 'bp_activity_get_specific', array( $this, 'add_count_total_to_get_specific' ), 10, 3 );

		return $retval;
	}

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
		return bp_get_group_permalink();
	}

	/**
	 * Remove the group permalink override just after it's been applied.
	 *
	 * @since 1.0.9
	 */
	public function remove_the_permalink_override() {
		remove_filter( 'the_permalink', array( $this, 'override_the_permalink_with_group_permalink' ) );
	}
}

