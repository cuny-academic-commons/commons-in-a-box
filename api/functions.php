<?php

/**
 * Utility functions related to the BP REST API implementation
 */

function bp_api_load_template( $templates ) {
	$template_path = '';

	// Check for existence of the template in the theme directories. Fall back on plugin dir
	$dirs = array( get_stylesheet_directory(), get_template_directory(), CIAB_PLUGIN_DIR . 'api/templates' );

	foreach( $dirs as $dir ) {
		if ( file_exists( trailingslashit( $dir ) . $templates . '.php' ) ) {
			$template_path = trailingslashit( $dir ) . $templates . '.php';
			break;
		}
	}

	if ( $template_path ) {
		load_template( $template_path );
		die();
	}
}

/**
 * Fetch the OAuth data store, modified for use with $wpdb
 */
function bp_api_get_oauth_store() {
	global $wpdb;

	if ( !class_exists( 'OAuthStore' ) ) {
		require_once( CIAB_LIB_DIR . 'oauth-php/library/OAuthStore.php' );
	}

	return OAuthStore::instance( CIAB_PLUGIN_DIR . 'api/BP_OAuthStore.php', array( 'conn' => $wpdb->dbh ) );
}

function bp_api_oauth_table_prefix( $q ) {
	global $wpdb;

	// Not sure what this will do on enable_multisite, groan
	$prefix = $wpdb->get_blog_prefix( bp_get_root_blog_id() );

	$pattern = '/(CREATE TABLE|CREATE TABLE IF NOT EXISTS|ALTER TABLE|UPDATE|INTO|FROM|JOIN) oauth_/';
	$replacement = '$1 ' . $prefix . 'oauth_';

	$q = preg_replace( $pattern, $replacement, $q );

	return $q;
}
add_filter( 'query', 'bp_api_oauth_table_prefix' );

?>