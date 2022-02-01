<?php

function cutemi_term_is_enable( $term_id ) {
	return ( '1' !== get_term_meta( $term_id, 'cutemi_disabled', true ) );
}

function cutemi_term_exists_and_enabled( $term_slug, $tax ) {
	$term = term_exists( $term_slug, $tax );

	if ( 0 !== $term && null !== $term && isset( $term['term_id'] ) ) {
		return cutemi_term_is_enable( $term['term_id'] );
	}

	return false;
}

function cutemi_get_misc_icon_pack( $icon ) {
	if (
		'/' === substr( $icon, 0, 1 ) &&
		'https:' === substr( $icon, 0, 6 ) &&
		'http:' === substr( $icon, 0, 5 )
	) {
		return $icon;
	}

	$selected = get_option( 'cutemi_icon_pack', 'default' );

	$image_url = apply_filters( 'cutemi_misc_icon_' . $selected, false, $icon );

	if ( false === $image_url && 'default' !== $selected ) {
		$image_url = apply_filters( 'cutemi_misc_icon_default', false, $icon );
	}

	return ( false === $image_url ) ? '' : $image_url;
}

function cutemi_get_term_icon_pack( $taxonomy, $term_slug ) {
	if ( is_int( $term_slug ) ) {
		$term = get_term( $term_slug, $taxonomy );
		if ( is_object( $term ) && property_exists( $term, 'slug' ) ) {
			$term_slug = $term->slug;
		} else {
			return '';
		}
	} else {
		$term = get_term_by( 'slug', $term_slug, $taxonomy );
	}
	if ( is_object( $term ) ) {
		$image_url = get_term_meta( $term->term_id, 'image_url', true );
		if ( ! empty( $image_url ) ) {
			return $image_url;
		}
	}

	$icon_pack = get_option( 'cutemi_icon_pack', 'default' );

	$image_url = apply_filters( 'cutemi_term_icon_' . $icon_pack, false, $taxonomy, $term_slug );

	if ( false === $image_url && 'default' !== $icon_pack ) {
		$image_url = apply_filters( 'cutemi_term_icon_default', false, $taxonomy, $term_slug );
	}

	return ( false === $image_url ) ? '' : $image_url;
}

/**
 * Get mediainfo data.
 *
 * This return an associative array with the data of the mediainfo.
 * You can specify the groups and fields to reduce data.
 *
 * @param WP_Post       $post       WP_Post Object.
 * @param bool|array    $groups     Optional. Array of groups to complete data.
 * @param bool|array    $fields     Optional. Array of fields to complete data.
 *
 * @return array    Mediainfo data.
 */
function cutemi_get_mediainfo_data( $post, $groups = false, $fields = false ) {

	$general = array(
		'type'     => 'unique',
		'group-id' => 'general',
		'fields'   => array(
			array(
				'id'       => 'cutemi_file_format',
				'type'     => 'tax',
				'meta_img' => 'image_url',
				'key'      => 'format',
			),
			array(
				'id'   => 'size',
				'type' => 'meta',
				'key'  => 'size',
			),
			array(
				'id'   => 'duration',
				'type' => 'meta',
				'key'  => 'duration',
			),
			array(
				'id'   => 'video_date',
				'type' => 'meta',
				'key'  => 'date',
			),
			array(
				'id'   => 'desc',
				'type' => 'meta',
				'key'  => 'desc',
			),
			array(
				'id'   => 'mediainfo',
				'type' => 'meta',
				'key'  => 'mediainfo',
			),
		),
	);
	$videos  = array(
		'type'     => 'arr',
		'group-id' => 'videos',
		'fields'   => array(
			array(
				'id'               => 'cutemi_video_resolution',
				'type'             => 'tax',
				'child_expand_img' => true,
				'meta_img'         => 'image_url',
				'key'              => 'resolution',
			),
			array(
				'id'               => 'cutemi_video_tech',
				'type'             => 'tax',
				'child_expand_img' => true,
				'meta_img'         => 'image_url',
				'key'              => 'tech',
			),
			array(
				'id'       => 'cutemi_video_bitrate',
				'type'     => 'tax',
				'meta_img' => 'image_url',
				'key'      => 'bitrate',
			),
			array(
				'id'       => 'cutemi_video_bitrate_mode',
				'type'     => 'tax',
				'meta_img' => 'image_url',
				'key'      => 'bitrate_mode',
			),
		),
	);
	$audios  = array(
		'type'     => 'arr',
		'group-id' => 'audios',
		'fields'   => array(
			array(
				'id'               => 'cutemi_audio_langs',
				'type'             => 'tax',
				'child_expand_img' => true,
				'meta_img'         => 'image_url',
				'key'              => 'lang',
			),
			array(
				'id'               => 'cutemi_audio_tech',
				'type'             => 'tax',
				'child_expand_img' => true,
				'meta_img'         => 'image_url',
				'key'              => 'tech',
			),
			array(
				'id'               => 'cutemi_audio_channels',
				'type'             => 'tax',
				'child_expand_img' => true,
				'meta_img'         => 'image_url',
				'key'              => 'channels',
			),
			array(
				'id'       => 'cutemi_audio_bitrate',
				'type'     => 'tax',
				'meta_img' => 'image_url',
				'key'      => 'bitrate',
			),
			array(
				'id'       => 'cutemi_audio_bitrate_mode',
				'type'     => 'tax',
				'meta_img' => 'image_url',
				'key'      => 'bitrate_mode',
			),
		),
	);
	$texts   = array(
		'type'     => 'arr',
		'group-id' => 'texts',
		'fields'   => array(
			array(
				'id'               => 'cutemi_text_langs',
				'child_expand_img' => true,
				'type'             => 'tax',
				'meta_img'         => 'image_url',
				'key'              => 'lang',
			),
			array(
				'id'       => 'cutemi_text_format',
				'type'     => 'tax',
				'meta_img' => 'image_url',
				'key'      => 'format',
			),
			array(
				'id'       => 'cutemi_text_type',
				'type'     => 'tax',
				'meta_img' => 'image_url',
				'key'      => 'type',
			),
		),
	);
	$links   = array(
		'type'     => 'arr',
		'group-id' => 'links',
		'fields'   => array(
			array(
				'id'       => 'cutemi_site_source',
				'type'     => 'tax',
				'meta_img' => 'image_url',
				'key'      => 'source',
			),
			array(
				'id'      => 'part_nro',
				'type'    => 'meta',
				'default' => '1',
				'key'     => 'part',
			),
			array(
				'id'   => 'part_size',
				'type' => 'meta',
				'key'  => 'size',
			),
			array(
				'id'   => 'external_id',
				'type' => 'meta',
				'key'  => 'external_id',
			),
		),
	);
	$table   = array(
		$general,
		$videos,
		$audios,
		$texts,
		$links,
	);

	$table = apply_filters( 'cutemi_video_structural_data', $table, $post, $groups, $fields );

	$res = array();

	if ( ! empty( $fields ) ) {
		if ( ! is_array( $fields ) ) {
			$fields = array( $fields );
		}
	}

	if ( ! empty( $groups ) ) {
		if ( ! is_array( $groups ) ) {
			$groups = array( $groups );
		}
		$table_tmp = array();
		foreach ( $table as $table_group ) {
			if ( in_array( $table_group['group-id'], $groups, true ) ) {
				$table_tmp[] = $table_group;
			}
		}
		$table = $table_tmp;
	}

	$part_nros = get_post_meta( $post->ID, 'part_nro', true );
	$part_max  = 1;
	if ( ! empty( $part_nros ) && is_array( $part_nros ) ) {
		foreach ( $part_nros as $key => $nro ) {
			if ( (int) $nro > $part_max ) {
				$part_max = (int) $nro;
			}
		}
	}
	$res['parts'] = $part_max;
	$res['name']  = get_the_title( $post );

	foreach ( $table as $row_idx => $row ) {
		if ( 'arr' === $row['type'] ) {
			$res[ $row['group-id'] ] = array();

			$max = 0;
			//Count max of rows for the fields
			foreach ( $row['fields'] as $field ) {
				if ( ! empty( $fields ) && ! in_array( $field['id'], $fields, true ) ) {
					continue;
				}
				if ( 'tax' === $field['type'] ) {
					$arr = get_post_meta( $post->ID, 'order:' . $field['id'], true );
				} else {
					$arr = get_post_meta( $post->ID, $field['id'], true );
				}
				if ( is_array( $arr ) && count( $arr ) > $max ) {
					$max = count( $arr );
				}
			}
			$res[ $row['group-id'] . '-count' ] = $max;

			for ( $idx = 0; $idx < $max; $idx ++ ) {
				$count_col_row = 0;
				$sub           = array();
				foreach ( $row['fields'] as $field ) {
					if ( ! empty( $fields ) && ! in_array( $field['id'], $fields, true ) ) {
						continue;
					}
					$count_col_row ++;

					$val = cutemi_get_field( $post, $field, $idx );
					if ( empty( $val['text'] ) && ! empty( $field['default'] ) ) {
						$val['text'] = $field['default'];
					}

					$val = apply_filters( 'cutemi_field_val', $val, $row['group-id'], $field, $idx, $post );

					if ( 'external_id' === $field['id'] ) { //Link
						$val = cutemi_set_link_from_external_id( $val, $post, $idx );
						if ( null === $val ) { //hidden source, source was delete or not set
							$res[ $row['group-id'] . '-count' ] -= 1;
							continue 2; //skip add this link
						}
					}
					$sub[ $field['key'] ] = $val;
				}
				$res[ $row['group-id'] ][] = $sub;
			}
		} else {
			foreach ( $row['fields'] as $field ) {
				if ( ! empty( $fields ) && ! in_array( $field['id'], $fields, true ) ) {
					continue;
				}
				if ( 'general' === $row['group-id'] ) {
					$res[ $field['key'] ] = cutemi_get_field( $post, $field );

					$res[ $field['key'] ] = apply_filters( 'cutemi_field_val', $res[ $field['key'] ], $row['group-id'], $field, false, $post );
				} else {
					$res[ $row['group-id'] ][ $field['key'] ] = cutemi_get_field( $post, $field );

					$res[ $row['group-id'] ][ $field['key'] ] = apply_filters(
						'cutemi_field_val',
						$res[ $row['group-id'] ][ $field['key'] ],
						$row['group-id'],
						$field,
						false,
						$post
					);
				}
			}
		}
	}

	return apply_filters( 'cutemi_video_data', $res, $post, $groups, $fields );
}

function cutemi_set_link_from_external_id( $default_val, $post, $idx ) {

	$val = $default_val;

	$val_source = cutemi_get_field(
		$post,
		array(
			'type' => 'tax',
			'id'   => 'cutemi_site_source',
		),
		$idx
	);
	$val_title  = cutemi_get_field(
		$post,
		array(
			'type' => 'meta',
			'id'   => 'link_title',
		),
		$idx
	);

	if ( empty( $val_source['slug'] ) ) {
		return null;
	} else {
		$term = get_term_by( 'slug', $val_source['slug'], 'cutemi_site_source' );

		if ( false === $term ) {
			return null;
		}

		if ( get_term_meta( $term->term_id, 'hidden', true ) === '1' ) {
			return null;
		}
		if ( 'cutemi-link-source-generic' === $val_source['slug'] ) {
			$val_url     = cutemi_get_field(
				$post,
				array(
					'type' => 'meta',
					'id'   => 'original_link',
				),
				$idx
			);
			$val['text'] = $val_url['text'];
		} else {

			if ( 'on' === get_option( 'cutemi_hide_offline', 'on' ) ) {
				$status = cutemi_get_field(
					$post,
					array(
						'type' => 'meta',
						'id'   => 'link_status',
					),
					$idx
				);
				if ( isset( $status['text'] ) && '1' === $status['text'] ) {
					return null;
				}
			}

			$url_template = get_term_meta( $term->term_id, 'url_template', true );
			if ( ! empty( $url_template ) ) {
				$link_search  = array( '%ID%', '%TITLE%' );
				$link_replace = array( $val['text'], $val_title['text'] );
				$val['text']  = str_replace( $link_search, $link_replace, $url_template );
			}
		}
	}

	return apply_filters( 'cutemi_set_link_from_external_id', $val, $post, $idx );
}

function cutemi_get_field( $post, $field, $idx = - 1 ) {
	$value = array(
		'img'  => '',
		'text' => '',
		'desc' => '',
	);
	if ( 'meta' === $field['type'] ) {
		$meta_value = get_post_meta( $post->ID, $field['id'], true );
		if ( $idx > - 1 ) {
			if ( is_array( $meta_value ) ) {
				$meta_value = isset( $meta_value[ $idx ] ) ? $meta_value[ $idx ] : '';
			} elseif ( '0' !== $idx ) {
				$meta_value = '';
			}
		} else {
			if ( is_array( $meta_value ) ) {
				$meta_value = isset( $meta_value[0] ) ? $meta_value[0] : '';
			}
		}
		$value['text'] = $meta_value;
	} elseif ( 'tax' === $field['type'] ) {
		$selected = - 1;
		$taxonomy = $field['id'];

		if ( - 1 < $idx ) {
			$order_meta = get_post_meta( $post->ID, 'order:' . $taxonomy, true );
			if ( is_array( $order_meta ) ) {
				if ( isset( $order_meta[ $idx ] ) ) {
					$selected_slug = $order_meta[ $idx ];
					if ( ! empty( $selected_slug ) ) {
						$selected = get_term_by( 'slug', $selected_slug, $taxonomy );
					} else {
						$selected = '';
					}
				} else {
					$selected = null;
				}
			}
		}

		if ( - 1 === $selected ) {
			$selected = wp_get_post_terms( $post->ID, $taxonomy );
			if ( is_wp_error( $selected ) || empty( $selected ) ) {
				$selected = null;
			} else {
				if ( - 1 < $idx ) {
					$selected = $selected[ $idx ];
				} elseif ( is_array( $selected ) ) {
					$selected = array_shift( $selected );
				}
			}
		}

		if ( empty( $selected ) ) {
			$selected = '';
		} else {
			if ( isset( $field['meta_img'] ) ) {
				if ( 'image_url' === $field['meta_img'] ) {
					$img = cutemi_get_term_icon_pack( $taxonomy, $selected->slug );
				} else {
					$img = get_term_meta( $selected->term_id, $field['meta_img'], true );
				}
				if ( empty( $img ) ) {
					if ( isset( $field['child_expand_img'] ) && $field['child_expand_img'] && 0 !== $selected->parent ) {
						$term_parent = get_term( $selected->parent, $taxonomy );
						if ( 'image_url' === $field['meta_img'] ) {
							$img = cutemi_get_term_icon_pack( $taxonomy, $term_parent->slug );
						} else {
							$img = get_term_meta( $term_parent->term_id, $field['meta_img'], true );
						}
					}
				}
				if ( ! empty( $img ) ) {
					$value['img'] = $img;
				}
			}
			$value['text'] = $selected->name;
			$value['desc'] = $selected->description;
			$value['slug'] = $selected->slug;
		}
	}

	return $value;
}


function cutemi_get_allowed_html() {
	static $default_attribs = array(
		'id'             => array(),
		'class'          => array(),
		'title'          => array(),
		'style'          => array(),
		'data'           => array(),
		'data-mce-id'    => array(),
		'data-mce-style' => array(),
		'data-mce-bogus' => array(),
	);

	return array(
		'div'        => $default_attribs,
		'span'       => $default_attribs,
		'p'          => $default_attribs,
		'a'          => array_merge(
			$default_attribs,
			array(
				'href'   => array(),
				'target' => array( '_blank', '_top' ),
			)
		),
		'img'        => array_merge(
			$default_attribs,
			array(
				'src' => array(),
				'alt' => array(),
			)
		),
		'u'          => $default_attribs,
		'i'          => $default_attribs,
		'q'          => $default_attribs,
		'b'          => $default_attribs,
		'ul'         => $default_attribs,
		'ol'         => $default_attribs,
		'li'         => $default_attribs,
		'br'         => $default_attribs,
		'hr'         => $default_attribs,
		'strong'     => $default_attribs,
		'blockquote' => $default_attribs,
		'del'        => $default_attribs,
		'strike'     => $default_attribs,
		'em'         => $default_attribs,
		'code'       => $default_attribs,
		'small'      => $default_attribs,
	);
}


function cutemi_check_color( $color ) {
	// If there's a semicolon in it,
	if ( false !== strpos( $color, ';' ) ) {
		$color_chunks = explode( ';', $color, 2 );
		$color        = $color_chunks[0];
	}

	// Trim it.
	$color = trim( $color );

	$named_colors = apply_filters(
		'css_named_colors',
		array(
			'aliceblue',
			'antiquewhite',
			'aqua',
			'aquamarine',
			'azure',
			'beige',
			'bisque',
			'black',
			'blanchedalmond',
			'blue',
			'blueviolet',
			'brown',
			'burlywood',
			'cadetblue',
			'chartreuse',
			'chocolate',
			'coral',
			'cornflowerblue',
			'cornsilk',
			'crimson',
			'cyan',
			'darkblue',
			'darkcyan',
			'darkgoldenrod',
			'darkgray',
			'darkgreen',
			'darkgrey',
			'darkkhaki',
			'darkmagenta',
			'darkolivegreen',
			'darkorange',
			'darkorchid',
			'darkred',
			'darksalmon',
			'darkseagreen',
			'darkslateblue',
			'darkslategray',
			'darkslategrey',
			'darkturquoise',
			'darkviolet',
			'deeppink',
			'deepskyblue',
			'dimgray',
			'dimgrey',
			'dodgerblue',
			'firebrick',
			'floralwhite',
			'forestgreen',
			'fuchsia',
			'gainsboro',
			'ghostwhite',
			'gold',
			'goldenrod',
			'gray',
			'green',
			'greenyellow',
			'grey',
			'honeydew',
			'hotpink',
			'indianred',
			'indigo',
			'ivory',
			'khaki',
			'lavender',
			'lavenderblush',
			'lawngreen',
			'lemonchiffon',
			'lightblue',
			'lightcoral',
			'lightcyan',
			'lightgoldenrodyellow',
			'lightgray',
			'lightgreen',
			'lightgrey',
			'lightpink',
			'lightsalmon',
			'lightseagreen',
			'lightskyblue',
			'lightslategray',
			'lightslategrey',
			'lightsteelblue',
			'lightyellow',
			'lime',
			'limegreen',
			'linen',
			'magenta',
			'maroon',
			'mediumaquamarine',
			'mediumblue',
			'mediumorchid',
			'mediumpurple',
			'mediumseagreen',
			'mediumslateblue',
			'mediumspringgreen',
			'mediumturquoise',
			'mediumvioletred',
			'midnightblue',
			'mintcream',
			'mistyrose',
			'moccasin',
			'navajowhite',
			'navy',
			'oldlace',
			'olive',
			'olivedrab',
			'orange',
			'orangered',
			'orchid',
			'palegoldenrod',
			'palegreen',
			'paleturquoise',
			'palevioletred',
			'papayawhip',
			'peachpuff',
			'peru',
			'pink',
			'plum',
			'powderblue',
			'purple',
			'rebeccapurple',
			'red',
			'rosybrown',
			'royalblue',
			'saddlebrown',
			'salmon',
			'sandybrown',
			'seagreen',
			'seashell',
			'sienna',
			'silver',
			'skyblue',
			'slateblue',
			'slategray',
			'slategrey',
			'snow',
			'springgreen',
			'steelblue',
			'tan',
			'teal',
			'thistle',
			'tomato',
			'turquoise',
			'violet',
			'wheat',
			'white',
			'whitesmoke',
			'yellow',
			'yellowgreen',
		)
	);
	if ( in_array( strtolower( $color ), $named_colors, true ) ) {
		return $color;
	}

	// hex color
	if ( preg_match( '/^#([a-f\d]{3}){1,2}$/i', $color ) ) {
		return $color;
	}

	// rgb
	if ( preg_match( '/rgb\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*\)/i', $color, $matches ) ) {
		$matches['r'] = min( 255, max( 0, (int) $matches[1] ) );
		$matches['g'] = min( 255, max( 0, (int) $matches[2] ) );
		$matches['b'] = min( 255, max( 0, (int) $matches[3] ) );

		return "rgb( {$matches['r']}, {$matches['g']}, {$matches['b']} )";
	}

	// rgba
	if ( preg_match( '/rgba\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*([\d.]+)\s*\)/i', $color, $matches ) ) {
		$matches['r'] = min( 255, max( 0, (int) $matches[1] ) );
		$matches['g'] = min( 255, max( 0, (int) $matches[2] ) );
		$matches['b'] = min( 255, max( 0, (int) $matches[3] ) );
		$matches['a'] = min( 1, max( 0, (float) $matches[4] ) );

		return "rgba( {$matches['r']}, {$matches['g']}, {$matches['b']}, {$matches['a']} )";
	}

	// hsl
	if ( preg_match( '/hsl\(\s*([\d.]+)\s*,\s*([\d.]+)%\s*,\s*([\d.]+)%\s*\)/i', $color, $matches ) ) {
		$matches['h'] = min( 360, max( 0, (float) $matches[1] ) );
		$matches['s'] = min( 100, max( 0, (float) $matches[2] ) );
		$matches['l'] = min( 100, max( 0, (float) $matches[3] ) );

		return "hsl( {$matches['h']}, {$matches['s']}%, {$matches['l']}% )";
	}

	// hsla
	if ( preg_match( '/hsla?\(\s*([\d.]+)\s*,\s*([\d.]+)%\s*,\s*([\d.]+)%\s*,\s*([\d.]+)\s*\)/i', $color, $matches ) ) {
		$matches['h'] = min( 360, max( 0, (float) $matches[1] ) );
		$matches['s'] = min( 100, max( 0, (float) $matches[2] ) );
		$matches['l'] = min( 100, max( 0, (float) $matches[3] ) );
		$matches['a'] = min( 1, max( 0, (float) $matches[4] ) );

		return "hsla( {$matches['h']}, {$matches['s']}%, {$matches['l']}%, {$matches['a']} )";
	}

	return null;
}
