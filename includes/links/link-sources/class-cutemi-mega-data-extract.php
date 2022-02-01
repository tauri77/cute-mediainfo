<?php
/** @noinspection DuplicatedCode */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

require_once CUTE_MEDIAINFO_DIR . '/includes/links/mega-lib/class-cutemi-megacrypto.php';
require_once CUTE_MEDIAINFO_DIR . '/includes/links/mega-lib/class-cutemi-megautil.php';

class CUTEMI_Mega_Data_Extract extends CUTEMI_Link_Source_Base {

	public function __construct() {
		add_filter( 'cutemi_link_data_extract_post', array( $this, 'complete_data' ), 10, 2 );
		parent::__construct();
	}

	public function complete_data( $valid, $link ) {
		if ( 'cutemi-link-source-mega' === $valid['sitesource'] || 'cutemi-link-source-mega-folder' === $valid['sitesource'] ) {
			return $this->complete_info( $valid, $link );
		}

		return $valid;
	}

	public function complete_info( $valid, $o_link ) {
		$link = $this->parse_link( $valid['urls']['link'] );
		if ( false === $link ) {
			return $valid;
		}
		if ( 'file' === $link['type'] && ! empty( $link['key'] ) ) {
			$infos = $this->bach( $link['ph'] . '#' . $link['key'] );
			if ( ! empty( $infos ) ) {
				if ( ! empty( $infos[0]['title'] ) ) {
					$valid['title']  = $infos[0]['title'];
					$valid['online'] = 1;
				}
				if ( ! empty( $infos[0]['size'] ) ) {
					$valid['size'] = $infos[0]['size'];
				}
				if ( isset( $infos[0]['offline'] ) ) {
					$valid['offline'] = $infos[0]['offline'];
				}
			}
		}
		if ( 'folder' === $link['type'] && ! empty( $link['key'] ) ) {
			return $this->complete_folder_file( $valid, $link );
		}

		return $valid;
	}

	public static function parse_link( $link ) {
		$matches = array();
		if ( preg_match( '@(/embed/|/file/|/#!|/F!|/folder/?)([a-zA-Z0-9]+)(?:[!#]([a-zA-Z0-9_,\-]+))?@', $link, $matches ) ) {
			$file = array(
				'type' => in_array( $matches[1], array( 'F!', '/folder/' ), true ) ? 'folder' : 'file',
				'ph'   => $matches[2],
			);
			if ( ! empty( $matches[3] ) ) {
				$file['key'] = $matches[3];
			}

			return $file;
		}

		return false;
	}

	public function bach( $ids ) {
		$url = 'https://eu.api.mega.co.nz/cs';
		$this->browser->init( 'eu.api.mega.co.nz' );

		if ( is_string( $ids ) ) {
			$ids = explode( ',', $ids );
		}

		$keys = array();
		$data = array();
		foreach ( $ids as $key => $id ) {
			$p      = explode( '#', $id );
			$keys[] = $p[1];
			$data[] = array(
				'a'   => 'g',
				'g'   => 0,
				'ssl' => 0,
				'p'   => $p[0],
			);
		}

		$this->browser->simple_post( $url, wp_json_encode( $data ) );
		$infos = array();
		$json  = json_decode( $this->browser->response, true );
		if ( is_array( $json ) ) {
			foreach ( $json as $key => $res ) {
				if ( - 9 === $res || - 2 === $res || - 11 === $res ) {
					$info                = array();
					$info['offline']     = 1;
					$info['external_id'] = $ids[ $key ];
					$infos[]             = $info;
				} elseif ( isset( $res['s'] ) ) {

					$k               = CUTEMI_MEGAUtil::base64_to_a32( $keys[ $key ] );
					$attr            = CUTEMI_MEGAUtil::base64_to_str( $res['at'] );
					$res['filename'] = CUTEMI_MEGACrypto::dec_attr( $attr, $k );
					$info            = array();
					$info['offline'] = 0;
					if ( isset( $res['filename']['n'] ) ) {
						$info['title'] = $res['filename']['n'];
					}
					$info['size']        = $res['s'];
					$info['external_id'] = $ids[ $key ];
					$infos[]             = $info;
				}
			}
		}

		return $infos;
	}

	public function complete_folder_file( $valid, $link_parts ) {
		$file_id = null;
		if ( preg_match( '@/file/([a-z0-9_\-]*)@i', $valid['urls']['link'], $match ) ) {
			$file_id = $match[1];
		}

		$url = 'https://g.api.mega.co.nz/cs?id=-' . wp_rand( 0, 9999999999999 ) . '&n=' . $link_parts['ph'] . '';
		$this->browser->init( 'g.api.mega.co.nz' );

		$data = array(
			'a'  => 'f',
			'c'  => 1,
			'ca' => 1,
			'r'  => 1,
		);

		$this->browser->simple_post( $url, wp_json_encode( array( $data ) ) );
		$res = json_decode( $this->browser->response, true );

		$shared_key = CUTEMI_MEGAUtil::base64_to_a32( $link_parts['key'] );
		$shared_key = CUTEMI_MEGAUtil::a32_to_str( $shared_key );
		if ( ! empty( $res ) ) {
			$res = array_shift( $res );
			if ( isset( $res['f'] ) ) {
				foreach ( $res['f'] as $index => $node ) {
					if ( ( 0 === $node['t'] || 1 === $node['t'] ) && ! empty( $node['k'] ) ) {
						//as file in folder
						if ( null !== $file_id ) {
							if ( $node['h'] === $file_id ) {
								$key = $this->decrypt_node_key( $node['k'], $shared_key );
								if ( $key ) {
									$attr           = CUTEMI_MEGAUtil::base64_to_str( $node['a'] );
									$filename       = CUTEMI_MEGACrypto::dec_attr( $attr, $key );
									$valid['title'] = $filename['n'];
									if ( $node['s'] ) {
										$valid['size'] = $node['s'];
									}
								}
							}
						} else { //as folder
							if ( $node['s'] ) {
								$valid['size'] += $node['s'];
							}
							if ( empty( $valid['title'] ) ) {
								$key = $this->decrypt_node_key( $node['k'], $shared_key );
								if ( $key ) {
									$attr           = CUTEMI_MEGAUtil::base64_to_str( $node['a'] );
									$filename       = CUTEMI_MEGACrypto::dec_attr( $attr, $key );
									$valid['title'] = $filename['n'];
								}
							}
						}
					}
				}
			}
		}

		return $valid;
	}

	private function decrypt_node_key( $key_str, $shared_key ) {
		$kk = explode( ':', $key_str );
		if ( count( $kk ) < 2 ) {
			return null;
		}
		$encrypted_key = CUTEMI_MEGAUtil::base64_to_a32( $kk[1] );

		return CUTEMI_MEGACrypto::decrypt_key( $shared_key, $encrypted_key );
	}

}

new CUTEMI_Mega_Data_Extract();
