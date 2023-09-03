<?php
/**
 * Class for parsing font file for css classes that defines icons.
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
 * Class SD_Font_Icons_Manager
 */
class SD_Font_Icons_Manager extends SD_Component {

	/**
	 * Url to the font file.
	 *
	 * @var string
	 */
	public $font_file_url = '';

	/**
	 * Flag that determines if font file should be registered in styles list.
	 *
	 * @var bool
	 */
	public $register_in_assets = true;

	/**
	 * Pattern for dispatching classes that allow define icons.
	 * Class name should be in 1-st match.
	 *
	 * @example:
	 * <pre>'/\.(td-(?:\w+(?:-)?)+):before\s*{\s*content/'</pre>
	 *
	 * @var string
	 */
	public $pattern = '';

	/**
	 * Caching key used to store cached results in transients.
	 *
	 * @var string
	 */
	public $cache_key = '';

	/**
	 * Caching time in seconds.
	 *
	 * @var integer
	 */
	public $cache_time = 86400; // 60 * 60 * 24

	/**
	 * Init
	 *
	 * @return bool
	 */
	public function init() {
		if ( ! parent::init() ) {
			return false;
		}
		if ( $this->register_in_assets ) {
			$file_url = $this->get_font_file_url();
			if ( $file_url ) {
				if ( is_admin() ) {
					add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ) );
				} else {
					add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
				}
			}
		}

		return true;
	}

	/**
	 * Register assets.
	 */
	public function register_assets() {
		$file_url = $this->get_font_file_url();
		if ( $file_url ) {
			wp_enqueue_style( 'icons-font-' . md5( $file_url ), $file_url );
		}
	}

	/**
	 * Get the list of classes.
	 *
	 * @return array
	 */
	public function get_list() {
		return $this->get_icon_classes();
	}

	/**
	 * Get the font file.
	 *
	 * @return string
	 */
	public function get_font_file_url() {
		return $this->font_file_url;
	}

	/**
	 * Get icons from font file.
	 *
	 * @return array
	 */
	public function get_icon_classes() {
		$cache_key = $this->cache_time ? $this->cache_key : null;

		$icons = $cache_key ? get_transient( $cache_key ) : false;

		if ( false === $icons && $this->pattern ) {
			$icons = array();
			$font_file = $this->get_font_file_url();
			$file_path = null;

			// if file located in theme - url will be converted into full file path, so file will loaded withou http request.
			if ( $font_file ) {
				$try_file_path = str_replace(
					get_template_directory_uri(),
					get_template_directory(),
					$font_file
				);
				if ( $try_file_path && file_exists( $try_file_path ) ) {
					$file_path = $try_file_path;
				}
			}

			$content = $font_file ? file_get_contents( $file_path ? $file_path : $font_file ) : null;
			if ( $content ) {
				preg_match_all( $this->pattern, $content, $matches, PREG_SET_ORDER );

				if ( $matches ) {
					foreach ( $matches as $match ) {
						$icons[] = array(
								'value' => $match[1],
								'label' => $match[1],
						);
					}
				}

				if ( $cache_key ) {
					set_transient( $cache_key, $icons,  $this->cache_time );
				}
			}
		}

		return $icons ? $icons : array();
	}

}
