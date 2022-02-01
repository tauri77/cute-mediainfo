<?php
/**
 * Class CUTEMI_Color_2_Filter
 *
 * Search filter css property for colorize to a color
 */

class CUTEMI_Color_2_Filter {

	private $reused_color;
	private $target;
	private $target_hsl;

	public function __construct( $target ) {
		$this->target       = $target;
		$this->target_hsl   = $target->hsl();
		$this->reused_color = new CUTEMI_Color( 0, 0, 0 );
	}

	public function solve() {
		$result = $this->solve_narrow( $this->solve_wide() );

		return array(
			'values' => $result['values'],
			'loss'   => $result['loss'],
			'filter' => $this->css( $result['values'] ),
		);
	}

	public function solve_narrow( $wide ) {
		$a_ = $wide['loss'];
		$c  = 2;
		$a1 = $a_ + 1;
		$a  = array( 0.25 * $a1, 0.25 * $a1, $a1, 0.25 * $a1, 0.2 * $a1, 0.2 * $a1 );

		return $this->spsa( $a_, $a, $c, $wide['values'], 500 );
	}

	public function spsa( $a_, $a, $c, $values, $iters ) {
		$alpha = 1;
		$gamma = 0.16666666666666666;

		$best      = null;
		$best_loss = 999999999999999;
		$deltas    = array();
		$high_args = array();
		$low_args  = array();

		for ( $k = 0; $k < $iters; $k ++ ) {
			$ck = $c / pow( $k + 1, $gamma );
			for ( $i = 0; $i < 6; $i ++ ) {
				$deltas[ $i ]    = wp_rand( 0, 100 ) > 50 ? 1 : - 1;
				$high_args[ $i ] = $values[ $i ] + $ck * $deltas[ $i ];
				$low_args[ $i ]  = $values[ $i ] - $ck * $deltas[ $i ];
			}

			$loss_diff = $this->loss( $high_args ) - $this->loss( $low_args );
			for ( $i = 0; $i < 6; $i ++ ) {
				$g            = $loss_diff / ( 2 * $ck ) * $deltas[ $i ];
				$ak           = $a[ $i ] / pow( $a_ + $k + 1, $alpha );
				$values[ $i ] = $this->fix( $values[ $i ] - $ak * $g, $i );
			}

			$loss = $this->loss( $values );
			if ( $loss < $best_loss ) {
				$best      = array_slice( $values, 0 );
				$best_loss = $loss;
			}
		}

		return array(
			'values' => $best,
			'loss'   => $best_loss,
		);
	}

	public function loss( $filters ) {
		// Argument is array of percentages.
		$color = $this->reused_color;
		$color->set( 0, 0, 0 );

		$color->invert( $filters[0] / 100 );
		$color->sepia( $filters[1] / 100 );
		$color->saturate( $filters[2] / 100 );
		$color->hue_rotate( $filters[3] * 3.6 );
		$color->brightness( $filters[4] / 100 );
		$color->contrast( $filters[5] / 100 );

		$color_hsl = $color->hsl();

		return (
			abs( $color->r - $this->target->r ) +
			abs( $color->g - $this->target->g ) +
			abs( $color->b - $this->target->b ) +
			abs( $color_hsl['h'] - $this->target_hsl['h'] ) +
			abs( $color_hsl['s'] - $this->target_hsl['s'] ) +
			abs( $color_hsl['l'] - $this->target_hsl['l'] )
		);
	}

	public function fix( $value, $idx ) {
		$max = 100;
		if ( 2 === $idx /* saturate */ ) {
			$max = 7500;
		} elseif ( 4 === $idx /* brightness */ || 5 === $idx /* contrast */ ) {
			$max = 200;
		}

		if ( 3 === $idx /* hue - rotate */ ) {
			if ( $value > $max ) {
				$value %= $max;
			} elseif ( $value < 0 ) {
				$value = $max + $value % $max;
			}
		} elseif ( $value < 0 ) {
			$value = 0;
		} elseif ( $value > $max ) {
			$value = $max;
		}

		return $value;
	}

	public function solve_wide() {
		$a_ = 5;
		$c  = 15;
		$a  = array( 60, 180, 18000, 600, 1.2, 1.2 );

		$best = array( 'loss' => 999999999999 );
		for ( $i = 0; $best['loss'] > 25 && $i < 3; $i ++ ) {
			$initial = array( 50, 20, 3750, 50, 100, 100 );
			$result  = $this->spsa( $a_, $a, $c, $initial, 1000 );
			if ( $result['loss'] < $best['loss'] ) {
				$best = $result;
			}
		}

		return $best;
	}

	public function css( $filters ) {
		return 'invert(' . $this->fmt( 0, $filters ) . '%) ' .
				'sepia(' . $this->fmt( 1, $filters ) . '%) ' .
				'saturate(' . $this->fmt( 2, $filters ) . '%) ' .
				'hue-rotate(' . $this->fmt( 3, $filters, 3.6 ) . 'deg) ' .
				'brightness(' . $this->fmt( 4, $filters ) . '%) ' .
				'contrast(' . $this->fmt( 5, $filters ) . '%);';
	}

	public function fmt( $idx, $filters, $multiplier = 1 ) {
		return round( $filters[ $idx ] * $multiplier );
	}
}
