<?php

/**
 * Include WP-CLI commands to alter users adn profiles relating to Buddypress
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once dirname( __FILE__ ) . '/commands/user.php';
	require_once dirname( __FILE__ ) . '/commands/bp.php';
}


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



/**
 * Set up json api extensions related to Murrow Connect
 */
function murrow_api_init() {
	global $Murrow_api_bp_user;
	$murrow_api_bp_user = new Murrow_API_BP_USER();
	add_filter( 'json_endpoints', array( $murrow_api_bp_user, 'register_routes' ) );
}
add_action( 'wp_json_server_before_serve', 'murrow_api_init' );

class Murrow_API_BP_USER {
	/**
	* Register Routes
	*/
	public function register_routes( $routes ) {
		$routes['/bp/users'] = array(
			array( array( $this, 'get_users'), WP_JSON_Server::READABLE )
		);
		$routes['/bp/user/(?P<id>\d+)'] = array(
			array( array( $this, 'get_user'), WP_JSON_Server::READABLE )
		);
		// Add more custom routes here
		return $routes;
	}
	
	public function get_users(){
		
		$args = array(
			//params?
		);
		
		// The Query
		$user_query = new WP_User_Query( $args );
		
		// User Loop
		if ( ! empty( $user_query->results ) ) {
			foreach ( $user_query->results as $user ) {
				//$some_field = xprofile_get_field_data( 'Some Field', $user->id );
				var_dump( $user );
			}
		} else {
			echo 'No users found.';
		}/**/

		
		
		var_dump( 'end of user list' );die();
	}
	
	public function get_user(){
		var_dump('a user');die();
	}
}




