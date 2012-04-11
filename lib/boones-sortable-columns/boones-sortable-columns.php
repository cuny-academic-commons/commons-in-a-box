<?php

if ( !class_exists( 'BBG_CPT_Sort' ) ) :

class BBG_CPT_Sort {
	/**
	 * The full column data
	 */
	var $columns;
	
	var $column_count;
	var $current_column;
	var $column;
	var $in_the_loop;
	
	/**
	 * Default orderby column
	 */
	var $default_orderby;
	
	/**
	 * The desired $_GET keys for orderby and order
	 */
	var $get_orderby_key;
	var $get_order_key;
	
	/**
	 * The values of orderby and order as retrieved from $_GET
	 */
	var $get_orderby;
	var $get_order;
	
	var $sortable_keys;
	
	/**
	 * The URL used as a base for concatenating links
	 */
	var $base_url;
	
	/**
	 * PHP 4 constructor
	 *
	 * @package Boone's Sortable Columns
	 * @since 1.0
	 */
	function bbg_cpt_sort( $cols = false ) {
		$this->__construct( $cols );
	}

	/**
	 * PHP 5 constructor
	 *
	 * @package Boone's Sortable Columns
	 * @since 1.0
	 *
	 * $columns should be an array of arrays. That is, an array of args for each column. See
	 * $defaults below for an explanation of these arrays.
	 */	
	function __construct( $cols = false ) {
		
		/**
		 * This defines the default values for *each column*. Please note that the $cols
		 * param for this method is an array of arrays like this.
		 *
		 * Values:
		 *   - 'name'	The unique identifier of the column, also used (for the time being)
		 *		as the key for the URL param (eg &orderby=name). When possible, it's
		 *		handy to use the same orderby param that you use when constructing
		 *		the posts query. So, for example, you might use 'author' or 'date'.
		 *		If you don't do this (or can't, because it's not a column natively
		 * 		understood by WP_Query, or because you're not sorting a WP_Query)
		 *		you'll have to do some translation between this 'name' value and
		 *		your actual sorting code, whatever it is.
		 *   - 'title'	The text that will be used to create the column header. 
		 *   - 'is_sortable' True if you want the column to be sortable, false if not
		 *   - 'css_class' This object will create a CSS selector that is tailored for use 
		 *		in <th> elements of WP Dashboard 'widefat' tables. That complex CSS
		 *		selector will automatically contain classes like 'sortable' and
		 *		'asc', depending on the parameters and on your current page.
		 *		css_class is an additional class that will be added to the selector
		 *		so that you can do column-specific styling. If you don't provide
		 *		a css_class, it'll default to the 'name' parameter.
		 *   - 'default_order' Accepts 'asc' or 'desc'. Usually you'll want 'asc', except
		 *		for date-based columns, when it generally makes sense for the
		 *		most recent columns to be listed first.
		 *   - 'posts_column' Right now this does nothing :)
		 *   - 'is_default' True if you want the given column to be the default sort order.
		 *		If more than one of your columns have 'is_default' set to true, the
		 *		last one will take precedence.
		 */
		$defaults = array(
			'name'		=> false,
			'title'		=> false,
			'is_sortable'	=> true,
			'css_class'	=> false,
			'default_order'	=> 'asc',
			'posts_column'	=> false,
			'is_default'	=> false
		);
	
		$this->columns = array();
		$this->sortable_keys = array();
	
		foreach( $cols as $col ) {
			// You need at least a name and a title to continue
			if ( empty( $col['name'] ) || empty( $col['title'] ) )
				continue;
				
			$r = wp_parse_args( $col, $defaults );
			
			// If the css_class is not set, just use the name param
			if ( empty( $r['css_class'] ) )
				$r['css_class'] = $r['name'];
		
			// Check to see whether this is a default. Providing more than one default
			// will mean that the last one overrides the others
			if ( !empty( $r['is_default'] ) )
				$this->default_orderby = $r['name'];
		
			// Compare the default order against a whitelist of 'asc' and 'desc'
			if ( 'asc' == strtolower( $r['default_order'] ) || 'desc' == strtolower( $r['default_order'] ) ) {
				$r['default_order'] = strtolower( $r['default_order'] );
			} else {
				$r['default_order'] = 'asc';
			}
			
			// If it's sortable, add the name to the $sortable_keys array
			if ( $r['is_sortable'] )
				$this->sortable_keys[] = $r['name'];
		
			// Convert to an object for maximum prettiness
			$col_obj = new stdClass;
			
			foreach( $r as $key => $value ) {
				$col_obj->$key = $value;
			}
			
			$this->columns[] = $col_obj;
		}
					
		// Now, set up some values for the loop
		$this->column_count = count( $this->columns );
		$this->current_column = -1;
		
		// If a default orderby was not found, just choose the first item in the array
		if ( empty( $this->default_orderby ) && !empty( $cols[0]['name'] ) ) {
			$this->default_orderby = $cols[0]['name'];
		}
		
		// Set up the $_GET keys (which are customizable)
		$this->setup_get_keys();
		
		// Get the pagination parameters out of $_GET
		$this->setup_get_params();
		
		// Set up the next orders (asc or desc) depending on current state
		$this->setup_next_orders();
	
		// Set up the URL to be used as a base for href links
		$this->setup_base_url();
	}
	
	/**
	 * Sets up the $_GET param keys.
	 *
	 * You can either override this function in your own extended class, or filter the default
	 * values. I have provided both options because I love you so very much.
	 *
	 * @package Boone's Sortable Columns
	 * @since 1.0
	 */
	function setup_get_keys() {
		$this->get_orderby_key	= apply_filters( 'bbg_cpt_sort_orderby_key', 'orderby' );
		$this->get_order_key 	= apply_filters( 'bbg_cpt_sort_order_key', 'order' );
	}
	
	/**
	 * Gets params out of $_GET global
	 *
	 * Does some basic sanity checks on the orderby and order parameters, ensuring that the
	 * 'order' param is either 'asc' or 'desc', and that the 'orderby' param actually matches
	 * one of the columns fed to the constructor.
	 *
	 * @package Boone's Sortable Columns
	 * @since 1.0
	 */
	function setup_get_params() {
		// Orderby
		$orderby = isset( $_GET[$this->get_orderby_key] ) ? $_GET[$this->get_orderby_key] : false;
		
		// If an orderby param is provided, check to see that it's permitted.
		// Otherwise set the current orderby to the default
		if ( $orderby && in_array( $orderby, $this->sortable_keys ) ) {
			$this->get_orderby = $orderby;
		} else {
			$this->get_orderby = $this->default_orderby;		
		}
		
		// Order	
		$order = isset( $_GET[$this->get_order_key] ) ? $_GET[$this->get_order_key] : false;
		
		// If an order is provided, make sure it's either 'desc' or 'asc'
		// Otherwise set current order to the orderby's default order
		if ( $order && ( 'desc' == strtolower( $order ) || 'asc' == strtolower( $order ) ) ) {
			$order = strtolower( $order );
		} else {
			// Loop through to find the default order for this bad boy
			// This is not optimized because of the way the array is keyed
			// Cry me a river why don't you
			foreach( $this->columns as $col ) {
				if ( $col->name == $this->get_orderby ) {	
					$order = $col->default_order;
					break;
				}
			}
		}
		
		// There should only be two options, 'asc' and 'desc'. We'll cut some slack for
		// uppercase variants
		$order = 'desc' == strtolower( $order ) ? 'desc' : 'asc';
		
		$this->get_order = $order;
	}
	
	/**
	 * Loops through the columns and determines what the next_order should be
	 *
	 * In other words: when you are currently sorting by (for example) post_date ASC, the
	 * next_order for the post_date column should be DESC. For all columns that are not the
	 * current sort order, the next_order should be the default_order of that column.
	 *
	 * The next_order values are used to create the href of the column header links, as well
	 * as the CSS selectors 'asc' and 'desc' that the WP admin CSS/JS need to do fancy schmancy
	 * mouseovers.
	 *
	 * @package Boone's Sortable Columns
	 * @since 1.0
	 */
	function setup_next_orders() {
		foreach( $this->columns as $name => $col ) {
			if ( $col->name == $this->get_orderby ) {
				$current_order = $this->get_order;
				$next_order = 'asc' == $current_order ? 'desc' : 'asc';
			} else {
				$next_order = $col->default_order;
			}
			
			$this->columns[$name]->next_order = $next_order;
		}
	}
	
	/**
	 * Set the base url that will be used for creating links
	 *
	 * By default, Boone's Sortable Columns will use your current URL as the base for creating
	 * the clickable headers. (To be more specific, it uses add_query_arg() with a null value
	 * for the query/url param, so that it defaults to $_SERVER['REQUEST_URI']. See 
	 * add_query_arg() for more details.)
	 *
	 * In some cases, you may want to use a special URL for this purpose. For instance, you may 
	 * want to remove certain query argument. In this function, I assume that you *always* want
	 * to remove _wpnonce, since that should be generated on the fly. I also assume that when 
	 * a column is resorted, pagination should be reset (thus the presence of 'paged' and
	 * 'per_page' on the blacklist). If you want to remove additional query arguments (such as
	 * those used to generate success messages, etc), filter 
	 * boones_sortable_columns_keys_to_remove.
	 *
	 * You can also override this behavior by feeding your own custom value to the method,
	 * immediately after instantiating the class. For example,
	 *   $sortable = new BBG_CPT_Sort( $cols );
	 *   $sortable->setup_base_url( 'http://example.com' );
	 * Or, of course, you can override the method in your own class.
	 *
	 * @package Boone's Sortable Columns
	 * @since 1.0.1
	 *
	 * @param str $url The base URL. Optional. Defaults to $_SERVER['REQUEST_URI'].
	 */
	function setup_base_url( $url = false ) {
		if ( !$url ) {
			$current_keys = array_keys( $_GET );
			
			// These are keys that will always be removed from the base url
			$keys_to_remove = apply_filters( 'boones_sortable_columns_keys_to_remove', array(
				'_wpnonce',
				'paged'
			) );
		
			foreach( $keys_to_remove as $key ) {
				$url = remove_query_arg( $key, $url );
			}
		}
		
		$this->base_url = $url;
	}
	
	/**
	 * Part of the Loop
	 *
	 * As in the regular WP post Loop, you can loop through the columns like so:
	 *   $sortable = new BBG_CPT_Sort( $cols );
	 *   if ( $sortable->have_columns() ) {
	 *      while ( $sortable->have_columns() ) {
	 *         $sortable->the_column();
	 *      }
	 *    }
	 *
	 * @package Boone's Sortable Columns
	 * @since 1.0
	 */
	function have_columns() {
		// Compare against the column_count - 1 to account for the 0 array index shift
		if ( $this->column_count && $this->current_column < $this->column_count - 1 )
			return true;

		return false;
	}

	/**
	 * Part of the Loop
	 *
	 * @package Boone's Sortable Columns
	 * @since 1.0
	 */
	function next_column() {
		$this->current_column++;
		$this->column = $this->columns[$this->current_column];

		return $this->column;
	}

	/**
	 * Part of the Loop
	 *
	 * @package Boone's Sortable Columns
	 * @since 1.0
	 */
	function rewind_columns() {
		$this->current_column = -1;
		if ( $this->column_count > 0 ) {
			$this->column = $this->columns[0];
		}
	}

	/**
	 * Part of the Loop
	 *
	 * @package Boone's Sortable Columns
	 * @since 1.0
	 */
	function the_column() {
		$this->in_the_loop = true;
		$this->column = $this->next_column();

		if ( 0 == $this->current_column ) // loop has just started
			do_action('loop_start');
	}
	
	/**
	 * Constructs a complex CSS selector for the column header
	 *
	 * This set of CSS classes is designed to work seamlessly with WP's admin CSS and JS for
	 * <th> elements inside of <table class="widefat">. With just a bit of custom CSS and JS,
	 * though, the same class can work well on the front end as well.
	 *
	 * The following classes are created, as appropriate:
	 *   - 'sortable' / 'sorted'
	 *   - 'asc' / 'desc'
	 *   - the custom css_class fed to BBG_CPT_Sort::__construct()
	 *
	 * @package Boone's Sortable Columns
	 * @since 1.0
	 *
	 * @param str $type 'echo' if you want the result echo, 'return' if you want it returned
	 * @return str $class The CSS classes, separated by spaces
	 */
	function the_column_css_class( $type = 'echo' ) {
		// The column-identifying class
		$class = array( $this->column->css_class );
		
		// Sortable logic
		if ( $this->column->is_sortable ) {
			// Add the sorted/sortable class, based on whether this is the current sort
			if ( $this->column->name == $this->get_orderby ) {
				$class[] = 'sorted';
				$class[] = $this->get_order;
			} else {
				$class[] = 'sortable';
				$class[] = 'asc' == $this->column->default_order ? 'desc' : 'asc';
			}
		}
		
		$class = implode( ' ', $class );
		
		if ( 'echo' == $type )
			echo $class;
		else
			return $class;
	}
	
	/**
	 * Constructs the href URL for the column header
	 *
	 * This method is really the raison d'être of Boone's Sortable Columns, so make sure you
	 * read this docblock carefully.
	 *
	 * Sortable columns work by turning each column header into a link that, when clicked, will
	 * return new results that are sorted based on your requests. Making the URLs for those
	 * links can be complex, however. This method will do all the heavy lifting for you, 
	 * producing a URL or an entire anchor tag that you can use as the column header.
	 *
	 * For example, let's say your current URL is http://example.com/restaurants, which displays
	 * a list of restaurants which are, by default, sorted by restaurant name, in ascending
	 * alphabetical order. The column header for the Restaurant Name column should be a link
	 * to sort the list by restaurant name in *descending* alphabetical order, while, for
	 * example, the Cuisine column should be a link to sort the list by cuisine type, in 
	 * ascending order. Accordingly (assuming you've instantiated the class properly; see
	 * readme.txt for more instructions), the following lines of code
	 * 
	 *   <?php if ( $sortable->have_columns() ) : ?> 
	 *      <?php while ( $sortable->have_columns() ) : $sortable->the_column() ?>
	 *	   <?php $sortable->the_column_next_link() ?>
	 *	<?php endwhile ?>
	 *   <?php endif ?>
	 * 
	 * will output the following HTML:
	 *
	 *   <a href="http://example.com/restaurants?orderby=restaurant_name&order=desc">Restaurant
	 *   Name</a>
	 *   <a href="http://example.com/restaurants?orderby=cuisine_type&order=asc">Cuisine
	 *   Type</a>
	 *
	 * @package Boone's Sortable Columns
	 * @since 1.0
	 *
	 * @param str $type 'echo' if you want the result echo, 'return' if you want it returned
	 * @param str $html_or_url 'html' if you want the entire anchor HTML, or 'url' if you just
	 *    want the URL for the href
	 * @return str $link The link URL or the HTML anchor object, depending on the $html_or_url
	 *    param
	 */
	function the_column_next_link( $type = 'echo', $html_or_url = 'html' ) {
		$args = array(
			$this->get_orderby_key	=> $this->column->name,
			$this->get_order_key	=> $this->column->next_order
		);
		
		$url = add_query_arg( $args, $this->base_url );
		
		// Assemble the html link, if necessary
		if ( 'html' == $html_or_url ) {
			$html = sprintf( '<a title="%1$s" href="%2$s">%3$s</a>', $this->column->name, $url, $this->the_column_title( 'return' ) );
			
			$link = $html;
		} else {
			$link = $url;
		}
		
		if ( 'echo' == $type )
			echo $link;
		else
			return $link;
	}
	
	/**
	 * Gets the title text for the column header
	 *
	 * Essentially, this just returns the value of 'title' fed to $cols. See
	 * BBG_CPT_Sort::__construct() for more information
	 *
	 * @package Boone's Sortable Columns
	 * @since 1.0
	 *
	 * @param str $type 'echo' if you want the result echo, 'return' if you want it returned
	 * @return str $class The title
	 */
	function the_column_title( $type = 'echo' ) {
		$name = $this->column->title;
		
		if ( 'echo' == $type )
			echo $name;
		else
			return $name;
	}
	
	/**
	 * Constructs a <th> column header element out of the column data
	 *
	 * The item returned will look something like this:
	 *   <th class="manage-column login sortable desc" scope="col">
	 *      <a href="http://example.com/restaurants?orderby=restaurant_name&order=asc">
	 *         <span>Restaurant Name</span>
	 *         <span class="sorting-indicator"></span>
	 *      </a>
	 *   </th>
	 *
	 * This is intended for use in <table class="widefat"> on the WordPress Dashboard, and will
	 * take advantage of WordPress's nice CSS and JavaScript governing the appearance and
	 * behavior of these column headers. You can also use these <td>s in other tables (say, on
	 * the front-end of your WordPress site), but you'll have to duplicate some of WP's core
	 * CSS and JS if you want it to be all pretty-like.
	 *
	 * @package Boone's Sortable Columns
	 * @since 1.0
	 *
	 * @param str $type 'echo' if you want the result echo, 'return' if you want it returned
	 * @return str $class The <th> element
	 */
	function the_column_th( $type = 'echo' ) {
		if ( $this->column->is_sortable ) {
			$td_content = sprintf( '<a href="%1$s"><span>%2$s</span><span class="sorting-indicator"></span></a>', $this->the_column_next_link( 'return', 'url' ), $this->the_column_title( 'return' ) );
		} else {
			$td_content = $this->the_column_title( 'return' );
		
		}
	
		$html = sprintf( '<th scope="col" class="manage-column %1$s">%2$s</th>', $this->the_column_css_class( 'return' ), $td_content );
		
		if ( 'echo' == $type )
			echo $html;
		else
			return $html;
	}
}

endif;

?>