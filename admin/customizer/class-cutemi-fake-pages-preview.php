<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

if ( is_customize_preview() ) {

	class CUTEMI_Fake_Pages_Preview {

		public function __construct() {
			add_filter( 'the_posts', array( $this, 'fake_pages' ) );
		}

		private static function get_fake_pages() {
			$fake_pages['cutemi-fake-preview-page'] = array(
				'title'   => 'Cute MediaInfo Preview',
				'content' => 'cutemi_replace_content',
			);

			return $fake_pages;
		}

		public function page_template( $template ) {
			$new_template = locate_template( array( 'page.php', 'singular.php', 'post.php' ) );
			if ( '' !== $new_template ) {
				return $new_template;
			}

			return $template;
		}

		public function fake_pages( $posts ) {
			global $wp, $wp_query;
			//Only load if cutemi_profile_force is defined
			if ( ! get_query_var( 'cutemi_profile_force' ) ) {
				return $posts;
			}
			$fake_pages       = self::get_fake_pages();
			$fake_pages_slugs = array();
			foreach ( $fake_pages as $slug => $fp ) {
				$fake_pages_slugs[] = $slug;
			}
			if (
					true === in_array( strtolower( $wp->request ), $fake_pages_slugs, true ) ||
					(
						true === isset( $wp->query_vars['page_id'] ) &&
						true === in_array( strtolower( $wp->query_vars['page_id'] ), $fake_pages_slugs, true )
					)
			) {
				add_filter( 'template_include', array( $this, 'page_template' ), 99 );
				add_filter( 'the_content', array( $this, 'cutemi_customizer_fake_content' ), 10, 99 );

				if ( true === in_array( strtolower( $wp->request ), $fake_pages_slugs, true ) ) {
					$fake_page = strtolower( $wp->request );
				} else {
					$fake_page = strtolower( $wp->query_vars['page_id'] );
				}
				$posts                  = null;
				$posts[]                = self::create_fake_page( $fake_page, $fake_pages[ $fake_page ] );
				$wp_query->is_page      = true;
				$wp_query->is_singular  = true;
				$wp_query->is_home      = false;
				$wp_query->is_archive   = false;
				$wp_query->is_category  = false;
				$wp_query->is_fake_page = true;
				$wp_query->fake_page    = $wp->request;
				//Longer permalink structures may not match the fake post slug and cause a 404 error so we catch the error here
				unset( $wp_query->query['error'] );
				$wp_query->query_vars['error'] = '';
				$wp_query->is_404              = false;
			}

			return $posts;
		}

		private static function create_fake_page( $pagename, $page ) {
			$post                 = new stdClass();
			$post->post_author    = 1;
			$post->post_name      = $pagename;
			$post->guid           = get_bloginfo( 'wpurl' ) . '/' . $pagename;
			$post->post_title     = $page['title'];
			$post->post_content   = $page['content'];
			$post->ID             = - 1;
			$post->post_status    = 'static';
			$post->comment_status = 'closed';
			$post->ping_status    = 'closed';
			$post->comment_count  = 0;
			$post->post_date      = current_time( 'mysql' );
			$post->post_date_gmt  = current_time( 'mysql', 1 );

			return $post;
		}

		public function cutemi_customizer_fake_content( $content ) {

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
					'text' => '31771852',
					'desc' => '',
				),
				'duration'     => array(
					'img'  => '',
					'text' => '47',
					'desc' => '',
				),
				'date'         => array(
					'img'  => '',
					'text' => '20100821',
					'desc' => '',
				),
				'desc'         => array(
					'img'  => '',
					'text' => '(c) copyright 2006, Blender Foundation / Netherlands Media Art Institute / www.elephantsdream.org',
					'desc' => '',
				),
				'mediainfo'    => array(
					'img'  => '',
					'text' => 'General
Unique ID                                : 209111522552945927144880130529875262896 (0x9D516A0F927A12D286E1502D23D0FDB0)
Complete name                            : Elephant Dreams Sample.mp4
Format                                   : MP4
File size                                : 30.3 MiB
Duration                                 : 46 s 665 ms
Overall bit rate                         : 5 445 kb/s
Movie name                               : Elephant Dreams Sample
Released date                            : 2010
Encoded date                             : UTC 2010-08-21 18:06:43

Video
ID                                       : 1
Format                                   : AVC
Format/Info                              : Advanced Video Codec
Format profile                           : Main@L3.1
Format settings                          : 2 Ref Frames
Format settings, CABAC                   : No
Format settings, Reference frames        : 2 frames
Format settings, GOP                     : M=2, N=24
Codec ID                                 : V_MPEG4/ISO/AVC
Duration                                 : 46 s 667 ms
Width                                    : 1 024 pixels
Height                                   : 576 pixels
Display aspect ratio                     : 16:9
Frame rate mode                          : Constant
Frame rate                               : 24.000 FPS
Color space                              : YUV
Chroma subsampling                       : 4:2:0
Bit depth                                : 8 bits
Scan type                                : Progressive
Default                                  : Yes
Forced                                   : No
Color range                              : Limited
Color primaries                          : BT.601 NTSC
Transfer characteristics                 : BT.709
Matrix coefficients                      : BT.601

Audio #1
ID                                       : 2
Format                                   : AAC LC
Format/Info                              : Advanced Audio Codec Low Complexity
Codec ID                                 : 2 / A_AAC-2
Duration                                 : 46 s 665 ms
Channel(s)                               : 2 channels
Channel layout                           : L R
Sampling rate                            : 48.0 kHz
Frame rate                               : 46.875 FPS (1024 SPF)
Compression mode                         : Lossy
Delay relative to video                  : 12 ms
Default                                  : Yes
Forced                                   : No

Audio #2
ID                                       : 10
Format                                   : AAC LC
Format/Info                              : Advanced Audio Codec Low Complexity
Codec ID                                 : 2 / A_AAC-2
Duration                                 : 46 s 665 ms
Channel(s)                               : 1 channel
Channel layout                           : C
Sampling rate                            : 22.05 kHz
Frame rate                               : 21.533 FPS (1024 SPF)
Compression mode                         : Lossy
Delay relative to video                  : 9 ms
Title                                    : Commentary
Language                                 : English
Default                                  : No
Forced                                   : No

Text #1
ID                                       : 3
Format                                   : UTF-8
Codec ID                                 : S_TEXT/UTF8
Codec ID/Info                            : UTF-8 Plain Text
Language                                 : English
Default                                  : Yes
Forced                                   : No

Text #2
ID                                       : 4
Format                                   : UTF-8
Codec ID                                 : S_TEXT/UTF8
Codec ID/Info                            : UTF-8 Plain Text
Language                                 : Hungarian
Default                                  : No
Forced                                   : No

Text #3
ID                                       : 5
Format                                   : UTF-8
Codec ID                                 : S_TEXT/UTF8
Codec ID/Info                            : UTF-8 Plain Text
Language                                 : German
Default                                  : No
Forced                                   : No

Text #4
ID                                       : 6
Format                                   : UTF-8
Codec ID                                 : S_TEXT/UTF8
Codec ID/Info                            : UTF-8 Plain Text
Language                                 : French
Default                                  : No
Forced                                   : No

Text #5
ID                                       : 8
Format                                   : UTF-8
Codec ID                                 : S_TEXT/UTF8
Codec ID/Info                            : UTF-8 Plain Text
Language                                 : Spanish
Default                                  : No
Forced                                   : No

Text #6
ID                                       : 9
Format                                   : UTF-8
Codec ID                                 : S_TEXT/UTF8
Codec ID/Info                            : UTF-8 Plain Text
Language                                 : Italian
Default                                  : No
Forced                                   : No

Text #7
ID                                       : 11
Format                                   : UTF-8
Codec ID                                 : S_TEXT/UTF8
Codec ID/Info                            : UTF-8 Plain Text
Language                                 : Japanese
Default                                  : No
Forced                                   : No',
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
					1 => array(
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
							'img'  => $base_url . 'icon-packs/images/default/audio-channels/1.0.svg',
							'text' => '1.0',
							'desc' => 'Mono',
							'slug' => 'cutemi-audio-channels-1-0',
						),
						'bitrate'      => array(
							'img'  => $base_url . 'icon-packs/images/default/audio-bitrate/192_kbps.svg',
							'text' => '192 kbps',
							'desc' => 'Approximately 192 kbps',
							'slug' => 'cutemi-audio-bitrate-192-k',
						),
						'bitrate_mode' => array(
							'img'  => $base_url . 'icon-packs/images/default/audio-bitrate-mode/cbr.svg',
							'text' => 'CBR',
							'desc' => 'Constant Bitrate',
							'slug' => 'cutemi-audio-bitrate-mode-cbr',
						),
					),
				),
				'audios-count' => 2,
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
					2 => array(
						'lang'   => array(
							'img'  => $base_url . 'icon-packs/images/default/langs/de.svg',
							'text' => 'German',
							'desc' => '',
							'slug' => 'cutemi-text-lang-de',
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
					3 => array(
						'lang'   => array(
							'img'  => $base_url . 'icon-packs/images/default/langs/fr.svg',
							'text' => 'French',
							'desc' => '',
							'slug' => 'cutemi-text-lang-fr',
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
					4 => array(
						'lang'   => array(
							'img'  => $base_url . 'icon-packs/images/default/langs/es.svg',
							'text' => 'Spanish',
							'desc' => '',
							'slug' => 'cutemi-text-lang-es',
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
					5 => array(
						'lang'   => array(
							'img'  => $base_url . 'icon-packs/images/default/langs/c-it.svg',
							'text' => 'Italian',
							'desc' => '',
							'slug' => 'cutemi-text-lang-it',
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
					6 => array(
						'lang'   => array(
							'img'  => $base_url . 'icon-packs/images/default/langs/c-jp.svg',
							'text' => 'Japanese',
							'desc' => '',
							'slug' => 'cutemi-text-lang-ja',
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
				'texts-count'  => 7,
				'links'        => array(
					0 => array(
						'source'      => array(
							'img'  => $base_url . 'icon-packs/images/default/link-source/1fichier.svg',
							'text' => '1fichier',
							'desc' => '',
							'slug' => 'cutemi-link-source-1fichier',
						),
						'part'        => array(
							'img'  => '',
							'text' => '1',
							'desc' => '',
						),
						'size'        => array(
							'img'  => '',
							'text' => '20000000',
							'desc' => '',
						),
						'external_id' => array(
							'img'  => '',
							'text' => 'https://1fichier.com/?s9qdlx8ze8mhdezyh9yn',
							'desc' => '',
						),
					),
				),
				'links-count'  => 1,
			);

			$video = apply_filters( 'cutemi_customizer_fake_mediainfo', $video, $base_url );

			$profile = get_query_var( 'cutemi_profile_force' );
			if ( ! cutemi_is_valid_profile( $profile, true ) ) {
				return '<h3>Profile not found</h3>'; // Never for normal user, without translate
			}
			return cutemi_get_mediainfo_content( $video, $profile, false, true );
		}
	}

	new CUTEMI_Fake_Pages_Preview();
}
