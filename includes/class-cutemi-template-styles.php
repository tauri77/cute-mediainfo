<?php
/**
 * Manage the styles.
 *
 * Generate the css file from the user options (when it changes)
 * and enqueuing the generated styles and google fonts required
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

class CUTEMI_Template_Styles {

	private static $unique_instance;

	private $customizer_saving = false;
	private $error_css_writing = false;
	/**
	 * @var bool
	 */
	private $save_on_end = false;

	public static function instance() {
		if ( ! self::$unique_instance instanceof self ) {
			self::$unique_instance = new self();
		}

		return self::$unique_instance;
	}

	private function __construct() {

		//refresh css styles hooks
		add_action( 'cutemi_activated', array( $this, 'save_compile_all_css' ) );
		add_action( 'cutemi_refresh_css', array( $this, 'save_compile_all_css' ) );

		$setting_for_refresh = array(
			'cutemi_profiles',
			'cutemi_svg_head_colorized',
			'cutemi_styling',
		);
		foreach ( $setting_for_refresh as $setting_name ) {
			add_action( 'update_option_' . $setting_name, array( $this, 'set_for_save_compile_all_css' ) );
		}

		//write css if any changes
		add_filter( 'wp_redirect', array( $this, 'on_end' ) );
		add_action( 'customize_save_after', array( $this, 'on_end' ) );

		//For set customizer_saving = true, then write on_end
		add_action( 'customize_save', array( $this, 'pre_customize_save' ) );
		//Add errors to customizer
		add_filter( 'customize_save_response', array( $this, 'customize_save_response' ), 10, 2 );

		add_action( 'update_option_cutemi_force_inline_css', array( $this, 'cutemi_force_inline_css_change' ) );
		add_action( 'update_option_cutemi_relaxed_ownership', array( $this, 'cutemi_cutemi_relaxed_ownership_change' ) );

		//For forced CSS inline or customizer
		add_action( 'wp_head', array( $this, 'head' ), 20 );
		add_action( 'admin_head', array( $this, 'head' ), 20 );
		//Enqueue css
		add_action( 'cutemi_enqueue_css', array( $this, 'enqueue_css' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_css' ) );
		//add Subresource Integrity (SRI) to <link> ( hash generate when write css file )
		add_filter( 'style_loader_tag', array( $this, 'style_loader_tag' ), 10, 2 );
	}

	public function cutemi_force_inline_css_change() {
		if ( 'on' === get_option( 'cutemi_force_inline_css', 'off' ) ) {
			$styles = $this->compile_all_css();
			$styles = $this->sanitize_styles( $styles );
			update_option( 'cutemi_css_compiled', $styles, true );
		} else {
			update_option( 'cutemi_css_compiled', '', false );
			$this->save_compile_all_css();
		}
	}

	public function cutemi_cutemi_relaxed_ownership_change() {
		if ( 'on' === get_option( 'cutemi_relaxed_ownership', 'off' ) ) {
			$this->save_compile_all_css();
		}
	}

	/**
	 * Add SRI to cutemi-css
	 *
	 * @param $html
	 * @param $handle
	 *
	 * @return string
	 */
	public function style_loader_tag( $html, $handle ) {
		if ( 'cutemi-css' === $handle ) {
			$hash          = get_option( 'cutemi_css_integrity', '' );
			$insertion_pos = strpos( $html, '/>' );
			if ( false === $insertion_pos ) {
				$insertion_pos = strpos( $html, '>' );
			}
			if ( false !== $insertion_pos ) {
				if ( ' ' === substr( $html, $insertion_pos - 1, 1 ) ) {
					$insertion_pos--;
				}
				return substr( $html, 0, $insertion_pos ) .
						' integrity="' . esc_attr( $hash ) . '"' .
						substr( $html, $insertion_pos );
			}
		}
		return $html;
	}

	public function set_for_save_compile_all_css() {
		$this->save_on_end = true;
	}

	public function on_end( $any = '' ) {
		if ( $this->save_on_end ) {
			$this->save_on_end = false;
			$this->save_compile_all_css();
			set_transient( 'settings_errors', get_settings_errors(), 30 );
		}
		return $any;
	}

	public function pre_customize_save( $customizer ) {
		$this->customizer_saving = true;
	}

	public function enqueue_css() {
		if ( ! is_customize_preview() ) {
			$this->enqueue_google_fonts_tag();
			if (
				'on' !== get_option( 'cutemi_force_inline_css', 'off' ) &&
				'1' !== get_option( 'cutemi_pending_css', '0' )
			) {
				$upload_path = wp_upload_dir();
				wp_enqueue_style(
					'cutemi-css',
					$upload_path['baseurl'] . '/cute-mediainfo/css/styles.css',
					array(),
					get_option( 'cutemi_css_version', 1 )
				);
			}
		}
	}

	public function head() {
		if ( is_customize_preview() ) {
			$out_css = $this->compile_all_css();
			$out_css = $this->sanitize_styles( $out_css );
			echo '<style id="cutemi-css">';
			echo wp_strip_all_tags( $out_css ); // phpcs:ignore WordPress.Security.EscapeOutput
			echo '</style>';
			$this->enqueue_google_fonts_tag();
			wp_dequeue_style( 'cutemi-css' );
		} elseif (
			'on' === get_option( 'cutemi_force_inline_css', 'off' ) ||
			'1' === get_option( 'cutemi_pending_css', '0' )
		) {
			$this->print_inline();
		}
	}

	public function print_inline() {
		echo '<style id="cutemi-css">';
		$raw_content = get_option( 'cutemi_css_compiled', '' );
		//Hack from https://plugins.svn.wordpress.org/simple-custom-css/trunk/includes/public.php
		/** @noinspection PhpParamsInspection */
		$content = wp_kses( $raw_content, array( '\'', '\"' ) );
		$content = str_replace( '&gt;', '>', $content );
		echo wp_strip_all_tags( $content ); // phpcs:ignore WordPress.Security.EscapeOutput
		echo '</style>';
	}

	private function compile_all_css() {
		include_once CUTE_MEDIAINFO_DIR . '/templates/template-css.php';
		$out          = cutemi_template_table_style_generic();
		$profiles     = cutemi_get_profiles( is_customize_preview() && ! $this->is_customize_saving() );
		$google_fonts = array();
		foreach ( $profiles as $id => $profile ) {
			$profile['id'] = $id;

			$table_template = new CUTEMI_Table_Template( $profile );
			$style_config   = $table_template->get_styles_config();
			if ( ! empty( $style_config['font_import'] ) ) {
				$google_fonts[] = $style_config['font_import'];
			}
			$out .= cutemi_template_table_style_customized( $id, $style_config );
		}
		$google_fonts = array_unique( $google_fonts );
		if ( is_customize_preview() ) {
			$GLOBALS['cutemi_google_fonts_tmp'] = $google_fonts;
		} else {
			update_option( 'cutemi_google_fonts', $google_fonts );
		}
		if ( $this->is_customize_saving() ) {
			update_option( 'cutemi_google_fonts', $google_fonts );
		}

		return $out;
	}

	private function is_customize_saving() {
		return $this->customizer_saving;
	}

	private function sanitize_styles( $styles ) {

		//Hack from https://plugins.svn.wordpress.org/simple-custom-css/trunk/includes/public.php
		/** @noinspection PhpParamsInspection */
		$styles = wp_kses( $styles, array( "'", '"' ) );
		$styles = str_replace( '&gt;', '>', $styles );
		$styles = wp_strip_all_tags( $styles );

		/*    "minify"   */
		$styles = preg_replace( "/[\n\r\t]/", '', $styles );
		$styles = str_replace( ': ', ':', $styles );
		$styles = preg_replace( '/\s?([,{}])\s?/', '$1', $styles );
		$styles = preg_replace( '/ {2,}/', ' ', $styles );

		/*  Blocklist Property */
		$remove = array(
			'-moz-binding',
			'expression',
			'javascript',
			'vbscript',
		);
		$styles = str_replace( $remove, '', $styles );
		$styles = preg_replace( '#</?\w+#', '', $styles );

		return $styles;
	}

	private function enqueue_google_fonts_tag() {
		if ( is_customize_preview() ) {
			$google_fonts = $GLOBALS['cutemi_google_fonts_tmp'];
		} else {
			$google_fonts = get_option( 'cutemi_google_fonts', array() );
		}
		foreach ( $google_fonts as $idx => $font ) {
			//reason: multiple families gfont $ver require null, see: https://core.trac.wordpress.org/ticket/49742
			wp_enqueue_style( 'cutemi-css-gfont-' . $idx, $font, array(), null ); // phpcs:ignore
		}
	}

	/**
	 * Add error messages if css file could not be written
	 *
	 * @param $response
	 * @param $customizer
	 *
	 * @return mixed
	 */
	public function customize_save_response( $response, $customizer ) {

		if ( $this->error_css_writing ) {

			$err    = get_settings_errors();
			$errors = array();
			foreach ( $err as $msg ) {
				if (
					'general' === $msg['setting'] &&
					! in_array( $msg['code'], array( 'cutemi_css_err', 'settings_updated' ), true )
				) {
					$errors[] = wp_kses( $msg['message'], wp_kses_allowed_html() );
				}
			}

			$response['cutemi'] = array(
				'message' => 'Settings saved but CuteMI CSS file could not be written.<br />' .
								implode( '<br />', $errors ),
				'code'    => 'cutemi_error_css_writing',
			);
		}

		return $response;
	}

	/**
	 * Get settings, generate styles code, and save the css file.
	 *
	 * Also save the hash for integrity link tag.
	 *
	 * @return bool All OK return true
	 */
	public function save_compile_all_css() {
		$styles = $this->compile_all_css();

		$styles = $this->sanitize_styles( $styles );

		if ( $styles ) {
			if ( 'on' === get_option( 'cutemi_force_inline_css', 'off' ) ) {
				update_option( 'cutemi_css_compiled', $styles, true );
				return false;
			}
			if ( ! self::write_css_file( $styles ) ) {
				$this->error_css_writing = true;
				add_settings_error(
					'general',
					'cutemi_css_err',
					__( 'Cute MediaInfo write CSS ERROR.', 'cute-mediainfo' ),
					'error'
				);
				update_option( 'cutemi_pending_css', '1' );
				update_option( 'cutemi_css_compiled', $styles, true );
				return false;
			} else {
				if ( ! $this->error_css_writing ) {

					//generate and save the integrity hash
					$hash        = hash( 'sha256', $styles, true );
					$hash_base64 = base64_encode( $hash );// phpcs:ignore
					update_option( 'cutemi_css_integrity', 'sha256-' . $hash_base64 );
					update_option( 'cutemi_pending_css', '0' );

					add_settings_error(
						'general',
						'cutemi_css_ok',
						__( 'Cute MediaInfo write CSS OK.', 'cute-mediainfo' ),
						'success'
					);

					return true;
				}
			}
		}

		return false;
	}

	public static function write_css_file( $styles ) {

		global $wp_filesystem;
		$created = CUTEMI_Storage::create_storage_path();
		if ( is_wp_error( $created ) ) {
			add_settings_error(
				'general',
				'css_write_error',
				$created->get_error_message()
			);

			return false;
		}
		$file = CUTEMI_Storage::get_storage_path();
		if ( $wp_filesystem->exists( $file ) ) {
			if ( ! $wp_filesystem->delete( $file ) ) {
				add_settings_error(
					'general',
					'css_write_error',
					esc_html__( 'Error when trying to delete the previous css. The new styles cannot be written.', 'cute-mediainfo' )
				);
				return false;
			}
		}
		$ok = $wp_filesystem->put_contents( $file, $styles, 0644 );

		if ( false !== $ok ) {
			update_option( 'cutemi_css_version', wp_rand( 1, 999 ) . time() );

			return true;
		}

		add_settings_error(
			'general',
			'css_write_error',
			esc_html__( 'Writing Error. The new styles cannot be written.', 'cute-mediainfo' )
		);

		return false;
	}

}

CUTEMI_Template_Styles::instance();
