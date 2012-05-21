<?php

/**
 * Authentication class
 */
class BP_API_Auth implements iAuthenticate{
	protected $store;

	/**
	 * Here is the schema for the time being:
	 *
	 * - Server admin manually creates and passes to the Client admin:
	 *    - consumer_token
	 *    - client_public
	 *    - client_secret
	 */
	function __isAuthenticated() {

		if ( !class_exists( 'BP_OAuthServer' ) ) {
			require_once( CIAB_PLUGIN_DIR . 'api/oauth-extensions/BP_OAuthServer.php' );
		}

		error_log( 'attempting authorization' );

		$authorized = false;
		$server = new BP_OAuthServer();
		try
		{
			if ($server->verifyIfSigned())
			{
				error_log( 'authorized' );
				$authorized = true;
			} else {
				error_log( 'not authorized' );
			}
		}
		catch (OAuthException2 $e)
		{
			error_log( $e );
		}

	//	require( CIAB_PLUGIN_DIR . 'api/class.bp-oauth.php' );
	//	$this->oauth = new BP_OAuth();


return true;
		return $authorized;
	}
}
?>