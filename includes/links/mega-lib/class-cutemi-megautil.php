<?php
/** @noinspection PhpUnused */

if ( ! class_exists( 'CUTEMI_MEGAUtil' ) ) {

	/**
	 * PHP port of MEGA Javascript util functions.
	 */
	class CUTEMI_MEGAUtil {

		// unsubstitute standard base64 special characters, restore padding.
		public static function a32_to_base64( $a ) {
			return self::base64urlencode( self::a32_to_str( $a ) );
		}

		// substitute standard base64 special characters to prevent JSON escaping, remove padding

		public static function base64urlencode( $data ) {
			$data = base64_encode( $data );

			return str_replace( array( '+', '/', '=' ), array( '-', '_', '' ), $data );
		}

		// array of 32-bit words to string (big endian)
		public static function a32_to_str( $a ) {
			return call_user_func_array( 'pack', array_merge( array( 'N*' ), $a ) );
		}

		public static function base64_to_a32( $s ) {
			return self::str_to_a32( self::base64urldecode( $s ) );
		}

		// string to array of 32-bit words (big endian)

		public static function str_to_a32( $b ) {
			$padding = ( ( ( strlen( $b ) + 3 ) >> 2 ) * 4 ) - strlen( $b );
			if ( $padding > 0 ) {
				$b .= str_repeat( "\0", $padding );
			}

			return array_values( unpack( 'N*', $b ) );
		}

		public static function base64urldecode( $data ) {
			$data .= substr( '==', ( 2 - strlen( $data ) * 3 ) & 3 );
			$data  = str_replace( array( '-', '_', ',' ), array( '+', '/', '' ), $data );

			return base64_decode( $data );
		}

		// string to binary string (ab_to_base64)

		public static function str_to_base64( $ab ) {
			return self::base64urlencode( $ab );
		}

		// binary string to string, 0-padded to AES block size (base64_to_ab)
		public static function base64_to_str( $a ) {
			return self::str_pad( self::base64urldecode( $a ) );
		}

		// binary string depadding (ab_to_str_depad)

		public static function str_pad( $b ) {
			$padding = 16 - ( ( strlen( $b ) - 1 ) & 15 );

			return $b . str_repeat( "\0", $padding - 1 );
		}

		// binary string 0-padded to AES block size (str_to_ab)
		public static function str_depad( $b ) {
			$i = strlen( $b );
			$v = hexdec( bin2hex( $b[ $i - 1 ] ) );
			while ( ! $v ) {
				$i--;
				$v = hexdec( bin2hex( $b[ $i ] ) );
			}
			$b = substr( $b, 0, $i + 1 );

			return $b;
		}

		public static function mpi2b( $s ) {
			$s   = bin2hex( substr( $s, 2 ) );
			$len = strlen( $s );
			$n   = 0;
			for ( $i = 0; $i < $len; $i ++ ) {
				$n = bcadd( $n, bcmul( hexdec( $s[ $i ] ), bcpow( 16, $len - $i - 1 ) ) );
			}

			return $n;
		}

		public static function to8( $unicode ) {
			return $unicode;
		}

		public static function from8( $utf8 ) {
			return $utf8;
		}
	}
}
