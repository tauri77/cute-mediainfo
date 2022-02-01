<?php

function cutemi_human_filesize( $bytes, $decimals = 1 ) {
	$size   = array( 'B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' );
	$factor = floor( ( strlen( $bytes ) - 1 ) / 3 );
	$unit   = isset( $size[ $factor ] ) ? $size[ $factor ] : '';
	return sprintf( "%.{$decimals}f", $bytes / pow( 1024, $factor ) ) . $unit;
}

function cutemi_human_duration_simple( $input_seconds ) {
	$minutes = floor( $input_seconds / 60 );
	if ( $minutes < 1 ) {
		$seconds = ceil( $input_seconds % 60 );
		/* translators: digit = seconds */
		$ret = _x( '%dsec', 'cutemi-simple-duration', 'cute-mediainfo' );

		return sprintf( $ret, $seconds );
	}
	/* translators: digit = minutes */
	$ret = _x( '%dmin', 'cutemi-simple-duration', 'cute-mediainfo' );

	return sprintf( $ret, $minutes );
}

/**
 * Aproximate value on pixels
 *
 * @param float $value
 * @param string $unit
 * @param int $width
 *
 * @return float
 */
function cutemi_get_px_equivalent( $value, $unit, $width = 1280 ) {
	$r_value = $value;
	if ( 'em' === $unit ) {
		$r_value = $r_value * 14;
	} elseif ( 'rem' === $unit ) {
		$r_value = $r_value * 14;
	} elseif ( 'vh' === $unit ) {
		$r_value = $r_value * 720 / 100;
	} elseif ( 'vw' === $unit ) {
		$r_value = $r_value * $width / 100;
	} elseif ( 'pt' === $unit ) {
		$r_value = $r_value * 1.328;
	}

	return apply_filters( 'cutemi_get_px_equivalent', $r_value, $value, $unit );
}

function cutemi_human_duration_h_m_s( $input_seconds, $suffixed = false, $limit = 3, $pad = true, $left_zero = false ) {
	$seconds_in_a_minute = 60;
	$seconds_in_an_hour  = 60 * $seconds_in_a_minute;

	// Extract hours
	$hours = floor( $input_seconds / $seconds_in_an_hour );

	// Extract minutes
	$minute_seconds = $input_seconds % $seconds_in_an_hour;
	$minutes        = floor( $minute_seconds / $seconds_in_a_minute );

	// Extract the remaining seconds
	$remaining_seconds = $minute_seconds % $seconds_in_a_minute;
	$seconds           = ceil( $remaining_seconds );

	// Format and return
	$time_parts = array();
	$sections   = array(
		'h'   => (int) $hours,
		'min' => (int) $minutes,
		's'   => (int) $seconds,
	);

	$label       = array(
		/* translators: digit = hours */
		'h'   => __( '%dh', 'cute-mediainfo' ),
		/* translators: digit = minutes */
		'min' => __( '%dmin', 'cute-mediainfo' ),
		/* translators: digit = seconds */
		's'   => __( '%ds', 'cute-mediainfo' ),
	);
	$terms_count = 0;
	foreach ( $sections as $name => $value ) {
		if ( $left_zero || $value > 0 || $terms_count > 0 ) {
			$terms_count ++;
			if ( true === $pad || 1 === $pad ) {
				$value = str_pad( $value, 2, '0', STR_PAD_LEFT );
			}
			if ( is_int( $pad ) ) {
				$pad --;
			}
			if ( $suffixed ) {
				$time_parts[] = sprintf( $label[ $name ], $value );
			} else {
				$time_parts[] = $value;
			}
		}
		if ( $terms_count === $limit ) {
			break;
		}
	}

	return implode( ':', $time_parts );
}

function cutemi_human_duration( $input_seconds ) {
	$format = get_option( 'cutemi_duration_format', 'simple' );
	if ( 'simple' === $format ) {
		return cutemi_human_duration_simple( $input_seconds );
	} elseif ( 'h_mm' === $format ) {
		return cutemi_human_duration_h_m_s( $input_seconds, false, 2, 2 );
	} elseif ( 'hh_mm_ss' === $format ) {
		return cutemi_human_duration_h_m_s( $input_seconds, false );
	} elseif ( 'fhh_mm_ss' === $format ) {
		return cutemi_human_duration_h_m_s( $input_seconds, false, 3, true, true );
	} elseif ( 'hh_mm' === $format ) {
		return cutemi_human_duration_h_m_s( $input_seconds, false, 2 );
	} elseif ( 'hh_mm_suffixed' === $format ) {
		return cutemi_human_duration_h_m_s( $input_seconds, true, 2 );
	}

	return cutemi_human_duration_h_m_s( $input_seconds, true );
}

/**
 * Convierte string a float ignorando lo demas. Ej '1.645,369€' o '1,645,645.43€'
 *
 * @param string $num
 *
 * @return float       The float floatval
 */
function cutemi_str_to_float( $num ) {
	$dot_pos   = strrpos( $num, '.' );
	$comma_pos = strrpos( $num, ',' );
	$sep       = ( ( $dot_pos > $comma_pos ) && $dot_pos ) ? $dot_pos :
		( ( ( $comma_pos > $dot_pos ) && $comma_pos ) ? $comma_pos : false );
	if ( false !== $sep && $sep === $comma_pos ) {
		if ( strpos( $num, ',' ) !== $sep ) { // '1,999,369€'
			$sep = false;
		}
	} elseif ( false !== $sep && $sep === $dot_pos ) {
		if ( strpos( $num, '.' ) !== $sep ) { // '1.999.369€'
			$sep = false;
		}
	}

	if ( ! $sep ) {
		return floatval( preg_replace( '/[^0-9]/', '', $num ) );
	}

	return floatval(
		preg_replace( '/[^0-9]/', '', substr( $num, 0, $sep ) ) . '.' .
		preg_replace( '/[^0-9]/', '', substr( $num, $sep + 1, strlen( $num ) ) )
	);
}


/*
 * overwrite properties
 */
function cutemi_over_write_match( $defaults, $rw ) {
	if ( is_scalar( $rw ) ) {
		return $rw;
	}
	//overwrite props
	foreach ( $rw as $key => $value ) {
		if (
			! isset( $defaults[ $key ] ) ||
			is_scalar( $rw[ $key ] )
		) {
			$defaults[ $key ] = $value;
		} elseif ( is_array( $value ) ) {
			foreach ( $value as $k => $v ) {
				if (
					! isset( $defaults[ $key ][ $k ] ) ||
					is_scalar( $rw[ $key ][ $k ] )
				) {
					$defaults[ $key ][ $k ] = $rw[ $key ][ $k ];
				} elseif ( is_array( $v ) ) {
					foreach ( $v as $k2 => $v2 ) {
						if (
							! isset( $defaults[ $key ][ $k ][ $k2 ] ) ||
							is_scalar( $rw[ $key ][ $k ][ $k2 ] )
						) {
							$defaults[ $key ][ $k ][ $k2 ] = $rw[ $key ][ $k ][ $k2 ];
						}
					}
				}
			}
		}
	}

	return $defaults;
}
