<?php

/**
 * Rich Text xprofile field type.
 *
 * @since BuddyPress (2.0.0)
 */
class BP_XProfile_Field_Type_State extends BP_XProfile_Field_Type {



	/**
	 * Constructor for the Rich Text field type
 	 */
	public function __construct() {

		parent::__construct();

		$this->category = _x( 'Multi Fields', 'xprofile field type category', 'buddypress-xprofile-state-field' );
		$this->name     = _x( 'State picker', 'xprofile field type', 'buddypress-xprofile-state-field' );
		$this->type     = 'state';

		// allow all values to pass validation
		$this->set_format( '/.*/', 'replace' );

		do_action( 'bp_xprofile_field_type_state', $this );

	}


	/**
	 * Output the edit field options HTML for this field type.
	 *
	 * BuddyPress considers a field's "options" to be, for example, the items in a selectbox.
	 * These are stored separately in the database, and their templating is handled separately.
	 *
	 * This templating is separate from {@link BP_XProfile_Field_Type::edit_field_html()} because
	 * it's also used in the wp-admin screens when creating new fields, and for backwards compatibility.
	 *
	 * Must be used inside the {@link bp_profile_fields()} template loop.
	 *
	 * @param array $args Optional. The arguments passed to {@link bp_the_profile_field_options()}.
	 * @since BuddyPress (2.0.0)
	 */
	public function edit_field_options_html( array $args = array() ) {
		$original_option_values = maybe_unserialize( BP_XProfile_ProfileData::get_value_byid( $this->field_obj->id, $args['user_id'] ) );

		$options = $this->get_state_list();
		$html    = '<option value="">' . /* translators: no option picked in select box */ esc_html__( '----', 'buddypress' ) . '</option>';

		if ( empty( $original_option_values ) && !empty( $_POST['field_' . $this->field_obj->id] ) ) {
			$original_option_values = sanitize_text_field(  $_POST['field_' . $this->field_obj->id] );
		}

		$option_values = ( $original_option_values ) ? (array) $original_option_values : array();
		for ( $k = 0, $count = count( $options ); $k < $count; ++$k ) {
			$selected = '';

			// Check for updated posted values, but errors preventing them from
			// being saved first time
			foreach( $option_values as $i => $option_value ) {
				if ( isset( $_POST['field_' . $this->field_obj->id] ) && $_POST['field_' . $this->field_obj->id] != $option_value ) {
					if ( ! empty( $_POST['field_' . $this->field_obj->id] ) ) {
						$option_values[$i] = sanitize_text_field( $_POST['field_' . $this->field_obj->id] );
					}
				}
			}

			// Run the allowed option name through the before_save filter, so
			// we'll be sure to get a match
			$allowed_options = xprofile_sanitize_data_value_before_save( $options[$k], false, false );

			// First, check to see whether the user-entered value matches
			if ( in_array( $allowed_options, $option_values ) ) {
				$selected = ' selected="selected"';
			}

			// Then, if the user has not provided a value, check for defaults
			if ( ! is_array( $original_option_values ) && empty( $option_values ) && $options[$k] ==  "WA" ) {
				$selected = ' selected="selected"';
			}

			/**
			 * Filters the HTML output for options in a select input.
			 *
			 * @since BuddyPress (1.1.0)
			 *
			 * @param string $value    Option tag for current value being rendered.
			 * @param object $value    Current option being rendered for.
			 * @param int    $id       ID of the field object being rendered.
			 * @param string $selected Current selected value.
			 * @param string $k        Current index in the foreach loop.
			 */
			$html .= apply_filters( 'bp_get_the_profile_field_options_select', '<option' . $selected . ' value="' . esc_attr( stripslashes( $options[$k] ) ) . '">' . esc_html( stripslashes( $options[$k] ) ) . '</option>', $options[$k], $this->field_obj->id, $selected, $k );
		}

		echo $html;
	}
	/**
	 * Output the edit field HTML for this field type.
	 *
	 * Must be used inside the {@link bp_profile_fields()} template loop.
	 *
	 * @param array $raw_properties Optional key/value array
	 */
	public function edit_field_html( array $raw_properties = array() ) {
// user_id is a special optional parameter that we pass to
		// {@link bp_the_profile_field_options()}.
		if ( isset( $raw_properties['user_id'] ) ) {
			$user_id = (int) $raw_properties['user_id'];
			unset( $raw_properties['user_id'] );
		} else {
			$user_id = bp_displayed_user_id();
		} ?>

		<label for="<?php bp_the_profile_field_input_name(); ?>">
			<?php bp_the_profile_field_name(); ?>
			<?php if ( bp_get_the_profile_field_is_required() ) : ?>
				<?php esc_html_e( '(required)', 'buddypress' ); ?>
			<?php endif; ?>
		</label>

		<?php

		/** This action is documented in bp-xprofile/bp-xprofile-classes */
		do_action( bp_get_the_profile_field_errors_action() ); ?>

		<select <?php echo $this->get_edit_field_html_elements( $raw_properties ); ?>>
			<?php bp_the_profile_field_options( array( 'user_id' => $user_id ) ); ?>
		</select>


		<?php
		

	}



	/**
	 * Output HTML for this field type on the wp-admin Profile Fields screen
	 *
	 * Must be used inside the {@link bp_profile_fields()} template loop.
	 *
	 * @param array $raw_properties Optional key/value array of permitted attributes that you want to add.
	 */
	public function admin_field_html( array $raw_properties = array() ) {
		?>
		<select <?php echo $this->get_edit_field_html_elements( $raw_properties ); ?>>
			<?php 
			$states = $this->get_state_list();
			$i=0;
			foreach (  $states as $state ) {
				if($state == "WA"){
					$selected = ' selected="selected"';
				}else{
					$selected = '';
				}
				echo '<option'.$selected.' value="' . $state . '">' . $state . '</option>';
			}
			?>
		</select>
		<?php
	}



	/**
	 * This method usually outputs HTML for this field type's children options on the wp-admin Profile Fields
	 * "Add Field" and "Edit Field" screens, but for this field type, we don't want it, so it's stubbed out.
	 *
	 * @param BP_XProfile_Field $current_field The current profile field on the add/edit screen.
	 * @param string $control_type Optional. HTML input type used to render the current field's child options.
	 */
	public function admin_new_field_html( BP_XProfile_Field $current_field, $control_type = '' ) {}

	/**
	 *
	 */
	public function get_state_list(){
		$states = array('AL', 'AK', 'AS', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'DC', 'FM', 'FL', 'GA', 'GU', 'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MH', 'MD', 'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ', 'NM', 'NY', 'NC', 'ND', 'MP', 'OH', 'OK', 'OR', 'PW', 'PA', 'PR', 'RI', 'SC', 'SD', 'TN', 'TX', 'UT', 'VT', 'VI', 'VA', 'WA', 'WV', 'WI', 'WY', 'AE', 'AA', 'AP');
		return $states;
		
	}

} // class ends



