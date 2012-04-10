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

?>