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
		$sql = $this->sql_printf(func_get_args());
		return $this->query( $sql );
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

}

?>