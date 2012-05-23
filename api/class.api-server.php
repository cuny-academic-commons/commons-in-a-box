<?php

/**
 * BuddyPress API server
 */
class BP_API_Server extends BP_Component {
	protected $request;
	protected $restler;

	/**
	 * Constructor
	 */
	public function __construct() {
		global $bp;

		// for oauth
		session_start();

		parent::start(
			'api',
			__( 'API Server', 'cbox' ),
			dirname(__FILE__)
		);

		$bp->active_components[$this->id] = '1';

		require( CIAB_PLUGIN_DIR . 'api/functions.php' );

		if ( is_admin() || is_network_admin() ) {
			require( CIAB_PLUGIN_DIR . 'api/admin.php' );
		}

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

		add_action( 'admin_menu', array( &$this, 'setup_admin_panels' ) );
	}

	/**
	 * Sets up a URL endpoint for incoming HTTP requests
	 *
	 * This is a temporary implementation
	 */
	public function endpoint() {
		global $bp;
		if ( bp_is_current_component( $bp->api->id ) ) {

			if ( bp_is_current_action( 'addclient' ) ) {
				if ( !empty( $_POST ) ) {
					$this->process_addclient();
				}
				bp_api_load_template( 'api/addclient' );
			} else if ( bp_is_current_action( 'request_token' ) ) {
				$this->process_request_token();
			} else if ( bp_is_current_action( 'authorize' ) ) {
				$this->process_authorize();
			} else if ( bp_is_current_action( 'access_token' ) ) {
				$this->process_access_token();
			}

			require( CIAB_PLUGIN_DIR . 'lib/restler/restler.php' );
			require( CIAB_PLUGIN_DIR . 'api/class.bp-restler.php' );
			$this->restler = new BP_Restler;

			do_action( 'bp_api_init' );

			/**
			 * @todo Make this non-nonsense
			 *
			 * Be sure you know what you're doing before you disable OAuth! You can
			 * enable admin access to your whole site if you're not careful!
			 *
			 * You can override OAuth by supplying your own BP_API_Auth class. Make sure
			 * it's loaded before we get to this point, so that we don't load OAuth.
			 */
			if ( !class_exists( 'BP_API_Auth' ) ) {
				require( CIAB_PLUGIN_DIR . 'api/class.auth.php' );
			}
			$this->restler->addAuthenticationClass( 'BP_API_Auth' );

			require( CIAB_PLUGIN_DIR . 'api/class.api-server-actions.php' );
			$this->restler->addAPIClass( 'BP_API_Server_Actions' );

			$this->restler->handle();
			die();
		}
	}

	function install_oauth_store() {
		global $wpdb;

		// todo - move!
		$sql = file_get_contents( CIAB_LIB_DIR . 'oauth-php/library/store/mysql/mysql.sql');
		$ps  = explode('#--SPLIT--', $sql);

		foreach( $ps as $p ) {
			$p = str_replace( 'oauth_', $wpdb->base_prefix . 'oauth_', $p );
			$wpdb->query( $p );
		}
	}

	function process_addclient() {
		global $wpdb, $bp;

		check_admin_referer( 'add_client' );

		// Check for required fields
		$user_id = 1; // TEMP

		// Assemble
		$consumer = array();
		$c_keys = array( 'requester_name', 'requester_email',
			'callback_uri', 'application_uri', 'application_title',
			'application_descr',
			'application_notes',
			'application_type',
			'application_commercial'
			);

		foreach( $c_keys as $c_key ) {
			if ( isset( $_POST[$c_key] ) ) {
				$consumer[$c_key] = $_POST[$c_key];
			}
		}

		$store = bp_api_get_oauth_store();
		$key   = $store->updateConsumer( $consumer, $user_id );

		// Get the complete consumer from the store
		$bp->api->consumer = $store->getConsumer( $key, $user_id );
	}

	function process_authorize() {
		if ( !is_user_logged_in() ) {
			bp_core_no_access( array( 'mode' => 2 ) );
		}

		if ( !empty( $_POST['allow'] ) ) {
			if ( !class_exists( 'OAuthServer' ) ) {
				require( CIAB_LIB_DIR . 'oauth-php/library/OAuthServer.php' );
			}

			$this->store = bp_api_get_oauth_store();
			$server = new OAuthServer();

			try {
				$server->authorizeVerify();
			} catch ( OAuthException2 $e ) {
				echo '<pre>'; print_r( $e ); echo '</pre>';
			}

			// Let's manually authorize the token


			// This method handles the redirect back to the client
			try {
				$server->authorizeFinish( true, 0 );
			} catch ( OAuthException2 $e ) {
				echo '<pre>'; print_r( $e ); echo '</pre>';
			}
		}

		bp_api_load_template( 'api/authorize' );
	}

	function process_request_token() {
		if ( !class_exists( 'OAuthServer' ) ) {
			require( CIAB_LIB_DIR . 'oauth-php/library/OAuthServer.php' );
		}

		$this->store = bp_api_get_oauth_store();
		$server = new OAuthServer();

		try {
			$token = $server->requestToken();
		} catch ( OAuthException2 $e ) {
			//echo '<pre>'; print_r( $e ); echo '</pre>';
		}

		// Manually modify the request token ttl (1000 years from now, groan)
		try {
			$this->store->setCServerTokenTtl( $server->getParam( 'oauth_consumer_key' ), $token, 60*60*24*365*1000 );
		} catch ( OAuthException2 $e ) {
			//echo '<pre>'; print_r( $e ); echo '</pre>';
		}

		die();
	}

	function process_access_token() {
		// The current user
		$user_id = 1;

		if ( !class_exists( 'OAuthServer' ) ) {
			require( CIAB_LIB_DIR . 'oauth-php/library/OAuthServer.php' );
		}

		// Fetch the oauth store and the oauth server.
		$store  = bp_api_get_oauth_store();
		$server = new OAuthServer();

		$at = $server->accessToken();

		die();

	}

	function setup_admin_panels() {

	}
}

/**
 * @todo Find a more elegant way of loading this
 */
function cbox_test() {
	global $bp;
	$bp->api = new BP_API_Server;

}
add_action( 'bp_loaded', 'cbox_test' );



?>