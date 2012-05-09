<?php

require CIAB_PLUGIN_DIR . 'lib/oauth-php/library/store/OAuthStoreMySQL.php';

class BP_OAuthStore extends OAuthStoreMySQL {
	/**
	 * Perform a query, ignore the results
	 *
	 * @param string sql
	 * @param vararg arguments (for sprintf)
	 */
	protected function query ( $sql )
	{
		global $wpdb;
		$sql = $this->sql_printf(func_get_args());
		return $wpdb->query( $sql );
	}


	/**
	 * Perform a query, ignore the results
	 *
	 * @param string sql
	 * @param vararg arguments (for sprintf)
	 * @return array
	 */
	protected function query_all_assoc ( $sql )
	{
		global $wpdb;
		$sql = $this->sql_printf(func_get_args());
		return $wpdb->get_results( $sql, ARRAY_A );
	}


	/**
	 * Perform a query, return the first row
	 *
	 * @param string sql
	 * @param vararg arguments (for sprintf)
	 * @return array
	 */
	protected function query_row_assoc ( $sql )
	{
		global $wpdb;
		$sql = $this->sql_printf(func_get_args());
		return $wpdb->get_row( $sql, ARRAY_A );
	}


	/**
	 * Perform a query, return the first row
	 *
	 * @param string sql
	 * @param vararg arguments (for sprintf)
	 * @return array
	 */
	protected function query_row ( $sql )
	{
		global $wpdb;
		$sql = $this->sql_printf(func_get_args());
		return $wpdb->get_row( $sql, ARRAY_N );
	}


	/**
	 * Perform a query, return the first column of the first row
	 *
	 * @param string sql
	 * @param vararg arguments (for sprintf)
	 * @return mixed
	 */
	protected function query_one ( $sql )
	{
		global $wpdb;
		$sql = $this->sql_printf(func_get_args());
		return $wpdb->get_var( $sql );
	}

	/**
	 * List of all registered applications. Data returned has not sensitive
	 * information and therefore is suitable for public displaying.
	 *
	 * @param int $begin
	 * @param int $total
	 * @return array
	 */
	public function listConsumerApplications( $args = array(), $deprecated = true )
	{
		global $wpdb;

		$defaults = array(
			'paged'    => 1,
			'per_page' => 20,
			'order'    => 'DESC',
			'orderby'  => 'issue_date'
		);
		$r = wp_parse_args( $args, $defaults );
		extract( $r );

		// Sanitization
		if ( !in_array( strtolower( $orderby ), array( 'id', 'enabled', 'status', 'issue_date', 'application_uri', 'application_title', 'application_descr' ) ) ) {
			$orderby = 'issue_date';
		}

		if ( !in_array( strtoupper( $order ), array( 'ASC', 'DESC' ) ) ) {
			$order = 'DESC';
		}

		// Convert paged/per page to limits
		$lower_limit = (int) ( $per_page * ( $paged - 1 ) );
		$upper_limit = (int) ( $lower_limit + $per_page );

		$apps = $this->query_all_assoc( "
				SELECT SQL_CALC_FOUND_ROWS
				    osr_id			as id,
				    osr_enabled			as enabled,
				    osr_status 			as status,
				    osr_issue_date		as issue_date,
				    osr_application_uri		as application_uri,
				    osr_application_title	as application_title,
				    osr_application_descr	as application_descr
				FROM oauth_server_registry
				ORDER BY {$orderby} {$order}
				LIMIT {$lower_limit}, {$upper_limit}
				" );

		// Fake a WP_Query
		$rs = new stdClass;
		$rs->posts = $apps;
		$rs->found_posts = $wpdb->get_var( "SELECT FOUND_ROWS()" );
		$rs->max_num_pages = ceil( $rs->found_posts / $per_page );

		return $rs;
	}

	/**
	 * Get a list of all consumers from the consumer registry.
	 * The consumer keys belong to the user or are public (user id is null)
	 *
	 * @param string q	query term
	 * @param int user_id
	 * @return array
	 */
	public function listServers ( $args = array(), $deprecated = 0 )
	{
		global $wpdb;

		$defaults = array(
			'paged'    => 1,
			'per_page' => 20,
			'order'    => 'ASC',
			'orderby'  => 'id'
		);
		$r = wp_parse_args( $args, $defaults );
		extract( $r );

		// Sanitization
		if ( !in_array( strtolower( $orderby ), array( 'id', 'user_id', 'consumer_key', 'consumer_secret', 'signature_methods', 'server_uri', 'server_uri_host', 'server_uri_path', 'request_token_uri', 'authorize_uri', 'access_token_uri' ) ) ) {
			$orderby = 'id';
		}

		if ( !in_array( strtoupper( $order ), array( 'ASC', 'DESC' ) ) ) {
			$order = 'ASC';
		}

		// Convert paged/per page to limits
		$lower_limit = (int) ( $per_page * ( $paged - 1 ) );
		$upper_limit = (int) ( $lower_limit + $per_page );

		$servers = $this->query_all_assoc( "
			SELECT SQL_CALC_FOUND_ROWS
				ocr_id			as id,
				ocr_usa_id_ref		as user_id,
				ocr_consumer_key 	as consumer_key,
				ocr_consumer_secret 	as consumer_secret,
				ocr_signature_methods	as signature_methods,
				ocr_server_uri		as server_uri,
				ocr_server_uri_host	as server_uri_host,
				ocr_server_uri_path	as server_uri_path,
				ocr_request_token_uri	as request_token_uri,
				ocr_authorize_uri	as authorize_uri,
				ocr_access_token_uri	as access_token_uri
			FROM oauth_consumer_registry
			ORDER BY {$orderby} {$order}
			LIMIT {$lower_limit}, {$upper_limit}
			" );

		// Fake a WP_Query
		$rs = new stdClass;
		$rs->posts = $servers;
		$rs->found_posts = $wpdb->get_var( "SELECT FOUND_ROWS()" );
		$rs->max_num_pages = ceil( $rs->found_posts / $per_page );

		return $rs;
	}

	/**
	 * Delete a token we obtained from a server.
	 *
	 * Overridden here because of what appears to be some invalid SQL in the original.
	 *
	 * @see OAuthStoreSQL::deleteServerToken()
	 *
	 * @param string consumer_key
	 * @param string token
	 * @param int user_id
	 * @param boolean user_is_admin
	 */
	public function deleteServerToken ( $consumer_key, $token, $user_id, $user_is_admin = false )
	{
		global $wpdb;

		$table_oct = $wpdb->get_blog_prefix( bp_get_root_blog_id() ) . 'oauth_consumer_token';
		$table_ocr = $wpdb->get_blog_prefix( bp_get_root_blog_id() ) . 'oauth_consumer_registry';

		if ($user_is_admin)
		{
			// Get the oct_id
			$oct_id = $wpdb->get_var( $wpdb->prepare( "SELECT oct_id FROM {$table_oct} JOIN {$table_ocr} on {$table_oct}.oct_ocr_id_ref = {$table_ocr}.ocr_id WHERE {$table_ocr}.ocr_consumer_key = %s AND oct_token = %s", $consumer_key, $token ) );

		}
		else
		{
			// Get the oct_id
			$oct_id = $wpdb->get_var( $wpdb->prepare( "SELECT oct_id FROM {$table_oct} JOIN {$table_ocr} on {$table_oct}.oct_ocr_id_ref = {$table_ocr}.ocr_id WHERE {$table_ocr}.ocr_consumer_key = %s AND oct_token = %s AND oct_usa_id_ref = %d", $consumer_key, $token, $user_id ) );

		}

		if ( $oct_id ) {
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$table_oct} WHERE oct_id = %s", $oct_id ) );
		}
	}

	public function getTokenByOcrIdRef( $ref ) {
		global $wpdb;

		$table_oct = $wpdb->get_blog_prefix( bp_get_root_blog_id() ) . 'oauth_consumer_token';

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_oct WHERE oct_ocr_id_ref = %d", $ref ) );
	}


	/**
	 * Set the ttl of a server access token.  This is done when the
	 * server receives a valid request with a xoauth_token_ttl parameter in it.
	 *
	 * There appears to be a bug in the original method, resulting in invalid MySQL syntax
	 *
	 * @param string consumer_key
	 * @param string token
	 * @param int token_ttl
	 */
	public function setCServerTokenTtl ( $consumer_key, $token, $token_ttl )
	{
		global $wpdb;

		if ($token_ttl <= 0)
		{
			// Immediate delete when the token is past its ttl
			$this->deleteServerToken($consumer_key, $token, 0, true);
		}
		else
		{
			// Set maximum time to live for this token
			$sql = $wpdb->prepare( "
					UPDATE oauth_server_token, oauth_server_registry
					SET ost_token_ttl = DATE_ADD(NOW(), INTERVAL %d SECOND)
					WHERE osr_consumer_key  = %s
					  AND ost_osr_id_ref    = osr_id
					  AND ost_token         = %s
					", $token_ttl, $consumer_key, $token );

			$q = $wpdb->query( $sql );
		}
	}

}

?>