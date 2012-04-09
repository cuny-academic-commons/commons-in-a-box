<?php

class BP_API_Server_Actions {
	
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
	
		if ( !$user_id ) {
			throw new RestException( 404, 'User not found' );
		}
		
		if ( !$profile_field_id ) {
			throw new RestException( 404, 'Profile field not found' );
		}
	
		// Note that $profile_field_data can be empty, resulting in a delete action
		$retval = xprofile_set_field_data( $profile_field_id, $user_id, $profile_field_data );

		if ( $retval ) {
			return $retval;
		} else {
			throw new RestException( 500 );
		}		
	}
}

?>