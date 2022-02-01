<?php
/**
 * Class for parse saved options and defaults values, and get styles, layout/blocks and data for a specific profile
 */
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

class CUTEMI_Table_Template {

	/**
	 * The profile handle.
	 *
	 * @var string
	 */
	public $profile_id;

	/**
	 * Info/layout to show for actual profile_id.
	 * Example:
	 *    [
	 *      [
	 *          fields       => [...],
	 *          type         => "rows"|"arr"|"unique",
	 *          group-val    => [...],
	 *          group-config => [...]
	 *      ],
	 *      [
	 *          type         => "rows",
	 *          fields       => [ //Only for type "arr": each row is an array of fields, else only array of fields
	 *                              [
	 *                                  [
	 *                                      "show_as": "img",
	 *                                      "row": 1,
	 *                                      "position": 2,
	 *                                      "tooltip": "text",
	 *                                      "text": 2,
	 *                                      "img": true,
	 *                                      "id": "format"
	 *                                  ],
	 *                                   ...row 1 fields..
	 *                               ],
	 *                              [
	 *                                   ...row 2 fields..
	 *                               ],
	 *                          ],
	 *          group-val    => [
	 *              "img": "block-general",
	 *              "text": "%%name%%",
	 *              "desc": ""
	 *          ],
	 *          group-config => [
	 *              "priority": 1,
	 *              "where_head": "top",
	 *              "show_as": "img_txt",
	 *              "id": "name",
	 *              "text": true,
	 *              "img": true,
	 *              "class": "cutemi-group-top-head"
	 *          ]
	 *      ],
	 *    ],
	 *    [ ... more blocks...
	 *
	 * @var array
	 */
	public $layout;

	/**
	 * CUTEMI_Table_Template constructor.
	 *
	 * @param string $profile Profile slug
	 */
	public function __construct( $profile ) {
		if ( is_array( $profile ) ) {
			$this->profile_id = $profile['id'];
		} else {
			$this->profile_id = $profile;
		}
		$this->load_table_rows();
	}

	/**
	 * Read options and complete layout for the profile, also set layout property.
	 *
	 * @return array    The layout for the profile
	 */
	private function load_table_rows() {
		$this->layout = wp_cache_get( $this->profile_id, 'cutemi_table_layout' );
		if ( is_customize_preview() || ! $this->layout ) {
			$this->layout = array();
			$groups       = $this->groups_config();
			foreach ( $groups as $group ) {
				$setting_group                 = $this->data_fields( $group['id'] );
				$setting_group['group-val']    = $this->group_val( $group['id'] );
				$setting_group['group-config'] = $this->group_config( $group['show_as'], $group['where_head'] );
				$setting_group['group-config'] = array_merge( $group, $setting_group['group-config'] );

				foreach ( $setting_group['fields'] as $k => $field_config ) {
					$merge                         = $this->extra_cell_data_config( $field_config, $group['id'], $k );
					$merge['id']                   = $k;
					$setting_group['fields'][ $k ] = array_merge( $field_config, $merge );
				}

				if ( 'rows' === $setting_group['type'] ) {
					$order_fields = array();
					foreach ( $setting_group['fields'] as $k => $field_config ) {
						if ( ! isset( $order_fields[ $field_config['row'] ] ) ) {
							$order_fields[ $field_config['row'] ] = array();
						}
						$order_fields[ $field_config['row'] ][] = $field_config;
					}
					ksort( $order_fields );
					$setting_group['fields'] = $order_fields;
				}
				$this->layout[] = $setting_group;
			}
			if ( ! is_customize_preview() ) {
				wp_cache_add( $this->profile_id, $this->layout, 'cutemi_table_layout', 5 );
			}
		}

		return $this->layout;
	}

	/**
	 * Get groups settings mixing options and defaults values.
	 *
	 * @param bool $for_export If is for export skip set defaults customize properties, sort, priority zero remove..
	 *
	 * @return array
	 */
	public function groups_config( $for_export = false ) {
		$layout_groups_config = get_option( 'cutemi_layout_groups', array() );
		if ( isset( $layout_groups_config[ $this->profile_id ] ) ) {
			$layout_groups_config = $layout_groups_config[ $this->profile_id ];
		} else {
			$layout_groups_config = array();
		}

		$defaults = cutemi_get_data_groups_default_config( $this->profile_id );

		//complete options
		foreach ( $defaults as $key => $value ) {
			if ( ! isset( $layout_groups_config[ $key ] ) ) {
				$layout_groups_config[ $key ] = $value; // Add the new key -> value pair.
			} else {
				if ( is_array( $value ) ) {
					foreach ( $value as $k => $v ) {
						if ( ! isset( $layout_groups_config[ $key ][ $k ] ) ) {
							$layout_groups_config[ $key ][ $k ] = $v;
						}
					}
				}
			}
		}

		if ( $for_export ) {
			return $layout_groups_config;
		}

		foreach ( $layout_groups_config as $k => $config ) {
			//remove if priority=0, or not in default (ex: external module disabled)
			if ( 0 === intval( $config['priority'] ) || ! isset( $defaults[ $k ] ) ) {
				unset( $layout_groups_config[ $k ] );
			}
			if ( isset( $config['customize'] ) ) {
				foreach ( $config['customize'] as $k2 => $v2 ) {
					if ( isset( $v2['default'] ) && ! isset( $layout_groups_config[ $k ][ $k2 ] ) ) {
						$layout_groups_config[ $k ][ $k2 ] = $v2['default'];
					}
				}
				unset( $layout_groups_config[ $k ]['customize'] );
			}
		}

		usort(
			$layout_groups_config,
			function ( $a, $b ) {
				return strcmp( $a['priority'], $b['priority'] );
			}
		);

		return $layout_groups_config;
	}

	/**
	 * Get layout/data for a group/block settings mixing options and defaults values.
	 *
	 * @param string $group The block id
	 * @param bool $for_export Skip set defaults customize properties, sort, priority zero remove..
	 *
	 * @return array
	 */
	public function data_fields( $group, $for_export = false ) {
		$layout_data_config = get_option( 'cutemi_layout_data', array() );

		if ( isset( $layout_data_config[ $this->profile_id ] ) ) {
			$layout_data_config = $layout_data_config[ $this->profile_id ];
		} else {
			$layout_data_config = array();
		}

		if ( isset( $layout_data_config[ $group ] ) ) {
			$layout_data_config = $layout_data_config[ $group ];
		} else {
			$layout_data_config = array();
		}
		$defaults = cutemi_get_default_config_data_fields( $group, false, $this->profile_id );

		//complete options
		foreach ( $defaults as $key => $value ) {
			if ( ! isset( $layout_data_config[ $key ] ) ) {
				$layout_data_config[ $key ] = $value; // Add the new key -> value pair.
			} elseif ( is_array( $value ) ) {
				foreach ( $value as $k => $v ) {
					if ( ! isset( $layout_data_config[ $key ][ $k ] ) ) {
						$layout_data_config[ $key ][ $k ] = $v;
					} elseif ( is_array( $v ) ) {
						foreach ( $v as $k2 => $v2 ) {
							if ( ! isset( $layout_data_config[ $key ][ $k ][ $k2 ] ) ) {
								$layout_data_config[ $key ][ $k ][ $k2 ] = $v2;
							}
						}
					}
				}
			}
		}

		if ( $for_export ) {
			return $layout_data_config;
		}

		if ( ! isset( $layout_data_config['fields'] ) ) {
			$layout_data_config['fields'] = array();
		}

		//Remove if not on default or customize default
		foreach ( $layout_data_config as $key => $value ) {
			if ( ! isset( $defaults[ $key ] ) ) {
				unset( $layout_data_config[ $key ] );
			} elseif ( is_array( $value ) ) {
				foreach ( $value as $k => $v ) {
					if (
						! isset( $defaults[ $key ][ $k ] ) &&
						( ! isset( $defaults[ $key ]['customize'] ) || ! isset( $defaults[ $key ]['customize'][ $k ] ) )
					) {
						unset( $layout_data_config[ $key ][ $k ] );
					} elseif ( is_array( $v ) ) {
						foreach ( $v as $k2 => $v2 ) {
							if (
								! isset( $defaults[ $key ][ $k ][ $k2 ] ) &&
								(
									! isset( $defaults[ $key ][ $k ]['customize'] ) ||
									! isset( $defaults[ $key ][ $k ]['customize'][ $k2 ] )
								)
							) {
								unset( $layout_data_config[ $key ][ $k ][ $k2 ] );
							}
						}
					}
				}
			}
		}

		foreach ( $layout_data_config['fields'] as $k => $config ) {
			if ( 'none' === $config['show_as'] ) {
				unset( $layout_data_config['fields'][ $k ] );
			}
			if ( isset( $config['customize'] ) ) {
				foreach ( $config['customize'] as $k2 => $v2 ) {
					if ( isset( $v2['default'] ) && ! isset( $layout_data_config['fields'][ $k ][ $k2 ] ) ) {
						$layout_data_config['fields'][ $k ][ $k2 ] = $v2['default'];
					}
				}
				unset( $layout_data_config['fields'][ $k ]['customize'] );
			}
		}

		uasort(
			$layout_data_config['fields'],
			function ( $a, $b ) {
				return (int) $a['position'] > (int) $b['position'];
			}
		);

		return $layout_data_config;
	}

	/**
	 * Get group val. This defines what will be displayed in the block header.
	 *
	 * @param string $group_id The block (name|videos|audios|texts|links|mediainfo...)
	 *
	 * @return array
	 */
	public function group_val( $group_id ) {
		$val = array();
		if ( 'name' === $group_id ) {
			$val = array(
				'img'  => 'block-general',
				'text' => '%%name%%',
				'desc' => '',
			);
		} elseif ( 'videos' === $group_id ) {
			$val = array(
				'img'  => 'block-video',
				'text' => 'Video',
				'desc' => '',
			);
		} elseif ( 'audios' === $group_id ) {
			$val = array(
				'img'  => 'block-audio',
				'text' => 'Audio',
				'desc' => '',
			);
		} elseif ( 'texts' === $group_id ) {
			$val = array(
				'img'  => 'block-text',
				'text' => 'Text',
				'desc' => '',
			);
		} elseif ( 'links' === $group_id ) {
			$val = array(
				'img'  => 'block-link',
				'text' => 'Links',
				'desc' => '',
			);
		} elseif ( 'mediainfo' === $group_id ) {
			$val = array(
				'img'  => 'mediainfo',
				'text' => 'MediaInfo',
				'desc' => '',
			);
		}

		return apply_filters( 'cutemi_get_data_group_val', $val, $group_id, $this->profile_id );
	}

	/**
	 * Define how and what to show in the header from the configuration show_as and where_head
	 *
	 * @param string $show_as The mode to show (img_txt|img_n_txt|img|txt)
	 * @param string $where_head Where the header will be displayed (top|left|top-left|no-head)
	 * @param array $ini_config Array to merge with result
	 *
	 * @return mixed|void
	 */
	public function group_config( $show_as, $where_head = 'left', $ini_config = array() ) {
		$config = array();
		if ( 'img_txt' === $show_as ) {
			$config = array(
				'text' => true,
				'img'  => true,
			);
		} elseif ( 'img_n_txt' === $show_as ) {
			$config = array(
				'text'        => true,
				'img'         => true,
				'label_class' => 'cutemi-img-txt',
			);
		} elseif ( 'img' === $show_as ) {
			$config = array(
				'tooltip' => 'text',
				'text'    => 2,
				'img'     => true,
			);
		} elseif ( 'txt' === $show_as ) {
			$config = array(
				'text' => true,
				'img'  => false,
			);
		}

		if ( 'top' === $where_head ) {
			$config['class'] = 'cutemi-group-top-head';
		} elseif ( 'left' === $where_head ) {
			$config['class'] = 'cutemi-group-left-head';
		} elseif ( 'left-top' === $where_head ) {
			$config['class'] = 'cutemi-group-left-top-head';
		} elseif ( 'no-head' === $where_head ) {
			$config['class'] = 'cutemi-group-top-head cutemi-group-no-head';
		}

		$config = array_merge( $ini_config, $config );

		return apply_filters( 'cutemi_get_group_config', $config, $show_as, $where_head, $this->profile_id );
	}

	/**
	 * Define how and what to show for a field
	 *
	 * @param array $field_config Field configuration, an array with at least the 'show_as' property
	 * @param string $group block_id/group_id of the field
	 * @param string $field Field id
	 *
	 * @return array
	 */
	public function extra_cell_data_config( $field_config, $group = '', $field = '' ) {
		$config = array();
		if ( 'img_txt' === $field_config['show_as'] ) {
			$config = array(
				'tooltip' => 'desc',
				'class'   => 'cutemi-inline-img-txt',
				'text'    => true,
				'img'     => true,
			);
		} elseif ( 'img_n_txt' === $field_config['show_as'] ) {
			$config = array(
				'tooltip' => 'desc',
				'class'   => 'cutemi-img-txt',
				'text'    => true,
				'img'     => true,
			);
		} elseif ( 'img_txt_flex' === $field_config['show_as'] ) {
			$config = array(
				'tooltip' => 'desc',
				'class'   => 'cutemi-img-txt cutemi-inline-flex',
				'text'    => true,
				'img'     => true,
			);
		} elseif ( 'img' === $field_config['show_as'] ) {
			$config = array(
				'tooltip' => 'text',
				'text'    => 2,
				'img'     => true,
			);
		} elseif ( 'txt' === $field_config['show_as'] ) {
			$config = array(
				'tooltip' => 'desc',
				'text'    => true,
				'img'     => false,
			);
		} elseif ( 'txt_top_label' === $field_config['show_as'] ) {
			$config = array(
				'tooltip' => 'desc',
				'text'    => true,
				'img'     => false,
			);
		} elseif ( 'pre' === $field_config['show_as'] ) {
			$config = array(
				'class' => 'cutemi-cell-multiline',
				'text'  => true,
				'img'   => false,
			);
		} elseif ( 'link' === $field_config['show_as'] ) {
			$config = array(
				'text' => true,
			);
		} elseif ( 'multiline' === $field_config['show_as'] ) {
			$config = array(
				'class' => 'cutemi-cell-multiline',
				'nl2br' => true,
				'text'  => true,
				'img'   => false,
			);
		}

		$extras = array(
			'name'  => array(
				'size'     => array(
					'icon' => 'size',
				),
				'date'     => array(
					'icon' => 'date',
				),
				'duration' => array(
					'icon' => 'duration',
				),
			),
			'links' => array(
				'part' => array(
					'icon' => 'part',
				),
			),
		);

		if ( isset( $extras[ $group ] ) && isset( $extras[ $group ][ $field ] ) ) {
			$config = array_merge( $config, $extras[ $group ][ $field ] );
		}

		return apply_filters( 'cutemi_get_cell_data_config', $config, $field_config, $group, $field, $this->profile_id );
	}

	/**
	 *  Get styles for the profile settings mixing options and defaults values.
	 *
	 * @param bool $for_export Skip fonts handle and google fonts parse..
	 *
	 * @return array
	 */
	public function get_styles_config( $for_export = false ) {
		$style_config = get_option( 'cutemi_styling', array() );
		if ( isset( $style_config[ $this->profile_id ] ) ) {
			$style_config = $style_config[ $this->profile_id ];
		} else {
			$style_config = array();
		}

		$defaults = cutemi_get_style_config_default( $this->profile_id );

		//complete options
		foreach ( $defaults as $key => $value ) {
			if ( ! isset( $style_config[ $key ] ) ) {
				$style_config[ $key ] = $value; // Add the new key -> value pair.
			}
		}

		//not empty
		if ( empty( $style_config['unit'] ) ) {
			$style_config['unit'] = 'px';
		}

		if ( $for_export ) {
			return $style_config;
		}

		//set google font to load
		if ( ! empty( $style_config['font_family'] ) && substr( $style_config['font_family'], 0, 1 ) === '{' ) {

			$font = json_decode( $style_config['font_family'], true );

			$style_config['font_family'] = $font['family'];
			if ( ! empty( $font['wght'] ) ) {
				$style_config['font_weight'] = $font['wght'];
			}
			if ( ! empty( $font['ital'] ) ) {
				$style_config['font_variant'] = 'italic';
			}
			if ( ! empty( $font['wdth'] ) ) {
				$style_config['font_stretch'] = $font['wdth'];
			}
		}

		$g_fonts = array();

		//set google font
		if ( ! empty( $style_config['google_font_family'] ) ) {
			if ( substr( $style_config['google_font_family'], 0, 1 ) === '{' ) {
				$font                               = json_decode( $style_config['google_font_family'], true );
				$style_config['google_font_family'] = $font['family'];

				//set defaults
				$style_config['font_weight']  = '400';
				$style_config['font_variant'] = '';
				$style_config['font_stretch'] = '';
				if ( ! empty( $font['ital'] ) ) {
					$style_config['font_variant'] = 'italic';
				}
				if ( ! empty( $font['wght'] ) ) {
					$style_config['font_weight'] = $font['wght'];
				}
				if ( ! empty( $font['wdth'] ) ) {
					$style_config['font_stretch'] = $font['wdth'];
				}

				$g_fonts[] = $font;
			} else {
				$g_fonts[] = array(
					'family' => $style_config['google_font_family'],
				);
			}
			if ( ! empty( $style_config['font_family'] ) ) {
				$style_config['font_family'] = array( $style_config['google_font_family'], $style_config['font_family'] );
			} else {
				$style_config['font_family'] = $style_config['google_font_family'];
			}
		}
		if ( ! empty( $style_config['mediainfo_google_font_family'] ) ) {
			if ( substr( $style_config['mediainfo_google_font_family'], 0, 1 ) === '{' ) {
				$font = json_decode( $style_config['mediainfo_google_font_family'], true );
				$style_config['mediainfo_google_font_family'] = $font['family'];
				if ( ! empty( $font['ital'] ) ) {
					$style_config['mediainfo_google_font_variant'] = 'italic';
				}
				if ( ! empty( $font['wght'] ) ) {
					$style_config['mediainfo_google_font_weight'] = $font['wght'];
				}
				if ( ! empty( $font['wdth'] ) ) {
					$style_config['mediainfo_google_font_stretch'] = $font['wdth'];
				}
				$g_fonts[] = $font;
			} else {
				$g_fonts[] = array(
					'family' => $style_config['mediainfo_google_font_family'],
				);
			}
		}

		if ( ! empty( $g_fonts ) ) {
			//group by family
			$grouped = array();
			foreach ( $g_fonts as $g_font ) {
				if ( ! isset( $grouped[ $g_font['family'] ] ) ) {
					$grouped[ $g_font['family'] ] = array();
				}
				$grouped[ $g_font['family'] ][] = $g_font;
			}

			$g_font_reqs = array();
			foreach ( $grouped as $family_name => $family ) {
				//get used axis
				$axis         = array();
				$support_axis = array( 'ital', 'wdth', 'wght' );
				foreach ( $family as $family_item ) {
					foreach ( $support_axis as $_axis ) {
						if ( ! in_array( $_axis, $axis, true ) && ! empty( $family_item[ $_axis ] ) ) {
							$axis[] = $_axis;
						}
					}
				}
				$font_req = '';
				if ( empty( $axis ) ) {
					foreach ( $family as $use ) {
						$font_req = 'family=' . str_replace( ' ', '+', $use['family'] );
					}
				} else {
					sort( $axis );
					$variants = array();
					foreach ( $family as $use ) {
						$s = array();
						foreach ( $axis as $k ) {
							if ( isset( $use[ $k ] ) ) {
								$s[] = $use[ $k ];
							} else {
								//default
								if ( 'ital' === $k ) {
									$s[] = '0';
								} elseif ( 'wdth' === $k ) {
									$s[] = '100';
								} elseif ( 'wght' === $k ) {
									$s[] = '400';
								}
							}
						}
						$variants[] = implode( ',', $s );
					}
					$variants = array_unique( $variants );

					$font_req  = 'family=' . str_replace( ' ', '+', $family_name ) .
								':' . implode( ',', $axis ) . '@';
					$font_req .= implode( ';', $variants );
				}
				if ( ! empty( $font_req ) ) {
					$g_font_reqs[] = $font_req;
				}
			}
			$style_config['font_import'] = 'https://fonts.googleapis.com/css2?' . implode( '&', $g_font_reqs ) .
											'&display=swap';
		}

		return $style_config;
	}

}
