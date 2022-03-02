<?php

class CUTEMI_Alpha_Color_Control extends WP_Customize_Control {

	/**
	 * Control name.
	 */
	public $type = 'cutemi-alpha-color';

	/**
	 * Add support for palettes to be passed in.
	 *
	 * Supported palette values are true, false, or an array of RGBa and Hex colors.
	 */
	public $palette;

	/**
	 * Add support for showing the opacity value on the slider handle.
	 */
	public $show_opacity;

	/**
	 * Enqueue scripts and styles.
	 *
	 * Ideally these would get registered and given proper paths before this control object
	 * gets initialized, then we could simply enqueue them here, but for completeness as a
	 * stand alone class we'll register and enqueue them here.
	 */
	public function enqueue() {
		wp_enqueue_script(
			'cutemi-alpha-color-picker',
			plugins_url( 'assets/alpha-color-picker.js', __FILE__ ),
			array( 'jquery', 'wp-color-picker' ),
			'1.0.3',
			true
		);
		wp_enqueue_style(
			'cutemi-alpha-color-picker',
			plugins_url( 'assets/alpha-color-picker.css', __FILE__ ),
			array( 'wp-color-picker' ),
			'1.0.3'
		);
	}

	/**
	 * Render the control.
	 */
	public function render_content() {

		// Process the palette
		if ( is_array( $this->palette ) ) {
			$palette = implode( '|', $this->palette );
		} else {
			// Default to true.
			$palette = ( false === $this->palette || 'false' === $this->palette ) ? 'false' : 'true';
		}

		// Support passing show_opacity as string or boolean. Default to true.
		$show_opacity = ( false === $this->show_opacity || 'false' === $this->show_opacity ) ? 'false' : 'true';

		$description_id = '_customize-description-' . $this->id;

		?>
		<?php if ( ! empty( $this->label ) ) : ?>
			<label class="customize-control-title"><?php echo esc_html( $this->label ); ?></label>
		<?php endif; ?>
		<?php if ( ! empty( $this->description ) ) : ?>
			<span id="<?php echo esc_attr( $description_id ); ?>" class="description customize-control-description">
				<?php echo wp_kses( $this->description, wp_kses_allowed_html() ); ?>
			</span>
		<?php endif; ?>
		<label>
			<input class="cutemi-alpha-color-control" type="text"
				data-show-opacity="<?php echo esc_attr( $show_opacity ); ?>"
				data-palette="<?php echo esc_attr( $palette ); ?>"
				data-default-color="<?php echo esc_attr( $this->settings['default']->default ); ?>"
				<?php $this->link(); ?> />
		</label>
		<?php
	}
}
