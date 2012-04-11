<?php

class BP_API_Client {
	function __construct() {
		$this->setup_endpoint();
	}
	
	function setup_endpoint() {
		
	}
}


function cbox_api_client_test() {
	//BP_API_Server::install_oauth_store();
	/*$url = 'http://boone.cool/ciab/api/v1/addclient/';
	$method = 'POST';
	
	$client_id = '12345';
	$client_secret = 'abcde';
	$redirect_uri = 'http://boone.cool/cbox';
	
	$body = array(
		'client_id' => $client_id,
		'client_secret' => $client_secret,
		'redirect_uri' => $redirect_uri
	);
	*/
	
	/*
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
*/	/*
		echo '<pre>';
		print_r( wp_remote_request( $url, array(
			'method' => $method,
			'body' => $body
		) ) );
		echo '</pre>';
		die();*/


//	new BP_API_Client;
}
add_action( 'init', 'cbox_api_client_test' );

?>