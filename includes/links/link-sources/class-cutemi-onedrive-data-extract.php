<?php
/** @noinspection DuplicatedCode */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

class CUTEMI_Onedrive_Data_Extract extends CUTEMI_Link_Source_Base {

	public function __construct() {
		add_filter( 'cutemi_link_data_extract_post', array( $this, 'complete_data' ), 10, 2 );
		parent::__construct();
	}

	public function complete_data( $valid, $link ) {
		if ( 'cutemi-link-source-onedrive' === $valid['sitesource'] ) {
			return $this->complete_info( $valid, $link );
		}

		/*
		 * "FileName":"([^"]*)"
		 * "FileSize":([0-9]*)
		 * */

		return $valid;
	}

	public function complete_info( $valid, $link ) {
		$parameter = $valid['urls']['link'];

		$sub_folder_base = null;
		$cid             = '';
		$id              = '';
		if (
			preg_match( '@.+/redir\?resid=[A-Za-z0-9]+!\d+.*?@', $parameter ) ||
			preg_match( '@.+/view\.aspx\?resid=.+@', $parameter )
		) {
			if ( preg_match( '@\?resid=([A-Za-z0-9]+)(!\d+)@', $parameter, $f_info ) ) {
				$cid = $f_info[1];
				$id  = $cid . $f_info[2];
			}
		} else {
			$cid = $this->simple_regex( '@cid=([A-Za-z0-9]*)@', $parameter, 0 );
			$id  = $this->getLastID( $parameter );
		}
		$authkey = $this->simple_regex( '@(?:&|\?)authkey=((?:!|%21)[A-Za-z0-9\-_%]+)@', $parameter, 0 );

		if ( null === $cid || null === $id ) {
			return $valid;
		}

		$cid = strtoupper( $cid );

		$additional_data = '';
		if ( ! empty( $authkey ) ) {
			$additional_data = '&authkey=' . $authkey;
		}

		$this->prepBrAPI();
		$this->accessItems_API( $valid['urls']['link'], $cid, $id, $additional_data );
		$json = json_decode( $this->browser->response, true );

		if ( isset( $json['error'] ) && isset( $json['error']['code'] ) && 3000 === $json['error']['code'] ) {
			$valid['offline'] = 1;
		}
		if ( is_array( $json ) && isset( $json['items'] ) ) {
			foreach ( $json['items'] as $item ) {
				if ( isset( $item['id'] ) && $item['id'] === $id ) {
					if ( ! empty( $item['size'] ) ) {
						$valid['size'] = $item['size'];
					}
					if ( ! empty( $item['name'] ) ) {
						$valid['title'] = $item['name'];
						if ( ! empty( $item['extension'] ) ) {
							$valid['title'] .= $item['extension'];
						}
						$valid['online'] = 1;
					}
					break;
				}
			}
		}

		return $valid;
	}

	private function getLastID( $parameter ) {
		/* Get last ID */
		$pos            = strrpos( $parameter, '&id=' ) + 4;
		$parameter_part = substr( $parameter, $pos );
		$ret            = $this->simple_regex( '@([A-Z0-9]+(?:\!|%21)\d+)@', $parameter_part );
		if ( ! empty( $ret ) ) {
			return str_replace( '%21', '!', $ret );
		}

		return $ret;
	}

	public function prepBrAPI() {
		$this->browser->set_header( 'Accept', 'application/json, text/javascript, */*; q=0.01' );
		$this->browser->set_header( 'X-Requested-With', 'XMLHttpRequest' );
		$this->browser->set_header( 'Accept-Language', 'en-us;q=0.7,en;q=0.3' );
		$this->browser->set_header( 'Accept-Charset', null );
		$this->browser->set_header( 'X-ForceCache', '1' );
		$this->browser->set_header( 'X-SkyApiOriginId', '0.9554840477898046' );
		$this->browser->set_header( 'Referer', 'https://skyapi.onedrive.live.com/api/proxy?v=3' );
		$this->browser->set_header( 'AppId', '1141147648' );
	}

	public function accessItems_API( $original_link, $cid, $id, $additional, $start_index = 0, $max_items = 100 ) {
		$v       = '0.10707631620552516';
		$data    = null;
		$from_to = '&si=' . $start_index . '&ps=' . $max_items;

		if ( false !== strpos( $original_link, 'ithint=' ) && null !== $id ) {
			$data = '&cid=' . rawurlencode( $cid ) . $additional;
			$this->browser->simple_get(
				'https://skyapi.onedrive.live.com/API/2/GetItems?id=' . $id .
										'&group=0&qt=&ft=&sb=1&sd=1&gb=0%2C1%2C2&d=1&iabch=1&caller=&path=1&pi=5&' .
										'm=de-DE&rset=skyweb&lct=1&v='
										. $v . $data . $from_to
			);
		} else {
			$data = '&cid=' . rawurlencode( $cid ) . '&id=' . rawurlencode( $id ) . $additional;
			$this->browser->simple_get(
				'https://skyapi.onedrive.live.com/API/2/GetItems?group=0&qt=&ft=&sb=0' .
										'&sd=0&gb=0&d=1&iabch=1&caller=unauth&path=1&pi=5&m=de-DE&rset=skyweb&lct=1&v=' .
										$v . $data . $from_to
			);
			if ( 500 === $this->browser->response_code ) {
				$this->browser->simple_get(
					'https://skyapi.onedrive.live.com/API/2/GetItems?group=0&qt=&ft=&sb=0' .
											'&sd=0&gb=0%2C1%2C2&d=1&iabch=1&caller=&path=1&pi=5&m=de-DE&rset=skyweb&lct=1&v=' .
											$v . $data . $from_to
				);
				$json = json_decode( $this->browser->response, true );
				if ( ! empty( $json['parentId'] ) ) {
					$data = '&cid=' . rawurlencode( $cid ) . '&id=' . rawurlencode( $json['parentId'] ) . '&sid=' .
							rawurlencode( $id ) . $additional;
					$this->browser->simple_get(
						'https://skyapi.onedrive.live.com/API/2/GetItems?group=0&qt=&ft=&' .
												'sb=0&sd=0&gb=0&d=1&iabch=1&caller=&path=1&pi=5&m=de-DE&rset=skyweb&lct=1&v=' .
												$v . $data . $from_to
					);
				}
			}
		}
	}

}

new CUTEMI_Onedrive_Data_Extract();
