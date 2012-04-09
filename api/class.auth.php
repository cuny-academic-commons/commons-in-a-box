<?php

/**
 * Authentication class
 */
class BP_API_Auth implements iAuthenticate{
	const KEY = 'rEsTlEr2';
	function __isAuthenticated() {
		return true;
		//return isset($_GET['key']) && $_GET['key'] == BP_API_Auth::KEY ? TRUE : FALSE;
	}
	function key(){
		return BP_API_Auth::KEY;
	}
}
?>