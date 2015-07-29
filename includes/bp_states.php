<?php
/**
 * Add states as a field type
 */
class BP_XProfile_State_Field {
	/**
	 * Initialises this object
	 *
	 * @return object
	 */
	function __construct() {

		// include class
		require_once dirname( __FILE__ ) . '/bp_states_class.php';
		
		// register with BP the 2.0 way...
		add_filter( 'bp_xprofile_get_field_types', array( $this, 'add_field_type' ) );

		// we need to parse the edit value in BP 2.0
		add_filter( 'bp_get_the_profile_field_edit_value', array( $this, 'get_field_value' ), 30, 3 );

		// show our field type in read mode after all BuddyPress filters
		add_filter( 'bp_get_the_profile_field_value', array( $this, 'get_field_value' ), 30, 3 );

		// filter for those who use xprofile_get_field_data instead of get_field_value
		add_filter( 'xprofile_get_field_data', array( $this, 'get_field_data' ), 15, 3 );

		return $this;
	}
	
	/**
	 * Add details of our xProfile field type (BuddyPress 2.0)
	 *
	 * @param array Key/value pairs (field type => class name).
	 * @return array Key/value pairs (field type => class name).
	 */
	function add_field_type( $fields ) {
		// make sure we get an array
		if ( is_array( $fields ) ) {
			// add our field to the array
			$fields['state'] = 'BP_XProfile_Field_Type_State';
		} else {
			// create array with our item
			$fields = array( 'state' => 'BP_XProfile_Field_Type_State' );
		}
		return $fields;
	}
	/**
	 * Show our field type in read mode
	 *
	 * @param string $value
	 * @param string $type
	 * @param integer $id
	 * @return string
	 */
	function get_field_value( $value = '', $type = '', $id = '' ) {
		// is it our field type?
		if ( $type == 'state' ) {
			// we want the raw data, unfiltered
			global $field;
			$value = $field->data->value;
			// apply content filter
			$value = apply_filters( 'bp_xprofile_field_type_state_content', stripslashes( $value ) );
			// return filtered value
			return apply_filters( 'bp_xprofile_field_type_state_value', $value );
		}
		// fallback
		return $value;
	}
	/**
	 * Filter for those who use xprofile_get_field_data instead of get_field_value
	 *
	 * @param string $value
	 * @param integer $field_id
	 * @param integer $user_id
	 * @return string
	 */
	function get_field_data( $value = '', $field_id = '', $user_id = '' ) {
		// check we get a field ID
		if ( $field_id === '' ) { return $value; }
		// get field object
		$field = new BP_XProfile_Field( $field_id );
		// is it ours?
		if ( $field->type == 'state' ) {
			// apply content filter
			$value = apply_filters( 'bp_xprofile_field_type_state_content', stripslashes( $value ) );
			// return filtered value
			return apply_filters( 'bp_xprofile_field_type_state_value', $value );
		}
		// fallback
		return $value;

	}
}


/**
 * Initialise our plugin after BuddyPress initialises
 *
 * @return void
 */
function bp_xprofile_state_field() {
	// make global in scope
	global $bp_xprofile_state_field;
	// init plugin
	$bp_xprofile_state_field = new BP_XProfile_State_Field();
}

// add action for plugin loaded
add_action( 'bp_init', 'bp_xprofile_state_field' );