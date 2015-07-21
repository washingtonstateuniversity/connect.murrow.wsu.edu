<?php

/**
 * Manage xprofile data.
 *
 * @since 1.2.0
 */
class BPCLI_XProfile extends BPCLI_Component {
	/**
	 * Create an xprofile group.
	 *
	 * ## OPTIONS
	 *
	 * --name=<name>
	 * : The name for this field group.
	 *
	 * [--description=<description>]
	 * : The description for this field group.
	 *
	 * [--can-delete=<can-delete>]
	 * : Whether the group can be deleted. Default: true.
	 *
	 * @since 1.2.0
	 */
	public function create_group( $args, $assoc_args ) {
		$r = wp_parse_args( $assoc_args, array(
			'name'        => '',
			'description' => '',
			'can_delete'  => true,
		) );

		$group = xprofile_insert_field_group( $r );

		if ( ! $group ) {
			WP_CLI::error( 'Could not create field group.' );
		} else {
			$group = new BP_XProfile_Group( $group );
			$success = sprintf(
				'Created XProfile field group "%s" (id %d)',
				$group->name,
				$group->id
			);
			WP_CLI::success( $success );
		}
	}

	/**
	 * Create an xprofile field.
	 *
	 * ## OPTIONS
	 *
	 * --field_group_id=<field-group-id>
	 * : ID of the associated field group.
	 *
	 * --name=<name>
	 * : Name of the new field.
	 *
	 * [--description=<description>]
	 * : Description of the new field.
	 *
	 * [--parent_id=<parent-id>]
	 * : ID of the parent field. For use when defining options for radio
	 * buttons, etc.
	 *
	 * [--type=<type>]
	 * : Field type. 'textbox', 'textarea', 'radio', 'checkbox',
	 * 'selectbox', 'multiselectbox', 'datebox'. Default: 'textbox'.
	 *
	 * [--can_delete=<can-delete>]
	 * : Whether the field can be deleted. Default: true.
	 *
	 * [--field_order=<field-order>]
	 * : The position of the field in the field order.
	 *
	 * [--order_by=<order-by>]
	 * : Order for the field.
	 *
	 * [--is_default_option=<is-default-option>]
	 * : For suboptions of radio buttons, etc. Whether the field is the
	 * default option. Default: false.
	 *
	 * [--option_order=<option-order>]
	 * : Order for the options.
	 *
	 * @since 1.2.0
	 */
	public function create_field( $args, $assoc_args ) {
		// Rest of arguments are passed through
		$r = wp_parse_args( $assoc_args, array(
			'type'  => 'textbox',
			'field_group_id' => 1
		) );

		$field_id = xprofile_insert_field( $r );

		if ( ! $field_id ) {
			WP_CLI::error( 'Could not create field.' );
		} else {
			$field = new BP_XProfile_Field( $field_id );
			$success = sprintf(
				'Created XProfile field "%s" (id %d)',
				$field->name,
				$field->id
			);
			WP_CLI::success( $success );
		}
	}

	/**
	 * Set profile data for a user.
	 *
	 * ## OPTIONS
	 *
	 * --user_id=<user>
	 * : Identifier for the user. Accepts either a user_login or a numeric ID.
	 *
	 * --field_id=<field-id>
	 * : Field ID. Accepts either the name of the field or a numeric ID.
	 *
	 * --value=<value>
	 * : Value to set.
	 *
	 * [--is_required=<is-required>]
	 * : Whether a non-empty value is required. Default: false
	 *
	 * @since 1.2.0
	 */
	public function set_data( $args, $assoc_args ) {
		$r = wp_parse_args( $assoc_args, array(
			'user_id'     => '',
			'field_id'    => '',
			'value'       => '',
			'is_required' => false,
			'auto_create' => false,
		) );

		$user_id = $this->get_user_id_from_identifier( $r['user_id'] );

		if ( ! $user_id ) {
			WP_CLI::error( 'No user found by that username or id' );
			return;
		}

		// Validate field
		// We need this info anyway for the success message
		if ( ! is_numeric( $r['field_id'] ) ) {
			$field_id = xprofile_get_field_id_from_name( $r['field_id'] );
		} else {
			$field_id = intval( $r['field_id'] );
		}

		$field = new BP_XProfile_Field( $field_id );

		if ( empty( $field->name ) ) {
			if( $r['auto_create'] === false ){
				WP_CLI::error( 'No field found by that name' );
				return;
			}else{
				$this->create_field( $args, array(
					'name'  => $r['field_id'],
				) );
				if ( ! is_numeric( $r['field_id'] ) ) {
					$field_id = xprofile_get_field_id_from_name( $r['field_id'] );
				} else {
					$field_id = intval( $r['field_id'] );
				}

			}
		}

		$updated = xprofile_set_field_data( $field->id, $user_id, $r['value'], $r['is_required'] );

		if ( ! $updated ) {
			WP_CLI::error( 'Could not set profile data.' );
		} else {
			$user = new WP_User( $user_id );
			$success = sprintf(
				'Updated field "%s" (id %d) with value "%s" for user %s (id %d)',
				$field->name,
				$field->id,
				$r['value'],
				$user->user_nicename,
				$user->ID
			);

			WP_CLI::success( $success );
		}
	}
    
	/**
	 * Import users from a CSV file.
	 *
	 * ## OPTIONS
	 *
	 * <file>
	 * : The local or remote CSV file of users to import.
	 *
	 * [--send-email]
	 * : Send an email to new users with their account details.
	 *
	 * [--skip-update]
	 * : Don't update users that already exist.
	 *
	 * ## EXAMPLES
	 *
	 *     wp user import-csv /path/to/users.csv
	 *     wp user import-csv http://example.com/users.csv
	 *
	 *     Sample users.csv file:
	 *
	 *     user_login,user_email,display_name,role
	 *     bobjones,bobjones@example.com,Bob Jones,contributor
	 *     newuser1,newuser1@example.com,New User,author
	 *     existinguser,existinguser@example.com,Existing User,administrator
	 *
	 * @subcommand import-csv
	 */
	public function import_csv( $args, $assoc_args ) {

		$blog_users = get_users();

		$filename = $args[0];

		if ( 0 === stripos( $filename, 'http://' ) || 0 === stripos( $filename, 'https://' ) ) {
			$response = wp_remote_head( $filename );
			$response_code = (string)wp_remote_retrieve_response_code( $response );
			if ( in_array( $response_code[0], array( 4, 5 ) ) ) {
				WP_CLI::error( "Couldn't access remote CSV file (HTTP {$response_code} response)." );
			}
		} else if ( ! file_exists( $filename ) ) {
			WP_CLI::error( sprintf( "Missing file: %s", $filename ) );
		}

		foreach ( new \WP_CLI\Iterators\CSV( $filename ) as $i => $new_user_data ) {
			$defaults =  array();
			$new_user_data = array_merge( $defaults, $new_user_data );


			// User already exists and we just need to add them to the site if they aren't already there
			$existing_user = get_user_by( 'login', $new_user_data['user_login'] );
			if ( !$existing_user ) {
				$existing_user = get_user_by( 'email', $new_user_data['user_email'] );

			}

			if ( $existing_user && \WP_CLI\Utils\get_flag_value( $assoc_args, 'skip-update' ) ) {

				WP_CLI::log( "{$existing_user->user_login} exists and has been skipped" );
				continue;

			} else if ( $existing_user ) {

				$user_id = $existing_user->ID;
				foreach($new_user_data as $key=>$value){
					if( !in_array($key, array("user_id","user_login","user_email","display_name","role")) ){
						WP_CLI::log( "setting data for {$user_id} on {$key} of {$value}" );
						$this->set_data( $args, array(
							'user_id'     => $user_id,
							'field_id'    => $key,
							'value'       => $value,
							'is_required' => false,
							'auto_create' => true,
						) );
					}
				}
			} else {
				WP_CLI::log( "{$existing_user->user_login} didn't exist." );
			}
            
			WP_CLI::success( $new_user_data['user_login'] . " updated" );
		}
	}
    
    
}

WP_CLI::add_command( 'bp xprofile', 'BPCLI_XProfile', array(
	'before_invoke' => function() {
		if ( ! bp_is_active( 'xprofile' ) ) {
			WP_CLI::error( 'The XProfile component is not active.' );
		}
} ) );

