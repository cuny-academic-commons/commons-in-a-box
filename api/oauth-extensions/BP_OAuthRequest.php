<?php

require_once( CIAB_LIB_DIR . 'oauth-php/library/OAuthRequest.php' );

class BP_OAuthRequest extends OAuthRequest {

	/**
	 * Return the normalised url for signature checks
	 */
	function getRequestUrl ()
	{
		var_dump( 'ok' ); die();
        $url =  $this->uri_parts['scheme'] . '://'
              . $this->uri_parts['user'] . (!empty($this->uri_parts['pass']) ? ':' : '')
              . $this->uri_parts['pass'] . (!empty($this->uri_parts['user']) ? '@' : '')
			  . $this->uri_parts['host'];

		if (	$this->uri_parts['port']
			&&	$this->uri_parts['port'] != $this->defaultPortForScheme($this->uri_parts['scheme']))
		{
			$url .= ':'.$this->uri_parts['port'];
		}
		if (!empty($this->uri_parts['path']))
		{
			$url .= $this->uri_parts['path'];
		}
		return $url;
	}

}

?>