<?php
/**
 * Class for generation css file based on the less file and values of the variables those can be passed to the variables file.
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
 * Class SD_Less_Processor
 */
class SD_Less_Processor extends SD_Component {

	/**
	 * Theme base directory
	 *
	 * @var string
	 */
	public $theme_base_dir;

	/**
	 * Theme base url
	 *
	 * @var string
	 */
	public $theme_base_url;

	/**
	 * Path to the view less file that should be used for css generation.
	 * Path related to the theme location.
	 *
	 * @example
	 * <pre>
	 * /assets/css/style.less
	 * </pre>
	 * @var string
	 */
	public $less_file = '';

	/**
	 * If compiled css should be saved as file (otherwise will be outputed as inline style).
	 *
	 * @var boolean
	 */
	public $save_as = false;

	/**
	 * FLag to determine if we need to generate a token for the css file.
	 *
	 * @var bool
	 */
	public $generate_token_for_css_file = true;

	/**
	 * Variable file marker for the less files.
	 *
	 * @var string
	 */
	public $variables_file_marker = '@variables-file';

	/**
	 * Variable meta tag for the less files.
	 *
	 * @var string
	 */
	public $variable_meta_tag = '@theme_option\:';

	/**
	 * Generate css.
	 *
	 * @param array|assoc $theme_settings theme settings array|object.
	 * @param bool|false  $plain_css flag to determine if plain css.
	 * @return array|string
	 * @throws Exception If can't create dir.
	 */
	public function generate_css( $theme_settings, $plain_css = false ) {
		$css_text = '';
		$compiled_from_less = false;

		if ( $this->less_file ) {
			$compiled_from_less = true;

			$css_text = $this->render_less_file(
				$this->get_theme_file_path( $this->less_file ),
				$theme_settings
			);

			if ( ! empty( $theme_settings['custom_css_text'] ) ) {
				$css_text .= "\n" . $theme_settings['custom_css_text'];
			}
		}

		if ( $plain_css ) {
			return $css_text;
		}

		$parts = array();

		if ( $css_text ) {
			$add_as_inline = true;
			$css_id = 'style-css';
			if ( $compiled_from_less && $this->save_as ) {

				$new_css_theme_filename = $this->save_as;

				// $css_full_filepath = $this->get_theme_file_path($new_css_theme_filename);
				$new_css_fileinfo = $this->generate_css_fileinfo( $new_css_theme_filename );

				if ( $new_css_fileinfo && file_put_contents( $new_css_fileinfo['path'], $css_text ) ) {
					$add_as_inline = false;
					// $cache_token = $this->generate_token_for_css_file ? '?ct=' . time() : '';
					// $style_url = $this->get_theme_file_url($new_css_theme_filename);
					// $parts[] = '<link id="customCss" href="' . esc_url($style_url). '" rel="stylesheet" />';
					$parts[ $css_id ] = array(
						'url' => $new_css_fileinfo['url'],
					);
				}
			}

			if ( $add_as_inline ) {
				$parts[ $css_id ] = array(
					'text' => $css_text,
				);
			}
		}

		return $parts;
	}

	/**
	 * Getter for theme file path.
	 *
	 * @param string $local_path local file path.
	 * @return string
	 */
	protected function get_theme_file_path( $local_path ) {
		return $this->theme_base_dir . $local_path;
	}

	/**
	 * Getter for theme file url.
	 *
	 * @param string $local_path local file path.
	 * @return string
	 */
	protected function get_theme_file_url( $local_path ) {
		return $this->theme_base_url . $local_path;
	}

	/**
	 * Returns the generated css file info.
	 *
	 * @param string $postfix
	 * @return array|null
	 * @throws Exception If can't create dir.
	 */
	protected function generate_css_fileinfo( $postfix = '' ) {
		$upload_dir = wp_upload_dir();
		$file_folder_relative_path = '/' . basename( get_template_directory() ) . '-assets/';
		$file_folder = $upload_dir['basedir'] . $file_folder_relative_path;

		if ( ! is_dir( $file_folder ) ) {
			if ( ! mkdir( $file_folder, 0755, true ) && WP_DEBUG ) {
				throw new Exception(strtr('Can not create folder {path}.', array(
					'{path}' => $file_folder,
				)));
			}
		}

		if ( $postfix ) {
			$postfix = preg_replace( '/\.css$/', '', $postfix );
		}
		if ( strlen( $postfix ) < 5 ) {
			$postfix = time() . '-' . rand( 100,999 );
		}
		$file_name = $postfix . '.css';

		if ( is_dir( $file_folder ) ) {
			return array(
				'path' => $file_folder . $file_name,
				'url' => $upload_dir['baseurl'] . $file_folder_relative_path . $file_name,
			);
		}

		return null;
	}

	/**
	 * Returns rendered less file.
	 *
	 * @param string    $full_less_filepath the filepath.
	 * @param array     $theme_settings settings.
	 * @param bool|true $compressed flag to determine if it should be compressed.
	 *
	 * @return string
	 */
	protected function render_less_file( $full_less_filepath, array $theme_settings, $compressed = true ) {
		$less_variables = $this->get_less_variables( $full_less_filepath, $theme_settings );

		$new_values_text = array();

		foreach ( $less_variables as $option_name => $details ) {
			$txt_value = $details['value'];

			// grouping options related to the font settings.
			// to load related fonts later.
			$parse_res = null;
			if ( preg_match( '`(\w+)\_font\[(\w+)\]`', $option_name, $parse_res ) ) {
				$font_group = $parse_res[1];
				$font_option = $parse_res[2];
				$fonts[ $font_group ][ $font_option ] = $details['value'];
				if ( 'weight' == $font_option ) {
					$txt_value = str_replace( array( 'normal', 'regular' ), '400', $txt_value );
				} elseif ( 'family' == $font_option && preg_match( '`\s+`', $txt_value ) ) {
					$txt_value = "'{$txt_value}'";
				}
			}
			if ( $txt_value ) {
				$new_values_text[] = $details['less_name'] . ':' . $txt_value . ';';
			}
		}

		$less = $this->create_less_processor();
		$less->addImportDir( dirname( $full_less_filepath ) );
		if ( $compressed ) {
			$less->setFormatter( 'compressed' );
		}

		$less_content = file_get_contents( $full_less_filepath );

		$upload_dir_info = wp_upload_dir();
		$tmp_file = tempnam( $upload_dir_info['basedir'], 'less-vars' );
		$tmp_base_name = basename( $tmp_file );

		file_put_contents( $tmp_file, join( "\n",$new_values_text ) );
		$less_content = preg_replace(
			'`\/\*\s*@variables-file\s*\*\/`',
			"\n@import '{$tmp_base_name}';\n",
			$less_content
		);

		$less->addImportDir( dirname( $tmp_file ) );

		$compiled_css_text = $less->compile( $less_content );

		unlink( $tmp_file );

		return $compiled_css_text;
	}

	/**
	 * Returns list of variables that can be set for the passed less file.
	 * Each one has following keys:
	 * 	- 'less_name'   string - name of the less variable (contains leading @)
	 * 	- 'option_name' string - name of the theme option related with variable
	 * 	- 'value'       string - that should be sent to the less file
	 * 	- 'rawValue'    mixed  - optional, will be defined if corresponding option in theme options is not a string
	 * 	- 'default'     string - default value that currently set in the less file
	 *
	 * @param  string $full_less_filepath full path to the less file.
	 * @param  array  $theme_settings    values of the theme options.
	 * @return array
	 */
	public function get_less_variables( $full_less_filepath, array $theme_settings ) {
		$result = array();

		$variables_file = $this->get_variables_file_from_less_file( $full_less_filepath );
		$parsed_options = $this->parse_available_options_from_variables_file( $variables_file );

		if ( $parsed_options ) {
			foreach ( $parsed_options as $_option_name => $details ) {

				$option_name = null;
				$option_subkey = null;
				$parse_results = null;

				if ( preg_match( '/(\w+)\[(\w+)\]/', $_option_name, $parse_results ) ) {
					$option_name = $parse_results[1];
					$option_subkey = $parse_results[2];
				} else {
					$option_name = $_option_name;
				}

				$raw_value = isset( $theme_settings[ $option_name ] ) ? $theme_settings[ $option_name ] : null;
				if ( $option_subkey && $raw_value ) {
					$raw_value = isset( $raw_value[ $option_subkey ] ) ? $raw_value[ $option_subkey ] : null;
				}

				$text_value = $this->convert_theme_option_value_for_less( $raw_value, $_option_name );

				$details['value'] = $text_value;
				if ( $text_value != $raw_value ) {
					$details['rawValue'] = $raw_value;
				}

				$result[ $_option_name ] = $details;
			}
		}
		return $result;
	}

	/**
	 * Filtering function used to convert some option value before rendering it to the less variable.
	 *
	 * @param  mixed  $raw_value     value of the $option_name from the theme settings.
	 * @param  string $option_name  name of the option.
	 * @return mixed
	 */
	public function convert_theme_option_value_for_less( $raw_value, $option_name ) {
		if ( $raw_value ) {
			if ( is_array( $raw_value ) ) { // && preg_match('/_font_size$/', $option_name)
				return join( '', $raw_value );
			}
		}
		return $raw_value;
	}

	/**
	 * Parses variables file to find all definition of options.
	 * Relation between less variable and option should be defined via \/\*@theme_option:OPTION_NAME \*\/ meta comment.
	 *
	 * @param  string $variables_file path to the less variables file.
	 * @return assoc
	 */
	protected function parse_available_options_from_variables_file( $variables_file ) {
		$content = file_get_contents( $variables_file );
		$result = array();

		$parse_res = null;
		$meta_tag = $this->variable_meta_tag;
		if ( preg_match_all( '`\@(\S+)\:\s*([^;]+);[\ ]*\/\*\s*' . $meta_tag . '\s*(\S+)\s*\*\/`', $content, $parse_res ) ) {
			foreach ( $parse_res[0] as $index => $full_text ) {
				$option_name = $parse_res[3][ $index ];
				$result[ $option_name ] = array(
					'less_name' => '@' . $parse_res[1][ $index ],
					'option_name' => $option_name,
					'default' => $parse_res[2][ $index ],
				);
			}
		}
		return $result;
	}

	/**
	 * Searches for the variables file inside the passed less file.
	 * Variables file should be marked in the following way:
	 * <pre>
	 * \@import 'variables.less';\/\* @variables-file \*\/
	 * </pre>
	 *
	 * @param  string $less_file_path path to the less file.
	 * @return string               path to the less file that defines all variables required for less.
	 * @throws Exception If filepath doesn't exist.
	 */
	protected function get_variables_file_from_less_file( $less_file_path ) {
		$meta_comment = $this->variables_file_marker ? $this->variables_file_marker : '@variables-file';

		$result = null;

		$parse_res = null;
		if ( preg_match( '`@import\s*(.*);\s*\/\*\s*' . $meta_comment . '\s*\*\/`', file_get_contents( $less_file_path ), $parse_res ) ) {
			$related_path = $parse_res[1];
			$result = dirname( $less_file_path ) . '/' . trim( $related_path, '\"\'' );
		};

		if ( ! $result ) {
			throw new Exception( 'Variables file has not been found.' );
		} elseif ( ! file_exists( $result ) ) {
			throw new Exception(strtr('File {filePath} does not exist.', array(
				'{filePath}' => $result,
			)));
		}

		return $result;
	}

	/**
	 * Creates instance of the less processor used for the less file compilation.
	 *
	 * @return lessc
	 */
	protected function create_less_processor() {
		$less_processor = new lessc;
		SD_Lessc_Plugin::get_instance()->inject( $less_processor );
		return $less_processor;
	}

}
