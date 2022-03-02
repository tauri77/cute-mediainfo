<?php

/**
 * Simple get content for a mediainfo
 *
 * @param int|WP_Post $post The post (with post_type "cute_mediainfo")
 * @param string $profile The profile slug
 *
 * @return string
 */
function cutemi_get_post_content( $post, $profile ) {

	if ( is_numeric( $post ) ) {
		$post = get_post( $post );
	}
	if ( empty( $post ) || 'cute_mediainfo' !== $post->post_type ) {
		return '';
	}

	$video = cutemi_get_mediainfo_data( $post );

	return cutemi_get_mediainfo_content( $video, $profile, $post );
}


/**
 * Get the code to render the mediainfo with specific profile.
 *
 * @param array $mediainfo       Video data as associative array.
 * @param bool|string $profile   Optional. Profile slug. Default false.
 * @param bool|int|WP_Post $post Optional. The post. Default false.
 * @param bool $profile_disabled Optional. Include disabled profile(for customizer). Default false.
 *
 * @return string
 */
function cutemi_get_mediainfo_content( $mediainfo, $profile = false, $post = false, $profile_disabled = false ) {

	if ( empty( $profile ) ) {
		$profile = cutemi_get_default_profile();
	}

	//Check if profile is php templated
	$templated = cutemi_get_templated_profile( $profile );
	if ( $templated ) {
		//Set global mediainfo var
		$GLOBALS['mediainfo'] = $mediainfo;
		/**
		 * render as php templated
		 */
		ob_start();
		/** @noinspection PhpIncludeInspection */
		include $templated;
		$out = ob_get_clean();

		return is_string( $out ) ? $out : '';
	}

	$profiles = cutemi_get_profiles( $profile_disabled );
	if ( ! is_customize_preview() && ! isset( $profiles[ $profile ] ) ) {
		$profile = cutemi_get_default_profile();
	}

	$table_template = cutemi_get_table_template( $profile, $mediainfo, $post );

	$out = '<div class="cutemi-template cutemi-template-' . esc_attr( $profile ) . '">';

	foreach ( $table_template->layout as $row_idx => $group ) {
		$group_out = '';
		$all_empty = true;
		$max_rows  = 1;

		$group_val   = $group['group-val'];
		$group_field = $group['group-config'];

		//Count max of rows for the fields
		if ( isset( $mediainfo[ $group_field['id'] . '-count' ] ) ) {
			$max_rows = $mediainfo[ $group_field['id'] . '-count' ];
		}

		$max_count_cols = 0;
		if ( 'arr' === $group['type'] ) {
			//Show multi row block group
			$group_out .= '<ul class="cutemi-list cutemi-col-count-sub cutemi-group-count-' . esc_attr( $max_rows ) . '">';

			//Show only no all empty cells.
			$showing_fields = array();
			for ( $idx = 0; $idx < $max_rows; $idx ++ ) {
				foreach ( $group['fields'] as $field ) {
					$showing_fields[ $field['id'] ] = 0;
				}
			}
			for ( $idx = 0; $idx < $max_rows; $idx ++ ) {
				foreach ( $group['fields'] as $field ) {
					if ( ! empty( $mediainfo[ $group_field['id'] ][ $idx ][ $field['id'] ]['text'] ) ) {
						$showing_fields[ $field['id'] ] = 1;
					}
				}
			}

			for ( $idx = 0; $idx < $max_rows; $idx ++ ) {
				$count_col_row = 0;
				$out_row       = '';
				foreach ( $group['fields'] as $field ) {
					if ( 'part' === $field['id'] ) { // no show parts on only one part
						if ( $mediainfo['parts'] < 2 ) {
							continue;
						}
					}
					if ( 'size' === $field['id'] ) { // no show size on only one part?
						if ( $mediainfo['parts'] < 2 && get_option( 'cutemi_link_size_1_part', 'off' ) === 'off' ) {
							continue;
						}
					}
					if ( ! empty( $mediainfo[ $group_field['id'] ][ $idx ][ $field['id'] ]['text'] ) ) {
						$all_empty = false;
						$count_col_row ++;
					}

					/**
					 * cell code already escaped
					 */
					$cell = cutemi_get_table_cell( $mediainfo, $group_field, $post, $field, $idx );

					if ( ! empty( $cell ) && '<span></span>' !== $cell ) {
						$out_row .= $cell;
					} else {
						if ( false !== $cell && $showing_fields[ $field['id'] ] ) {
							$_classes  = isset( $field['class'] ) ? $field['class'] : '';
							$_classes .= ' cutemi-cell cutemi-cell-' . $field['id'];
							$out_row  .= '<li class="' . esc_attr( $_classes ) . '"><span>-</span></li>';
							$count_col_row ++;
						}
					}
				}
				if ( $count_col_row > $max_count_cols ) {
					$max_count_cols = $count_col_row;
				}
				$group_out .= '<li class="cutemi-row">';
				$group_out .= '<ul class="cutemi-list cutemi-row-cols-' . esc_attr( $count_col_row ) . '">';
				/**
				 * $out_row are already escaped!
				 */
				$group_out .= $out_row . '<div class="cutemi-only-texting">, </div>';
				$group_out .= '</ul>';
				$group_out .= '</li>';
			}
			$group_out .= '</ul> ';
		} elseif ( 'rows' === $group['type'] ) {
			//Show simple rows block group, aka General, Mediainfo...
			$max_count_cols = 0;
			foreach ( $group['fields'] as $row_fields ) {
				$count_cols = 0;
				$out_row    = '';
				foreach ( $row_fields as $field ) {
					if ( ! empty( $mediainfo[ $field['id'] ]['text'] ) ) {
						$all_empty = false;
						$count_cols ++;
					}
					/**
					 * cell code already escaped
					 */
					$cell = cutemi_get_table_cell( $mediainfo, false, $post, $field );
					if ( ! empty( $cell ) && '<span></span>' !== $cell ) {
						$out_row .= $cell;
					}
				}
				if ( $count_cols > 0 ) {
					$group_out .= '<li class="cutemi-row">';
					$group_out .= '<ul class="cutemi-list cutemi-row-cols-' . esc_attr( $count_cols ) . '">';
					/**
					 * $out_row are already escaped!
					 */
					$group_out .= $out_row . '<div class="cutemi-only-texting">, </div>';
					$group_out .= '</ul>';
					$group_out .= '</li>';
				}
				if ( $max_count_cols < $count_cols ) {
					$max_count_cols = $count_cols;
				}
			}
			/**
			 * $group_out are already escaped!
			 */
			$group_out = '<ul class="cutemi-list cutemi-col-count-sub cutemi-group-count-' .
							esc_attr( $max_count_cols ) . '">' . $group_out . '</ul> ';
		} else {
			//Show simple row block group...
			if ( count( $group['fields'] ) > 0 ) {
				$max_count_cols = 0;
				$row_out        = '';
				foreach ( $group['fields'] as $field ) {
					if ( ! empty( $mediainfo[ $field['id'] ]['text'] ) ) {
						$all_empty = false;
						$max_count_cols ++;
					}
					$cell = cutemi_get_table_cell( $mediainfo, false, $post, $field );
					if ( ! empty( $cell ) && '<span></span>' !== $cell ) {
						$row_out .= $cell;
					}
				}
				$group_out .= '<ul class="cutemi-list cutemi-row cutemi-row-cols-' . count( $group['fields'] ) . '">';
				/**
				 * $row_out are already escaped!
				 */
				$group_out .= $row_out;
				$group_out .= '</ul> ';
			}
		}
		$pre_group_out  = '';
		$post_group_out = '';
		if ( isset( $group['group-config'] ) ) {

			//GROUP CONTAINER
			$classes = 'cutemi-group cutemi-group-id-' . $group['group-config']['id'];
			if ( ! empty( $group_field['class'] ) ) {
				$classes .= ' ' . $group_field['class'];
			}
			$classes .= ' cutemi-group-cols-' . $max_count_cols;

			$pre_group_out .= '<div class="' . esc_attr( $classes ) . '">';

			//LABEL GROUP
			$classes = ' cutemi-group-count-' . $max_rows;
			if ( ! empty( $group_field['label_class'] ) ) {
				$classes .= ' ' . $group_field['label_class'];
			}
			$pre_group_out .= cutemi_get_group_head( $group_field, $group_val, $classes );

			$post_group_out .= '</div>';
		}

		if ( ! $all_empty || 'name' === $group_field['id'] ) {
			/**
			 * All already escaped
			 */
			$out .= $pre_group_out . $group_out . $post_group_out;
		}
	}
	$out .= '</div>';

	return $out;
}


/**
 * Get code for table cell.
 *
 * @param array $group_field The group field (config, with keys "img" and "text" for display or not)
 * @param array $group_val The group values (with keys "img" and "text")
 * @param string $classes Block/group header classes.
 *
 * @return string
 */
function cutemi_get_group_head( $group_field, $group_val, $classes ) {
	$pre_group_out = '<div class="cutemi-group-head' . esc_attr( $classes ) . '">';
	if ( true === $group_field['img'] ) {
		$pre_group_out .= '<img ';
		if ( ! empty( $group_val['img'] ) ) {
			$group_val['img'] = cutemi_get_misc_icon_pack( $group_val['img'] );
			if (
				get_option( 'cutemi_svg_head_colorized', '1' ) === '2' &&
				preg_match( '/\.svg($|\?)/i', $group_val['img'] )
			) {
				#https://github.com/iconfu/svg-inject#how-does-svginject-prevent-unstyled-image-flash
				$pre_group_out .= 'onload="SVGInject?SVGInject(this):0;" class="cutemi-svg-2-inline" ';
			}
		}
		$pre_group_out .= 'src="' . esc_url( $group_val['img'] ) . '" title="' . esc_attr( $group_val['text'] ) . '">';
	}
	if ( true === $group_field['text'] ) {
		$pre_group_out .= '<span>' . esc_html( $group_val['text'] ) . '<s class="cutemi-only-texting">: </s></span>';
	} else {
		//For themes/plugins that use content for excerpt/summary
		$pre_group_out .= '<div class="cutemi-only-texting">' . esc_html( $group_val['text'] ) . ': </div>';
	}
	$pre_group_out .= '</div>';

	return $pre_group_out;
}

/**
 * Get code for table cell.
 *
 * @param array $mediainfo The video data as associative array.
 * @param bool|array $group The group/block associative array. (config of the group).
 * @param bool|int|WP_Post $post The post ID.
 * @param array $field The field associative array. (config of the field).
 * @param int $idx Optional. The row of group. If -1, general group field. Default -1.
 *
 * @return mixed|string|void
 */
function cutemi_get_table_cell( $mediainfo, $group, $post, $field, $idx = - 1 ) {

	$classes  = isset( $field['class'] ) ? $field['class'] : '';
	$classes .= ' cutemi-cell cutemi-cell-' . $field['id'];

	if ( ! empty( $group ) ) {
		if (
				! isset( $mediainfo[ $group['id'] ] ) ||
				! isset( $mediainfo[ $group['id'] ][ $idx ] ) ||
				! isset( $mediainfo[ $group['id'] ][ $idx ][ $field['id'] ] )
		) {
			return apply_filters( 'cutemi_table_cell_data', '', $mediainfo, $group, $post, $field, $idx );
		}
		$val = $mediainfo[ $group['id'] ][ $idx ][ $field['id'] ];
	} else {
		//Unique
		if (!isset($mediainfo[ $field['id'] ])){
			return apply_filters( 'cutemi_table_cell_data', '', $mediainfo, $group, $post, $field, $idx );
		}
		$val = $mediainfo[ $field['id'] ];
	}

	if ( empty( $val['text'] ) && ! empty( $field['default'] ) ) {
		$val['text'] = $field['default'];
	}
	if ( 'size' === $field['id'] || 'part_size' === $field['id'] ) {
		if ( is_numeric( $val['text'] ) ) {
			$val['text'] = cutemi_human_filesize( $val['text'] );
		} else {
			return apply_filters( 'cutemi_table_cell_data', '', $mediainfo, $group, $post, $field, $idx );
		}
	}
	if ( 'duration' === $field['id'] ) {
		if ( is_numeric( $val['text'] ) ) {
			$val['text'] = cutemi_human_duration( $val['text'] );
		}
	}
	if ( 'date' === $field['id'] ) {
		if ( is_numeric( $val['text'] ) ) {
			$val['text'] = date_i18n( get_option( 'date_format' ), strtotime( $val['text'] ) );
		}
	}
	if ( 'part' === $field['id'] && ! empty( $mediainfo['parts'] ) ) {
		$val['text'] = $val['text'] . '/' . $mediainfo['parts'];
	}

	if ( ! empty( $field['nl2br'] ) ) {
		$val['text'] = nl2br( $val['text'] );
	}

	$field = apply_filters( 'cutemi_out_cell_field', $field, $mediainfo, $group, $post, $val, $idx );
	$val   = apply_filters( 'cutemi_out_cell_val', $val, $mediainfo, $group, $post, $field, $idx );

	$alt_img      = '';
	$tooltip_text = '';
	if ( isset( $field['tooltip'] ) && in_array( $field['tooltip'], array( 'text', 'desc' ), true ) ) {
		$tooltip_text = $val[ $field['tooltip'] ];
	}

	if ( isset( $field['icon'] ) ) {
		$val['img'] = cutemi_get_misc_icon_pack( $field['icon'] );
	}

	$show_img  = ( isset( $field['img'] ) && true === $field['img'] && ! empty( $val['img'] ) );
	$show_text = ( true === $field['text'] || ( 2 === $field['text'] && ! $show_img ) );

	$text = $val['text'];
	if ( ! $show_text && ! empty( $text ) ) {
		$alt_img = wp_strip_all_tags( $text );
	}

	/*
	 * OK, render the cell
	 */

	$allowed_html = cutemi_get_allowed_html();

	$out = ' <li class="' . esc_attr( $classes ) . '">';

	/*
	 * Image
	 */
	if ( $show_img ) {
		$out .= '<img src="' . esc_url( $val['img'] ) . '"';
		if ( ! empty( $tooltip_text ) ) {
			$out .= ' title="' . esc_attr( $tooltip_text ) . '"';
		}
		if ( ! empty( $alt_img ) ) {
			$out .= ' alt="' . esc_attr( $alt_img ) . '"';
		}
		$out .= '>';
	}

	/*
	 * Text (if mot show, print ijto hidden div for some theme or plugin excerpt/summary extract from content)
	 */
	if ( $show_text ) {
		$out .= '<span';
		if ( ! empty( $tooltip_text ) ) {
			$out .= ' title="' . esc_attr( $tooltip_text ) . '"';
		}
		$out .= '>';
	} else {
		$out .= '<div class="cutemi-only-texting">';
	}

	if ( 'txt_top_label' === $field['show_as'] ) {
		$out .= '<sup>' . esc_html( ucfirst( $field['id'] ) ) . '</sup><b>';
	} elseif ( 'pre' === $field['show_as'] ) {
		$out .= '<pre>';
	}
	/*
	 * Link start with "_" are special and skipped for plugins mods
	 */
	if ( 'link' === $field['show_as'] && substr( $text, 0, 1 ) !== '_' ) {
		$out .= '<a class="cutemi-btn" href="' . esc_attr( $text ) . '" target="_blank">';
		if ( ! empty( $field['link_anchor_img'] ) ) {
			$out .= '<img src="' . esc_url( $field['link_anchor_img'] ) . '">';
		}
		if ( ! empty( $field['link_anchor_text'] ) ) {
			$out .= '<span>' . esc_html( $field['link_anchor_text'] ) . '</span>';
		}
		$out .= '</a>';
	} else {
		/**
		 * esc before, then plugins can add html.
		 */
		$out .= apply_filters( 'cutemi_table_cell_text', wp_kses( $text, $allowed_html ), $mediainfo, $group, $post, $field, $idx );
	}

	if ( 'txt_top_label' === $field['show_as'] ) {
		$out .= '</b>';
	} elseif ( 'pre' === $field['show_as'] ) {
		$out .= '</pre>';
	}

	$out .= ( $show_text ) ? '</span>' : '</div>';
	$out .= '</li>';

	/**
	 * Fix for skip render empty rows
	 */
	if ( ! $show_img && empty( $text ) ) {
		$out = '';
	}

	return apply_filters( 'cutemi_table_cell_data', $out, $mediainfo, $group, $post, $field, $idx );
}


/**
 * Get table template data scheme and assign variables from video.
 *
 * @param string $profile Profile slug.
 * @param bool|array $mediainfo Optional. Video data as associative array. Default false.
 * @param bool|int|WP_Post $post Optional. Post ID. Default false.
 *
 * @return CUTEMI_Table_Template
 */
function cutemi_get_table_template( $profile, $mediainfo = false, $post = false ) {
	$table_template = new CUTEMI_Table_Template( $profile );

	$replacements = array();
	if ( ! empty( $mediainfo ) && isset( $mediainfo['parts'], $mediainfo['size'], $mediainfo['name'] ) ) {
		$replacements = array(
			'%%parts%%' => $mediainfo['parts'],
			'%%size%%'  => $mediainfo['size']['text'],
			'%%name%%'  => $mediainfo['name'],
		);
	}

	$replacement_prop_group_config = array();
	$replacement_prop_group_val    = array( 'text' );
	$replacement_prop_cell         = array();
	foreach ( $table_template->layout as $row_idx => $group ) {
		if ( ! isset( $group['group-config'] ) ) {
			$group['group-config'] = array();
		}
		if ( ! isset( $group['group-val'] ) ) {
			$group['group-val'] = array();
		}
		if ( ! isset( $group['fields'] ) ) {
			$group['fields'] = array();
		}
		foreach ( $replacement_prop_group_config as $prop ) {
			if ( isset( $group['group-config'][ $prop ] ) ) {
				$table_template->layout[ $row_idx ]['group-config'][ $prop ] = cutemi_prop_data_replacements(
					$group['group-config'][ $prop ],
					$replacements,
					$post,
					$mediainfo
				);
			}
		}
		foreach ( $replacement_prop_group_val as $prop ) {
			if ( isset( $group['group-val'][ $prop ] ) ) {
				$table_template->layout[ $row_idx ]['group-val'][ $prop ] = cutemi_prop_data_replacements(
					$group['group-val'][ $prop ],
					$replacements,
					$post,
					$mediainfo
				);
			}
		}
		foreach ( $group['fields'] as $k => $field ) {
			foreach ( $replacement_prop_cell as $prop ) {
				if ( isset( $field[ $prop ] ) ) {
					$table_template->layout[ $row_idx ]['fields'][ $k ][ $prop ] = cutemi_prop_data_replacements(
						$field[ $prop ],
						$replacements,
						$post,
						$mediainfo
					);
				}
			}
		}
	}

	return $table_template;
}

/**
 * Property data replacement
 *
 * @param string $string String to replace
 * @param array $replacements Replacements as associative array
 * @param bool|int|WP_Post $post Post
 * @param array $mediainfo Video data
 *
 * @return string
 */
function cutemi_prop_data_replacements( $string, $replacements, $post, $mediainfo ) {
	$replacements = apply_filters( 'cutemi_prop_data_replacements', $replacements, $post, $mediainfo );
	if ( is_array( $replacements ) && array() !== $replacements ) {
		$string = str_replace(
			array_keys( $replacements ),
			// Make sure to exclude replacement values that are arrays e.g. coming from a custom field serialized value.
			array_filter( array_values( $replacements ), 'is_scalar' ),
			$string
		);
	}

	return $string;
}
