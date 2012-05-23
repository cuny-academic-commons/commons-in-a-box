<?php

class BP_API_Server_Actions {

	/** USER ******************************************************/

	/**
	 * Create a user
	 *
	 * We use bp_core_signup_user(), so that BP will do all xprofile data processing, and so
	 * that all of the BP-specific actions will be fired. However, bp_core_signup_user() (and
	 * wp_insert_user() itself) do not provide very robust error reporting, so we do a bit of
	 * manual validation first.
	 *
	 * @url POST/v1/user
	 * @param string $login
	 * @param string $email_address
	 * @param string $display_name
	 * @param string $password
	 * @param int $activate_user 0 if you want an activation email sent; 1 if you want the user
	 *    auto-activated
	 */
	protected function create_user( $login = '', $email_address = '', $display_name = '', $password = '', $activate_user = 1 ) {
		global $wpdb;

		// @todo if current_user_can( create_users )

		if ( empty( $display_name ) ) {
			throw new RestException( 400, 'You must provide a display name' );
		}

		if ( username_exists( $login ) ) {
			throw new RestException( 409, 'A user with this login name already exists' );
		}

		if ( email_exists( $email_address ) ) {
			throw new RestException( 409, 'A user with this email address already exists' );
		}

		// Passwords can't be empty
		if ( empty( $password ) ) {
			$password = wp_generate_password( 12, true );
		}

		// bp_core_signup_user() expects a particular format
		// @todo Allow more profile fields to be set at creation?
		$userdata = array(
			'profile_field_ids' => '1',
			'field_1' => $display_name
		);

		if ( is_multisite() ) {
			$userdata['password'] = wp_hash_password( $password );
		}

		// Prevent activation emails from being sent, if necessary
		if ( $activate_user ) {
			if ( is_multisite() ) {
				add_filter( 'wpmu_signup_user_notification', '__return_false' );
			} else {
				add_filter( 'bp_core_signup_send_activation_key', '__return_false' );
			}
		}

		$user_id = bp_core_signup_user( $login, $password, $email_address, $userdata );

		// If there's a failure at this point, return a generic error
		if ( is_wp_error( $user_id ) ) {
			throw new RestException( 500 );
		}

		// On Multisite, unactivated users do not really exist yet. Thus, if activate_user
		// is true, we will have to kickstart the activation process in order to get a valid
		// user_id and WP_User object. If activate_user is false, we'll just return an empty
		// object, with a nice note explaining what's happened.
		if ( is_multisite() ) {
			if ( $activate_user ) {
				$key = $wpdb->get_var( $wpdb->prepare( "SELECT activation_key FROM $wpdb->signups WHERE user_login = %s ORDER BY registered DESC LIMIT 1", $login ) );

				if ( empty( $key ) ) {
					throw new RestException( 500 );
				}

				$user_id = bp_core_activate_signup( $key );
			} else {
				return array(
					'user_id' => 0,
					'user_login' => $login,
					'user_email' => $email_address,
					'display_name' => $display_name,
					'uri' => '',
					'note' => __( 'Your new user has been created, but a user_id and uri will not be available until the user has clicked the link in the activation email just sent. This is a limitation of WordPress Multisite, and the fact that you have not chosen to auto-activate the newly created user.', 'cbox' )
				);
			}
		} else {
			if ( $activate_user ) {
				// Due to a BP bug, disabling the activation email also results
				// in no key being generated. So we create one manually.
				// This is a profoundly dumb workaround.
				// @see https://buddypress.trac.wordpress.org/ticket/4218
				$key = wp_hash( $user_id );
				update_user_meta( $user_id, 'activation_key', $key );
				$user_id = bp_core_activate_signup( $key );
			}
		}

		if ( !$user_id || is_wp_error( $user_id ) ) {
			throw new RestException( 500 );
		}

		// Create a useful user object to return to the requestee
		$user = new WP_User( $user_id );

		$retval = array(
			'user_id'      => $user->ID,
			'user_login'   => $user->user_login,
			'user_email'   => $user->user_email,
			'display_name' => bp_core_get_user_displayname( $user->ID ),
			'uri'          => bp_core_get_user_domain( $user->ID )
		);

		return $retval;
	}

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
	 * Update a group
	 *
	 * For $args, pass an array that is keyed by the aspect of the group you want to update. Eg:
	 *     $args = array(
	 *         'name' => 'Foo',
	 *         'description' => 'This group is totally foo'
	 *     );
	 * If you don't want to change a given value, just don't include it in your array, and it'll
	 * remain untouched.
	 *
	 * @url POST/v1/group
	 * @param int $group_id
	 * @param array $args
	 */
	protected function update_group( $group_id = 0, $args = array() ) {

		if ( !$group_id ) {
			throw new RestException( 404, 'Group not found' );
		}

		$group = groups_get_group( array( 'group_id' => $group_id ) );

		foreach( (array) $args as $key => $value ) {
			if ( isset( $group->{$key} ) && !empty( $value ) ) {
				$group->{$key} = $value;
			}
		}

		if ( $group->save() ) {
			$retval = array(
				'uri' => bp_get_group_permalink( $group_obj ),
			);
			foreach( $group as $k => $v ) {
				$retval[$k] = $v;
			}
			return $retval;
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
			throw new RestException( 409, 'A group by this name already exists.' );
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