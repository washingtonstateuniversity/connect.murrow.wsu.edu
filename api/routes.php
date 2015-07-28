<?php
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
	
	/**
	* Outputs a json feed of users adn xprofile data
	*/
	public function get_users(){
		$args = array( 
			'role' => isset( $_GET['role'] ) ? $_GET['role'] : 'Subscriber'
		);
		
		// The Query
		$user_query = new WP_User_Query( $args );
		
		// User Loop
		$found_users = array();
		$users = array();
		if ( ! empty( $user_query->results ) ) {
			if( isset( $_GET['fields'] ) ){
				//this is not complete, and needs to bypass the cache
				$fields = array_merge( array( 'cached_profile_iw_object' ), explode( ',', $_GET['fields'] ) );
			}else{
				$fields = array( 'First name', 'Last name', 'City', 'State', 'Country', 'Bio', 'cached_profile_iw_object' );
			}
			foreach ( $user_query->results as $user ) {
				$tmp_data = array();
				$tmp_data['ID'] = $user->id;
				foreach($fields as $field_name){
					$tmp_data[$field_name] = xprofile_get_field_data( $field_name, $user->id );
				}
				$found_users[] = $tmp_data;
				//var_dump( $user );
			}
			$default_img = bp_core_avatar_default( 'local' ) ;
			foreach( $found_users as $user ){
				if( empty( $user['cached_profile_iw_object'] ) ){
					$geocode=file_get_contents("http://maps.google.com/maps/api/geocode/json?address=".$user['City'].','.$user['State']."&sensor=false");
					$output= json_decode($geocode);
					//var_dump($output);die();
					$lat = $output->results[0]->geometry->location->lat;
					$lng = $output->results[0]->geometry->location->lng;
					$first_name = substr($user['First name'], 0, 1);

					$profile_pic = bp_core_fetch_avatar( array( 'item_id' => $user['ID'], 'no_grav' => true, 'html' => false ) );
					if($profile_pic!=$default_img){
						$profile_pic='/wp-content/themes/connect.murrow.wsu.edu/images/default-profile.jpg';
					}

					$tmp_data = (object) array(
						'name' => $user['Last name'] . ($first_name!==false?', '.$first_name:''),
						'profile_img' => $profile_pic,
						'city' => $user['City'],
						'state' => $user['State'],
						'country' => $user['Country'],
						'location' => array( 'lat' => "{$this->randomizeCord($lat)}",
											'lon' => "{$this->randomizeCord($lng)}"
										   ),
						'bio' => !empty($user['Bio'])?$user['Bio']:'<p></p>'
					);

					$field_id = xprofile_get_field_id_from_name( 'cached_profile_iw_object' );
					$field = new BP_XProfile_Field( $field_id );
					$updated = xprofile_set_field_data( $field->id, $user['ID'], json_encode($tmp_data), false );

					$users[] = $tmp_data;
				}else{
					$users[] = json_decode($user['cached_profile_iw_object']);
				}
			}
		} else {
			$users = (object) array(
				'message' => 'No users found.'
			);
		}/**/
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
	/**
	 *
	 */
	private function randomizeCord( $cord, $level=1 , $spread = 10){
		//basic need is to take a cord and create a randomized spread around the real one with in meters normally
		//"46.7" . $this->randomIntFromInterval(22,41) . $this->randomIntFromInterval(1111,9999)
		$parts = explode('.',$cord);
		$accurate_value = substr($parts[1], 0, $level);
		$digit_count = strlen($spread);
		$spread_start = substr($parts[1], $level, $digit_count);
		$number = rand();
		if ($number % 2 == 0) {
			$spread_end = $spread_start + $spread;
		}else{
			$spread_end = $spread_start - $spread;
		}
		
		// if the $spread_end length is greater then the $spread length then normalize to a 9 at the count of the $spread
		if(strlen($spread_end)>$digit_count){
			$spread_end = str_repeat('9',$digit_count);
			
		//if the $spread_end has fewer digits, make sure we normallize it to match the $spread length
		}elseif(strlen($spread_end)<$digit_count){
			$spread_end = $spread_end . str_repeat('0',$digit_count-strlen($spread_end));
			
		//if the $spread_end goes neg then just 0 it out
		}elseif($spread_end<=0){
			$spread_end = str_repeat('0',$digit_count);
		}
		//if the $spread_end is less then the $spread_start switch the values 
		if($spread_end<$spread_start){
			$tmp = $spread_start;
			$spread_start = $spread_end;
			$spread_end = $tmp;
		}
		
		$fullspread = $accurate_value . $this->randomIntFromInterval($spread_start, $spread_end);
		
		$final_value = $parts[0] .'.'. $fullspread . $this->randomIntFromInterval(1111,9999);
		return $final_value;
	}
}


