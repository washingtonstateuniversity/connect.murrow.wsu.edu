<?php

/**
 * Manage Buddypress and xprofile user metadata.
 *
 * @package wp-cli
 */
class BP_Command extends \WP_CLI\CommandWithDBObject {

	protected $obj_type = 'bp';
	protected $obj_fields = array(
		'ID',
	);

	public function __construct() {
		$this->fetcher = new \WP_CLI\Fetchers\BP;
	}

}

WP_CLI::add_command( 'bp', 'BP_Command' );


