<?php

/**
 * The BuddyPress API request actions
 */
class BP_API_Server_Request_Action {
	protected $action = '';
	
	/**
	 * API request parameters
	 */
	protected $params = array();
	
	protected $results = array();
	
	public function __construct( $args = array() ) {
		// Get the action out of the args
		// Parse the other params
		// Hand off to individual methods
		
		$this->setup_params( $args );
	
		if ( method_exists( &$this, $this->action ) ) {
			call_user_func( array( &$this, $this->action ) );
		}
	}
	
	/** PARAMETER HANDLERS ******************************************************/

	/**
	 * Parse the $args passed to the function. Validate.
	 *
	 * @param array $args See the documentation for this class
	 */
	protected function setup_params( $args ) {
		// The 'action' parameter is necessary
		if ( empty( $args['action'] ) ) {
			$this->set_response( 400, __( 'You must provide an "action" parameter.', 'cbox' ) );
		} else {
			$this->action = $args['action'];
			unset( $args['action'] );
			$this->params = $args;
		}		
	}
	
	/**
	 * Fetch the data corresponding to a given param key.
	 *
	 * Most of this data gets validated somehow. We cast to the proper type, and we check to
	 * make sure the referenced object exists
	 *
	 * @param string $key The parameter key
	 * @return mixed The validated return content
	 */
	function get_param( $key = false ) {
		$value = '';
		
		if ( !$key ) {
			return $value;
		}
		
		switch( $key ) {
			case 'user_id' :
				$value = $this->clean_param( $key, 'int' );
				
				// Check that the user exists. If not, $value is set to 0
				$user = new WP_User( $value );
				$value = $user->ID;
				
				break;
			
			case 'profile_field_id' :			
				if ( !bp_is_active( 'xprofile' ) ) {
					return 0;
				}
				
				$field = $this->clean_param( $key );
				
				// You can pass a field id or name
				if ( is_numeric( $field ) ) {
					$field_id = $field;
				} else {
					$field_id = xprofile_get_field_id_from_name( $field );
				}
				
				// Make sure the field exists
				$field_obj = xprofile_get_field( $field_id );
				
				$value = (int) $field_obj->id;
				
				break;
			
			case 'profile_field_data' :
				if ( !bp_is_active( 'xprofile' ) ) {
					return 0;
				}
				
				$value = $this->clean_param( $key, 'string' );
				
				break;
			
			default :
				// @todo I think this is where a hook will go?
				break;
			
		}
		
		return $value;
	}
	
	function clean_param( $key = '', $type = false ) {
		$value = '';
		
		// Be sure it exists
		if ( isset( $this->params[$key] ) ) {
			$value = $this->params[$key];
		}
		
		// Cast
		switch ( $type ) {
			case 'int' :
				$value = (int) $value;
				break;
			
			case 'string' :
				$value = (string) $value;
				break;
			
			case 'array' :
				$value = (array) $value;
				break;
			
			case 'object' :
				$value = (object) $value;
				break;
			
			default :
				break;
		}
		
		return $value;
	}
	
	/** RESPONSE HANDLERS *******************************************************/

	/**
	 * Set a response for this action.
	 *
	 * Generally we just use an unglossed HTTP code. This is shorthand, and is translated in
	 * the parent class to a canonical version. Optionally, we can pass a $message, which will
	 * override the default plaintext message set there.
	 *
	 * We don't do any validation here.
	 *
	 * @param int $code
	 * @param string $message Optional
	 */
	protected final function set_response( $code, $message = '' ) {
		$this->response = array(
			'code'    => $code,
			'message' => $message
		);
	}
	
	/**
	 * Get the response for this action
	 *
	 * @return array
	 */
	public final function get_response() {
		if ( empty( $this->response ) ) {
			$this->response = array(
				'code'    => '',
				'message' => ''
			);
		}
		
		return $this->response;
	}
	
	/**
	 * Have we already set an error for this action?
	 *
	 * This method is called throughout the class to check if we should proceed with various
	 * other actions.
	 *
	 * @return bool
	 */
	protected final function is_error() {
		$response = $this->get_response();
		
		if ( 200 == $response['code'] ) {
			return false;
		} else {
			return true;
		}
	}
	
	/** BUDDYPRESS ACTIONS ******************************************************/
	
	/**
	 * Updates an xprofile field
	 *
	 * @uses xprofile_set_field_data()
	 */
	function update_profile_field() {
		// Set up params
		$user_id            = $this->get_param( 'user_id' );
		$profile_field_id   = $this->get_param( 'profile_field_id' );
		$profile_field_data = $this->get_param( 'profile_field_data' );
		
		if ( !$user_id || !$profile_field_id ) {
			$this->set_response( 400 );
		} else {
			// Note that $profile_field_data can be empty, resulting in a delete action
			$retval = xprofile_set_field_data( $profile_field_id, $user_id, $profile_field_data );

			if ( $retval ) {
				$this->set_response( 200 );
			} else {
				$this->set_response( 500 );
			}
		}		
	}
}

?>