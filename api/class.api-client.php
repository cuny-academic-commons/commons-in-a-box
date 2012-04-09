<?php

class BP_API_Client {
	function __construct() {
		$url = 'http://boone.cool/ciab/api/v1/user/admin';
		$method = 'POST';
		$body = array(
			'action' => 'update_profile_field',
			'profile_field_id' => 1,
			'profile_field_data' => 'abcde'
		);
	
		wp_remote_request( $url, array(
			'method' => $method,
			'body' => $body
		) );
	}
}


function cbox_api_client_test() {
	new BP_API_Client;
}
add_action( 'init', 'cbox_api_client_test' );

?>