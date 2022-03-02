<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

function cutemi_register_block() {
	add_filter(
		'block_type_metadata_settings',
		function ( $settings, $metadata ) {
			if ( 'cute-mediainfo/cutemi-block' === $metadata['name'] ) {
				$settings['render_callback'] = 'cutemi_render_wp_block';
			}

			return $settings;
		},
		2,
		2
	);
	if ( function_exists( 'register_block_type' ) ) {
		if ( version_compare( $GLOBALS['wp_version'], '5.8', '>=' ) ) {
			register_block_type( __DIR__ );
		} else {
			cutemi_register_block_type_from_metadata( __DIR__ );
		}
	}
}

add_action( 'init', 'cutemi_register_block' );

function cutemi_render_wp_block( $attributes ) {
	$pre = '';
	if ( empty( $attributes['id'] ) ) {
		return $pre . cutemi_render_block_preview(
			isset( $attributes['profile'] ) ? $attributes['profile'] : cutemi_get_default_profile()
		);
	}
	if ( defined( 'REST_REQUEST' ) ) {
		$edit_link = get_edit_post_link( (int) $attributes['id'], 'api' );
		if ( $edit_link ) {
			$pre = '<a href="' . esc_url( $edit_link ) . '" title="' .
						esc_attr__( 'Edit Mediainfo', 'cute-mediainfo' ) .
						'" class="button cutemi-preview-edit-link" target="_blank">' .
						'<svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" role="img" ' .
						'aria-hidden="true" focusable="false">' .
						'<path d="M20.1 5.1L16.9 2 6.2 12.7l-1.3 4.4 4.5-1.3L20.1 5.1zM4 20.8h8v-1.5H4v1.5z"></path></svg>' .
						'</a>';
		}
	}

	return $pre . cutemi_get_post_content( (int) $attributes['id'], $attributes['profile'] );
}


add_action(
	'rest_api_init',
	function () {
		register_rest_field(
			'cute_mediainfo',
			'edit_link',
			array(
				'get_callback' => function( $arr ) {
					return get_edit_post_link( $arr['id'], 'api' );
				},
				'schema'       => array(
					'description' => __( 'Edit url', 'cute-mediainfo' ),
					'type'        => 'string',
				),
			)
		);
	}
);


function cutemi_render_block_preview( $profile ) {
	$base_url = plugins_url( '/', CUTE_MEDIAINFO_FILE );
	/** @noinspection DuplicatedCode */
	$video = array(
		'parts'        => 2,
		'name'         => __( 'Editing This Table.mp4', 'cute-mediainfo' ),
		'format'       => array(
			'img'  => $base_url . 'icon-packs/images/default/video-format/MP4.svg',
			'text' => 'MP4',
			'desc' => 'MPEG-4 Part 14',
			'slug' => 'cutemi-file-format-mp4',
		),
		'size'         => array(
			'img'  => '',
			'text' => '90000000',
			'desc' => '',
		),
		'duration'     => array(
			'img'  => '',
			'text' => '120',
			'desc' => '',
		),
		'date'         => array(
			'img'  => '',
			'text' => '20220101',
			'desc' => '',
		),
		'desc'         => array(
			'img'  => '',
			'text' => '',
			'desc' => '',
		),
		'mediainfo'    => array(
			'img'  => '',
			'text' => '',
			'desc' => '',
		),
		'videos'       => array(
			0 => array(
				'resolution'   => array(
					'img'  => $base_url . 'icon-packs/images/default/resolutions/SD.svg',
					'text' => 'SD',
					'desc' => 'Standard Definiton',
					'slug' => 'cutemi-video-resolution-sd',
				),
				'tech'         => array(
					'img'  => $base_url . 'icon-packs/images/default/video-tech/h.264.svg',
					'text' => 'MPEG-4 AVC',
					'desc' => 'H.264, MPEG-4 Advanced Video Coding (MPEG-4 Part 10)',
					'slug' => 'cutemi-video-tech-mpeg-4-avc',
				),
				'bitrate'      => array(
					'img'  => $base_url . 'icon-packs/images/default/video-bitrate/1_Mbps.svg',
					'text' => '1 Mbps',
					'desc' => 'Approximately 1 Mbps',
					'slug' => 'cutemi-video-bitrate-1-m',
				),
				'bitrate_mode' => array(
					'img'  => $base_url . 'icon-packs/images/default/video-bitrate-mode/cbr.svg',
					'text' => 'CBR',
					'desc' => 'Constant Bitrate',
					'slug' => 'cutemi-video-bitrate-mode-cbr',
				),
			),
		),
		'videos-count' => 1,
		'audios'       => array(
			0 => array(
				'lang'         => array(
					'img'  => $base_url . 'icon-packs/images/default/langs/en.svg',
					'text' => 'English',
					'desc' => '',
					'slug' => 'cutemi-audio-lang-en',
				),
				'tech'         => array(
					'img'  => $base_url . 'icon-packs/images/default/audio-techs/ACC.svg',
					'text' => 'AAC',
					'desc' => 'Advanced Audio Coding',
					'slug' => 'cutemi-audio-tech-aac',
				),
				'channels'     => array(
					'img'  => $base_url . 'icon-packs/images/default/audio-channels/5.1.svg',
					'text' => '5.1',
					'desc' => 'Five channel output, plus subwoofer',
					'slug' => 'cutemi-audio-channels-5-1',
				),
				'bitrate'      => array(
					'img'  => $base_url . 'icon-packs/images/default/audio-bitrate/256_kbps.svg',
					'text' => '256 kbps',
					'desc' => 'Approximately 256 kbps',
					'slug' => 'cutemi-audio-bitrate-256-k',
				),
				'bitrate_mode' => array(
					'img'  => $base_url . 'icon-packs/images/default/audio-bitrate-mode/vbr.svg',
					'text' => 'VBR',
					'desc' => 'Variable Bitrate',
					'slug' => 'cutemi-audio-bitrate-mode-vbr',
				),
			),
		),
		'audios-count' => 1,
		'texts'        => array(
			0 => array(
				'lang'   => array(
					'img'  => $base_url . 'icon-packs/images/default/langs/en.svg',
					'text' => 'English',
					'desc' => '',
					'slug' => 'cutemi-text-lang-en',
				),
				'format' => array(
					'img'  => $base_url . 'icon-packs/images/default/text-format/SRT.svg',
					'text' => 'SRT',
					'desc' => 'SubRip subtitle (srt)',
					'slug' => 'cutemi-text-format-srt',
				),
				'type'   => array(
					'img'  => $base_url . 'icon-packs/images/default/text-types/SDH.svg',
					'text' => 'SDH',
					'desc' => 'Subtitles For The Deaf And Hard Of Hearing',
					'slug' => 'cutemi-text-type-sdh',
				),
			),
			1 => array(
				'lang'   => array(
					'img'  => $base_url . 'icon-packs/images/default/langs/c-hu.svg',
					'text' => 'Hungarian',
					'desc' => '',
					'slug' => 'cutemi-text-lang-hu',
				),
				'format' => array(
					'img'  => $base_url . 'icon-packs/images/default/text-format/SRT.svg',
					'text' => 'SRT',
					'desc' => 'SubRip subtitle (srt)',
					'slug' => 'cutemi-text-format-srt',
				),
				'type'   => array(
					'img'  => '',
					'text' => '',
					'desc' => '',
				),
			),
		),
		'texts-count'  => 2,
		'links'        => array(),
		'links-count'  => 0,
	);

	return cutemi_get_mediainfo_content( $video, $profile );
}


add_action( 'enqueue_block_editor_assets', 'cutemi_wp_block_styles' );
function cutemi_wp_block_styles() {
	do_action( 'cutemi_enqueue_css' );

	$profiles           = cutemi_get_profiles();
	$profiles_templated = cutemi_get_templated_profiles();

	$options_profiles = array();
	foreach ( $profiles as $profile ) {
		$options_profiles[] = array(
			'value' => $profile['slug'],
			'label' => $profile['label'],
		);
	}
	foreach ( $profiles_templated as $slug => $profile ) {
		$options_profiles[] = array(
			'value' => $slug,
			'label' => $profile['label'],
		);
	}

	wp_register_script( 'cutemi_profiles_add', '', array(), '1', false );
	wp_enqueue_script( 'cutemi_profiles_add' );
	wp_add_inline_script(
		'cutemi_profiles_add',
		'window.cutemiProfiles = ' . wp_json_encode( $options_profiles ) . ';'
	);

}

/*****
 * Only for old versions of WP. > 5.0 and < 5.8
 *
 * @param $file_or_folder
 * @param array $args
 *
 * @return bool|false|string|WP_Block_Type
 */
function cutemi_register_block_type_from_metadata( $file_or_folder, $args = array() ) {
	$filename      = 'block.json';
	$metadata_file = ( substr( $file_or_folder, - strlen( $filename ) ) !== $filename ) ?
		trailingslashit( $file_or_folder ) . $filename :
		$file_or_folder;
	if ( ! file_exists( $metadata_file ) ) {
		return false;
	}

	$metadata = json_decode( file_get_contents( $metadata_file ), true );
	if ( ! is_array( $metadata ) || empty( $metadata['name'] ) ) {
		return false;
	}
	$metadata['file'] = $metadata_file;

	$metadata = apply_filters( 'block_type_metadata', $metadata );

	// Add `style` and `editor_style` for core blocks if missing.
	if ( ! empty( $metadata['name'] ) && 0 === strpos( $metadata['name'], 'core/' ) ) {
		$block_name = str_replace( 'core/', '', $metadata['name'] );

		if ( ! isset( $metadata['style'] ) ) {
			$metadata['style'] = "wp-block-$block_name";
		}
		if ( ! isset( $metadata['editorStyle'] ) ) {
			$metadata['editorStyle'] = "wp-block-{$block_name}-editor";
		}
	}

	$settings          = array();
	$property_mappings = array(
		'title'           => 'title',
		'category'        => 'category',
		'parent'          => 'parent',
		'icon'            => 'icon',
		'description'     => 'description',
		'keywords'        => 'keywords',
		'attributes'      => 'attributes',
		'providesContext' => 'provides_context',
		'usesContext'     => 'uses_context',
		'supports'        => 'supports',
		'styles'          => 'styles',
		'example'         => 'example',
		'apiVersion'      => 'api_version',
	);

	foreach ( $property_mappings as $key => $mapped_key ) {
		if ( isset( $metadata[ $key ] ) ) {
			$value = $metadata[ $key ];
			if ( empty( $metadata['textdomain'] ) ) {
				$settings[ $mapped_key ] = $value;
				continue;
			}
			$textdomain = $metadata['textdomain'];
			switch ( $key ) {
				case 'title':
				case 'description':
					$settings[ $mapped_key ] = translate_with_gettext_context( $value, sprintf( 'block %s', $key ), $textdomain );
					break;
				case 'keywords':
					$settings[ $mapped_key ] = array();
					if ( ! is_array( $value ) ) {
						continue 2;
					}

					foreach ( $value as $keyword ) {
						$settings[ $mapped_key ][] = translate_with_gettext_context( $keyword, 'block keyword', $textdomain );
					}

					break;
				case 'styles':
					$settings[ $mapped_key ] = array();
					if ( ! is_array( $value ) ) {
						continue 2;
					}

					foreach ( $value as $style ) {
						if ( ! empty( $style['label'] ) ) {
							$style['label'] = translate_with_gettext_context( $style['label'], 'block style label', $textdomain );
						}
						$settings[ $mapped_key ][] = $style;
					}

					break;
				default:
					$settings[ $mapped_key ] = $value;
			}
		}
	}

	if ( ! empty( $metadata['editorScript'] ) ) {
		$settings['editor_script'] = register_block_script_handle(
			$metadata,
			'editorScript'
		);
	}

	if ( ! empty( $metadata['script'] ) ) {
		$settings['script'] = register_block_script_handle(
			$metadata,
			'script'
		);
	}

	if ( ! empty( $metadata['editorStyle'] ) ) {
		$settings['editor_style'] = register_block_style_handle(
			$metadata,
			'editorStyle'
		);
	}

	if ( ! empty( $metadata['style'] ) ) {
		$settings['style'] = register_block_style_handle(
			$metadata,
			'style'
		);
	}

	$settings = apply_filters(
		'block_type_metadata_settings',
		array_merge(
			$settings,
			$args
		),
		$metadata
	);

	return WP_Block_Type_Registry::get_instance()->register(
		$metadata['name'],
		$settings
	);
}
