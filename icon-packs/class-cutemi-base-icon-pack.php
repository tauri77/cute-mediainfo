<?php

class CUTEMI_Base_Icon_Pack {

	public $misc_icons = array();

	public $terms_icons = array();

	public $base_url;

	public $name;
	public $label;

	public function __construct( $name = false ) {

		if ( false !== $name ) {
			$this->name = $name;
		}

		add_action( 'plugins_loaded', array( $this, '_init' ) );

		add_filter( 'cutemi_term_icon_' . $this->name, array( $this, 'term_icon' ), 10, 3 );
		add_filter( 'cutemi_misc_icon_' . $this->name, array( $this, 'misc_icon' ), 10, 2 );

		add_filter( 'cutemi_term_icon_val_' . $this->name, array( $this, 'term_icon_val' ), 10, 3 );
		add_filter( 'cutemi_misc_icon_val_' . $this->name, array( $this, 'misc_icon_val' ), 10, 2 );

		add_filter( 'cutemi_expo_list_' . $this->name, array( $this, 'expo_list' ), 10, 2 );

		add_filter( 'cutemi_available_icon_packs', array( $this, 'add_pack_option' ), 10, 1 );
	}

	public function _init() {
		$this->terms_icons = apply_filters( 'cutemi_terms_icons', $this->terms_icons, $this->name );
		$this->misc_icons  = apply_filters( 'cutemi_misc_icons', $this->misc_icons, $this->name );
	}

	public function add_pack_option( $icon_packs ) {
		$icon_packs[ $this->name ] = array( 'name' => $this->label );

		return $icon_packs;
	}

	public function term_icon( $icon_url, $taxonomy, $term_slug ) {
		return $this->get_term_icon( $taxonomy, $term_slug );
	}

	public function get_term_icon( $taxonomy, $term_slug ) {
		$val = $this->get_term_icon_val( $taxonomy, $term_slug );
		if ( false !== $val && './' === substr( $val, 0, '2' ) ) {
			$val = $this->base_url . substr( $val, 1 );
		}

		return $val;
	}

	public function get_term_icon_val( $taxonomy, $term_slug ) {
		if ( isset( $this->terms_icons[ $taxonomy ] ) && isset( $this->terms_icons[ $taxonomy ][ $term_slug ] ) ) {
			return $this->terms_icons[ $taxonomy ][ $term_slug ];
		}

		return false;
	}

	public function misc_icon( $icon_url, $icon_name ) {
		return $this->get_misc_icon( $icon_name );
	}

	public function get_misc_icon( $icon ) {
		$val = $this->get_misc_icon_val( $icon );
		if ( false !== $val && './' === substr( $val, 0, '2' ) ) {
			$val = $this->base_url . substr( $val, 1 );
		}

		return $val;
	}

	public function get_misc_icon_val( $icon ) {
		if ( isset( $this->misc_icons[ $icon ] ) ) {
			return $this->misc_icons[ $icon ];
		}

		return false;
	}

	public function term_icon_val( $icon_url, $taxonomy, $term_slug ) {
		return $this->get_term_icon_val( $taxonomy, $term_slug );
	}

	public function misc_icon_val( $icon_url, $icon_name ) {
		return $this->get_misc_icon_val( $icon_name );
	}

	public function expo_list() {
		$tax_list = array(
			'cutemi_file_format'        => array(
				'cutemi-file-format-avi',
				'cutemi-file-format-mp4',
				'cutemi-file-format-mkv',
			),
			'cutemi_video_resolution'   => array(
				'cutemi-video-resolution-hd',
				'cutemi-video-resolution-full-hd',
				'cutemi-video-resolution-4k',
			),
			'cutemi_video_tech'         => array(
				'cutemi-video-tech-divx',
				'cutemi-video-tech-x264',
				'cutemi-video-tech-rv60',
			),
			'cutemi_video_bitrate'      => array(
				'cutemi-video-bitrate-500-k',
				'cutemi-video-bitrate-2-m',
				'cutemi-video-bitrate-50-m',
			),
			'cutemi_video_bitrate_mode' => array(
				'cutemi-video-bitrate-mode-cbr',
				'cutemi-video-bitrate-mode-vbr',
				'cutemi-video-bitrate-mode-abr',
			),
			'cutemi_audio_langs'        => array(
				'cutemi-audio-lang-en',
				'cutemi-audio-lang-pt-pt',
				'cutemi-audio-lang-es-419',
			),
			'cutemi_audio_tech'         => array(
				'cutemi-audio-tech-dolby-digital-plus-atmos',
				'cutemi-audio-tech-dts-hd-ma',
				'cutemi-audio-tech-mp3',
			),
			'cutemi_audio_channels'     => array(
				'cutemi-audio-channels-2-0',
				'cutemi-audio-channels-2-1',
				'cutemi-audio-channels-5-1',
			),
			'cutemi_audio_bitrate'      => array(
				'cutemi-audio-bitrate-128-k',
				'cutemi-audio-bitrate-320-k',
				'cutemi-audio-bitrate-1-5-m',
			),
			'cutemi_audio_bitrate_mode' => array(
				'cutemi-audio-bitrate-mode-cbr',
				'cutemi-audio-bitrate-mode-vbr',
				'cutemi-audio-bitrate-mode-abr',
			),
			'cutemi_text_format'        => array(
				'cutemi-text-format-srt',
				'cutemi-text-format-ass',
				'cutemi-text-format-webvtt',
			),
			'cutemi_text_type'          => array(
				'cutemi-text-type-forced',
				'cutemi-text-type-sdh',
			),
			'cutemi_site_source'        => array(
				'cutemi-link-source-1fichier',
				'cutemi-link-source-mediafire',
				'cutemi-link-source-gdrive',
			),
		);

		$res = array();

		foreach ( $tax_list as $taxonomy => $terms ) {
			foreach ( $terms as $term_slug ) {
				$url = $this->get_term_icon( $taxonomy, $term_slug );
				if ( ! empty( $url ) ) {
					if ( ! isset( $res[ $taxonomy ] ) ) {
						$res[ $taxonomy ] = array();
					}
					$res[ $taxonomy ][] = $url;
				}
			}
		}

		return $res;
	}

}
