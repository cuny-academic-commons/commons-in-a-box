<?php

/**
 * Authentication class
 */
class BP_API_Auth implements iAuthenticate{
	protected $OAuth;

	/**
	 * Here is the schema for the time being:
	 *
	 * - Server admin manually creates and passes to the Client admin:
	 *    - consumer_token
	 *    - client_public
	 *    - client_secret
	 */
	function __isAuthenticated() {
		require( CIAB_PLUGIN_DIR . 'api/class.bp-oauth.php' );
		$this->oauth = new BP_OAuth();
		
		
	
		return true;
	}
}
?>