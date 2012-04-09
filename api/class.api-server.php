<?php

/**
 * BuddyPress API server
 *
 * This class is responsible for:
 *   - Setting up the API endpoint(s)
 *   - Processing incoming requests (whether internal or, more likely, over HTTP) and parsing them
 *     to a common format, for passing along to the Server_Request class
 *   - Formatting the request results (returned from Server_Request) into the requested format
 *     (xml, json, etc)
 *   - Returning the request results, along with any necessary header information
 */
class BP_API_Server extends BP_Component {
	protected $request;
	protected $restler;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		global $bp;
		
		parent::start(
			'api',
			__( 'API Server', 'cbox' ),
			dirname(__FILE__)
		);

		$bp->active_components[$this->id] = '1';
		$this->setup_hooks();
	}

	function setup_globals() {
		global $bp;

		// Defining the slug in this way makes it possible for site admins to override it
		if ( !defined( 'BP_API_SERVER_SLUG' ) )
			define( 'BP_API_SERVER_SLUG', $this->id );

		// Set up the $globals array to be passed along to parent::setup_globals()
		$globals = array(
			'slug'                  => BP_API_SERVER_SLUG,
			'root_slug'             => isset( $bp->pages->{$this->id}->slug ) ? $bp->pages->{$this->id}->slug : BP_API_SERVER_SLUG,
			'has_directory'         => true, // Set to false if not required
		);

		// Let BP_Component::setup_globals() do its work.
		parent::setup_globals( $globals );
	}
	
	public function setup_hooks() {
		// @todo This needs to be toggleable
		add_action( 'bp_actions', array( &$this, 'endpoint' ), 1 );
	}
	
	/**
	 * Sets up a URL endpoint for incoming HTTP requests
	 *
	 * This is a temporary implementation
	 */
	public function endpoint() {
		global $bp;
		if ( bp_is_current_component( $bp->api->id ) ) {
			require( CIAB_PLUGIN_DIR . 'lib/restler/restler.php' );
			require( CIAB_PLUGIN_DIR . 'api/class.bp-restler.php' );
			$this->restler = new BP_Restler;
			
			// @todo Make this non-nonsense
			require( CIAB_PLUGIN_DIR . 'api/class.auth.php' );			
			$this->restler->addAuthenticationClass( 'BP_API_Auth' );
			
			require( CIAB_PLUGIN_DIR . 'api/class.api-server-actions.php' );
			$this->restler->addAPIClass( 'BP_API_Server_Actions' );
			
			$this->restler->handle();
			die();
		}
	}
}



function cbox_test() {
	/*
	$args = array(
		'action' => 'update_profile_field',
		'user_id' => 1,
		'profile_field_id' => 1,
		'profile_field_data' => 'Top Notch Dude'
	);
	$test = new BP_API_Server_Request( $args );
	var_dump( $test );*/
	global $bp;
	$bp->api = new BP_API_Server;

}
add_action( 'bp_loaded', 'cbox_test' );



?>