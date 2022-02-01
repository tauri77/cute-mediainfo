<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

require_once CUTE_MEDIAINFO_DIR . '/admin/includes/scrap/class-cutemi-browser.php';

class CUTEMI_Link_Source_Base {

	/**
	 * @var null|CUTEMI_Browser
	 */
	public $browser = null;

	public function __construct() {
		$this->browser = new CUTEMI_Browser();
	}

	public function check_link_as_complete_data( $response, $data ) {
		if ( isset( $data['offset'] ) ) {
			unset( $data['offset'] );
		}
		if ( isset( $data['online'] ) ) {
			unset( $data['online'] );
		}
		$valid = $this->complete_data( $data, $data['urls']['link'] );
		if ( isset( $valid['offline'] ) ) {
			$response['offline'] = $valid['offline'];
		}
		if ( isset( $valid['online'] ) ) {
			$response['online'] = $valid['online'];
		}

		return $response;
	}

	public function complete_data( $valid, $link ) {
		return $valid;
	}

	public function regex( $pattern, $case_sensitive = false, $idx = 0 ) {
		$pattern = '`' . $pattern . ( $case_sensitive ? '`' : '`i' );
		if ( preg_match( $pattern, $this->browser->response, $matches ) ) {
			if ( $idx < 0 ) {
				if ( isset( $matches[ count( $matches ) + $idx ] ) ) {
					return $matches[ count( $matches ) + $idx ];
				}

				return '';
			}
			if ( isset( $matches[ $idx + 1 ] ) ) {
				return $matches[ $idx + 1 ];
			}
		}

		return '';
	}

	public function simple_regex( $pattern, $content, $idx = 0 ) {
		if ( preg_match( $pattern, $content, $matches ) ) {
			if ( $idx < 0 ) {
				if ( isset( $matches[ count( $matches ) + $idx ] ) ) {
					return $matches[ count( $matches ) + $idx ];
				}

				return '';
			}
			if ( isset( $matches[ $idx + 1 ] ) ) {
				return $matches[ $idx + 1 ];
			}
		}

		return '';
	}


}
