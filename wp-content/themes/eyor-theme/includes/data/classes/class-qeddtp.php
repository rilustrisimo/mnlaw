<?php
/**
 * Defines custom post types, taxonomies and shortcodes for Swish Design theme.
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
 * Class QEDDTP
 */
class QEDDTP {

	/**
	 * Set of available services.
	 *
	 * @var assoc
	 */
	protected $services = array();

	/**
	 * @var QEDDTP
	 */
	private static $instance;

	/**
	 * Returns instance of the container.
	 *
	 * @return QEDDTP
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
			self::$instance->init();
		}

		return self::$instance;
	}

	/**
	 * @return QEDDTP_Shortcodes_Helper
	 */
	public function shortcodes_helper() {
		return $this->services['shortcodes_helper'];
	}

	/**
	 * @return QEDDTP_Storage_Installer
	 */
	public function storage_installer() {
		if ( empty( $this->services['storage_installer'] ) ) {
			//$this->require_file( '/classes/class-qeddtp-storage-installer.php' );
			$this->services['storage_installer'] = new QEDDTP_Storage_Installer();
		}

		return $this->services['storage_installer'];
	}

	/**
	 * Requires by relative path.
	 *
	 * @param  string $local_path
	 * @return void
	 */
	public function require_file( $local_path ) {
		$full_path = QEDDTP_PATH . $local_path;
		//if ( ! file_exists( $full_path ) ) return;
		require $full_path;
	}

	/**
	 * Loads and inits all related services.
	 *
	 * @return void
	 */
	protected function init() {
		//$this->require_file( '/classes/class-qeddtp-data-types-registrator.php' );
		//$this->require_file( '/classes/class-qeddtp-shortcodes-helper.php' );

		$this->services['data_registrator'] = new QEDDTP_Data_Types_Registrator();
		$this->services['shortcodes_helper'] = new QEDDTP_Shortcodes_Helper();

		add_action( 'after_switch_theme', array( $this, 'hook_storage_install' ) );
		add_action( 'switch_theme', array( $this, 'hook_storage_uninstall' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'action_load_assets' ) );
	}

	public function hook_storage_install() {
		$this->storage_installer()->do_install();
	}

	public function hook_storage_uninstall() {
		$this->storage_installer()->do_uninstall();
	}

	public function action_load_assets() {
		// If plugin used with main theme - default styles are not required.
		if ( function_exists( 'qed_init_theme_assets' ) ) {
			return;
		}
		wp_enqueue_style( 'qeddtp-plugin-shortcodes', $this->get_datatypes_url() . 'assets/css/style.css', null, QED_VERSION );
		wp_enqueue_style( 'qeddtp-plugin-font-awesome', $this->get_datatypes_url() . 'assets/css/font-awesome.min.css', null, QED_VERSION );
	}

	public function get_datatypes_url() {
		return PARENT_URL . '/data-types/';
	}

	/**
	 * Constructor.
	 */
	protected function __construct() {
	}

	/**
	 * Clone if forbidden.
	 *
	 * @return void
	 */
	private function __clone() {
	}

	/**
	 * Unserialize if forbidden.
	 *
	 * @return void
	 */
	private function __wakeup() {
	}
}
