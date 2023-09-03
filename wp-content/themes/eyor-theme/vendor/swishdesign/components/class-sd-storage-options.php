<?php
/**
 * [Replace with file description]
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
 * Class SD_Storage_Options
 */
class SD_Storage_Options extends SD_Component implements SD_Storage {

	public $option_name = 'td_storage_options';

	public function get_data( $data_id ) {
		$data = get_option( $this->get_option_name() );
		return isset( $data[ $data_id ] ) ? $data[ $data_id ] : false;
	}

	public function set_data( $data_id, $data_value ) {
		$option_name = $this->get_option_name();
		$data = get_option( $option_name );

		if ( ! $data ) {
			$data = array();
		}

		$data[ $data_id ] = $data_value;
		update_option( $option_name, $data );
	}

	public function deleteData( $data_id ) {
		$option_name = $this->get_option_name();
		$data = get_option( $option_name );

		if ( isset( $data[ $data_id ] ) ) {
			unset( $data[ $data_id ] );

			if ( count( $data ) > 0 ) {
				update_option( $option_name, $data );
			} else {
				delete_option( $option_name );
			}
		}
	}

	public function get_option_name() {
		return $this->option_name;
	}
}
