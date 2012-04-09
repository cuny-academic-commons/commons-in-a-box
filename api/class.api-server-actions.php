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

	
	/** BUDDYPRESS ACTIONS ******************************************************/
	
	/**
	 * Updates an xprofile field
	 *
	 * @uses xprofile_set_field_data()
	 * @url POST/v1/user
	 * @param int $user_id
	 * @param int $profile_field_id
	 * @param string $profile_field_data
	 */
	protected function update_profile_field( $user_id = 0, $profile_field_id = 0, $profile_field_data = '' ) {
		// Set up params
	//	$user_id            = $this->_get_param( 'user_id' );
	//	$profile_field_id   = $this->_get_param( 'profile_field_id' );
	//	$profile_field_data = $this->_get_param( 'profile_field_data' );
		
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