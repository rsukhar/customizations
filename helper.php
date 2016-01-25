<?php

if ( ! class_exists( 'CL_Shortcode_Overloader' ) ) {
	class CL_Shortcode_Overloader {

		/**
		 * @var array The list of filters
		 */
		public $filters = array();
		/**
		 * @var array The list of original shortcodes functions
		 */
		public $functions = array();

		public function overload( $shortcode, $filter ) {
			if ( count( $this->filters ) == 0 ) {
				add_action( 'init', array( $this, 'init' ), 100 );
			}
			$this->filters[ $shortcode ] = $filter;
		}

		/**
		 * Function for WordPress "init" action
		 */
		public function init() {
			global $shortcode_tags;
			foreach ( $this->filters as $shortcode => $filter ) {
				if ( array_key_exists( $shortcode, $shortcode_tags ) ) {
					$this->functions[ $shortcode ] = $shortcode_tags[ $shortcode ];
					remove_shortcode( $shortcode );
					add_shortcode( $shortcode, array( $this, $shortcode ) );
				}
			}
		}

		/**
		 * The new shortcodes functions to be bound
		 *
		 * @param string $shortcode
		 * @param array $args
		 *
		 * @return string
		 */
		public function __call( $shortcode, $args ) {
			if ( ! isset( $this->functions[ $shortcode ] ) OR ! is_callable( $this->functions[ $shortcode ] ) ) {
				return '';
			}
			$output = call_user_func_array( $this->functions[ $shortcode ], $args );
			if ( isset( $this->filters[ $shortcode ] ) AND is_callable( $this->filters[ $shortcode ] ) ) {
				$output = call_user_func( $this->filters[ $shortcode ], $output );
			}

			return $output;
		}
	}

	global $cl_shortcode_overloader;
	$cl_shortcode_overloader = new CL_Shortcode_Overloader;
}

/*
$cl_shortcode_overloader->overload( 'ult_buttons', 'gevin_ult_buttons' );
function gevin_ult_buttons( $output ) {
	return '<div class="gevin-button">' . $output . '</div>';
}
*/