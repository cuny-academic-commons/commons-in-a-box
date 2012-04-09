<?php

class BP_API_Client {
	function __construct() {
		$url = 'http://boone.cool/ciab/api/v1/group/3';
		$method = 'POST';
		$body = array(
			'action' => 'update_group_name',
			'name' => 'My Test Group 323232',
			'description' => 'A great test group. Yeah!',
			'creator_id' => 1,
			'enable_forum' => 1,
			'status' => 'private',
			'invite_status' => 'mods'
		);
	
		echo '<pre>';
		print_r( wp_remote_request( $url, array(
			'method' => $method,
			'body' => $body
		) ) );
		echo '</pre>';
		die();
	}
}


function cbox_api_client_test() {
	new BP_API_Client;
}
//add_action( 'init', 'cbox_api_client_test' );

?>