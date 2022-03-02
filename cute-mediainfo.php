<?php
/*
 * Plugin Name: Cute MediaInfo
 * Plugin URI:  https://galetto.info/cutemi-file-info-and-links/
 * Description: Mediainfo for humans. Cute way to display video file information.
 * Version:     1.0.3
 * Author:      Mauricio Galetto
 * Author URI:  https://galetto.info
 * Domain Path: /languages
 * Text Domain: cute-mediainfo
 * License:     GPL v3
 * Requires at least: 4.6
 * Requires PHP: 5.6
*/
/**
 * @author    Mauricio Galetto
 * @copyright Mauricio Galetto, 2021-22, All Rights Reserved
 * This code is released under the GPL licence version 3 or later, available here
 * https://www.gnu.org/licenses/gpl.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

if ( ! defined( 'CUTE_MEDIAINFO_FILE' ) ) {
	define( 'CUTE_MEDIAINFO_FILE', __FILE__ );
}

if ( ! defined( 'CUTE_MEDIAINFO_DIR' ) ) {
	define( 'CUTE_MEDIAINFO_DIR', dirname( __FILE__ ) );
}

add_action( 'plugins_loaded', 'cutemi_load_textdomain' );
function cutemi_load_textdomain() {
	load_plugin_textdomain(
		'cute-mediainfo',
		false,
		'cute-mediainfo/languages'
	);
}

/**
 * Utils and functions
 */
require_once CUTE_MEDIAINFO_DIR . '/includes/utils.php';
require_once CUTE_MEDIAINFO_DIR . '/includes/functions.php';
require_once CUTE_MEDIAINFO_DIR . '/includes/class-cutemi-storage.php';

/**
 * Functions to get profile settings and icon packs
 */
require_once CUTE_MEDIAINFO_DIR . '/includes/functions-settings.php';

/**
 * Change default customizer for profile styles/data. Ex: Summary Profile -> remove links block
 */
require_once CUTE_MEDIAINFO_DIR . '/includes/class-cutemi-profile-summary.php';

/**
 * Cute MediaInfo Editor Block
 */
require_once CUTE_MEDIAINFO_DIR . '/includes/block/block.php';

if ( is_admin() ) {
	/**
	 * Admin includes
	 */
	require_once CUTE_MEDIAINFO_DIR . '/admin/admin-init.php';
	require_once CUTE_MEDIAINFO_DIR . '/includes/class-cutemi-walker-taxonomy-single-term.php';
	/**
	 * Plugin deactivate and activate Hook, defined on admin-init.php
	 */
	register_deactivation_hook( __FILE__, 'cute_mediainfo_deactivate' );
	register_activation_hook( __FILE__, 'cute_mediainfo_activate' );
}

/**
 * Class for get options for a specific table profile (styles/blocks/data)
 */
require_once CUTE_MEDIAINFO_DIR . '/includes/class-cutemi-table-template.php';

/**
 * Manage the styles
 */
require_once CUTE_MEDIAINFO_DIR . '/includes/class-cutemi-template-styles.php';

/**
 * Register taxonomies
 */
require_once CUTE_MEDIAINFO_DIR . '/includes/cute-mediainfo/taxonomies.php';

/**
 * Define custom post type "cute_mediainfo"
 */
require_once CUTE_MEDIAINFO_DIR . '/includes/cute-mediainfo/class-cutemi-post-type-mediainfo.php';

/**
 * Functions used for render mediainfo
 */
require_once CUTE_MEDIAINFO_DIR . '/templates/templates-load.php';

/**
 * Class and implements of icon packs
 */
require_once CUTE_MEDIAINFO_DIR . '/icon-packs/class-cutemi-base-icon-pack.php';
require_once CUTE_MEDIAINFO_DIR . '/icon-packs/class-cutemi-default-icon-pack.php';
require_once CUTE_MEDIAINFO_DIR . '/icon-packs/class-cutemi-black-icon-pack.php';

/**
 * Customizer load
 */
add_action( 'customize_register', 'cute_mediainfo_customize_register' );
function cute_mediainfo_customize_register( $wp_customize ) {
	/**
	 * Add var for profile force on customizer preview
	 *
	 * @param $vars
	 *
	 * @return mixed
	 */
	function cutemi_add_query_vars_customizer( $vars ) {
		$vars[] = 'cutemi_profile_force';
		return $vars;
	}
	add_filter( 'query_vars', 'cutemi_add_query_vars_customizer' );
	/**
	 * Controls
	 */
	require_once CUTE_MEDIAINFO_DIR . '/admin/customizer/class-cutemi-font-dropdown-customize-control.php';
	require_once CUTE_MEDIAINFO_DIR . '/admin/customizer/class-cutemi-gfont-dropdown-customize-control.php';
	require_once CUTE_MEDIAINFO_DIR . '/admin/customizer/class-cutemi-alpha-color-control.php';
	/**
	 * Configuration class and instantiate each profile (Panels, sections and settings for each profile)
	 */
	require_once CUTE_MEDIAINFO_DIR . '/admin/customizer/class-cutemi-customize-settings.php';
	/**
	 * Fake mediainfo post for preview when customizing
	 */
	require_once CUTE_MEDIAINFO_DIR . '/admin/customizer/class-cutemi-fake-pages-preview.php';
}

/**
 * Enqueue svg-inject js if enabled in config
 */
function cutemi_enqueue_scripts_do() {
	if ( get_option( 'cutemi_svg_head_colorized', '1' ) === '2' ) {
		#see https://github.com/iconfu/svg-inject#how-does-svginject-prevent-unstyled-image-flash
		wp_enqueue_script(
			'svg-inject.min.js',
			plugin_dir_url( CUTE_MEDIAINFO_FILE ) . 'assets/js/svg-inject.min.js',
			array( 'jquery' ),
			'1.0.3',
			false
		);
	}
}
add_action( 'wp_enqueue_scripts', 'cutemi_enqueue_scripts_do' );
