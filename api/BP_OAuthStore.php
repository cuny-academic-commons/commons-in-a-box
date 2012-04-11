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
		return $wpdb->get_row( $sql, ARRAY_A );
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


}

?>