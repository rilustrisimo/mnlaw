<?php
/**
 * Shortcodes register component used for shorcodes menu generation (for wp rich text editor).
 *
 * @author    eyorsogood.com, Rouie Ilustrisimo
 * @package   SwishDesign
 * @version   1.0.0
 */

/**
 * No direct access to this file.
 *
 * @since 1.0.0
 */
defined( 'ABSPATH' ) || die();

/**
 * Class SD_Shortcodes_Register
 */
class SD_Shortcodes_Register extends SD_Component {

	protected $list = array();

	protected $menu = array();

	protected $titles = array();

	protected $is_disabled = false;

	public function set_disabled_state( $value ) {
		$this->is_disabled = $value;
	}

	public function add( $name, $menu_position, array $attributes_config = array() ) {
		if ( $this->is_disabled ) {
			return $this;
		}

		$sc_title = '';
		if ( is_array( $name ) ) {
			$sc_name = array_shift( $name );
			if ( $name ) {
				$sc_title = array_shift( $name );
			}
		} else {
			$sc_name = $name;
		}

		$this->menu[ $sc_name ] = $menu_position;

		if ( $attributes_config ) {
			$this->list[ $sc_name ] = $attributes_config;
		}

		if ( $sc_title ) {
			$this->titles[ $sc_name ] = $sc_title;
		}
		return $this;
	}

	public function get_menu_config() {
		$list = array();

		foreach ( $this->menu as $sh_name => $fullpath ) {
			$parts = explode( '.', $fullpath );
			$cp = &$list;
			foreach ( $parts as $level ) {
				if ( ! isset( $cp[ $level ] ) ) {
					$cp[ $level ] = array();
				}
				$cp = &$cp[ $level ];
			}

			if ( ! empty( $cp ) ) {
				if ( is_string( $cp ) ) {
					$x = $cp;
					$cp = array();
					$cp[ $this->get_shortcode_title( $x ) ] = $x;
					$cp[ $this->get_shortcode_title( $sh_name ) ] = $sh_name;
				} else {
					$cp[ $this->get_shortcode_title( $sh_name ) ] = $sh_name;
				}
			} else {
				$cp = $sh_name;
			}
		}
		return $list;
	}

	public function get_dialogs_config() {
		return $this->list;
	}

	public function get_shortcode_title( $name ) {
		if ( isset( $this->titles[ $name ] ) ) {
			return $this->titles[ $name ];
		} else {
			return ucfirst( str_replace( '_', ' ', $name ) );
		}
	}
}
