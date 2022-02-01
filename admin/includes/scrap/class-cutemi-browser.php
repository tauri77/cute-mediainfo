<?php
/** @noinspection DuplicatedCode */
/** @noinspection PhpUnused */

require_once dirname( __FILE__ ) . '/class-cutemi-wp-http.php';
require_once dirname( __FILE__ ) . '/class-cutemi-javascript-unpacker.php';
require_once dirname( __FILE__ ) . '/class-cutemi-wise.php';

class CUTEMI_Browser {

	public $url         = null;
	public $method      = 'GET';
	public $base_url    = null;
	public $base_domain = '';
	public $base_scheme = 'https';
	public $host_header = '';
	public $proxy       = null;
	public $sslverify   = false;
	public $redirection = false;
	public $httpversion = '1.1';
	public $cookies     = array();
	public $body        = null;
	public $user_agent  = 'Mozilla/5.0 (X11; Linux x86_64; rv:78.0) Gecko/20100101 Firefox/78.0';

	public $debugging = false;

	public $last_url = '';

	public $headers = array(
		'Accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
		'Accept-Encoding'           => 'gzip, deflate',
		'Accept-Language'           => 'en-US;q=0.5,en;q=0.3',
		'Upgrade-Insecure-Requests' => '1',
		'TE'                        => 'Trailers',
		'pragma'                    => 'no-cache',
		'cache-control'             => 'no-cache',
	);

	public $response         = '';
	public $response_headers = array();
	public $response_code    = 0;

	public function __construct() {
		if ( ! empty( $GLOBALS['cutemi_proxy_list'] ) ) {
			$i = array_rand( $GLOBALS['cutemi_proxy_list'] );
			$this->set_proxy_from_str( $GLOBALS['cutemi_proxy_list'][ $i ] );
		}
	}

	public function init( $base_domain, $base_scheme = 'https', $host_header = '' ) {

		$this->base_domain = $base_domain;
		$this->base_scheme = $base_scheme;
		$this->host_header = $host_header;

		if ( ! empty( $this->base_domain ) ) {
			$this->headers['authority'] = $this->base_domain;
			$url                        = "{$this->base_scheme}://{$this->base_domain}/";
			$this->set_referer( $url );
			$this->set_base( $url );
			$this->set_url( $url );
		}

		$this->redirection = 3;
	}

	public function set_url( $url, $mixed_data = '' ) {
		$this->base_url = $url;
		$this->url      = $this->build_url( $url, $mixed_data );
	}

	private function build_url( $url, $mixed_data = '' ) {
		$query_string = '';
		if ( ! empty( $mixed_data ) ) {
			if ( is_string( $mixed_data ) ) {
				$query_string .= '?' . $mixed_data;
			} elseif ( is_array( $mixed_data ) ) {
				$query_string .= '?' . http_build_query( $mixed_data, '', '&' );
			}
		}

		return $url . $query_string;
	}

	public function set_referer( $referer ) {
		$this->set_header( 'referer', $referer );
	}

	public function set_header( $key, $value ) {
		$this->headers[ $key ] = $value;
	}

	public function set_headers( $headers ) {
		foreach ( $headers as $key => $value ) {
			$this->headers[ $key ] = $value;
		}
	}

	public function unset_header( $key ) {
		unset( $this->headers[ $key ] );
	}

	public function set_base( $url ) {
		$result = wp_parse_url( $url );
		if ( ! empty( $result['host'] ) ) {
			$this->base_domain = $result['host'];
		}
		if ( ! empty( $result['scheme'] ) ) {
			$this->base_scheme = $result['scheme'];
		}
	}

	public function set_user_agent( $user_agent ) {
		$this->user_agent = $user_agent;
	}

	/**
	 * Replace host_header(real domain) with domain(maybe IP)
	 *
	 * @param $url
	 *
	 * @return string
	 */
	public function fix_url_host_header( $url ) {
		if ( ! empty( $this->host_header ) && ! empty( $this->domain ) ) {
			$url = str_replace( '//' . $this->host_header . '/', '//' . $this->domain . '/', $url );
			if ( 'http' === $this->base_scheme ) {
				$url = str_replace( 'https://', 'http://', $url );
			}
		}

		return $url;
	}

	public function set_cookie_value( $name, $value, $domain = false, $secure = false, $expires = null ) {
		$data = array();

		$data['expires'] = $expires;
		if ( null === $expires ) {
			$data['expires'] = time() + ( 6 * 60 * 60 );
		}

		$data['host_only'] = true;
		if ( ! $secure ) {
			$data['host_only'] = false;
		}

		if ( false === $domain ) {
			$domain = $this->base_domain;
		}
		$data['domain'] = $domain;
		$data['name']   = $name;
		$data['value']  = $value;

		$this->unset_cookie( $name, $domain );
		$this->cookies[] = new WP_Http_Cookie( $data );
	}

	public function unset_cookie( $name, $host = false ) {
		foreach ( $this->cookies as $k => $cookie ) {
			if ( $name === $cookie->name ) {
				if ( false !== $host && $cookie->host !== $host ) {
					continue;
				}
				unset( $this->cookies[ $k ] );
			}
		}
	}

	public function clear_cookies() {
		$this->cookies[] = array();
	}

	public function clear_cookies_except_cf() {
		foreach ( $this->cookies as $k => $cookie ) {
			if ( ! $this->starts_with( $cookie->name, '__cf' ) ) {
				unset( $this->cookies[ $k ] );
			}
		}
	}

	public function get_first_match( $regex ) {
		$str = $this->response;
		if ( preg_match( $regex, $str, $matches ) ) {
			return $matches[1];
		}

		return '';
	}

	public function submit_form( $form ) {
		$url = $form['action'];
		if ( empty( $url ) ) {
			$url = ! empty( $this->last_url ) ? $this->last_url : $this->base_url;
		}
		$url = $this->get_completed_url( $url );
		if ( strtolower( $form['method'] ) === 'post' ) {
			$this->debug( "POST $url", false );

			return $this->simple_post( $url, $form['params'] );
		}
		$this->debug( "GET $url", false );

		return $this->simple_get( $url, $form['params'] );
	}

	public function get_completed_url( $maybe_relative_url, $base_url = null ) {
		if ( null === $base_url ) {
			$base_url = $this->base_url;
		}
		if ( empty( $base_url ) ) {
			$base_url = $this->base_scheme . '://' . $this->base_domain;
		}
		$parsed_url = wp_parse_url( $base_url );

		if ( $this->starts_with( $maybe_relative_url, 'https://' ) || $this->starts_with( $maybe_relative_url, 'http://' ) ) {
			return $maybe_relative_url;
		} elseif ( $this->starts_with( $maybe_relative_url, '//' ) ) {
			return $parsed_url['scheme'] . ':' . $maybe_relative_url;
		} elseif ( $this->starts_with( $maybe_relative_url, '/' ) ) {
			return $parsed_url['scheme'] . '://' . $parsed_url['host'] . $maybe_relative_url;
		} else {
			$path = $parsed_url['path'];
			if ( ! $this->ends_with( $path, '/' ) ) {
				$path = dirname( $path ) . '/';
			}

			return $parsed_url['scheme'] . '://' . $parsed_url['host'] . $path . $maybe_relative_url;
		}
	}

	public function starts_with( $haystack, $needle ) {
		return substr( $haystack, 0, strlen( $needle ) ) === $needle;
	}

	public function ends_with( $haystack, $needle ) {
		$length = strlen( $needle );
		if ( 0 === $length ) {
			return true;
		}

		return ( substr( $haystack, - $length ) === $needle );
	}

	public function simple_post( $url, $params ) {
		$url = $this->get_completed_url( $url );

		$this->debug( '<h1>POST</h1>', false );
		$this->debug( $url );
		$this->debug( $params, true );

		if ( ! empty( $this->host_header ) ) {
			$this->set_header( 'Host', $this->host_header );
		}
		return $this->post( $url, $params );
	}

	public function simple_get( $url, $params = array() ) {
		$url = $this->get_completed_url( $url );

		$this->debug( '<h1>GET</h1>', false );
		$this->debug( $url );
		$this->debug( $params, true );
		if ( empty( $this->base_domain ) ) {
			$this->set_base( $url );
		}
		if ( ! empty( $this->host_header ) ) {
			$this->set_header( 'Host', $this->host_header );
		}

		return $this->get( $url, $params );
	}

	public function get( $url, $data = array() ) {
		if ( is_array( $url ) ) {
			$data = $url;
			$url  = $this->base_url;
		}
		unset( $this->headers['Content-Type'] );
		$this->set_url( $url, $data );
		$this->method = 'GET';

		return $this->exec();
	}

	public function head( $url, $data = array() ) {
		if ( is_array( $url ) ) {
			$data = $url;
			$url  = $this->base_url;
		}
		unset( $this->headers['Content-Type'] );
		$this->method = 'HEAD';
		$this->set_url( $url, $data );

		return $this->exec();
	}

	public function post( $url, $data = array() ) {
		$this->set_url( $url );
		$this->method = 'POST';
		unset( $this->headers['Content-Type'] );
		if ( is_string( $data ) ) {
			if (
				in_array( substr( $data, 0, 1 ), array( '{', '[' ), true ) &&
				is_array( json_decode( $data, true ) )
			) {
				$this->body                    = $data;
				$this->headers['Content-Type'] = 'application/json';
			} else {
				parse_str( $data, $this->body );
			}
		} else {
			$this->body = $data;
		}

		return $this->exec();
	}

	private function get_http_args() {
		return array(
			'method'      => $this->method,
			'timeout'     => 5,
			'redirection' => false, //Manage redirection here...
			'httpversion' => $this->httpversion,
			'user-agent'  => $this->user_agent,
			'headers'     => $this->headers,
			'cookies'     => $this->cookies,
			'body'        => ( in_array( $this->method, array( 'PUT', 'POST' ), true ) ? $this->body : null ),
			'sslverify'   => $this->sslverify,
			'proxy'       => $this->proxy,
		);
	}

	public function exec() {
		$http           = new CUTEMI_WP_Http();
		$args           = $this->get_http_args();
		$this->last_url = $this->url;
		$return         = $http->request( $this->url, $args );
		$this->mix_cookies( $return['cookies'] );
		$this->debug( 'REQUEST: ' . $this->url );
		$this->debug( $args );

		$this->response_code = wp_remote_retrieve_response_code( $return );

		$redirected = 0;
		while ( $this->is_redirect() && (int) $this->redirection > $redirected ) {
			if ( isset( $return['headers']['location'] ) ) {
				if ( 303 === $this->response_code ) {
					$args['method'] = 'GET';
				}
				$location = $return['headers']['location'];
				if ( 0 !== strpos( $location, 'http://' ) && 0 !== strpos( $location, 'https://' ) ) {
					// relative redirect, for compatibility make it absolute
					$this->get_completed_url( $location, $this->url );
				}
				$this->last_url = $location;
				$this->debug( 'REQUEST: ' . $location );
				$this->debug( $args );
				$return = $http->request( $location, $args );
				$this->mix_cookies( $return['cookies'] );

				$this->response_code = wp_remote_retrieve_response_code( $return );
			}
			$redirected++;
		}

		$this->response         = wp_remote_retrieve_body( $return );
		$this->response_headers = $return['headers'];
		$this->debug( $return );

		return $this->response;
	}

	public function mix_cookies( $new_cookies ) {
		if ( is_array( $new_cookies ) ) {
			foreach ( $new_cookies as $new_cookie ) {
				if ( $new_cookie instanceof WP_Http_Cookie ) {
					$this->set_cookie_value(
						$new_cookie->name,
						$new_cookie->value,
						$new_cookie->domain,
						$new_cookie->host_only,
						$new_cookie->expires
					);
				}
			}
		}
	}

	public function is_redirect() {
		$code = $this->response_code;
		return in_array( $code, array( 300, 301, 302, 303, 307 ), true ) || $code > 307 && $code < 400;
	}

	public function get_form( $name ) {
		$forms = $this->get_forms();
		foreach ( $forms as $form ) {
			if ( $form['name'] === $name ) {
				return $form;
			}
		}

		return null;
	}

	public function get_forms() {
		$doc_forms = array();
		if ( preg_match_all( '@<(form[^>]*).*?</form@imsx', $this->response, $forms ) ) {
			foreach ( $forms[0] as $idx => $code ) {
				$form           = array();
				$form['action'] = '';
				$form['name']   = '';
				$form['method'] = 'GET';
				$form['code']   = $code;

				$comment = $this->cut_str( $form['code'], '<!--', '-->' );
				while ( $comment ) {
					$form['code'] = str_replace( '<!--' . $comment . '-->', '', $form['code'] );
					$comment      = $this->cut_str( $form['code'], '<!--', '-->' );
				}

				if ( preg_match( '@name\s*=\s*["\']([^>\'"]*)"@imsx', $forms[1][ $idx ], $valor ) ) {
					$form['name'] = $valor[1];
				}
				if ( preg_match( '@action\s*=\s*["\']([^>\'"]*)"@imsx', $forms[1][ $idx ], $valor ) ) {
					$form['action'] = $valor[1];
				}
				if ( preg_match( '@method\s*=\s*["\']([^>"\']*)"@imsx', $forms[1][ $idx ], $valor ) ) {
					$form['method'] = $valor[1];
				}
				$form['params'] = array();
				if ( preg_match_all( '@<input[^>]*name\s*=\s*"([^>"]*)"[^>]*>@imsx', $form['code'], $inputs ) ) {
					foreach ( $inputs[1] as $input_idx => $input_name ) {
						$val = '';
						if ( preg_match( '@value\s*=\s*"([^>"]*)"@imsx', $inputs[0][ $input_idx ], $valor ) ) {
							$val = $valor[1];
						}
						if ( preg_match( "@value\s*=\s*'([^>']*)'@imsx", $inputs[0][ $input_idx ], $valor ) ) {
							$val = $valor[1];
						}
						$form['params'][ $input_name ] = $val;
					}
				}
				//Ahora con comillas simples
				if ( preg_match_all( "@<input[^>]*name\s*=\s*'([^>']*)'[^>]*>@imsx", $form['code'], $inputs ) ) {
					foreach ( $inputs[1] as $input_idx => $input_name ) {
						$val = '';
						if ( preg_match( "@value\s*=\s*'([^>']*)'@imsx", $inputs[0][ $input_idx ], $valor ) ) {
							$val = $valor[1];
						}
						if ( preg_match( '@value\s*=\s*"([^>"]*)"@imsx', $inputs[0][ $input_idx ], $valor ) ) {
							$val = $valor[1];
						}
						$form['params'][ $input_name ] = $val;
					}
				}
				$doc_forms[] = $form;
			}
		}

		return $doc_forms;
	}

	public function cut_str( $str, $left, $right ) {
		$str      = substr( stristr( $str, $left ), strlen( $left ) );
		$left_len = strlen( stristr( $str, $right ) );
		$left_len = $left_len ? - ( $left_len ) : strlen( $str );
		$str      = substr( $str, 0, $left_len );

		return $str;
	}

	public function set_proxy_from_str( $pr ) {
		//"10.28.138.12:45554[:http|socks4|socks5]:user:pass"

		$px_parts  = explode( ':', $pr );
		$px_ip     = $px_parts[0];
		$px_port   = $px_parts[1];
		$px_type   = '';
		$px_tunnel = '';
		$px_user   = false;
		$px_pass   = false;
		if ( count( $px_parts ) === 5 ) {
			$px_type = $px_parts[2];
			$px_user = $px_parts[3];
			$px_pass = $px_parts[4];
		} elseif ( count( $px_parts ) === 4 ) {
			$px_user = $px_parts[2];
			$px_pass = $px_parts[3];
		} elseif ( count( $px_parts ) === 3 ) {
			$px_type = $px_parts[2];
		}
		if ( substr( $px_type, 0, 1 ) === '_' ) {
			$px_tunnel = true;
			$px_type   = substr( $px_type, 1 );
		}

		$this->set_proxy( $px_ip, $px_port, $px_type, $px_tunnel, $px_user, $px_pass );
	}

	public function set_proxy( $px_ip, $px_port, $px_type = '', $px_tunnel = false, $px_user = false, $px_pass = false ) {

		$this->proxy = array(
			'ip'   => $px_ip,
			'port' => $px_port,
			'type' => $px_type,
			'user' => $px_user,
			'pass' => $px_pass,
		);
	}

	public function unpackjs() {
		$unpacker = new CUTEMI_JavaScript_Unpacker();
		if ( preg_match_all( '/eval\(function\(\w+,\w+,\w+,\w+,\w+,\w+\).*?\.split\(\'\|\'\)(?:,0)?(?:,{})?\)\)/', $this->response, $matches ) ) {
			$this->debug( 'Upacking', false );
			foreach ( $matches[0] as $packed ) {
				$this->debug( $packed, true );
				$unpacked       = $unpacker->unpack( $packed );
				$this->response = str_replace( $packed, $unpacked, $this->response );
				$this->debug( $unpacked, true );
			}
		}
	}

	public function debug( $text, $in_textarea = true ) {
		if ( true === $this->debugging ) {
			if ( is_object( $text ) ) {
				$text = (array) $text;
			}
			if ( ! is_string( $text ) ) {
				$text = wp_json_encode( $text, JSON_UNESCAPED_UNICODE );
				if ( false === $text ) {
					echo 'json_encode error..';
					if ( function_exists( 'iconv ' ) ) {
						$text = iconv( 'UTF-8', 'UTF-8//IGNORE', $text );
					}
					$text = wp_json_encode( $text, JSON_UNESCAPED_UNICODE );
				}
			}
			if ( $in_textarea ) {
				echo '<textarea>' . esc_html( $text ) . "</textarea><br>\n";
			} else {
				echo esc_html( $text ) . "<br>\n";
			}
			if ( ob_get_level() > 0 ) {
				flush();
			}
		}
	}

	public function unwise() {
		$re = '/eval\(function\(\s*w,\s*i,\s*s,\s*e\){(.*?)}\([\'"]([^\'"]*)[\'"](?:,[\'"]([^\'"]*)[\'"])?(?:,[\'"]([^\'"]*)[\'"])?(?:,[\'"]([^\'"]*)[\'"])?\)\);?/m';
		if ( preg_match_all( $re, $this->response, $matches, PREG_SET_ORDER, 0 ) ) {
			foreach ( $matches as $packed ) {
				if ( preg_match( '/var\s*lIll\s*=\s*0/i', $packed[1] ) ) {
					$unpacked       = CUTEMI_Wise::wise1( $packed[2], $packed[3], $packed[4], $packed[5] );
					$this->response = str_replace( $packed[0], $unpacked, $this->response );
				} elseif ( preg_match( '/for\s*\(\s*s\s*=\s*0\s*;\s*s\s*<\s*w/i', $packed[1] ) ) {
					$unpacked       = CUTEMI_Wise::wise2( $packed[2], $packed[3], $packed[4], $packed[5] );
					$this->response = str_replace( $packed[0], $unpacked, $this->response );
				}
			}
		}
	}

}
