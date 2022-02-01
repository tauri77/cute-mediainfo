<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

require_once CUTE_MEDIAINFO_DIR . '/includes/links/link-sources/boot.php';

function cutemi_extract_cute_mediainfo_data( $data ) {

	$link        = $data['original_link'];
	$valid_terms = array();

	$part = 1;
	if ( preg_match( '`\.(?:part|z|chunk|00)0*(\d+)(\.rar|)(/file|/)?$`i', $link, $part_matches ) ) {
		$part = $part_matches[1];
	}

	$data_extract = ( get_option( 'cutemi_link_data_extractor', 'on' ) === 'on' );

	$user_can_request = current_user_can( 'edit_posts' ); //only this user can send http request out

	// One for one check sources match with the link
	$terms = get_terms(
		array(
			'taxonomy'   => 'cutemi_site_source',
			'hide_empty' => false,
		)
	);
	foreach ( $terms as $term ) {
		if ( ! cutemi_term_is_enable( $term->term_id ) ) {
			continue;
		}
		$extract_regexs = get_term_meta( $term->term_id, 'extract_regexs', true );
		if ( ! empty( $extract_regexs ) ) {
			$regexs = explode( "\n", $extract_regexs );
			//check all url regex
			foreach ( $regexs as $key => $regex ) {
				$regex = trim( $regex );
				if ( empty( $regex ) ) {
					continue;
				}
				if ( preg_match( '`' . $regex . '`i', $link, $matches ) ) {

					$valid = array(
						'title'    => '',
						'part_nro' => $part,
					);

					//first group is the id
					if ( isset( $matches['id'] ) ) {
						$valid['external_id'] = $matches['id'];
					} else {
						$valid['external_id'] = $matches[1];
					}
					//second group is the title (if exist)
					if ( isset( $matches['title'] ) ) {
						$valid['title'] = $matches['title'];
					} elseif ( isset( $matches[2] ) ) {
						$valid['title'] = $matches[2];
					}

					//set the source
					$valid['sitesource'] = $term->slug;
					//set source img
					$valid['sitesourceimg'] = '';
					$img                    = get_term_meta( $term->term_id, 'image_url', true );
					if ( ! empty( $img ) ) {
						$valid['sitesourceimg'] = esc_attr( $img );
					}

					//set the urls
					$url_template       = get_term_meta( $term->term_id, 'url_template', true );
					$url_template_title = get_term_meta( $term->term_id, 'url_template_title', true );
					$url_template_size  = get_term_meta( $term->term_id, 'url_template_size', true );

					$template_search  = array( '%ID%', '%TITLE%', '%TIME%' );
					$template_replace = array( $valid['external_id'], $valid['title'], time() );

					$valid['urls'] = array();
					if ( ! empty( $url_template ) ) {

						$urls['link'] = str_replace( $template_search, $template_replace, $url_template );

						//if not set url for info, set the default url
						$urls['title'] = empty( $url_template_title ) ? $urls['link'] :
							str_replace( $template_search, $template_replace, $url_template_title );
						$urls['size']  = empty( $url_template_size ) ? $urls['link'] :
							str_replace( $template_search, $template_replace, $url_template_size );

						$valid['urls'] = $urls;
					}

					if ( $user_can_request && $data_extract ) {
						$GLOBALS['cutemi_proxy_list'] = array();
						$proxy_list                   = get_term_meta( $term->term_id, 'proxy_list', true );
						$proxy_list                   = trim( $proxy_list );
						if ( ! empty( $proxy_list ) ) {
							$proxy_list                   = explode( "\n", $proxy_list );
							$GLOBALS['cutemi_proxy_list'] = array_map( 'trim', $proxy_list );
						}
					}

					if ( $user_can_request && $data_extract && ! empty( $valid['urls']['title'] ) ) {
						// Search for title
						if ( empty( $valid['title'] ) ) {
							$title_regex = get_term_meta( $term->term_id, 'regex_title', true );
							$title_regex = trim( $title_regex );
							if ( ! empty( $title_regex ) ) {
								$title_regex                              = str_replace( $template_search, $template_replace, $title_regex );
								$html                                     = cutemi_http_fetch( $valid['urls']['title'] );
								$valid['html'][ $valid['urls']['title'] ] = $html;

								//if json remove unescaped unicode
								if (
									in_array( substr( $html, 0, 1 ), array( '{', '[' ), true ) &&
									strpos( $html, '\u' ) > 0
								) {
									$json = json_decode( $html, true );
									if ( is_array( $json ) ) {
										$html = wp_json_encode( $json, JSON_UNESCAPED_UNICODE );
									}
								}
								if ( preg_match( '`' . $title_regex . '`i', $html, $matches ) ) {
									if ( isset( $matches['title'] ) ) {
										$valid['title'] = html_entity_decode( $matches['title'] );
									} else {
										$valid['title'] = html_entity_decode( $matches[1] );
									}
									$template_replace[1] = $valid['title'];
								}
							}
						}
					}

					if ( $user_can_request && $data_extract && ! empty( $valid['urls']['size'] ) ) {
						// Search for size
						$size_regex = get_term_meta( $term->term_id, 'regex_size', true );
						$size_regex = trim( $size_regex );
						if ( ! empty( $size_regex ) ) {
							if ( isset( $valid['html'][ $valid['urls']['size'] ] ) ) {
								$html = $valid['html'][ $valid['urls']['size'] ];
							} else {
								$html                                    = cutemi_http_fetch( $valid['urls']['size'] );
								$valid['html'][ $valid['urls']['size'] ] = $html;
							}
							$size_regex = str_replace( $template_search, $template_replace, $size_regex );
							if ( preg_match( '`' . $size_regex . '`i', $html, $matches ) ) {
								$size_str = isset( $matches['size'] ) ? $matches['size'] : $matches[1];
								if ( strtoupper( substr( $size_str, strlen( $size_str ) - 1, 1 ) ) !== 'B' ) {
									$size_str = $size_str . 'B';
								}
								$valid['size_str'] = $size_str;
								$valid['size']     = cutemi_to_byte_size( $size_str );
							}
						}
					}

					$valid = apply_filters( 'cutemi_link_data_extract_post', $valid, $link );

					$valid['taxonomies'] = array();
					//extract data from title tags
					if ( $data_extract && ! empty( $valid['title'] ) ) {
						if ( preg_match( '`\.(?:part|z|chunk|00)0*(\d+)(\.rar|)(/file|/)?$`i', $valid['title'], $part_matches ) ) {
							$valid['part_nro'] = $part_matches[1];
						}
						$taxs_search = array(
							'cutemi_video_resolution',
							'cutemi_file_format',
							'cutemi_audio_channels',
							'cutemi_audio_tech',
							'cutemi_audio_langs',
						);
						$taxs_search = apply_filters( 'cutemi_link_title_data_extractor_taxs', $taxs_search );
						foreach ( $taxs_search as $tax ) {
							//Search for term
							$valid['taxonomies'][ $tax ] = array(
								'found'         => array(),
								'candidate_max' => array(),
							);

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
								foreach ( $tags_for_regex as $regex_tag ) {
									$regex_tag = trim( $regex_tag );
									if ( ! empty( $regex_tag ) ) {
										//Test tag over valid['title']
										$pre_reg = '(?:[\.\s\-\[\]_,]|^)';
										if ( substr( $regex_tag, 0, 6 ) === '(?i)\.' ) {
											$pre_reg = '.';
										}
										$regex = "`({$pre_reg})(" . $regex_tag . ')([\.\s\-\[\]_,]|$)`';
										if ( preg_match( $regex, $valid['title'], $matches ) ) {
											//Test OK!;
											if ( isset( $matches[3] ) ) {
												$tag_valid          = array();
												$tag_valid['for']   = $term->slug;
												$tag_valid['pre']   = $matches[1];//before tag
												$tag_valid['match'] = $matches[2];//tag found
												$tag_valid['post']  = $matches[3];//after tag

												//calc match valority
												$tag_valid['valority'] = strlen( $matches[2] );
												if ( $tag_valid['post'] === $tag_valid['pre'] ) {
													$tag_valid['valority'] ++;
												}

												//set if is the better valority for taxonomy
												if (
													empty( $valid['taxonomies'][ $tax ]['candidate_max'] ) ||
													$tag_valid['valority'] > $valid['taxonomies'][ $tax ]['candidate_max']['valority']
												) {
													$valid['taxonomies'][ $tax ]['candidate_max'] = $tag_valid;
												}

												//add to taxonomy found
												$valid['taxonomies'][ $tax ]['found'][] = $tag_valid;
											}
										}
									}
								}
							}
						}
					}

					$valid_terms[] = $valid;
					break; //match one url regex, ready with this source.. next
				}
			}
		}
	}

	return array(
		'candidates' => $valid_terms,
	);
}


function cutemi_http_fetch( $url, $content = false, $headers = array( 'Accept-Language: en-gb, en;q=0.8' ), $method = 'GET' ) {

	include_once CUTE_MEDIAINFO_DIR . '/admin/includes/scrap/class-cutemi-browser.php';

	$browser = new CUTEMI_Browser();
	$parse   = wp_parse_url( $url );
	$browser->init( $parse['host'] );

	foreach ( $headers as $header ) {
		$parts = explode( ':', $header );
		$browser->set_header( $parts[0], ltrim( $parts[1] ) );
	}

	if ( is_string( $content ) ) {
		$tmp = $content;
		parse_str( $tmp, $content );
	}

	if ( 'POST' === $method ) {
		$browser->post( $url, $content );
	} else {
		$browser->get( $url, $content );
	}

	return $browser->response;
}
