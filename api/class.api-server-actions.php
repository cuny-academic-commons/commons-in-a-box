<?php

class BP_API_Server_Actions {
	
	
	/** PARAMETER HANDLERS ******************************************************/

	/**
	 * Parse the $args passed to the function. Validate.
	 *
	 * @param array $args See the documentation for this class
	 */
	protected function _setup_params( $args = array() ) {	
		$this->action = $args['action'];
		unset( $args['action'] );
		$this->params = $args;
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
	function _get_param( $key = false ) {
		$value = '';
		
		if ( !$key ) {
			return $value;
		}
		
		switch( $key ) {
			case 'user_id' :
				$value = $this->_clean_param( $key, 'int' );
				
				// Check that the user exists. If not, $value is set to 0
				$user = new WP_User( $value );
				$value = $user->ID;
				
				break;
			
			case 'profile_field_id' :			
				if ( !bp_is_active( 'xprofile' ) ) {
					return 0;
				}
				
				$field = $this->_clean_param( $key );
				
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
				
				$value = $this->_clean_param( $key, 'string' );
				
				break;
			
			default :
				// @todo I think this is where a hook will go?
				break;
			
		}
		
		return $value;
	}
	
	/**
	 * Ensures that a requested parameter has indeed been passed, and ensures that it's cast
	 * as the proper data type.
	 *
	 * @param string $key The name of the parameter (eg, the $_GET index)
	 * @param string $type The desired data type (int, string, array, object)
	 */
	function _clean_param( $key = '', $type = false ) {
		$value = '';
		
		// Be sure it exists
		if ( isset( $this->restler->request_data[$key] ) ) {
			$value = $this->restler->request_data[$key];
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
	
	/** BUDDYPRESS ACTIONS ******************************************************/
	
	/**
	 * Updates an xprofile field
	 *
	 * @uses xprofile_set_field_data()
	 * @url POST/v1/user
	 */
	protected function update_profile_field() {
		// Set up params
		$user_id            = $this->_get_param( 'user_id' );
		$profile_field_id   = $this->_get_param( 'profile_field_id' );
		$profile_field_data = $this->_get_param( 'profile_field_data' );
		
		if ( !$user_id || !$profile_field_id ) {
			throw new RestException( 404 );
		} else {
			// Note that $profile_field_data can be empty, resulting in a delete action
			$retval = xprofile_set_field_data( $profile_field_id, $user_id, $profile_field_data );

			if ( $retval ) {
				return $retval;
			} else {
				throw new RestException( 500 );
			}
		}		
	}
}

?>