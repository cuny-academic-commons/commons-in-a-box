<?php

require_once( CIAB_PLUGIN_DIR . 'api/oauth-extensions/BP_OAuthRequestSigner.php' );
require_once( CIAB_LIB_DIR . 'oauth-php/library/OAuthRequester.php' );

class BP_OAuthRequester extends OAuthRequester {

	/**
	 * Swaps out the direct cURL requests with WP's HTTP API, for better WP support and more
	 * robust fallbacks
	 */
	function curl_raw( $opts = array() ) {

		if (isset($opts[CURLOPT_HTTPHEADER]))
		{
			$header = $opts[CURLOPT_HTTPHEADER];
		}
		else
		{
			$header = array();
		}

		$ch 		= curl_init();
		$method		= $this->getMethod();
		$url		= $this->getRequestUrl();
		$header[]	= $this->getAuthorizationHeader();
		$query		= $this->getQueryString();
		$body		= $this->getBody();

		// Params (the oAuth tokens) have to be sent in the body
		if ( is_array( $body ) && is_array( $this->param ) ) {
			$body = array_merge( $body, $this->param );
		}

		$has_content_type = false;
		foreach ($header as $h)
		{
			if (strncasecmp($h, 'Content-Type:', 13) == 0)
			{
				$has_content_type = true;
			}
		}

		if (!is_null($body))
		{
			if ($method == 'TRACE')
			{
				throw new OAuthException2('A body can not be sent with a TRACE operation');
			}

			// PUT and POST allow a request body
			if (!empty($query))
			{
				$url .= '?'.$query;
			}

			// Make sure that the content type of the request is ok
			if (!$has_content_type)
			{
				$header[]         = 'Content-Type: application/octet-stream';
				$has_content_type = true;
			}
		}
		else
		{
			// a 'normal' request, no body to be send
			if ($method == 'POST')
			{
				if (!$has_content_type)
				{
					$header[]         = 'Content-Type: application/x-www-form-urlencoded';
					$has_content_type = true;
				}
			}
			else
			{
				if (!empty($query))
				{
					$url .= '?'.$query;
				}
			}
		}

		$header = array_merge( $header, $opts );

		$request_args = array(
			'method' => $method,
			'timeout' => 30,
			'user-agent' =>  'anyMeta/OAuth 1.0 - ($LastChangedRevision: 174 $)',
			'header' => $header,
			'body' => $body
		);

		$request = wp_remote_request( $url, $request_args );

		if ( is_wp_error( $request ) ) {
			throw new OAuthException2('CURL error: ' . $error);
		}

		// Tell the logger what we requested and what we received back
		$data = $method . " $url\n".implode("\n",$header);
		if (is_string($body))
		{
			$data .= "\n\n".$body;
		}
		else if ($method == 'POST')
		{
			$data .= "\n\n".$query;
		}
		return $request;

		OAuthRequestLogger::setSent($data, $body);
		OAuthRequestLogger::setReceived($retval);

		return $retval;
	}
}

?>