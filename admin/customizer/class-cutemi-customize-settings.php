<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

class CUTEMI_Customize_Settings {

	public $head_position = array();

	public $head_layouts = array();

	public $data_layouts = array();

	public $group_priority = array();

	public $data_position = array();

	public $data_row = array();

	public $units = array(
		'px'  => 'px',
		'em'  => 'em',
		'rem' => 'rem',
		'pt'  => 'pt',
		'vw'  => 'vw',
		'vh'  => 'vh',
	);

	public $g_font_url = '';

	public $g_font_mono_url = '';

	public $id_styling   = 'cutemi_styling';
	public $id_lt_groups = 'cutemi_layout_groups';
	public $id_lt_data   = 'cutemi_layout_data';
	private $profile;
	private $labels = array();
	private $panel;

	public function __construct( $profile ) {
		$this->profile       = $profile;
		$this->id_styling   .= '[' . $profile['id'] . ']';
		$this->id_lt_groups .= '[' . $profile['id'] . ']';
		$this->id_lt_data   .= '[' . $profile['id'] . ']';

		$this->g_font_url = 'https://fonts.googleapis.com/css2?' .
							'family=Aclonica&' .
							'family=Audiowide&' .
							'family=Baumans&' .
							'family=Berkshire+Swash&' .
							'family=Cinzel&' .
							'family=Creepster&' .
							'family=Fredericka+the+Great&' .
							'family=Fuzzy+Bubbles:wght@400;700&' .
							'family=Galada&' .
							'family=Istok+Web:ital,wght@0,400;0,700;1,400&' .
							'family=Lexend:wght@100;400;700&' .
							'family=Nanum+Pen+Script&' .
							'&family=Nova+Square&' .
							'family=Poiret+One&' .
							'family=Press+Start+2P&' .
							'family=Roboto:wght@100;300;500;700;900&' .
							'family=Roboto+Condensed:wght@300;400;700&' .
							'family=Share+Tech&' .
							'family=Six+Caps&' .
							'family=Special+Elite&' .
							'family=Stardos+Stencil:wght@400;700&' .
							'family=Teko&' .
							'family=Ubuntu+Condensed&' .
							'family=Unica+One&' .
							'family=Vidaloka&' .
							'';

		$locale = get_locale();
		if ( in_array( $locale, array( 'zh_TW', 'zh_HK', 'zh_CN' ), true ) ) {
			$this->g_font_url .= 'family=Noto+Sans+TC:wght@300;700&';
			$this->g_font_url .= 'family=Noto+Sans+SC:wght@300;700&';
			$this->g_font_url .= 'family=Noto+Sans+HK:wght@300;700&';
		}
		if ( 'ja' === $locale ) {
			$this->g_font_url .= 'family=Noto+Sans+JP:wght@300;700&';
		}
		if ( 'ko_KR' === $locale ) {
			$this->g_font_url .= 'family=Noto+Sans+KR:wght@300;700&';
		}

		$this->g_font_url .= 'display=swap';

		$this->g_font_mono_url = 'https://fonts.googleapis.com/css2?' .
									'family=Major+Mono+Display&' .
									'family=Nanum+Gothic+Coding:wght@400;700&' .
									'family=Nova+Mono&' .
									'family=Roboto+Mono:wght@100;400&' .
									'family=Share+Tech+Mono&' .
									'family=Syne+Mono&' .
									'family=Ubuntu+Mono:ital,wght@0,400;0,700;1,400&' .
									'family=VT323&' .
									'family=Xanh+Mono&' .
									'display=swap';

		$this->head_position = array(
			'top'      => __( 'Top', 'cute-mediainfo' ),
			'left'     => __( 'Left Side', 'cute-mediainfo' ),
			'left-top' => __( 'Left, but top on Small', 'cute-mediainfo' ),
			'no-head'  => __( 'Without Header', 'cute-mediainfo' ),
		);

		$this->head_layouts = array(
			'txt'       => __( 'Only Text', 'cute-mediainfo' ),
			'img_n_txt' => __( 'Icon ¬ Text', 'cute-mediainfo' ),
			'img_txt'   => __( 'Icon -> Text', 'cute-mediainfo' ),
			'img'       => __( 'Only Icon', 'cute-mediainfo' ),
		);

		$this->data_layouts = array(
			'none'          => __( 'Dont Show', 'cute-mediainfo' ),
			'txt'           => __( 'Only Text', 'cute-mediainfo' ),
			'img_n_txt'     => __( 'Icon ¬ Text', 'cute-mediainfo' ),
			'img_txt'       => __( 'Icon -> Text', 'cute-mediainfo' ),
			'img_txt_flex'  => __( 'Icon ¬-> Text', 'cute-mediainfo' ),
			'txt_top_label' => __( 'Top Labeled', 'cute-mediainfo' ),
			'img'           => __( 'Only Icon', 'cute-mediainfo' ),
			'multiline'     => __( 'Multiline Text', 'cute-mediainfo' ),
			'pre'           => __( 'Preformatted Text', 'cute-mediainfo' ),
			'link'          => __( 'Link', 'cute-mediainfo' ),
		);

		$this->group_priority = array(
			'0' => __( 'Dont Show Block Info', 'cute-mediainfo' ),
		);
		/* translators: digit is the priority of block, to sort */
		$priority_x = __( 'Show Block Info [priority:%d]', 'cute-mediainfo' );
		for ( $i = 1; $i < 10; $i ++ ) {
			$this->group_priority[ $i ] = sprintf( $priority_x, $i );
		}

		$this->data_position = array();
		/* translators: digit is the number on row position */
		$pos_x = __( 'Position %d', 'cute-mediainfo' );
		for ( $i = 1; $i < 10; $i ++ ) {
			$this->data_position[ $i ] = sprintf( $pos_x, $i );
		}

		$this->data_row = array();
		/* translators: digit is the number of row */
		$row_x = __( 'Row %d', 'cute-mediainfo' );
		for ( $i = 1; $i < 10; $i ++ ) {
			$this->data_row[ $i ] = sprintf( $row_x, $i );
		}

		$this->customize_register();

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

	}

	public function enqueue() {
		wp_enqueue_script(
			'cutemi-customizer',
			plugins_url( 'assets/customizer.js', __FILE__ ),
			array(),
			'1.0.2',
			true
		);
	}

	private function customize_register() {
		$this->labels = array(
			'Unit'                         => __( 'Unit', 'cute-mediainfo' ),
			'Font Size'                    => __( 'Font Size', 'cute-mediainfo' ),
			'Data Color'                   => __( 'Data Color', 'cute-mediainfo' ),
			'External Margin'              => __( 'External Margin', 'cute-mediainfo' ),
			'External Margin Sides Auto'   => __( 'Automatic Side Margin', 'cute-mediainfo' ),
			'Outer Border Width'           => __( 'Outer Border Width', 'cute-mediainfo' ),
			'Outer Border Color'           => __( 'Outer Border Color', 'cute-mediainfo' ),
			'Outer Blocks Padding'         => __( 'Outer Blocks Padding', 'cute-mediainfo' ),
			'Background Color'             => __( 'Background Color', 'cute-mediainfo' ),
			'Outer Border Radius'          => __( 'Outer Border Radius', 'cute-mediainfo' ),
			'Internal Border Radius'       => __( 'Internal Border Radius', 'cute-mediainfo' ),
			'Min Width'                    => __( 'Min Width', 'cute-mediainfo' ),
			'Max Width'                    => __( 'Max Width', 'cute-mediainfo' ),
			'Multiline Max Height'         => __( 'Multiline Max Height', 'cute-mediainfo' ),
			'Blocks Border Width'          => __( 'Blocks Border Width', 'cute-mediainfo' ),
			'Blocks Border Radius'         => __( 'Blocks Border Radius', 'cute-mediainfo' ),
			'Blocks Border Color'          => __( 'Blocks Border Color', 'cute-mediainfo' ),
			'Blocks Background Color'      => __( 'Blocks Background Color', 'cute-mediainfo' ),
			'Block Headers Color'          => __( 'Block Headers Color', 'cute-mediainfo' ),
			'Block Headers Font Color'     => __( 'Block Headers Font Color', 'cute-mediainfo' ),
			'Block Headers Side Width'     => __( 'Block Headers Side Width', 'cute-mediainfo' ),
			'Blocks Spacing'               => __( 'Blocks Spacing', 'cute-mediainfo' ),
			'Row Border'                   => __( 'Row Border', 'cute-mediainfo' ),
			'Row Border Color'             => __( 'Row Border Color', 'cute-mediainfo' ),
			'Row Height'                   => __( 'Row Height', 'cute-mediainfo' ),
			'Cell Padding'                 => __( 'Cell Padding', 'cute-mediainfo' ),
			'Cell Border'                  => __( 'Cell Border', 'cute-mediainfo' ),
			'Cell Border Color'            => __( 'Cell Border Color', 'cute-mediainfo' ),
			'Button Background Color'      => __( 'Button Background Color', 'cute-mediainfo' ),
			'Button Border Color'          => __( 'Button Border Color', 'cute-mediainfo' ),
			'Button Height Percentage'     => __( 'Button Height Percentage', 'cute-mediainfo' ),
			'Button Width Percentage'      => __( 'Button Width Percentage', 'cute-mediainfo' ),
			'Button Border Width'          => __( 'Button Border Width', 'cute-mediainfo' ),
			'Button Border Radius'         => __( 'Button Border Radius', 'cute-mediainfo' ),
			'Button Font Color'            => __( 'Button Font Color', 'cute-mediainfo' ),
			'Mediainfo Font Size'          => __( 'Mediainfo Font Size', 'cute-mediainfo' ),
			'General Block'                => __( 'General Block', 'cute-mediainfo' ),
			'General Header'               => __( 'General Header', 'cute-mediainfo' ),
			'General Header Layout'        => __( 'General Header Layout', 'cute-mediainfo' ),
			'Videos Block'                 => __( 'Videos Block', 'cute-mediainfo' ),
			'Videos Header'                => __( 'Videos Header', 'cute-mediainfo' ),
			'Videos Header Layout'         => __( 'Videos Header Layout', 'cute-mediainfo' ),
			'Audios Block'                 => __( 'Audios Block', 'cute-mediainfo' ),
			'Audios Header'                => __( 'Audios Header', 'cute-mediainfo' ),
			'Audios Header Layout'         => __( 'Audios Header Layout', 'cute-mediainfo' ),
			'Texts Block'                  => __( 'Texts Block', 'cute-mediainfo' ),
			'Texts Header'                 => __( 'Texts Header', 'cute-mediainfo' ),
			'Texts Header Layout'          => __( 'Texts Header Layout', 'cute-mediainfo' ),
			'Links Block'                  => __( 'Links Block', 'cute-mediainfo' ),
			'Links Header'                 => __( 'Links Header', 'cute-mediainfo' ),
			'Links Header Layout'          => __( 'Links Header Layout', 'cute-mediainfo' ),
			'Mediainfo Block'              => __( 'Mediainfo Block', 'cute-mediainfo' ),
			'Mediainfo Header'             => __( 'Mediainfo Header', 'cute-mediainfo' ),
			'Mediainfo Header Layout'      => __( 'Mediainfo Header Layout', 'cute-mediainfo' ),
			'General: Format Layout'       => __( 'General: Format Layout', 'cute-mediainfo' ),
			'General: Format Row Number'   => __( 'General: Format Row Number', 'cute-mediainfo' ),
			'General: Format Order'        => __( 'General: Format Order', 'cute-mediainfo' ),
			'General: Size Layout'         => __( 'General: Size Layout', 'cute-mediainfo' ),
			'General: Size Row Number'     => __( 'General: Size Row Number', 'cute-mediainfo' ),
			'General: Size Order'          => __( 'General: Size Order', 'cute-mediainfo' ),
			'General: Date Layout'         => __( 'General: Date Layout', 'cute-mediainfo' ),
			'General: Date Row Number'     => __( 'General: Date Row Number', 'cute-mediainfo' ),
			'General: Date Order'          => __( 'General: Date Order', 'cute-mediainfo' ),
			'General: Duration Layout'     => __( 'General: Duration Layout', 'cute-mediainfo' ),
			'General: Duration Row Number' => __( 'General: Duration Row Number', 'cute-mediainfo' ),
			'General: Duration Order'      => __( 'General: Duration Order', 'cute-mediainfo' ),
			'General: Desc Layout'         => __( 'General: Desc Layout', 'cute-mediainfo' ),
			'General: Desc Row Number'     => __( 'General: Desc Row Number', 'cute-mediainfo' ),
			'General: Desc Order'          => __( 'General: Desc Order', 'cute-mediainfo' ),
			'Videos: Resolution Layout'    => __( 'Videos: Resolution Layout', 'cute-mediainfo' ),
			'Videos: Resolution Order'     => __( 'Videos: Resolution Order', 'cute-mediainfo' ),
			'Videos: Tech Layout'          => __( 'Videos: Tech Layout', 'cute-mediainfo' ),
			'Videos: Tech Order'           => __( 'Videos: Tech Order', 'cute-mediainfo' ),
			'Videos: Bitrate Layout'       => __( 'Videos: Bitrate Layout', 'cute-mediainfo' ),
			'Videos: Bitrate Order'        => __( 'Videos: Bitrate Order', 'cute-mediainfo' ),
			'Videos: Bitrate Mode Layout'  => __( 'Videos: Bitrate Mode Layout', 'cute-mediainfo' ),
			'Videos: Bitrate Mode Order'   => __( 'Videos: Bitrate Mode Order', 'cute-mediainfo' ),
			'Audios: Lang Layout'          => __( 'Audios: Lang Layout', 'cute-mediainfo' ),
			'Audios: Lang Order'           => __( 'Audios: Lang Order', 'cute-mediainfo' ),
			'Audios: Tech Layout'          => __( 'Audios: Tech Layout', 'cute-mediainfo' ),
			'Audios: Tech Order'           => __( 'Audios: Tech Order', 'cute-mediainfo' ),
			'Audios: Channels Layout'      => __( 'Audios: Channels Layout', 'cute-mediainfo' ),
			'Audios: Channels Order'       => __( 'Audios: Channels Order', 'cute-mediainfo' ),
			'Audios: Bitrate Layout'       => __( 'Audios: Bitrate Layout', 'cute-mediainfo' ),
			'Audios: Bitrate Order'        => __( 'Audios: Bitrate Order', 'cute-mediainfo' ),
			'Audios: Bitrate Mode Layout'  => __( 'Audios: Bitrate Mode Layout', 'cute-mediainfo' ),
			'Audios: Bitrate Mode Order'   => __( 'Audios: Bitrate Mode Order', 'cute-mediainfo' ),
			'Texts: Lang Layout'           => __( 'Texts: Lang Layout', 'cute-mediainfo' ),
			'Texts: Lang Order'            => __( 'Texts: Lang Order', 'cute-mediainfo' ),
			'Texts: Format Layout'         => __( 'Texts: Format Layout', 'cute-mediainfo' ),
			'Texts: Format Order'          => __( 'Texts: Format Order', 'cute-mediainfo' ),
			'Texts: Type Layout'           => __( 'Texts: Type Layout', 'cute-mediainfo' ),
			'Texts: Type Order'            => __( 'Texts: Type Order', 'cute-mediainfo' ),
			'Links: Source Layout'         => __( 'Links: Source Layout', 'cute-mediainfo' ),
			'Links: Source Order'          => __( 'Links: Source Order', 'cute-mediainfo' ),
			'Links: Part Layout'           => __( 'Links: Part Layout', 'cute-mediainfo' ),
			'Links: Part Order'            => __( 'Links: Part Order', 'cute-mediainfo' ),
			'Links: Size Layout'           => __( 'Links: Size Layout', 'cute-mediainfo' ),
			'Links: Size Order'            => __( 'Links: Size Order', 'cute-mediainfo' ),
			'Links: Link Layout'           => __( 'Links: Link Layout', 'cute-mediainfo' ),
			'Links: Link Order'            => __( 'Links: Link Order', 'cute-mediainfo' ),
		);

		$this->add_panel();
		$this->add_sections();
		$this->add_settings();
		$this->add_controls();
	}

	private function add_panel() {
		global $wp_customize;
		$this->panel = 'cute_mediainfo-' . $this->profile['slug'];

		/* translators: string is the profile label, ex: Full, Summary, ...  */
		$title = sprintf( __( 'Profile: %s (Cute MI)', 'cute-mediainfo' ), $this->profile['label'] );
		$wp_customize->add_panel(
			$this->panel,
			array(
				'title'    => $title,
				'priority' => 160,
			)
		);
	}

	private function add_sections() {
		global $wp_customize;

		$title = __( 'Styles', 'cute-mediainfo' );
		$wp_customize->add_section(
			$this->id_styling,
			array(
				'title'       => sprintf( $title, $this->profile['label'] ),
				'description' => __( 'Edit colors and dimensions', 'cute-mediainfo' ),
				'priority'    => 130,
				'capability'  => 'edit_theme_options',
				'panel'       => $this->panel,
			)
		);

		$title = __( 'Layout (Blocks/Headers)', 'cute-mediainfo' );
		$wp_customize->add_section(
			$this->id_lt_groups,
			array(
				'title'       => sprintf( $title, $this->profile['label'] ),
				'description' => __( 'Edit how to display block headers', 'cute-mediainfo' ),
				'priority'    => 130,
				'capability'  => 'edit_theme_options',
				'panel'       => $this->panel,
			)
		);

		$title = __( 'Data to Show', 'cute-mediainfo' );
		$wp_customize->add_section(
			$this->id_lt_data,
			array(
				'title'       => sprintf( $title, $this->profile['label'] ),
				'description' => __( 'Edit data to showing', 'cute-mediainfo' ),
				'priority'    => 130,
				'capability'  => 'edit_theme_options',
				'panel'       => $this->panel,
			)
		);

	}

	private function add_settings() {
		global $wp_customize;

		$styles = cutemi_get_style_config_default( $this->profile['slug'] );

		foreach ( $styles as $group_name => $val ) {
			if ( 'unit' === $group_name ) {
				$wp_customize->add_setting(
					$this->id_styling . '[' . $group_name . ']',
					array(
						'default'           => $val,
						'type'              => 'option',
						'capability'        => 'manage_options',
						'sanitize_callback' => array( $this, 'sanitize_unit' ),
					)
				);
			}
			if ( 'font_family' === $group_name ) {
				$wp_customize->add_setting(
					$this->id_styling . '[' . $group_name . ']',
					array(
						'default'           => $val,
						'type'              => 'option',
						'capability'        => 'manage_options',
						'sanitize_callback' => array( $this, 'sanitize_font' ),
					)
				);
			}
			if ( 'google_font_family' === $group_name ) {
				$wp_customize->add_setting(
					$this->id_styling . '[' . $group_name . ']',
					array(
						'default'           => $val,
						'type'              => 'option',
						'capability'        => 'manage_options',
						'sanitize_callback' => array( $this, 'sanitize_font' ),
					)
				);
			}
			if ( 'mediainfo_google_font_family' === $group_name ) {
				$wp_customize->add_setting(
					$this->id_styling . '[' . $group_name . ']',
					array(
						'default'           => $val,
						'type'              => 'option',
						'capability'        => 'manage_options',
						'sanitize_callback' => array( $this, 'sanitize_font' ),
					)
				);
			}
			if ( is_int( $val ) || is_float( $val ) ) {
				$wp_customize->add_setting(
					$this->id_styling . '[' . $group_name . ']',
					array(
						'default'           => $val,
						'type'              => 'option',
						'capability'        => 'manage_options',
						'sanitize_callback' => array( $this, 'sanitize_number' ),
					)
				);
			}
			if (
				is_string( $val ) &&
				( '#' === substr( $val, 0, 1 ) || 'rgba(' === substr( $val, 0, 5 ) )
			) {
				$wp_customize->add_setting(
					$this->id_styling . '[' . $group_name . ']',
					array(
						'default'           => $val,
						'type'              => 'option',
						'capability'        => 'manage_options',
						'sanitize_callback' => array( $this, 'sanitize_css_color' ),
					)
				);
			}
			if ( is_string( $val ) && in_array( $val, array( 'on', 'off' ), true ) ) {
				$wp_customize->add_setting(
					$this->id_styling . '[' . $group_name . ']',
					array(
						'default'           => $val,
						'type'              => 'option',
						'capability'        => 'manage_options',
						'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
					)
				);
			}
		}

		$groups = cutemi_get_data_groups_default_config( $this->profile['slug'] );
		foreach ( $groups as $group_name => $group ) {
			if ( isset( $group['flags'] ) && in_array( 'disable_autocustomize', $group['flags'], true ) ) {
				continue;
			}
			$wp_customize->add_setting(
				$this->id_lt_groups . '[' . $group_name . '][priority]',
				array(
					'default'           => $group['priority'],
					'type'              => 'option',
					'capability'        => 'manage_options',
					'sanitize_callback' => array( $this, 'sanitize_select' ),
				)
			);
			$wp_customize->add_setting(
				$this->id_lt_groups . '[' . $group_name . '][where_head]',
				array(
					'default'           => $group['where_head'],
					'type'              => 'option',
					'capability'        => 'manage_options',
					'sanitize_callback' => array( $this, 'sanitize_select' ),
				)
			);
			$wp_customize->add_setting(
				$this->id_lt_groups . '[' . $group_name . '][show_as]',
				array(
					'default'           => $group['show_as'],
					'type'              => 'option',
					'capability'        => 'manage_options',
					'sanitize_callback' => array( $this, 'sanitize_select' ),
				)
			);
		}

		$groups = cutemi_get_data_groups_default_config( $this->profile['slug'] );
		foreach ( $groups as $group_name => $group ) {
			$group_default = cutemi_get_default_config_data_fields( $group_name, false, $this->profile['slug'] );
			foreach ( $group_default['fields'] as $field_name => $field ) {
				if ( isset( $field['flags'] ) && in_array( 'disable_autocustomize', $field['flags'], true ) ) {
					continue;
				}
				$wp_customize->add_setting(
					$this->id_lt_data . '[' . $group_name . '][fields][' . $field_name . '][show_as]',
					array(
						'default'           => $field['show_as'],
						'type'              => 'option',
						'capability'        => 'manage_options',
						'sanitize_callback' => array( $this, 'sanitize_select' ),
					)
				);

				if ( 'rows' === $group_default['type'] ) {
					$wp_customize->add_setting(
						$this->id_lt_data . '[' . $group_name . '][fields][' . $field_name . '][row]',
						array(
							'default'           => $field['row'],
							'type'              => 'option',
							'capability'        => 'manage_options',
							'sanitize_callback' => array( $this, 'sanitize_select' ),
						)
					);
				}

				if ( count( $group_default['fields'] ) > 1 ) {
					$wp_customize->add_setting(
						$this->id_lt_data . '[' . $group_name . '][fields][' . $field_name . '][position]',
						array(
							'default'           => $field['position'],
							'type'              => 'option',
							'capability'        => 'manage_options',
							'sanitize_callback' => array( $this, 'sanitize_select' ),
						)
					);
				}
			}
		}

		$groups = cutemi_get_data_groups_default_config( $this->profile['slug'] );
		foreach ( $groups as $group_name => $group ) {
			if ( ! empty( $group['customize'] ) ) {
				foreach ( $group['customize'] as $custom_key => $custom_config ) {
					$this->add_setting( $group_name, false, $custom_key, $custom_config );
				}
			}
			$group_default = cutemi_get_default_config_data_fields( $group_name, false, $this->profile['slug'] );
			foreach ( $group_default['fields'] as $field_name => $field ) {
				if ( ! empty( $field['customize'] ) ) {
					foreach ( $field['customize'] as $custom_key => $custom_config ) {
						$this->add_setting( $group_name, $field_name, $custom_key, $custom_config );
					}
				}
			}
		}

	}

	public function add_setting( $group_name, $field_name, $custom_key, $custom_config ) {
		global $wp_customize;

		if ( empty( $field_name ) ) {
			$main_setting = $this->id_lt_groups;
			$key          = $main_setting . '[' . $group_name . '][' . $custom_key . ']';
		} else {
			$main_setting = $this->id_lt_data;
			$key          = $main_setting . '[' . $group_name . '][fields][' . $field_name . '][' . $custom_key . ']';
		}

		$ret            = array(
			'id'   => $key,
			'args' => array(),
		);
		$simple_process = array( 'select', 'number', 'color', 'checkbox', 'url', 'code', 'text' );
		if ( 'dropdown-pages' === $custom_config['control_type'] ) {
			$ret['args'] = array(
				'sanitize_callback' => array( $this, 'dropdown_pages' ),
			);
		} elseif ( 'textarea' === $custom_config['control_type'] ) {
			$ret['args'] = array(
				'sanitize_callback' => 'sanitize_textarea_field',
			);
		} elseif ( 'css' === $custom_config['control_type'] ) {
			$ret['args'] = array(
				'sanitize_callback' => 'wp_strip_all_tags',
			);
		} elseif ( in_array( $custom_config['control_type'], $simple_process, true ) ) {
			$ret['args'] = array(
				'sanitize_callback' => array( $this, 'sanitize_' . $custom_config['control_type'] ),
			);
		}
		$ret['args']['type']       = 'option';
		$ret['args']['capability'] = 'manage_options';
		$ret['args']['default']    = $custom_config['default'];
		if ( isset( $custom_config['sanitize_callback'] ) ) {
			$ret['args']['sanitize_callback'] = $custom_config['sanitize_callback'];
		}

		$wp_customize->add_setting(
			$ret['id'],
			$ret['args']
		);

		return $ret;
	}

	private function add_controls() {
		global $wp_customize;

		$styles = cutemi_get_style_config_default( $this->profile['slug'] );
		$p      = 1;

		$controls_styles = array();

		foreach ( $styles as $group_name => $val ) {
			$p ++;

			$field_label = ucwords( implode( ' ', explode( '_', $group_name ) ) );

			if ( 'unit' === $group_name ) {
				$controls_styles[] = array(
					'name' => $group_name,
					'id'   => $this->id_styling . '[' . $group_name . ']',
					'args' => array(
						'type'        => 'select',
						'priority'    => $p,
						'section'     => $this->id_styling,
						'label'       => $this->get_setting_label( $field_label ),
						'description' => __( 'All size fields will be expressed in this unit (border-radius, border, font-size, width, etc..)', 'cute-mediainfo' ),
						'choices'     => $this->units,
					),
				);
			}

			if ( 'font_family' === $group_name ) {
				$wp_customize->add_control(
					new CUTEMI_Font_Dropdown_Customize_Control(
						$wp_customize,
						$this->id_styling . '[' . $group_name . ']',
						array(
							'label'    => __( 'Font', 'cute-mediainfo' ),
							'section'  => $this->id_styling,
							'priority' => $p,
						)
					)
				);
			}

			if ( 'google_font_family' === $group_name ) {
				$wp_customize->add_control(
					new CUTEMI_GFont_Dropdown_Customize_Control(
						$wp_customize,
						$this->id_styling . '[' . $group_name . ']',
						array(
							'label'            => __( 'Google Font', 'cute-mediainfo' ),
							'section'          => $this->id_styling,
							'priority'         => $p,
							'google_fonts_url' => get_option( 'cutemi_google_fonts_options', $this->g_font_url ),
						)
					)
				);
			}

			if ( 'mediainfo_google_font_family' === $group_name ) {
				$wp_customize->add_control(
					new CUTEMI_GFont_Dropdown_Customize_Control(
						$wp_customize,
						$this->id_styling . '[' . $group_name . ']',
						array(
							'label'            => __( 'Google Font For MediaInfo', 'cute-mediainfo' ),
							'section'          => $this->id_styling,
							'priority'         => $p,
							'google_fonts_url' => get_option( 'cutemi_google_fonts_options', $this->g_font_mono_url ),
						)
					)
				);
			}

			if ( is_int( $val ) || is_float( $val ) ) {
				$controls_styles[] = array(
					'id'   => $this->id_styling . '[' . $group_name . ']',
					'args' => array(
						'type'        => 'number',
						'section'     => $this->id_styling,
						'priority'    => $p,
						'label'       => $this->get_setting_label( $field_label ),
						'description' => '',
						'input_attrs' => array(
							'min'  => 0,
							'step' => 0.2,
						),
					),
				);
			}

			if (
				is_string( $val ) &&
				( '#' === substr( $val, 0, 1 ) || 'rgba(' === substr( $val, 0, 5 ) )
			) {
				$wp_customize->add_control(
					new CUTEMI_Alpha_Color_Control(
						$wp_customize,
						$this->id_styling . '[' . $group_name . ']',
						array(
							'label'        => $this->get_setting_label( $field_label ),
							'section'      => $this->id_styling,
							'priority'     => $p,
							'show_opacity' => true,
						)
					)
				);
			}

			if ( is_string( $val ) && in_array( $val, array( 'on', 'off' ), true ) ) {
				$controls_styles[] = array(
					'id'   => $this->id_styling . '[' . $group_name . ']',
					'args' => array(
						'type'        => 'checkbox',
						'section'     => $this->id_styling,
						'priority'    => $p,
						'label'       => $this->get_setting_label( $field_label ),
						'description' => '',
					),
				);
			}
		}

		foreach ( $controls_styles as $ctl ) {
			$wp_customize->add_control( $ctl['id'], $ctl['args'] );
		}

		/*
		 * GROUPS SETTINGS
		 */
		$controls_layout = array();
		$groups          = cutemi_get_data_groups_default_config( $this->profile['slug'] );
		foreach ( $groups as $group_name => $group ) {
			if ( isset( $group['flags'] ) && in_array( 'disable_autocustomize', $group['flags'], true ) ) {
				continue;
			}
			$group_label = ( 'name' === $group_name ) ? 'general' : $group_name;
			$group_label = ucwords( $group_label );

			$p ++;
			/* translators: string is the group name, ex: general,videos,audios,etc  */
			$desc              = __( 'Hide/Order "%s" block [priority 1 listed first]', 'cute-mediainfo' );
			$controls_layout[] = array(
				'id'   => $this->id_lt_groups . '[' . $group_name . '][priority]',
				'args' => array(
					'type'        => 'select',
					'priority'    => $p,
					'section'     => $this->id_lt_groups,
					'label'       => $this->get_setting_label( $group_label . ' Block' ),
					'description' => sprintf( $desc, $group_label ),
					'choices'     => $this->group_priority,
				),
			);

			$p ++;
			/* translators: string is the group name, ex: general,videos,audios,etc  */
			$desc              = __( 'Where title for "%s" block are show', 'cute-mediainfo' );
			$controls_layout[] = array(
				'id'   => $this->id_lt_groups . '[' . $group_name . '][where_head]',
				'args' => array(
					'type'        => 'select',
					'priority'    => $p,
					'section'     => $this->id_lt_groups,
					'label'       => $this->get_setting_label( $group_label . ' Header' ),
					'description' => sprintf( $desc, $group_label ),
					'choices'     => $this->head_position,
				),
			);

			$p ++;
			/* translators: string is the group name, ex: general,videos,audios,etc  */
			$desc              = __( 'How title for "%s" are show', 'cute-mediainfo' );
			$controls_layout[] = array(
				'id'   => $this->id_lt_groups . '[' . $group_name . '][show_as]',
				'args' => array(
					'type'        => 'select',
					'priority'    => $p,
					'section'     => $this->id_lt_groups,
					'label'       => $this->get_setting_label( $group_label . ' Header Layout' ),
					'description' => sprintf( $desc, $group_label ),
					'choices'     => $this->head_layouts,
				),
			);

		}

		/*
		 * DATA CELLS SETTINGS
		 */
		$groups = cutemi_get_data_groups_default_config( $this->profile['slug'] );
		foreach ( $groups as $group_name => $group ) {
			$group_default = cutemi_get_default_config_data_fields( $group_name, false, $this->profile['slug'] );
			foreach ( $group_default['fields'] as $field_name => $field ) {
				if ( isset( $field['flags'] ) && in_array( 'disable_autocustomize', $field['flags'], true ) ) {
					continue;
				}
				$group_label = ( 'name' === $group_name ) ? 'general' : $group_name;
				$field_label = ( 'external_id' === $field_name ) ? 'link' : $field_name;

				$field_label = $group_label . ': ' . implode( ' ', explode( '_', $field_label ) );
				$field_label = ucwords( $field_label );

				$base_id = $this->id_lt_data . '[' . $group_name . '][fields][' . $field_name . ']';

				$opts = $this->get_data_layout_opts( $group_name, $field_name );
				if ( count( $opts ) < 2 ) {
					continue;
				}
				$p ++;
				/* translators: string is the field label, ex: Size, Format, Audio Tech  */
				$desc              = __( 'How %s title are show', 'cute-mediainfo' );
				$controls_layout[] = array(
					'id'   => $base_id . '[show_as]',
					'args' => array(
						'type'        => 'select',
						'priority'    => $p,
						'section'     => $this->id_lt_data,
						'label'       => $this->get_setting_label( $field_label . ' Layout' ),
						'description' => sprintf( $desc, $field_label ),
						'choices'     => $opts,
					),
				);
				if ( 'rows' === $group_default['type'] ) {
					$p ++;
					$controls_layout[] = array(
						'id'   => $base_id . '[row]',
						'args' => array(
							'type'     => 'select',
							'priority' => $p,
							'section'  => $this->id_lt_data,
							'label'    => $this->get_setting_label( $field_label . ' Row Number' ),
							'choices'  => $this->data_row,
						),
					);
				}

				if ( count( $group_default['fields'] ) > 1 ) {
					$p ++;
					$controls_layout[] = array(
						'id'   => $base_id . '[position]',
						'args' => array(
							'type'        => 'select',
							'priority'    => $p,
							'section'     => $this->id_lt_data,
							'label'       => $this->get_setting_label( $field_label . ' Order' ),
							'description' => __( 'position: 1 2 3...', 'cute-mediainfo' ),
							'choices'     => $this->data_position,
						),
					);
				}
			}
		}

		foreach ( $controls_layout as $ctl ) {
			$wp_customize->add_control( $ctl['id'], $ctl['args'] );
		}

		$controls_layout = array();

		$groups = cutemi_get_data_groups_default_config( $this->profile['slug'] );
		foreach ( $groups as $group_name => $group ) {
			if ( ! empty( $group['customize'] ) ) {
				foreach ( $group['customize'] as $custom_key => $custom_config ) {
					$p ++;
					$controls_layout[] = $this->get_customize_control( $group_name, false, $custom_key, $p, $custom_config );
				}
			}

			$group_default = cutemi_get_default_config_data_fields( $group_name, false, $this->profile['slug'] );
			foreach ( $group_default['fields'] as $field_name => $field ) {
				if ( ! empty( $field['customize'] ) ) {
					foreach ( $field['customize'] as $custom_key => $custom_config ) {
						$p ++;
						$controls_layout[] = $this->get_customize_control( $group_name, $field_name, $custom_key, $p, $custom_config );
					}
				}
			}
		}

		foreach ( $controls_layout as $ctl ) {
			$wp_customize->add_control( $ctl['id'], $ctl['args'] );
		}

	}

	private function get_setting_label( $default_label ) {

		$label = apply_filters( 'cutemi_customizer_label', '', $default_label );
		if ( ! empty( $label ) ) {
			return $label;
		}

		return isset( $this->labels[ $default_label ] ) ? $this->labels[ $default_label ] : $default_label;
	}

	private function get_data_layout_opts( $group, $name_field ) {
		$opts = $this->data_layouts;

		$no_img      = array( 'name|desc', 'links|size', 'links|external_id' );
		$require_txt = array( 'name|desc', 'name|date', 'name|size', 'name|duration', 'links|size', 'links|part' );
		$only_pre    = array( 'mediainfo|mediainfo' );
		$can_pre     = array( 'mediainfo|mediainfo', 'name|desc' );
		$can_link    = array( 'links|external_id' );

		$actual = $group . '|' . $name_field;

		$to_quit = array();
		if ( ! in_array( $actual, $can_link, true ) ) {
			$to_quit[] = 'link';
		}
		if ( ! in_array( $actual, $can_pre, true ) ) {
			$to_quit[] = 'pre';
		}
		if ( in_array( $actual, $no_img, true ) ) {
			$to_quit[] = 'img';
			$to_quit[] = 'img_txt';
			$to_quit[] = 'img_n_txt';
			$to_quit[] = 'img_txt_flex';
		}
		if ( in_array( $actual, $require_txt, true ) ) {
			$to_quit[] = 'img';
		}
		foreach ( $to_quit as $quit_me ) {
			if ( isset( $opts[ $quit_me ] ) ) {
				unset( $opts[ $quit_me ] );
			}
		}
		if ( in_array( $actual, $only_pre, true ) ) {
			$opts = array( 'pre' => $opts['pre'] );
		}

		return apply_filters( 'cutemi_customize_data_layout_opts', $opts, $group, $name_field );
	}

	private function get_customize_control( $group_name, $field_name, $custom_key, $default_priority, $custom_config ) {
		if ( empty( $field_name ) ) {
			$main_setting = $this->id_lt_groups;
			$key          = $main_setting . '[' . $group_name . '][' . $custom_key . ']';
		} else {
			$main_setting = $this->id_lt_data;
			$key          = $main_setting . '[' . $group_name . '][fields][' . $field_name . '][' . $custom_key . ']';
		}

		$ret = array(
			'id'   => $key,
			'args' => array(),
		);

		$simple_process = array( 'number', 'color', 'checkbox', 'text', 'textarea', 'url', 'dropdown-pages' );
		if ( 'select' === $custom_config['control_type'] ) {
			$ret['args'] = array(
				'type'    => 'select',
				'choices' => $custom_config['options'],
			);
		} elseif ( in_array( $custom_config['control_type'], array( 'css', 'code' ), true ) ) {
			$ret['args'] = array(
				'type' => 'textarea',
			);
		} elseif ( in_array( $custom_config['control_type'], $simple_process, true ) ) {
			$ret['args'] = array(
				'type' => $custom_config['control_type'],
			);
		}

		$ret['args']['section']  = $main_setting;
		$ret['args']['priority'] = isset( $custom_config['priority'] ) ? $custom_config['priority'] : $default_priority;
		$ret['args']['label']    = $custom_config['label'];
		if ( isset( $custom_config['description'] ) ) {
			$ret['args']['description'] = $custom_config['description'];
		}
		if ( isset( $custom_config['input_attrs'] ) ) {
			$ret['args']['input_attrs'] = $custom_config['input_attrs'];
		}

		return $ret;

	}

	public function sanitize_code( $input, $setting ) {
		return wp_kses( $input, cutemi_get_allowed_html() );
	}

	public function sanitize_text( $input, $setting ) {
		return sanitize_text_field( $input );
	}

	public function sanitize_select( $input, $setting ) {
		$choices = $setting->manager->get_control( $setting->id )->choices;

		return ( array_key_exists( $input, $choices ) ? $input : $setting->default );
	}

	public function sanitize_dropdown_pages( $input, $setting ) {
		$page_id = absint( $input );

		return ( get_post_status( $page_id ) === 'publish' ? $page_id : $setting->default );
	}

	public function sanitize_checkbox( $input, $setting ) {
		if ( in_array( $input, array( true, 1, '1', 'on', 'true' ), true ) ) {
			return 'on';
		}

		return '';
	}

	public function sanitize_url( $input, $setting ) {
		return esc_url_raw( $input );
	}

	public function sanitize_unit( $val, $setting ) {
		if ( isset( $this->units[ $val ] ) ) {
			return $val;
		}

		return $setting->default;
	}

	public function sanitize_number( $number, $setting ) {
		$number = abs( $number );
		$attrs  = $setting->manager->get_control( $setting->id )->input_attrs;
		$min    = ( isset( $attrs['min'] ) ? $attrs['min'] : $number );
		$max    = ( isset( $attrs['max'] ) ? $attrs['max'] : $number );

		return ( $min <= $number && $number <= $max && is_numeric( $number ) ) ? floatval( $number ) : $setting->default;
	}

	public function sanitize_font( $val, $setting ) {
		if ( substr( $val, 0, 1 ) === '{' ) {
			$font = json_decode( $val, true );
			if ( empty( $font ) || ! isset( $font['family'] ) ) {
				return $setting->default;
			}
			$save           = array(
				'label'  => '',
				'family' => '',
				'wght'   => '',
				'wdth'   => '',
				'ital'   => '',
				'type'   => 'gfont',
			);
			$save['family'] = sanitize_text_field( $font['family'] );
			if ( isset( $font['label'] ) ) {
				$save['label'] = sanitize_text_field( $font['label'] );
			}
			if ( in_array( $font['ital'], array( '0', '1' ), true ) ) {
				$save['ital'] = $font['ital'];
			}
			if ( isset( $font['wght'] ) && is_numeric( $font['wght'] ) ) {
				$save['wght'] = $font['wght'];
			}
			if ( isset( $font['wdth'] ) && is_numeric( $font['wdth'] ) ) {
				$save['wdth'] = $font['wdth'];
			}
			if ( isset( $font['type'] ) && in_array( $font['type'], array( 'gfont', 'common' ), true ) ) {
				$save['type'] = $font['type'];
			}

			return wp_json_encode( $save );
		} else {
			return sanitize_text_field( $val );
		}
	}

	public function sanitize_css_color( $color ) {
		if ( empty( $color ) ) {
			return '';
		}

		return cutemi_check_color( $color );
	}

}

$profiles = cutemi_get_profiles( true );
foreach ( $profiles as $profile_id => $profile ) {
	$profile['id'] = $profile_id;
	new CUTEMI_Customize_Settings( $profile );
}
