<?php

/**
 * Assuming OAuth for the moment. Will abstract at a later date.
 */
class BP_API_Client {
	function __construct() {
		// @todo This needs to be toggleable
		add_action( 'init', array( &$this, 'endpoint' ), 1001 );

		$this->store = bp_api_get_oauth_store();
	}

	function get_oauth_info_for_site( $url ) {
		return $this->store->getServerForUri( $url, 0 );
	}

	function endpoint() {
		global $bp;

		if ( bp_is_current_component( $bp->api->id ) ) {

			if ( bp_is_current_action( 'request_access_token' ) ) {
				$this->process_request_access_token();
			} else if ( bp_is_current_action( 'authorized' ) ) {
				$this->process_authorized();
			}
		}
	}

	function process_request_access_token() {
		var_dump( $_GET );
		die();
	}

	function process_authorized() {
		bp_api_load_template( 'api/authorized' );
	}
}


function cbox_api_client_test() {
	//BP_API_Server::install_oauth_store();
	/*$url = 'http://boone.cool/ciab/api/v1/addclient/';
	$method = 'GET';



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
*/ /*
		echo '<pre>';
		print_r( wp_remote_request( $url, array(
			'method' => $method,
			'body' => $body
		) ) );
		echo '</pre>';
		die();*/


	$client = new BP_API_Client;
	if ( !isset( $_GET['test_request'] ) )
		return false;

	if ( 'authorized' == bp_current_action() )
		return false;

	if ( 'request_access_token' == bp_current_action() && !empty( $_GET ) )
		return false;


	// Set up our special store
	$store = bp_api_get_oauth_store();

	$server_uri = 'http://boone.cool/ciab/api/';
	$user_id = 1; // temp

	// Get the consumer info
	$consumer_info = $client->get_oauth_info_for_site( $server_uri );

	include( CIAB_LIB_DIR . 'oauth-php/library/OAuthRequester.php' );

	// Obtain a request token from the server
	$token = OAuthRequester::requestRequestToken( $consumer_info['consumer_key'], $consumer_info['user_id'] );

	// Callback to our (consumer) site, will be called when the user finished the authorization at the server
	$callback_uri = add_query_arg( array(
		'consumer_key' => rawurlencode( $consumer_info['consumer_key'] ),
		'user_id' => intval( $consumer_info['user_id'] )
	), bp_get_root_domain() . '/api/authorized' );

	error_log( $token['token'] );

	$request_uri = add_query_arg( array(
		'callback_uri' => urlencode( $callback_uri ),
		'oauth_token'  => urlencode( $token['token'] )
	), $token['authorize_uri'] );

	bp_core_redirect( $request_uri );
return;
	try
	{
	    OAuthRequester::requestAccessToken($consumer_info['consumer_key'], $token['token'], $consumer_info['user_id']);
	}
	catch (OAuthException $e)
	{
	    // Something wrong with the oauth_token.
	    // Could be:
	    // 1. Was already ok
	    // 2. We were not authorized
	}

	var_dump( $token );
	var_dump( $callback_uri );

	var_dump( $consumer_info );
}
add_action( 'init', 'cbox_api_client_test', 1000 );


function cbox_test_request_2() {
	if ( !isset( $_GET['tt'] ) ) {
		return;
	}

	// Must be trailingslashed bc of BP canonical. Fix upstream
	$url = 'http://boone.cool/ciab/api/v1/group/3/';

	$method = 'POST';
	$body = array(
		'action' => 'update_group_name',
		'name' => '2foo My Test Group abc1',
		'description' => 'A great test group. Yeah!',
		'creator_id' => 1,
		'enable_forum' => 1,
		'status' => 'private',
		'invite_status' => 'mods'
	);


	require_once( CIAB_PLUGIN_DIR . 'api/oauth-extensions/BP_OAuthRequester.php' );

	$request = new BP_OAuthRequester( $url, $method, '', $body );

	try {
		$result = $request->doRequest(0);
	} catch ( OAuthException2 $e ) {

		echo '<pre>'; print_r( $e ); echo '</pre>';
	}

	var_dump( $request );
	var_dump( $result );
}
add_action( 'init', 'cbox_test_request_2', 1000 );

?>