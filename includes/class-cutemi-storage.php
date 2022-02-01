<?php

class CUTEMI_Storage {

	/**
	 * @return string|WP_Error
	 */
	public static function create_storage_path() {
		global $wp_filesystem;

		$dir     = self::get_storage_base( true );
		$context = dirname( untrailingslashit( $dir ) );

		$relaxed = ( 'on' === get_option( 'cutemi_relaxed_ownership', 'off' ) );

		if ( $relaxed ) {
			if ( ! defined( 'FS_METHOD' ) ) {
				define( 'FS_METHOD', 'direct' );
			}
		}

		if ( isset( $GLOBALS['cutemi_creds'] ) ) {
			$fs = WP_Filesystem( $GLOBALS['cutemi_creds'], $context, $relaxed );
		} else {
			$fs = WP_Filesystem( false, $context, $relaxed );
		}
		if ( ! $fs ) {
			if ( is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
				add_settings_error(
					'general',
					'css_write_error',
					$wp_filesystem->errors->get_error_message()
				);
			}
			$access_type = get_filesystem_method(
				array(),
				$context,
				$relaxed
			);

			if ( 'direct' !== $access_type ) {
				//require ftp credentials

				$msg = esc_html__( 'WP_Filesystem require credentials.', 'cute-mediainfo' ) .
						' <a href="' . esc_url( self::get_setting_credentials() ) . '">' .
						esc_html__( 'Configure Now', 'cute-mediainfo' ) .
						'</a>.';

				return new WP_Error(
					'cutemi_fs_error',
					$msg
				);
			}

			return new WP_Error(
				'fs_error',
				esc_html__( 'WP_Filesystem unknown error.', 'cute-mediainfo' )
			);
		}

		if ( ! $wp_filesystem->exists( $dir ) ) {
			if ( false === $wp_filesystem->mkdir( $dir ) ) {
				return new WP_Error(
					'fs_error',
					esc_html__( 'Could not create Cute Mediainfo directory.', 'cute-mediainfo' )
				);
			}
		}
		$dir .= '/css';
		if ( ! $wp_filesystem->exists( $dir ) ) {
			if ( false === $wp_filesystem->mkdir( $dir ) ) {
				return new WP_Error(
					'fs_error',
					esc_html__( 'Could not create Cute Mediainfo CSS directory.', 'cute-mediainfo' )
				);
			}
		}

		return $dir;
	}

	public static function delete_storage_base( $refresh_cache = false ) {
		global $wp_filesystem;

		$dir = self::get_storage_base( true );

		WP_Filesystem( false, untrailingslashit( $dir ), true );

		return $wp_filesystem->delete( $dir, true );
	}

	public static function get_storage_base( $refresh_cache = false ) {
		$upload_dir = wp_upload_dir( null, true, $refresh_cache );

		return $upload_dir['basedir'] . '/cute-mediainfo/';
	}

	public static function get_storage_path() {
		return self::get_storage_base() . '/css/styles.css';
	}

	public static function get_setting_credentials( $action = 'credentials' ) {
		//Maybe not initialize settings, dont use menu_page_url!
		$url = admin_url( 'admin.php?page=settings_cute_mediainfo' );
		$url = add_query_arg( array( 'cutemi_creds_action' => $action ), $url );
		$url = add_query_arg( array( 'cutemi_nonce' => wp_create_nonce( 'cutemi_credentials' ) ), $url );
		$url = add_query_arg( array( 'version' => wp_rand( 0, 99999999 ) ), $url );

		return $url;
	}

}
