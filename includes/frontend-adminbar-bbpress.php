<?php

function cbox_frontend_adminbar_bbpress() {
	// Get plugin meta.
	$plugin_meta = get_file_data( realpath( CBOX_PLUGIN_DIR ) . '/../bbpress/bbpress.php', array(
		// We only care about the 'Version' header.
		'Version' => 'Version',
	), 'plugin' );

	// Sanity check!
	if ( empty( $plugin_meta['Version'] ) ) {
		return;
	}

	$nav = array();
	$id  = 'forums';
	$my_engagements_link = $my_favorites_link = $my_subscriptions_link = '';

	// Setup the logged in user variables
	$user_domain = bp_loggedin_user_domain();
	$forums_link = trailingslashit( $user_domain . 'forums' );

	// Engagements - only available in v2.6.0.
	if ( version_compare( $plugin_meta['Version'], '2.6') >= 0 && (bool) apply_filters( 'bbp_is_engagements_active', (bool) get_blog_option( cbox_get_main_site_id(), '_bbp_enable_engagements', 1 ) ) ) {
		$my_engagements_link   = trailingslashit( $forums_link . apply_filters( 'bbp_get_user_engagements_slug', get_blog_option( cbox_get_main_site_id(), '_bbp_user_engagements_slug', 'engagements' ) ) );
	}

	// Favorites
	if ( (bool) apply_filters( 'bbp_is_favorites_active', (bool) get_blog_option( cbox_get_main_site_id(), '_bbp_enable_favorites', 1 ) ) ) {
		$my_favorites_link     = trailingslashit( $forums_link . apply_filters( 'bbp_get_user_favorites_slug', get_blog_option( cbox_get_main_site_id(), '_bbp_user_favs_slug', 'favorites' ) ) );
	}

	// Subscriptions
	if ( (bool) apply_filters( 'bbp_is_subscriptions_active', (bool) get_blog_option( cbox_get_main_site_id(), '_bbp_enable_subscriptions', 1 ) ) ) {
		$my_subscriptions_link = trailingslashit( $forums_link . apply_filters( 'bbp_get_user_subscriptions_slug', get_blog_option( cbox_get_main_site_id(), '_bbp_user_subs_slug', 'subscriptions' ) ) );
	}

	// Add the "My Account" sub menus
	$nav[] = array(
		'parent' => buddypress()->my_account_menu_id,
		'id'     => 'my-account-' . $id,
		'title'  => __( 'Forums', 'bbpress' ),
		'href'   => trailingslashit( $forums_link )
	);

	// Topics
	$nav[] = array(
		'parent' => 'my-account-' . $id,
		'id'     => 'my-account-' . $id . '-topics',
		'title'  => __( 'Topics Started', 'bbpress' ),
		'href'   => trailingslashit( $forums_link . apply_filters( 'bbp_get_topic_archive_slug', get_blog_option( cbox_get_main_site_id(), '_bbp_topic_archive_slug', 'topics' ) ) )
	);

	// Replies
	$nav[] = array(
		'parent' => 'my-account-' . $id,
		'id'     => 'my-account-' . $id . '-replies',
		'title'  => __( 'Replies Created', 'bbpress' ),
		'href'   => trailingslashit( $forums_link . apply_filters( 'bbp_get_topic_archive_slug', get_blog_option( cbox_get_main_site_id(), '_bbp_reply_archive_slug', 'replies' ) ) )
	);

	// Engagements
	if ( '' !== $my_engagements_link ) {
		$nav[] = array(
			'parent' => 'my-account-' . $id,
			'id'     => 'my-account-' . $id . '-engagements',
			'title'  => __( 'Engagements', 'bbpress' ),
			'href'   => $my_engagements_link
		);
	}

	// Favorites
	if ( '' !== $my_favorites_link ) {
		$nav[] = array(
			'parent' => 'my-account-' . $id,
			'id'     => 'my-account-' . $id . '-favorites',
			'title'  => __( 'Favorite Topics', 'bbpress' ),
			'href'   => $my_favorites_link
		);
	}

	// Subscriptions
	if ( '' !== $my_subscriptions_link ) {
		$nav[] = array(
			'parent' => 'my-account-' . $id,
			'id'     => 'my-account-' . $id . '-subscriptions',
			'title'  => __( 'Subscribed Topics', 'bbpress' ),
			'href'   => $my_subscriptions_link
		);
	}

	// Register the menus.
	foreach( $nav as $n ) {
		$GLOBALS['wp_admin_bar']->add_menu( $n );
	}
}
add_action( 'bp_setup_admin_bar', 'cbox_frontend_adminbar_bbpress', 75 );

/*
 * Ensure bbPress' 'forums' component is registered to BP notifications.
 *
 * This allows bbPress notifications to be displayed on sub-sites, even when
 * bbPress isn't activated on the current site.
 *
 * @since 1.1.0
 */
add_filter( 'bp_notifications_get_registered_components', function( $retval ) {
	if ( ! function_exists( 'bbp_activation') ) {
		$retval[] = 'forums';
	}
	return $retval;
} );

/*
 * Re-apply logic to format bbPress notifications.
 *
 * @since 1.1.0
 *
 * @see bbp_format_buddypress_notifications()
 */
add_filter( 'bp_notifications_get_notifications_for_user', function( $retval, $item_id, $secondary_item_id, $total_items, $format, $action, $component, $id ) {
	// If not on our bbPress action, bail.
	if ( 'bbp_new_reply' !== $action ) {
		return $retval;
	}

	$title_attr  = __( 'Topic Replies', 'bbpress' );

	$topic_id = bp_notifications_get_meta( $id, 'cbox_bbp_reply_topic_id' );
	if ( ! empty( $topic_id ) ) {
		$topic_title = bp_notifications_get_meta( $id, 'cbox_bbp_topic_title' );
		$n_args      = array(
			'action'   => 'bbp_mark_read',
			'topic_id' => $topic_id
		);
		$topic_link  = wp_nonce_url( add_query_arg( $n_args, bp_notifications_get_meta( $id, 'cbox_bbp_reply_permalink' ) ), 'bbp_mark_topic_' . $topic_id );

	// No topic meta, so add generic title and link.
	} else {
		$topic_title = esc_html_( 'a forum topic', 'cbox' );
		$topic_link  = add_query_arg( 'type', 'bbp_new_reply', bp_get_notifications_permalink() );
	}

	if ( (int) $total_items > 1 ) {
		$text = sprintf( __( 'You have %d new replies', 'bbpress' ), (int) $total_items );
	} else {
		if ( ! empty( $secondary_item_id ) ) {
			$text = sprintf( __( 'You have %d new reply to %2$s from %3$s', 'bbpress' ), (int) $total_items, $topic_title, bp_core_get_user_displayname( $secondary_item_id ) );
		} else {
			$text = sprintf( __( 'You have %d new reply to %s',             'bbpress' ), (int) $total_items, $topic_title );
		}
	}

	/*
	 * We're not applying bbPress notification filters here since plugins might
	 * expect bbPress to be loaded and might try using bbPress functions, which
	 * would throw fatal errors.
	 *
	 * https://bbpress.trac.wordpress.org/browser/tags/2.5.14/includes/extend/buddypress/notifications.php?marks=52,59#L41
	 */
	if ( 'string' === $format ) {
		return '<a href="' . esc_url( $topic_link ) . '" title="' . esc_attr( $title_attr ) . '">' . esc_html( $text ) . '</a>';
	} else {
		return array(
			'text' => $text,
			'link' => $topic_link
		);
	}
}, 10, 8 );