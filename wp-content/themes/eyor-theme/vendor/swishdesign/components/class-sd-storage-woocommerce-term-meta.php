<?php
/**
 * TODO: Add desc
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
 * Class SD_Storage_Woocommerce_Term_Meta
 */
class SD_Storage_Woocommerce_Term_Meta extends SD_Component implements SD_Storage {

	public $option_name = 'sd_storage_wc_ter_meta';

	public function get_data( $data_id ) {
		return get_woocommerce_term_meta( $data_id, $this->get_option_name(), true );
	}

	public function set_data( $data_id, $data_value ) {
		update_woocommerce_term_meta( $data_id, $this->get_option_name(), $data_value );
	}

	public function delete_data( $data_id ) {
		delete_woocommerce_term_meta( $data_id, $this->get_option_name() );
	}

	public function get_option_name() {
		return $this->option_name;
	}
}
