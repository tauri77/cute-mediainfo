<?php
/** @noinspection PhpUnused */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

/**
 * Video File Info settings API class
 *
 * @author Mauricio Galetto
 */
if ( ! class_exists( 'Cute_Mediainfo_Settings' ) ) :
	class Cute_Mediainfo_Settings {

		private $settings_api;

		public function __construct() {
			$this->settings_api = new CUTEMI_Settings_API();

			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		}

		public function admin_init() {
			//set the settings
			$this->settings_api->set_sections( $this->get_settings_sections() );
			$this->settings_api->set_fields( $this->get_settings_fields() );

			//initialize settings
			$this->settings_api->admin_init();
		}

		public function admin_menu() {
			$this->settings_api->page_ref = add_options_page(
				__( 'Cute MediaInfo Settings', 'cute-mediainfo' ),
				__( 'Cute MediaInfo', 'cute-mediainfo' ),
				'manage_options',
				'settings_cute_mediainfo',
				array( $this, 'plugin_page' )
			);
		}

		public function plugin_page() {

			CUTEMI_Settings_Credentials::instance();
			CUTEMI_Settings_Wizards::instance();

			/**
			 * If return false dont print
			 */
			$short_circuit = apply_filters( 'cutemi_settings_pre_print', true );
			if ( $short_circuit ) {

				if ( 'on' !== get_option( 'cutemi_force_inline_css', 'off' ) ) {
					$errors      = get_settings_errors();
					$has_css_err = false;
					foreach ( $errors as $msg ) {
						if ( 'cutemi_css_err' === $msg['code'] ) {
							$has_css_err = true;
						}
					}

					if ( false === $has_css_err && '1' === get_option( 'cutemi_pending_css', '0' ) ) {
						// settings changes or initialize and css not save
						$msg = sprintf(
							'%s <a href="%s">%s</a>.',
							esc_html__( 'The Cute MediaInfo settings could not be written to the css file. Inline styles will be used until you configure storage.', 'cute-mediainfo' ),
							esc_url( CUTEMI_Storage::get_setting_credentials() ),
							esc_html__( 'Configure Now', 'cute-mediainfo' )
						);
						?>
						<div class="notice notice-error">
							<p><?php echo wp_kses( $msg, wp_kses_allowed_html() ); ?></p>
						</div>
						<?php
					}
				}

				echo '<div class="wrap">';

				$this->settings_api->show_navigation();
				$this->settings_api->show_forms();

				echo '</div>';

			}

		}

		/**
		 * Get all the pages
		 *
		 * @return array page names with key value pairs
		 */
		public function get_pages() {
			$pages         = get_pages();
			$pages_options = array();
			if ( $pages ) {
				foreach ( $pages as $page ) {
					$pages_options[ $page->ID ] = $page->post_title;
				}
			}

			return $pages_options;
		}

		private function get_settings_sections() {
			$sections = array(
				array(
					'id'       => 'cutemi_general',
					'title'    => __( 'General Settings', 'cute-mediainfo' ),
					'as_array' => false,
				),
				array(
					'id'       => 'cutemi_advanced',
					'title'    => __( 'Advanced Settings', 'cute-mediainfo' ),
					'as_array' => false,
				),
				array(
					'id'       => 'cutemi_profiles',
					'title'    => __( 'Profiles', 'cute-mediainfo' ),
					'as_array' => false,
				),
			);

			add_filter( 'cutemi_settings_sections', array( $this, 'set_last_settings_sections' ), 50 );

			return apply_filters( 'cutemi_settings_sections', $sections );
		}

		public function set_last_settings_sections( $sections ) {
			$sections[] = array(
				'id'            => 'cutemi_setup',
				'title'         => __( 'Wizard', 'cute-mediainfo' ),
				'form_callback' => array( CUTEMI_Settings_Wizards::instance(), 'print_setup' ),
				'as_array'      => false,
			);
			$sections[] = array(
				'id'            => 'documentation',
				'title'         => __( 'Documentation', 'cute-mediainfo' ),
				'form_callback' => array( $this, 'print_documentation' ),
				'as_array'      => false,
			);

			return $sections;
		}

		/**
		 * Returns all the settings fields
		 *
		 * @return array settings fields
		 */
		private function get_settings_fields() {
			$settings_fields = array();

			$settings_fields['cutemi_general'] = array();

			$packs   = cutemi_available_icon_packs();
			$options = array();
			//Show sample icons per pack
			foreach ( $packs as $key => $pack ) {
				$ex   = apply_filters( 'cutemi_expo_list_' . $key, '' );
				$code = '<br>';
				foreach ( $ex as $cat => $imgs ) {
					$code .= '<div class="cutemi-icons-preview cat-' . esc_attr( $cat ) . '">';
					foreach ( $imgs as $img ) {
						$code .= '<img class="cutemi-icon-preview" src="' . esc_attr( $img ) . '">';
					}
					$code .= '</div>';
				}
				$options[ $key ] = $pack['name'] . $code;
			}
			$settings_fields['cutemi_general'][] = array(
				'name'    => 'cutemi_icon_pack',
				'label'   => __( 'Icon Pack', 'cute-mediainfo' ),
				'desc'    => '',
				'type'    => 'radio',
				'default' => 'default',
				'options' => $options,
			);

			$profiles         = array();
			$profiles_enabled = cutemi_get_profiles();
			foreach ( $profiles_enabled as $prof ) {
				$profiles[ $prof['slug'] ] = $prof['label'];
			}

			$profiles_enabled = cutemi_get_templated_profiles();
			foreach ( $profiles_enabled as $prof_slug => $prof ) {
				$profiles[ $prof_slug ] = $prof['label'];
			}
			$settings_fields['cutemi_general'][] = array(
				'name'    => 'cutemi_profile_default',
				'label'   => __( 'Default profile', 'cute-mediainfo' ),
				'desc'    => __( 'This profile is used when a profile is not specified or does not exist', 'cute-mediainfo' ),
				'default' => cutemi_get_default_profile(),
				'type'    => 'radio',
				'options' => $profiles,
			);
			$settings_fields['cutemi_general'][] = array(
				'name'    => 'cutemi_hide_offline',
				'label'   => __( 'Hide Offline Links', 'cute-mediainfo' ),
				'desc'    => __( 'Does not show links with "Offline" status to users', 'cute-mediainfo' ),
				'default' => 'on',
				'type'    => 'checkbox',
			);
			$settings_fields['cutemi_general'][] = array(
				'name'    => 'cutemi_link_size_1_part',
				'label'   => __( 'Show Always Link Size', 'cute-mediainfo' ),
				'desc'    => __( 'Show the link size even if it is a single fragment', 'cute-mediainfo' ),
				'default' => 'off',
				'type'    => 'checkbox',
			);

			$ex                                  = 53.3 * 60;
			$settings_fields['cutemi_general'][] = array(
				'name'    => 'cutemi_duration_format',
				'label'   => __( 'Duration Format', 'cute-mediainfo' ),
				'desc'    => '',
				'type'    => 'radio',
				'default' => 'simple',
				'options' => array(
					'simple'         => 'Mmin or Ssec -> ' . cutemi_human_duration_simple( $ex ),
					'h_mm'           => 'H:MM or M:SS -> ' . cutemi_human_duration_h_m_s( $ex, false, 2, 2 ),
					'hh_mm_ss'       => '[HH:]MM:SS -> ' . cutemi_human_duration_h_m_s( $ex, false ),
					'fhh_mm_ss'      => 'HH:MM:SS -> ' . cutemi_human_duration_h_m_s( $ex, false, 3, true, true ),
					'hh_mm'          => 'HH:MM or MM:SS -> ' . cutemi_human_duration_h_m_s( $ex, false, 2 ),
					'hh_mm_suffixed' => 'HHh:MMmin or MMmin:SSs -> ' . cutemi_human_duration_h_m_s( $ex, true, 2 ),
				),
			);

			$settings_fields['cutemi_advanced'] = array();

			$settings_fields['cutemi_advanced'][] = array(
				'name'    => 'cutemi_link_data_extractor',
				'label'   => __( 'Link data extractor', 'cute-mediainfo' ),
				'desc'    => __( 'Enable automatic part/size/name extraction from link. This will generate a request to external servers (gdrive, mega, etc) ', 'cute-mediainfo' ),
				'default' => 'on',
				'type'    => 'checkbox',
			);
			$settings_fields['cutemi_advanced'][] = array(
				'name'    => 'cutemi_purge_on_uninstall',
				'label'   => __( 'Remove data on uninstall', 'cute-mediainfo' ),
				'desc'    => __( 'Check this if you would like to remove ALL data upon plugin deletion. All settings and videos will be unrecoverable.', 'cute-mediainfo' ),
				'default' => 'off',
				'type'    => 'checkbox',
			);
			$settings_fields['cutemi_advanced'][] = array(
				'name'    => 'cutemi_svg_head_colorized',
				'label'   => __( 'Method to color the header svg', 'cute-mediainfo' ),
				'desc'    => __( 'This allows changing the header icon color', 'cute-mediainfo' ),
				'default' => '1',
				'type'    => 'radio',
				'options' => array(
					'0' => __( 'Disabled', 'cute-mediainfo' ),
					'1' => __( 'Use CSS filter', 'cute-mediainfo' ),
					'2' => __( 'Use SVGInject JavaScript library', 'cute-mediainfo' ),
				),
			);
			$settings_fields['cutemi_advanced'][] = array(
				'name'    => 'cutemi_mediainfo_lib',
				'label'   => __( 'MediaInfoLib version', 'cute-mediainfo' ),
				'desc'    => __( 'This allows to change the library used in the edition of a mediainfo. This library extracts the information from a video file.', 'cute-mediainfo' ),
				'default' => 'official',
				'type'    => 'radio',
				'options' => array(
					'official'      => __( 'Official MediaInfo (served by your server)', 'cute-mediainfo' ),
					'buzz_port'     => __( 'Buzz JavaScript port (served by your server)', 'cute-mediainfo' ),
					'buzz_port_cdn' => __( 'Buzz JavaScript port (served by unpkg.com)', 'cute-mediainfo' ),
				),
			);

			$desc = __( 'Enable this only as a last resort. First try to set the credentials.', 'cute-mediainfo' ) .
					' <a href="' . CUTEMI_Storage::get_setting_credentials() . '">' .
					__( 'Set credentials now', 'cute-mediainfo' ) . '</a>';

			$settings_fields['cutemi_advanced'][] = array(
				'name'    => 'cutemi_relaxed_ownership',
				'label'   => __( 'Use relaxed file ownership ', 'cute-mediainfo' ),
				'desc'    => $desc,
				'default' => 'off',
				'type'    => 'checkbox',
			);

			$settings_fields['cutemi_advanced'][] = array(
				'name'    => 'cutemi_force_inline_css',
				'label'   => __( 'Force CSS inline', 'cute-mediainfo' ),
				'desc'    => __( 'Do not generate static css file, instead add the styles in an inline style tag on each page. (not recommended)', 'cute-mediainfo' ),
				'default' => 'off',
				'type'    => 'checkbox',
			);

			/*******************************************************************************
			 *                  PROFILES
			 */

			add_filter( 'cutemi_settings_array_can_remove_item', array( $this, 'profile_can_remove_item' ), 10, 3 );
			add_action( 'cutemi_settings_array_end_item', array( $this, 'profile_array_end_item' ), 10, 3 );
			add_filter( 'cutemi_settings_field_render_args', array( $this, 'profile_field_render_args' ), 10, 8 );

			$settings_fields['cutemi_profiles'] = array();

			/* translators:  %3$s is the profile label, Ex: Summary */
			$item_title                           = __( 'Profile %3$s', 'cute-mediainfo' );
			$settings_fields['cutemi_profiles'][] = array(
				'name'                     => 'cutemi_profiles',
				'label'                    => __( 'Profiles Styles', 'cute-mediainfo' ),
				'desc'                     => '',
				'type'                     => 'array',
				'sortable'                 => false,
				'can_remove'               => true,
				'no_empty'                 => true,
				'item_title'               => $item_title,
				// 1 is index for de option array. 2 is array_text_val, 3 is array_number_input, etc..
				'default'                  => cutemi_get_default_profiles(),
				'sanitize_callback_params' => 3,
				'sanitize_callback'        => function ( $value, $option, $original_value ) {
					if (
						! is_array( $value ) ||
						empty( $value )
					) {
						//This never happen to normal user.. only "hackers", no translate
						add_settings_error(
							$option['name'],
							$option['name'] . '_err',
							'Invalid profiles value'
						);

						return $original_value;
					}

					$value = array_values( $value );

					$ok = false;
					//Required full enabled
					foreach ( $value as $i => $profile ) {
						if ( 'full' === $profile['slug'] && 'on' === $profile['enabled'] ) {
							$ok = true;
						}
					}

					if ( ! $ok ) {
						//This never happen to normal user.. only "hackers". no translate
						add_settings_error(
							$option['name'],
							$option['name'] . '_err',
							"The profile 'full' always enabled"
						);

						return $original_value;
					}

					return $value;
				},
				'item_fields'              => array(
					'slug'    => array(
						'name'                     => 'slug',
						'label'                    => __( 'Slug', 'cute-mediainfo' ),
						'desc'                     => __( 'Alphanumeric characters, underscore (_) and dash (-).' ),
						'placeholder'              => '',
						'type'                     => 'text',
						'default'                  => '',
						'can_edit'                 => false,
						'unique'                   => true,
						'sanitize_callback_params' => 3,
						'sanitize_callback'        => function ( $value, $option, $original_value ) {
							if ( empty( $value ) ) {
								$value = 'profile-' . wp_rand( 0, 9999 );
							}

							return sanitize_title( $value );
						},
					),
					'label'   => array(
						'name'        => 'label',
						'label'       => __( 'Label', 'cute-mediainfo' ),
						'desc'        => __( 'Name to display', 'cute-mediainfo' ),
						'placeholder' => '',
						'type'        => 'text',
						'default'     => '',
					),
					'enabled' => array(
						'name'    => 'enabled',
						'default' => 'on',
						'label'   => '',
						'desc'    => __( 'Enable this profile', 'cute-mediainfo' ),
						'type'    => 'checkbox',
					),
				),
			);

			$sanitize_options  = array( 'Cute_Mediainfo_Settings', 'sanitize_options' );
			$sanitize_checkbox = array( 'Cute_Mediainfo_Settings', 'sanitize_checkbox' );
			$sanitize_text     = array( 'Cute_Mediainfo_Settings', 'sanitize_text' );

			foreach ( $settings_fields as $tab => $fileds ) {
				foreach ( $fileds as $k => $field ) {
					if ( ! isset( $field['sanitize_callback'] ) ) {
						if ( 'checkbox' === $field['type'] ) {
							$settings_fields[ $tab ][ $k ]['sanitize_callback']        = $sanitize_checkbox;
							$settings_fields[ $tab ][ $k ]['sanitize_callback_params'] = 3;
						} elseif ( 'radio' === $field['type'] ) {
							$settings_fields[ $tab ][ $k ]['sanitize_callback']        = $sanitize_options;
							$settings_fields[ $tab ][ $k ]['sanitize_callback_params'] = 4;
						} elseif ( 'text' === $field['type'] ) {
							$settings_fields[ $tab ][ $k ]['sanitize_callback']        = $sanitize_text;
							$settings_fields[ $tab ][ $k ]['sanitize_callback_params'] = 3;
						}
					}
					if ( 'array' === $field['type'] && isset( $field['item_fields'] ) ) {
						foreach ( $field['item_fields'] as $ss => $sub_field ) {
							if ( ! isset( $sub_field['sanitize_callback'] ) ) {
								if ( 'checkbox' === $sub_field['type'] ) {
									$settings_fields[ $tab ][ $k ]['item_fields'][ $ss ]['sanitize_callback']        = $sanitize_checkbox;
									$settings_fields[ $tab ][ $k ]['item_fields'][ $ss ]['sanitize_callback_params'] = 3;
								} elseif ( 'radio' === $sub_field['type'] ) {
									$settings_fields[ $tab ][ $k ]['item_fields'][ $ss ]['sanitize_callback']        = $sanitize_options;
									$settings_fields[ $tab ][ $k ]['item_fields'][ $ss ]['sanitize_callback_params'] = 4;
								} elseif ( 'text' === $sub_field['type'] ) {
									$settings_fields[ $tab ][ $k ]['item_fields'][ $ss ]['sanitize_callback']        = $sanitize_text;
									$settings_fields[ $tab ][ $k ]['item_fields'][ $ss ]['sanitize_callback_params'] = 3;
								}
							}
						}
					}
				}
			}

			return apply_filters( 'cutemi_settings_fields', $settings_fields );
		}

		public static function sanitize_checkbox( $value, $option, $original_value ) {

			if ( ! in_array( $value, array( 'on', 'off' ), true ) ) {
				//This never happen to no hackers users
				if ( in_array( $original_value, array( 'on', 'off' ), true ) ) {
					$msg = sprintf(
						'ERROR on set %s value: %s, return to %s',
						esc_html( $option ),
						esc_html( $value ),
						esc_html( $original_value )
					);
					add_settings_error( $option['name'], $option['name'] . 'ERR', $msg );
					$value = $original_value;
				}
			}

			return ( 'on' === $value ) ? 'on' : 'off';
		}

		public static function sanitize_options( $value, $option, $original_value, $config ) {
			if ( ! isset( $config['options'] ) ) {
				//Never happen this to normal user, no translate
				add_settings_error(
					$option,
					sanitize_title( $option ) . '_ERR',
					'Options not available: ' . esc_html( $option ) . '.'
				);

				return $original_value;
			}
			if ( ! isset( $config['options'][ $value ] ) ) {
				if ( ! isset( $config['options'][ $original_value ] ) && isset( $config['default'] ) ) {
					$original_value = $config['default'];
				}
				//Never happen this to normal user, no translate
				add_settings_error(
					$option,
					sanitize_title( $option ) . '_ERR',
					sprintf(
						'ERROR %s: The option was not found %s',
						esc_html( $config['label'] ),
						esc_html( $value )
					)
				);

				return $original_value;
			}

			return $value;
		}

		public static function sanitize_text( $value, $option, $original_value ) {
			return sanitize_text_field( $value );
		}

		public function profile_can_remove_item( $can_remove, $args, $item ) {
			if ( isset( $item['slug'] ) && 'full' === $item['slug'] ) {
				return false;
			}

			return $can_remove;
		}

		public function profile_array_end_item( $array_value, $item_index, $args ) {
			if ( ! isset( $array_value[ $item_index ] ) || ! isset( $array_value[ $item_index ]['slug'] ) ) {
				return;
			}

			$profile = $array_value[ $item_index ];
			if ( ! cutemi_is_valid_profile( $profile['slug'], true ) ) {
				return;
			}

			$query['autofocus[panel]'] = 'cute_mediainfo-' . $profile['slug'];
			$query['return']           = rawurlencode( admin_url( 'options-general.php?page=settings_cute_mediainfo' ) );
			$query['url']              = rawurlencode(
				add_query_arg(
					array( 'cutemi_profile_force' => $profile['slug'] ),
					site_url( '/?page_id=cutemi-fake-preview-page' )
				)
			);
			$link                      = add_query_arg( $query, admin_url( 'customize.php' ) );

			printf(
				'<a class="button cutemi-profile-edit" href="%s">%s</a>',
				esc_url( $link ),
				esc_html__( 'Customize Profile', 'cute-mediainfo' )
			);
		}

		public function profile_field_render_args( $args, $section_id, $option, $label, $option_input_name, $option_id, $type, $value ) {
			//readonly full enabled
			if ( 'cutemi_profiles' === $option_id ) {
				$profiles = get_option( 'cutemi_profiles', cutemi_get_default_profiles() );
				foreach ( $profiles as $i => $profile ) {
					if ( 'full' === $profile['slug'] && 'cutemi_profiles[' . $i . '][enabled]' === $args['option_name'] ) {
						$args['readonly'] = true;
					}
				}
			}

			return $args;
		}

		public function print_documentation() {
			include dirname( __FILE__ ) . '/includes/template-documentation.php';
		}

	}
endif;


new Cute_Mediainfo_Settings();

