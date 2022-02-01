<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

class CUTEMI_Clicknupload_Data_Extract extends CUTEMI_XFileSharing_Data_Extract {

	public function __construct() {
		parent::__construct();
		$this->source = 'cutemi-link-source-clicknupload';
		add_filter( 'cutemi_link_data_extract_post', array( $this, 'complete_data' ), 10, 2 );
	}

	public function complete_data( $valid, $link ) {
		if ( $valid['sitesource'] === $this->source ) {
			return parent::complete_data( $valid, $link );
		}

		return $valid;
	}

}

new CUTEMI_Clicknupload_Data_Extract();
