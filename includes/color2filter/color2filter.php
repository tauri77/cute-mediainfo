<?php

require_once CUTE_MEDIAINFO_DIR . '/includes/color2filter/class-cutemi-color.php';
require_once CUTE_MEDIAINFO_DIR . '/includes/color2filter/class-cutemi-color-2-filter.php';

/**
 * Transform color code to rgba array.
 *
 * @param string $color Color code
 *
 * @return array|bool rgba array ['r'=>x,'g'=>y,'b'=>z,'a'=>a], or false on error.
 */
function cutemi_rbga_color( $color ) {
	// hex color
	if ( preg_match( '/^#([a-f\d]{3}){1,2}$/i', $color ) ) {
		$color_rgb      = CUTEMI_Color::hex_2_rgb( $color );
		$color_rgb['a'] = 1;

		return $color_rgb;
	}

	// rgb
	if ( preg_match( '/rgb\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*\)/i', $color, $matches ) ) {
		$color_rgb['r'] = min( 255, max( 0, (int) $matches[1] ) );
		$color_rgb['g'] = min( 255, max( 0, (int) $matches[2] ) );
		$color_rgb['b'] = min( 255, max( 0, (int) $matches[3] ) );
		$color_rgb['a'] = 1;

		return $color_rgb;
	}

	// rgba
	if ( preg_match( '/rgba\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*([\d.]+)\s*\)/i', $color, $matches ) ) {
		$color_rgb['r'] = min( 255, max( 0, (int) $matches[1] ) );
		$color_rgb['g'] = min( 255, max( 0, (int) $matches[2] ) );
		$color_rgb['b'] = min( 255, max( 0, (int) $matches[3] ) );
		$color_rgb['a'] = min( 1, max( 0, (float) $matches[4] ) );

		return $color_rgb;
	}

	// hsl
	if ( preg_match( '/hsl\(\s*([\d.]+)\s*,\s*([\d.]+)%\s*,\s*([\d.]+)%\s*\)/i', $color, $matches ) ) {
		$h              = min( 360, max( 0, (float) $matches[1] ) );
		$s              = min( 100, max( 0, (float) $matches[2] ) );
		$l              = min( 100, max( 0, (float) $matches[3] ) );
		$color_rgb      = CUTEMI_Color::hsl_2_rgb( $h, $s, $l );
		$color_rgb['a'] = 1;

		return $color_rgb;
	}

	// hsla
	if ( preg_match( '/hsl\(\s*([\d.]+)\s*,\s*([\d.]+)%\s*,\s*([\d.]+)%\s*,\s*([\d.]+)\s*\)/i', $color, $matches ) ) {
		$h              = min( 360, max( 0, (float) $matches[1] ) );
		$s              = min( 100, max( 0, (float) $matches[2] ) );
		$l              = min( 100, max( 0, (float) $matches[3] ) );
		$color_rgb      = CUTEMI_Color::hsl_2_rgb( $h, $s, $l );
		$color_rgb['a'] = min( 1, max( 0, (float) $matches[4] ) );

		return $color_rgb;
	}

	return false;
}
