<?php

require_once( CIAB_PLUGIN_DIR . 'api/oauth-extensions/BP_OAuthRequestSigner.php' );
require_once( CIAB_LIB_DIR . 'oauth-php/library/OAuthRequester.php' );

class BP_OAuthRequester extends OAuthRequester {

	/**
	 * Perform the request, returns the response code, headers and body.
	 *
	 * We can do without some of the native method's cURL checking, since we're using WP's
	 * HTTP API
	 *
	 * @param int usr_id			optional user id for which we make the request
	 * @param array curl_options	optional extra options for curl request
	 * @param array options			options like name and token_ttl
	 * @exception OAuthException2 when authentication not accepted
	 * @exception OAuthException2 when signing was not possible
	 * @return array (code=>int, headers=>array(), body=>string)
	 */
	function doRequest ( $usr_id = 0, $curl_options = array(), $options = array() )
	{
		$name = isset($options['name']) ? $options['name'] : '';
		if (isset($options['token_ttl']))
		{
			$this->setParam('xoauth_token_ttl', intval($options['token_ttl']));
		}

		if (!empty($this->files))
		{
			// At the moment OAuth does not support multipart/form-data, so try to encode
			// the supplied file (or data) as the request body and add a content-disposition header.
			list($extra_headers, $body) = OAuthBodyContentDisposition::encodeBody($this->files);
			$this->setBody($body);
			$curl_options = $this->prepareCurlOptions($curl_options, $extra_headers);
		}
		$this->sign($usr_id, null, $name);
		$result = $this->curl_raw($curl_options);

		if ($result['code'] >= 400)
		{
			throw new OAuthException2('Request failed with code ' . $result['code'] . ': ' . $result['body']);
		}

		// Record the token time to live for this server access token, immediate delete iff ttl <= 0
		// Only done on a succesful request.
		$token_ttl = $this->getParam('xoauth_token_ttl', false);
		if (is_numeric($token_ttl))
		{
			$this->store->setServerTokenTtl($this->getParam('oauth_consumer_key',true), $this->getParam('oauth_token',true), $token_ttl);
		}

		return $result;
	}

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