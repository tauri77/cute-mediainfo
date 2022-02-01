<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

class CUTEMI_Profile_Summary {

	public function __construct() {
		add_filter( 'cutemi_get_data_groups_default_config', array( $this, 'get_data_groups_default_config' ), 21, 2 );
		add_filter( 'cutemi_get_default_config_data_fields', array( $this, 'get_default_config_data_fields' ), 10, 4 );
		add_filter( 'cutemi_get_default_config_styles', array( $this, 'get_default_config_styles' ), 10, 2 );
	}

	public function get_data_groups_default_config( $defaults, $profile_id ) {
		if ( 'summary' === $profile_id ) {
			$rw = array(
				'videos'    =>
					array(
						'where_head' => 'no-head',
					),
				'links'     =>
					array(
						'priority' => '0',
					),
				'mediainfo' =>
					array(
						'priority' => '0',
					),
			);

			$defaults = cutemi_over_write_match( $defaults, $rw );
		}

		return $defaults;
	}

	public function get_default_config_data_fields( $defaults, $group, $field, $profile ) {
		if ( 'summary' === $profile ) {
			$rw = array(
				'name'   =>
					array(
						'fields' =>
							array(
								'desc'     => array( 'show_as' => 'none' ),
								'size'     => array( 'show_as' => 'txt' ),
								'date'     => array( 'show_as' => 'none' ),
								'duration' => array(
									'show_as' => 'txt',
									'row'     => '1',
								),
							),
					),
				'videos' =>
					array(
						'fields' =>
							array(
								'bitrate'      => array( 'show_as' => 'none' ),
								'bitrate_mode' => array( 'show_as' => 'none' ),
							),
					),
				'audios' =>
					array(
						'fields' =>
							array(
								'bitrate'      => array( 'show_as' => 'none' ),
								'bitrate_mode' => array( 'show_as' => 'none' ),
							),
					),
			);
			if ( ! empty( $group ) && isset( $rw[ $group ] ) ) {
				if ( ! isset( $defaults[ $group ] ) ) {
					$defaults[ $group ] = array();
				}
				if ( ! empty( $field ) && isset( $rw[ $group ]['fields'] ) && isset( $rw[ $group ]['fields'][ $field ] ) ) {
					$defaults = cutemi_over_write_match(
						$defaults,
						$rw[ $group ]['fields'][ $field ]
					);
				} else {
					$defaults = cutemi_over_write_match( $defaults, $rw[ $group ] );
				}
			} else {
				$defaults = cutemi_over_write_match( $defaults, $rw );
			}
		}

		return $defaults;
	}

	public function get_default_config_styles( $defaults, $profile_id ) {
		if ( 'summary' === $profile_id ) {
			$rw = array(
				'outer_border_width'     => 0.0,
				'outer_blocks_padding'   => 0.0,
				'font_size'              => 12.0,
				'outer_border_radius'    => 6.0,
				'internal_border_radius' => 6.0,
				'blocks_border_radius'   => 0.0,
				'blocks_border_color'    => '#5d8787',
				'blocks_spacing'         => 0.0,
				'row_border'             => 1.0,
				'row_height'             => 30.0,
				'cell_padding'           => 2.0,
				'row_border_color'       => '#aebdbf',
				'outer_border_color'     => '#6e8482',
				'blocks_border_width'    => 1.0,
				'google_font_family'     => '{"label":"Audiowide","family":"Audiowide","wght":"","wdth":"","ital":"","type":"gfont"}',
				'font_family'            => '{"label":"Helvetica","family":"Helvetica","wght":"","wdth":"","ital":"","type":"gfont"}',
			);

			$defaults = cutemi_over_write_match( $defaults, $rw );
		}

		return $defaults;
	}
}

new CUTEMI_Profile_Summary();
