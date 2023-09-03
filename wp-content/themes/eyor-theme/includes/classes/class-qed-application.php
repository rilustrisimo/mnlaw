<?php
/**
 * Main theme component that contains different core functions related to theme.
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
 * Class QED_Application
 */
class QED_Application extends SD_App {

	/**
	 * List of body classes.
	 *
	 * @var array
	 */
	public $body_classes = array();

	/**
	 * Flag to determine if body classes should be filtered.
	 *
	 * @var bool
	 */
	protected $_body_class_filter_set = false;

	/* body classes management */
	/**
	 * Adds specefined class to body.
	 *
	 * @param string $class the body class.
	 * @return  Theme
	 */
	public function add_body_class( $class ) {
		$this->body_classes[] = $class;
		if ( ! $this->_body_class_filter_set ) {
			add_filter( 'body_class', array( $this, 'body_class_filter' ) );
			$this->_body_class_filter_set = true;
		}
		return $this;
	}

	/**
	 * Filter for wp 'body_class' function.
	 *
	 * @param  array $classes list of classes.
	 * @return array
	 */
	public function body_class_filter( $classes ) {
		if ( $this->body_classes ) {
			foreach ( $this->body_classes as $class ) {
				$classes[] = $class;
			}
		}
		return $classes;
	}
	/* end body classes management */

	/* custom css generation */
	/**
	 * Method to generate custom css.
	 *
	 * @param string    $source style source.
	 * @param array     $theme_mods theme modifications.
	 * @param bool|true $save_as flag to determine if generated css should be saved as a file.
	 *
	 * @return array
	 */
	public function generate_custom_css( $source, array $theme_mods, $save_as = true ) {
		$result = array();

		// fonts including [start].
		$font_options = array();
		// filtering all options that ends with "_font", this settings should contain font family settings.
		foreach ( $theme_mods as $name => $mod_value ) {
			if ( preg_match( '`\_font$`', $name ) ) {
				$font_options[ $name ] = $mod_value;
			}
		}
		if ( $font_options ) {

			$font_manager = new SD_Fonts_Manager( array(
				'font_set' => $this->get_font_set(),
			) );

			if ( $font_definitions = $font_manager->generate_definitions( $font_options ) ) {

				foreach ( $font_definitions as $key => $value ) {
					$result[ 'theme-font-' . $key ] = $value;
				}
			}
		}
		/* Fonts including [end].*/

		$generator = new SD_Less_Processor( array(
			'less_file' => $source,
			'theme_base_dir' => PARENT_DIR,
			'theme_base_url' => PARENT_URL,
			'save_as' => $save_as,
		) );
		if ( $generated_elements = $generator->generate_css( $theme_mods ) ) {
			$result = $result ? array_merge( $result, $generated_elements ) : $generated_elements;
		}
		return $result;
	}

	/**
	 * Method that gets style options.
	 *
	 * @param bool|false $customize_mode flag to determine if customize mode is enabled.
	 * @return array|void
	 */
	public function get_style_options( $customize_mode = false ) {

		// Getting all theme mods.
		$result = $theme_mods = get_theme_mods();

		if ( $customize_mode ) {
			$updated_options = isset( $_POST['customized'] ) ? json_decode( wp_unslash( $_POST['customized'] ), true ) : null;
			if ( $updated_options ) {
				foreach ( $updated_options as $option_name => $value ) {
					$result[ $option_name ] = get_theme_mod( $option_name );
				}
			}
		}

//		$theme_customizer = qed_di( 'theme_customizer' );
//		$font_options = $theme_customizer->get_font_family_settings();
//		if ( $font_options ) {
//			foreach ( $font_options as $option_name ) {
//				if ( ! isset( $result[ $option_name ] ) ) {
//					$result[ $option_name ] = $theme_customizer->get_font_setting_defaults( $option_name );
//				}
//			}
//		}

		return $result;
	}

	/**
	 * Method to get the font set.
	 *
	 * @return mixed
	 */
	public function get_font_set() {
		static $include_cache = null;

		$fs = qed_di( 'register' )->get_var( 'font_set', null );
		if ( ! $fs ) {
			if ( null == $include_cache ) {
				$include_cache = require PARENT_DIR . '/includes/font-set.php';
			}
			return $include_cache;
		}
		return $fs;
	}
	/* end custom css generation */
}
