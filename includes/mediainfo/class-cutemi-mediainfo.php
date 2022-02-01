<?php

class CUTEMI_MediaInfo {

	public $sections = array();

	public function __construct() {

	}

	public function parse( $mediainfo ) {
		$lines = array_map( 'trim', explode( "\n", $mediainfo ) );

		$section_name_type = '';
		$section_lines     = array();
		foreach ( $lines as $line ) {
			if ( empty( $line ) ) {
				continue;
			}
			if ( strpos( $line, ':' ) === false ) {
				//ready section
				if ( ! empty( $section_name_type ) ) {
					$this->sections[] = $this->get_section( $section_lines, $section_name_type );
					//reset for next section
					$section_lines = array();
				}
				$section_name_type = $line;
			} else {
				$section_lines[] = $line;
			}
		}
		//last section:
		if ( ! empty( $section_name_type ) ) {
			$this->sections[] = $this->get_section( $section_lines, $section_name_type );
		}
	}

	private function get_section( $section_lines, $section_name_type ) {
		$section = $this->section_properties( $section_lines );
		if ( preg_match( '/([a-z]*)[\s*#]?([0-9]*)/i', $section_name_type, $match ) ) {
			$section['type'] = strtolower( $match[1] );
			if ( ! empty( $match[2] ) ) {
				$section['index'] = $match[2];
			}
		}

		return $section;
	}

	private function section_properties( $lines ) {
		if ( is_string( $lines ) ) {
			$lines = explode( "\n", $lines );
		}
		$props = array();
		foreach ( $lines as $line ) {
			$parts = explode( ':', $line, 2 );
			if ( count( $parts ) === 2 ) {
				$props[ $this->prepare_key( $parts[0] ) ] = trim( $parts[1] );
			}
		}

		return $props;
	}

	private function prepare_key( $key ) {
		return preg_replace(
			'/[^A-Za-z0-9]/',
			'',
			html_entity_decode( strtolower( $key ), ENT_QUOTES, 'UTF-8' )
		);
	}

	public function get_sections_by_types( $type ) {
		$type_sections = array();
		$type          = strtolower( $type );
		foreach ( $this->sections as $section ) {
			if ( isset( $section['type'] ) && $section['type'] === $type ) {
				$type_sections[] = $section;
			}
		}

		return $type_sections;
	}

	public function properties_match( $section, $required, $ignore_case = true ) {
		foreach ( $required as $property => $value ) {
			if ( substr( $value, 0, 1 ) === '*' ) {
				if ( ! preg_match( substr( $value, 1 ), $this->get_property( $section, $property ) ) ) {
					return false;
				}
			} else {
				if ( $ignore_case ) {
					if ( strtolower( $this->get_property( $section, $property ) ) !== strtolower( $value ) ) {
						return false;
					}
				} else {
					if ( $this->get_property( $section, $property ) !== $value ) {
						return false;
					}
				}
			}
		}

		return true;
	}

	public function get_property( $section, $name ) {
		if ( ! is_array( $section ) ) {
			if ( ! isset( $this->sections[ $section ] ) ) {
				return false;
			}
			$section = $this->sections[ $section ];
		}
		$name_key = $this->prepare_key( $name );
		if ( isset( $section[ $name_key ] ) ) {
			return $section[ $name_key ];
		}

		return false;
	}

}
