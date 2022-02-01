<?php

if ( ! class_exists( 'WP_Customize_Control' ) ) {
	return null;
}

/**
 * A class to create a dropdown for all google fonts
 */
class CUTEMI_Font_Dropdown_Customize_Control extends WP_Customize_Control {
	private $fonts;
	private $no_set_option = true;

	public function __construct( $manager, $id, $args = array(), $options = array() ) {
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
		return array(
			array(
				'label'  => 'Helvetica',
				'family' => 'Helvetica',
				'wght'   => '',
				'ital'   => '',
				'wdth'   => '',
				'type'   => '',
			),
			array(
				'label'  => 'Helvetica Bold',
				'family' => 'Helvetica',
				'wght'   => '600',
				'ital'   => '',
				'wdth'   => '',
				'type'   => '',
			),
			array(
				'label'  => 'Arial',
				'family' => 'Arial',
				'wght'   => '',
				'ital'   => '',
				'wdth'   => '',
				'type'   => '',
			),
			array(
				'label'  => 'Times',
				'family' => 'Times',
				'wght'   => '',
				'ital'   => '',
				'wdth'   => '',
				'type'   => '',
			),
			array(
				'label'  => 'Times New Roman',
				'family' => 'Times New Roman',
				'wght'   => '',
				'ital'   => '',
				'type'   => '',
			),
			array(
				'label'  => 'Courier',
				'family' => 'Courier',
				'wght'   => '',
				'ital'   => '',
				'wdth'   => '',
				'type'   => '',
			),
			array(
				'label'  => 'Courier New',
				'family' => 'Courier New',
				'wght'   => '',
				'ital'   => '',
				'type'   => '',
			),
			array(
				'label'  => 'Verdana',
				'family' => 'Verdana',
				'wght'   => '',
				'ital'   => '',
				'wdth'   => '',
				'type'   => '',
			),
			array(
				'label'  => 'Tahoma',
				'family' => 'Tahoma',
				'wght'   => '',
				'ital'   => '',
				'wdth'   => '',
				'type'   => '',
			),
			array(
				'label'  => 'Arial Black',
				'family' => 'Arial Black',
				'wght'   => '',
				'ital'   => '',
				'wdth'   => '',
				'type'   => '',
			),
			array(
				'label'  => 'Impact',
				'family' => 'Impact',
				'wght'   => '',
				'ital'   => '',
				'wdth'   => '',
				'type'   => '',
			),
		);
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
				<span id="<?php echo esc_attr( $description_id ); ?>" class="description customize-control-description">
					<?php echo wp_kses( $this->description, wp_kses_allowed_html() ); ?>
				</span>
				<select
				id="<?php echo esc_attr( $input_id ); ?>"
				aria-describedby="<?php echo esc_attr( $description_id ); ?>"
				<?php $this->link(); ?>>
			<?php else : ?>
				<select
				id="<?php echo esc_attr( $input_id ); ?>"
				<?php $this->link(); ?>>
			<?php endif; ?>
				<?php
				if ( $this->no_set_option ) {
					printf(
						'<option value="" %s >%s</option>',
						selected( $this->value(), '', false ),
						esc_html__( 'Unset', 'cute-mediainfo' )
					);
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
