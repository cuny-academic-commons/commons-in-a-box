<?php

/**
 * This class handles incoming BuddyPress API requests, after they've been delegated by the
 * Server class.
 *
 * Instantiate class, passing an argument array:
 *   $args = array(
 *       'action' => 'add_user_to_group', // The action to perform. Required
 *       'group_id' => 5,                 // Group id, if relevant
 *       'user_id' => 10,                 // User id, if relevant
 *       'profile_field_id' => 20,        // Profile field id, if relevant
 *       'profile_field_data' => 'foo',   // Profile field data, if relevant
 *   );
 */
class BP_API_Server_Request {
	/**
	 * Data to return on success
	 */
	protected $return_data;
	
	/**
	 * Human-readable error message to return on failure
	 */
	protected $return_error;
	
	/**
	 * HTTP code to return
	 */
	protected $return_status_code = 200;
	
	/**
	 * List of HTTP status codes
	 * @todo Move to init file?
	 */
	protected $status_codes = array();
	
	/**
	 * Authentication status
	 */
	protected $authentication_status = '';
	
	/**
	 * Constructor
	 */
	public function __construct( $args ) {
		$this->setup_status_codes();
		
		$this->authenticate();
		
		// @todo Do an authentication block here?
		if ( 200 == $this->get_status_code() ) {
			$this->setup_action( $args );
		}
	}
	
	/**
	 * @todo Authentication
	 */
	protected function authenticate() {
		$this->authentication_status = 'OK';	
	}
	
	protected function setup_action( $args ) {
		// Instantiate the BP_API_Request_Action object
		require_once( dirname(__FILE__) . '/class.api-server-request-action.php' );
		$this->bp_action = new BP_API_Server_Request_Action( $args );
		
		$response = $this->bp_action->get_response();
		$message = empty( $response['message'] ) ? false : $response['message'];
		$this->set_status( $response['code'], $message );
	}
	
	public function get_authentication_status() {
		return $this->authentication_status;
	}
	
	public function get_status_code() {
		return $this->return_status_code;
	}
	
	/**
	 * Set the error
	 *
	 * You can pass an option $message, or fall back on the default
	 */
	protected final function set_status( $error_code, $message = false ) {		
		// Make sure that the error code is on our whitelist. Otherwise fall back to 500		
		$this->return_status_code = (int) $error_code;
		
		if ( !$this->return_error = $this->get_message_from_status_code( $this->return_status_code ) ) {
			$this->return_error = $this->get_message_from_status_code( 500 );
			$this->return_status_code = 500;
		}
		
		if ( false !== $message ) {
			$this->return_error = $message;
		}
	}
	
	/**
	 * Get the message corresponding to a status code
	 *
	 * @param int $error_code
	 * @return string
	 */
	protected final function get_message_from_status_code( $error_code = 200 ) {
		$error_code = (int) $error_code;
		
		if ( isset( $this->status_codes[$error_code] ) ) {
			return $this->status_codes[$error_code];
		} else {
			return '';
		}
	}
	
	/**
	 * Sets up status codes
	 */
	protected function setup_status_codes() {
		// Todo: pare down
		// Todo: l18n
		$this->status_codes = array(
			100 => 'Continue',
			101 => 'Switching Protocols',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			306 => '(Unused)',
			307 => 'Temporary Redirect',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported'
		);
	}
}


?>