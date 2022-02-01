<?php
/** @noinspection DuplicatedCode */

/*********
 * WISE
 *******/

class CUTEMI_Wise {

	public static function wise2( $w = null, $i = null, $s = null, $e = null ) {
		$string = null;
		$len    = self::get_prop( $w, 'length' );
		for ( $s = 0.0; $s < $len; $s = self::plus( $s, 2.0 ) ) {
			$i  .= self::call_method(
				$string,
				'fromCharCode',
				intval( self::call_method( $w, 'substr', $s, 2.0 ), 36.0 )
			);
			$len = self::get_prop( $w, 'length' );
		}

		return $i;
	}

	private static function get_prop( $obj, $prop ) {
		if ( 'length' === $prop ) {
			if ( is_array( $obj ) ) {
				return count( $obj );
			} else {
				return strlen( utf8_decode( $obj ) );
			}
		}

		return '';
	}

	private static function plus() {
		$vals = func_get_args();
		$res  = 0;
		foreach ( $vals as $val ) {
			$res = $res + $val;
		}

		return $res;
	}

	private static function call_method( &$obj, $func, $param1 = null, $param2 = null ) {
		if ( 'push' === $func ) {
			$obj[] = $param1;
		}
		if ( 'charCodeAt' === $func ) {
			return self::utf8_char_code_at_vl( $obj, $param1 );
		}
		if ( 'charAt' === $func ) {
			$param1 = (int) $param1;

			return $obj{$param1};
		}
		if ( 'fromCharCode' === $func ) {
			return chr( $param1 );
		}
		if ( 'join' === $func ) {
			return implode( $param1, $obj );
		}
		if ( 'substr' === $func ) {
			return substr( $obj, $param1, $param2 );
		}

		return false;
	}

	private static function utf8_char_code_at_vl( $str, $index ) {
		$char = mb_substr( $str, $index, 1, 'UTF-8' );
		if ( mb_check_encoding( $char, 'UTF-8' ) ) {
			$ret = mb_convert_encoding( $char, 'UTF-32BE', 'UTF-8' );

			return hexdec( bin2hex( $ret ) );
		} else {
			return null;
		}
	}

	public static function wise1( $w = null, $i = null, $s = null, $e = null ) {
		$string = null;
		$l_ill  = 0.0;
		$ll1_i  = 0.0;
		$_il1l  = 0.0;
		$ll1l   = array();
		$l1l_i  = array();
		while ( true ) {
			if ( $l_ill < 5.0 ) {
				self::call_method( $l1l_i, 'push', self::call_method( $w, 'charAt', $l_ill ) );
			} elseif ( $l_ill < self::get_prop( $w, 'length' ) ) {
				self::call_method( $ll1l, 'push', self::call_method( $w, 'charAt', $l_ill ) );
			}

			++ $l_ill;
			if ( $ll1_i < 5.0 ) {
				self::call_method( $l1l_i, 'push', self::call_method( $i, 'charAt', $ll1_i ) );
			} elseif ( $ll1_i < self::get_prop( $i, 'length' ) ) {
				self::call_method( $ll1l, 'push', self::call_method( $i, 'charAt', $ll1_i ) );
			}

			++ $ll1_i;
			if ( $_il1l < 5.0 ) {
				self::call_method( $l1l_i, 'push', self::call_method( $s, 'charAt', $_il1l ) );
			} elseif ( $_il1l < self::get_prop( $s, 'length' ) ) {
				self::call_method( $ll1l, 'push', self::call_method( $s, 'charAt', $_il1l ) );
			}

			++ $_il1l;
			if ( self::eq( self::plus( self::get_prop( $w, 'length' ), self::get_prop( $i, 'length' ), self::get_prop( $s, 'length' ), self::get_prop( $e, 'length' ) ), self::plus( self::get_prop( $ll1l, 'length' ), self::get_prop( $l1l_i, 'length' ), self::get_prop( $e, 'length' ) ) ) ) {
				break;
			}
		}
		$l_i1l  = self::call_method( $ll1l, 'join', '' );
		$_i1l_i = self::call_method( $l1l_i, 'join', '' );
		$ll1_i  = 0.0;
		$l1ll   = array();
		$len    = self::get_prop( $ll1l, 'length' );
		for ( $l_ill = 0.0; $l_ill < $len; $l_ill = $l_ill + 2.0 ) {
			$ll11 = - 1.0;
			if ( self::to_number( self::call_method( $_i1l_i, 'charCodeAt', $ll1_i ) ) % 2.0 !== 0 ) {
				$ll11 = 1.0;
			}
			self::call_method( $l1ll, 'push', self::call_method( $string, 'fromCharCode', self::to_number( intval( self::call_method( $l_i1l, 'substr', $l_ill, 2.0 ), 36.0 ) ) - self::to_number( $ll11 ) ) );
			++ $ll1_i;
			if ( $ll1_i >= self::get_prop( $l1l_i, 'length' ) ) {
				$ll1_i = 0.0;
			}
			$len = self::get_prop( $ll1l, 'length' );
		}

		return self::call_method( $l1ll, 'join', '' );
	}

	private static function eq( $v1, $v2 ) {
		return $v1 === $v2;
	}

	private static function to_number( $v ) {
		return (int) $v;
	}
}

