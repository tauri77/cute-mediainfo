<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

require_once CUTE_MEDIAINFO_DIR . '/includes/class-cutemi-custom-post-type.php';

class CUTEMI_Post_Type_Mediainfo extends CUTEMI_Custom_Post_Type {
	// Post type name
	private $name_ = 'cute_mediainfo';

	//for register the taxonomy to the post type
	private $taxonomies_ = array(
		'cutemi_site_source',
		'cutemi_file_format',
		'cutemi_video_tech',
		'cutemi_video_resolution',
		'cutemi_video_bitrate',
		'cutemi_video_bitrate_mode',
		'cutemi_audio_tech',
		'cutemi_audio_channels',
		'cutemi_audio_bitrate',
		'cutemi_audio_bitrate_mode',
		'cutemi_audio_langs',
		'cutemi_text_format',
		'cutemi_text_langs',
		'cutemi_text_type',
	);

	// required post_metas
	private $post_metas_required_ = array();
	// required taxonomies
	private $taxonomies_required_ = array();

	//The post_meta box that are list of item with each field
	private $list_of_fields_ = array( 'video-link', 'text-tracks', 'audio-tracks', 'video-tracks' );

	// The boxs for edit post
	private $post_metas_;

	public function __construct() {

		$this->name = $this->name_;

		parent::__construct();

		add_filter( 'get_the_excerpt', array( $this, 'get_the_excerpt' ), 10, 2 );
		add_filter( 'the_content', array( $this, 'the_content' ), 10, 2 );

		add_action( 'wp_ajax_extract_cute_mediainfo_data', array( $this, 'extract_link_data' ) );

		add_action( 'wp_ajax_cutemi_mediainfo_extract', array( $this, 'extract_on_mediainfo' ) );
	}

	public function init() {
		$this->init_();
		$this->taxonomies          = $this->taxonomies_;
		$this->post_metas_required = $this->post_metas_required_;
		$this->taxonomies_required = $this->taxonomies_required_;
		$this->list_of_fields      = $this->list_of_fields_;
		$this->post_metas          = $this->post_metas_;

		$this->taxonomies          = apply_filters( 'cutemi_post_type_taxonomies', $this->taxonomies );
		$this->taxonomies_required = apply_filters( 'cutemi_post_type_taxonomies_required', $this->taxonomies_required );
		$this->post_metas          = apply_filters( 'cutemi_post_type_post_metas', $this->post_metas );
		$this->post_metas_required = apply_filters( 'cutemi_post_type_post_metas_required', $this->post_metas_required );
		$this->list_of_fields      = apply_filters( 'cutemi_post_type_list_fields', $this->list_of_fields );

		add_shortcode( 'mediainfo', array( $this, 'shortcode' ) );

		parent::init();
	}

	public function init_() {

		$this->post_metas_ = array(
			'full-mediaInfo' => array(
				'title'  => __( 'Full MediaInfo', 'cute-mediainfo' ),
				'fields' => array(
					array(
						'id'          => 'mediainfo',
						'label'       => __( 'Full MediaInfo', 'cute-mediainfo' ),
						'type'        => 'textarea',
						'field_class' => 'cutemi-field-line',
					),
				),
			),
			'video-link'     => array(
				'title'  => __( 'Video Link', 'cute-mediainfo' ),
				'fields' => array(
					array(
						'id'            => 'original_link',
						'label'         => __( 'Link', 'cute-mediainfo' ),
						'as_array_item' => 0,
						'type'          => 'url',
					),
					array(
						'id'         => 'cutemi_site_source',
						'label'      => __( 'Link Source', 'cute-mediainfo' ),
						'tax_config' => array(
							'text'          => __( 'Link Source', 'cute-mediainfo' ),
							'meta_img'      => 'image_url',
							'meta_sub'      => 'url_template',
							'option_class'  => 'taxonomy-select-ui cutemi_site_source-option',
							'as_array_item' => 0,
							'show_label'    => true,
						),
					),
					array( //No use filename type, sanitize replace .part1.rar to .part1_.rar
						'id'            => 'link_title',
						'label'         => __( 'Name', 'cute-mediainfo' ),
						'as_array_item' => 0,
					),
					array(
						'id'            => 'external_id',
						'label'         => __( 'Extracted ID', 'cute-mediainfo' ),
						'as_array_item' => 0,
						'type'          => 'url_part',
					),
					array(
						'id'            => 'part_nro',
						'label'         => __( 'Part Nro', 'cute-mediainfo' ),
						'type'          => 'number',
						'default'       => '1',
						'as_array_item' => 0,
					),
					array(
						'id'            => 'part_size',
						'label'         => __( 'Part Size', 'cute-mediainfo' ),
						'type'          => 'number',
						'as_array_item' => 0,
					),
					array(
						'id'            => 'link_status',
						'label'         => __( 'Status', 'cute-mediainfo' ),
						'type'          => 'select',
						'as_array_item' => 0,
						'options'       => array(
							''  => 'Online',
							'1' => 'Offline',
						),
					),
				),
			),
			'video-general'  => array(
				'title'  => __( 'Video General', 'cute-mediainfo' ),
				'fields' => array(
					array(
						'id'         => 'cutemi_file_format',
						'label'      => __( 'Video Format', 'cute-mediainfo' ),
						'tax_config' => array(
							'text'             => __( 'Format', 'cute-mediainfo' ),
							'meta_img'         => 'image_url',
							'meta_sub'         => '',
							'child_expand_img' => true,
							'option_class'     => 'taxonomy-select-ui cutemi_file_format-option',
							'show_label'       => true,
							'field_class'      => 'cutemi-field-line',
						),
					),
					array(
						'id'          => 'size',
						'label'       => __( 'Size', 'cute-mediainfo' ),
						'type'        => 'number',
						'field_class' => 'cutemi-field-line',
					),
					array(
						'id'          => 'duration',
						'label'       => __( 'Duration (seconds)', 'cute-mediainfo' ),
						'type'        => 'number',
						'field_class' => 'cutemi-field-line',
					),
					array(
						'id'          => 'video_date',
						'label'       => __( 'Date', 'cute-mediainfo' ),
						'type'        => 'date',
						'field_class' => 'cutemi-field-line',
					),
					array(
						'id'          => 'desc',
						'label'       => __( 'Description', 'cute-mediainfo' ),
						'type'        => 'textarea',
						'field_class' => 'cutemi-field-line',
					),
				),
			),
			'video-tracks'   => array(
				'title'  => __( 'Video Tracks', 'cute-mediainfo' ),
				'fields' => array(
					array(
						'id'         => 'cutemi_video_resolution',
						'label'      => __( 'Video Resolution', 'cute-mediainfo' ),
						'tax_config' => array(
							'text'             => __( 'Resolution', 'cute-mediainfo' ),
							'meta_img'         => 'image_url',
							'meta_sub'         => '_description',
							'child_expand_img' => true,
							'option_class'     => 'taxonomy-select-ui cutemi_video_resolution-option',
						),
					),
					array(
						'id'         => 'cutemi_video_tech',
						'label'      => __( 'Video Tech', 'cute-mediainfo' ),
						'tax_config' => array(
							'text'             => __( 'Video Tech', 'cute-mediainfo' ),
							'meta_img'         => 'image_url',
							'meta_sub'         => '_description',
							'child_expand_img' => true,
							'option_class'     => 'taxonomy-select-ui cutemi_video_tech-option',
						),
					),
					array(
						'id'         => 'cutemi_video_bitrate',
						'label'      => __( 'Video Bitrate', 'cute-mediainfo' ),
						'tax_config' => array(
							'text'             => __( 'Video Bitrate', 'cute-mediainfo' ),
							'meta_img'         => 'image_url',
							'meta_sub'         => '_description',
							'child_expand_img' => true,
							'option_class'     => 'taxonomy-select-ui cutemi_video_bitrate-option',
							'as_array_item'    => 0,
						),
					),
					array(
						'id'         => 'cutemi_video_bitrate_mode',
						'label'      => __( 'Video Bitrate Mode', 'cute-mediainfo' ),
						'tax_config' => array(
							'text'             => __( 'Video Bitrate Mode', 'cute-mediainfo' ),
							'meta_img'         => 'image_url',
							'meta_sub'         => '_description',
							'child_expand_img' => true,
							'option_class'     => 'taxonomy-select-ui cutemi_video_bitrate_mode-option',
							'as_array_item'    => 0,
						),
					),
				),
			),
			'audio-tracks'   => array(
				'title'  => __( 'Audio Tracks', 'cute-mediainfo' ),
				'fields' => array(
					array(
						'id'         => 'cutemi_audio_langs',
						'label'      => __( 'Audio Language', 'cute-mediainfo' ),
						'tax_config' => array(
							'text'             => __( 'Audio Languages', 'cute-mediainfo' ),
							'meta_img'         => 'image_url',
							'meta_sub'         => 'native', //'_description_parent',
							'child_expand_img' => true,
							'option_class'     => 'taxonomy-select-ui cutemi_audio_langs-option',
							'as_array_item'    => 0,
						),
					),
					array(
						'id'         => 'cutemi_audio_tech',
						'label'      => __( 'Audio Tech', 'cute-mediainfo' ),
						'tax_config' => array(
							'text'             => __( 'Audio Tech', 'cute-mediainfo' ),
							'meta_img'         => 'image_url',
							'meta_sub'         => '_description', //'_description_parent',
							'child_expand_img' => true,
							'option_class'     => 'taxonomy-select-ui cutemi_audio_tech-option',
							'as_array_item'    => 0,
						),
					),
					array(
						'id'         => 'cutemi_audio_channels',
						'label'      => __( 'Audio Channel', 'cute-mediainfo' ),
						'tax_config' => array(
							'text'             => __( 'Audio Channels', 'cute-mediainfo' ),
							'meta_img'         => 'image_url',
							'meta_sub'         => '_description', //'_description_parent',
							'child_expand_img' => true,
							'option_class'     => 'taxonomy-select-ui cutemi_audio_channels-option',
							'as_array_item'    => 0,
						),
					),
					array(
						'id'         => 'cutemi_audio_bitrate',
						'label'      => __( 'Audio Bitrate', 'cute-mediainfo' ),
						'tax_config' => array(
							'text'             => __( 'Audio Bitrate', 'cute-mediainfo' ),
							'meta_img'         => 'image_url',
							'meta_sub'         => '_description',
							'child_expand_img' => true,
							'option_class'     => 'taxonomy-select-ui cutemi_audio_bitrate-option',
							'as_array_item'    => 0,
						),
					),
					array(
						'id'         => 'cutemi_audio_bitrate_mode',
						'label'      => __( 'Audio Bitrate Mode', 'cute-mediainfo' ),
						'tax_config' => array(
							'text'             => __( 'Audio Bitrate Mode', 'cute-mediainfo' ),
							'meta_img'         => 'image_url',
							'meta_sub'         => '_description',
							'child_expand_img' => true,
							'option_class'     => 'taxonomy-select-ui cutemi_audio_bitrate_mode-option',
							'as_array_item'    => 0,
						),
					),
				),
			),
			'text-tracks'    => array(
				'title'  => __( 'Text Tracks', 'cute-mediainfo' ),
				'fields' => array(
					array(
						'id'         => 'cutemi_text_langs',
						'label'      => __( 'Text Language', 'cute-mediainfo' ),
						'tax_config' => array(
							'text'             => __( 'Text Languages', 'cute-mediainfo' ),
							'meta_img'         => 'image_url',
							'meta_sub'         => 'native', //'_description_parent',
							'child_expand_img' => true,
							'option_class'     => 'taxonomy-select-ui cutemi_audio_langs-option',
							'as_array_item'    => 0,
						),
					),
					array(
						'id'         => 'cutemi_text_format',
						'label'      => __( 'Text Format', 'cute-mediainfo' ),
						'tax_config' => array(
							'text'             => __( 'Text Format', 'cute-mediainfo' ),
							'meta_img'         => 'image_url',
							'meta_sub'         => '_description', //'_description_parent',
							'child_expand_img' => true,
							'option_class'     => 'taxonomy-select-ui cutemi_text_format-option',
							'as_array_item'    => 0,
						),
					),
					array(
						'id'         => 'cutemi_text_type',
						'label'      => __( 'Text Type', 'cute-mediainfo' ),
						'tax_config' => array(
							'text'          => __( 'Text Type', 'cute-mediainfo' ),
							'meta_img'      => 'image_url',
							'meta_sub'      => '_description',
							'option_class'  => 'taxonomy-select-ui cute_mediainfo_audio_type-option',
							'as_array_item' => 0,
						),
					),
				),
			),
		);
	}

	public function shortcode( $atts ) {
		$config = shortcode_atts(
			array(
				'profile' => 'full',
				'id'      => 0,
			),
			$atts
		);

		$post = get_post( $config['id'] );
		if ( is_wp_error( $post ) || empty( $post ) ) {
			return '';
		}

		return cutemi_get_post_content( $post, $config['profile'] );
	}

	public function get_the_excerpt( $post_excerpt, $post ) {
		if ( $post->post_type === $this->name ) {
			$post_excerpt = cutemi_get_post_content( $post, 'excerpt' );
		}

		return $post_excerpt;
	}

	public function the_content( $content ) {
		global $post;
		if ( ! is_object( $post ) ) {
			return $content;
		}
		if ( $post->post_type !== $this->name ) {
			return $content;
		}

		return cutemi_get_post_content( $post, 'full' );
	}

	public function extract_link_data() {
		if (
			! isset( $_POST['nonce'] ) ||
			! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'cutemi_extract_cute_mediainfo' )
		) {
			die();
		}

		self::check_ajax_can_edit();

		if ( isset( $_POST['sitesource'], $_POST['sitesource'], $_POST['sitesource'], $_POST['sitesource'] ) ) {
			$data = array(
				'sitesource'    => sanitize_title( wp_unslash( $_POST['sitesource'] ) ),
				'external_id'   => wp_check_invalid_utf8( wp_unslash( $_POST['external_id'] ) ),
				'original_link' => wp_check_invalid_utf8( wp_unslash( $_POST['link'] ) ),
				'title'         => sanitize_text_field( wp_unslash( $_POST['title'] ) ),
			);
		} else {
			die();
		}

		do_action( 'before_load_link_data' );
		include_once CUTE_MEDIAINFO_DIR . '/includes/links/link-data-extractor.php';
		do_action( 'after_load_link_data' );

		self::ajax_response( cutemi_extract_cute_mediainfo_data( $data ) );
	}

	public static function check_ajax_can_edit() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json( array( 'error' => '403' ) );
		}
	}

	public static function ajax_response( $response ) {
		$response['nonce'] = wp_create_nonce( 'cutemi_extract_cute_mediainfo' );
		wp_send_json( $response );
	}

	public function extract_on_mediainfo() {
		if (
			! isset( $_POST['nonce'] ) ||
			! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'cutemi_extract_cute_mediainfo' )
		) {
			die();
		}
		self::check_ajax_can_edit();

		include_once CUTE_MEDIAINFO_DIR . '/includes/mediainfo/class-cutemi-mediainfo-data.php';
		$mid = new CUTEMI_Mediainfo_Data();
		// phpcs:ignore WordPress.Security.NonceVerification
		self::ajax_response( $mid->get_data( sanitize_textarea_field( wp_unslash( $_POST['mediainfo'] ) ) ) );
	}

	public function taxs_for_columns( $taxonomies ) {
		//Las tax que se muestran en columnas al listar los post(videos).
		$taxonomies[] = 'cutemi_site_source';

		return apply_filters( 'cutemi_taxs_for_columns', $taxonomies );
	}

	public function set_custom_columns( $columns ) {
		unset( $columns['author'] );

		$new_columns = array();
		$position    = count( $columns ) - 1;
		foreach ( $columns as $id => $label ) {
			$new_columns[ $id ] = $label;
			$position --;
			if ( 0 === $position ) {
				$new_columns['cutemi_shortcode'] = __( 'Shortcode', 'cute-mediainfo' );
			}
		}

		return apply_filters( 'cutemi_set_custom_columns', $new_columns );
	}

	public function custom_column( $column, $post_id ) {
		do_action( 'cutemi_custom_column', $column, $post_id );
		if ( 'cutemi_shortcode' === $column ) {
			echo '[mediainfo id=' . intval( $post_id ) . ' profile=full]';
		}
	}

	public function add_admin_scripts( $hook ) {
		global $post;
		if ( 'post-new.php' === $hook || 'post.php' === $hook ) {
			if ( $this->name === $post->post_type ) {
				$plugin_dir_url = plugin_dir_url( CUTE_MEDIAINFO_FILE );
				wp_enqueue_style(
					'cutemi-jquery-ui',
					$plugin_dir_url . 'admin/assets/jquery-ui/jquery-ui.css',
					false,
					1.0,
					false
				);

				wp_enqueue_script( 'jquery-ui-core' );
				wp_enqueue_script( 'jquery-ui-widget' );
				wp_enqueue_script( 'jquery-ui-mouse' );
				wp_enqueue_script( 'jquery-ui-accordion' );
				wp_enqueue_script( 'jquery-ui-autocomplete' );
				wp_enqueue_script( 'jquery-ui-slider' );
				wp_enqueue_script( 'jquery-ui-selectmenu' );
				wp_enqueue_script( 'jquery-ui-datepicker' );

				wp_enqueue_script(
					'cutemi-post-type-cute-mediainfo.js',
					$plugin_dir_url . 'admin/assets/js/post-type-cute-mediainfo.js',
					array( 'jquery' ),
					'1.0.2',
					true
				);
				/** @noinspection SqlResolve */
				/** @noinspection SqlNoDataSourceInspection */
				wp_localize_script(
					'cutemi-post-type-cute-mediainfo.js',
					'cutemiData',
					array(
						'ajaxUrl'                   => admin_url( 'admin-ajax.php' ),
						'choose_a_source'           => __( 'Choose a Source', 'cute-mediainfo' ),
						'select_the_link_source'    => __( 'Select the link source', 'cute-mediainfo' ),
						'select_local_video'        => __( 'Select local video', 'cute-mediainfo' ),
						'load_data_from_mediainfo'  => __( 'Autocomplete fields from mediainfo', 'cute-mediainfo' ),
						'check_link_required'       => __( 'Required an know Source and Extracted ID', 'cute-mediainfo' ),
						'check_link_support_err'    => __( 'This link does not support checking', 'cute-mediainfo' ),
						'select_from_media_library' => __( 'Select video from media library', 'cute-mediainfo' ),
						'use_this_video'            => __( 'Use this video', 'cute-media' ),
						'link_data_extractor'       => get_option( 'cutemi_link_data_extractor', 1 ),
						'mediainfo_lib'             => get_option( 'cutemi_mediainfo_lib', 'official' ),
						'nonce_ajax'                => wp_create_nonce( 'cutemi_extract_cute_mediainfo' ),
						'input_force_1024'          => get_option( 'cutemi_input_force_1024', 1 ),
						'plugin_dir_url'            => $plugin_dir_url,
					)
				);

				wp_register_style(
					'post-type-cute-mediainfo.css',
					$plugin_dir_url . 'admin/assets/css/post-type-cute-mediainfo.css',
					array(),
					'1.0.2',
					'all'
				);
				wp_enqueue_style( 'post-type-cute-mediainfo.css' );

				add_thickbox();

				do_action( 'cutemi_admin_post_type_enqueue' );
			}
		}
	}


	public function register_post_type() {
		$labels = array(
			'name'                  => _x( 'MediaInfo', 'MediaInfo General Name', 'cute-mediainfo' ),
			'singular_name'         => _x( 'MediaInfo', 'MediaInfo Singular Name', 'cute-mediainfo' ),
			'menu_name'             => __( 'MediaInfo', 'cute-mediainfo' ),
			'name_admin_bar'        => __( 'MediaInfo', 'cute-mediainfo' ),
			'archives'              => __( 'MediaInfo Archives', 'cute-mediainfo' ),
			'attributes'            => __( 'MediaInfo Attributes', 'cute-mediainfo' ),
			'parent_item_colon'     => __( 'Parent MediaInfo:', 'cute-mediainfo' ),
			'all_items'             => __( 'All MediaInfo Items', 'cute-mediainfo' ),
			'add_new_item'          => __( 'Add New MediaInfo', 'cute-mediainfo' ),
			'add_new'               => __( 'Add New MediaInfo', 'cute-mediainfo' ),
			'new_item'              => __( 'New MediaInfo', 'cute-mediainfo' ),
			'edit_item'             => __( 'Edit MediaInfo', 'cute-mediainfo' ),
			'update_item'           => __( 'Update MediaInfo', 'cute-mediainfo' ),
			'view_item'             => __( 'View MediaInfo', 'cute-mediainfo' ),
			'view_items'            => __( 'View MediaInfo Items', 'cute-mediainfo' ),
			'search_items'          => __( 'Search MediaInfo Items', 'cute-mediainfo' ),
			'not_found'             => __( 'Not found', 'cute-mediainfo' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'cute-mediainfo' ),
			'featured_image'        => __( 'Featured Image', 'cute-mediainfo' ),
			'set_featured_image'    => __( 'Set featured image', 'cute-mediainfo' ),
			'remove_featured_image' => __( 'Remove featured image', 'cute-mediainfo' ),
			'use_featured_image'    => __( 'Use as featured image', 'cute-mediainfo' ),
			'insert_into_item'      => __( 'Add into MediaInfo', 'cute-mediainfo' ),
			'uploaded_to_this_item' => __( 'Uploaded to this MediaInfo', 'cute-mediainfo' ),
			'items_list'            => __( 'Items list', 'cute-mediainfo' ),
			'items_list_navigation' => __( 'Items list navigation', 'cute-mediainfo' ),
			'filter_items_list'     => __( 'Filter items list', 'cute-mediainfo' ),
		);

		$rewrite = array(
			'slug'       => 'mediainfo',
			'with_front' => false,
			'pages'      => false,
			'feeds'      => false,
		);

		$publico = true;

		$args = array(
			'label'                 => __( 'MediaInfo', 'cute-mediainfo' ),
			'description'           => __( 'MediaInfo Description', 'cute-mediainfo' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'author' ),
			'hierarchical'          => false,
			'public'                => $publico,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 5,
			'menu_icon'             => 'dashicons-format-video',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => false,
			'can_export'            => true,
			'has_archive'           => false,
			'exclude_from_search'   => true,
			'publicly_queryable'    => $publico,
			'rewrite'               => $rewrite,
			'capability_type'       => 'post',
			'show_in_rest'          => true,
			'rest_base'             => $this->name,
			'rest_controller_class' => 'WP_REST_Posts_Controller',
		);

		register_post_type( $this->name, $args );

		add_rewrite_rule(
			'mediainfo/([^/]*)/([^/]+)$',
			'index.php?post_type=' . $this->name . '&name=$matches[1]',
			'top'
		);
		add_rewrite_rule( '^mediainfo/?$', 'index.php?post_type=' . $this->name, 'top' );

		add_post_type_support( $this->name, 'thumbnail' );
	}

	public function show_permalinks( $post_link, $post ) {
		$site_source = wp_get_object_terms(
			$post->ID,
			'cutemi_site_source',
			array(
				'orderby' => 'term_id',
				'order'   => 'ASC',
			)
		);
		if ( ! is_wp_error( $site_source ) ) {
			if ( isset( $site_source[0] ) ) {
				if ( ! empty( $post->post_name ) ) {
					$site_source = $site_source[0];
					$uri         = "mediainfo/{$post->post_name}/{$site_source->slug}";
					$post_link   = home_url( $uri );
				}
			}
		}

		return $post_link;
	}

}

$GLOBALS['cutemi_post_type'] = new CUTEMI_Post_Type_Mediainfo();
