<?php

/**
 * Authentication class
 *
 * Uses OAuth
 */
class BP_API_Auth implements iAuthenticate{

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

		$authorized = false;
		$server = new BP_OAuthServer();
		try
		{
			if ($server->verifyIfSigned())
			{
				$authorized = true;
			} else {
				//error_log( 'not authorized' );
			}
		}
		catch (OAuthException2 $e)
		{
			error_log( $e );
		}

		return $authorized;
	}
}
?>