<?php

/**
 * Include WP-CLI commands to alter users and profiles relating to Buddypress
 */
if ( defined( 'WP_CLI' ) && WP_CLI && !class_exists("BPCLI_Component")){
	//not 100% on how, but seems that the the require_one is not resepected.  I think this
	//may be related to the fact that the CLI and php are being used as two different threads
	//not 100% but it happens when doing `wp` on the command line.
	require_once dirname( __FILE__ ) . '/commands/wp-cli-bp.php';
}

/**
 * Set up json api extensions related to Murrow Connect
 */
require_once dirname( __FILE__ ) . '/api/routes.php';

/**
 * Set admin only readable BP profile fields
 */
require_once dirname( __FILE__ ) . '/includes/bp_admin_only_fields.php';



/**
 * Set up the theme and functions related to Murrow Connect
 */
class WSU_MurrowConnect_Theme {
	/**
	 * Setup the hooks used in the theme.
	 */
	public function __construct() {
		add_action( 'xprofile_updated_profile', array( $this, 'clear_profile_cache' ), 1, 1 );
	}
	
	/**
	 * Clear the profile object cache
	 */
	public function clear_profile_cache( $user_id ) {
		xprofile_set_field_data( 'cached_profile_iw_object', $user_id, '' );
		return;
	}

	
	
	
}
new WSU_MurrowConnect_Theme();