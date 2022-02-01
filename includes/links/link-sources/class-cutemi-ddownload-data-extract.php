<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

class CUTEMI_Ddownload_Data_Extract extends CUTEMI_XFileSharing_Data_Extract {

	public function __construct() {
		parent::__construct();
		$this->source = 'cutemi-link-source-ddownload';
		add_filter( 'cutemi_link_data_extract_post', array( $this, 'complete_data' ), 10, 2 );
	}

	public function complete_data( $valid, $link ) {
		if ( $valid['sitesource'] === $this->source ) {
			return parent::complete_data( $valid, $link );
		}

		return $valid;
	}

	public function supports_availablecheck_filesize_html() {
		return false;
	}

	public function scanInfo( $file_info ) {
		$file_info['title'] = $this->regex( '<div class="name position-relative">\s*<h4>([^<>"]+)</h4>' );
		if ( empty( $file_info['title'] ) ) {
			$file_info['title'] = $this->regex( '>File\s*:\s*<font[^>]*>([^<>"]+)<' );
		}
		$file_info['size_str'] = $this->regex( 'class="file-size">([^<>"]+)<' );
		if ( empty( $file_info['size_str'] ) ) {
			$file_info['size_str'] = $this->regex( '\[<font[^>]*>(\d+[^<>"]+)</font>\]' );
		}
		if ( empty( $file_info['title'] ) || empty( $file_info['size_str'] ) ) {
			return parent::scanInfo( $file_info );
		}

		return $file_info;
	}

}

new CUTEMI_Ddownload_Data_Extract();
