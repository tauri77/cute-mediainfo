<?php

/**
 * Class CUTEMI_Taxonomy_Customs_Metas
 * For custom taxonomies with metadata
 * Extends from this and set tax_name, meta_fields and meta_columns
 */
class CUTEMI_Taxonomy_Customs_Metas {

	/**
	 * @var array List the terms metas with our config
	 */
	public $meta_fields = array();

	/**
	 * @var string Taxonomy slug
	 */
	public $tax_name = '';

	/**
	 * @var array the metas key to add has column on admin terms (with label and optionally position)
	 */
	public $meta_columns = array();

	/**
	 * @var bool Add a field to select that not change on update
	 */
	public $add_prevent_updates = true;

	/**
	 * @var int 0: disable, 1: enable, 2: enable with column
	 */
	public $priority_meta_mode = 1;
	/**
	 * @var int 0: disable, 1: enable, 2: enable with column
	 */
	public $image_meta_mode = 2;
	/**
	 * @var int 0: disable, 1: enable
	 */
	public $disable_meta_mode = 1;

	/**
	 * @var bool|false|WP_Taxonomy
	 */
	public $taxonomy;

	/**
	 * @var string If term start with this prefix then unable to delete
	 */
	public $disable_delete_prefix = 'cutemi-';

	public function __construct() {
		//Set on child class metas and tax_name
		if ( empty( $this->tax_name ) ) {
			wp_die( 'Set on __construct the tax_name and meta_fields..' );
		}

		add_action( 'admin_init', array( $this, 'action_init' ) );
	}

	public function action_init() {
		$this->taxonomy = get_taxonomy( $this->tax_name );
		if ( empty( $this->taxonomy ) ) {
			die( 'Taxonomy not found.' );
		}
		$this->init();
	}

	public function init() {
		$singular_name = $this->taxonomy->labels->singular_name;

		if ( $this->priority_meta_mode > 0 ) {
			$this->meta_fields[] = array(
				'label'   => __( 'Priority', 'cute-mediainfo' ),
				'id'      => 'priority_sum',
				'type'    => 'number',
				'desc'    => __( 'Determine the order', 'cute-mediainfo' ),
				'default' => 1,
			);
			if ( 2 === $this->priority_meta_mode ) {
				$this->meta_columns['priority_sum'] = array(
					'label'    => __( 'Priority', 'cute-mediainfo' ),
					'position' => - 1,
					'sortable' => true,
					'width'    => '84px',
					'type'     => 'NUMERIC',
					// NUMERIC|BINARY|CHAR|DATE|DATETIME|DECIMAL|SIGNED|TIME|UNSIGNED. Default value is 'CHAR'.
				);
			}
		}
		if ( $this->image_meta_mode > 0 ) {
			$this->meta_fields[] = array(
				'label' => __( 'Icon', 'cute-mediainfo' ),
				'id'    => 'image_url',
				'type'  => 'media',
				'desc'  => __( 'Small image.', 'cute-mediainfo' ),
			);
			if ( 2 === $this->image_meta_mode ) {
				$this->meta_columns['image_url'] = array(
					'label'    => __( 'Icon', 'cute-mediainfo' ),
					'position' => 1,
					'sortable' => false,
					'type'     => 'IMAGE',
				);
			}
		}

		/* translators: string is the singular name of term. Ex: Video Resolution*/
		$check_label = __( 'Disable this %s for new videos', 'cute-mediainfo' );
		if ( $this->disable_meta_mode > 0 ) {
			$this->meta_fields[] = array(
				'label'          => __( 'Disabled', 'cute-mediainfo' ),
				'id'             => 'cutemi_disabled',
				'type'           => 'checkbox',
				'checkbox_label' => sprintf( $check_label, $singular_name ),
				'desc'           => '',
				'default'        => '',
			);
		}

		$this->meta_fields  = apply_filters( 'cutemi_tax_custom_meta_fields', $this->meta_fields, $this->tax_name );
		$this->meta_columns = apply_filters( 'cutemi_tax_custom_meta_columns', $this->meta_columns, $this->tax_name );

		if ( $this->add_prevent_updates ) {
			$for_disable_option = array(
				'name'        => __( 'Name', 'cute-mediainfo' ),
				'description' => __( 'Description', 'cute-mediainfo' ),
			);
			if ( is_taxonomy_hierarchical( $this->tax_name ) ) {
				$for_disable_option['parent'] = 'Parent';
			}
			$default = array();
			foreach ( $this->meta_fields as $meta_field ) {
				if ( 'cutemi_disabled' === $meta_field['id'] ) {
					$default[] = 'cutemi_disabled';
				}
				$for_disable_option[ $meta_field['id'] ] = $meta_field['label'];
			}
			$this->meta_fields[] = array(
				'label'   => __( 'Prevent changes when updating', 'cute-mediainfo' ),
				'id'      => 'cutemi_disable_update',
				'type'    => 'checkbox_multi',
				'desc'    => __( 'Fields that you edit and want them to not change when the plugin updates.', 'cute-mediainfo' ),
				'default' => $default,
				'options' => $for_disable_option,
			);
		}

		if ( current_user_can( 'manage_categories' ) ) {

			add_filter( $this->tax_name . '_row_actions', array( $this, 'row_actions' ), 10, 2 );

			if ( ! empty( $this->disable_delete_prefix ) ) {
				//disable bulk actions
				//add_filter( 'bulk_actions-edit-'.$this->tax_name, '__return_empty_array' );
				add_filter( 'user_has_cap', array( $this, 'user_has_cap' ), 10, 3 );
				add_filter( 'map_meta_cap', array( $this, 'map_meta_cap' ), 10, 4 );
			}
			add_action( "{$this->tax_name}_add_form_fields", array( $this, 'create_fields' ), 10, 1 );
			add_action( "{$this->tax_name}_edit_form_fields", array( $this, 'edit_fields' ), 10, 2 );
			add_action( "created_{$this->tax_name}", array( $this, 'save_fields' ), 10, 1 );
			add_action( "edited_{$this->tax_name}", array( $this, 'save_fields' ), 10, 1 );

			add_action( 'admin_enqueue_scripts', 'wp_enqueue_media' );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10, 1 );

			add_filter( "manage_edit-{$this->tax_name}_columns", array( $this, 'custom_columns' ) );

			// Make custom column sortable
			add_filter( "manage_edit-{$this->tax_name}_sortable_columns", array( $this, 'tax_sortable_columns' ), 10, 3 );
			add_filter( "manage_{$this->tax_name}_custom_column", array( $this, 'add_custom_column_content' ), 10, 3 );

			// Add custom sortable content to taxonomy query
			add_filter( 'pre_get_terms', array( $this, 'orderby' ) );

		}
	}

	public function tax_sortable_columns( $columns ) {
		foreach ( $this->meta_columns as $key => $config ) {
			if ( true === $config['sortable'] ) {
				$columns[ $key ] = $key;
			}
		}

		return $columns;
	}

	public function user_has_cap( $all_caps, $caps, $args ) {
		if ( is_array( $caps ) && isset( $caps[0] ) && 'cutemi_delete_term' === $caps[0] ) {
			if ( 'delete_term' === $args[0] ) {
				if ( $args[2] ) {
					$tag = get_term( $args[2], $this->tax_name );
					if ( null === $tag ) {
						return $all_caps;
					}
					$start = substr( $tag->slug, 0, strlen( $this->disable_delete_prefix ) );
					if ( $start === $this->disable_delete_prefix ) {
						return $all_caps;
					}
				}
			}
			if ( isset( $all_caps['manage_categories'] ) && true === $all_caps['manage_categories'] ) {
				$all_caps['cutemi_delete_term'] = 1;
			}
		}

		return $all_caps;
	}

	public function map_meta_cap( $caps, $req_cap, $user_id, $args ) {
		if (
			is_multisite() &&
			is_super_admin( $user_id ) &&
			'cutemi_delete_term' === $req_cap
		) {
			$caps[] = 'do_not_allow';
		}

		return $caps;
	}

	public function row_actions( $actions, $tag ) {
		if ( isset( $actions['inline hide-if-no-js'] ) ) {
			unset( $actions['inline hide-if-no-js'] );
		}

		return $actions;
	}

	public function orderby( $term_query ) {
		global $pagenow;

		if ( ! is_admin() ) {
			return $term_query;
		}
		if (
			! is_admin() ||
			'edit-tags.php' !== $pagenow ||
			empty( $term_query->query_vars['taxonomy'] ) ||
			$term_query->query_vars['taxonomy'][0] !== $this->tax_name
		) {
			return $term_query;
		}

		//Not nonce...
		//Sanitize and validate
		$order_by = '';
		if ( isset( $_GET['orderby'] ) ) {
			$order_by = sanitize_text_field( wp_unslash( $_GET['orderby'] ) );
		}
		$order = false;
		if ( isset( $_GET['order'] ) ) {
			$order = sanitize_key( wp_unslash( $_GET['order'] ) );
		}
		//validate order
		if ( ! in_array( $order, array( 'asc', 'desc' ), true ) ) {
			$order = 'DESC';
		}
		//get valid sortable keys
		$sortables = array_keys(
			array_filter(
				$this->meta_columns,
				function ( $meta_config ) {
					return ( true === $meta_config['sortable'] );
				}
			)
		);
		//validate order_by
		if ( in_array( $order_by, $sortables, true ) ) {
			// set orderby to the named clause in the meta_query
			$term_query->query_vars['orderby'] = 'order_clause';
			$term_query->query_vars['order']   = $order;
			// the OR relation and the NOT EXISTS clause allow for terms without a meta_value at all
			$args                   = array(
				'relation'     => 'OR',
				'order_clause' => array(
					'key'  => $order_by,
					'type' => $this->meta_columns[ $order_by ]['type'],
				),
				array(
					'key'     => $order_by,
					'compare' => 'NOT EXISTS',
				),
			);
			$term_query->meta_query = new WP_Meta_Query( $args );
		}

		return $term_query;
	}

	public function custom_columns( $columns ) {
		// remove slug column
		unset( $columns['slug'] );

		foreach ( $this->meta_columns as $key => $config ) {
			if ( ! isset( $config['position'] ) || $config['position'] < 0 ) {
				$columns[ $key ] = $config['label'];
				continue;
			}
			$before        = array_slice( $columns, 0, $config['position'], true );
			$after         = array_slice( $columns, $config['position'], count( $columns ), true );
			$custom_column = array(
				$key => $config['label'],
			);
			$columns       = array_merge( $before, $custom_column, $after );
		}

		return $columns;
	}

	public function add_custom_column_content( $content, $column_name, $term_id ) {
		if ( isset( $this->meta_columns[ $column_name ] ) ) {
			$content    = '';
			$meta_value = get_term_meta( $term_id, $column_name, true );
			if ( ! empty( $meta_value ) ) {
				if ( 'IMAGE' === $this->meta_columns[ $column_name ]['type'] ) {
					$content = '<img src="' . esc_attr( $meta_value ) . '">';
				} else {
					$content = $meta_value;
				}
			} else {
				if ( 'image_url' === $column_name ) {
					if ( 'IMAGE' === $this->meta_columns[ $column_name ]['type'] ) {
						$src = cutemi_get_term_icon_pack( $this->tax_name, $term_id );
						if ( ! empty( $src ) ) {
							$content = '<img src="' . esc_attr( $src ) . '">';
						}
					}
				}
			}
		}

		return $content;
	}


	public function enqueue_scripts( $hook_suffix ) {
		if ( 'term.php' === $hook_suffix || 'edit-tags.php' === $hook_suffix ) {
			if ( ! isset( $GLOBALS['taxnow'] ) || $this->tax_name !== $GLOBALS['taxnow'] ) {
				return;
			}
			$plugin_dir_url = plugin_dir_url( CUTE_MEDIAINFO_FILE );
			wp_register_script(
				'cutemi_tax_script.js',
				$plugin_dir_url . 'admin/assets/js/term-edit.js',
				array(),
				'0.11',
				false
			);
			wp_enqueue_script( 'cutemi_tax_script.js' );

			wp_localize_script(
				'cutemi_tax_script.js',
				'cutemi_tax_script',
				array(
					'ajaxUrl'                => admin_url( 'admin-ajax.php' ),
					'select_or_upload_media' => __( 'Select or Upload Media', 'cute-mediainfo' ),
					'use_this_media'         => __( 'Use this media', 'cute-mediainfo' ),
				)
			);

			wp_register_style(
				'cutemi_tax_term.css',
				$plugin_dir_url . 'admin/assets/css/tax-term.css',
				false,
				'1.0.3'
			);
			wp_enqueue_style( 'cutemi_tax_term.css' );
		}
	}


	public function create_fields( $taxonomy ) {
		wp_nonce_field( $this->tax_name . '_save', $this->tax_name . '_nonce' );
		foreach ( $this->meta_fields as $meta_field ) {
			$meta_value = '';
			if ( ! empty( $meta_field['default'] ) ) {
				$meta_value = $meta_field['default'];
			}
			$this->print_meta_fields( $meta_field, $meta_value );
		}
	}

	public function edit_fields( $term, $taxonomy ) {
		wp_nonce_field( $this->tax_name . '_save', $this->tax_name . '_nonce' );
		foreach ( $this->meta_fields as $meta_field ) {
			$meta_value = get_term_meta( $term->term_id, $meta_field['id'], true );
			$this->print_meta_fields( $meta_field, $meta_value );
		}
	}

	private function print_meta_fields( $meta_field, $meta_value ) {
		echo '<div class="form-field">';
		echo '<tr class="form-field"><th>';
		echo '<label for="' . esc_attr( $meta_field['id'] ) . '">' . esc_html( $meta_field['label'] ) . '</label>';
		echo '</th><td>';

		switch ( $meta_field['type'] ) {
			case 'media':
				$this->media_field( $meta_field, $meta_value );
				break;
			case 'checkbox':
				$this->field_checkbox( $meta_field, $meta_value );
				break;
			case 'checkbox_multi':
				$this->field_checkbox_multi( $meta_field, $meta_value );
				break;
			case 'select':
				$this->field_select( $meta_field, $meta_value );
				break;
			case 'textarea':
				$this->field_textarea( $meta_field, $meta_value );
				break;
			default:
				$this->field_default( $meta_field, $meta_value );
		}
		if ( ! empty( $meta_field['desc'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $meta_field['desc'] ) );
		}
		echo '</td></tr>';
		echo '</div>';
	}

	private function media_field( $meta_field, $meta_value ) {
		printf(
			'<input style="width: 80%%" id="%s" name="%s" type="text" value="%s"> ' .
			'<input style="width: 15%%" class="button cutemi-tax-media ' .
			'%s-media" id="%s_button" data-for="%s" name="%s_button" type="button" value="Upload" />',
			esc_attr( $meta_field['id'] ),
			esc_attr( $meta_field['id'] ),
			esc_attr( $meta_value ),
			esc_attr( $this->tax_name ),
			esc_attr( $meta_field['id'] ),
			esc_attr( $meta_field['id'] ),
			esc_attr( $meta_field['id'] )
		);
		if ( ! empty( $meta_value ) ) {
			printf(
				'<div class="custom-img-container"><img src="%s" alt="" style="max-width:100%%;"/></div>',
				esc_url( $meta_value )
			);
		} else {
			echo '<div class="custom-img-container"></div>';
		}
	}

	private function field_checkbox( $meta_field, $meta_value = '' ) {
		printf(
			'<label for="%s"><input %s id="%s" name="%s" type="checkbox" value="1">%s</label>',
			esc_attr( $meta_field['id'] ),
			'1' === $meta_value ? 'checked' : '',
			esc_attr( $meta_field['id'] ),
			esc_attr( $meta_field['id'] ),
			esc_html( isset( $meta_field['checkbox_label'] ) ? $meta_field['checkbox_label'] : '' )
		);
	}

	private function field_checkbox_multi( $meta_field, $meta_value = false ) {
		if ( empty( $meta_value ) ) {
			$meta_value = array();
		}
		foreach ( $meta_field['options'] as $key => $option_label ) {
			printf(
				'<div><label for="%s"><input %s id="%s" name="%s" type="checkbox" value="%s">%s</label></div>',
				esc_attr( $meta_field['id'] . '_' . $key ),
				in_array( $key, $meta_value, true ) ? 'checked' : '',
				esc_attr( $meta_field['id'] . '_' . $key ),
				esc_attr( $meta_field['id'] . '[]' ),
				esc_attr( $key ),
				esc_html( $option_label )
			);
		}
	}

	private function field_select( $meta_field, $meta_value = '' ) {
		printf( '<select name="%1$s" id="%1$s">', esc_attr( $meta_field['id'] ) );
		foreach ( $meta_field['options'] as $key => $option_label ) {
			printf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $key ),
				selected( $meta_value, $key, false ),
				esc_html( $option_label )
			);
		}
		echo '</select>';
	}

	private function field_textarea( $meta_field, $meta_value = '' ) {
		printf(
			'<textarea id="%s" name="%s" rows="5">%s</textarea>',
			esc_attr( $meta_field['id'] ),
			esc_attr( $meta_field['id'] ),
			esc_textarea( $meta_value )
		);
	}

	private function field_default( $meta_field, $meta_value = '' ) {
		printf(
			'<input id="%s" name="%s" type="%s" value="%s"',
			esc_attr( $meta_field['id'] ),
			esc_attr( $meta_field['id'] ),
			esc_attr( $meta_field['type'] ),
			esc_attr( $meta_value )
		);
		if ( ! empty( $meta_field['min'] ) ) {
			echo ' min="' . esc_attr( $meta_field['min'] );
		}
		if ( ! empty( $meta_field['max'] ) ) {
			echo ' max="' . esc_attr( $meta_field['max'] );
		}
		if ( ! empty( $meta_field['step'] ) ) {
			echo ' step="' . esc_attr( $meta_field['step'] );
		}
		echo '>';
	}

	public function save_fields( $term_id ) {
		// Check if nonce is valid.
		if (
			! isset( $_POST[ $this->tax_name . '_nonce' ] ) ||
			! wp_verify_nonce( sanitize_key( $_POST[ $this->tax_name . '_nonce' ] ), $this->tax_name . '_save' )
		) {
			return;
		}

		// Check if user has permissions.
		if ( ! current_user_can( 'manage_categories' ) ) {
			return;
		}

		foreach ( $this->meta_fields as $meta_field ) {
			if ( isset( $_POST[ $meta_field['id'] ] ) ) {
				$raw_value       = wp_unslash( $_POST[ $meta_field['id'] ] );
				$sanitized_value = '';
				switch ( $meta_field['type'] ) {
					case 'email':
						$sanitized_value = sanitize_email( $raw_value );
						break;
					case 'select':
						// Check valid option
						if ( array_key_exists( $raw_value, $meta_field['options'] ) ) {
							$sanitized_value = $raw_value;
						}
						break;
					case 'textarea':
						if ( isset( $meta_field['raw'] ) && true === $meta_field['raw'] ) {
							//Regex without changes...
							$sanitized_value = is_string( $raw_value ) ? wp_check_invalid_utf8( $raw_value ) : '';
						} else {
							$sanitized_value = sanitize_textarea_field( $raw_value );
						}
						break;
					case 'number':
						if ( isset( $meta_field['step'] ) && 1 === $meta_field['step'] ) {
							$sanitized_value = intval( $raw_value );
						} else {
							$sanitized_value = floatval( $raw_value );
						}
						break;
					case 'checkbox_multi':
						if ( is_array( $raw_value ) ) {
							$sanitized_value = array_map( 'sanitize_textarea_field', $raw_value );
						} else {
							$sanitized_value = array();
						}
						break;
					case 'checkbox':
						$sanitized_value = ( '1' === $raw_value ) ? '1' : '0';
						break;
					case 'text':
					case 'media':
						//Regex without changes...
						if ( isset( $meta_field['raw'] ) && true === $meta_field['raw'] ) {
							$sanitized_value = is_string( $raw_value ) ? wp_check_invalid_utf8( $raw_value ) : '';
						} else {
							$sanitized_value = sanitize_text_field( $raw_value );
						}
						break;
				}
				update_term_meta( $term_id, $meta_field['id'], wp_slash( $sanitized_value ) );
			} elseif ( 'checkbox' === $meta_field['type'] ) {
				update_term_meta( $term_id, $meta_field['id'], '0' );
			} elseif ( 'checkbox_multi' === $meta_field['type'] ) {
				update_term_meta( $term_id, $meta_field['id'], array() );
			}
		}
	}
}
