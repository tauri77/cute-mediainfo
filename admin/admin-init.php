<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

/**
 * Admin utils
 */
require CUTE_MEDIAINFO_DIR . '/admin/includes/utils.php';

/**
 * Setting class parent
 */
require CUTE_MEDIAINFO_DIR . '/admin/includes/class-cutemi-settings-api.php';

/**
 * Setting class
 */
require CUTE_MEDIAINFO_DIR . '/admin/class-cutemi-settings-credentials.php';
require CUTE_MEDIAINFO_DIR . '/admin/class-cutemi-settings-wizards.php';
require CUTE_MEDIAINFO_DIR . '/admin/class-cute-mediainfo-settings.php';

/**
 * Define taxonomies metas
 */
require CUTE_MEDIAINFO_DIR . '/admin/includes/class-cutemi-taxonomy-customs-metas.php';
require CUTE_MEDIAINFO_DIR . '/admin/includes/taxonomy-meta/class-cutemi-taxonomy-site-sources-metas.php';
require CUTE_MEDIAINFO_DIR . '/admin/includes/taxonomy-meta/class-cutemi-taxonomy-video-resolutions-metas.php';
require CUTE_MEDIAINFO_DIR . '/admin/includes/taxonomy-meta/class-cutemi-taxonomy-video-bitrates-metas.php';
require CUTE_MEDIAINFO_DIR . '/admin/includes/taxonomy-meta/class-cutemi-taxonomy-video-bitrate-modes-metas.php';
require CUTE_MEDIAINFO_DIR . '/admin/includes/taxonomy-meta/class-cutemi-taxonomy-audio-techs-metas.php';
require CUTE_MEDIAINFO_DIR . '/admin/includes/taxonomy-meta/class-cutemi-taxonomy-audio-channels-metas.php';
require CUTE_MEDIAINFO_DIR . '/admin/includes/taxonomy-meta/class-cutemi-taxonomy-audio-langs-metas.php';
require CUTE_MEDIAINFO_DIR . '/admin/includes/taxonomy-meta/class-cutemi-taxonomy-audio-bitrates-metas.php';
require CUTE_MEDIAINFO_DIR . '/admin/includes/taxonomy-meta/class-cutemi-taxonomy-audio-bitrate-modes-metas.php';
require CUTE_MEDIAINFO_DIR . '/admin/includes/taxonomy-meta/class-cutemi-taxonomy-video-formats-metas.php';
require CUTE_MEDIAINFO_DIR . '/admin/includes/taxonomy-meta/class-cutemi-taxonomy-video-techs-metas.php';
require CUTE_MEDIAINFO_DIR . '/admin/includes/taxonomy-meta/class-cutemi-taxonomy-text-langs-metas.php';
require CUTE_MEDIAINFO_DIR . '/admin/includes/taxonomy-meta/class-cutemi-taxonomy-text-formats-metas.php';
require CUTE_MEDIAINFO_DIR . '/admin/includes/taxonomy-meta/class-cutemi-taxonomy-text-type-metas.php';

/**
 * Deactivate plugin
 */
function cute_mediainfo_deactivate() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	// Unregister the post type, so the rules are no longer in memory.
	unregister_post_type( 'cute_mediainfo' );
	// Clear the permalinks to remove our post type rules from the database.
	flush_rewrite_rules();
}

/**
 * Activate plugin
 */
function cute_mediainfo_activate() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	add_option( 'cute_mediainfo_activate', 'yes' );
}

/**
 * If is first request after activate run installer and flush_rewrite_rules
 */
function cute_mediainfo_initialize() {

	if ( is_admin() && 'yes' === get_option( 'cute_mediainfo_activate' ) ) {
		delete_option( 'cute_mediainfo_activate' );

		include_once CUTE_MEDIAINFO_DIR . '/admin/install/class-cutemi-update.php';
		$installer = new CUTEMI_Update();
		$installer->update();

		flush_rewrite_rules();

		add_action( 'admin_notices', 'cutemi_setup_notice' );

		do_action( 'cutemi_activated' );
	}
}

add_action( 'admin_init', 'cute_mediainfo_initialize' );

/**
 * Notice for run wizard. For select the terms to use on each taxonomy.
 */
function cutemi_setup_notice() {
	if ( get_option( 'cutemi_setup_ready', false ) === '1' ) {
		return;
	}
	$url = menu_page_url( 'settings_cute_mediainfo', false ) . '#cutemi_setup';
	$txt = __( "Welcome to Cute MediaInfo! You're almost there, but we recommended this wizard to setup the plugin.", 'cute-mediainfo' );
	?>
	<div class="updated notice is-dismissible">
		<p><?php echo esc_html( $txt ); ?></p>
		<p><a href="<?php echo esc_url( $url ); ?>" class="button button-primary">
				<?php esc_html_e( 'Run wizard', 'cute-mediainfo' ); ?>
			</a>
			<a href="javascript:window.location.reload()" class="button">
				<?php esc_html_e( 'dismiss', 'cute-mediainfo' ); ?>
			</a>
		</p>
	</div>
	<?php
}


/**
 * Add setting link in list plugins page
 */
add_filter( 'plugin_action_links_' . plugin_basename( CUTE_MEDIAINFO_FILE ), 'cutemi_add_page_settings_link' );
function cutemi_add_page_settings_link( $links ) {
	$links[] = '<a href="' .
				menu_page_url( 'settings_cute_mediainfo', false ) .
				'">' . esc_html__( 'Settings', 'cute-mediainfo' ) . '</a>';

	return $links;
}
