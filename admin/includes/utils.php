<?php

function cutemi_to_byte_size( $size_string, $no_binary_based = 1024 ) {
	//remove whitespaces
	$size_string = preg_replace( '/\s+/', '', $size_string );
	$float       = cutemi_str_to_float( $size_string );

	$a_units           = array(
		'B'   => 0,
		'KiB' => 1,
		'MiB' => 2,
		'GiB' => 3,
		'TiB' => 4,
		'PiB' => 5,
		'EiB' => 6,
		'ZiB' => 7,
		'YiB' => 8,
	);
	$a_units_no_binary = array(
		'KB' => 1,
		'MB' => 2,
		'GB' => 3,
		'TB' => 4,
		'PB' => 5,
		'EB' => 6,
		'ZB' => 7,
		'YB' => 8,
	);

	$s_unit = substr( $size_string, - 3 );
	if ( is_numeric( substr( $s_unit, 0, 1 ) ) ) {
		$s_unit = substr( $s_unit, 1 );
	}
	//for 434B
	if ( is_numeric( substr( $s_unit, 0, 1 ) ) ) {
		$s_unit = substr( $s_unit, 1 );
	}

	$based = 1024;
	if ( isset( $a_units[ $s_unit ] ) ) {
		$pow = $a_units[ $s_unit ];
	} elseif ( isset( $a_units_no_binary[ $s_unit ] ) ) {
		$based = $no_binary_based;
		$pow   = $a_units_no_binary[ $s_unit ];
	} else {
		return false;
	}

	return (int) ( $float * pow( $based, $pow ) );
}


function cutemi_duration_to_seconds( $time ) {
	$hr   = false;
	$min  = false;
	$sec  = false;
	$mili = false;
	if ( preg_match( '/([0-9]+)\s*h\s*([0-9]+)\s*mi?n\s*([0-9]+)\s*s\s*([0-9]+)\s*ms/i', $time, $match ) ) {
		$hr   = $match[1];
		$min  = $match[2];
		$sec  = $match[3];
		$mili = $match[4];
	} elseif ( preg_match( '/([0-9]+)\s*h\s*([0-9]+)\s*mi?n\s*([0-9]+)\s*s/i', $time, $match ) ) {
		$hr   = $match[1];
		$min  = $match[2];
		$sec  = $match[3];
		$mili = 0;
	} elseif ( preg_match( '/([0-9]+)\s*h\s*([0-9]+)\s*mi?n/i', $time, $match ) ) {
		$hr   = $match[1];
		$min  = $match[2];
		$sec  = 0;
		$mili = 0;
	} elseif ( preg_match( '/([0-9]+)\s*mi?n\s*([0-9]+)\s*s/i', $time, $match ) ) {
		$hr   = 0;
		$min  = $match[1];
		$sec  = $match[2];
		$mili = 0;
	} elseif ( preg_match( '/([0-9]+)\s*s\s*([0-9]+)\s*ms/i', $time, $match ) ) {
		$hr   = 0;
		$min  = 0;
		$sec  = $match[1];
		$mili = $match[2];
	} elseif ( preg_match( '/([0-9]+)\s*:\s*([0-9]+)\s*:\s*([0-9]+)\s*[.:]\s*([0-9]+)/i', $time, $match ) ) {
		$hr   = $match[1];
		$min  = $match[2];
		$sec  = $match[3];
		$mili = $match[4];
	}
	if ( false !== $min ) {
		return round(
			intval( $hr ) * 3600 +
			intval( $min ) * 60 +
			intval( $sec ) +
			intval( $mili ) / 1000
		);
	}

	return false;
}
