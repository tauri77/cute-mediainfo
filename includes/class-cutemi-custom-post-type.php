<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

class CUTEMI_Custom_Post_Type {
	// Post type name
	public $name = '';

	//form nonce already print?
	public $nonce_flag = false;

	//for register the taxonomy to the post type
	public $taxonomies = array();

	// required post_metas
	public $post_metas_required = array();

	// required taxonomies
	public $taxonomies_required = array();

	// that have special save, taxonomy showing as meta
	public $taxonomies_as_post_metas = array();

	//The post_meta box that are list of item with each field
	public $list_of_fields = array();

	// The boxs for edit post
	public $post_metas = array();

	public function __construct() {
		$this->register();
	}

	public function register() {
		add_action( 'init', array( $this, 'init' ) );
		// register the PostType
		add_action( 'init', array( $this, 'register_post_type' ) );
		// register Taxonomies to the PostType
		add_action( 'init', array( $this, 'register_taxonomies' ) );
		add_action( 'add_meta_boxes', array( $this, 'register_metabox' ) );
		add_action( "save_post_{$this->name}", array( $this, 'save_post_data' ), 10, 3 );
		add_action( 'edit_form_top', array( $this, 'required_field_error_msg' ) );
		add_filter( 'post_type_link', array( $this, 'pre_show_permalinks' ), 1, 2 );

		add_filter( "manage_taxonomies_for_{$this->name}_columns", array( $this, 'taxs_for_columns' ) );

		add_filter( "manage_{$this->name}_posts_columns", array( $this, 'set_custom_columns' ) );
		add_action( "manage_{$this->name}_posts_custom_column", array( $this, 'custom_column' ), 10, 2 );

		add_action( 'admin_enqueue_scripts', array( $this, 'pre_add_admin_scripts' ), 10, 1 );

	}

	public function init() {
		//Map for fields
		foreach ( $this->post_metas as $box_name => $box ) {
			$box_fields = $box['fields'];
			foreach ( $box_fields as $args ) {
				if ( in_array( $args['id'], $this->taxonomies, true ) ) {
					$this->taxonomies_as_post_metas[] = $args['id'];
				}
			}
		}
	}

	//overwrite this
	public function taxs_for_columns( $taxonomies ) {
		return $taxonomies;
	}

	//overwrite this
	public function set_custom_columns( $columns ) {
		return $columns;
	}

	//overwrite this
	public function custom_column( $column, $post_id ) {

	}

	//overwrite this
	public function register_post_type() {

	}

	//overwrite this
	public function pre_show_permalinks( $post_link, $post ) {
		if ( is_object( $post ) && $post->post_type === $this->name ) {
			$_post = get_post( $post );
			if ( $_post ) {
				$this->show_permalinks( $post_link, $_post );
			}
		}

		return $post_link;
	}

	//overwrite this
	public function show_permalinks( $post_link, $post ) {
		return $post_link;
	}

	public function pre_add_admin_scripts( $hook ) {
		global $post;
		if ( 'post-new.php' === $hook || 'post.php' === $hook ) {
			if ( $this->name === $post->post_type ) {
				$this->add_admin_scripts( $hook );
			}
		}
	}

	//overwrite this
	public function add_admin_scripts( $hook ) {

	}

	public function register_taxonomies() {
		foreach ( $this->taxonomies as $tax_name ) {
			register_taxonomy_for_object_type( $tax_name, $this->name );
		}
	}

	public function register_metabox() {
		if ( function_exists( 'add_meta_box' ) ) {
			foreach ( $this->post_metas as $id => $box ) {
				add_meta_box(
					$id,
					$box['title'],
					array( $this, 'custom_box' ),
					$this->name,
					'normal',
					'high'
				);
			}
		}
	}

	public function custom_box( $post, $args ) {
		if ( ! $this->nonce_flag ) {
			wp_nonce_field( $this->name . '_nonce_action', $this->name . '_nonce' );
			$this->nonce_flag = true;
		}
		echo '<div class="cutemi-metabox cutemi-jquery-ui">';
		if ( in_array( $args['id'], $this->list_of_fields, true ) ) {
			$this->print_list_fields( $post, $args['id'] );
		} else {
			// Generate box contents
			foreach ( $this->post_metas[ $args['id'] ]['fields'] as $field ) {
				$this->print_field( $post, $field );
			}
		}
		echo '</div>';
	}


	public function print_list_fields( $post, $id, $sortable = true ) {

		$fields = $this->post_metas[ $id ]['fields'];

		//Count max of "rows" of all fields
		$count_rows = 1;
		foreach ( $fields as $field ) {
			if ( isset( $field['tax_config'] ) ) {
				//value is array of terms taxonomy
				$arr = get_post_meta( $post->ID, 'order:' . $field['id'], true );
			} else {
				//value is a simple array meta data
				$arr = get_post_meta( $post->ID, $field['id'], true );
			}
			if ( is_array( $arr ) && count( $arr ) > $count_rows ) {
				$count_rows = count( $arr );
			}
		}

		if ( true === $sortable ) {
			echo '<ul class="cutemi-list cutemi-sorteable cutemi-count-' . count( $fields ) . '">';
		} else {
			echo '<ul class="cutemi-list cutemi-count-' . count( $fields ) . '">';
		}
		for ( $idx = 0; $idx <= $count_rows; $idx ++ ) {

			if ( $idx === $count_rows ) {
				echo '<li class="cutemi-list-item-template">';
			} else {
				echo '<li class="cutemi-list-item">';
			}

			foreach ( $fields as $field ) {
				$args = $field;
				if ( isset( $args['tax_config'] ) ) {
					$args['tax_config']['as_array_item'] = ( $idx === $count_rows ) ? '' : $idx;
				} else {
					$args['as_array_item'] = ( $idx === $count_rows ) ? '' : $idx;
				}
				$this->print_field( $post, $args );
			}
			if ( true === $sortable ) {
				echo '<span class="cutemi-item-sort-action">
							<a class="ui-button" href="javascript:void(0)" onclick="cutemiItemDelete(this);"> &#10005; </a>
							<a class="ui-button cutemi-action-down" href="javascript:void(0)" onclick="cutemiItemDown(this);"> &#8681; </a>
							<a class="ui-button cutemi-action-up" href="javascript:void(0)" onclick="cutemiItemUp(this);"> &#8679; </a>
						</span><hr>';
			} else {
				echo '<span class="cutemi-item-sort-action">
						<a class="ui-button" href="javascript:void(0)" onclick="cutemiItemDelete(this);"> &#10005; </a>
				      </span><hr>';
			}
			echo '</li>';
		}
		echo '</ul>';
		echo '<a class="ui-button cutemi-button-add" href="javascript:void(0)" onclick="cutemiItemAdd(this);"> + </a>';
	}

	public function print_field( $post, $field ) {
		$short = apply_filters( 'cutemi_field_shortcut_' . $this->name, false, $field, $post );
		if ( false !== $short ) {
			return;
		}
		do_action( 'cutemi_field_before_' . $this->name, $field, $post );
		if ( isset( $field['tax_config'] ) ) {
			$this->get_taxonomy_select( $field['id'], $field['tax_config'], $post );
		} else {
			$this->field_html( $field, $post );
		}
		do_action( 'cutemi_field_after_' . $this->name, $field, $post );
	}

	public function get_taxonomy_select( $taxonomy, $config, $post = false ) {

		if ( false === $post ) {
			$post = $GLOBALS['posts'];
		}

		$taxonomy_obj = get_taxonomy( $taxonomy );
		if ( ! is_object( $taxonomy_obj ) ) {
			return;
		}

		$defaults = array(
			'as_array_item'    => - 1,
			'empty_disable'    => false,
			'show_label'       => false,
			'no_selected_text' => ! empty( $config['text'] ) ? $config['text'] : 'Select',
			'query_terms_args' => array(
				'orderby'    => 'meta_value_num',
				'order'      => 'DESC',
				'meta_key'   => 'priority_sum',
				'hide_empty' => false,
				'meta_query' => array(
					'relation' => 'OR',
					array(
						'key'     => 'cutemi_disabled',
						'compare' => '!=',
						'value'   => '1',
					),
					array(
						'key'     => 'cutemi_disabled',
						'compare' => 'NOT EXISTS',
					),
				),
			),
		);

		$config = wp_parse_args( $config, $defaults );

		$selected = - 1;

		if ( - 1 < $config['as_array_item'] ) {
			$order_meta = get_post_meta( $post->ID, 'order:' . $taxonomy, true );
			if ( is_array( $order_meta ) ) {
				if ( '' === $config['as_array_item'] ) {
					$selected = '';
				} elseif ( isset( $order_meta[ $config['as_array_item'] ] ) ) {
					$selected_slug = $order_meta[ $config['as_array_item'] ];
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
				if ( - 1 < $config['as_array_item'] ) {
					$selected = $selected[ $config['as_array_item'] ];
				} else {
					$selected = array_shift( $selected );
				}
			}
		}

		if ( is_object( $selected ) ) { //not as array item
			$selected = $selected->term_id;
		}

		$config['hierarchical']  = $taxonomy_obj->hierarchical;
		$config['input_element'] = 'select';

		$terms_args             = $config['query_terms_args'];
		$terms_args['taxonomy'] = $taxonomy;

		if ( isset( $config['meta_filter'] ) ) {
			$terms_args['meta_query'][] = $config['meta_filter'];
		}

		$terms = (array) get_terms( $terms_args );
		if ( ! cutemi_term_is_enable( $selected ) ) {
			$terms[] = get_term( $selected, $taxonomy );
		}

		//add no selected option
		$extra_name  = ( - 1 < $config['as_array_item'] ) ? '[]' : '';
		$extra_id    = ( - 1 < $config['as_array_item'] ) ? '[' . $config['as_array_item'] . ']' : '';
		$extra_first = ( '' === $selected ) ? ' selected' : '';

		if ( true === $config['empty_disable'] ) { //required option selected
			$extra_first .= ' disabled';
		}
		$extra_class = 'cutemi-col-' . $config['as_array_item'] . ' field-' . $taxonomy . $extra_name;

		$no_selected_data_style = '';
		if ( ! empty( $config['no_selected_img'] ) ) {
			$no_selected_data_style = 'background-image: url(&quot;' . esc_attr( $config['no_selected_img'] ) . '&quot;);';
		}

		/**
		 * OK, render
		 */
		printf(
			'<div class="cutemi-field %s">',
			isset( $config['field_class'] ) ? esc_attr( $config['field_class'] ) : ''
		);

		if ( true === $config['show_label'] ) {
			printf(
				'<label for="%s">%s</label>',
				esc_attr( $taxonomy . $extra_id ),
				esc_html( $config['text'] )
			);
		}

		printf(
			'<select class="%s" style="width: 40%%;" id="%s" name="%s">',
			esc_attr( $extra_class . ' inline-form-obj ui-cutemiselectmenu tax-' . $taxonomy ),
			esc_attr( $taxonomy . $extra_id ),
			esc_attr( $taxonomy . $extra_name )
		);

		printf(
			'<option value="" %s data-style="%s">%s</option>',
			esc_attr( $extra_first ),
			esc_attr( $no_selected_data_style ),
			esc_html( $config['no_selected_text'] )
		);

		$walker = new CUTEMI_Walker_Taxonomy_Single_Term( $config );
		$args   = array(
			'taxonomy'      => $taxonomy,
			'selected_cats' => array( $selected ),
			'popular_cats'  => false,
			'checked_ontop' => false,
			'walker'        => $walker,
			'echo'          => false,
		);
		//phpcs:ignore WordPress.Security.EscapeOutput
		echo call_user_func_array( array( $walker, 'walk' ), array( $terms, 0, $args ) ); //Already escaped

		echo '</select>';

		echo '</div>';
	}

	public function field_html( $args, $post ) {
		$defaults = array(
			'as_array_item' => - 1,
			'label'         => '',
		);

		$args = wp_parse_args( $args, $defaults );

		if ( ! isset( $args['type'] ) ) {
			$args['type'] = 'text';
		}
		switch ( $args['type'] ) {
			case 'textarea':
				$this->text_area_render( $args, $post );
				break;
			case 'select':
				$this->select_render( $args, $post );
				break;
			case 'number':
				$this->number_field_render( $args, $post );
				break;
			case 'text':
			default:
				$this->text_field_render( $args, $post );
		}
	}

	/**
	 * Get field value (meta value).
	 *
	 * The args parameter has keys 'id', 'as_array_item' and 'default'.
	 * If 'as_array_item' is > -1, the meta is an array and return the item with index 'as_array_item' of meta.
	 *
	 * @param array $args Associative array with args for field (keys: 'id', 'as_array_item' and 'default')
	 * @param WP_Post $post Post object
	 *
	 * @return mixed|string The field value.
	 */
	public static function get_field_value( $args, $post ) {

		$meta_value = get_post_meta( $post->ID, $args['id'], true );

		if ( - 1 < $args['as_array_item'] ) {
			// meta is array or values
			if ( is_array( $meta_value ) ) {
				$meta_value = isset( $meta_value[ $args['as_array_item'] ] ) ? $meta_value[ $args['as_array_item'] ] : '';
			} elseif ( '0' !== $args['as_array_item'] ) {
				$meta_value = '';
			}
		} else {
			if ( is_array( $meta_value ) ) {
				$meta_value = isset( $meta_value[0] ) ? $meta_value[0] : '';
			}
		}

		if ( '' === $meta_value ) {
			if ( isset( $args['default'] ) ) {
				return $args['default'];
			}
		}

		return $meta_value;
	}

	/**
	 * Print field label
	 *
	 *
	 * @param array $args require keys 'id' and 'label'
	 * @param string $part
	 *
	 */
	public function print_field_label( $args, $part = 'start' ) {
		if ( ! isset( $args['show_label'] ) || false !== $args['show_label'] ) {
			if ( 'start' === $part ) {
				printf(
					'<div class="' . esc_attr( $this->get_field_div_class( $args ) ) . '">' .
					'<label for="%1$s">%2$s</label>',
					esc_attr( $this->get_field_id( $args ) ),
					esc_html( $args['label'] )
				);
			} else {
				echo '</div>';
			}
		}
	}

	public function get_field_div_class( $args ) {
		$div_class  = 'cutemi-field';
		$div_class .= ( isset( $args['field_class'] ) ? ' ' . $args['field_class'] : '' );
		$div_class .= ' cutemi-col-' . $args['as_array_item'] . ' field-' . $args['id'];

		return $div_class;
	}

	public function get_field_id( $args ) {
		if ( - 1 < $args['as_array_item'] ) {
			return $args['id'] . '[' . $args['as_array_item'] . ']';
		}

		return $args['id'];
	}

	public function get_field_name( $args ) {
		if ( - 1 < $args['as_array_item'] ) {
			return $args['id'] . '[]';
		}

		return $args['id'];
	}


	/**
	 * Displays a text field
	 *
	 * @param array $args settings field args
	 * @param $post
	 *
	 */
	public function text_field_render( $args, $post ) {

		$args['label'] = isset( $args['label'] ) ? $args['label'] : '';
		$args['value'] = self::get_field_value( $args, $post );

		$class = '';
		if ( isset( $args['type'] ) && 'date' === $args['type'] ) {
			if ( ! empty( $args['value'] ) ) {
				$timestamp = strtotime( $args['value'] );
				if ( false === $timestamp ) {
					$args['value'] = '';
				} else {
					$args['value'] = gmdate( 'Y-m-d', $timestamp );
				}
			}
			$class .= ' cutemi-datepicker';
		}

		$this->print_field_label( $args, 'start' );
		printf(
			'<input class="%4$s" type="text" autocomplete="off" name="%3$s" id="%1$s" value="%2$s" />',
			esc_attr( $this->get_field_id( $args ) ),
			esc_attr( $args['value'] ),
			esc_attr( $this->get_field_name( $args ) ),
			esc_attr( $class )
		);
		$this->print_field_label( $args, 'end' );
	}

	/**
	 * Displays a textarea field.
	 *
	 * @param array $args Settings field args (array keys: id, label, show_label, field_class, as_array_item)
	 * @param WP_Post $post Post object
	 *
	 */
	public function text_area_render( $args, $post ) {

		$args['value'] = self::get_field_value( $args, $post );
		$args['label'] = isset( $args['label'] ) ? $args['label'] : '';

		$this->print_field_label( $args, 'start' );
		printf(
			'<textarea rows="5" name="%3$s" id="%1$s">%2$s</textarea>',
			esc_attr( $this->get_field_id( $args ) ),
			esc_textarea( $args['value'] ),
			esc_attr( $this->get_field_name( $args ) )
		);
		$this->print_field_label( $args, 'end' );
	}

	/**
	 * Displays a select field
	 *
	 * @param array $args Settings field args (array keys: id, label, show_label, field_class, as_array_item)
	 * @param WP_Post $post Post object
	 *
	 */
	public function select_render( $args, $post ) {

		$value = self::get_field_value( $args, $post );
		$class = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

		$this->print_field_label( $args, 'start' );
		printf(
			'<select class="%s" id="%s" name="%s">',
			esc_attr( $class ),
			esc_attr( $this->get_field_id( $args ) ),
			esc_attr( $this->get_field_name( $args ) )
		);
		foreach ( $args['options'] as $key => $label ) {
			printf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $key ),
				selected( $value, $key, false ),
				esc_attr( $label )
			);
		}
		echo '</select>';
		$this->print_field_label( $args, 'end' );

	}

	/**
	 * Displays a number field
	 *
	 * @param array $args Settings field args (array keys: id, label, show_label, field_class, as_array_item)
	 * @param WP_Post $post Post object
	 *
	 */
	public function number_field_render( $args, $post ) {
		$args['value'] = self::get_field_value( $args, $post );

		$this->print_field_label( $args, 'start' );
		printf(
			'<input type="number" name="%3$s" id="%1$s" value="%2$s" />',
			esc_attr( $this->get_field_id( $args ) ),
			esc_attr( $args['value'] ),
			esc_attr( $this->get_field_name( $args ) )
		);
		$this->print_field_label( $args, 'end' );
	}

	public function required_field_error_msg( $post ) {
		if ( get_post_type( $post ) === $this->name && get_post_status( $post ) !== 'auto-draft' ) {
			foreach ( $this->post_metas_required as $meta_key ) {
				$meta_value = get_post_meta( $post->ID, $meta_key, true );
				if ( is_wp_error( $meta_value ) || empty( $meta_value ) ) {
					printf(
						'<div class="error below-h2"><p>%s</p></div>',
						esc_html(
							sprintf(
								// translators: %1$s is the field and %2$s is the object. Ex Size and Mediainfo
								__( '%1$s is mandatory for creating a new %2$s', 'cute-mediainfo' ),
								$meta_key,
								$this->name
							)
						)
					);
				}
			}
			foreach ( $this->taxonomies_required as $tax_key ) {
				$terms = wp_get_object_terms(
					$post->ID,
					$tax_key,
					array(
						'orderby' => 'term_id',
						'order'   => 'ASC',
					)
				);
				if ( is_wp_error( $terms ) || empty( $terms ) ) {
					printf(
						'<div class="error below-h2"><p>%s</p></div>',
						sprintf(
							'The %1$s is mandatory for creating a new %2$s',
							esc_html( $tax_key ),
							esc_html( $this->name )
						)
					);
				}
			}
		}
	}

	public function save_post_data( $post_id, $post, $update ) {
		// Check if nonce is valid.
		if (
			! isset( $_POST[ $this->name . '_nonce' ] ) ||
			! wp_verify_nonce( sanitize_key( $_POST[ $this->name . '_nonce' ] ), $this->name . '_nonce_action' )
		) {
			return;
		}

		// Check if user has permissions to save data.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check if not an autosave.
		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// Check if not a revision.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		foreach ( $this->post_metas as $box_id => $box ) {
			$box_fields = $box['fields'];
			foreach ( $box_fields as $box_prop ) {
				$key = $box_prop['id'];
				if ( isset( $_POST[ $key ] ) ) {
					$raw_value = wp_unslash( $_POST[ $key ] );
					if ( in_array( $key, $this->taxonomies_as_post_metas, true ) ) {
						$is_required = in_array( $key, $this->taxonomies_required, true );
						$is_sortable = in_array( $box_id, $this->list_of_fields, true );
						$this->save_post_terms( $post_id, $raw_value, $key, $is_required, $is_sortable );
						continue;
					}
					if ( '0' !== $raw_value && empty( $raw_value ) ) {
						if ( in_array( $key, $this->post_metas_required, true ) ) {
							$this->safe_set_as_draft( $post_id );
						}
					}
					$sanitized_meta_data = self::sanitize_meta_data( $raw_value, $box_prop );
					update_post_meta( $post_id, $key, wp_slash( $sanitized_meta_data ) );
				} else {
					if ( in_array( $key, $this->post_metas_required, true ) ) {
						$this->safe_set_as_draft( $post_id );
					}
				}
			}
		}

	}

	/**
	 * Set terms for taxonomy, and save terms order as metadata "order:TAXONOMY"
	 *
	 * @param $post_id
	 * @param $raw_values
	 * @param $taxonomy
	 * @param $is_required
	 * @param $is_sortable
	 * @param bool $empty_terms
	 */
	private function save_post_terms( $post_id, $raw_values, $taxonomy, $is_required, $is_sortable, $empty_terms = true ) {

		if ( is_string( $raw_values ) ) {
			$sanitized_terms = sanitize_title( $raw_values );
		} else {
			$sanitized_terms = array_map( 'sanitize_title', $raw_values );
		}
		// A valid term is required, so don't let this get published without one
		if ( empty( $sanitized_terms ) ) {
			if ( $is_required ) {
				$this->safe_set_as_draft( $post_id );
			}
		} else {
			if ( ! is_array( $sanitized_terms ) ) {
				$sanitized_terms = array( $sanitized_terms );
			}
			$set_terms_id = array();
			$meta_order   = array();
			foreach ( $sanitized_terms as $idx => $sanitized_term ) {
				// Maybe someone field of the list is empty, add this for preserve order
				// The last is the template, isn't item
				if ( ( '' === $sanitized_term ) && $empty_terms && ( $idx < count( $sanitized_terms ) - 1 ) ) {
					$meta_order[] = '';
				}
				$term = get_term_by( 'slug', $sanitized_term, $taxonomy );
				if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
					$set_terms_id[] = $term->term_id;
					$meta_order[]   = $term->slug;
				}
			}
			if ( $is_required && empty( $set_terms_id ) ) {
				$this->safe_set_as_draft( $post_id );
				$set_terms_id = false;
			}
			if ( false !== $set_terms_id ) {
				wp_set_object_terms( $post_id, $set_terms_id, $taxonomy, false );
				if ( $is_sortable ) {
					//remove last empty values
					while ( '' === end( $meta_order ) ) {
						array_pop( $meta_order );
					}
					update_post_meta( $post_id, 'order:' . $taxonomy, $meta_order );
				}
			}
		}
	}

	public function safe_set_as_draft( $post_id ) {
		// unhook this function so it doesn't loop infinitely
		remove_action( "save_post_{$this->name}", array( $this, 'save_post_data' ) );
		$post_data = array(
			'ID'          => $post_id,
			'post_status' => 'draft',
		);
		wp_update_post( $post_data );
	}

	public static function sanitize_meta_data( $raw_meta_data, $box_prop ) {
		if ( isset( $box_prop['as_array_item'] ) && $box_prop['as_array_item'] > - 1 ) {
			//sanitize each item
			foreach ( $raw_meta_data as $k => $raw_value ) {
				$raw_meta_data[ $k ] = self::sanitize_simple_meta( $raw_value, $box_prop );
			}
			//remove not values
			while (
				'' === end( $raw_meta_data ) ||
				(
					isset( $box_prop['default'] ) &&
					end( $raw_meta_data ) === $box_prop['default']
				)
			) {
				array_pop( $raw_meta_data );
			}
			$raw_meta_data = array_values( $raw_meta_data );
		} else {
			$raw_meta_data = self::sanitize_simple_meta( $raw_meta_data, $box_prop );
		}

		return $raw_meta_data;
	}

	public static function sanitize_simple_meta( $raw_value, $box_prop ) {
		if ( ! isset( $box_prop['type'] ) ) {
			$box_prop['type'] = 'text';
		}
		if ( 'date' === $box_prop['type'] ) {
			$meta_data = sanitize_text_field( $raw_value );
			if ( '' !== $meta_data ) {
				$timestamp = strtotime( $meta_data );
				if ( false === $timestamp ) {
					$meta_data = ''; //no valid date
				} else {
					$meta_data = gmdate( 'Ymd', $timestamp );
				}
			}
		} elseif ( 'textarea' === $box_prop['type'] ) {
			$meta_data = sanitize_textarea_field( $raw_value );
		} elseif ( 'filename' === $box_prop['type'] ) {
			$meta_data = sanitize_file_name( $raw_value );
		} elseif ( 'url_part' === $box_prop['type'] ) {
			$meta_data = self::sanitize_url_part( $raw_value );
		} elseif ( 'url' === $box_prop['type'] ) {
			$meta_data = esc_url_raw( $raw_value );
		} else {
			$meta_data = sanitize_text_field( $raw_value );
		}

		return $meta_data;
	}

	public static function sanitize_url_part( $url ) {
		$url = preg_replace( '|[^a-z 0-9-~+_.?#=!&;,/:%@$\|*\'()\[\]\\x80-\\xff]|i', '', $url );

		return $url;
	}

}
