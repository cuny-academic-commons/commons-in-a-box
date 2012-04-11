<?php

if ( !class_exists( 'BBG_CPT_Pag' ) ) :

class BBG_CPT_Pag {
	/**
	 * The CPT query. Defaults to $wp_query; see BBG_CPT_Pag::setup_query()
	 */
	var $query;
	
	/**
	 * The desired $_GET keys for per_page and paged
	 */
	var $get_per_page_key;
	var $get_paged_key;
	
	/**
	 * The values of per_page and paged as retrieved from $_GET
	 */
	var $get_per_page;
	var $get_paged;
	
	/**
	 * The number of items found, and the total page number based on this
	 */
	var $total_items;
	var $total_pages;
	
	/**
	 * PHP 4 constructor
	 *
	 * @package Boone's Pagination
	 * @since 1.0
	 */
	function bbg_cpt_pag() {
		$this->__construct();
	}

	/**
	 * PHP 5 constructor
	 *
	 * @package Boone's Pagination
	 * @since 1.0
	 */	
	function __construct( $query = false ) {		
		// Set up the $_GET keys (which are customizable)
		$this->setup_get_keys();
		
		// Get the pagination parameters out of $_GET
		$this->setup_get_params();	
	}
	
	/**
	 * Sets up query vars.
	 *
	 * I recommend that you instantiate this class right away when you start rendering the page,
	 * so that it can do some of the $_GET argument parsing for you, which you can use to
	 * construct your CPT query (query_posts() or new WP_Query). Then, after you have made the
	 * query, call this function manually, in order to populate the class with query-specific
	 * data.
	 *
	 * If you use query_posts() to construct the query, there's no need to pass along a $query
	 * parameter - the function will simply look inside of the $wp_query global. However, if
	 * you use WP_Query to run your query (so that the data is not in $wp_query), you should
	 * pass your query object along to setup_query().
	 *
	 * @package Boone's Pagination
	 * @since 1.0
	 */
	function setup_query( $query = false ) {
		global $wp_query;
		
		if ( !$query )
			$query =& $wp_query;
			
		$this->query = $query;
	
		// Get the total number of items
		$this->setup_total_items();
		
		// Get the total number of pages
		$this->setup_total_pages();	
	}
	
	/**
	 * Sets up the $_GET param keys.
	 *
	 * You can either override this function in your own extended class, or filter the default
	 * values. I have provided both options because I love you so very much.
	 *
	 * @package Boone's Pagination
	 * @since 1.0
	 */
	function setup_get_keys() {
		$this->get_per_page_key = apply_filters( 'bbg_cpt_pag_per_page_key', 'per_page' );
		
		/**
		 * I chose 'paged' as the default not because I like it - I don't - but because
		 * other choices threatened to interfere with native WP functions. In particular,
		 * 'page' is already used in the Dashboard area to signify a plugin settings page.
		 */
		$this->get_paged_key 	= apply_filters( 'bbg_cpt_pag_paged_key', 'paged' );
	}
	
	/**
	 * Gets params out of $_GET global
	 *
	 * Does some basic checks to ensure that the values are integers and that they are non-empty
	 *
	 * @package Boone's Pagination
	 * @since 1.0
	 */
	function setup_get_params() {
		// Per page
		$per_page = isset( $_GET[$this->get_per_page_key] ) ? $_GET[$this->get_per_page_key] : 10;
		
		// Basic per_page sanity and security
		if ( !(int)$per_page )
			$per_page = 10;
		
		$this->get_per_page = $per_page;
		
		// Page number		
		$paged = isset( $_GET[$this->get_paged_key] ) ? $_GET[$this->get_paged_key] : 1;
		
		// Basic paged sanity and security
		if ( !(int)$paged )
			$paged = 1;
		
		$this->get_paged = $paged;
	}
	
	/**
	 * Get the total number of items out of the query
	 *
	 * @package Boone's Pagination
	 * @since 1.0
	 */
	function setup_total_items() {
		$this->total_items = $this->query->found_posts;
	}
	
	/**
	 * Get the total number of pages out of the query
	 *
	 * @package Boone's Pagination
	 * @since 1.0
	 */
	function setup_total_pages() {
		$this->total_pages = $this->query->max_num_pages;
	}
	
	/**
	 * Get the start number for the current view (ie "Viewing *5* - 8 of 12")
	 *
	 * Here's the math: Subtract one from the current page number; multiply times posts_per_page
	 * to get the last post on the previous page; add one to get the start for this page.
	 *
	 * @package Boone's Pagination
	 * @since 1.0
	 *
	 * @return int $start The start number
	 */
	function get_start_number() {		
		$start = ( ( $this->get_paged - 1 ) * $this->get_per_page ) + 1;
		
		return $start;
	}
	
	/**
	 * Get the end number for the current view (ie "Viewing 5 - *8* of 12")
	 *
	 * Here's the math: Multiply the posts_per_page by the current page number. If it's the last
	 * page (ie if the result is greater than the total number of docs), just use the total doc
	 * count
	 *
	 * @package Boone's Pagination
	 * @since 1.0
	 *
	 * @return int $end The start number
	 */
	function get_end_number() {
		global $wp_query;
		
		$end = $this->get_paged * $this->get_per_page;
		
		if ( $end > $this->total_items )
			$end = $this->total_items;
		
		return $end;
	}
	
	/**
	 * Return or echo the "Viewing x-y of z" message
	 *
	 * @package Boone's Pagination
	 * @since 1.0
	 *
	 * @param str $type Optional. 'echo' will echo the results, anything else will return them
	 * @return str $page_links The "viewing" text
	 */
	function currently_viewing_text( $type = 'echo' ) {	 
		$start  = $this->get_start_number();
		$end	= $this->get_end_number();
		
		$string = sprintf( __( 'Viewing %1$d - %2$d of a total of %3$d', 'bbg-cpt-pag' ), $start, $end, $this->total_items );
		
		if ( 'echo' == $type )
			echo $string;
		else
			return $string;
	}
	
	/**
	 * Return or echo the pagination links
	 *
	 * @package Boone's Pagination
	 * @since 1.0
	 *
	 * @param str $type Optional. 'echo' will echo the results, anything else will return them
	 * @return str $page_links The pagination links
	 */
	function paginate_links( $type = 'echo' ) {	 
		$page_links = paginate_links( array(
			'base' 		=> add_query_arg( $this->get_paged_key, '%#%' ),
			'format' 	=> '',
			'prev_text' 	=> __( '&laquo;' ),
			'next_text' 	=> __( '&raquo;' ),
			'total' 	=> $this->total_pages,
			'current' 	=> $this->get_paged,
			'add_args'	=> array( $this->get_per_page_key => $this->get_per_page )
		));
		
		if ( 'echo' == $type )
			echo $page_links;
		else
			return $page_links;
	}
}

endif;

?>