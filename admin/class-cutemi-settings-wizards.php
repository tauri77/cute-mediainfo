<?php

class CUTEMI_Settings_Wizards {

	private static $unique_instance;

	private $action = 'general';

	private $invalid_nonce = false; //use for print message

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
	 * For save some option before print fields
	 *
	 * Always return original value (show tabs)
	 *
	 * @param $print_tabs
	 *
	 * @return bool
	 */
	public function pre_process_setup( $print_tabs ) {

		$this->action = 'general';
		if ( isset( $_POST ) && isset( $_POST['cutemi_setup_tax'] ) ) {
			if (
				isset( $_POST['cutemi_setup_nonce'] ) &&
				wp_verify_nonce( $_POST['cutemi_setup_nonce'], 'cutemi_setup' )
			) {
				if ( is_array( $_POST['cutemi_setup_tax'] ) ) {
					foreach ( $_POST['cutemi_setup_tax'] as $_tax => $_terms ) {
						//validate _terms and _tax
						if ( is_string( $_tax ) && taxonomy_exists( $_tax ) && is_array( $_terms ) ) {
							//Only use $_terms that exist on core for taxonomy $_tax
							$_terms = $this->filter_valid_core_terms( $_tax, $_terms );

							include_once CUTE_MEDIAINFO_DIR . '/admin/install/class-cutemi-update.php';
							$installer = new CUTEMI_Update();
							$installer->install_taxonomy_terms( $_tax, $_terms );
							$this->set_installed_terms( $_tax, $_terms );
						}
					}
				}
				if ( 'setup_ready' === $_REQUEST['cutemi_setup_action'] ) {
					update_option( 'cutemi_setup_ready', '1', true );
				}
			} else {
				$this->invalid_nonce = true;

				return $print_tabs;
			}
		}

		$valid_actions = array(
			'general',
			'video',
			'audio',
			'text',
			'setup_ready',
			'force',
		);
		if (
			isset( $_REQUEST['cutemi_setup_action'] ) &&
			in_array( $_REQUEST['cutemi_setup_action'], $valid_actions, true )
		) {
			$this->action = $_REQUEST['cutemi_setup_action'];
		}

		if ( 'force' === $this->action ) {
			if (
				isset( $_POST['cutemi_setup_nonce'] )
				&& wp_verify_nonce( $_POST['cutemi_setup_nonce'], 'cutemi_setup' )
			) {
				//Force re-run setup
				$this->action = 'general';
				update_option( 'cutemi_setup_ready', '0', true );
			} else {
				$this->invalid_nonce = true;
				return $print_tabs;
			}
		}

		return $print_tabs;
	}

	/** @noinspection PhpUnused */
	public function print_setup() {

		if ( $this->invalid_nonce ) {
			echo '<div class="cutemi-setup-steps-desc"><p>';
			echo esc_html__( 'Invalid nonce. Please try again.', 'cute-mediainfo' );
			echo '</p></div>';

			return;
		}

		if ( '1' === get_option( 'cutemi_setup_ready', false ) ) {
			$this->print_setup_ready();

			return;
		}

		$cutemi_taxonomies = array();
		include CUTE_MEDIAINFO_DIR . '/admin/install/taxonomies/load-terms.php';
		include_once CUTE_MEDIAINFO_DIR . '/admin/install/class-cutemi-walker-term-install.php';

		$desc        = __( 'Check the options you want to be available. Deselecting an option will erase it along with its settings.', 'cute-mediainfo' );
		$step_titles = array(
			array(
				'label'      => __( 'General', 'cute-mediainfo' ),
				'desc'       => $desc,
				'setup_step' => 'general',
			),
			array(
				'label'      => __( 'Video', 'cute-mediainfo' ),
				'desc'       => $desc,
				'setup_step' => 'video',
			),
			array(
				'label'      => __( 'Audio', 'cute-mediainfo' ),
				'desc'       => $desc,
				'setup_step' => 'audio',
			),
			array(
				'label'      => __( 'Text', 'cute-mediainfo' ),
				'desc'       => $desc,
				'setup_step' => 'text',
			),
		);

		$actual_step_idx = 0;
		foreach ( $step_titles as $i => $title ) {
			if ( $this->action === $title['setup_step'] ) {
				$actual_step_idx = $i;
				break;
			}
		}

		//Set next step
		$next = 'setup_ready';
		if ( isset( $step_titles[ $actual_step_idx + 1 ] ) ) {
			$next = $step_titles[ $actual_step_idx + 1 ]['setup_step'];
		}

		$this->print_open_form( $next );

		echo '<div class="cutemi-setup-steps cutemi-steps-' . count( $step_titles ) . '">';
		foreach ( $step_titles as $i => $title ) {
			$class = ( $i === $actual_step_idx ) ? 'cutemi-actual-step' : '';
			echo sprintf(
				'<a class="cutemi-setup-step %s" href="%s"><b>%d</b>%s</a>',
				esc_attr( $class ),
				esc_url( add_query_arg( array( 'cutemi_setup_action' => $title['setup_step'] ) ) ),
				esc_html( $i + 1 ),
				esc_html( $title['label'] )
			);
		}
		echo '</div>';

		echo '<div class="cutemi-setup-steps-desc"><p>';
		echo wp_kses( $step_titles[ $actual_step_idx ]['desc'], cutemi_get_allowed_html() );
		echo '</p></div>';

		$steps       = array(
			'general' => array( 'cutemi_file_format', 'cutemi_site_source' ),
			'video'   => array(
				'cutemi_video_resolution',
				'cutemi_video_tech',
				'cutemi_video_bitrate',
				'cutemi_video_bitrate_mode',
			),
			'audio'   => array(
				'cutemi_audio_tech',
				'cutemi_audio_channels',
				'cutemi_audio_langs',
				'cutemi_audio_bitrate',
				'cutemi_audio_bitrate_mode',
			),
			'text'    => array( 'cutemi_text_langs', 'cutemi_text_format', 'cutemi_text_type' ),
		);
		$actual_step = $steps['general'];
		if ( isset( $steps[ $this->action ] ) ) {
			$actual_step = $steps[ $this->action ];
		}

		echo '<div>';
		foreach ( $cutemi_taxonomies as $taxonomy => $terms ) {
			if ( in_array( $taxonomy, $actual_step, true ) ) {
				$this->print_setup_tax_terms( $taxonomy, $terms );
			}
		}
		echo '</div>';
		submit_button( esc_html__( 'Next', 'cute-mediainfo' ) );
		echo '</form>';
	}

	private function print_open_form( $setup_action, $print_nonce = true ) {
		printf(
			'<form class="cutemi-setup-form" action="%s#cutemi_setup" method="POST">',
			esc_url( menu_page_url( 'settings_cute_mediainfo', false ) )
		);
		printf(
			'<input type="hidden" name="cutemi_setup_action" value="%s">',
			esc_attr( $setup_action )
		);
		if ( $print_nonce ) {
			wp_nonce_field( 'cutemi_setup', 'cutemi_setup_nonce' );
		}
	}

	private function print_setup_ready() {
		echo '<h2 class="cutemi-ready-title">';
		echo esc_html__( 'Wizard already finished!', 'cute-mediainfo' );
		echo '</h2>';
		echo '<p class="cutemi-ready-text">';
		echo esc_html__( 'Now you can continue with general settings or customize profiles', 'cute-mediainfo' );
		echo '</p>';
		echo '<div class="cutemi-ready-rerun">';
		$this->print_open_form( 'force' );
		submit_button( esc_html__( 'Rerun the Wizard', 'cute-mediainfo' ), 'button' );
		echo '</form>';
		echo '</div>';
	}

	private function print_setup_tax_terms( $taxonomy, $terms ) {
		$tax = get_taxonomy( $taxonomy );

		$config                 = array();
		$config['hierarchical'] = true;
		$walker                 = new CUTEMI_Walker_Term_Install( $config );
		$selected_cats          = $this->get_installed_terms( $taxonomy );

		$args = array(
			'taxonomy'      => $taxonomy,
			'selected_cats' => $selected_cats,
			'popular_cats'  => false,
			'checked_ontop' => false,
			'walker'        => $walker,
			'echo'          => false,
		);

		$_terms = array();
		foreach ( $terms as $term ) {
			$_term           = (object) $term;
			$_term->priority = isset( $term['metas'] ) && isset( $term['metas']['priority_sum'] ) ? $term['metas']['priority_sum'] : 0;
			$_terms[]        = $_term;
		}
		usort(
			$_terms,
			function ( $a, $b ) {
				return $b->priority - $a->priority;
			}
		);

		echo '<div class="cutemi-setup-tax-list">';
		echo '<h2>' . esc_html( $tax->label );
		echo ' <a href="javascript:void(0)" onclick="cutemiSetupSelectAll(this)" class="cutemi-setup-select-all">';
		echo '<small>Select All</small>';
		echo '</a></h2>';
		echo '<ul>';
		// phpcs:ignore WordPress.Security.EscapeOutput
		echo call_user_func_array( array( $walker, 'walk' ), array( $_terms, 0, $args ) ); //(already escaped)
		echo '</ul>';
		echo '</div>';
	}

	public function get_installed_terms( $taxonomy ) {
		$installed_terms = get_option( 'cutemi_installed_terms', false );
		if ( false === $installed_terms || ! isset( $installed_terms[ $taxonomy ] ) ) {
			$installed = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'hide_empty' => false,
				)
			);
			$slugs     = array();
			foreach ( $installed as $term ) {
				$slugs[] = $term->slug;
			}

			return $this->filter_valid_core_terms( $taxonomy, $slugs );
		}

		return $installed_terms[ $taxonomy ];
	}

	public function set_installed_terms( $taxonomy, $terms ) {
		$installed_terms = get_option( 'cutemi_installed_terms', false );
		if ( ! is_array( $installed_terms ) ) {
			$installed_terms = array();
		}
		$installed_terms[ $taxonomy ] = $this->filter_valid_core_terms( $taxonomy, $terms );
		update_option( 'cutemi_installed_terms', $installed_terms, false );
	}

	public function filter_valid_core_terms( $taxonomy, $terms ) {
		$cutemi_taxonomies = array();
		include CUTE_MEDIAINFO_DIR . '/admin/install/taxonomies/load-terms.php';

		$filtered_terms = array();
		$core_terms     = isset( $cutemi_taxonomies[ $taxonomy ] ) ? $cutemi_taxonomies[ $taxonomy ] : array();
		foreach ( $terms as $term_slug ) {
			foreach ( $core_terms as $core_term ) {
				if ( $core_term['slug'] === $term_slug ) {
					$filtered_terms[] = $core_term['slug'];
				}
			}
		}

		return $filtered_terms;
	}

}
