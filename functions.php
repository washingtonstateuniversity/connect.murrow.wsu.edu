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
				//var_dump( $user );
			}
		} else {
			//echo 'No users found.';
		}/**/

		$loaded_users = array("Abdulgani, Z","Almeleh, N","Amuro, S","Ankrah, S","Astudillo, L","Ayala-Sanchez, J","Ayers, A","Babcock, J","Baily, B","Baird, H","Baldwin, S","Bay, L","Becker, A","Beer, K","Berg, S","Betancourt Jr, J","Billett, M","Bobbe, E","Brinton, K","Bruck, M","Bryant, B","Bulzomi, L","Burton, E","Bush, K","Butchcoe, K","Campbell, A","Capron, H","Carey, J","Carey, N","Carrigan, J","Carstens, G","Chaffin, C","Cheshier, W","Choate, A","Christensen, K","Clark, J","Cohen, M","Cole, M","Connor, R","Cooper, N","Cramer, M","Crawford, J","Cross-Karras, H","Cruz, K","Cuddie, G","Dahmen, J","Dance, E","Davis, A","Davis, H","Davis, M","Devitt, C","Downie, A","Edwards, K","Ehde, M","Engman, K","Engstrom, C","Evenson, M","Fan, B","Fausset, N","Fernandes, R","Fink, T","Fleuret, M","Foreman, M","Foster, T","Gabriel, M","Gavranich, C","Genger III, T","Ghosn, S","Gilardo, J","Gillies, C","Godlove, H","Goldberg, R","Goode, D","Goodman, J","Grasso, J","Gray, C","Grosse, K","Groves, P","Gust, L","Hadreas, O","Hair, C","Handy III, J","Harris, J","Harris, J","Harrison, K","Hart, M","Haskey, C","Havard, T","Hedin, B","Henry, C","Hensch, K","Hernandez, D","Hiegel, K","Howard, N","Hsu, J","Ilyankoff, K","Isernio, A","Jacobs-Pfluger, K","Jarvis, S","Johnson, K","Johnson, M","Jones, M","Jung, K","Kelly, B","Kennedy, H","Kennedy, S","Kenyon, K","Kostelecky, S","Kraemer, T","Kugler, S","Lane, T","Lange, S","Lawrence, E","Ledesma, J","Lefaber, T","Lemond, K","Levis, O","Lingenfelter, T","Machmiller, S","Macpherson, K","Mak, K","Mata, J","Mayeda, A","McGlynn, M","McGrail, D","McGraw, K","McKiernan, E","McNair, J","Meier, H","Meinberg, C","Mendoza, J","Miller, C","Monteggia, J","Montgomery, K","Mroz, A","Munson, J","Murphy, H","Myers, A","Nelson, C","Nguyen, A","Nishida, K","O'Brien, M","Ojwang, E","Oliver, D","Othman, C","Ouk, S","Pacheco, C","Parker, D","Pearce, H","Phillips, M","Pierce, D","Pietrandrea, C","Porter, G","Pretzer, E","Price, E","Ragsdale, G","Ray, C","Ray, J","Rice, J","Rohr, K","Rucker, J","Rummel, J","Russell, K","Sanabria, D","Santic, M","Schulte, M","Schur, H","Scott, A","Sears, A","Selstead, K","Shannon, S","Shovlowsky, M","Siddons, A","Sidor, A","Simmons, D","Smith, K","Smith, W","Song, Q","Soriano, S","Sposari, D","Stewart, S","Student, A","Suchy, N","Sullivan, C","Swift, T","Taylor, H","Taylor, J","Thompson, F","Tousignant, G","Vargas, V","Velliquette, B","Vincent, S","Viste, E","Vo, C","Wagner, A","Wai, M","Watson, M","Whitehead, T","Williams, A","Willis, A","Winslow, T","Wonio, R","Wyman, M","Yusuff, S","Zeth, A","Zhong, Z","Zimmer, M","Alvarado, S","Anderson, K","Chernesky, K","Dawson, D","Dessi, S","Donnan, B","Feller, R","Flanigan, C","Fredericks, T","Gaynor, H","Gese, C","Hagstrom, L","Hausske, K","Hines, J","Hoefer, B","Hughes, M","Kaholokula, K","Kim, S","Koffley, V","Lo, J","Mann, B","Mischaikov, E","Nash, E","Phillips, C","Phillips, R","Priddy, C","Rabinowitz, D","Riley, M","Rinkenberger, N","Sackman, S","Sawyer, M","Segal, M","Shapiro, J","Tarr, A","Valle, R","Welsh, B");

		$users = array();

		foreach( $loaded_users as $user ){
			$users[] = (object) array(
				'name' => $user,
				'profile_img' => '/wp-content/themes/connect.murrow.wsu.edu/images/default-profile.jpg',
				'city' => 'Pullman',
				'state' => 'WA',
				'country' => 'US',
				'location' => array( 'lat' => "46.7" . $this->randomIntFromInterval(22,41) . $this->randomIntFromInterval(1111,9999),
									'lon' => "-117.1" . $this->randomIntFromInterval(30,71) . $this->randomIntFromInterval(1111,9999)
								   ),
				'bio' => '<p>Curae massa vestibulum erat nisi a etiam ut bibendum posuere suspendisse dignissim id a fringilla porttitor ut ipsum.Ullamcorper ad torquent suspendisse at mi faucibus primis mattis hendrerit id adipiscing fringilla lacinia a interdum.Vulputate at parturient a ante nibh a rutrum curae urna in suspendisse pharetra consequat a adipiscing nunc sem scelerisque aliquet a eget a morbi mi nunc tellus lacinia ornare.Ultricies inceptos posuere et a ipsum auctor condimentum velit orci.</p>'
			);
		}
		
		//var_dump( json_encode( $users ) );
		//var_dump( 'end of user list' );die();
		return $users;
	}
	
	public function get_user(){
		var_dump('a user');die();
	}
	
	private function randomIntFromInterval($min, $max){
		return floor(rand( $min , $max ));
	}
	
}




