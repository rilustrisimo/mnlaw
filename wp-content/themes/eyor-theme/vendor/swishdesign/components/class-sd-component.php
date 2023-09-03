<?php
/**
 * Component basic class.
 * Implements configuration functionality.
 *
 * @author    eyorsogood.com, Rouie Ilustrisimo
 * @package   SwishDesign/Components
 * @version   1.0.0
 */

/**
 * No direct access to this file.
 *
 * @since 1.0.0 SwishDesign/Components
 */
defined( 'ABSPATH' ) || die();

/**
 * Class SdComponent
 */
class SD_Component {

	/**
	 * Flag that determines if initiated.
	 *
	 * @access protected
	 * @var bool
	 */
	protected $inited;

	/**
	 * Constructor runs when this class instantiates.
	 *
	 * @param array $config Data via config file.
	 */
	public function __construct( array $config = array() ) {
		if ( $config ) {
			$this->set_config( $config );
		}
		$this->init();
	}

	/**
	 * Init function.
	 *
	 * @return bool
	 */
	public function init() {
		if ( $this->inited ) {
			return false;
		}
		$this->inited = true;
		return true;
	}

	/**
	 * Config options.
	 *
	 * @param array $config Data via consructor.
	 */
	public function set_config( array $config ) {
		foreach ( $config as $option => $value ) {
			$this->$option = $value;
		}
	}
}
