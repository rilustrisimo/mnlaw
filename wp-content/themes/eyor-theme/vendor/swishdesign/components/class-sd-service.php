<?php
/**
 * Basic class for services creation (component that should be created only once per app).
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
 * Abstract class SD_Service
 */
abstract class SD_Service {

	abstract public function get_service_id();

	private static $instances = array();

	private $inited = false;

	protected function __construct( array $config = array() ) {
		if ( $config ) {
			$this->set_config( $config );
		}
		$this->init();
	}

	private function __clone() {

	}

	/**
	 * @return SD_Service
	 */
	public static function get_instance( $class = __CLASS__ ) {
		if ( ! isset( self::$instances[ $class ] ) ) {
			self::$instances[ $class ] = new $class();
		}

		return self::$instances[ $class ];
	}

	public function set_config( array $config ) {
		foreach ( $config as $option => $value ) {
			$this->$option = $value;
		}
	}

	/**
	 * Init method.
	 *
	 * @return void
	 */
	protected function init() {
		if ( $this->inited ) {
			return false;
		}
		$this->inited = true;

		do_action( 'sd_service_init', $this, $this->get_service_id() );

		return true;
	}
}
