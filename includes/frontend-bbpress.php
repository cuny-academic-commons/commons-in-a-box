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

		$this->enable_visual_editor();

		$this->fix_form_actions();

		$this->save_notification_meta();

		$this->allow_revisions_during_edit();

		$this->bypass_link_limit();

		$this->show_notice_for_moderated_posts();

		$this->fix_duplicate_forum_creation();

		$this->fix_pending_group_topics();

		$this->fix_topic_description_for_spam_and_trash_status();

		$this->fix_untrashed_posts();

		$this->send_pending_posts_notification();
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
			add_filter( "bbp_is_{$post_type}_anonymous", function( $retval ) use ( $post_type ) {
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
			$msg = esc_html__( 'Your forum post is pending moderation', 'commons-in-a-box' );
			bp_core_add_message( $msg, 'error' );

			return $retval;
		};

		add_filter( 'bbp_new_reply_pre_insert', $notice );
	}

	/** DUPLICATE FORUM CREATION ***************************************/

	/**
	 * Stops duplicate forum creation on a group's "Manage > Forum" page.
	 *
	 * Runs at 'bp_actions' on priority 7, so logic is run before BP's group
	 * extension on priority 8.
	 *
	 * Hotfix for https://bbpress.trac.wordpress.org/ticket/3399
	 *
	 * @since 1.3.0
	 */
	public function fix_duplicate_forum_creation() {
		add_action( 'bp_actions', function() {
			// Not on a group's "Manage > Forum" page? Bail.
			if ( false === ( bp_is_groups_component() && bp_is_current_action( 'admin' ) && bp_is_action_variable( 'forum', 0 ) ) ) {
				return;
			}

			// Check POST and AJAX.
			if ( empty( $_POST ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return;
			}

			// Verify nonce
			if ( ! wp_verify_nonce( $_POST['_bp_group_edit_nonce_forum'], 'bp_group_extension_forum_edit' ) ) {
				return;
			}

			$meta_key = 'bbp_previous_forum_id';
			$group_id = bp_get_current_group_id();

			$forum_id = groups_get_groupmeta( $group_id, 'forum_id' );
			if ( ! empty( $forum_id[0] ) ) {
				$forum_id = $forum_id[0];
			}

			// Disabling forum. Save old forum ID for later.
			if ( empty( $_POST['bbp-edit-group-forum'] ) && ! empty( $forum_id ) ) {
				groups_update_groupmeta( $group_id, $meta_key, $forum_id );
			}

			// Check for previous forum and validate existence.
			$previous_forum = groups_get_groupmeta( $group_id, $meta_key );
			$previous_forum = ! empty( $previous_forum )     ? bbp_get_forum( $previous_forum ) : 0;
			$previous_forum = ! empty( $previous_forum->ID ) ? $previous_forum->ID : 0;

			// Use old forum; keymaster logic.
			if ( bbp_is_user_keymaster() ) {
				if ( empty( $_POST['bbp_group_forum_id'] ) ) {
					if ( empty( $forum_id ) ) {
						$forum_id = $previous_forum;
					}

					if ( ! empty( $forum_id ) ) {
						$_POST['bbp_group_forum_id'] = $forum_id;
					}
				}

			// Use old forum; group admin logic.
			} elseif ( empty( $forum_id ) && ! empty( $previous_forum ) ) {
				bbp_update_group_forum_ids( $group_id, [ $previous_forum ] );
			}
		}, 7 );
	}

	/**
	 * Allow pending group topics to be viewed.
	 *
	 * Hotfix for https://bbpress.trac.wordpress.org/ticket/3430
	 *
	 * @since 1.3.0
	 */
	public function fix_pending_group_topics() {
		$pending_slug_prefix = 'pending--';

		// Utility function to parse pending post name to post ID.
		$parse_post_name = function( $post_name ) use ( $pending_slug_prefix ) {
			// Bail if post name does not match our pending prefix.
			if ( 0 !== strpos( $post_name, $pending_slug_prefix ) ) {
				return false;
			}

			// Sanity check! Ensure we have a post ID.
			$id = substr( $post_name, 9 );
			if ( ! is_numeric( $id ) ) {
				return false;
			}

			// Sanity check 2: Ensure post is pending and user can edit post.
			$topic = get_post( $id );
			if ( 'pending' !== get_post_status( $topic ) && ! current_user_can( 'edit_posts' ) ) {
				return false;
			}

			return $topic->ID;
		};

		// Fix <title> filter.
		add_filter( 'bp_modify_page_title', function( $retval ) use ( $parse_post_name ) {
			if ( ! bbp_is_group_forums_active() ) {
				return $retval;
			}

			// Bail if not on a group forum topic page.
			if ( ! bp_is_group_forum_topic() && ! bp_is_group_forum_topic_edit() ) {
				return $retval;
			}

			// Bail if we're not on a pending topic.
			$post_id = $parse_post_name( bp_action_variable( 1 ) );
			if ( false === $post_id ) {
				return $retval;
			}

			// Filter WP_Query lookup to use pending post ID.
			$pre_get_posts = function( $q ) use ( $post_id ) {
				if ( false !== $post_id ) {
					$q->set( 'p', $post_id );
					$q->set( 'name', '' );
				}
			};

			add_action( 'pre_get_posts', $pre_get_posts );

			// Remove our filter after we're done.
			add_action( 'posts_selection', function() use ( $pre_get_posts ) {
				remove_action( 'pre_get_posts', $pre_get_posts );
			} );
		}, 9 );

		// Allow pending group topics to be viewed.
		add_action( 'bbp_before_group_forum_display', function() use ( $parse_post_name ) {
			if ( 'topic' !== bp_action_variable() ) {
				return;
			}

			// Filter bbPress topic lookup to use pending post ID.
			add_filter( 'bbp_after_has_topics_parse_args', function( $retval ) use ( $parse_post_name ) {
				$post_id = $parse_post_name( $retval['name'] );

				if ( false !== $post_id ) {
					unset( $retval['name'] );
					$retval['p'] = $post_id;
				}

				return $retval;
			} );
		} );

		// Fix pending, trash and spam group topic permalinks.
		add_filter( 'bbp_get_topic_permalink', function( $retval, $topic_id ) use ( $pending_slug_prefix ) {
			$topic = get_post( $topic_id );
			if ( 'topic' !== $topic->post_type ) {
				return $retval;
			}

			if ( 'pending' !== get_post_status( $topic ) && 'spam' !== get_post_status( $topic ) && 'trash' !== get_post_status( $topic ) ) {
				return $retval;
			}

			if ( ! bp_is_group() ) {
				return $retval;
			}

			// Use 'pending--{$topic_id}' as slug for pending topics with no slug.
			if ( 'pending' === get_post_status( $topic ) && empty( $topic->post_name ) ) {
				$retval = substr( $retval, 0, strpos( $retval, 'forum/topic/' ) + 12 );
				$retval .= sprintf( '%s%d', $pending_slug_prefix, $topic_id );
			}

			// Add special "?view=all" query param so our post statuses are viewable.
			return bbp_add_view_all( trailingslashit( $retval ), true );
		}, 20, 2 );

		// Fix redirect when posting a new pending group topic.
		add_filter( 'bbp_new_topic_redirect_to', function( $retval, $redirect, $topic_id ) use ( $pending_slug_prefix ) {
			if ( ! bp_is_group() ) {
				return $retval;
			}

			$topic      = bbp_get_topic( $topic_id );
			$slug       = trailingslashit( $topic->post_name );
			$topic_hash = '#post-' . $topic_id;

			// Pending status
			if ( bbp_get_pending_status_id() === get_post_status( $topic_id ) ) {
				$slug = bbp_add_view_all( sprintf( '%s%d', $pending_slug_prefix, $topic_id ), true );
			}

			return trailingslashit( bp_get_group_permalink( groups_get_current_group() ) ) . 'forum/topic/' . $slug . $topic_hash;
		}, 20, 3 );

		// Fix redirect link after pending topic is approved.
		add_filter( 'bbp_toggle_topic', function( $retval, $r ) {
			if ( ! bp_is_group() ) {
				return $retval;
			}

			if ( ! empty( get_post( $r['id'] )->post_name ) && bbp_get_pending_status_id() !== get_post_status( $r['id'] ) ) {
				$retval['redirect_to'] = bbp_get_topic_permalink( $r['id'] );
			}

			return $retval;
		}, 10, 2 );
	}

	/**
	 * Fix description when a topic's status is 'spam' or 'trash'.
	 *
	 * Hotfix for https://bbpress.trac.wordpress.org/ticket/3432
	 *
	 * @since 1.3.0
	 */
	public function fix_topic_description_for_spam_and_trash_status() {
		add_filter( 'bbp_get_single_topic_description', function( $retval, $r ) {
			$topic_id = bbp_get_topic_id( $r['topic_id'] );
			$message  = '';

			// Trash.
			if ( bbp_get_topic_status( $topic_id ) === bbp_get_trash_status_id() ) {
				$message = esc_html__( 'This topic is in the trash.', 'bbpress' );

			// Spam.
			} elseif ( bbp_get_topic_status( $topic_id ) === bbp_get_spam_status_id() ) {
				$message = esc_html__( 'This topic is marked as spam.', 'bbpress' );
			}

			if ( '' !== $message ) {
				$retval = $r['before'] . $message . $r['after'];
			}

			return $retval;
		}, 10, 2 );
	}

	/**
	 * Fix untrashed topics or replies to use their previous post status.
	 *
	 * Hotfix for https://bbpress.trac.wordpress.org/ticket/3433
	 *
	 * @since 1.3.0
	 */
	public function fix_untrashed_posts() {
		add_filter( 'wp_untrash_post_status', function( $retval, $post_id, $previous_status ) {
			if ( ! bbp_is_topic( $post_id ) && ! bbp_is_reply( $post_id ) ) {
				return $retval;
			}

			if ( ! empty( $previous_status ) ) {
				return $previous_status;
			} else {
				return $retval;
			}
		}, 10, 3 );
	}

	/**
	 * Send emails of pending forum posts to moderators.
	 *
	 * For BuddyPress, also sends pending posts to group admins and mods.
	 * Hotfix for https://bbpress.trac.wordpress.org/ticket/3349
	 *
	 * @since 1.3.0
	 */
	public function send_pending_posts_notification() {
		// Hopefully if this function is included, this hotfix is no longer needed.
		if ( function_exists( 'bbp_notify_forum_moderators' ) ) {
			return;
		}

		/*
		 * Sends emails for pending forum posts to moderators.
		 *
		 * @param  int  $post_id bbPress topic or reply ID
		 * @return bool True on success, false on failure
		 */
		$notify_mods = function( $post_id = 0 ) {
			// Bail if importing
			if ( defined( 'WP_IMPORTING' ) && WP_IMPORTING ) {
				return false;
			}

			// If post isn't pending, bail
			if ( bbp_get_pending_status_id() !== get_post_status( $post_id ) ) {
				return false;
			}

			// Check if this is a bbPress topic or reply
			if ( ! bbp_is_topic( $post_id ) && ! bbp_is_reply( $post_id ) ) {
				return false;
			}

			// Determine bbPress type
			if ( bbp_is_reply( $post_id ) ) {
				$type        = 'reply';
				$forum_id    = bbp_get_reply_forum_id( $post_id );
				$author_name = bbp_get_reply_author_display_name( $post_id );
				$content     = bbp_get_reply_content( $post_id );
				$title       = bbp_get_reply_topic_title( $post_id );
				$url         = bbp_get_reply_url( $post_id );
			} else {
				$type        = 'topic';
				$forum_id    = bbp_get_topic_forum_id( $post_id );
				$author_name = bbp_get_topic_author_display_name( $post_id );
				$content     = bbp_get_topic_content( $post_id );
				$title       = bbp_get_topic_title( $post_id );
				$url         = bbp_get_topic_permalink( $post_id );
			}

			// Get moderator user IDs
			$user_ids = bbp_get_moderator_ids( $forum_id );

			// Remove author from the list
			$author_key = array_search( (int) get_post( $post_id )->post_author, $user_ids, true );
			if ( false !== $author_key ) {
				unset( $user_ids[ $author_key ] );
			}

			/**
			 * User IDs filter to send moderation emails to.
			 *
			 * @param array $user_ids Array of user IDs
			 * @param int   $forum_id Forum ID
			 */
			$user_ids = (array) apply_filters( 'bbp_forum_moderation_user_ids', $user_ids, $forum_id );

			// Bail if no one to notify
			if ( empty( $user_ids ) ) {
				return false;
			}

			// Get email addresses, bail if empty
			$email_addresses = bbp_get_email_addresses_from_user_ids( $user_ids );
			if ( empty( $email_addresses ) ) {
				return false;
			}

			/** Mail ******************************************************************/

			// Remove content filters for email display usage
			bbp_remove_all_filters( "bbp_get_{$type}_content" );
			bbp_remove_all_filters( "bbp_get_{$type}_title" );
			bbp_remove_all_filters( 'the_title' );

			// Email variables
			$blog_name   = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
			$title       = wp_specialchars_decode( strip_tags( $title ), ENT_QUOTES );
			$author_name = wp_specialchars_decode( strip_tags( $author_name ), ENT_QUOTES );
			$forum_name  = wp_specialchars_decode( strip_tags( bbp_get_forum_title( $forum_id ) ), ENT_QUOTES );
			$content     = wp_specialchars_decode( strip_tags( $content ), ENT_QUOTES );
			$url         = bbp_add_view_all( $url, true );

			$message = sprintf( esc_html__( '%1$s wrote:

%2$s

-----------

To approve, trash or mark this post as spam, click here:
%3$s

You are receiving this email because you are a moderator for the %4$s forum.', 'bbpress' ),

				$author_name,
				$content,
				$url,
				$forum_name
			);

			/**
			 * Filters email message for moderation email.
			 *
			 * @param string $message  Email content
			 * @param int    $post_id  Moderated post ID
			 * @param int    $forum_id Forum ID containing the moderated post.
			 */
			$message = apply_filters( 'bbp_forum_moderation_mail_message', $message, $post_id, $forum_id );
			if ( empty( $message ) ) {
				return;
			}

			/* translators: bbPress moderation email subject. 1: Site title, 2: Topic title, 3: Forum title */
			$subject = sprintf( __( '[%1$s] Please moderate: "%2$s" from the forum "%3$s"', 'bbpress' ), $blog_name, $title, $forum_name );

			/**
			 * Filters email subject for moderation email.
			 *
			 * @param string $title    Email subject
			 * @param int    $post_id  Moderated post ID
			 * @param int    $forum_id Forum ID containing the moderated post
			 */
			$subject = apply_filters( 'bbp_forum_moderation_mail_subject', $subject, $post_id, $forum_id );
			if ( empty( $subject ) ) {
				return;
			}

			/** Headers ***************************************************************/

			// Default bbPress X-header
			$headers = array( bbp_get_email_header() );

			// Get the noreply@ address
			$no_reply = bbp_get_do_not_reply_address();

			// Setup "From" email address
			$from_email = apply_filters( 'bbp_moderation_from_email', $no_reply );

			// Setup the From header
			$headers[] = 'From: ' . get_bloginfo( 'name' ) . ' <' . $from_email . '>';

			// Loop through addresses
			foreach ( (array) $email_addresses as $address ) {
				$headers[] = 'Bcc: ' . $address;
			}

			/** Send it ***************************************************************/

			/**
			 * Filters mail headers for the email sent to moderators.
			 *
			 * @param array $headers Email headers
			 */
			$headers = apply_filters( 'bbp_moderation_mail_headers', $headers  );

			/**
			 * Filters "To" email address for the email sent to moderators.
			 *
			 * @param string $email Email address. Defaults to no-reply email address.
			 */
			$to_email = apply_filters( 'bbp_moderation_to_email', $no_reply );


			/**
			 * Hook to do something before email is sent to moderators.
			 *
			 * @param int   $post_id  Moderated post ID
			 * @param int   $forum_id Forum ID containing moderated post
			 * @param array $user_ids User IDs to send email to.
			 */
			do_action( 'bbp_pre_notify_forum_moderators', $post_id, $forum_id, $user_ids );

			// Send notification email
			wp_mail( $to_email, $subject, $message, $headers );

			/**
			 * Hook to do something after email is sent to moderators.
			 *
			 * @param int   $post_id  Moderated post ID
			 * @param int   $forum_id Forum ID containing moderated post
			 * @param array $user_ids User IDs to send email to.
			 */
			do_action( 'bbp_post_notify_forum_moderators', $post_id, $forum_id, $user_ids );

			// Restore previously removed filters
			bbp_restore_all_filters( "bbp_get_{$type}_content" );
			bbp_restore_all_filters( "bbp_get_{$type}_title" );
			bbp_restore_all_filters( 'the_title' );

			return true;
		};

		// Notify moderators of pending forum replies.
		add_action( 'bbp_new_reply', $notify_mods );

		// Notify moderators of pending forum topics.
		add_action( 'bbp_new_topic', $notify_mods );

		// Add group admins and mods to moderation email list.
		add_filter( 'bbp_forum_moderation_user_ids', function( $user_ids, $forum_id ) {
			// Bail if not a BP group forum or groups component is inactive
			if ( ! bbp_is_forum_group_forum( $forum_id ) || ! bp_is_active( 'groups' ) ) {
				return $user_ids;
			}

			// bbPress doesn't support multiple forums per group yet
			$group_id = current( bbp_get_forum_group_ids( $forum_id ) );
			if ( empty( $group_id ) ) {
				return $user_ids;
			}

			// Fetch group admins
			$admin_ids = wp_list_pluck( groups_get_group_admins( $group_id ), 'user_id' );

			// Fetch group moderators
			$mod_ids = wp_list_pluck( groups_get_group_mods( $group_id ), 'user_id' );

			// Merge user ids and return
			$user_ids = array_merge( $user_ids, $admin_ids, $mod_ids );
			return array_unique( $user_ids );
		}, 10, 2 );
	}
}
