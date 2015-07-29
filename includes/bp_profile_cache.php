<?php
/**
* Add states as a field type
*/

function bp_add_profile_cache() {

	if ( !xprofile_get_field_id_from_name('cached_profile_iw_object') && 'bp-profile-setup' == $_GET['page'] ) {

		$cache_args = array(
			'field_group_id'     => 1,
			'name'               => 'cached_profile_iw_object',
			'description'        => 'Clearing will reset cache for user',
			'can_delete'         => false,
			'field_order'        => 2,
			'is_required'        => false,
			'type'               => 'textarea',
			'order_by'           => 'custom',
			'default_visibility' => 'hidden'
		);

		$cache_id = xprofile_insert_field( $cache_args );
	}
}
add_action('bp_init', 'bp_add_profile_cache');
