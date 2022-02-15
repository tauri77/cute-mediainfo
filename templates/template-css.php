<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

/**
 *
 * <div>.cutemi-template .cutemi-template-{PROFILE}
 *      <div>.cutemi-group .cutemi-group-id-{GROUP_ID} .{HEAD_MODE} .cutemi-group-cols-{NRO_COLUMNS}
 *      <div>.cutemi-group .cutemi-group-id-{GROUP_ID} .{HEAD_MODE} .cutemi-group-cols-{NRO_COLUMNS}
 *      ...
 * PROFILE: The profile slug. Ex: full,summary
 * GROUP_ID: name, videos, audios, links, mediainfo..
 * HEAD_MODE:
 * TOP: cutemi-group-top-head
 * LEFT: cutemi-group-left-head
 * LEFT, TOP ON SMALL: cutemi-group-left-top-head
 * HIDDEN: cutemi-group-top-head .cutemi-group-no-head
 * NRO_COLUMNS: The number of columns on the group. (Max of all rows) Ex: 3
 ************************* Ex Group blocks ****************************
 * <div>.cutemi-template .cutemi-template-full
 *      <div>.cutemi-group .cutemi-group-id-name .cutemi-group-top-head .cutemi-group-cols-2
 *      <div>.cutemi-group .cutemi-group-id-videos .cutemi-group-left-top-head .cutemi-group-cols-3
 *      <div>.cutemi-group .cutemi-group-id-audios .cutemi-group-left-top-head .cutemi-group-cols-5
 *      <div>.cutemi-group .cutemi-group-id-links .cutemi-group-left-top-head .cutemi-group-cols-3
 *************************** row and cell *********************************
 * <div>.cutemi-template .cutemi-template-{PROFILE}
 *      <div>.cutemi-group .cutemi-group-id-{GROUP_ID} .{HEAD_MODE} .cutemi-group-cols-{NRO_COLUMNS}
 *          <div>.cutemi-group-head cutemi-group-count-{NRO_ROWS}           |
 *              <img|svg>                                                   | HEAD
 *             <span>                                                      |
 *      <ul>.cutemi-list .cutemi-col-count-sub .cutemi-group-count-{NRO_ROWS}
 *          <li>.cutemi-row                                                              ¬
 *              <ul>.cutemi-list .cutemi-row-cols-{NRO_COLUMNS}                          |
 *                  <li>.cutemi-cell .cutemi-cell-{ID_FIELD}         ¬                   |
 *                      <img>                                        | Cell              |
 *                      <span>                                      _|                   | Row
 *                  <li>.cutemi-cell .cutemi-img-txt .cutemi-cell-{ID_FIELD} ¬           |
 *                      <span>                                               | Cell      |
 *                      <sup>                                                |           |
 *                      <b>                                                 _|          _|
 *  <ul>.cutemi-list .cutemi-row-cols-{NRO_COLUMNS}
 *  cutemi-cell + [.cutemi-img-txt|.cutemi-inline-img-txt|.cutemi-img-txt .cutemi-inline-flex|.cutemi-cell-multiline|]
 *  Cell content img+span,img,span. Top labeled span content sup+b: <span><sup></sup><b></b></span>
 * EX:
 * <div>.cutemi-template .cutemi-template-{PROFILE}
 *      <div>.cutemi-group .cutemi-group-id-{GROUP_ID} .{HEAD_MODE} .cutemi-group-cols-{NRO_COLUMNS}
 *          <div>.cutemi-group-head cutemi-group-count-1
 *              <svg>
 *              <span>
 *      <ul>.cutemi-list .cutemi-col-count-sub .cutemi-group-count-2
 *          <li>.cutemi-row
 *              <ul>.cutemi-list .cutemi-row-cols-2
 *                  <li>.cutemi-cell .cutemi-cell-format
 *                      <img>
 *                      <span>
 *                  <li>.cutemi-cell .cutemi-img-txt .cutemi-cell-size
 *                      <span>
 *                      <sup>
 *                      <b>
 *              .....
 *          <li>.cutemi-row
 *              <ul>.cutemi-list .cutemi-row-cols-1
 *                  <li>.cutemi-cell .cutemi-cell-multiline .cutemi-cell-desc
 *                      <span>
 */

if ( ! function_exists( 'cutemi_template_table_style_customized' ) ) {

	/**
	 * The css code that not depend of profile style config
	 *
	 * @return string
	 */
	function cutemi_template_table_style_generic() {
		$out = '
.cutemi-template {
    overflow: auto;
}
.cutemi-only-texting {
	display: none;
	max-height: 0;
	max-width: 0;
	overflow: hidden;
}
.cutemi-template span {
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.25;
/*    max-height: 2.3em;*/
}
.cutemi-template pre {
    margin: 0;
    padding: 1em;
    white-space: normal;
    font-size: 0.7em;
    border: none;
}
.cutemi-template li.cutemi-cell.cutemi-cell-multiline {
	height: inherit;
	max-height: none;
}
.cutemi-template li.cutemi-cell.cutemi-cell-multiline span {
	height: inherit;
	max-height: none;
	line-height: normal;
}
.cutemi-template .cutemi-cell.cutemi-cell-mediainfo {
	padding: 0;
}
.cutemi-template pre {
    font-family: monospace;
    white-space: pre-wrap;
    text-align: left;
    background: transparent;
}
.cutemi-template ul{
    list-style: none;
    margin: 0;
    overflow: hidden;
    padding: 0;
}
.cutemi-group {
	overflow: hidden;
	position: relative;
}
.cutemi-template .cutemi-row:last-child {
    border-bottom: none;
}
.cutemi-template li.cutemi-cell:last-child {
    border-right: 0;
}
.cutemi-template .cutemi-group ul {
    margin-top: 0;
    margin-bottom: 0;
    padding: 0;
}
.cutemi-template .cutemi-col-count-sub {
    border-bottom: none;
}
.cutemi-template .cutemi-group li {
    position: relative;
    margin: 0;
    display: inline-block;
    float: left;
    text-align: center;
    box-sizing: border-box;
    
    display: flex;
    flex-direction: column;
    align-content: center;
    justify-content: space-evenly;
}
.cutemi-template li:first-child {
    border-left: none;
}
.cutemi-template li:last-child {
    border-right: none;
}

.cutemi-template .cutemi-col-count-sub > li {
    width: 100%;
    padding: 0;
    border-right: none;
}

.cutemi-template li sup {
    position: absolute;
    right: 0;
    opacity: 0.5;
    line-height: 1.15em;
    text-align: center;
    width: 100%;
}
.cutemi-template li sup + b {
	line-height: 1.05em;
    padding-top: 0.65em;
    display: inline-block;
    font-weight: 300;
    width: 100%;
    text-align: center;
    font-size: 83%;
}
.cutemi-template .cutemi-row-cols-5 > li {
    width: 20%;
}
.cutemi-template .cutemi-row-cols-4 > li {
    width: 25%;
}
.cutemi-template .cutemi-row-cols-3 > li {
    width: 33.33333%;
}
.cutemi-template .cutemi-row-cols-2 > li {
    width: 50%;
}
.cutemi-template .cutemi-row-cols-1 > li {
    width: 100%;
}
.cutemi-template img,
.cutemi-template svg {
    max-width: 90%;
    display: block;
    margin: auto;
}
.cutemi-template .cutemi-img-txt {
	display: flex;
    flex-direction: column;
    align-items: center;
}
.cutemi-template .cutemi-img-txt span {
	display: block;
	white-space: nowrap;
    width: 100%;
}
.cutemi-template li.cutemi-inline-flex {
	padding-left: 0.25em;
    padding-right: 0.25em;
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    align-content: center;
    flex-wrap: wrap;
}
.cutemi-template li.cutemi-inline-flex img,
.cutemi-template li.cutemi-inline-flex svg {
	margin: 0;
}
.cutemi-template li.cutemi-inline-flex span {
	width: auto;
	margin: 0.1em 0.5em;
}
.cutemi-template li span {
    display: inline-block;
    vertical-align: top;
}
.cutemi-template .cutemi-group .cutemi-inline-img-txt {
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
    
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    align-content: center;
    flex-wrap: nowrap;
}
.cutemi-template .cutemi-inline-img-txt img,
.cutemi-template .cutemi-inline-img-txt svg {
    max-height: 100%;
    margin: 0;
    display: inline;
    
    height: auto;
    max-width: 40%;
}
.cutemi-template li.cutemi-inline-img-txt span {
    text-align: left;
    margin: 0 0 0 0.4em; 
}
.cutemi-group-head {
    display: inline-block;
    float: left;
    text-align: center;
    overflow: hidden;
    position: absolute;
    height: 100%;
}
.cutemi-group-head span {
	white-space: normal;
    line-break: anywhere;
}
.cutemi-group-head.cutemi-v-center {
    display: flex;
    flex-direction: column;
    justify-content: space-evenly;
}
.cutemi-group.cutemi-group-top-head .cutemi-group-head {
	height: auto;
	min-height: auto;
	padding: 0.4em;
	width: 100%;
    max-width: none;
}
.cutemi-group-top-head.cutemi-group > ul {
	width: 100%;
}
.cutemi-col-count-sub ul.cutemi-list {
    margin: 0;
} 
.cutemi-group.cutemi-group-top-head ul.cutemi-list{
	margin-left: 0;
}
.cutemi-group-top-head .cutemi-group-head {
	position: relative;
}
.cutemi-group-no-head .cutemi-group-head {
	display: none;
}
.cutemi-group-left-top-head .cutemi-group-head {
    display: flex;
    align-items: center;
    justify-content: center;
}
.cutemi-group-left-top-head.cutemi-group-top-head .cutemi-group-head {
    flex-direction: row;
}
.cutemi-group-left-top-head.cutemi-group-top-head .cutemi-group-head span {
    width: auto;
}
.cutemi-template .cutemi-group-head img,
.cutemi-template .cutemi-group-head svg {
	display: inline;
    vertical-align: middle;
    margin: 0;
    margin-right: 0.3em;
    margin-left: 0.3em;
}
.cutemi-group-top-head .cutemi-group-head img,
.cutemi-group-top-head .cutemi-group-head svg {
	display: inline-block;
    vertical-align: middle;
    width: auto;
}
.cutemi-group-top-head .cutemi-group-head span {
    vertical-align: middle;
    display: inline;
    padding: 0 0.2em;
    word-break: break-word;
}
.cutemi-btn {
	width: 84%;
    width: fit-content;
    padding: 0.1em 0;
    overflow: hidden;
    margin: auto;
    white-space: nowrap;
    text-overflow: ellipsis;
    display: block;
    text-decoration: none !important;
}
.cutemi-template .cutemi-btn:hover,
.cutemi-template .cutemi-btn:focus,
.cutemi-template .cutemi-btn:active {
    filter: brightness(1.2);
    text-decoration: none;
    opacity: 0.9;
}

.cutemi-template .cutemi-btn img,
.cutemi-template .cutemi-btn svg {
	display: inline-block;
    vertical-align: middle;
    margin-right: 0.15em;
    margin-left: 0.15em;
}
.cutemi-template .cutemi-btn img+span,
.cutemi-template .cutemi-btn svg+span {
    margin-right: 0.15em;
    margin-left: 0.15em;
    display: inline;
}
.cutemi-template .cutemi-btn span {
    vertical-align: middle;
}
.cutemi-full-div {
    position: fixed;
    top: 0;
    bottom: 0;
    right: 0;
    left: 0;
    opacity: 0;
    display: none;
    cursor: pointer;
    text-align: center;
    overflow: auto;
    z-index: 99999;
    background-color: rgba(0,0,0,0.8);
    transition: all 0.3s;
}
.cutemi-full-div.cutemi-active {
	opacity: 1;
    display: block;
}
.cutemi-full-div-loading:before {
    content: \'\';
    box-sizing: border-box;
    position: absolute;
    top: 50%;
    left: 50%;
    width: 30px;
    height: 30px;
    margin-top: -15px;
    margin-left: -15px;
    border-radius: 50%;
    border-top: 2px solid #fff;
    border-right: 2px solid transparent;
    animation: cutemi-spinner .6s linear infinite;
}
.cutemi-full-div-error:before {
    content: \'!\';
    color: #ff1100;
    font-size: 22px;
    font-weight: bold;
    box-sizing: content-box;
    vertical-align: middle;
    text-align: center;
    position: absolute;
    top: 50%;
    left: 50%;
    width: 30px;
    height: 30px;
    margin-top: -15px;
    margin-left: -15px;
    border-radius: 50%;
    border: 4px solid #ff1100;
}
@keyframes cutemi-spinner {
    to {transform: rotate(360deg);}
}
.cutemi-template li.cutemi-cell.cutemi-cell-external_id {
    padding: 0;
}
li.cutemi-cell-external_id span{
	height: 100%;
    width: 100%;
    display: block;
    position: relative;
}
li.cutemi-cell-external_id span .cutemi-btn{
    min-width: auto;
}
li.cutemi-inline-img-txt.cutemi-cell.cutemi-cell-source {
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    justify-content: flex-start;
}
li.cutemi-inline-img-txt.cutemi-cell.cutemi-cell-source img,
li.cutemi-inline-img-txt.cutemi-cell.cutemi-cell-source svg {
    padding-right: 3%;
    padding-left: 3%;
}

@media (max-width:500px) {
	.cutemi-group.cutemi-group-left-top-head .cutemi-group-head {
		position: relative;
		flex-direction: row;
		height: auto;
		min-height: auto;
		padding: 0.4em;
		width: 100%;
		max-width: none;
	}
	.cutemi-group-left-top-head .cutemi-group-head span {
		width: auto;
	}
	.cutemi-group-left-top-head .cutemi-group-head img,
	.cutemi-group-left-top-head .cutemi-group-head svg {
		display: inline-block;
		vertical-align: middle;
		width: auto;
	}
	.cutemi-group-left-top-head .cutemi-group-head span {
		vertical-align: middle;
		display: inline;
		padding: 0 0.2em;
		word-break: break-word;
	}
	.cutemi-group.cutemi-group-left-top-head.cutemi-group > ul {
		width: 100%;
	}
	.cutemi-group.cutemi-group-left-top-head ul.cutemi-list{
		margin-left: 0;
	}
}
';

		return apply_filters( 'cutemi_table_generic_style', $out );
	}

	/**
	 * The css code that depends of profile style config
	 *
	 * @param $profile
	 * @param $style_cfg
	 *
	 * @return string
	 */
	function cutemi_template_table_style_customized( $profile, $style_cfg ) {

		$class_profile = '.cutemi-template-' . sanitize_title( $profile );

		$numbers_configs = array(
			'font_size',
			'external_margin',
			'outer_border_width',
			'outer_blocks_padding',
			'outer_border_radius',
			'internal_border_radius',
			'min_width',
			'max_width',
			'multiline_max_height',
			'blocks_border_width',
			'blocks_border_radius',
			'blocks_headers_side_width',
			'blocks_spacing',
			'row_border',
			'row_height',
			'cell_padding',
			'cell_border',
			'button_height_percentage',
			'button_width_percentage',
			'button_border_width',
			'button_border_radius',
			'mediainfo_font_size',
		);

		$colors_configs = array(
			'outer_border_color',
			'background_color',
			'data_color',
			'blocks_border_color',
			'blocks_background_color',
			'blocks_headers_color',
			'blocks_headers_font_color',
			'row_border_color',
			'cell_border_color',
			'button_background_color',
			'button_border_color',
			'button_font_color',
		);

		$font_stretch_valid = array(
			'ultra-condensed',
			'extra-condensed',
			'condensed',
			'semi-condensed',
			'normal',
			'semi-expanded',
			'expanded',
			'extra-expanded',
			'ultra-expanded',
			'initial',
			'inherit',
		);

		$units_valid = array( 'px', 'em', 'rem', 'pt', 'vw', 'vh' );
		/**
		 * Make sure all variables are correct for safe output
		 */

		/**
		 * Make sure the numbers are numbers.
		 */
		foreach ( $numbers_configs as $n ) {
			$safe_cfg[ $n ] = floatval( $style_cfg[ $n ] );
		}
		/**
		 * Make sure the colors are colors
		 */
		foreach ( $colors_configs as $n ) {
			if ( empty( $style_cfg[ $n ] ) ) {
				$safe_cfg[ $n ] = 'transparent';
			} else {
				$safe_cfg[ $n ] = cutemi_check_color( $style_cfg[ $n ] );
				if ( null === $safe_cfg[ $n ] ) {
					$safe_cfg[ $n ] = 'transparent';
				}
			}
		}
		/**
		 * Make sure the unit is a valid unit
		 */
		$safe_cfg['unit'] = 'px';
		if ( ! in_array( $style_cfg['unit'], $units_valid, true ) ) {
			$safe_cfg['unit'] = $style_cfg['unit'];
		}
		//on-off settings
		$safe_cfg['external_margin_sides_auto'] = ( 'on' === $style_cfg['external_margin_sides_auto'] ) ? 'on' : 'off';
		//escape font family
		$safe_cfg['font_family_quoted'] = '';
		if ( is_array( $style_cfg['font_family'] ) ) {
			if ( ! empty( $style_cfg['font_family'] ) ) {
				$safe_cfg['font_family_quoted'] = '"' . implode(
					'","',
					array_map( 'esc_attr', $style_cfg['font_family'] )
				) . '"';
			}
		} else {
			$safe_cfg['font_family_quoted'] = '"' . esc_attr( $style_cfg['font_family'] ) . '"';
		}
		//mediainfo escape font family
		$safe_cfg['mediainfo_gfont_family_quoted'] = '';
		if ( ! empty( $style_cfg['mediainfo_google_font_family'] ) ) {
			$safe_cfg['mediainfo_gfont_family_quoted'] = '"' . esc_attr( $style_cfg['mediainfo_google_font_family'] ) . '"';
		}
		//valid stretch
		if (
			! empty( $style_cfg['font_stretch'] ) &&
			in_array( $style_cfg['font_stretch'], $font_stretch_valid, true )
		) {
			$safe_cfg['font_stretch'] = $style_cfg['font_stretch'];
		}
		if (
			! empty( $style_cfg['mediainfo_google_font_stretch'] ) &&
			in_array( $style_cfg['mediainfo_google_font_stretch'], $font_stretch_valid, true )
		) {
			$safe_cfg['mediainfo_google_font_stretch'] = $style_cfg['mediainfo_google_font_stretch'];
		}
		//font_weight
		if ( ! empty( $style_cfg['font_weight'] ) ) {
			$safe_cfg['font_weight'] = intval( $style_cfg['font_weight'] );
		}
		if ( ! empty( $style_cfg['mediainfo_google_font_weight'] ) ) {
			$safe_cfg['mediainfo_google_font_weight'] = intval( $style_cfg['mediainfo_google_font_weight'] );
		}

		/**
		 * Ready, all safe
		 */

		$row_height_min         = $safe_cfg['cell_padding'] * 2 + $safe_cfg['font_size'] * 1.55;
		$safe_cfg['row_height'] = max( $row_height_min, $safe_cfg['row_height'] );

		//Min dimension for selection unit
		$min_dim       = 8 / cutemi_get_px_equivalent( 1, $safe_cfg['unit'] );
		$row_available = $safe_cfg['row_height'] - 2 * $safe_cfg['cell_padding'];

		$font_sets = '';
		if ( ! empty( $safe_cfg['font_family_quoted'] ) ) {
			$font_sets = ' font-family: ' . $safe_cfg['font_family_quoted'] . ',"Helvetica Neue",Helvetica,Arial,sans-serif;';
		}
		if ( ! empty( $safe_cfg['font_weight'] ) ) {
			$font_sets .= ' font-weight: ' . $safe_cfg['font_weight'] . ';';
		}
		if ( ! empty( $safe_cfg['font_stretch'] ) ) {
			$font_sets .= ' font-stretch: ' . $safe_cfg['font_stretch'] . ';';
		}

		$font_sets_mediainfo = '';
		if ( ! empty( $safe_cfg['mediainfo_google_font_family'] ) ) {
			$font_sets_mediainfo = ' font-family: ' . $safe_cfg['mediainfo_gfont_family_quoted'] . ', monospace;';
		}
		if ( ! empty( $safe_cfg['mediainfo_google_font_weight'] ) ) {
			$font_sets_mediainfo .= ' font-weight: ' . $safe_cfg['mediainfo_google_font_weight'] . ';';
		}
		if ( ! empty( $safe_cfg['mediainfo_google_font_stretch'] ) ) {
			$font_sets_mediainfo .= ' font-stretch: ' . $safe_cfg['mediainfo_google_font_stretch'] . ';';
		}
		if ( ! empty( $safe_cfg['mediainfo_font_size'] ) ) {
			$font_sets_mediainfo .= ' font-size: ' . $safe_cfg['mediainfo_font_size'] . $safe_cfg['unit'] . ';';
		}

		$safe_cfg['font_size_bottom'] = max( $min_dim / 2, ( $row_available ) / 3 );

		$out = '';
		if ( ! empty( $font_sets_mediainfo ) ) {
			$out .= $class_profile . ' .cutemi-cell.cutemi-cell-mediainfo pre {
	' . $font_sets_mediainfo . '
}';
		}

		if ( get_option( 'cutemi_svg_head_colorized', '1' ) === '1' ) {
			//filter colorized header icon
			require_once CUTE_MEDIAINFO_DIR . '/includes/color2filter/color2filter.php';
			//Customizer can be slow without cache
			$cached_color_key = 'cutemi_css_colorize_' . $safe_cfg['blocks_headers_font_color'];
			$prop_css         = wp_cache_get( $cached_color_key, 'cutemi_table_data_config' );
			if ( ! $prop_css ) {
				$rgba = cutemi_rbga_color( $safe_cfg['blocks_headers_font_color'] );
				if ( ! empty( $rgba ) && isset( $rgba['r'] ) ) {
					$color  = new CUTEMI_Color( $rgba['r'], $rgba['g'], $rgba['b'] );
					$result = array( 'loss' => 100 );
					$iter   = 0;
					while ( $result['loss'] > 6 && $iter < 3 ) {
						$solver = new CUTEMI_Color_2_Filter( $color );
						$result = $solver->solve();
						$iter ++;
					}
					if ( $result['filter'] ) {
						$prop_css = 'filter: ' . $result['filter'] . ';
								opacity: ' . $rgba['a'] . ';';
					}
					wp_cache_add( $cached_color_key, $prop_css, 3600 );
				}
			}
			if ( ! empty( $prop_css ) ) {
				$out .= "\n" . $class_profile . ' .cutemi-group-head img {
								' . $prop_css . '
							}';
			}
		}

		$out .= '
' . $class_profile . ' {
    ' . $font_sets . '
    font-variant: ' . ( ! empty( $safe_cfg['font_variant'] ) ? $safe_cfg['font_variant'] : 'normal' ) . ';
    border: solid ' . $safe_cfg['outer_border_width'] . $safe_cfg['unit'] . ' ' . $safe_cfg['outer_border_color'] . ';
    background: ' . $safe_cfg['background_color'] . ';
    border-radius: ' . $safe_cfg['outer_border_radius'] . $safe_cfg['unit'] . ';
    max-width: ' . ( empty( $safe_cfg['max_width'] ) ? 'none' : ( $safe_cfg['max_width'] . $safe_cfg['unit'] ) ) . ';
    padding: ' . $safe_cfg['outer_blocks_padding'] . $safe_cfg['unit'] . ';
    margin: ' . $safe_cfg['external_margin'] . $safe_cfg['unit'] .
		( 'on' === $safe_cfg['external_margin_sides_auto'] ? ' auto' : '' ) . ';
}
' . $class_profile . ' span {
	' . $font_sets . '
}
' . $class_profile . ' .cutemi-group {
	overflow: hidden;
    margin-bottom: ' . ( $safe_cfg['blocks_spacing'] - $safe_cfg['blocks_border_width'] ) . $safe_cfg['unit'] . ';
    border: solid ' . $safe_cfg['blocks_border_width'] . $safe_cfg['unit'] . ' ' . $safe_cfg['blocks_border_color'] . ';
    border-radius: ' . $safe_cfg['blocks_border_radius'] . $safe_cfg['unit'] . ';
    min-width: ' . ( empty( $safe_cfg['min_width'] ) ?
				'auto' :
				( ( $safe_cfg['min_width'] - 2 * $safe_cfg['outer_border_radius'] ) . $safe_cfg['unit'] ) ) . ';
}
' . $class_profile . ' .cutemi-group:last-child {
	margin-bottom: 0;
}
' . $class_profile . ' .cutemi-group > ul {
    background: ' . $safe_cfg['blocks_background_color'] . ';
}
' . $class_profile . ' span,
' . $class_profile . ' small,
' . $class_profile . ' pre {
    font-size: ' . $safe_cfg['font_size'] . $safe_cfg['unit'] . ';
    color: ' . $safe_cfg['data_color'] . ';
}
' . $class_profile . ' svg {
    fill: ' . $safe_cfg['data_color'] . ';
}
' . $class_profile . ' .cutemi-group-head img,
' . $class_profile . ' .cutemi-group-head svg {
    height: ' . ( $safe_cfg['font_size'] * 1.3 ) . $safe_cfg['unit'] . ';
}
' . $class_profile . ' .cutemi-group:first-child {
   border-top-left-radius: ' . $safe_cfg['internal_border_radius'] . $safe_cfg['unit'] . ';
   border-top-right-radius: ' . $safe_cfg['internal_border_radius'] . $safe_cfg['unit'] . ';
}
' . $class_profile . ' .cutemi-group:last-child {
   border-bottom-left-radius: ' . $safe_cfg['internal_border_radius'] . $safe_cfg['unit'] . ';
   border-bottom-right-radius: ' . $safe_cfg['internal_border_radius'] . $safe_cfg['unit'] . ';
}
' . $class_profile . ' .cutemi-row {
    border-bottom: solid ' . $safe_cfg['row_border'] . $safe_cfg['unit'] . ' ' . $safe_cfg['row_border_color'] . ';
} 
' . $class_profile . ' li.cutemi-cell {
    height: ' . $safe_cfg['row_height'] . $safe_cfg['unit'] . ';
    padding: ' . $safe_cfg['cell_padding'] . $safe_cfg['unit'] . ' 2px;
    border-right: solid ' . $safe_cfg['cell_border'] . $safe_cfg['unit'] . ' ' . $safe_cfg['cell_border_color'] . ';
}
' . $class_profile . ' li.cutemi-cell.cutemi-cell-multiline {
	padding: ' . ( $safe_cfg['cell_padding'] + 0.25 * $safe_cfg['font_size'] ) . $safe_cfg['unit'] . ';
}
' . $class_profile . ' li.cutemi-cell sup {
    top: ' . ( $safe_cfg['cell_padding'] / 2 ) . $safe_cfg['unit'] . ';
}
' . $class_profile . ' .cutemi-cell img,
' . $class_profile . ' .cutemi-cell svg {
    max-height: ' . max( $min_dim, $row_available ) . $safe_cfg['unit'] . ';
}
' . $class_profile . ' .cutemi-cell.cutemi-img-txt img,
' . $class_profile . ' .cutemi-cell.cutemi-img-txt svg {
    max-height: ' . max( $min_dim, $row_available / 3 * 1.75 ) . $safe_cfg['unit'] . ';
}
' . $class_profile . ' .cutemi-img-txt span {
    font-size: ' . $safe_cfg['font_size_bottom'] . $safe_cfg['unit'] . ';
    line-height: 1.2;
}
' . $class_profile . ' .cutemi-inline {
    line-height: ' . ( $row_available ) . $safe_cfg['unit'] . ';
}
' . $class_profile . ' .cutemi-group-head {
    background: ' . $safe_cfg['blocks_headers_color'] . ';
    color: ' . $safe_cfg['blocks_headers_font_color'] . ';
    fill: ' . $safe_cfg['blocks_headers_font_color'] . ';
    /*height: ' . ( $safe_cfg['row_height'] ) . $safe_cfg['unit'] . ';*/
	padding: ' . ( $safe_cfg['cell_padding'] ) . $safe_cfg['unit'] . ' ' . ( $safe_cfg['cell_padding'] / 4 ) . $safe_cfg['unit'] . ';
    width: ' . $safe_cfg['blocks_headers_side_width'] . $safe_cfg['unit'] . ';
}
' . $class_profile . '>div>ul.cutemi-list {
	margin-left: ' . $safe_cfg['blocks_headers_side_width'] . $safe_cfg['unit'] . ';
}
' . $class_profile . ' .cutemi-group-head span,
' . $class_profile . ' .cutemi-group-head svg {
    color: ' . $safe_cfg['blocks_headers_font_color'] . ';
    fill: ' . $safe_cfg['blocks_headers_font_color'] . ';
}
' . $class_profile . ' .cutemi-group-head .cutemi-img-txt img,
' . $class_profile . ' .cutemi-group-head .cutemi-img-txt svg {
    max-height: ' . max( $min_dim, ( $safe_cfg['row_height'] - $safe_cfg['cell_padding'] * 2 ) ) . $safe_cfg['unit'] . ';
}
' . $class_profile . ' .cutemi-group-head.cutemi-img-txt img,
' . $class_profile . ' .cutemi-group-head.cutemi-img-txt svg {
	height: ' . max( $min_dim, ( $safe_cfg['row_height'] - $safe_cfg['font_size'] - $safe_cfg['cell_padding'] * 2 ) ) . $safe_cfg['unit'] . ';
}
' . $class_profile . ' .cutemi-group-left-top-head .cutemi-group-head img,
' . $class_profile . ' .cutemi-group-left-top-head .cutemi-group-head svg{
	height: ' . max( $min_dim, ( $safe_cfg['row_height'] - $safe_cfg['font_size_bottom'] - $safe_cfg['cell_padding'] * 2 ) ) . $safe_cfg['unit'] . ';
}
' . $class_profile . ' .cutemi-group-head img,
' . $class_profile . ' .cutemi-group-head svg {
	height: ' . ( $safe_cfg['font_size'] * 1.2 ) . $safe_cfg['unit'] . ';
}
';

		if ( ! empty( $safe_cfg['multiline_max_height'] ) ) {
			$out .= $class_profile . ' li.cutemi-cell.cutemi-cell-multiline span {
	max-height: ' . $safe_cfg['multiline_max_height'] . $safe_cfg['unit'] . '; 
	overflow: auto;
}';
		}

		$btn_padding     = $safe_cfg['font_size'] * 0.15;
		$btn_height      = min( 100, $safe_cfg['button_height_percentage'] ) / 100 *
			max( $safe_cfg['font_size'] * 1.2, $safe_cfg['row_height'] );
		$btn_line_height = $btn_height - 2 * $safe_cfg['button_border_width'] - 2 * $btn_padding;

		//on focus twentytwentyone change the background
		//  :not(.wp-block-button__link):not(.wp-block-file__button)
		//  :not(:hover):not(:active):not(.has-text-color)
		// .editor-styles-wrapper editor preview
		$out .= $class_profile . ' .cutemi-btn,
		' . $class_profile . ' .cutemi-btn:hover,
		' . $class_profile . ' .cutemi-btn:focus:not(.wp-block-button__link):not(.wp-block-file__button),
		' . $class_profile . ' .cutemi-btn:active,
		 .editor-styles-wrapper ' . $class_profile . ' .cutemi-btn{
    border: solid ' . $safe_cfg['button_border_width'] . $safe_cfg['unit'] . ' ' . $safe_cfg['button_border_color'] . ';
    background: ' . $safe_cfg['button_background_color'] . ';
    color: ' . $safe_cfg['button_font_color'] . ';
    width: ' . $safe_cfg['button_width_percentage'] . '%;
    border-radius: ' . $safe_cfg['button_border_radius'] . $safe_cfg['unit'] . ';
    line-height: ' . $btn_line_height . $safe_cfg['unit'] . ';
    height: ' . $btn_height . $safe_cfg['unit'] . ';
    padding: ' . ( $btn_padding ) . $safe_cfg['unit'] . ' 0.25em;
	margin-top: ' . ( ( $safe_cfg['row_height'] - $btn_line_height -
			2 * ( $safe_cfg['button_border_width'] + $btn_padding ) ) / 2 ) . $safe_cfg['unit'] . ';
}';
		$out .= $class_profile . ' .cutemi-btn img,
				' . $class_profile . ' .cutemi-btn svg{
			height: ' . ( $btn_line_height * 0.7 ) . $safe_cfg['unit'] . ';
			margin-top: ' . ( $btn_line_height * 0.1 ) . $safe_cfg['unit'] . ';
			margin-bottom: ' . ( $btn_line_height * 0.1 ) . $safe_cfg['unit'] . ';
		}';
		$out .= $class_profile . ' .cutemi-btn span,
				' . $class_profile . ' .cutemi-btn span{
	color: ' . $safe_cfg['button_font_color'] . ';
	line-height: ' . $btn_line_height . $safe_cfg['unit'] . ';
		}';

		/****
		 * Responsive mods
		 */
		if ( 'on' === get_option( 'cutemi_responsive_enable', 'on' ) ) {
			$b_labeled_img = ( $row_available ) / ( 2 * 1.25 ) . $safe_cfg['unit'];

			$px_convert      = array(
				'min_width',
				'outer_border_radius',
				'blocks_border_width',
				'outer_blocks_padding',
				'cell_border',
				'blocks_headers_side_width',
				'cell_padding',
				'row_height',
				'font_size',
			);
			$style_config_px = array();
			foreach ( $px_convert as $name ) {
				$style_config_px[ $name ] = cutemi_get_px_equivalent( $safe_cfg[ $name ], $safe_cfg['unit'] );
			}
			//Responsive
			$widths = array( 1000, 650, 450, 320 );
			foreach ( $widths as $break ) {

				$available_content = max( $style_config_px['min_width'], $break )
									- 2 * $style_config_px['outer_border_radius']
									- 2 * $style_config_px['blocks_border_width']
									- 2 * $style_config_px['outer_blocks_padding'];

				$width = (int) ( $break * 1.2 );

				$cols = 2;
				while ( $cols < 6 ) {
					$available_top_head  = $available_content - ( ( $cols - 1 ) * $style_config_px['cell_border'] );
					$available_side_head = $available_top_head - $style_config_px['blocks_headers_side_width'];

					$per_cell_top_head  = $available_top_head / $cols;
					$per_cell_side_head = $available_side_head / $cols;

					$b_labeled_two_lines = ( $row_available ) / 4;

					$font_size['common']      = max( 12, $per_cell_side_head / 5.5 );
					$font_size['top_labeled'] = max( 12, $per_cell_top_head / 5 );
					$font_size['left_image']  = max( 10, $per_cell_side_head * 0.54 / 5 );
					$font_size['b_labeled']   = max( 9, min( $b_labeled_two_lines, $per_cell_side_head / 5 ) );

					foreach ( $font_size as $n => $fz ) {
						if ( $style_config_px['font_size'] < $fz ) {
							$font_size[ $n ] = $style_config_px['font_size'];
						}
					}

					$font_size['big'] = $available_top_head / 10;
					if ( $font_size['big'] > $style_config_px['font_size'] * 1.4 ) {
						$font_size['big'] = $style_config_px['font_size'] * 1.4;
					}

					$left_to_top  = '';
					$left_to_top2 = '';
					if ( $width <= 500 ) {
						$left_to_top  = $class_profile . ' .cutemi-group-left-top-head .cutemi-group-cols-' . $cols . ' span,
							' . $class_profile . ' .cutemi-group-left-top-head .cutemi-group-cols-' . $cols . ' small,
							';
						$left_to_top2 = $class_profile . ' .cutemi-group-cols-' . $cols .
										' .cutemi-group-left-top-head .cutemi-group-head span';
					}

					$out .= '
						@media (max-width:' . $width . 'px) {
							' . $class_profile . ' .cutemi-group-cols-' . $cols . ' .cutemi-cell span,
							' . $class_profile . ' .cutemi-group-cols-' . $cols . ' .cutemi-cell small {
								font-size: ' . $font_size['common'] . 'px;
							}
							' . $left_to_top . '
							' . $class_profile . ' .cutemi-group-top-head .cutemi-group-cols-' . $cols . ' span,
							' . $class_profile . ' .cutemi-group-top-head .cutemi-group-cols-' . $cols . ' small {
								font-size: ' . $font_size['top_labeled'] . 'px;
							}
							' . $class_profile . ' .cutemi-group-cols-' . $cols . ' .cutemi-cell.cutemi-inline-img-txt span {
								font-size: ' . $font_size['left_image'] . 'px;
							}
							' . $class_profile . ' .cutemi-group-cols-' . $cols . ' .cutemi-cell.cutemi-img-txt img,
							' . $class_profile . ' .cutemi-group-cols-' . $cols . ' .cutemi-cell.cutemi-img-txt svg{
							    height: ' . $b_labeled_img . ';
							}
							' . $class_profile . ' .cutemi-group-cols-' . $cols . ' .cutemi-cell.cutemi-img-txt span {
								font-size: ' . $font_size['b_labeled'] . 'px;
						        line-height: 1.1em;
						        white-space: normal;
						    }
						    ' . $left_to_top2 . '
						    ' . $class_profile . ' .cutemi-group-cols-' . $cols . ' .cutemi-group-top-head .cutemi-group-head span {
							    font-size: ' . ( $font_size['big'] ) . 'px;
							}
						}';

					$cols ++;
				}
			}
		}
		//Flex cells
		$out .= '
' . $class_profile . ' .cutemi-group-top-head .cutemi-group-head span{
    font-size: ' . ( $safe_cfg['font_size'] * 1.3 ) . $safe_cfg['unit'] . ';
}

@media (max-width:500px) {
	' . $class_profile . ' .cutemi-group-left-top-head .cutemi-group-head span {
		 font-size: ' . ( $safe_cfg['font_size'] * 1.3 ) . $safe_cfg['unit'] . ';
	}
}
@media (min-width:300px) {
	' . $class_profile . ' .cutemi-group-cols-1 .cutemi-inline-flex span {
        font-size: ' . $safe_cfg['font_size'] . $safe_cfg['unit'] . ';
	}
}
@media (min-width:500px) {
	' . $class_profile . ' .cutemi-group-cols-2 .cutemi-inline-flex span {
        font-size: ' . $safe_cfg['font_size'] . $safe_cfg['unit'] . ';
	}
}
@media (min-width:750px) {
	' . $class_profile . ' .cutemi-group-cols-3 .cutemi-inline-flex span {
        font-size: ' . $safe_cfg['font_size'] . $safe_cfg['unit'] . ';
	}
	' . $class_profile . ' .cutemi-group-cols-1 .cutemi-cell.cutemi-img-txt.cutemi-inline-flex img,
	' . $class_profile . ' .cutemi-group-cols-2 .cutemi-cell.cutemi-img-txt.cutemi-inline-flex img,
	' . $class_profile . ' .cutemi-group-cols-3 .cutemi-cell.cutemi-img-txt.cutemi-inline-flex img {
		max-height: ' . max( $min_dim, ( $safe_cfg['row_height'] - $safe_cfg['font_size'] - $safe_cfg['cell_padding'] * 2 ) ) . $safe_cfg['unit'] . ';
		height: auto;
	}
}
@media (min-width:850px) {
	' . $class_profile . ' .cutemi-inline-flex span {
        font-size: ' . $safe_cfg['font_size'] . $safe_cfg['unit'] . ';
	}
	' . $class_profile . ' .cutemi-group-cols-1 .cutemi-cell.cutemi-img-txt.cutemi-inline-flex img,
	' . $class_profile . ' .cutemi-group-cols-2 .cutemi-cell.cutemi-img-txt.cutemi-inline-flex img,
	' . $class_profile . ' .cutemi-group-cols-3 .cutemi-cell.cutemi-img-txt.cutemi-inline-flex img,
	' . $class_profile . ' .cutemi-group-cols-4 .cutemi-cell.cutemi-img-txt.cutemi-inline-flex img {
		max-height: ' . max( $min_dim, ( $safe_cfg['row_height'] - $safe_cfg['font_size'] - $safe_cfg['cell_padding'] * 2 ) ) . $safe_cfg['unit'] . ';
		height: auto;
	}
}
';

		return apply_filters( 'cutemi_table_customize_style', $out, $class_profile, $style_cfg, $safe_cfg );
	}
}
