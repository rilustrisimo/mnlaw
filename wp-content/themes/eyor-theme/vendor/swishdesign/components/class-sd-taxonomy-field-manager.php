<?php
/**
 * Basic class for custom fields implementation related to terms/taxonomies/posts.
 * Implements basic gui/storing behavior.
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
 * Class SD_Taxonomy_Field_Manager
 */
abstract class SD_Taxonomy_Field_Manager extends SD_Component {

	/**
	 * List taxonomies for use SD_Taxonomy_Field_Manager.
	 *
	 * @var array
	 */
	public $taxonomies = array();

	/**
	 * Swishdesign component
	 *
	 * @var object
	 */
	public $storage;

	/**
	 * Identificator column for html table, where show list categories.
	 *
	 * @var string
	 */
	public $table_column_id = 'sd_field';

	/**
	 * Label for field, where show list taxonomies, "Add new taxonomy" and "Edit taxonomy".
	 *
	 * @var string
	 */
	public $field_label = 'Field';

	/**
	 * Post variable uses for save data.
	 *
	 * @see SD_Taxonomy_Field_Manager::hook_add_form_insert_field(), SD_Taxonomy_Field_Manager::hook_edit_form_insert_field() To set variable.
	 * @see SD_Taxonomy_Field_Manager::hook_save_data() variable processing.
	 * @var string
	 */
	public $post_variable_field_data = 'sd_taxonomy_field_data';

	/**
	 * HTML template for field "Add new taxonomy".
	 */
	abstract public function hook_add_form_insert_field();

	/**
	 * HTML template for field "Edit taxonomy".
	 *
	 * @param object $term
	 */
	abstract public function hook_edit_form_insert_field( $term );

	/**
	 * HTML template for field where show list taxonomies.
	 *
	 * @param stirn $deprecated
	 * @param string $column_name
	 * @param int $termId
	 */
	abstract public function hook_add_table_column_value( $deprecated, $column_name, $termId );

	public function init() {
		if ( parent::init() ) {
			if ( is_admin() && $this->get_taxonomies() && $this->get_storage() ) {
				$this->init_fields();
				$this->init_table_column();
				$this->init_save_data();
				$this->init_remove_data();
			}
			return true;
		}

		return false;
	}

	/**
	 * Initialization field "Add new taxonomy" and "Edit taxonomy".
	 *
	 * @return void
	 */
	protected function init_fields() {
		$taxonomies = $this->get_taxonomies();
		foreach ( $taxonomies as $taxonomy ) {
			add_action( $taxonomy . '_add_form_fields', array( $this, 'hook_add_form_insert_field' ), 10 );
			add_action( $taxonomy . '_edit_form_fields', array( $this, 'hook_edit_form_insert_field' ), 10, 1 );
		}
	}

	/**
	 * Initialization table column where show list taxonomy.
	 *
	 * @return void
	 */
	protected function init_table_column() {
		$taxonomies = $this->get_taxonomies();
		foreach ( $taxonomies as $taxonomy ) {
			// Column title.
			add_action( 'manage_edit-' . $taxonomy . '_columns', array( $this, 'hook_add_table_column_title' ), 10, 1 );

			// Column value.
			add_action( 'manage_' . $taxonomy . '_custom_column', array( $this, 'hook_add_table_column_value' ), 10, 3 );
		}
	}

	/**
	 * Initialize method for save and edit data in database.
	 *
	 * @return void
	 */
	protected function init_save_data() {
		$taxonomies = $this->get_taxonomies();
		foreach ( $taxonomies as $taxonomy ) {
			add_action( 'created_' . $taxonomy, array( $this, 'hook_save_data' ), 10, 2 );
			add_action( 'edited_' . $taxonomy, array( $this, 'hook_save_data' ), 10, 2 );
		}
	}

	/**
	 * Initialize method for remove data from database.
	 *
	 * @return void
	 */
	protected function init_remove_data() {
		$taxonomies = $this->get_taxonomies();
		foreach ( $taxonomies as $taxonomy ) {
			add_action( 'delete_' . $taxonomy, array( $this, 'hook_delete_data' ), 10, 1 );
		}
	}

	/**
	 * Hook save data saves data in database or removes data from database if POST variables == none.
	 *
	 * @param int $term_id
	 * @return void
	 */
	public function hook_save_data( $term_id ) {
		$post_variable = $this->get_post_variable_field_data();
		$taxonomy_data = isset( $_POST[ $post_variable ] ) ? $_POST[ $post_variable ] : false;
		if ( false === $taxonomy_data ) {
			return;
		}

		if ( 'none' == $taxonomy_data ) {
			$this->remove_taxonomy_data( $term_id );
		} else {
			$this->update_taxonomy_data( $term_id, $taxonomy_data );
		}
	}

	/**
	 * Hook delete data removes data from database.
	 *
	 * @param int $term_id
	 * @return void
	 */
	public function hook_delete_data( $term_id ) {
		$this->remove_taxonomy_data( $term_id );
	}

	/**
	 * Hook sets table label, where show list taxonomies.
	 * @param array $columns
	 * @return array
	 */
	public function hook_add_table_column_title( $columns ) {
		$columns[$this->get_table_column_id()] = $this->get_field_label();

		return $columns;
	}

	/**
	 * Function gets taxonomy data from storage.
	 *
	 * @param int $term_id
	 * @return string | false
	 */
	protected function get_taxonomy_data( $term_id ) {
		if ( ! isset( $term_id ) ) {
			return false;
		}

		return $this->get_storage()->get_data( $term_id );
	}

	/**
	 * Function removes data from storage.
	 *
	 * @param int $term_id
	 * @return void
	 */
	protected function remove_taxonomy_data( $term_id ) {
		$this->get_storage()->delete_data( $term_id );
	}

	/**
	 * Function updates data in storage.
	 *
	 * @param int $term_id
	 * @param string $taxonomy_data
	 * @return void
	 */
	protected function update_taxonomy_data( $term_id, $taxonomy_data ) {
		$this->get_storage()->set_data( $term_id, $taxonomy_data );
	}

	public function get_taxonomies() {
		return $this->taxonomies;
	}

	public function get_table_column_id() {
		return $this->table_column_id;
	}

	public function get_field_label() {
		return $this->field_label;
	}

	public function get_storage() {
		return $this->storage;
	}

	public function get_post_variable_field_data() {
		return $this->post_variable_field_data;
	}
}
