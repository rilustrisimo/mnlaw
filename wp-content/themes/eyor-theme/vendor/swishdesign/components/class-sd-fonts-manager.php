<?php
/**
 * Component for generation web fonts defenition rules/links.
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
 * Class SD_Fonts_Manager
 */
class SD_Fonts_Manager extends SD_Component {

	/**
	 * Font families config. Font family should be used as a key.
	 * Each element may include following keys:
	 *     'style'   optional set of allowed styles, array('normal') is a default value
	 *     'weight'  optional set of allowed weights, array('400') is a default value
	 *     'files'   optional set of font files.
	 *
	 * @var array
	 */
	public $font_set = array();

	/**
	 * Convert set of font settings for css that should be included to the document to connect defined font.
	 * Each element of $fonts should
	 *
	 * @param  array $fonts each element should has following keys:
	 *                      'family' - required
	 *                      'style'  - optional, "normal" is default value
	 *                      'weight' - optional, "400" is default value.
	 * @return array  each element will include
	 */
	public function generate_definitions( array $fonts ) {
		$google_api_elements = array();
		$inline_definitions = array();

		foreach ( $fonts as $key => $font_settings ) {
			$family = ! empty( $font_settings['family'] ) ? $font_settings['family'] : '';
			if ( ! $family ) {
				continue;
			}
			$font_config = $this->get_config_by_family( $family );
			$weight = ! empty( $font_settings['weight'] ) ? $font_settings['weight'] : '';
			$style = ! empty( $font_settings['style'] ) ? $font_settings['style'] : '';

			// if font definition has not key 'files' - it is google web font.
			$is_google = empty( $font_config['files'] );

			$google_style_definition = $this->get_unified_font_weight( $weight ) . $style;
			if ( $is_google ) {
				$google_api_elements[ $family ][ $google_style_definition ] = $google_style_definition;
			} else {
				$inline_definitions[ $family . $google_style_definition ] = $this->render_font_family_definition( $font_settings, $font_config );
			}
		}

		$result = array();
		if ( $google_api_elements ) {
			$g_api_families = array();
			foreach ( $google_api_elements as $family => $definitions ) {
				$param_text = str_replace( ' ', '+', $family );

				if ( $definitions ) {
					$param_text .= ':' . join( ',', $definitions );
				}

				$g_api_families[] = $param_text;
			}

			$result['google-fonts'] = array(
				'url' => '//fonts.googleapis.com/css?family=' . join( '|', $g_api_families ),
			);
		}

		if ( $inline_definitions ) {
			$result['inline-fonts'] = array(
				'text' => join( "\n\n", $inline_definitions ),
			);
		}

		return $result;
	}

	/**
	 * Get config by font family.
	 *
	 * @param string $family the font family.
	 * @return array
	 */
	public function get_config_by_family( $family ) {
		if ( $family && isset( $this->font_set[ $family ] ) ) {
			return $this->font_set[ $family ];
		}
		return array();
	}

	/**
	 * Generates @font-face css rules based on the values passed in settings and configuration in $family_config.
	 *
	 * @param  assoc $settings css rules array.
	 * @param  assoc $family_config configuration.
	 * @return string
	 */
	protected function render_font_family_definition( $settings, $family_config ) {
		$files_set = ! empty( $family_config['files'] ) ? $family_config['files'] : array();

		$weight = ! empty( $settings['weight'] ) ? $settings['weight'] : 'regular';
		$style = ! empty( $settings['style'] ) ? $settings['style'] : 'normal';
		$unified_weight = $this->get_unified_font_weight( $weight );
		$is_default_weight = '400' == $unified_weight;

		$render_files = array();

		if ( is_array( $files_set ) ) {
			// $is_flat = count( $files_set ) === count( $files_set, COUNT_RECURSIVE );
			// if ( $is_flat ) $render_files = $files_set;
			$possible_keys = array(
				$weight . '_' . $style,
				$unified_weight . '_' . $style,
			);
			if ( 'normal' == $style ) {
				$possible_keys[] = $weight;
				$possible_keys[] = $unified_weight;

				if ( $is_default_weight ) {
					$possible_keys[] = '';
				}
			}
			if ( $is_default_weight ) {
				$possible_keys[] = $style;
			}

			foreach ( $possible_keys as $possible_key ) {
				if ( isset( $files_set[ $possible_key ] ) ) {
					$render_files = $files_set[ $possible_key ];
					break;
				}
			}
		}


		return $this->render_font_face_definition( $settings['family'], $unified_weight, $style, $render_files );
	}

	/**
	 * Generates font-face definition.
	 *
	 * @param  string       $family font family name.
	 * @param  string       $weight font weight.
	 * @param  string       $style  font style.
	 * @param  array|string $files  set of ulrs related with combination of weight & style or plain css text that defines specific font family
	 * @return string
	 */
	protected function render_font_face_definition( $family, $weight, $style, $files ) {
		//echo "\n\n"; var_export($files); echo "\n\n";
		if ( is_string( $files ) ) {
			if ( strpos( $files, '@font-face' ) !== false ) {
				return $files;
			} else {
				$files = (array) $files;
			}
		}

		$definition_lines = array();

		if ( $files ) {
			// otf => 'opentype'.
			$known_formats = array( 'woff2','woff','truetype','svg', 'eot' );
			foreach ( $files as $_format => $_url ) {
				$detected_format = '';
				if ( $_format && in_array( $_format, $known_formats, true ) ) {
					$detected_format = $_format;
				} else {
					$_matches = null;
					if ( preg_match( '`\.(' . join( '|', $known_formats ) . ')`', $_url, $_matches ) ) {
						$detected_format = $_matches[1];
					}
				}
				if ( $detected_format ) {
					$definition_lines[] = sprintf( 'url("%s") format("%s")', $_url, $detected_format );
				} else {
					$definition_lines[] = sprintf( 'url("%s")', $_url );
				}
			}
		}

		if ( $definition_lines ) {
			return sprintf('@font-face {' .
			               'font-family:"%s";' .
			               'font-style:%s;' .
			               'font-weight:%s;' .
			               'src:%s;' .
			               '}',
				$family,
				$style,
				$weight,
				join( ",\n", $definition_lines )
			);
		} else {
			return '';
		}
	}

	/**
	 * Unified font weight method.
	 *
	 * @param string $weight the font wweight.
	 * @return mixed
	 */
	protected function get_unified_font_weight( $weight ) {
		return str_replace( array( 'normal', 'regular' ), '400', $weight );
	}

}
