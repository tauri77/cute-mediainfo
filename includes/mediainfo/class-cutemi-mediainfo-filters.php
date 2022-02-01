<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

class CUTEMI_Mediainfo_Filters {

	public function __construct() {
		add_filter( 'cutemi_mediainfo_pre_stream_search', array( $this, 'pre_stream' ), 10, 2 );
		add_filter( 'cutemi_mediainfo_end_stream_search', array( $this, 'end_stream' ), 10, 2 );

		add_filter( 'cutemi_mediainfo_end_streams_search', array( $this, 'end_streams' ), 10, 2 );
	}

	/**
	 * @param array            $streams
	 * @param CUTEMI_Mediainfo $mi
	 *
	 * @return mixed
	 */
	public function end_streams( $streams, $mi ) {

		//Set 'taxs' and 'vals' property to all streams
		foreach ( $streams as $type_stream => $streams_types ) {
			foreach ( $streams_types as $k => $stream ) {
				if ( ! isset( $stream['taxs'] ) ) {
					$streams[ $type_stream ][ $k ]['taxs'] = array();
				}
				if ( ! isset( $stream['vals'] ) ) {
					$streams[ $type_stream ][ $k ]['vals'] = array();
				}
			}
		}

		if ( empty( $streams['general'] ) ) {
			if ( 1 === count( $streams['videos'] ) ) {
				$streams['general'][] = array();

				$duration = $mi->get_property( $streams['videos'][0], 'Duration' );
				if ( ! empty( $duration ) ) {
					$duration                          = cutemi_duration_to_seconds( $duration );
					$streams['general'][0]['duration'] = $duration;
					if ( ! empty( $duration ) ) {
						$streams['general'][0]['vals']['duration'] = $duration;
					}
				}

				$date = $mi->get_property( $streams['videos'][0], 'Encoded date' );
				if ( ! empty( $date ) ) {
					$streams['general'][0]['Encoded date'] = $date;

					$timestamp = strtotime( $date );
					if ( false === $timestamp ) {
						unset( $streams['general'][0]['Encoded date'] ); //no valid date
					} else {
						$streams['general'][0]['vals']['video_date'] = gmdate( 'Y-m-d', $timestamp );
					}
				}

				$partial_size = $mi->get_property( $streams['videos'][0], 'Stream size' );
				if ( preg_match( '/([0-9.]*\s*.i?B)\s*\(([0-9]{1,3})%\)/', $partial_size, $match ) ) {
					$video_size = cutemi_to_byte_size( $match[1] );
					if ( ! empty( $video_size ) ) {
						$streams['general'][0]['vals']['size'] = round( $video_size * 100 / intval( $match[2] ) );
					}
				}
			}
		}

		$overwrite_taxonomies                       = array();
		$overwrite_taxonomies['cutemi_file_format'] = array();
		$overwrite_taxonomies['cutemi_video_tech']  = array();
		$overwrite_taxonomies['cutemi_audio_tech']  = array();
		$overwrite_taxonomies['cutemi_text_format'] = array();

		$overwrite_taxonomies['cutemi_file_format'][] = array(
			'term'          => 'cutemi-file-format-dvr-ms',
			'stream_type'   => 'general',
			'only_on_empty' => true,
			'conditionals'  => array(
				'videos' => array(
					'Codec ID' => 'DVR',
				),
				'stream' => array(
					'Format' => 'Windows Media',
				),
			),
		);

		$overwrite_taxonomies['cutemi_video_tech'][] = array(
			'term'          => 'cutemi-video-tech-mpeg-4-nero-digital',
			'stream_type'   => 'videos',
			'only_on_empty' => false,
			'conditionals'  => array(
				'general' => array(
					'Format profile' => 'Nero Digital Standard Profile',
				),
				'stream'  => array(
					'Format' => 'MPEG-4 Visual',
				),
			),
		);
		$overwrite_taxonomies['cutemi_video_tech'][] = array(
			'term'          => 'cutemi-video-tech-quicktime-6',
			'stream_type'   => 'videos',
			'only_on_empty' => false,
			'conditionals'  => array(
				'general' => array(
					'Format profile' => 'QuickTime',
				),
				'stream'  => array(
					'Format' => 'MPEG-4 Visual',
				),
			),
		);

		$overwrite_taxonomies['cutemi_video_tech'][]  = array(
			'term'          => 'cutemi-video-tech-quicktime-7',
			'stream_type'   => 'videos',
			'only_on_empty' => false,
			'conditionals'  => array(
				'general' => array(
					'Format profile' => 'QuickTime',
				),
				'stream'  => array(
					'Format' => 'AVC',
				),
			),
		);
		$overwrite_taxonomies['cutemi_video_tech'][]  = array(
			'term'          => 'cutemi-video-tech-quicktime-6-sp',
			'stream_type'   => 'videos',
			'only_on_empty' => false,
			'conditionals'  => array(
				'general' => array(
					'Format' => 'QuickTime',
				),
				'stream'  => array(
					'Format profile' => '*/Simple@L/',
				),
			),
		);
		$overwrite_taxonomies['cutemi_video_tech'][]  = array(
			'term'          => 'cutemi-video-tech-quicktime-6',
			'stream_type'   => 'videos',
			'only_on_empty' => false,
			'conditionals'  => array(
				'general' => array(
					'Format' => 'QuickTime',
				),
				'stream'  => array(
					'Format profile' => '*/^Advanced\s*Simple@L[0-5]/i',
				),
			),
		);
		$overwrite_taxonomies['cutemi_video_tech'][]  = array(
			'term'          => 'cutemi-video-tech-mpeg-4-nero-digital',
			'stream_type'   => 'videos',
			'only_on_empty' => false,
			'conditionals'  => array(
				'general' => array(
					'Codec ID' => '*@/(NDAS|NDSC|ndsh|NDSM|NDSP|NDSS)\)@i',
				),
				'stream'  => array(
					'Format profile' => '*/^Advanced\s*Simple@L[0-5]/i',
				),
			),
		);
		$overwrite_taxonomies['cutemi_video_tech'][]  = array(
			'term'          => 'cutemi-video-tech-mpeg-4-nero-digital-avc',
			'stream_type'   => 'videos',
			'only_on_empty' => false,
			'conditionals'  => array(
				'general' => array(
					'Codec ID' => '*@/(NDXC|NDXH|NDXM|NDXP|NDXS)\)@i',
				),
				'stream'  => array(
					'Format' => 'AVC',
				),
			),
		);
		$overwrite_taxonomies['cutemi_video_tech'][]  = array(
			'term'          => 'cutemi-video-tech-mvc',
			'stream_type'   => 'videos',
			'only_on_empty' => false,
			'conditionals'  => array(
				'stream' => array(
					'Format profile' => '*/(Multiview High|Stereo High)@/i',
					'Format'         => 'AVC',
				),
			),
		);
		$overwrite_taxonomies['cutemi_video_tech'][]  = array(
			'term'          => 'cutemi-video-tech-xvid',
			'stream_type'   => 'videos',
			'only_on_empty' => false,
			'conditionals'  => array(
				'stream' => array(
					'Writing library' => '*/^XviD /i',
					'Format profile'  => '*/^Advanced\s*Simple@L[0-5]/i',
				),
			),
		);
		$overwrite_taxonomies['cutemi_video_tech'][]  = array(
			'term'          => 'cutemi-video-tech-mpeg-4-asp',
			'stream_type'   => 'videos',
			'only_on_empty' => true,
			'conditionals'  => array(
				'stream' => array(
					'Format profile' => '*/^Advanced\s*Simple@L[0-5]/i',
				),
			),
		);
		$overwrite_taxonomies['cutemi_video_tech'][]  = array(
			'term'          => 'cutemi-video-tech-mpeg-4-sp',
			'stream_type'   => 'videos',
			'only_on_empty' => true,
			'conditionals'  => array(
				'stream' => array(
					'Format profile' => '*/^Simple@L[0-6][ab]?/i',
				),
			),
		);
		$overwrite_taxonomies['cutemi_video_tech'][]  = array(
			'term'          => 'cutemi-video-tech-mpeg-1',
			'stream_type'   => 'videos',
			'only_on_empty' => true,
			'conditionals'  => array(
				'stream' => array(
					'Format'         => 'mpeg video',
					'Format version' => 'Version 1',
				),
			),
		);
		$overwrite_taxonomies['cutemi_video_tech'][]  = array(
			'term'          => 'cutemi-video-tech-mpeg-2',
			'stream_type'   => 'videos',
			'only_on_empty' => true,
			'conditionals'  => array(
				'stream' => array(
					'Format'         => 'mpeg video',
					'Format version' => 'Version 2',
				),
			),
		);
		$overwrite_taxonomies['cutemi_audio_tech'][]  = array(
			'term'          => 'cutemi-audio-tech-mp2',
			'stream_type'   => 'audios',
			'only_on_empty' => true,
			'conditionals'  => array(
				'stream' => array(
					'Format'         => 'MPEG Audio',
					'Format profile' => 'Layer 2',
				),
			),
		);
		$overwrite_taxonomies['cutemi_audio_tech'][]  = array(
			'term'          => 'cutemi-audio-tech-mp3',
			'stream_type'   => 'audios',
			'only_on_empty' => true,
			'conditionals'  => array(
				'stream' => array(
					'Format'         => 'MPEG Audio',
					'Format profile' => 'Layer 3',
				),
			),
		);
		$overwrite_taxonomies['cutemi_text_format'][] = array(
			'term'          => 'cutemi-text-format-dvd-subtitle',
			'stream_type'   => 'texts',
			'only_on_empty' => true,
			'conditionals'  => array(
				'stream' => array(
					'Format'      => 'RLE',
					'Muxing mode' => 'DVD-Video',
				),
			),
		);

		$overwrite_taxonomies['cutemi_audio_bitrate_mode'][] = array(
			'term'          => 'cutemi-audio-bitrate-mode-vbr',
			'stream_type'   => 'audios',
			'only_on_empty' => true,
			'conditionals'  => array(
				'stream' => array(
					'Bit rate mode' => 'Variable',
				),
			),
		);
		$overwrite_taxonomies['cutemi_audio_bitrate_mode'][] = array(
			'term'          => 'cutemi-audio-bitrate-mode-cbr',
			'stream_type'   => 'audios',
			'only_on_empty' => true,
			'conditionals'  => array(
				'stream' => array(
					'Bit rate mode' => 'Constant',
				),
			),
		);

		$overwrite_taxonomies['cutemi_video_bitrate_mode'][] = array(
			'term'          => 'cutemi-video-bitrate-mode-vbr',
			'stream_type'   => 'videos',
			'only_on_empty' => true,
			'conditionals'  => array(
				'stream' => array(
					'Bit rate mode' => 'Variable',
				),
			),
		);
		$overwrite_taxonomies['cutemi_video_bitrate_mode'][] = array(
			'term'          => 'cutemi-video-bitrate-mode-cbr',
			'stream_type'   => 'videos',
			'only_on_empty' => true,
			'conditionals'  => array(
				'stream' => array(
					'Bit rate mode' => 'Constant',
				),
			),
		);

		$overwrite_taxonomies = apply_filters( 'cutemi_mediainfo_extract_overwrite_taxonomies', $overwrite_taxonomies );
		foreach ( $overwrite_taxonomies as $taxonomy => $blocks ) {
			foreach ( $blocks as $block ) {
				if ( ! isset( $streams[ $block['stream_type'] ] ) ) {
					continue;
				}
				foreach ( $streams[ $block['stream_type'] ] as $stream_idx => $stream ) {
					$term_slug = $block['term'];
					if ( ! empty( $block['only_on_empty'] ) && ! empty( $stream['taxs'][ $taxonomy ] ) ) {
						continue;
					}
					foreach ( $block['conditionals'] as $stream_tye => $conditional ) {
						if ( 'stream' === $stream_tye ) {
							if ( ! $mi->properties_match( $stream, $block['conditionals']['stream'] ) ) {
								continue 2;
							}
						} else {
							//First stream of type
							if (
								empty( $streams[ $stream_tye ] ) ||
								! $mi->properties_match( $streams[ $stream_tye ][0], $conditional )
							) {
								continue 2;
							}
						}
					}
					if ( cutemi_term_exists_and_enabled( $term_slug, $taxonomy ) ) {
						$streams[ $block['stream_type'] ][ $stream_idx ]['taxs'][ $taxonomy ] = $term_slug;
					}
				}
			}
		}

		//Set format from extension
		if ( ! empty( $streams['general'] ) ) {
			$tax  = 'cutemi_file_format';
			$name = $mi->get_property( $streams['general'][0], 'Complete name' );
			if ( is_string( $name ) ) {
				if (
					preg_match(
						'/\.(3gp|3g2|flv|f4v|evo|m2ts|m4v|mcf|mkv|mov|mp4|mpeg|mxf' .
									'|ogg|ps|rmvb|ts|vob|webm|wmv|wtv)$/i',
						$name,
						$match
					)
				) {
					$term_slug = 'cutemi-file-format-' . strtolower( $match[1] );
					if ( cutemi_term_exists_and_enabled( $term_slug, $tax ) ) {
						$streams['general'][0]['taxs'][ $tax ] = $term_slug;
					}
				} elseif ( ! isset( $streams['general'][0]['taxs'][ $tax ] ) ) {
					if ( preg_match( '/\.(divx|avi)$/i', $name, $match ) ) {
						$term_slug = 'cutemi-file-format-' . strtolower( $match[1] );
						if ( cutemi_term_exists_and_enabled( $term_slug, $tax ) ) {
							$streams['general'][0]['taxs'][ $tax ] = $term_slug;
						}
					}
					$others = array(
						'cutemi-file-format-ps'   => 'm2p',
						'cutemi-file-format-wmv'  => 'asf',
						'cutemi-file-format-rmvb' => 'rm',
						'cutemi-file-format-ts'   => 'tsa|tsv',
						'cutemi-file-format-ogg'  => 'ogg|ogv|ogx|ogm|spx|opus',
					);
					foreach ( $others as $term_slug => $reg ) {
						if ( preg_match( '/\.(' . $reg . ')$/i', $name, $match ) ) {
							if ( cutemi_term_exists_and_enabled( $term_slug, $tax ) ) {
								$streams['general'][0]['taxs'][ $tax ] = $term_slug;
							}
						}
					}
				}
			}
		}

		//If not set video/audio/text tech search on vlc fourcc
		$streams_types = array(
			'videos' => 'cutemi_video_tech',
			'audios' => 'cutemi_audio_tech',
			'texts'  => 'cutemi_text_format',
		);
		foreach ( $streams_types as $stream_type => $stream_type_taxonomy ) {
			//Extracted From: https://videolan.videolan.me/vlc/fourcc__list_8h_source.html
			foreach ( $streams[ $stream_type ] as $i => $stream ) {
				$tax = $stream_type_taxonomy;
				if ( empty( $stream['taxs'][ $tax ] ) ) {
					$term_slug = $this->search_on_fourcc( $mi, $stream, $stream_type, $tax );
					if ( ! empty( $term_slug ) && cutemi_term_exists_and_enabled( $term_slug, $tax ) ) {
						$streams[ $stream_type ][ $i ]['taxs'][ $tax ] = $term_slug;
					}
				}
			}
		}

		return $streams;
	}


	/** @noinspection PhpIncludeInspection */
	private function search_on_fourcc( $mi, $stream, $stream_type, $tax ) {
		include CUTE_MEDIAINFO_DIR . '/includes/mediainfo/vlc-fourcc/' . $stream_type . '-codecs-main.php';
		include CUTE_MEDIAINFO_DIR . '/includes/mediainfo/vlc-fourcc/' . $stream_type . '-codecs-childs.php';
		include CUTE_MEDIAINFO_DIR . '/includes/mediainfo/vlc-fourcc/' . $stream_type . '-codecs-equivalents.php';
		$properties = array(
			'Codec ID',
			'Codec ID/Info',
			'Codec ID/Hint',
			'Format',
			'Format/Info',
		);
		foreach ( $properties as $property ) {
			$fourcc = $mi->get_property( $stream, $property );
			if ( empty( $fourcc ) ) {
				continue;
			}
			$main_class = '';
			//First search on child
			if ( isset( $fcc[ $stream_type ]['childs_eq'][ $fourcc ] ) ) {
				return $fcc[ $stream_type ]['childs_eq'][ $fourcc ];
			}
			if ( isset( $fcc[ $stream_type ]['childs'][ $fourcc ] ) ) {
				$found = CUTEMI_Mediainfo_Data::search_terms_match(
					$fcc[ $stream_type ]['childs'][ $fourcc ]['desc'],
					$tax
				);
				if ( ! empty( $found ) ) {
					return CUTEMI_Mediainfo_Data::get_better_match( $found );
				}
				$main_class = $fcc[ $stream_type ]['childs'][ $fourcc ]['class'];
			}

			//Now on main
			if ( empty( $main_class ) ) {
				if ( isset( $fcc[ $stream_type ]['main'][ $fourcc ] ) ) {
					$main_class = $fourcc;
				} else {
					continue;
				}
			}
			if ( isset( $fcc[ $stream_type ]['main_eq'][ $main_class ] ) ) {
				return $fcc[ $stream_type ]['main_eq'][ $main_class ];
			}
			if ( isset( $fcc[ $stream_type ]['main'][ $main_class ] ) ) {
				$found = CUTEMI_Mediainfo_Data::search_terms_match(
					$fcc[ $stream_type ]['main'][ $main_class ],
					$tax
				);
				if ( ! empty( $found ) ) {
					return CUTEMI_Mediainfo_Data::get_better_match( $found );
				}
			}
		}

		return false;
	}

	/**
	 * @param $stream array
	 * @param $mi CUTEMI_Mediainfo
	 *
	 * @return mixed
	 */
	public function pre_stream( $stream, $mi ) {
		if ( 'audio' === $stream['type'] ) {
			$stream = $this->pre_stream_audio( $stream, $mi );
		}

		return $stream;
	}

	/**
	 * @param $stream array
	 * @param $mi CUTEMI_Mediainfo
	 *
	 * @return mixed
	 */
	private function pre_stream_audio( $stream, $mi ) {
		$layout = $mi->get_property( $stream, 'Channel layout' );
		if ( ! empty( $layout ) ) {
			$lfe = '0';
			if ( strpos( $layout, 'LFE' ) !== false ) {
				$lfe = '1';
			}
			$purge_chrs = array( 'LFE2', 'LFE', 'Rear:', 'Side:', 'Front:', 'Back:', '/' );
			foreach ( $purge_chrs as $str ) {
				$layout = str_replace( $str, '', $layout );
			}
			//https://mediaarea.net/AudioChannelLayout
			$outputs = array(
				'Lscr',
				'Rscr',
				'Bfc',
				'Bfl',
				'Bfr',
				'Lss',
				'Rss',
				'Lsd',
				'Rsd',
				'Tfc',
				'Vhl',
				'Vhr',
				'Tfl',
				'Tfr',
				'Tsl',
				'Tsr',
				'Lvs',
				'Rvs',
				'Tbl',
				'Tbr',
				'Tbc',
				'Lc',
				'Rc',
				'Ls',
				'Rs',
				'Lb',
				'Rb',
				'Cb',
				'Tc',
				'Lt',
				'Rt',
				'R',
				'C',
				'L',
				'M',
			);
			$main    = 0;
			foreach ( $outputs as $str ) {
				$layout = str_replace( $str, '', $layout, $count );
				$main  += $count;
			}
			$tax       = 'cutemi_audio_channels';
			$term_slug = 'cutemi-audio-channels-' . $main . '-' . $lfe;

			if ( cutemi_term_exists_and_enabled( $term_slug, $tax ) ) {
				$stream['taxs'][ $tax ] = $term_slug;
			}
		} else {
			$channels = strtolower( $mi->get_property( $stream, 'Channel(s)' ) );
			$map      = array(
				'1 channel'   => 'cutemi-audio-channels-1-0',
				'2 channels'  => 'cutemi-audio-channels-2-0',
				'3 channels'  => 'cutemi-audio-channels-2-1',
				'4 channels'  => 'cutemi-audio-channels-3-1',
				'5 channels'  => 'cutemi-audio-channels-4-1',
				'6 channels'  => 'cutemi-audio-channels-5-1',
				'7 channels'  => 'cutemi-audio-channels-6-1',
				'8 channels'  => 'cutemi-audio-channels-7-1',
				'9 channels'  => 'cutemi-audio-channels-8-1',
				'10 channels' => 'cutemi-audio-channels-9-1',
				'11 channels' => 'cutemi-audio-channels-10-1',
				'12 channels' => 'cutemi-audio-channels-11-1',
			);
			if ( isset( $map[ $channels ] ) ) {
				$tax       = 'cutemi_audio_channels';
				$term_slug = $map[ $channels ];

				if ( cutemi_term_exists_and_enabled( $term_slug, $tax ) ) {
					$stream['taxs'][ $tax ] = $term_slug;
				}
			}
		}

		return $stream;
	}

	/**
	 * @param $stream array
	 * @param $mi CUTEMI_Mediainfo
	 *
	 * @return mixed
	 */
	public function end_stream( $stream, $mi ) {
		if ( 'general' === $stream['type'] ) {
			$stream = $this->end_stream_general( $stream, $mi );
		}

		if ( 'video' === $stream['type'] ) {
			$stream = $this->end_stream_video( $stream, $mi );
		}

		if ( 'audio' === $stream['type'] ) {
			$stream = $this->end_stream_audio( $stream, $mi );
		}

		return $stream;
	}

	/**
	 * @param $stream array
	 * @param $mi CUTEMI_Mediainfo
	 *
	 * @return mixed
	 */
	private function end_stream_general( $stream, $mi ) {
		if ( isset( $stream['vals']['size'] ) && is_string( $stream['vals']['size'] ) ) {
			$stream['vals']['size'] = cutemi_to_byte_size( $stream['vals']['size'] );
		}
		if ( isset( $stream['vals']['video_date'] ) && is_string( $stream['vals']['video_date'] ) ) {
			$timestamp = strtotime( $stream['vals']['video_date'] );
			if ( false === $timestamp ) {
				unset( $stream['vals']['video_date'] ); //no valid date
			} else {
				$stream['vals']['video_date'] = gmdate( 'Y-m-d', $timestamp );
			}
		}

		if ( isset( $stream['vals']['duration'] ) && is_string( $stream['vals']['duration'] ) ) {
			$time = cutemi_duration_to_seconds( $stream['vals']['duration'] );
			if ( false !== $time ) {
				$stream['vals']['duration'] = $time;
			}
		}

		return $stream;
	}

	/**
	 * @param $stream array
	 * @param $mi CUTEMI_Mediainfo
	 *
	 * @return mixed
	 */
	private function end_stream_video( $stream, $mi ) {
		$tax = 'cutemi_video_resolution';
		if ( ! isset( $stream['taxs'][ $tax ] ) ) {
			$height = $mi->get_property( $stream, 'Height' );
			$width  = $mi->get_property( $stream, 'Width' );
			if ( empty( $width ) || empty( $height ) ) {
				return $stream;
			}

			$video           = array(
				'Height' => intval( str_replace( array( ' ', 'pixels' ), '', $height ) ),
				'Width'  => intval( str_replace( array( ' ', 'pixels' ), '', $width ) ),
			);
			$max_resolutions = array(
				'cutemi-video-resolution-ld'      => array(
					'Height' => 360,
					'Width'  => 640,
				),
				'cutemi-video-resolution-sd'      => array( //QHD 960 x 540
					'Height' => 576,
					'Width'  => 960,
				),
				'cutemi-video-resolution-hd'      => array(
					'Height' => 720,
					'Width'  => 1280,
				),
				'cutemi-video-resolution-full-hd' => array(
					'Height' => 1080,
					'Width'  => 1920,
				),
				'cutemi-video-resolution-full-2k' => array(
					'Width'  => 2560,
					'Height' => 1440,
				),
				'cutemi-video-resolution-4k'      => array(
					'Width'  => 4096,
					'Height' => 2160,
				),
				'cutemi-video-resolution-5k'      => array(
					'Width'  => 5499,
					'Height' => 4320,
				),
				'cutemi-video-resolution-6k'      => array(
					'Width'  => 6499,
					'Height' => 4320,
				),
				'cutemi-video-resolution-8k'      => array(
					'Width'  => 8192,
					'Height' => 4320,
				),
			);

			$max_resolutions = apply_filters( 'cutemi_mediainfo_extract_max_resolutions', $max_resolutions );

			foreach ( $max_resolutions as $term_slug => $dims ) {
				foreach ( $dims as $prop => $max ) {
					if ( $max < $video[ $prop ] ) {
						continue 2;
					}
				}
				//OK!
				if ( cutemi_term_exists_and_enabled( $term_slug, $tax ) ) {
					$stream['taxs'][ $tax ] = $term_slug;
					break;
				}
			}
		}

		$tax = 'cutemi_video_bitrate';

		return $this->add_bitrate( $mi, $stream, $tax );
	}

	/**
	 * @param $stream array
	 * @param $mi CUTEMI_Mediainfo
	 *
	 * @return mixed
	 */
	private function end_stream_audio( $stream, $mi ) {
		$tax = 'cutemi_audio_bitrate';

		return $this->add_bitrate( $mi, $stream, $tax );
	}

	/**
	 * Add video or audio bitrate
	 *
	 * @param $mi
	 * @param $stream
	 * @param $tax
	 *
	 * @return mixed
	 */
	private function add_bitrate( $mi, $stream, $tax ) {
		if ( ! isset( $stream['taxs'][ $tax ] ) ) {
			$bitrate = str_replace( ' ', '', $mi->get_property( $stream, 'Bit rate' ) );
			if ( preg_match( '@(^[0-9.]+)\s*(kbps|Mbps|kb/s|Mb/s)@i', $bitrate, $matches ) ) {
				$val_number = floatval( $matches[1] );
				$val_unit   = $matches[2];
				if ( strtolower( $val_unit ) === 'mbps' || strtolower( $val_unit ) === 'mb/s' ) {
					$val_number = $val_number * 1000;
				}
				$terms         = get_terms(
					array(
						'taxonomy'   => $tax,
						'hide_empty' => false,
					)
				);
				$min_diff      = 9999999999;
				$min_diff_slug = '';
				foreach ( $terms as $term ) {
					if ( preg_match( '@(^[0-9.]+)\s*(kbps|Mbps|kb/s|Mb/s)@i', $term->name, $matches ) ) {
						$number = floatval( $matches[1] );
						$unit   = $matches[2];
						if ( strtolower( $unit ) === 'mbps' || strtolower( $unit ) === 'mb/s' ) {
							$number = $number * 1000;
						}
						$diff = abs( $val_number - $number );
						if ( $diff < $min_diff ) {
							$min_diff      = $diff;
							$min_diff_slug = $term->slug;
						}
					}
				}
				if ( ! empty( $min_diff_slug ) ) {
					if ( cutemi_term_exists_and_enabled( $min_diff_slug, $tax ) ) {
						$stream['taxs'][ $tax ] = $min_diff_slug;
					}
				}
			}
		}

		return $stream;
	}

}

new CUTEMI_Mediainfo_Filters();
