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
 * Class SD_Sql_Storage
 */
class SD_Sql_Storage extends SD_Component implements SD_Storage {

	public $table_name;

	public $storage_key;

	public $is_cache_allowed;

	public $check_table_exists = false;

	protected $_cache_storage = false;

	public function init() {
		if ( parent::init() ) {
			if ( ! $this->table_name ) {
				throw new Exception( 'Option "table_name" can not be empty.' );
			}
			if ( ! $this->storage_key ) {
				throw new Exception( 'Option "storage_key" can not be empty.' );
			}
			if ( $this->check_table_exists && ! $this->table_exists() ) {
				throw new Exception(
					sprintf( 'Table "%s" does not exist.', $this->get_table_name() )
				);
			}
			return true;
		}
		return false;
	}

	public function get_data( $key_id ) {
		$row = $this->get_row( $key_id );

		return $row ? $row['value'] : null;
	}

	public function set_data( $key_id, $value ) {
		if ( $key_id < 1 ) {
			return;
		}

		global $wpdb;

		$row = $this->get_row( $key_id );

		if ( $row ) {
			if ( $row['value'] != $value ) {
				$wpdb->update(
					$this->get_table_name(),
					array(
						'value' => $value,
					),
					array(
						'storage_key' => $this->storage_key,
						'key_id' => $key_id,
					),
					array(
						'%s',
					),
					array(
						'%s',
						'%d',
					)
				);
			} else {
				return;
			}
		} else {
			$wpdb->insert(
				$this->get_table_name(),
				array(
					'storage_key' => $this->storage_key,
					'key_id' => $key_id,
					'value' => $value,
				),
				array(
					'%s',
					'%d',
					'%s',
				)
			);
		} // End if().

		if ( $this->is_cache_allowed ) {
			$this->reset_cache();
		}
	}

	public function delete_data( $key_id ) {
		if ( $key_id < 1 ) {
			return;
		}

		$row = $this->get_row( $key_id );

		if ( $row ) {
			global $wpdb;

			$wpdb->delete(
				$this->get_table_name(),
				array(
					'storage_key' => $this->storage_key,
					'key_id' => $key_id,
				),
				array(
					'%s',
					'%d',
				)
			);

			if ( $this->is_cache_allowed ) {
				$this->reset_cache();
			}
		}
	}

	public function clear_all( ) {
		global $wpdb;

		$wpdb->delete(
			$this->get_table_name(),
			array(
				'storage_key' => $this->storage_key,
			),
			array(
				'%s'
			)
		);

		if ( $this->is_cache_allowed ) {
			$this->reset_cache();
		}
	}

	public function get_all( ) {
		$cached = $this->is_cache_allowed ? $this->get_cache() : false;
		if ( false !== $cached ) {
			return $cached;
		}

		$result = array();
		global $wpdb;

		$rows = $wpdb->get_results( $wpdb->prepare('SELECT `key_id`, `value` FROM `' . $this->get_table_name() .'` ' .
		                                           'WHERE `storage_key` = %s',
			$this->storage_key
		), ARRAY_A );

		if ( $rows ) {
			foreach ( $rows as $record ) {
				$result[ $record['key_id'] ] = $record['value'];
			}
		}

		if ( $this->is_cache_allowed ) {
			$this->set_cache( $result );
		}

		return $result;
	}

	protected function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . $this->table_name;
	}

	protected function get_row( $key_id ) {
		if ( $key_id < 1 ) {
			return null;
		}

		if ( $this->is_cache_allowed ) {
			$all_records = $this->get_all();
			$result_row = null;
			if ( isset( $all_records[ $key_id ] ) ) {
				return array(
					'key_id' => $key_id,
					'value' => $all_records[ $key_id ]['value'],
				);
			}
			return $result_row;
		} else {
			global $wpdb;

			$row = $wpdb->get_row( $wpdb->prepare('SELECT `key_id`, `value` FROM `' . $this->get_table_name() .'` ' .
			                                      'WHERE `storage_key` = %s AND `key_id` = %d',
				$this->storage_key,
				$key_id
			), ARRAY_A );

			return $row;
		}
	}

	protected function table_exists(){
		global $wpdb;
		$name = $this->get_table_name();
		return $wpdb->get_var("SHOW TABLES LIKE '$name'") == $name;
	}

	public function get_cache( ) {
		return $this->_cache_storage;
	}

	public function set_cache( $value ) {
		$this->_cache_storage = $value;
	}

	public function reset_cache() {
		if ( false !== $this->_cache_storage ) {
			$this->_cache_storage = false;
		}
	}
}
