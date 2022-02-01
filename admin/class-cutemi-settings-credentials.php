<?php

class CUTEMI_Settings_Credentials {

	private static $unique_instance;

	private function __construct() {
		add_filter( 'cutemi_settings_pre_print', array( $this, 'pre_process_setup' ) );
	}

	public static function instance() {
		if ( ! self::$unique_instance instanceof self ) {
			self::$unique_instance = new self();
		}

		return self::$unique_instance;
	}

	/**
	 * Stop cutemi settings tabs
	 *
	 * @param $print_tabs
	 *
	 * @return bool
	 */
	public function pre_process_setup( $print_tabs ) {
		$invalid_nonce = false;
		$valid_actions = array(
			'credentials',
			'credentials_flex',
			'credentials_flex_off',
			'credentials_css_inline',
			'credentials_css_inline_off',
		);

		if (
			isset( $_GET['cutemi_creds_action'] ) &&
			in_array( $_GET['cutemi_creds_action'], $valid_actions, true )
		) {
			$print_tabs = false; //dont show cutemi settings tabs
			echo '<div class="wrap">';
			// if credentials action cutemi nonce is on url of request_filesystem_credentials
			if ( 'credentials' === $_REQUEST['cutemi_creds_action'] ) {
				if (
					! isset( $_GET['cutemi_nonce'] ) ||
					! wp_verify_nonce( sanitize_key( $_GET['cutemi_nonce'] ), 'cutemi_credentials' )
				) {
					$invalid_nonce = true;
				} else {
					$this->print_credentials();
				}
			} else { //Enable/disable cutemi_relaxed_ownership, nonce on POST
				if (
					isset( $_POST['cutemi_setup_nonce'] ) &&
					wp_verify_nonce( sanitize_key( $_POST['cutemi_setup_nonce'] ), 'cutemi_setup' )
				) {
					if ( 'credentials_flex' === $_REQUEST['cutemi_creds_action'] ) {
						update_option( 'cutemi_relaxed_ownership', 'on', false );
						$this->print_credentials_flex();
					} elseif ( 'credentials_flex_off' === $_REQUEST['cutemi_creds_action'] ) {
						update_option( 'cutemi_relaxed_ownership', 'off', false );
						$this->print_credentials_flex_off();
					} elseif ( 'credentials_css_inline' === $_REQUEST['cutemi_creds_action'] ) {
						update_option( 'cutemi_force_inline_css', 'on' );
						$this->print_force_inline_css();
					} elseif ( 'credentials_css_inline_off' === $_REQUEST['cutemi_creds_action'] ) {
						update_option( 'cutemi_force_inline_css', 'off' );
						$this->print_force_inline_css_off();
					}
				} else {
					$invalid_nonce = true;
				}
			}
			if ( $invalid_nonce ) {
				echo '<div class="cutemi-setup-steps-desc"><p>';
				echo esc_html__( 'Invalid nonce. Please try again.', 'cute-mediainfo' );
				echo '</p></div>';
			}
			echo '</div>';
		}

		return $print_tabs;
	}

	private function print_credentials() {

		$dir     = CUTEMI_Storage::get_storage_base( true );
		$context = dirname( untrailingslashit( $dir ) );
		$url     = CUTEMI_Storage::get_setting_credentials();

		//Check if we have credentials
		$GLOBALS['cutemi_creds'] = request_filesystem_credentials(
			$url,
			'',
			false,
			$context
		);

		$relaxed = ( 'on' === get_option( 'cutemi_relaxed_ownership', 'off' ) );

		// With credentials?
		if ( false !== $GLOBALS['cutemi_creds'] ) {
			// Try use credentials
			// Disable temporally relaxed ownership
			if ( $relaxed ) {
				update_option( 'cutemi_relaxed_ownership', 'off' );
			}
			echo '<div class="cutemi-setup-steps-desc"><br /><p>';
			echo esc_html__( 'Trying to save with credentials...', 'cute-mediainfo' );
			echo '<br />';
			echo '</p></div>';

			$this->try_write_css();

			if ( $relaxed ) {
				update_option( 'cutemi_relaxed_ownership', 'on' );
			}
		}
		// Without credentials, printed form
		if ( false === $GLOBALS['cutemi_creds'] && ! $relaxed ) {
			$this->print_after_credentials_configure( $dir );

			return true;
		}

		return false;
	}

	private function print_credentials_flex() {
		echo '<div class="cutemi-setup-steps-desc"><p><br />';
		echo esc_html__( 'Relaxed file ownership enabled.', 'cute-mediainfo' );
		echo '</p></div>';

		$this->print_open_form( 'credentials_flex_off' );
		submit_button(
			__( 'Disable relaxed file ownership', 'cute-mediainfo' ),
			'small',
			'submit',
			true,
			array( 'style' => 'color: red;' )
		);
		echo '</form>';

		$this->try_write_css();
	}

	private function print_force_inline_css() {

		echo '<div class="cutemi-setup-steps-desc"><p><br />';
		echo esc_html__( 'Force inline style is enabled.', 'cute-mediainfo' );
		echo '</p></div>';

		CUTEMI_Template_Styles::instance()->save_compile_all_css();

		$this->print_open_form( 'credentials_css_inline_off' );
		submit_button(
			__( 'Disable force inline style', 'cute-mediainfo' ),
			'small',
			'submit',
			true,
			array( 'style' => 'color: red;' )
		);
		echo '</form>';
	}

	private function print_credentials_flex_off() {
		echo '<div class="cutemi-setup-steps-desc"><p><br />';
		echo esc_html__( 'Relaxed file ownership disabled.', 'cute-mediainfo' );
		echo '</p></div>';
		$this->print_credentials();
	}

	private function print_force_inline_css_off() {
		echo '<div class="cutemi-setup-steps-desc"><p><br />';
		echo esc_html__( 'Force inline style is disabled.', 'cute-mediainfo' );
		echo '</p></div>';
		$this->print_credentials();
	}

	private function print_open_form( $setup_action, $print_nonce = true ) {
		$url = menu_page_url( 'settings_cute_mediainfo', false );
		$url = add_query_arg( array( 'cutemi_creds_action' => $setup_action ), $url );
		printf(
			'<form class="cutemi-setup-form" action="%s#cutemi_setup" method="POST">',
			esc_url( $url )
		);
		if ( $print_nonce ) {
			wp_nonce_field( 'cutemi_setup', 'cutemi_setup_nonce' );
		}
	}

	private function try_write_css() {

		global $wp_settings_errors;
		//Clear errors before try save
		$wp_settings_errors = array();// phpcs:ignore

		$status = CUTEMI_Template_Styles::instance()->save_compile_all_css();
		if ( $status ) {
			echo esc_html__( 'Successfully processed.', 'cute-mediainfo' );
		} else {
			echo esc_html__( 'Could not be processed correctly.', 'cute-mediainfo' );
		}
		echo '<br />';
		echo '<p>';
		settings_errors();
		echo '</p>';

		return $status;
	}

	private function print_after_credentials_configure( $context ) {
		/* translators: Before a url to visit */
		echo esc_html__( 'To configure it persistently check:', 'cute-mediainfo' );
		printf(
			' <a href="%s">%s</a>',
			'https://wordpress.org/support/article/editing-wp-config-php/#wordpress-upgrade-constants',
			'https://wordpress.org/support/article/editing-wp-config-php/#wordpress-upgrade-constants'
		);
		//Check if relaxed file ownership work
		$access_type = get_filesystem_method(
			array(),
			$context,
			true
		);
		echo '<br />';
		echo '<br />';
		echo esc_html__( 'Alternative:', 'cute-mediainfo' );
		echo '<br />';
		echo '<br />';
		if ( 'direct' === $access_type ) {
			$this->print_open_form( 'credentials_flex' );
			echo esc_html__( 'Enable relaxed file ownership (not recommended, especially on shared hosting).', 'cute-mediainfo' );
			echo '<br />';
			submit_button(
				__( 'Enable relaxed file ownership', 'cute-mediainfo' ),
				'small',
				'submit',
				false,
				array( 'style' => 'color: red;' )
			);
			echo '</form>';
		}

		echo '<br />';
		$this->print_open_form( 'credentials_css_inline' );
		echo esc_html__( 'Do not generate static css file, instead add the styles in an inline style tag on each page. (not recommended, it increases the size of your pages and the styles are not cached).', 'cute-mediainfo' );
		echo '<br />';
		submit_button(
			__( 'Force CSS inline', 'cute-mediainfo' ),
			'small',
			'submit',
			false,
			array( 'style' => 'color: red;' )
		);

		echo '</form>';
	}

}
