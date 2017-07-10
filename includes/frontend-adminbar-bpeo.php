<?php

function cbox_frontend_adminbar_bpeo() {
	$nav  = array();

	$events_slug = defined( 'BPEO_EVENTS_SLUG' )      ? BPEO_EVENTS_SLUG      : 'events';
	$new_slug    = defined( 'BPEO_EVENTS_NEW_SLUG' )  ? BPEO_EVENTS_NEW_SLUG  : 'new-event';

	$link = trailingslashit( bp_loggedin_user_domain() . sanitize_title( $events_slug ) );

	// Add the "My Account" sub menus
	$nav[] = array(
		'parent' => buddypress()->my_account_menu_id,
		'id'     => 'my-account-events',
		'title'  => __( 'Events', 'bp-event-organiser' ),
		'href'   => $link,
	);

	$nav[] = array(
		'parent' => 'my-account-events',
		'id'     => 'my-account-events-calendar',
		'title'  => __( 'Calendar', 'bp-event-organiser' ),
		'href'   => $link,
	);

	$nav[] = array(
		'parent' => 'my-account-events',
		'id'     => 'my-account-events-new',
		'title'  => __( 'New Event', 'bp-event-organiser' ),
		'href'   => trailingslashit( $link . sanitize_title( $new_slug ) ),
	);

	// Register the menus.
	foreach( $nav as $n ) {
		$GLOBALS['wp_admin_bar']->add_menu( $n );
	}
}
add_action( 'bp_setup_admin_bar', 'cbox_frontend_adminbar_bpeo', 45 );