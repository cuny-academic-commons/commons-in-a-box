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