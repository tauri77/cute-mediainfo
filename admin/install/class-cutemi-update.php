<?php

class CUTEMI_Update {

	public $errors;
	public $notices;

	private $install_terms_mode = 'filter';
	private $install_terms      = array(
		'cutemi_file_format'        => array(
			'cutemi-file-format-mp4',
			'cutemi-file-format-mkv',
			'cutemi-file-format-avi',
			'cutemi-file-format-wmv',
			'cutemi-file-format-mov',
		),
		'cutemi_video_resolution'   => array(
			'cutemi-video-resolution-4k',
			'cutemi-video-resolution-2k',
			'cutemi-video-resolution-full-hd',
			'cutemi-video-resolution-hd',
			'cutemi-video-resolution-sd',
			'cutemi-video-resolution-ld',
		),
		'cutemi_video_tech'         => array(
			'cutemi-video-tech-x264',
			'cutemi-video-tech-webp',
			'cutemi-video-tech-h265',
			'cutemi-video-tech-divx',
			'cutemi-video-tech-xvid',
		),
		'cutemi_video_bitrate'      => array(
			'cutemi-video-bitrate-250-k',
			'cutemi-video-bitrate-500-k',
			'cutemi-video-bitrate-1-m',
			'cutemi-video-bitrate-6-m',
			'cutemi-video-bitrate-10-m',
			'cutemi-video-bitrate-18-m',
			'cutemi-video-bitrate-25-m',
			'cutemi-video-bitrate-45-m',
			'cutemi-video-bitrate-100-m',
			'cutemi-video-bitrate-200-m',
		),
		'cutemi_video_bitrate_mode' => array(
			'cutemi-video-bitrate-mode-cbr',
			'cutemi-video-bitrate-mode-vbr',
			'cutemi-video-bitrate-mode-abr',
		),
		'cutemi_audio_langs'        => array(
			'cutemi-audio-lang-en',
			'cutemi-audio-lang-es',
			'cutemi-audio-lang-fr',
			'cutemi-audio-lang-it',
			'cutemi-audio-lang-zh',
		),
		'cutemi_audio_tech'         => array(
			'cutemi-audio-tech-aac',
			'cutemi-audio-tech-he-acc',
			'cutemi-audio-tech-dts-x',
			'cutemi-audio-tech-dts-hd-ma',
			'cutemi-audio-tech-dolby-ac-4',
			'cutemi-audio-tech-mp3',
		),
		'cutemi_audio_channels'     => array(
			'cutemi-audio-channels-7-1',
			'cutemi-audio-channels-5-1',
			'cutemi-audio-channels-2-1',
			'cutemi-audio-channels-2-0',
			'cutemi-audio-channels-1-0',
		),
		'cutemi_audio_bitrate'      => array(
			'cutemi-audio-bitrate-96-k',
			'cutemi-audio-bitrate-128-k',
			'cutemi-audio-bitrate-192-k',
			'cutemi-audio-bitrate-320-k',
			'cutemi-audio-bitrate-1-m',
			'cutemi-audio-bitrate-4-m',
			'cutemi-audio-bitrate-10-m',
			'cutemi-audio-bitrate-15-m',
			'cutemi-audio-bitrate-30-m',
			'cutemi-audio-bitrate-50-m',
		),
		'cutemi_audio_bitrate_mode' => array(
			'cutemi-audio-bitrate-mode-cbr',
			'cutemi-audio-bitrate-mode-vbr',
			'cutemi-audio-bitrate-mode-abr',
		),
		'cutemi_text_format'        => array(
			'cutemi-text-format-srt',
			'cutemi-text-format-ssa',
			'cutemi-text-format-ass',
			'cutemi-text-format-webvtt',
			'cutemi-text-format-mp4tt',
		),
		'cutemi_text_type'          => array(
			'cutemi-text-type-forced',
			'cutemi-text-type-sdh',
		),
		'cutemi_text_langs'         => array(
			'cutemi-text-lang-en',
			'cutemi-text-lang-es',
			'cutemi-text-lang-fr',
			'cutemi-text-lang-it',
			'cutemi-text-lang-zh',
		),
		'cutemi_site_source'        => array(
			'cutemi-link-source-generic',
			'cutemi-link-source-1fichier',
			'cutemi-link-source-mediafire',
			'cutemi-link-source-mega',
			'cutemi-link-source-amazon-drive',
			'cutemi-link-source-amazon-drive-ue',
		),
	);

	public function __construct() {
		$this->errors  = array();
		$this->notices = array();

		add_action( 'admin_notices', array( $this, 'notices' ) );
	}

	public function notices() {
		/* notice-error – notice-warning– notice-success – notice-info */
		foreach ( $this->errors as $message ) {
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( 'notice notice-error' ), esc_html( $message ) );
		}
		foreach ( $this->notices as $message ) {
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( 'notice notice-info' ), esc_html( $message ) );
		}
	}

	public function update() {
		$this->add_taxonomy_terms();
	}

	public function add_taxonomy_terms() {
		$cutemi_taxonomies = array();
		include CUTE_MEDIAINFO_DIR . '/admin/install/taxonomies/load-terms.php';
		$this->update_taxonomies_terms( $cutemi_taxonomies );
	}

	/**
	 * This uninstall existing terms that not listed and found on core terms
	 * @param $taxonomy
	 * @param $terms_slugs
	 */
	public function install_taxonomy_terms( $taxonomy, $terms_slugs ) {
		$cutemi_taxonomies = array();
		include CUTE_MEDIAINFO_DIR . '/admin/install/taxonomies/load-terms.php';
		$this->install_terms      = array( $taxonomy => $terms_slugs );
		$this->install_terms_mode = 'list';
		$this->update_taxonomies_terms( $cutemi_taxonomies );
	}

	public function update_taxonomies_terms( $cutemi_taxonomies ) {
		foreach ( $cutemi_taxonomies as $taxonomy => $terms ) {
			$install_tax_terms = isset( $this->install_terms[ $taxonomy ] ) ? $this->install_terms[ $taxonomy ] : false;
			if ( 'list' === $this->install_terms_mode && false === $install_tax_terms ) {
				continue;
			}
			$installed      = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'hide_empty' => false,
				)
			);
			$installed_slug = array();
			foreach ( $installed as $_term ) {
				$installed_slug[ $_term->slug ] = $_term;
			}

			foreach ( $terms as $t => $term ) {
				if ( 'list' === $this->install_terms_mode ) {
					//Only install taxonomy terms listed and delete others terms
					if ( ! in_array( $term['slug'], $install_tax_terms, true ) ) {
						if ( isset( $installed_slug[ $term['slug'] ] ) ) {
							wp_delete_term( $installed_slug[ $term['slug'] ]->term_id, $taxonomy );
							unset( $installed_slug[ $term['slug'] ] );
						}
						continue;
					}
				} elseif ( 'filter' === $this->install_terms_mode ) {
					//Install all taxonomy terms that not have filter defined
					if ( false !== $install_tax_terms && ! in_array( $term['slug'], $install_tax_terms, true ) ) {
						continue;
					}
				}
				if ( isset( $installed_slug[ $term['slug'] ] ) ) {
					//Exist update metadata
					$this->update_term_metas( $taxonomy, $installed_slug[ $term['slug'] ]->term_id, $term['metas'] );
				} else {
					//Not exist, create
					$args = array(
						'description' => $term['description'],
						'slug'        => $term['slug'],
					);
					if ( ! empty( $term['parent'] ) ) {
						if ( isset( $installed_slug[ $term['parent'] ] ) ) {
							$args['parent']        = $installed_slug[ $term['parent'] ]->term_id;
							$terms[ $t ]['parent'] = true; //mark as already parent, then dont update later
						}
					}
					$res = wp_insert_term( $term['name'], $taxonomy, $args );
					if ( ! is_wp_error( $res ) && is_array( $res ) ) {
						$installed_slug[ $term['slug'] ] = (object) $res;
						$this->add_term_metas( $taxonomy, $res['term_id'], $term['metas'] );
					} else {
						$this->error(
							sprintf(
								'Error to add term %s on %d: %s',
								$term['slug'],
								$taxonomy,
								$res->get_error_message()
							)
						);
					}
				}
			}
			//Set parents
			foreach ( $terms as $term ) {
				if (
					'filter' === $this->install_terms_mode &&
					false !== $install_tax_terms &&
					! in_array( $term['slug'], $install_tax_terms, true )
				) {
					continue;
				} elseif (
					'list' === $this->install_terms_mode &&
					! in_array( $term['slug'], $install_tax_terms, true )
				) {
					continue;
				}
				if ( ! empty( $term['parent'] ) && true !== $term['parent'] ) {
					if ( isset( $installed_slug[ $term['parent'] ] ) ) {
						wp_update_term(
							$installed_slug[ $term['slug'] ]->term_id,
							$taxonomy,
							array( 'parent' => $installed_slug[ $term['parent'] ]->term_id )
						);
					}
				}
			}
		}
	}

	public function update_term_metas( $taxonomy, $term_id, $metas ) {
		$disable_update = get_term_meta( $term_id, 'cutemi_disable_update', true );
		if ( ! is_array( $disable_update ) ) {
			$disable_update = array();
		}
		foreach ( $metas as $meta_key => $meta_value ) {
			$old_value = get_term_meta( $term_id, $meta_key, true );
			if ( in_array( $meta_key, $disable_update, true ) ) {
				if ( $old_value !== $meta_value ) {
					$this->notice(
						sprintf(
							'No update %s->%s->%d, UPDATE VALUE%s, LOCK VALUE:%s',
							$taxonomy,
							$term_id,
							$meta_key,
							$meta_value,
							$old_value
						)
					);
				}
				continue;
			}
			if ( $old_value !== $meta_value ) {
				$meta_value = addslashes( $meta_value );
				$ok         = update_term_meta( $term_id, $meta_key, $meta_value );
				if ( is_wp_error( $ok ) ) {
					$this->error(
						sprintf(
							'Error to update term metadata to %s: %d=%s: %s',
							$taxonomy,
							$meta_key,
							wp_json_encode( $meta_value, true ),
							$ok->get_error_message()
						)
					);
				} elseif ( $meta_value && false === $ok ) {
					$this->error(
						sprintf(
							'Error to update term metadata to [term_id=%s]%s: %d=%s',
							$term_id,
							$taxonomy,
							$meta_key,
							wp_json_encode( $meta_value, true )
						)
					);
				}
			}
		}
	}

	public function notice( $error ) {
		$this->notices[] = $error;
	}

	public function error( $error ) {
		$this->errors[] = $error;
	}

	public function add_term_metas( $taxonomy, $term_id, $metas ) {
		foreach ( $metas as $meta_key => $meta_value ) {
			$meta_value = addslashes( $meta_value );
			$ok         = update_term_meta( $term_id, $meta_key, $meta_value );
			if ( is_wp_error( $ok ) ) {
				$this->error(
					sprintf(
						'Error to add term metadata to %s: %d: %s',
						$taxonomy,
						$meta_key,
						$ok->get_error_message()
					)
				);
			} elseif ( $meta_value && false === $ok ) {
				$this->error(
					sprintf(
						'Error to add term metadata to [term_id=%s]%s: %d=%s',
						$term_id,
						$taxonomy,
						$meta_key,
						$meta_value
					)
				);
			}
		}
	}

}

