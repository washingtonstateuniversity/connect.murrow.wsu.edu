<?php

/**
 * Include WP-CLI commands to alter users adn profiles relating to Buddypress
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	if(!class_exists("User_Command")){
		//require_once dirname( __FILE__ ) . '/commands/user.php';
	}
	if(!class_exists("BPCLI_Component")){
		require_once dirname( __FILE__ ) . '/commands/wp-cli-bp.php';
	}
}

require_once dirname( __FILE__ ) . '/api/routes.php';
/**
 * Set up the theme and functions related to Murrow Connect
 */
class WSU_MurrowConnect_Theme {
	/**
	 * Setup the hooks used in the theme.
	 */
	public function __construct() {
		
	}
}
new WSU_MurrowConnect_Theme();