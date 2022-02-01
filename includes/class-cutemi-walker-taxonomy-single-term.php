<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

if ( ! class_exists( 'CUTEMI_Walker_Taxonomy_Single_Term' ) && class_exists( 'Walker' ) ) :

	/**
	 * Walker to output an unordered list of taxonomy elements.
	 *
	 * @see Walker
	 * @see wp_category_checklist()
	 * @see wp_terms_checklist()
	 */
	class CUTEMI_Walker_Taxonomy_Single_Term extends Walker {
		public $tree_type         = 'category';
		public $db_fields         = array(
			'parent' => 'parent',
			'id'     => 'term_id',
		);
		public $config            = array(
			'text'         => '',
			'meta_img'     => '',
			'meta_sub'     => '',
			'option_class' => '',
		);
		public $last_term_depth_0 = null;
		public $pad_select        = false;

		public $hierarchical;
		public $input_element;

		public function __construct( $config ) {
			$this->config        = $config;
			$this->hierarchical  = $config['hierarchical'];
			$this->input_element = $config['input_element'];
		}

		/**
		 * Starts the list before the elements are added.
		 *
		 * @param string $output Passed by reference. Used to append additional content.
		 * @param int $depth Depth of category. Used for tab indentation.
		 * @param array $args An array of arguments. @see wp_terms_checklist()
		 *
		 * @see Walker:start_lvl()
		 *
		 */
		public function start_lvl( &$output, $depth = 0, $args = array() ) {
			if ( 'radio' === $this->input_element ) {
				$indent  = str_repeat( "\t", $depth );
				$output .= "$indent<ul class='children'>\n";
			} else {
				$output .= "</option>\n";
			}
		}

		/**
		 * Ends the list of after the elements are added.
		 *
		 * @param string $output Passed by reference. Used to append additional content.
		 * @param int $depth Depth of category. Used for tab indentation.
		 * @param array $args An array of arguments. @see wp_terms_checklist()
		 *
		 * @see Walker::end_lvl()
		 *
		 */
		public function end_lvl( &$output, $depth = 0, $args = array() ) {
			if ( 'radio' === $this->input_element ) {
				$indent  = str_repeat( "\t", $depth );
				$output .= "$indent</ul>\n";
			}
		}

		/**
		 * Start the element output.
		 *
		 * @param string $output Passed by reference. Used to append additional content.
		 * @param object $term The current term object.
		 * @param int $depth Depth of the term in reference to parents. Default 0.
		 * @param array $args An array of arguments. @see wp_terms_checklist()
		 * @param int $id ID of the current term.
		 *
		 * @see Walker::start_el()
		 *
		 */
		public function start_el( &$output, $term, $depth = 0, $args = array(), $id = 0 ) {

			$taxonomy = empty( $args['taxonomy'] ) ? 'category' : $args['taxonomy'];
			$name     = 'category' === $taxonomy ? 'post_category' : 'tax_input[' . $taxonomy . ']';
			// input name
			$name = $this->hierarchical ? $name . '[]' : $name;
			if ( ! is_object( $term ) ) {
				return;
			}

			$value = $term->slug;

			$selected_cats = empty( $args['selected_cats'] ) ? array() : $args['selected_cats'];
			$in_selected   = in_array( $term->term_id, $selected_cats, true );

			$data_sub   = '';
			$data_style = '';
			if ( ! empty( $this->config['meta_img'] ) ) {
				if ( 'image_url' === $this->config['meta_img'] ) {
					$img = cutemi_get_term_icon_pack( $taxonomy, $value );
				} else {
					$img = get_term_meta( $term->term_id, $this->config['meta_img'], true );
				}
				if (
					$term->parent > 0 &&
					empty( $img ) &&
					isset( $this->config['child_expand_img'] ) &&
					true === $this->config['child_expand_img']
				) {
					if ( $this->last_term_depth_0->term_id === $term->parent ) {
						$term_parent = $this->last_term_depth_0;
					} else {
						$term_parent = get_term( $term->parent, $taxonomy );
					}
					if ( 'image_url' === $this->config['meta_img'] ) {
						$img = cutemi_get_term_icon_pack( $taxonomy, $term_parent->slug );
					} else {
						$img = get_term_meta( $term_parent->term_id, $this->config['meta_img'], true );
					}
				}
				if ( ! empty( $img ) ) {
					$data_style = 'background-image: url("' . esc_attr( $img ) . '");';
				}
			}
			if ( ! empty( $this->config['meta_sub'] ) ) {
				if ( '_description' === $this->config['meta_sub'] ) {
					$data_sub = $term->description;
				} elseif ( '_description_parent' === $this->config['meta_sub'] ) {
					$term_parent = ( 0 === $term->parent ) ? $term : get_term( $term->parent, $taxonomy );
					$data_sub    = $term_parent->description;
				} else {
					$meta_sub = get_term_meta( $term->term_id, $this->config['meta_sub'], true );
					if ( ! empty( $meta_sub ) ) {
						$data_sub = $meta_sub;
					}
				}
			}

			if ( 0 === $depth ) {
				$this->last_term_depth_0 = $term;
			}

			$args = array(
				'id'              => $taxonomy . '-' . $term->term_id,
				'name'            => $name,
				'data_style'      => $data_style,
				'data_sub'        => $data_sub,
				'data_class'      => $this->config['option_class'],
				'value'           => $value,
				'in_selected'     => $in_selected,
				'cutemi_disabled' => isset( $args['cutemi_disabled'] ) ? $args['cutemi_disabled'] : false,
				'label'           => apply_filters( 'the_category', $term->name ),
				'depth'           => $depth,
			);

			$output .= 'radio' === $this->input_element
				? $this->start_el_radio( $args )
				: $this->start_el_select( $args );
		}

		/**
		 * Creates the opening markup for the radio input
		 *
		 * @param array $args Array of arguments for creating the element
		 *
		 * @return string       Opening li element and radio input
		 *
		 */
		public function start_el_radio( $args ) {
			return "\n" . sprintf(
				'<li id="%s"><label class="select_it"><input value="%s" type="radio" name="%s" id="in-%s" data-sub="%s" data-style="%s" data-class="%s" %s %s/>%s</label>',
				esc_attr( $args['id'] ),
				esc_attr( $args['value'] ),
				esc_attr( $args['name'] ),
				esc_attr( $args['id'] ),
				esc_attr( $args['data_sub'] ),
				esc_attr( $args['data_style'] ),
				esc_attr( $args['data_class'] ),
				checked( $args['in_selected'], true, false ),
				disabled( empty( $args['cutemi_disabled'] ), false, false ),
				esc_html( $args['label'] )
			);
		}

		/**
		 * Creates the opening markup for the select input
		 *
		 * @param array $args Array of arguments for creating the element
		 *
		 * @return string       Opening option element and option text
		 *
		 */
		public function start_el_select( $args ) {
			$pad = '';
			if ( $this->pad_select ) {
				$pad = str_repeat( '&nbsp;', $args['depth'] * 3 );
			}
			$args['data_class'] .= ' depth' . $args['depth'];

			return "\n" . sprintf(
				'<option %s %s id="%s" data-sub="%s" data-style="%s" data-class="%s" value="%s" class="class-single-term">%s',
				selected( $args['in_selected'], true, false ),
				disabled( empty( $args['cutemi_disabled'] ), false, false ),
				esc_attr( $args['id'] ),
				$pad . esc_attr( $args['data_sub'] ),
				esc_attr( $args['data_style'] ),
				esc_attr( $args['data_class'] ),
				esc_attr( $args['value'] ),
				$pad . esc_html( $args['label'] )
			);
		}

		/**
		 * Ends the element output, if needed.
		 *
		 * @param string $output Passed by reference. Used to append additional content.
		 * @param object $term The current term object.
		 * @param int $depth Depth of the term in reference to parents. Default 0.
		 * @param array $args An array of arguments. @see wp_terms_checklist()
		 *
		 * @see Walker::end_el()
		 */
		public function end_el( &$output, $term, $depth = 0, $args = array() ) {
			if ( 'radio' === $this->input_element ) {
				$output .= "</li>\n";
			} else {
				if ( false === $args['has_children'] ) {
					$output .= "</option> <!-- {$term->name} -->\n";
				}
			}
		}

	}

endif; // class_exists check
