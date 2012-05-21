<?php

class BP_OAuthSignatureMethod_MD5 extends OAuthSignatureMethod_MD5 {
	/**
	 * Check if the request signature corresponds to the one calculated for the request.
	 *
	 * Overridden here because of a bug in the way that urldecoding is done in the original
	 *
	 * @param OAuthRequest request
	 * @param string base_string	data to be signed, usually the base string, can be a request body
	 * @param string consumer_secret
	 * @param string token_secret
	 * @param string signature		from the request, still urlencoded
	 * @return string
	 */
	public function verify ( $request, $base_string, $consumer_secret, $token_secret, $signature )
	{
		error_log( 'ok' );
		$a = $request->urldecode($signature);
		$b = $request->urldecode($this->signature($request, $base_string, $consumer_secret, $token_secret));

		$a = urldecode( $a );
		$b = urldecode( $b );

		// We have to compare the decoded values
		$valA  = base64_decode($a);
		$valB  = base64_decode($b);

		// Crude binary comparison
		return rawurlencode($valA) == rawurlencode($valB);
	}
}

?>