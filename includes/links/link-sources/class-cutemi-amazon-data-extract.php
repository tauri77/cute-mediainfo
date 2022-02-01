<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

class CUTEMI_Amazon_Data_Extract {

	public function __construct() {
		add_filter( 'cutemi_link_data_extract_post', array( $this, 'complete_data' ), 10, 2 );
	}

	public function complete_data( $valid, $link ) {
		if ( 'cutemi-link-source-amazon-drive' === $valid['sitesource'] ) {
			$domain = 'www.amazon.com';
		} elseif ( 'cutemi-link-source-amazon-drive-ue' === $valid['sitesource'] ) {
			$domain = 'www.amazon.es';
		} else {
			return $valid;
		}

		$url = 'https://' . $domain . '/drive/v1/shares/' . $valid['external_id'] .
				'?shareId=' . $valid['external_id'] . '&resourceVersion=V2&ContentType=JSON&_=';

		$html = '';
		foreach ( $valid['html'] as $_url => $_html ) {
			if ( substr( $_url, 0, strlen( $url ) ) === $url ) {
				$html = $_html;
			}
		}

		if ( empty( $html ) ) {
			$url                  .= time();
			$html                  = cutemi_http_fetch( $url . time() );
			$valid['html'][ $url ] = $html;
		}

		$json = json_decode( $html, true );

		if ( isset( $json['nodeInfo'] ) ) {
			if ( isset( $json['nodeInfo']['name'] ) ) {
				$valid['title'] = $json['nodeInfo']['name'];

				$url2 = 'https://' . $domain . '/drive/v1/nodes/' . $json['nodeInfo']['id'] .
						'/children?asset=ALL&tempLink=false&limit=200&sort=%5B%27kind+DESC%27%2C+%27name+ASC%27%5D&' .
						'searchOnFamily=false&shareId=' . $valid['external_id'] . '&offset=0&resourceVersion=V2&' .
						'ContentType=JSON&_=' . time();
				$str2 = cutemi_http_fetch( $url2 );

				$valid['html'][ $url2 ] = $str2;

				$json2 = json_decode( $str2, true );
				if ( ! empty( $json2['data'] ) ) {
					if ( isset( $json2['data'][0]['contentProperties'] ) && isset( $json2['data'][0]['contentProperties']['size'] ) ) {
						$valid['size'] = $json2['data'][0]['contentProperties']['size'];
					}
					if ( isset( $json2['data'][0]['name'] ) ) {
						$valid['title'] = $json2['data'][0]['name'];
					}
				}
			}
		}

		return $valid;
	}

}

new CUTEMI_Amazon_Data_Extract();
