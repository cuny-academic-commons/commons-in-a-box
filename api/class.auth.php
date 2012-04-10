<?php

/**
 * Authentication class
 */
class BP_API_Auth implements iAuthenticate{
	protected $OAuth;

	function __isAuthenticated() {
		require( CIAB_PLUGIN_DIR . 'api/class.bp-oauth2.php' );
		$this->oauth = new BP_OAuth2();
		
	
		return true;
	}
	function key(){
		return BP_API_Auth::KEY;
	}
}
?>