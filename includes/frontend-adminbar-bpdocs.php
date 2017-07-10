<?php

function cbox_frontend_adminbar_bpdocs() {
	$nav  = array();
	$id   = 'bp_docs';

	$name = bp_get_option( 'bp-docs-user-tab-name', __( 'Docs', 'bp-docs' ) );
	$name = apply_filters( 'bp_docs_get_user_tab_name', $name );

	$slug  = defined( 'BP_DOCS_SLUG' ) ? BP_DOCS_SLUG : bp_get_option( 'bp-docs-slug', 'docs' );
	$slug  = apply_filters( 'bp_docs_get_docs_slug', $slug );

	$started_slug = defined( 'BP_DOCS_STARTED_SLUG' ) ? BP_DOCS_STARTED_SLUG : 'started';
	$edited_slug  = defined( 'BP_DOCS_EDITED_SLUG' )  ? BP_DOCS_EDITED_SLUG  : 'edited';
	$create_slug  = defined( 'BP_DOCS_CREATE_SLUG' )  ? BP_DOCS_CREATE_SLUG  : 'create';

	$docs_link    = apply_filters( 'bp_docs_get_mydocs_link',         trailingslashit( bp_loggedin_user_domain() . $slug ) );
	$started_link = apply_filters( 'bp_docs_get_mydocs_started_link', trailingslashit( $docs_link . $started_slug ) );
	$edited_link  = apply_filters( 'bp_docs_get_mydocs_edited_link',  trailingslashit( $docs_link . $edited_slug ) );

	$archive_link = apply_filters( 'bp_docs_get_archive_link', trailingslashit( get_home_url( bp_get_root_blog_id(), $slug ) ) );
	$create_link  = apply_filters( 'bp_docs_get_create_link',  trailingslashit( $archive_link . $create_slug ) );

	// Add the "My Account" sub menus
	$nav[] = array(
		'parent' => buddypress()->my_account_menu_id,
		'id'     => 'my-account-' . $id,
		'title'  => $name,
		'href'   => $docs_link,
	);

	$nav[] = array(
		'parent' => 'my-account-' . $id,
		'id'     => 'my-account-' . $id . '-started',
		'title'  => __( 'Started By Me', 'bp-docs' ),
		'href'   => $started_link,
	);

	$nav[] = array(
		'parent' => 'my-account-' . $id,
		'id'     => 'my-account-' . $id . '-edited',
		'title'  => __( 'Edited By Me', 'bp-docs' ),
		'href'   => $edited_link,
	);

	$nav[] = array(
		'parent' => 'my-account-' . $id,
		'id'     => 'my-account-' . $id . '-create',
		'title'  => __( 'Create New Doc', 'bp-docs' ),
		'href'   => $create_link,
	);

	// Register the menus.
	foreach( $nav as $n ) {
		$GLOBALS['wp_admin_bar']->add_menu( $n );
	}
}
add_action( 'bp_setup_admin_bar', 'cbox_frontend_adminbar_bpdocs', 80 );