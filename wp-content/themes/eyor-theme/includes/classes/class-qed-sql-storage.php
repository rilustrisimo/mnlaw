<?php
/**
 * Custom db table storage service.
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
 * Class QED_Sql_Storage
 */
class QED_Sql_Storage extends SD_Sql_Storage {

	public $table_name = 'qed_storage';

	// public $check_table_exists = WP_DEBUG;

	private static $_is_active_cache = array();

	public function is_active() {
		$table_name = $this->get_table_name();

		if ( ! isset( self::$_is_active_cache[ $table_name ] ) ) {
			self::$_is_active_cache[ $table_name ] = $this->table_exists();
		}

		return self::$_is_active_cache[ $table_name ];
	}
}
