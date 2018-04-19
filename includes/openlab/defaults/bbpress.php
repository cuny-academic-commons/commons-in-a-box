<?php
/**
 * OpenLab: Set defaults for the bbPress plugin during initial activation.
 *
 * @since 1.1.0
 */

/**
 * bbPress routine after activation.
 *
 * - Don't let bbPress redirect to its about page after activating.
 * - Create a forum category to house BuddyPress group forums if necessary.
 *
 * @since 1.1.0 This logic was moved out of the CBOX plugin-install.php file.
 */
add_action( 'activated_plugin', function( $plugin ) {
	if ( 'bbpress/bbpress.php' !== $plugin ) {
		return;
	}

	// Don't let bbPress redirect to its about page after activating
	delete_transient( '_bbp_activation_redirect' );

	/** If BP bundled forums exists, stop now! *********************/

	// stop if our bb-config-location was found
	$option = get_blog_option( cbox_get_main_site_id(), 'bb-config-location' );
	if ( file_exists( $option ) ) {
		return;
	}

	/** See if a bbPress forum named 'Group Forums' exists *********/

	// add a filter to WP_Query so we can search by post title
	add_filter( 'posts_where', function( $where, $wp_query ) {
		global $wpdb;

		if ( $post_title = $wp_query->get( 'cbox_post_title' ) ) {
			$where .= " AND {$wpdb->posts}.post_title = '" . esc_sql( $post_title ) . "'";
		}

		return $where;
	}, 10, 2 );

	// do our search
	$search = new WP_Query( array(
		'post_type'       => bbp_get_forum_post_type(),
		'cbox_post_title' => __( 'Group Forums', 'bbpress' )
	) );

	/** No match, create our forum! ********************************/

	if ( ! $search->have_posts() ) {
		// create a forum for BP groups
		$forum_id = bbp_insert_forum( array(
			'post_title'   => __( 'Group Forums', 'bbpress' ),
			'post_content' => __( 'All forums created in groups can be found here.', 'cbox' )
		) );

		// update the bbP marker for group forums
		update_blog_option( cbox_get_main_site_id(), '_bbp_group_forums_root_id', $forum_id );
	}
} );