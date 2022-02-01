<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

require_once CUTE_MEDIAINFO_DIR . '/includes/mediainfo/class-cutemi-mediainfo.php';
require_once CUTE_MEDIAINFO_DIR . '/includes/mediainfo/class-cutemi-mediainfo-filters.php';

class CUTEMI_Mediainfo_Data {

	public function get_data( $mediainfo_text ) {

		$mi = new CUTEMI_MediaInfo();
		$mi->parse( $mediainfo_text );

		$streams            = array();
		$streams['general'] = $mi->get_sections_by_types( 'general' );
		$streams['videos']  = $mi->get_sections_by_types( 'video' );
		$streams['audios']  = $mi->get_sections_by_types( 'audio' );
		$streams['texts']   = $mi->get_sections_by_types( 'text' );

		$section_taxs = $this->get_sections_taxonomies_data();
		$commons_taxs = $this->get_sections_taxonomies_commons_values();
		$section_vals = $this->get_sections_values_data();

		foreach ( $streams as $type => $type_streams ) {
			foreach ( $type_streams as $i => $stream ) {
				$streams[ $type ][ $i ]['taxs'] = array();
				$streams[ $type ][ $i ]['vals'] = array();
				$streams[ $type ][ $i ]         = apply_filters( 'cutemi_mediainfo_pre_stream_search', $stream, $mi );
				//Search taxonomies for stream
				//First check commons value
				foreach ( $commons_taxs[ $type ] as $tax => $common_terms ) {
					if ( isset( $streams[ $type ][ $i ]['taxs'][ $tax ] ) ) {
						continue;
					}
					foreach ( $common_terms as $term_slug => $properties_common ) {
						foreach ( $properties_common as $property => $expected_values ) {
							$val = strtolower( $mi->get_property( $stream, $property ) );
							foreach ( $expected_values as $expected_value ) {
								if ( strtolower( $expected_value ) === $val ) {
									//OK, match, Check if term exist..
									$term = cutemi_term_exists_and_enabled( $term_slug, $tax );
									if ( 0 !== $term && null !== $term ) {
										$streams[ $type ][ $i ]['taxs'][ $tax ] = $term_slug;
										break 3;
									}
								}
							}
						}
					}
				}

				//Else use tag regex
				foreach ( $section_taxs[ $type ] as $tax => $properties_search ) {
					if ( isset( $streams[ $type ][ $i ]['taxs'][ $tax ] ) ) {
						continue;
					}
					foreach ( $properties_search as $k => $property ) {
						$val = $mi->get_property( $stream, $property );
						if ( ! empty( $val ) ) {
							$found = self::search_terms_match( $val, $tax );
							if ( ! empty( $found ) ) {
								$streams[ $type ][ $i ]['taxs'][ $tax ] = self::get_better_match( $found );
								break;
							}
						}
					}
				}

				//Now vals
				foreach ( $section_vals[ $type ] as $val_key => $properties_search ) {
					if ( isset( $streams[ $type ][ $i ]['vals'][ $val_key ] ) ) {
						continue;
					}
					foreach ( $properties_search as $property ) {
						$val = $mi->get_property( $stream, $property );
						if ( ! empty( $val ) ) {
							$streams[ $type ][ $i ]['vals'][ $val_key ] = $val;
							break;
						}
					}
				}

				$streams[ $type ][ $i ] = apply_filters( 'cutemi_mediainfo_end_stream_search', $streams[ $type ][ $i ], $mi );
			}
		}

		return apply_filters( 'cutemi_mediainfo_end_streams_search', $streams, $mi );
	}

	/**
	 * Where search for match with a term in taxonomy
	 * @return array
	 */
	private function get_sections_taxonomies_data() {
		$section_taxs = array(
			'general' => array(
				'cutemi_file_format' => array( 'Format profile', 'Format', 'Complete name' ),
			),
			'videos'  => array(
				'cutemi_video_resolution' => array( 'Height', 'Title' ),
				'cutemi_video_tech'       => array(
					'Codec ID',
					'Codec ID/Info',
					'Codec ID/Hint',
					'Format',
					'Format/Info',
				),
			),
			'audios'  => array(
				'cutemi_audio_langs'    => array( 'Language' ),
				'cutemi_audio_tech'     => array(
					'Codec ID',
					'Codec ID/Info',
					'Codec ID/Hint',
					'Format',
					'Format/Info',
					'Commercial name',
				),
				'cutemi_audio_channels' => array( 'Channel(s)', 'Channel layout' ),
			),
			'texts'   => array(
				'cutemi_text_langs'  => array( 'Title', 'Language' ),
				'cutemi_text_format' => array( 'Codec ID', 'Codec ID/Info', 'Codec ID/Hint', 'Format' ),
				'cutemi_text_type'   => array( 'Title' ),
			),
		);

		return apply_filters( 'cutemi_mediainfo_sections_taxonomies_data', $section_taxs );
	}

	private function get_sections_taxonomies_commons_values() {

		$commons_taxs            = array();
		$commons_taxs['general'] = array(
			'cutemi_file_format' => array(
				'cutemi-file-format-ts' => array(
					'Format' => array( 'MPEG-TS' ),
				),
				'cutemi-file-format-ps' => array(
					'Format' => array( 'MPEG-PS' ),
				),
			),
		);
		//for codecID: https://www.matroska.org/technical/codec_specs.html;
		$commons_taxs['videos'] = array(
			'cutemi_video_resolution' => array(
				'cutemi-video-resolution-ld'      => array(
					'Height' => array(
						'360 pixels',
						'272 pixels',
						'288 pixels',
						'240 pixels',
						'180 pixels',
						'144 pixels',
						'120 pixels',
						'96 pixels',
					),
					'Width'  => array(
						'640 pixels',
						'480 pixels',
						'352 pixels',
						'426 pixels',
						'320 pixels',
						'256 pixels',
						'176 pixels',
						'160 pixels',
						'128 pixels',
					),
				),
				'cutemi-video-resolution-sd'      => array( //QHD 960 x 540
					'Height' => array( '480 pixels', '540 pixels', '576 pixels' ),
					'Width'  => array( '720 pixels', '960 pixels' ),
				),
				'cutemi-video-resolution-hd'      => array(
					'Height' => array( '720 pixels' ),
					'Width'  => array( '1 280 pixels' ),
				),
				'cutemi-video-resolution-full-hd' => array(
					'Height' => array( '1 080 pixels' ),
					'Width'  => array( '1 920 pixels' ),
				),
				'cutemi-video-resolution-4k-uhd'  => array(
					'Width' => array( '3 840 pixels' ),
				),
				'cutemi-video-resolution-dci-4k'  => array(
					'Width' => array( '4 096 pixels' ),
				),
				'cutemi-video-resolution-4k'      => array(
					'Height' => array( '2 160 pixels' ),
				),
			),
			'cutemi_video_tech'       => array(
				'cutemi-video-tech-divx' => array(
					'Codec ID' => array( 'DIVX', 'DX50' ),
				),
			),
		);

		$commons_taxs['audios'] = array(
			'cutemi_audio_langs'    => array(
				'cutemi-audio-lang-es-419' => array(
					'Title' => array( 'Latino' ),
				),
			),
			'cutemi_audio_tech'     => array(),
			'cutemi_audio_channels' => array(),
		);

		$commons_taxs['texts'] = array(
			'cutemi_text_langs'  => array(
				'cutemi-text-lang-pt-br' => array(
					'Title' => array( 'Brazilian Portuguese' ),
				),
			),
			'cutemi_text_format' => array(
				'cutemi-text-format-srt'          => array(
					'Codec ID' => array( 'S_TEXT/UTF8' ),
				),
				'cutemi-text-format-ass'          => array(
					'Codec ID' => array( 'S_TEXT/ASS' ),
				),
				'cutemi-text-format-ssa'          => array(
					'Codec ID' => array( 'S_TEXT/SSA' ),
				),
				'cutemi-text-format-webvtt'       => array(
					'Codec ID' => array( 'S_TEXT/WEBVTT' ),
				),
				'cutemi-text-format-hdmv-pgs'     => array(
					'Codec ID' => array( 'S_HDMV/PGS' ),
				),
				'cutemi-text-format-dvb-subtitle' => array(
					'Codec ID' => array( 'S_DVBSUB' ),
					'Format'   => array( 'DVB Subtitle' ),
				),
				'cutemi-text-format-bmp'          => array(
					'Codec ID' => array( 'S_IMAGE/BMP' ),
				),
				'cutemi-text-format-dvd-subtitle' => array(
					'Codec ID' => array( 'S_VOBSUB' ),
				),
				'cutemi-text-format-hdmv-text'    => array(
					'Codec ID' => array( 'S_HDMV/TEXTST' ),
				),
				'cutemi-text-format-kate'         => array(
					'Codec ID' => array( 'S_KATE' ),
				),
				'cutemi-text-format-eia-608-cc'   => array(
					'Format' => array( 'EIA-608' ),
				),
				'cutemi-text-format-eia-708-cc'   => array(
					'Format' => array( 'EIA-708' ),
				),
				'cutemi-text-format-teletext'     => array(
					'Format' => array( 'Teletext Subtitle', 'Teletext' ),
				),
				'cutemi-text-format-ebu-stl'      => array(
					'Format' => array( 'EBU Teletext subtitles' ),
				),

			),
			'cutemi_text_type'   => array(
				'cutemi-text-type-forced' => array(
					'Forced' => array( 'Yes' ),
				),
			),
		);

		return apply_filters( 'cutemi_mediainfo_sections_taxonomies_commons_values', $commons_taxs );
	}

	private function get_sections_values_data() {
		$section_vals = array(
			'general' => array(
				'title'      => array( 'Complete name' ),
				'size'       => array( 'File size' ),
				'duration'   => array( 'Duration' ),
				'video_date' => array( 'Encoded date', 'Tagged date' ),
			),
			'videos'  => array(),
			'audios'  => array(),
			'texts'   => array(),
		);

		return apply_filters( 'cutemi_mediainfo_sections_values_data', $section_vals );
	}

	public static function search_terms_match( $search_in, $tax, $use_name = true ) {
		$found = array();
		if ( empty( $search_in ) ) {
			return $found;
		}
		$terms = get_terms(
			array(
				'taxonomy'   => $tax,
				'hide_empty' => false,
			)
		);
		foreach ( $terms as $term ) {
			if ( ! cutemi_term_is_enable( $term->term_id ) ) {
				continue;
			}
			$tags_for_regex_str = get_term_meta( $term->term_id, 'tags', true );
			$tags_for_regex     = explode( ',', $tags_for_regex_str );
			$matched            = false;
			foreach ( $tags_for_regex as $regex_tag ) {
				$regex_tag = trim( $regex_tag );
				if ( ! empty( $regex_tag ) ) {
					//Test tag over valid['title']
					$pre_reg = '(?:[\.\s\-\[\]_,\(\)]|^)';
					if ( '(?i)\.' === substr( $regex_tag, 0, 6 ) ) {
						$pre_reg = '.';
					}
					$regex = "`({$pre_reg})(" . $regex_tag . ')([\.\s\-\[\]_,\(\)]|$)`';
					if ( preg_match( $regex, $search_in, $matches ) ) {
						//Test OK!;
						if ( isset( $matches[3] ) ) {
							$tag_valid          = array();
							$tag_valid['pre']   = $matches[1];//before tag
							$tag_valid['match'] = $matches[2];//tag found
							$tag_valid['post']  = $matches[3];//after tag
							$tag_valid['for']   = $term->slug;

							//calc match valority
							$tag_valid['valority'] = strlen( $matches[2] );
							if ( $tag_valid['post'] === $tag_valid['pre'] ) {
								$tag_valid['valority'] ++;
							}

							//add to taxonomy found
							$found[] = $tag_valid;
							$matched = true;
						}
					}
				}
			}
			if ( ! $matched && $use_name ) {
				if ( strtolower( $term->name ) === strtolower( $search_in ) ) {
					$tag_valid          = array();
					$tag_valid['pre']   = '';//before tag
					$tag_valid['match'] = $term->name;//tag found
					$tag_valid['post']  = '';//after tag
					$tag_valid['for']   = $term->slug;

					//calc match valority
					$tag_valid['valority'] = strlen( $term->name );
					if ( $term->name === $search_in ) {
						$tag_valid['valority'] ++;
					}

					//add to taxonomy found
					$found[] = $tag_valid;

				}
			}
		}

		return $found;
	}

	public static function get_better_match( $found ) {
		$max = array(
			'valority' => - 99,
			'for'      => '',
		);
		foreach ( $found as $match ) {
			if ( $match['valority'] > $max['valority'] ) {
				$max = $match;
			}
		}

		return $max['for'];
	}

}
