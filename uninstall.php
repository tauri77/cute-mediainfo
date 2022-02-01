<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

if ( ! current_user_can( 'activate_plugins' ) ) {
	return;
}

function cutemi_u_delete_terms( $taxonomy ) {
	if ( ! taxonomy_exists( $taxonomy ) ) {
		include_once 'includes/cute-mediainfo/taxonomies.php';
		cutemi_register_taxonomies();
	}

	$terms = get_terms(
		$taxonomy,
		array(
			'fields'     => 'ids',
			'hide_empty' => false,
		)
	);

	foreach ( $terms as $value ) {
		wp_delete_term( $value, $taxonomy );
	}
}

if ( get_option( 'cutemi_purge_on_uninstall' ) === 'on' ) {
	delete_option( 'cute_mediainfo_activate' );
	delete_option( 'cutemi_css_version' );
	delete_option( 'cutemi_google_fonts' );
	delete_option( 'cutemi_layout_data' );
	delete_option( 'cutemi_styling' );
	delete_option( 'cutemi_layout_groups' );
	delete_option( 'cutemi_sources_internals' );
	delete_option( 'cutemi_profile_default' );
	delete_option( 'cutemi_installed_terms' );
	delete_option( 'cutemi_setup_ready' );
	delete_option( 'cutemi_icon_pack' );
	delete_option( 'cutemi_duration_format' );
	delete_option( 'cutemi_svg_head_colorized' );
	delete_option( 'cutemi_profiles' );
	delete_option( 'cutemi_link_size_1_part' );
	delete_option( 'cutemi_css_integrity' );
	delete_option( 'cutemi_relaxed_ownership' );
	delete_option( 'cutemi_force_inline_css' );
	delete_option( 'cutemi_css_compiled' );
	delete_option( 'cutemi_pending_css' );

	delete_option( 'cutemi_link_data_extractor' );
	delete_option( 'cutemi_input_force_1024' );

	delete_option( 'cutemi_purge_on_uninstall' );
	delete_option( 'cutemi_hide_offline' );


	$_posts = get_posts(
		array(
			'post_type'      => 'cute_mediainfo',
			'post_status'    => array( 'any', 'inherit', 'trash', 'auto-draft' ),
			'posts_per_page' => - 1,
			'fields'         => 'ids',
		)
	);

	if ( $_posts ) {
		foreach ( $_posts as $p_id ) {
			$childrens = get_posts(
				array(
					'post_type'      => 'attachment',
					'post_status'    => array( 'any', 'inherit', 'trash', 'auto-draft' ),
					'posts_per_page' => - 1,
					'post_parent'    => $p_id,
					'fields'         => 'ids',
				)
			);
			if ( is_array( $childrens ) && count( $childrens ) > 0 ) {
				foreach ( $childrens as $child_post_id ) {
					wp_delete_post( $child_post_id, false );
				}
			}
			wp_delete_post( $p_id, false );
		}
	}

	$_taxonomies = array(
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

	foreach ( $_taxonomies as $tax_slug ) {
		cutemi_u_delete_terms( $tax_slug );
		$children = get_option( "{$tax_slug}_children" );
		if ( is_array( $children ) && empty( $children ) ) {
			delete_option( "{$tax_slug}_children" );
		}
	}

	//delete dir
	include_once 'includes/class-cutemi-storage.php';
	CUTEMI_Storage::delete_storage_base();

}

wp_cache_flush();
