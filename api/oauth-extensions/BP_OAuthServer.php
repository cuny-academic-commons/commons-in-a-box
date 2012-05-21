<?php

require_once( CIAB_LIB_DIR . 'oauth-php/library/OAuthServer.php' );

class BP_OAuthServer extends OAuthServer {
	/**
	 * Don't include non-oauth parameters when constructing
	 */
	function signatureBaseString() {
		$sig 	= array();
		$sig[]	= $this->method;
		$sig[]	= $this->getRequestUrl();

		$params = $this->getNormalizedParams();

		error_log( 'DIRTY: ' . $params );

		$params_a = wp_parse_args( $params );

		$new_params = array();
		foreach( $params_a as $k => $v ) {
			if ( 0 === strpos( $k, 'oauth' ) || 0 === strpos( $k, 'xoauth' ) ) {
				$new_params[] = $k . '=' . $v;
			}
		}

		$sig[]	= implode( '&', $new_params );

		return implode('&', array_map(array($this, 'urlencode'), $sig));
	}

	/**
	 * Fetch the signature object used for calculating and checking the signature base string
	 *
	 * Overriding this method so that we can attempt to load custom sig methods first, due
	 * to the bugs in the library classes
	 *
	 * @param string method
	 * @return OAuthSignatureMethod object
	 */
	function getSignatureMethod ( $method )
	{
		$m     = strtoupper($method);
		$m     = preg_replace('/[^A-Z0-9]/', '_', $m);
		$class = 'OAuthSignatureMethod_'.$m;

		if (file_exists(CIAB_PLUGIN_DIR . 'api/oauth-extensions/BP_'.$class.'.php'))
		{
			require_once CIAB_LIB_DIR .'oauth-php/library/signature_method/'.$class.'.php';
			require_once( CIAB_PLUGIN_DIR . 'api/oauth-extensions/BP_'.$class.'.php' );

			$bp_class = 'BP_' . $class;
			$sig = new $bp_class();
		} else if ( file_exists( CIAB_LIB_DIR . 'oauth-php/library/signature_method/' . $class . '.php') )
		{
			require_once CIAB_LIB_DIR . 'oauth-php/library/signature_method/' . $class . '.php';
			$sig = new $class();
		}
		else
		{
			throw new OAuthException2('Unsupported signature method "'.$m.'".');
		}
		return $sig;
	}
}

?>