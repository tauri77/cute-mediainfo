<?php

if ( ! class_exists( 'CUTEMI_MEGACrypto' ) ) {

	class CUTEMI_MEGACrypto {

		public static function prepare_key_pw( $password ) {
			return self::prepare_key( CUTEMI_MEGAUtil::str_to_a32( $password ) );
		}

		// prepare_key with string input

		/**
		 * Convert user-supplied password array.
		 *
		 * @param array $a
		 *   The user password array of 32-bit words.
		 *
		 * @return string
		 *   The AES user password key.
		 */
		public static function prepare_key( $a ) {
			$pkey  = CUTEMI_MEGAUtil::a32_to_str( array( 0x93C467E3, 0x7DB0C7A4, 0xD1BE3F81, 0x0152CB56 ) );
			$total = count( $a );
			for ( $r = 65536; $r --; ) {
				for ( $j = 0; $j < $total; $j += 4 ) {
					$key = array( 0, 0, 0, 0 );
					for ( $i = 0; $i < 4; $i ++ ) {
						if ( $i + $j < $total ) {
							$key[ $i ] = $a[ $i + $j ];
						}
					}
					$pkey = self::encrypt_aes_cbc( CUTEMI_MEGAUtil::a32_to_str( $key ), $pkey );
				}
			}

			return $pkey;
		}

		public static function encrypt_aes_cbc( $key, $data ) {
			$library = self::crypt_library();
			if ( 1 === $library ) {
				$data = str_pad( $data, 16 * ceil( strlen( $data ) / 16 ), "\0" ); // OpenSSL needs this padded.

				return openssl_encrypt(
					$data,
					'aes-128-cbc',
					$key,
					OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
					"\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0"
				);
			}
			if ( 2 === $library ) {
				$iv = str_repeat( "\0", mcrypt_get_iv_size( MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC ) );

				return mcrypt_encrypt( MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv );
			}

			return '';
		}

		// AES encrypt in CBC mode (zero IV)

		public static function crypt_library() {
			if ( function_exists( 'openssl_get_cipher_methods' ) ) {
				$ossl_ciphers = openssl_get_cipher_methods();
				if (
					version_compare( PHP_VERSION, '5.4.0', '>=' ) &&
					extension_loaded( 'openssl' ) &&
					in_array( 'aes-128-cbc', $ossl_ciphers, true )
				) {
					return 1;
				}
			}

			if (
				! extension_loaded( 'mcrypt' ) ||
				! in_array( 'rijndael-128', mcrypt_list_algorithms(), true )
			) {
				//No OpenSSL / Mcrypt
				return 0;
			}

			return 2;
		}

		// AES decrypt in CBC mode (zero IV)

		public static function stringhash( $s, $aeskey ) {
			$s32   = CUTEMI_MEGAUtil::str_to_a32( $s );
			$h32   = array( 0, 0, 0, 0 );
			$count = count( $s32 );
			for ( $i = 0; $i < $count; $i ++ ) {
				$h32[ $i & 3 ] ^= $s32[ $i ];
			}

			$h32 = CUTEMI_MEGAUtil::a32_to_str( $h32 );
			for ( $i = 16384; $i --; ) {
				$h32 = self::encrypt_aes_cbc( $aeskey, $h32 );
			}

			$h32 = CUTEMI_MEGAUtil::str_to_a32( $h32 );

			return CUTEMI_MEGAUtil::a32_to_base64( array( $h32[0], $h32[2] ) );
		}

		public static function encrypt_key( $key, $a ) {
			$count = count( $a );
			if ( 4 === $count ) {
				return self::encrypt_aes_cbc_a32( $key, $a );
			}
			$x = array();
			for ( $i = 0; $i < $count; $i += 4 ) {
				$x[] = self::encrypt_aes_cbc_a32( $key, array( $a[ $i ], $a[ $i + 1 ], $a[ $i + 2 ], $a[ $i + 3 ] ) );
			}

			return $x;
		}

		// AES encrypt in CBC mode (zero IV)

		public static function encrypt_aes_cbc_a32( $key, $a ) {
			return CUTEMI_MEGAUtil::str_to_a32( self::encrypt_aes_cbc( $key, CUTEMI_MEGAUtil::a32_to_str( $a ) ) );
		}

		// AES decrypt in CBC mode (zero IV)

		/**
		 * decrypt 4- or 8-element 32-bit integer array
		 *
		 * @param string $key
		 * @param array $a
		 *
		 * @return array
		 */
		public static function decrypt_key( $key, $a ) {
			$count = count( $a );
			if ( 4 === $count ) {
				return self::decrypt_aes_cbc_a32( $key, $a );
			}
			$x = array();
			for ( $i = 0; $i < $count; $i += 4 ) {
				$y = self::decrypt_aes_cbc_a32( $key, array( $a[ $i ], $a[ $i + 1 ], $a[ $i + 2 ], $a[ $i + 3 ] ) );
				$x = array_merge( $x, $y );
			}

			return $x;
		}

		// encrypt 4- or 8-element 32-bit integer array

		public static function decrypt_aes_cbc_a32( $key, $a ) {
			return CUTEMI_MEGAUtil::str_to_a32( self::decrypt_aes_cbc( $key, CUTEMI_MEGAUtil::a32_to_str( $a ) ) );
		}

		public static function decrypt_aes_cbc( $key, $data ) {
			$library = self::crypt_library();
			if ( 1 === $library ) {
				$iv = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";

				return openssl_decrypt(
					$data,
					'AES-128-CBC',
					$key,
					OPENSSL_NO_PADDING,
					$iv
				);
			}
			if ( 2 === $library ) {
				$iv = str_repeat( "\0", mcrypt_get_iv_size( MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC ) );

				return mcrypt_decrypt( MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv );
			}

			return '';
		}

		// generate attributes block using AES-CBC with MEGA canary
		// attr = Object, key = [] (four-word random key will be generated) or Array(8) (lower four words will be used)
		// returns [ArrayBuffer data,Array key]

		public static function enc_attr( $attr, $key ) {
		}

		// decrypt attributes block using AES-CBC, check for MEGA canary
		// attr = ab, key as with enc_attr
		// returns [Object] or false
		public static function dec_attr( $attr, $key ) {
			if ( count( $key ) !== 4 ) {
				$key = array( $key[0] ^ $key[4], $key[1] ^ $key[5], $key[2] ^ $key[6], $key[3] ^ $key[7] );
			}
			$key = CUTEMI_MEGAUtil::a32_to_str( $key );

			$attr = self::decrypt_aes_cbc( $key, $attr );
			$attr = CUTEMI_MEGAUtil::str_depad( $attr );

			if ( substr( $attr, 0, 6 ) !== 'MEGA{"' ) {
				return false;
			}

			$attr = json_decode( CUTEMI_MEGAUtil::from8( substr( $attr, 4 ) ), true );
			if ( is_null( $attr ) ) {
				$attr      = new stdClass();
				$attr['n'] = 'MALFORMED_ATTRIBUTES';
			}

			return $attr;
		}
	}

}
