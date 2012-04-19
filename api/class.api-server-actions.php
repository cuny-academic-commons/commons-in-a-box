<?php

class BP_API_Server_Actions {

	/** USER ******************************************************/

	/**
	 * Update an xprofile field
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


	/** GROUP ******************************************************/

	/**
	 * Update a group's name
	 *
	 * @url POST/v1/group
	 * @param int $group_id
	 * @param string $name
	 */
	protected function update_group_name( $group_id = 0, $name = 0 ) {

		if ( !$group_id ) {
			throw new RestException( 404, 'Group not found' );
		}

		$group = groups_get_group( array( 'group_id' => $group_id ) );
		$group->name = $name;

		if ( $group->save() ) {
			return true;
		} else {
			throw new RestException( 500 );
		}
	}

	/**
	 * Create a group
	 *
	 * For security, you are not allowed to create a group with the same name as an existing
	 * group.
	 *
	 * @url POST/v1/group
	 * @param string $name
	 * @param string $description
	 * @param slug $slug
	 * @param int $creator_id
	 * @param int $enable_forum 0 for false, 1 for true
	 * @param string $status 'public', 'private', 'hidden'
	 * @param string $invite_status 'members', 'mods', 'admins'
	 */
	protected function create_group( $name = '', $description = '', $slug = '', $creator_id = 0, $enable_forum = 1, $status = 'public', $invite_status = 'members' ) {
		global $wpdb, $bp;

		if ( !$name ) {
			throw new RestException( 400, 'You must provide a group name' );
		}

		if ( !$description ) {
			throw new RestException( 400, 'You must provide a group description' );
		}

		if ( !$creator_id ) {
			throw new RestException( 400, 'You must provide a creator id for a new group' );
		}

		if ( $slug ) {
			$slug = groups_check_slug( sanitize_title( esc_attr( $slug ) ) );
		} else {
			$slug = groups_check_slug( sanitize_title( esc_attr( $name ) ) );
		}

		// Make sure a group doesn't already exist by this name
		if ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$bp->groups->table_name} WHERE name = %s LIMIT 1", $name ) ) ) {
			throw new RestException( 409, 'A group exists by this name' );
		}

		$group_id = groups_create_group( array(
			'name'         => $name,
			'description'  => $description,
			'creator_id'   => $creator_id,
			'slug'         => $slug,
			'status'       => $status,
			'enable_forum' => $enable_forum,
			'date_created' => bp_core_current_time()
		) );

		if ( !$group_id ) {
			throw new RestException( 500 );
		} else {
			groups_update_groupmeta( $group_id, 'total_member_count', 1 );
			groups_update_groupmeta( $group_id, 'last_activity', bp_core_current_time() );

			$allowed_invite_status = apply_filters( 'groups_allowed_invite_status', array( 'members', 'mods', 'admins' ) );
			$invite_status = in_array( $invite_status, (array) $allowed_invite_status ) ? $invite_status : 'members';

			groups_update_groupmeta( $group_id, 'invite_status', $invite_status );

			$group_obj = groups_get_group( array( 'group_id' => $group_id ) );

			$retval = array(
				'uri' => bp_get_group_permalink( $group_obj ),
			);

			// @todo Should probably return a more helpful format
			foreach( (array) $group_obj as $key => $value ) {
				$retval[$key] = $value;
			}

			return $retval;
		}
	}
}

?>