<?php

if ( ! class_exists( 'CUTEMI_Walker_Term_Install' ) && class_exists( 'Walker' ) ) :

	class CUTEMI_Walker_Term_Install extends Walker {
		public $tree_type = 'category';
		public $db_fields = array(
			'parent' => 'parent',
			'id'     => 'slug',
		);
		public $config    = array();

		public $hierarchical;

		public function __construct( $config ) {
			$this->config       = $config;
			$this->hierarchical = $config['hierarchical'];
		}

		public function start_lvl( &$output, $depth = 0, $args = array() ) {
			$indent  = str_repeat( "\t", $depth );
			$output .= "$indent<ul class='children'>\n";
		}

		public function end_lvl( &$output, $depth = 0, $args = array() ) {
			$indent  = str_repeat( "\t", $depth );
			$output .= "$indent</ul>\n";
		}

		public function start_el( &$output, $term, $depth = 0, $args = array(), $id = 0 ) {

			$taxonomy = $args['taxonomy'];
			$name     = 'cutemi_setup_tax[' . $taxonomy . '][]';

			if ( is_array( $term ) ) {
				$term = (object) $term;
			}
			if ( ! is_object( $term ) ) {
				return;
			}
			$id_field = $this->db_fields['id'];

			$value = $term->$id_field;

			$selected_cats = empty( $args['selected_cats'] ) ? array() : $args['selected_cats'];
			$in_selected   = in_array( $term->$id_field, $selected_cats, true );

			$data_sub = '';
			$img      = cutemi_get_term_icon_pack( $taxonomy, $value );

			if ( property_exists( $term, 'description' ) ) {
				$data_sub = $term->description;
			}

			$code_img_format = '<span class="cutemi-setup-check-img"><img src="%s" alt="icon"></span>';

			$output .= "\n" . sprintf(
				'<li class="cutemi-setup-term-option %s" id="%s"><label class="select_it">' .
					'<span class="cutemi-setup-check"><input value="%s" type="checkbox" name="%s" id="in-%s" ' .
					' %s/></span>%s<span class="cutemi-setup-check-info">' .
					'<b>%s</b><i>%s</i></span></label>',
				esc_attr( 'depth' . $depth ),
				esc_attr( $taxonomy . '-' . $term->$id_field ),
				esc_attr( $value ),
				esc_attr( $name ),
				esc_attr( $taxonomy . '-' . $term->$id_field ),
				checked( $in_selected, true, false ),
				! empty( $img ) ? sprintf( $code_img_format, esc_url( $img ) ) : '',
				esc_html( apply_filters( 'the_category', $term->name ) ),
				esc_attr( $data_sub )
			);
		}

		public function end_el( &$output, $term, $depth = 0, $args = array() ) {
			$output .= "</li>\n";
		}

	}

endif; // class_exists check
