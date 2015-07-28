<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BuddyPress Admin Only Profile Fields
 */
class BP_Admin_Only_Profile_Fields {

	/**
	 * Instance of this class.
	 */
	private static $instance = null;

	/**
	 * Initialize the plugin.
	 */
	private function __construct() {

		// Setup plugin constants
		self::setup_constants();

		// Load plugin text domain
		self::load_plugin_textdomain();

		// Actions
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Filters
		add_filter( 'bp_xprofile_get_visibility_levels', array( $this, 'custom_visibility_levels' ) );
		add_filter( 'bp_xprofile_get_hidden_fields_for_user', array( $this, 'hide_hidden_fields' ), 10, 3 );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return BP_Admin_Only_Profile_Fields
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Setup plugin constants.
	 */
	private function setup_constants() {

		if ( ! defined( 'BPAOPF_VERSION' ) ) {
			define( 'BPAOPF_VERSION', '1.1.1' );
		}

		if ( ! defined( 'BPAOPF_PLUGIN_URL' ) ) {
			define( 'BPAOPF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		if ( ! defined( 'BPAOPF_PLUGIN_DIR' ) ) {
			define( 'BPAOPF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}
	}

	/**
	 * Load the plugin text domain.
	 */
	private function load_plugin_textdomain() {

		load_plugin_textdomain( 'bp_admin_only_profile_fields', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Enqueue admin scripts.
	 */
	public function enqueue_scripts() {
		$src     = plugins_url( 'js/admin_only.js', __FILE__ );
		wp_register_script( 'bp_admin_only_profile_fields', $src, array( 'jquery' ), BPAOPF_VERSION, true );

		if ( ! empty( $_GET['page'] ) && false !== strpos( $_GET['page'], 'bp-profile-setup' ) ) {
			wp_enqueue_script( 'bp_admin_only_profile_fields' );
		}
	}

	/**
	 * Add our hidden visibility level.
	 *
	 * @param array $levels
	 *
	 * @return array
	 */
	public function custom_visibility_levels( $levels ) {

		$levels['hidden'] = array(
			'id'    => 'hidden',
			'label' => __( 'Hidden', 'bp_admin_only_profile_fields' )
		);

		return $levels;
	}

	/**
	 * Hide our hidden fields.
	 *
	 * @param array $hidden_fields
	 * @param int   $displayed_user_id
	 * @param int   $current_user_id
	 *
	 * @return array
	 */
	public function hide_hidden_fields( $hidden_fields, $displayed_user_id, $current_user_id ) {

		$hidden_fields = bp_xprofile_get_fields_by_visibility_levels( $displayed_user_id, array( 'hidden' ) );

		if ( ! current_user_can( apply_filters( 'bp_admin_only_profile_fields_cap', 'manage_options' ) ) ) {
			return $hidden_fields;
		}

		return array();
	}

}

$bp_admin_only_profile_fields = BP_Admin_Only_Profile_Fields::get_instance();
