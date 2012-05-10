<?php

/**
 * API authorization success template
 */

$store = bp_api_get_oauth_store();

$consumer_key = isset( $_GET['consumer_key'] ) ? urldecode( $_GET['consumer_key'] ) : '';
$user_id = isset( $_GET['consumer_key'] ) ? (int) $_GET['user_id'] : 0;

$r = $store->getServer($consumer_key, $user_id);

$remote_uri = isset( $r['server_uri'] ) ? $r['server_uri'] : '';

var_dump( $_GET );


require_once( CIAB_PLUGIN_DIR . 'api/oauth-extensions/BP_OAuthRequester.php' );

try {
	$access_token = OAuthRequester::requestAccessToken($_GET['consumer_key'], $_GET['oauth_token'], $_GET['user_id']);
} catch ( OAuthException2 $e ) {
	echo '<pre>'; print_r( $e ); echo '</pre>';
}


//$url = 'http://boone.cool/ciab/api/v1/group/3';
$url = 'http://boone.cool/ciab/api/v1/group/3';

//$request, $method = null, $params = null, $body = null, $files = null

$method = 'POST';
$body = array(
	'action' => 'update_group_name',
	'name' => 'My Test Group abc1',
	'description' => 'A great test group. Yeah!',
	'creator_id' => 1,
	'enable_forum' => 1,
	'status' => 'private',
	'invite_status' => 'mods'
);

$request = new OAuthRequester( $url, $method, $_GET, $body );

try {
	$result = $request->doRequest(0);
} catch ( OAuthException2 $e ) {

	echo '<pre>'; print_r( $e ); echo '</pre>';
}


		echo '<pre>';
		print_r( wp_remote_request( $url, array(
			'method' => $method,
			'body' => $body
		) ) );
		echo '</pre>';
		die();


get_header( 'buddypress' ); ?>

	<div id="content">
		<div class="padder">
			<h2><?php _e( 'Authorization successful', 'cbox' ) ?></h2>

			<p><?php printf( __( 'Your account at %1$s is now connected to your account at %2$s', 'cbox' ), $remote_uri, bp_get_root_domain() ) ?></p>

			<p><?php printf( __( 'You will be redirected back to %1$s in a few seconds. If you are not redirected automatically, click <a href="%2$s">here</a> to continue.', 'cbox' ), $remote_uri, $remote_uri ) ?></p>
		</div>
	</div>

<?php get_sidebar( 'buddypress' ); ?>
<?php get_footer( 'buddypress' ); ?>