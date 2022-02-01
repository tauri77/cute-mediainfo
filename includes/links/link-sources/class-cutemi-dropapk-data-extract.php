<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

class CUTEMI_Dropapk_Data_Extract extends CUTEMI_XFileSharing_Data_Extract {

	public function __construct() {
		parent::__construct();
		$this->source = 'cutemi-link-source-dropapk';
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

	public function supports_availablecheck_filesize_alt_fast() {
		return false;
	}

	public function supports_mass_linkcheck_over_website() {
		return false;
	}

	public function scanInfo( $file_info ) {

		$url = 'https://' . $this->domain . '/' . $file_info['external_id'];
		$this->browser->simple_get( $url );

		$file_info = parent::scanInfo( $file_info );

		if ( empty( $file_info['size_str'] ) ) {
			$file_info['size_str'] = $this->regex( '\(\s*(\d+(?:\.\d+)?(?: |\&nbsp;)?(KB|MB|GB|B))' );
		}

		//Recheck for false offline
		if ( isset( $file_info['offline'] ) && 1 === $file_info['offline'] ) {
			$file_info['offline'] = 0;
			$filename             = $this->getFnameViaAbuseLink( $url );
			if ( 'NOT_FOUND' === $filename ) {
				$valid['offline'] = 1;
			} elseif ( ! empty( $filename ) ) {
				$valid['title'] = $filename;
			}
		}

		return $file_info;
	}

}

new CUTEMI_Dropapk_Data_Extract();
