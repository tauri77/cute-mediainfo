<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

class CUTEMI_Pcloud_Data_Extract extends CUTEMI_Link_Source_Base {

	private $europe = false;

	public function __construct() {
		add_filter( 'cutemi_link_data_extract_post', array( $this, 'complete_data' ), 10, 2 );
		parent::__construct();
	}

	public function complete_data( $valid, $link ) {
		if ( 'cutemi-link-source-pcloud' === $valid['sitesource'] ) {
			$this->europe = false;

			return $this->complete_info( $valid, $link );
		}
		if ( 'cutemi-link-source-pcloud-e' === $valid['sitesource'] ) {
			$this->europe = true;

			return $this->complete_info( $valid, $link );
		}

		return $valid;
	}

	public function complete_info( $valid, $link ) {

		if ( $this->europe ) {
			$this->browser->init( 'eapi.pcloud.com' );
			$url = 'http://eapi.pcloud.com/showpublink?code=' . $valid['external_id'];
		} else {
			$this->browser->init( 'api.pcloud.com' );
			$url = 'http://api.pcloud.com/showpublink?code=' . $valid['external_id'];
		}

		$this->browser->simple_get( $url );
		$json = json_decode( $this->browser->response, true );

		if ( isset( $json['result'] ) && '7001' === $json['result'] ) {
			$valid['offline'] = 1;
		}

		if ( isset( $json['metadata'] ) ) {
			if ( isset( $json['metadata']['name'] ) ) {
				$valid['title']  = $json['metadata']['name'];
				$valid['online'] = 1;
			}
			if ( isset( $json['metadata']['size'] ) ) {
				$valid['size'] = $json['metadata']['size'];
			}
		}

		return $valid;
	}

}

new CUTEMI_Pcloud_Data_Extract();
