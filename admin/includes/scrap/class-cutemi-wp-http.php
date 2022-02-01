<?php

/**
 * Class CUTEMI_WP_Http
 * Mod for accept proxy as args
 */
class CUTEMI_WP_Http extends WP_Http {

	private $proxy = null;

	public function request( $url, $args = array() ) {
		$defaults = array(
			'method'              => 'GET',
			/**
			 * Filters the timeout value for an HTTP request.
			 *
			 * @since 2.7.0
			 * @since 5.1.0 The `$url` parameter was added.
			 *
			 * @param float  $timeout_value Time in seconds until a request times out. Default 5.
			 * @param string $url           The request URL.
			 */
			'timeout'             => apply_filters( 'http_request_timeout', 5, $url ),
			/**
			 * Filters the number of redirects allowed during an HTTP request.
			 *
			 * @since 2.7.0
			 * @since 5.1.0 The `$url` parameter was added.
			 *
			 * @param int    $redirect_count Number of redirects allowed. Default 5.
			 * @param string $url            The request URL.
			 */
			'redirection'         => apply_filters( 'http_request_redirection_count', 5, $url ),
			/**
			 * Filters the version of the HTTP protocol used in a request.
			 *
			 * @since 2.7.0
			 * @since 5.1.0 The `$url` parameter was added.
			 *
			 * @param string $version Version of HTTP used. Accepts '1.0' and '1.1'. Default '1.0'.
			 * @param string $url     The request URL.
			 */
			'httpversion'         => apply_filters( 'http_request_version', '1.0', $url ),
			/**
			 * Filters the user agent value sent with an HTTP request.
			 *
			 * @since 2.7.0
			 * @since 5.1.0 The `$url` parameter was added.
			 *
			 * @param string $user_agent WordPress user agent string.
			 * @param string $url        The request URL.
			 */
			'user-agent'          => apply_filters( 'http_headers_useragent', 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ), $url ),
			/**
			 * Filters whether to pass URLs through wp_http_validate_url() in an HTTP request.
			 *
			 * @since 3.6.0
			 * @since 5.1.0 The `$url` parameter was added.
			 *
			 * @param bool   $pass_url Whether to pass URLs through wp_http_validate_url(). Default false.
			 * @param string $url      The request URL.
			 */
			'reject_unsafe_urls'  => apply_filters( 'http_request_reject_unsafe_urls', false, $url ),
			'blocking'            => true,
			'headers'             => array(),
			'cookies'             => array(),
			'body'                => null,
			'compress'            => false,
			'decompress'          => true,
			'sslverify'           => true,
			'sslcertificates'     => ABSPATH . WPINC . '/certificates/ca-bundle.crt',
			'stream'              => false,
			'filename'            => null,
			'limit_response_size' => null,
			'proxy'               => null,
		);

		// Pre-parse for the HEAD checks.
		$args = wp_parse_args( $args );

		// By default, HEAD requests do not cause redirections.
		if ( isset( $args['method'] ) && 'HEAD' === $args['method'] ) {
			$defaults['redirection'] = 0;
		}

		$parsed_args = wp_parse_args( $args, $defaults );
		/**
		 * Filters the arguments used in an HTTP request.
		 *
		 * @since 2.7.0
		 *
		 * @param array  $parsed_args An array of HTTP request arguments.
		 * @param string $url         The request URL.
		 */
		$parsed_args = apply_filters( 'http_request_args', $parsed_args, $url );

		// The transports decrement this, store a copy of the original value for loop purposes.
		if ( ! isset( $parsed_args['_redirection'] ) ) {
			$parsed_args['_redirection'] = $parsed_args['redirection'];
		}

		/**
		 * Filters the preemptive return value of an HTTP request.
		 *
		 * Returning a non-false value from the filter will short-circuit the HTTP request and return
		 * early with that value. A filter should return one of:
		 *
		 *  - An array containing 'headers', 'body', 'response', 'cookies', and 'filename' elements
		 *  - A WP_Error instance
		 *  - boolean false to avoid short-circuiting the response
		 *
		 * Returning any other value may result in unexpected behaviour.
		 *
		 * @since 2.9.0
		 *
		 * @param false|array|WP_Error $preempt     A preemptive return value of an HTTP request. Default false.
		 * @param array                $parsed_args HTTP request arguments.
		 * @param string               $url         The request URL.
		 */
		$pre = apply_filters( 'pre_http_request', false, $parsed_args, $url );

		if ( false !== $pre ) {
			return $pre;
		}

		if ( function_exists( 'wp_kses_bad_protocol' ) ) {
			if ( $parsed_args['reject_unsafe_urls'] ) {
				$url = wp_http_validate_url( $url );
			}
			if ( $url ) {
				$url = wp_kses_bad_protocol( $url, array( 'http', 'https', 'ssl' ) );
			}
		}

		$arr_url = wp_parse_url( $url );

		if ( empty( $url ) || empty( $arr_url['scheme'] ) ) {
			$response = new WP_Error( 'http_request_failed', __( 'A valid URL was not provided.' ) );
			/** This action is documented in wp-includes/class-http.php */
			do_action( 'http_api_debug', $response, 'response', 'Requests', $parsed_args, $url );
			return $response;
		}

		if ( $this->block_request( $url ) ) {
			$response = new WP_Error( 'http_request_not_executed', __( 'User has blocked requests through HTTP.' ) );
			/** This action is documented in wp-includes/class-http.php */
			do_action( 'http_api_debug', $response, 'response', 'Requests', $parsed_args, $url );
			return $response;
		}

		// If we are streaming to a file but no filename was given drop it in the WP temp dir
		// and pick its name using the basename of the $url.
		if ( $parsed_args['stream'] ) {
			if ( empty( $parsed_args['filename'] ) ) {
				$parsed_args['filename'] = get_temp_dir() . basename( $url );
			}

			// Force some settings if we are streaming to a file and check for existence
			// and perms of destination directory.
			$parsed_args['blocking'] = true;
			if ( ! wp_is_writable( dirname( $parsed_args['filename'] ) ) ) {
				$response = new WP_Error( 'http_request_failed', __( 'Destination directory for file streaming does not exist or is not writable.' ) );
				/** This action is documented in wp-includes/class-http.php */
				do_action( 'http_api_debug', $response, 'response', 'Requests', $parsed_args, $url );
				return $response;
			}
		}

		if ( is_null( $parsed_args['headers'] ) ) {
			$parsed_args['headers'] = array();
		}

		// WP allows passing in headers as a string, weirdly.
		if ( ! is_array( $parsed_args['headers'] ) ) {
			$processed_headers      = WP_Http::processHeaders( $parsed_args['headers'] );
			$parsed_args['headers'] = $processed_headers['headers'];
		}

		// Setup arguments.
		$headers = $parsed_args['headers'];
		$data    = $parsed_args['body'];
		$type    = $parsed_args['method'];
		$options = array(
			'timeout'   => $parsed_args['timeout'],
			'useragent' => $parsed_args['user-agent'],
			'blocking'  => $parsed_args['blocking'],
			'hooks'     => new WP_HTTP_Requests_Hooks( $url, $parsed_args ),
		);

		// Ensure redirects follow browser behaviour.
		$options['hooks']->register( 'requests.before_redirect', array( get_class(), 'browser_redirect_compatibility' ) );

		// Validate redirected URLs.
		if ( function_exists( 'wp_kses_bad_protocol' ) && $parsed_args['reject_unsafe_urls'] ) {
			$options['hooks']->register( 'requests.before_redirect', array( get_class(), 'validate_redirects' ) );
		}

		//Non http proxies, only support for curl
		$options['hooks']->register( 'curl.before_send', array( $this, 'curl_before_send' ) );

		if ( $parsed_args['stream'] ) {
			$options['filename'] = $parsed_args['filename'];
		}
		if ( empty( $parsed_args['redirection'] ) ) {
			$options['follow_redirects'] = false;
		} else {
			$options['redirects'] = $parsed_args['redirection'];
		}

		// Use byte limit, if we can.
		if ( isset( $parsed_args['limit_response_size'] ) ) {
			$options['max_bytes'] = $parsed_args['limit_response_size'];
		}

		// If we've got cookies, use and convert them to Requests_Cookie.
		if ( ! empty( $parsed_args['cookies'] ) ) {
			$options['cookies'] = WP_Http::normalize_cookies( $parsed_args['cookies'] );
		}

		// SSL certificate handling.
		if ( ! $parsed_args['sslverify'] ) {
			$options['verify']     = false;
			$options['verifyname'] = false;
		} else {
			$options['verify'] = $parsed_args['sslcertificates'];
		}

		// All non-GET/HEAD requests should put the arguments in the form body.
		if ( 'HEAD' !== $type && 'GET' !== $type ) {
			$options['data_format'] = 'body';
		}

		/**
		 * Filters whether SSL should be verified for non-local requests.
		 *
		 * @since 2.8.0
		 * @since 5.1.0 The `$url` parameter was added.
		 *
		 * @param bool   $ssl_verify Whether to verify the SSL connection. Default true.
		 * @param string $url        The request URL.
		 */
		$options['verify'] = apply_filters( 'https_ssl_verify', $options['verify'], $url );

		if (
			! empty( $parsed_args['proxy'] ) &&
			! empty( $parsed_args['proxy']['ip'] ) && ! empty( $parsed_args['proxy']['port'] )
		) {
			$this->proxy      = $parsed_args['proxy'];
			$options['proxy'] = new Requests_Proxy_HTTP(
				$parsed_args['proxy']['ip'] . ':' . $parsed_args['proxy']['port']
			);
			if ( ! empty( $parsed_args['proxy']['user'] ) && ! empty( $parsed_args['proxy']['pass'] ) ) {
				$options['proxy']->use_authentication = true;
				$options['proxy']->user               = $parsed_args['proxy']['user'];
				$options['proxy']->pass               = $parsed_args['proxy']['pass'];
			}
		} elseif ( null === $parsed_args['proxy'] ) {
			// Check for proxies.
			$proxy = new WP_HTTP_Proxy();
			if ( $proxy->is_enabled() && $proxy->send_through_proxy( $url ) ) {
				$options['proxy'] = new Requests_Proxy_HTTP( $proxy->host() . ':' . $proxy->port() );

				if ( $proxy->use_authentication() ) {
					$options['proxy']->use_authentication = true;
					$options['proxy']->user               = $proxy->username();
					$options['proxy']->pass               = $proxy->password();
				}
			}
		}

		// Avoid issues where mbstring.func_overload is enabled.
		mbstring_binary_safe_encoding();

		try {
			$requests_response = Requests::request( $url, $headers, $data, $type, $options );

			// Convert the response into an array.
			$http_response = new WP_HTTP_Requests_Response( $requests_response, $parsed_args['filename'] );
			$response      = $http_response->to_array();

			// Add the original object to the array.
			$response['http_response'] = $http_response;
		} catch ( Requests_Exception $e ) {
			$response = new WP_Error( 'http_request_failed', $e->getMessage() );
		}

		reset_mbstring_encoding();

		/**
		 * Fires after an HTTP API response is received and before the response is returned.
		 *
		 * @since 2.8.0
		 *
		 * @param array|WP_Error $response    HTTP response or WP_Error object.
		 * @param string         $context     Context under which the hook is fired.
		 * @param string         $class       HTTP transport used.
		 * @param array          $parsed_args HTTP request arguments.
		 * @param string         $url         The request URL.
		 */
		do_action( 'http_api_debug', $response, 'response', 'Requests', $parsed_args, $url );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! $parsed_args['blocking'] ) {
			return array(
				'headers'       => array(),
				'body'          => '',
				'response'      => array(
					'code'    => false,
					'message' => false,
				),
				'cookies'       => array(),
				'http_response' => null,
			);
		}

		/**
		 * Filters the HTTP API response immediately before the response is returned.
		 *
		 * @since 2.9.0
		 *
		 * @param array  $response    HTTP response.
		 * @param array  $parsed_args HTTP request arguments.
		 * @param string $url         The request URL.
		 */
		return apply_filters( 'http_response', $response, $parsed_args, $url );
	}

	/** @noinspection PhpUnused */
	public function curl_before_send( &$handle ) {
		if (
			! empty( $this->proxy ) &&
			! empty( $this->proxy['type'] ) &&
			isset( $this->proxy['tunnel'] ) &&
			function_exists( 'curl_setopt' ) &&
			(
				is_resource( $handle ) ||
				in_array( get_class( $handle ), array( 'CurlHandle', 'CurlMultiHandle', 'CurlShareHandle' ), true )
			)
		) {
			if ( true === $this->proxy['tunnel'] ) {
				//phpcs:ignore
				curl_setopt( $handle, CURLOPT_HTTPPROXYTUNNEL, 1 );
			}
			if ( 'socks5' === strtolower( $this->proxy['type'] ) ) {
				//phpcs:ignore
				curl_setopt($handle, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5 );
			} elseif ( 'socks4' === strtolower( $this->proxy['type'] ) ) {
				//phpcs:ignore
				curl_setopt($handle, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4 );
			}
		}
	}

}
