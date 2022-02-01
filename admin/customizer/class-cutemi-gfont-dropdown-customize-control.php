<?php

if ( ! class_exists( 'WP_Customize_Control' ) ) {
	return null;
}

/**
 * A class to create a dropdown for all google fonts
 */
class CUTEMI_GFont_Dropdown_Customize_Control extends WP_Customize_Control {
	private $fonts;
	private $no_set_option = true;
	private $google_font_css_url;

	public function __construct( $manager, $id, $args = array(), $options = array() ) {
		if ( ! empty( $args['google_fonts_url'] ) ) {
			$this->google_font_css_url = $args['google_fonts_url'];
		} else {
			$this->google_font_css_url = 'https://fonts.googleapis.com/css2?family=Teko:wght@300&family=Audiowide&' .
											'family=Creepster&family=Fredericka+the+Great&family=Galada&' .
											'family=Indie+Flower&family=Istok+Web:ital,wght@0,400;0,700;1,400&' .
											'family=Lexend:wght@100;400;700&family=Nanum+Pen+Script&' .
											'family=Poiret+One&family=Share+Tech&family=Six+Caps&' .
											'family=Ubuntu+Condensed&family=Unica+One&family=Vidaloka&display=swap';
		}
		if ( ! empty( $args['no_set_option'] ) ) {
			$this->no_set_option = (bool) ( $args['no_set_option'] );
		}
		$this->fonts = $this->get_fonts();
		parent::__construct( $manager, $id, $args );
	}

	/**
	 * Get the google fonts from the API or in the cache
	 *
	 *
	 * @return array
	 */
	public function get_fonts() {
		$fonts = array();
		$q     = wp_parse_url( $this->google_font_css_url, PHP_URL_QUERY );
		if ( ! empty( $q ) ) {
			$q    = str_replace( '&family=', '&family[]=', '&' . $q );
			$vars = array();
			parse_str( $q, $vars );
			if ( ! empty( $vars['family'] ) && is_array( $vars['family'] ) ) {
				foreach ( $vars['family'] as $family ) {
					$news  = $this->get_fonts_from_str( $family );
					$fonts = array_merge( $fonts, $news );
				}
			}
		}

		return $fonts;
	}

	private function get_fonts_from_str( $family_all ) {
		$fonts = array();

		$family_variants = explode( ':', $family_all );

		if ( 1 === count( $family_variants ) ) {
			//For simple family=Audiowide
			$fonts[] = $this->get_font( $family_variants[0] );
		} elseif ( is_array( $family_variants ) ) {
			$keys_arr = explode( '@', $family_variants[1] );
			if ( 1 === count( $keys_arr ) ) {
				//example: family = Open+Sans : 300,400,600,700 -> "300,400,600,700"
				$weights = explode( ',', $family_variants[1] );
				foreach ( $weights as $weight ) {
					$fonts[] = $this->get_font( $family_variants[0], $weight );
				}
			} elseif ( is_array( $keys_arr ) ) {
				//examples: family = Crimson+Pro : wght @ 400;700
				//family=Crimson+Pro:ital,wght@0,700;1,700 -> Crimson+Pro:ital,wght || 0,700;1,700
				//Istok+Web:ital,wght@0,400;0,700;1,400
				$arr_props = explode( ',', $keys_arr[0] );
				$arr_items = explode( ';', $keys_arr[1] );
				foreach ( $arr_items as $arr_item ) {
					$item_props_values = explode( ',', $arr_item );
					$item              = array();
					foreach ( $item_props_values as $idx => $val ) {
						$item[ $arr_props[ $idx ] ] = $val;
					}
					$fonts[] = $this->get_font(
						$family_variants[0],
						isset( $item['wght'] ) ? $item['wght'] : '',
						isset( $item['ital'] ) ? $item['ital'] : '',
						isset( $item['wdth'] ) ? $item['wdth'] : ''
					);
				}
			}
		}

		return $fonts;
	}

	private function get_font( $family, $wght = '', $ital = '', $wdth = '' ) {
		$extra = '';
		if ( ! empty( $wght ) ) {
			$extra .= ' ' . $wght;
		}
		if ( ! empty( $ital ) ) {
			$extra .= ' italic';
		}
		if ( ! empty( $wdth ) ) {
			$extra .= ' width ' . $wdth;
		}

		return array(
			'label'  => $family . $extra,
			'family' => $family,
			'wght'   => $wght,
			'wdth'   => $wdth,
			'ital'   => $ital,
			'type'   => 'gfont',
		);
	}

	public function enqueue() {
		// phpcs:ignore
		wp_register_style(
			'google-fonts-' . $this->id,
			$this->google_font_css_url,
			array(),
			//reason: multiple families gfont $ver require null, see: https://core.trac.wordpress.org/ticket/49742
			null,
			'all'
		);
		wp_enqueue_style( 'google-fonts-' . $this->id );
	}

	/**
	 * Render the content of the category dropdown
	 *
	 * @return void
	 */
	public function render_content() {
		if ( ! empty( $this->fonts ) ) {
			$input_id       = '_customize-input-' . $this->id;
			$description_id = '_customize-description-' . $this->id;
			?>
			<?php if ( ! empty( $this->label ) ) : ?>
				<label for="<?php echo esc_attr( $input_id ); ?>"
					class="customize-control-title"><?php echo esc_html( $this->label ); ?></label>
			<?php endif; ?>
			<?php if ( ! empty( $this->description ) ) : ?>
				<span id="<?php echo esc_attr( $description_id ); ?>"
						class="description customize-control-description">
					<?php echo wp_kses( $this->description, wp_kses_allowed_html() ); ?>
				</span>
				<select
					id="<?php echo esc_attr( $input_id ); ?>"
					aria-describedby="<?php echo esc_attr( $description_id ); ?>"
					<?php $this->link(); ?>>
			<?php else : ?>
				<select id="<?php echo esc_attr( $input_id ); ?>" <?php $this->link(); ?>>
			<?php endif; ?>
				<?php
				if ( $this->no_set_option ) {
					echo '<option value="" ' . selected( $this->value(), '', false ) . '>' .
							esc_html( __( 'Unset', 'cute-mediainfo' ) ) .
							'</option>';
				}
				foreach ( $this->fonts as $k => $v ) {
					$option_style = 'font-family: "' . $v['family'] . '";';
					if ( ! empty( $v['ital'] ) ) {
						$option_style .= 'font-variant: italic;';
					}
					if ( ! empty( $v['wght'] ) ) {
						$option_style .= 'font-weight:' . $v['wght'] . ';';
					}
					if ( ! empty( $v['wdth'] ) ) {
						$option_style .= 'font-stretch:' . $v['wdth'] . '%;';
					}
					$v['value'] = wp_json_encode( $v );
					printf(
						'<option style="%s" value="%s" %s >%s</option>',
						esc_attr( $option_style ),
						esc_attr( $v['value'] ),
						selected( $this->value(), $v['value'], false ),
						esc_html( $v['label'] )
					);
				}
				?>
			</select>
			<?php
		}
	}
}
