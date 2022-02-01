<?php
/** @noinspection DuplicatedCode */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

class CUTEMI_Fastdown_Data_Extract extends CUTEMI_XFileSharing_Data_Extract {

	public function __construct() {
		parent::__construct();
		$this->regex404 = ''; //disable generic offline
		$this->source   = 'cutemi-link-source-fastdown';
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
		if ( empty( $this->domain ) ) {
			$parse = wp_parse_url( $file_info['urls']['link'] );
			if ( empty( $parse['host'] ) ) {
				return $file_info;
			}
			$this->domain = $parse['host'];
		}

		$this->browser->init( $this->domain );

		$url = 'https://' . $this->domain . '/' . $file_info['external_id'] . '?v=' . time();
		$this->browser->simple_get( $url );

		$re = '`<font\s*color="red">https://down\.fast-down\.com/([^/]*)/([^<]*)</font>\s*\(([0-9\.,]*\s*(?:B|KB|MB|GB|TB))\)\s*</font>`im';
		if ( preg_match( $re, $this->browser->response, $matches ) ) {
			$file_info['external_id'] = $matches[1];
			$file_info['title']       = $matches[2];
			$file_info['size_str']    = $matches[3];
		}

		$file_info['offline'] = 0;
		if ( preg_match( '@>File Not Found<@', $this->browser->response ) ) {
			$url = 'https://' . $this->domain . '/' . $file_info['external_id'];
			$res = $this->mass_linkchecker_website( array( $url ) );
			if ( is_array( $res ) && isset( $res[ $url ] ) ) {
				if ( empty( $valid['size'] ) && ! empty( $res[ $url ]['size_str'] ) ) {
					$valid['size_str'] = $res[ $url ]['size_str'];
					$valid['size']     = cutemi_to_byte_size( $res[ $url ]['size_str'] );
				}
				if ( empty( $valid['offline'] ) && ! empty( $res[ $url ]['offline'] ) ) {
					$valid['offline'] = $res[ $url ]['offline'];
				}
			}
		}
		if ( empty( $file_info['title'] ) || empty( $file_info['size_str'] ) ) {
			return parent::scanInfo( $file_info );
		}

		return $file_info;
	}

}

new CUTEMI_Fastdown_Data_Extract();
