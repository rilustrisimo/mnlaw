<?php
/**
 * * Register component. Used for sharing some values through components/services.
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
 * Class SD_Register
 */
class SD_Register extends SD_Component {

	/**
	 * Registers storage property.
	 *
	 * @var array
	 */
	protected $data = array(
		// 'key' => 'value'
	);

	/**
	 * Registers state property.
	 *
	 * @var array
	 */
	protected $state_history = array(
		// 'key' => array()
	);

	/**
	 * Saves value into register.
	 *
	 * @param string $name  register name.
	 * @param mixed  $value register value.
	 */
	public function set_var( $name, $value ) {
		$this->data[ $name ] = $value;
	}

	/**
	 * Checks data if empty then saves value into register.
	 *
	 * @param string $name register name.
	 * @param mixed  $value register value.
	 */
	public function set_var_ifempty( $name, $value ) {
		if ( empty( $this->data[ $name ] ) ) {
			$this->set_var( $name, $value );
		}
	}

	/**
	 * * Saves value into state.
	 *
	 * @param string $name state name.
	 * @param mixed  $value state value.
	 */
	public function push_state( $name, $value ) {
		if ( ! isset( $this->state_history[ $name ] ) ) {
			$this->state_history[ $name ] = array();
		}
		$this->state_history[ $name ][] = $this->get_var( $name );
		$this->set_var( $name, $value );
	}

	/**
	 * Pop a state off the end of the list.
	 *
	 * @param string $name state name.
	 */
	public function pop_state( $name ) {
		if ( ! empty( $this->state_history[ $name ] ) ) {
			$this->set_var( $name, array_pop( $this->state_history[ $name ] ) );
		}
	}

	/**
	 * Appends value into register key.
	 *
	 * @param  string $name  register name.
	 * @param  mixed  $value register value.
	 * @return void
	 */
	public function push_var( $name, $value ) {
		if ( ! isset( $this->data[ $name ] ) ) {
			$this->data[ $name ] = array();
		} elseif ( ! is_array( $this->data[ $name ] ) ) {
			$this->data[ $name ] = array( $this->data[ $name ] );
		}
		$this->data[ $name ][] = $value;
	}

	/**
	 * Returns value stored in register.
	 *
	 * @param  string $name value name.
	 * @param  mixed  $default default value or null.
	 * @return mixed
	 */
	public function get_var( $name, $default = null ) {

		if ( isset( $this->data[ $name ] ) ) {
			return $this->data[ $name ];
		}

		return $default;
	}

}
