<?php

/**
 * Get available icon packs.
 *
 * @return array An associative array with icon packs (the keys are the slug Ex: ['default'=>['name'=>'Default']])
 */
function cutemi_available_icon_packs() {
	$default_icon_packs = array(
		'default' => array(
			'name' => 'Default',
		),
	);

	return apply_filters( 'cutemi_available_icon_packs', $default_icon_packs );
}

/**
 * Get default profiles.
 *
 * Return example:
 *    [
 *      'full' => [
 *         'slug'     => 'full',
 *         'label'    => 'Full',
 *         'enabled' => 'on'
 *      ],
 *      'summary' => [
 *         'slug'      => 'summary',
 *         'label'     => 'Summary',
 *          'enabled' => 'on'
 *      ]
 *    ];
 *
 * @return array The default profiles
 */
function cutemi_get_default_profiles() {
	return array(
		array(
			'slug'    => 'full',
			'label'   => __( 'Full', 'cute-mediainfo' ),
			'enabled' => 'on',
		),
		array(
			'slug'    => 'summary',
			'label'   => __( 'Summary', 'cute-mediainfo' ),
			'enabled' => 'on',
		),
	);
}

/**
 * Obtain list of profiles.
 *
 * Return example:
 *    [
 *      'full' => [
 *         'slug'     => 'full',
 *         'label'    => 'Full',
 *         'enabled' => 'on'
 *      ],
 *      'summary' => [
 *         'slug'      => 'summary',
 *         'label'     => 'Summary',
 *          'enabled' => 'on'
 *      ]
 *    ];
 *
 * @param bool $include_disabled Optional. If true, disabled profiles will also be included. Default false.
 *
 * @return array List of profile, each profile as associative array
 */
function cutemi_get_profiles( $include_disabled = false ) {
	$profiles  = array();
	$profiles_ = get_option( 'cutemi_profiles', cutemi_get_default_profiles() );
	foreach ( $profiles_ as $profile ) {
		if ( $include_disabled || 'on' === $profile['enabled'] ) {
			$profiles[ $profile['slug'] ] = $profile;
		}
	}

	return $profiles;
}

/**
 * Get default profile.
 *
 * This profile is used when a profile is not found or has not been set.
 *
 * @return string the profile slug
 */
function cutemi_get_default_profile() {
	$profile_settings = get_option( 'cutemi_profile_default', 'full' );

	$profiles = cutemi_get_profiles();
	if ( isset( $profiles[ $profile_settings ] ) ) {
		return $profile_settings;
	}

	foreach ( $profiles as $profile => $props ) {
		return $profile;
	}

	return 'full';
}

/**
 * Get "templated" profile list.
 *
 * List of profiles "templated", this is that render as php include.
 * Return example:
 *      [
 *          'excerpt' => [
 *              'template' => '...../templates/cutemi-excerpt-template.php',
 *              'label'    => 'Excerpt'
 *          ]
 *      ]
 *
 * @return array List of profiles
 */
function cutemi_get_templated_profiles() {
	$profiles = array(
		'excerpt' => array(
			'template' => CUTE_MEDIAINFO_DIR . '/templates/cutemi-excerpt-template.php',
			'label'    => 'Excerpt',
		),
	);

	return apply_filters( 'cutemi_get_templated_profiles', $profiles );
}

/**
 * Get an "templated" profile from the slug.
 *
 * @param string $profile Profile slug
 *
 * @return bool|string The php location to include, or false if not found
 */
function cutemi_get_templated_profile( $profile ) {
	$profiles = cutemi_get_templated_profiles();
	if (
			isset( $profiles[ $profile ] ) &&
			isset( $profiles[ $profile ]['template'] ) &&
			file_exists( $profiles[ $profile ]['template'] )
	) {
		return $profiles[ $profile ]['template'];
	}

	return false;
}

/**
 * Check if a profile slug is valid
 *
 * @param string $profile Profile slug
 * @param bool $include_disabled Optional. If true, disabled profiles will also be included. Default false.
 *
 * @return bool
 */
function cutemi_is_valid_profile( $profile, $include_disabled = false ) {
	if ( ! is_string( $profile ) || '' === $profile ) {
		return false;
	}
	//Check if profile is php templated
	$templated = cutemi_get_templated_profile( $profile );
	if ( false !== $templated ) {
		return true;
	}

	$profiles = cutemi_get_profiles( $include_disabled );
	if ( isset( $profiles[ $profile ] ) ) {
		return true;
	}

	return false;
}

/**
 * Get the defaults values for profile styling.
 *
 * The value also determines how the customizer handles the option:
 *     - int or float => number
 *     - string that start with "#" or "rgba(" => color
 *     - string "on" or "off" => checkbox
 * Special handle: unit, font_family, google_font_family, mediainfo_google_font_family.
 *
 * @param bool $profile Optional. The profile slug. Default false.
 *
 * @return array
 */
function cutemi_get_style_config_default( $profile = false ) {
	$default_styles = array(
		'unit'                         => 'px',
		'font_size'                    => 16.0,
		'font_family'                  => '{"label":"Arial","family":"Arial","wght":"","ital":"","wdth":"","type":""}',
		'google_font_family'           => '{"label":"Audiowide","family":"Audiowide","wght":"","wdth":"","ital":"",' .
											'"type":"gfont"}',
		'data_color'                   => '#2e3a4e',
		'external_margin'              => 7.0,
		'external_margin_sides_auto'   => 'on',
		'outer_border_width'           => 1,
		'outer_border_color'           => '#68bbd0',
		'outer_blocks_padding'         => 6,
		'background_color'             => '#3b99b1',
		'outer_border_radius'          => 3,
		'internal_border_radius'       => 3,
		'min_width'                    => 0,
		'max_width'                    => 0,
		'multiline_max_height'         => 200,
		'blocks_border_width'          => 1,
		'blocks_border_radius'         => 3,
		'blocks_border_color'          => '#5bd8d3',
		'blocks_background_color'      => '#ccccff',
		'blocks_headers_color'         => '#68bbd0',
		'blocks_headers_font_color'    => '#023c4a',
		'blocks_headers_side_width'    => 80.0,
		'blocks_spacing'               => 11,
		'row_border'                   => 1,
		'row_border_color'             => '#68bbd0',
		'row_height'                   => 45,
		'cell_padding'                 => 3,
		'cell_border'                  => 0,
		'cell_border_color'            => '#d0c70f',
		'button_background_color'      => '#2188a2',
		'button_border_color'          => '#275a58',
		'button_height_percentage'     => 60,
		'button_width_percentage'      => 80,
		'button_border_width'          => 1,
		'button_border_radius'         => 3,
		'button_font_color'            => '#d8f6ff',
		'mediainfo_google_font_family' => '',
		'mediainfo_font_size'          => 12,
	);

	return apply_filters( 'cutemi_get_default_config_styles', $default_styles, $profile );
}

/**
 * Get the default configuration for data groups/blocks (Where and how to show them).
 *
 * @param bool $profile Optional. Profile slug.  Default false.
 *
 * @return array
 */
function cutemi_get_data_groups_default_config( $profile = false ) {

	$groups_configs = array(
		'name'      => array(
			'id'         => 'name',
			'priority'   => 1,
			'where_head' => 'top',
			'show_as'    => 'img_txt',
		),
		'videos'    => array(
			'id'         => 'videos',
			'priority'   => 2,
			'where_head' => 'left-top',
			'show_as'    => 'img_n_txt',
		),
		'audios'    => array(
			'id'         => 'audios',
			'priority'   => 3,
			'where_head' => 'left-top',
			'show_as'    => 'img_n_txt',
		),
		'texts'     => array(
			'id'         => 'texts',
			'priority'   => 4,
			'where_head' => 'left-top',
			'show_as'    => 'img_n_txt',
		),
		'links'     => array(
			'id'         => 'links',
			'priority'   => 5,
			'where_head' => 'left-top',
			'show_as'    => 'img_n_txt',
		),
		'mediainfo' => array(
			'id'         => 'mediainfo',
			'priority'   => 6,
			'where_head' => 'left-top',
			'show_as'    => 'img_n_txt',
		),
	);

	return apply_filters( 'cutemi_get_data_groups_default_config', $groups_configs, $profile );
}


/**
 * Get the default configuration for data fields
 *
 * Basically this determines how each cell will be displayed in the render.
 * This can return configuration for all group or for a field ( if $field is set ).
 *
 * @param string $group The group/block id (name|videos|audios|etc...).
 * @param string|bool $field Optional. The field id (format|size|etc...). Default false (return group config).
 * @param string|bool $profile Optional. The profile slug. Default false.
 *
 * @return mixed|void
 */
function cutemi_get_default_config_data_fields( $group, $field = false, $profile = false ) {

	$defaults = array(
		'name'      => array(
			'type'   => 'rows',
			'fields' => array(
				'format'   => array(
					'show_as'  => 'img',
					'row'      => 1,
					'position' => 2,
				),
				'size'     => array(
					'show_as'  => 'txt',
					'row'      => 1,
					'position' => 3,
				),
				'date'     => array(
					'show_as'  => 'txt',
					'row'      => 2,
					'position' => 1,
				),
				'duration' => array(
					'show_as'  => 'txt',
					'row'      => 2,
					'position' => 2,
				),
				'desc'     => array(
					'show_as'  => 'multiline',
					'row'      => 3,
					'position' => 1,
				),
			),
		),
		'videos'    => array(
			'type'   => 'arr',
			'fields' => array(
				'resolution'   => array(
					'show_as'  => 'img',
					'position' => 1,
				),
				'tech'         => array(
					'show_as'  => 'img',
					'position' => 2,
				),
				'bitrate'      => array(
					'show_as'  => 'img',
					'position' => 3,
				),
				'bitrate_mode' => array(
					'show_as'  => 'img',
					'position' => 4,
				),
			),
		),
		'audios'    => array(
			'type'   => 'arr',
			'fields' => array(
				'lang'         => array(
					'show_as'  => 'img',
					'position' => 1,
				),
				'tech'         => array(
					'show_as'  => 'img',
					'position' => 2,
				),
				'channels'     => array(
					'show_as'  => 'img',
					'position' => 3,
				),
				'bitrate'      => array(
					'show_as'  => 'img',
					'position' => 4,
				),
				'bitrate_mode' => array(
					'show_as'  => 'img',
					'position' => 5,
				),
			),
		),
		'texts'     => array(
			'type'   => 'arr',
			'fields' => array(
				'lang'   => array(
					'show_as'  => 'img',
					'position' => 1,
				),
				'format' => array(
					'show_as'  => 'img',
					'position' => 2,
				),
				'type'   => array(
					'show_as'  => 'img',
					'position' => 3,
				),
			),
		),
		'links'     => array(
			'type'   => 'arr',
			'fields' => array(
				'source'      => array(
					'show_as'  => 'img',
					'position' => 1,
				),
				'part'        => array(
					'show_as'  => 'txt_top_label',
					'position' => 2,
				),
				'size'        => array(
					'show_as'  => 'txt',
					'position' => 3,
				),
				'external_id' => array(
					'show_as'   => 'link',
					'position'  => 4,
					'customize' => array(
						'link_anchor_text' => array(
							'label'        => __( 'Link Text', 'cute-mediainfo' ),
							'default'      => '>',
							'control_type' => 'text',
						),
						'link_anchor_img'  => array(
							'label'        => __( 'Link Image URL', 'cute-mediainfo' ),
							'default'      => '',
							'control_type' => 'url',
						),
					),
				),
			),
		),
		'mediainfo' => array(
			'type'   => 'unique',
			'fields' => array(
				'mediainfo' => array(
					'show_as'  => 'pre',
					'position' => 1,
				),
			),
		),
	);

	if ( ! empty( $field ) ) {
		if ( isset( $defaults[ $group ] ) && isset( $defaults[ $group ]['fields'][ $field ] ) ) {
			return apply_filters(
				'cutemi_get_default_config_data_fields',
				$defaults[ $group ]['fields'][ $field ],
				$group,
				$field,
				$profile
			);
		}
	} else {
		if ( isset( $defaults[ $group ] ) ) {
			return apply_filters(
				'cutemi_get_default_config_data_fields',
				$defaults[ $group ],
				$group,
				$field,
				$profile
			);
		}
	}

	return apply_filters(
		'cutemi_get_default_config_data_fields',
		array(),
		$group,
		$field,
		$profile
	);
}
