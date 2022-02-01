<?php
/** @noinspection PhpUnused */

/**
 * Class CUTEMI_Color
 *
 * Color class
 */
class CUTEMI_Color {
	public $r;
	public $g;
	public $b;

	public function __construct( $r, $g, $b ) {
		$this->set( $r, $g, $b );
	}

	public function set( $r, $g, $b ) {
		$this->r = $this->clamp( $r );
		$this->g = $this->clamp( $g );
		$this->b = $this->clamp( $b );
	}

	public function clamp( $value ) {
		if ( $value > 255 ) {
			$value = 255;
		} elseif ( $value < 0 ) {
			$value = 0;
		}

		return $value;
	}

	public static function hsl_2_rgb( $h, $s, $l ) {
		if ( 0 === $s ) {
			$r = $l;
			$g = $l;
			$b = $l; // achromatic
		} else {
			$q = $l < 0.5 ? $l * ( 1 + $s ) : $l + $s - $l * $s;
			$p = 2 * $l - $q;
			$r = self::hue_2_rgb( $p, $q, $h + 1 / 3 );
			$g = self::hue_2_rgb( $p, $q, $h );
			$b = self::hue_2_rgb( $p, $q, $h - 1 / 3 );
		}

		return array(
			'r' => round( $r * 255 ),
			'g' => round( $g * 255 ),
			'b' => round( $b * 255 ),
		);
	}

	private static function hue_2_rgb( $p, $q, $t ) {
		if ( $t < 0 ) {
			++$t;
		}
		if ( $t > 1 ) {
			--$t;
		}
		if ( $t < 1 / 6 ) {
			return $p + ( $q - $p ) * 6 * $t;
		}
		if ( $t < 1 / 2 ) {
			return $q;
		}
		if ( $t < 2 / 3 ) {
			return $p + ( $q - $p ) * ( 2 / 3 - $t ) * 6;
		}

		return $p;
	}

	public static function hex_2_rgb( $color ) {
		list( $r, $g, $b ) = array_map(
			function ( $c ) {
				return hexdec( str_pad( $c, 2, $c ) );
			},
			str_split( ltrim( $color, '#' ), strlen( $color ) > 4 ? 2 : 1 )
		);

		return array(
			'r' => $r,
			'g' => $g,
			'b' => $b,
		);
	}

	public function to_string() {
		return 'rgb(' . round( $this->r ) . ',' . round( $this->g ) . ',' . round( $this->b ) . ')';
	}

	public function hue_rotate( $angle = 0 ) {
		$angle = $angle / 180 * pi();
		$sin   = sin( $angle );
		$cos   = cos( $angle );

		$this->multiply(
			array(
				0.213 + $cos * 0.787 - $sin * 0.213,
				0.715 - $cos * 0.715 - $sin * 0.715,
				0.072 - $cos * 0.072 + $sin * 0.928,
				0.213 - $cos * 0.213 + $sin * 0.143,
				0.715 + $cos * 0.285 + $sin * 0.140,
				0.072 - $cos * 0.072 - $sin * 0.283,
				0.213 - $cos * 0.213 - $sin * 0.787,
				0.715 - $cos * 0.715 + $sin * 0.715,
				0.072 + $cos * 0.928 + $sin * 0.072,
			)
		);
	}

	public function multiply( $matrix ) {
		$new_r   = $this->clamp( $this->r * $matrix[0] + $this->g * $matrix[1] + $this->b * $matrix[2] );
		$new_g   = $this->clamp( $this->r * $matrix[3] + $this->g * $matrix[4] + $this->b * $matrix[5] );
		$new_b   = $this->clamp( $this->r * $matrix[6] + $this->g * $matrix[7] + $this->b * $matrix[8] );
		$this->r = $new_r;
		$this->g = $new_g;
		$this->b = $new_b;
	}

	public function grayscale( $value = 1 ) {
		$this->multiply(
			array(
				0.2126 + 0.7874 * ( 1 - $value ),
				0.7152 - 0.7152 * ( 1 - $value ),
				0.0722 - 0.0722 * ( 1 - $value ),
				0.2126 - 0.2126 * ( 1 - $value ),
				0.7152 + 0.2848 * ( 1 - $value ),
				0.0722 - 0.0722 * ( 1 - $value ),
				0.2126 - 0.2126 * ( 1 - $value ),
				0.7152 - 0.7152 * ( 1 - $value ),
				0.0722 + 0.9278 * ( 1 - $value ),
			)
		);
	}

	public function sepia( $value = 1 ) {
		$this->multiply(
			array(
				0.393 + 0.607 * ( 1 - $value ),
				0.769 - 0.769 * ( 1 - $value ),
				0.189 - 0.189 * ( 1 - $value ),
				0.349 - 0.349 * ( 1 - $value ),
				0.686 + 0.314 * ( 1 - $value ),
				0.168 - 0.168 * ( 1 - $value ),
				0.272 - 0.272 * ( 1 - $value ),
				0.534 - 0.534 * ( 1 - $value ),
				0.131 + 0.869 * ( 1 - $value ),
			)
		);
	}

	public function saturate( $value = 1 ) {
		$this->multiply(
			array(
				0.213 + 0.787 * $value,
				0.715 - 0.715 * $value,
				0.072 - 0.072 * $value,
				0.213 - 0.213 * $value,
				0.715 + 0.285 * $value,
				0.072 - 0.072 * $value,
				0.213 - 0.213 * $value,
				0.715 - 0.715 * $value,
				0.072 + 0.928 * $value,
			)
		);
	}

	public function brightness( $value = 1 ) {
		$this->linear( $value );
	}

	public function linear( $slope = 1, $intercept = 0 ) {
		$this->r = $this->clamp( $this->r * $slope + $intercept * 255 );
		$this->g = $this->clamp( $this->g * $slope + $intercept * 255 );
		$this->b = $this->clamp( $this->b * $slope + $intercept * 255 );
	}

	public function contrast( $value = 1 ) {
		$this->linear( $value, - ( 0.5 * $value ) + 0.5 );
	}

	public function invert( $value = 1 ) {
		$this->r = $this->clamp( ( $value + $this->r / 255 * ( 1 - 2 * $value ) ) * 255 );
		$this->g = $this->clamp( ( $value + $this->g / 255 * ( 1 - 2 * $value ) ) * 255 );
		$this->b = $this->clamp( ( $value + $this->b / 255 * ( 1 - 2 * $value ) ) * 255 );
	}

	public function hsl() {
		$r   = $this->r / 255;
		$g   = $this->g / 255;
		$b   = $this->b / 255;
		$max = max( $r, $g, $b );
		$min = min( $r, $g, $b );
		$h   = 0;
		$l   = ( $max + $min ) / 2;

		if ( $max === $min ) {
			$s = 0;
		} else {
			$d = $max - $min;
			$s = $l > 0.5 ? $d / ( 2 - $max - $min ) : $d / ( $max + $min );
			switch ( $max ) {
				case $r:
					$h = ( $g - $b ) / $d + ( $g < $b ? 6 : 0 );
					break;

				case $g:
					$h = ( $b - $r ) / $d + 2;
					break;

				case $b:
					$h = ( $r - $g ) / $d + 4;
					break;
			}
			$h /= 6;
		}

		return array(
			'h' => $h * 100,
			's' => $s * 100,
			'l' => $l * 100,
		);
	}
}
