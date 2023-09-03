<?php
/**
 * Theme customize options.
 * Component that uses wordpress customization API to implement customization options that allows configuration of theme visual presentation.
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
 * Class QED_Theme_Customizer
 */
class QED_Theme_Customizer extends SD_Component {

	/**
	 * Set of fonts available for selection of the theme text elements.
	 *
	 * @var array
	 */
	public $font_set = array();

	/**
	 * Wordpress customize manager object.
	 *
	 * @var WP_Customize_Manager
	 */
	protected $wp_customizer;

	/**
	 * Default font set.
	 *
	 * @var array
	 */
	public $font_default_settings = array(
		'main_font' => array(
			'family' => 'Raleway',
			'weight' => 'regular',
			'style' => 'normal',
		),
		'heading_font' => array(
			'family' => 'Raleway',
			'weight' => 'regular',
			'style' => 'normal',
		),
		'heading_one_font' => array(
			'family' => 'harfang-pro',
			'weight' => 400,
			'style' => 'normal',
		),
		'heading_two_font' => array(
			'family' => 'Fira Sans',
			'weight' => 'regular',
			'style' => 'italic',
		),
		'heading_three_font' => array(
			'family' => 'Raleway',
			'weight' => 700,
			'style' => 'normal',
		),
		'heading_four_font' => array(
			'family' => 'Raleway',
			'weight' => 500,
			'style' => 'normal',
		),
		'heading_five_font' => array(
			'family' => 'Raleway',
			'weight' => 300,
			'style' => 'normal',
		),
		'heading_six_font' => array(
			'family' => 'Raleway',
			'weight' => 700,
			'style' => 'normal',
		),
		'alternative_text_font' => array(
			'family' => 'Fira Sans',
			'weight' => 'regular',
			'style' => 'normal',
		),
	);

	/**
	 * Init method.
	 *
	 * @return bool
	 */
	public function init() {
		if ( parent::init() ) {
			if ( THEME_IS_DEV_MODE ) {
				add_action( 'customize_register', array( $this, 'hook_on_customize_register' ) );

				if ( is_admin() ) {
					add_action( 'customize_controls_print_scripts', array( $this, 'action_customize_controls_print_scripts' ) );
					add_action( 'wp_ajax_customizer_reset', array( $this, 'action_ajax_reset' ) );

					// add_action( 'customize_preview_init', array( $this, 'hook_registerPreviewAssets' ) );
					// add_action( 'customize_controls_enqueue_scripts', array( $this, 'hook_registerManageAssets' ) );
				}
			}

			$this->font_default_settings = apply_filters( 'eyor_default_fonts', $this->font_default_settings );

			return true;
		}
		return false;
	}

	/**
	 * The customizer settings that is added on the customize section.
	 *
	 * @return array
	 */
	public function get_customizer_settings() {

		return array(
			'typography' => array(
				'config' => array(
					'title' => esc_html__( 'Typography', 'swishdesign' ),
				),
				'fields' => array(
					'main_font' => array(
						'label' => esc_html__( 'Main Font', 'swishdesign' ),
						'field_type' => 'swishdesign_font',
						'default' => $this->get_font_setting_defaults( 'main_font' ),
					),
					'main_font_size' => array(
						'label' => esc_html__( 'Size', 'swishdesign' ),
						'as_subfield' => true,
						'field_type' => 'swishdesign_font_size',
						'default' => array(
							'size' => '16',
							'unit' => 'px',
						),
					),
					'main_line_height' => array(
							'label' => esc_html__( 'Main Line Height', 'swishdesign' ),
							'as_subfield' => true,
							'field_type' => 'swishdesign_line_height',
							'default' => array(
									'height_size' => '1.5',
									'height_unit' => 'em',
							),
					),
					'main_font_color' => array(
							'label' => esc_html__( 'Main Font Color', 'swishdesign' ),
							'field_type' => 'color',
							'default' => '#595660',
					),
					'alternative_text_font' => array(
						'label' => esc_html__( 'Alternative Text Font', 'swishdesign' ),
						'field_type' => 'swishdesign_font',
						'default' => $this->get_font_setting_defaults( 'alternative_text_font' ),
					),
					'heading_font' => array(
							'label' => esc_html__( 'Heading Font', 'swishdesign' ),
							'field_type' => 'swishdesign_font',
							'default' => $this->get_font_setting_defaults( 'heading_font' ),
					),
					'heading_one_font' => array(
						'label' => esc_html__( 'Heading Level 1 Font', 'swishdesign' ),
						'field_type' => 'swishdesign_font',
						'default' => $this->get_font_setting_defaults( 'heading_one_font' ),
					),
					'heading_one_font_size' => array(
							'label' => esc_html__( 'Size', 'swishdesign' ),
							'as_subfield' => true,
							'field_type' => 'swishdesign_font_size',
							'default' => array(
									'size' => '28',
									'unit' => 'px',
							),
					),
					'heading_one_text_transform' => array(
							'label' => esc_html__( 'Text Transform', 'swishdesign' ),
							'as_subfield' => true,
							'field_type' => 'swishdesign_text_transform',
							'default' => array(
									'transform' => 'lowercase',
							),
					),
					'heading_one_font_color' => array(
							'label' => esc_html__( 'Heading Level 1 Font Color', 'swishdesign' ),
							'field_type' => 'color',
							'default' => '#003c7e',
					),
					'heading_two_font' => array(
							'label' => esc_html__( 'Heading Level 2 Font', 'swishdesign' ),
							'field_type' => 'swishdesign_font',
							'default' => $this->get_font_setting_defaults( 'heading_two_font' ),
					),
					'heading_two_font_size' => array(
							'label' => esc_html__( 'Size', 'swishdesign' ),
							'as_subfield' => true,
							'field_type' => 'swishdesign_font_size',
							'default' => array(
									'size' => '18',
									'unit' => 'px',
							),
					),
					'heading_two_text_transform' => array(
							'label' => esc_html__( 'Text Transform', 'swishdesign' ),
							'as_subfield' => true,
							'field_type' => 'swishdesign_text_transform',
							'default' => array(
									'transform' => 'none',
							),
					),
					'heading_two_font_color' => array(
							'label' => esc_html__( 'Heading Level 2 Font Color', 'swishdesign' ),
							'field_type' => 'color',
							'default' => '#353535',
					),
					'heading_three_font' => array(
							'label' => esc_html__( 'Heading Level 3 Font', 'swishdesign' ),
							'field_type' => 'swishdesign_font',
							'default' => $this->get_font_setting_defaults( 'heading_three_font' ),
					),
					'heading_three_font_size' => array(
							'label' => esc_html__( 'Size', 'swishdesign' ),
							'as_subfield' => true,
							'field_type' => 'swishdesign_font_size',
							'default' => array(
									'size' => '20',
									'unit' => 'px',
							),
					),
					'heading_three_text_transform' => array(
							'label' => esc_html__( 'Text Transform', 'swishdesign' ),
							'as_subfield' => true,
							'field_type' => 'swishdesign_text_transform',
							'default' => array(
									'transform' => 'uppercase',
							),
					),
					'heading_three_font_color' => array(
							'label' => esc_html__( 'Heading Level 3 Font Color', 'swishdesign' ),
							'field_type' => 'color',
							'default' => '#005dab',
					),
					'heading_four_font' => array(
							'label' => esc_html__( 'Heading Level 4 Font', 'swishdesign' ),
							'field_type' => 'swishdesign_font',
							'default' => $this->get_font_setting_defaults( 'heading_four_font' ),
					),
					'heading_four_font_size' => array(
							'label' => esc_html__( 'Size', 'swishdesign' ),
							'as_subfield' => true,
							'field_type' => 'swishdesign_font_size',
							'default' => array(
									'size' => '18',
									'unit' => 'px',
							),
					),
					'heading_four_text_transform' => array(
							'label' => esc_html__( 'Text Transform', 'swishdesign' ),
							'as_subfield' => true,
							'field_type' => 'swishdesign_text_transform',
							'default' => array(
									'transform' => 'none',
							),
					),
					'heading_four_font_color' => array(
							'label' => esc_html__( 'Heading Level 4 Font Color', 'swishdesign' ),
							'field_type' => 'color',
							'default' => '#005dab',
					),
					'heading_five_font' => array(
							'label' => esc_html__( 'Heading Level 5 Font', 'swishdesign' ),
							'field_type' => 'swishdesign_font',
							'default' => $this->get_font_setting_defaults( 'heading_five_font' ),
					),
					'heading_five_font_size' => array(
							'label' => esc_html__( 'Size', 'swishdesign' ),
							'as_subfield' => true,
							'field_type' => 'swishdesign_font_size',
							'default' => array(
									'size' => '22',
									'unit' => 'px',
							),
					),
					'heading_five_text_transform' => array(
							'label' => esc_html__( 'Text Transform', 'swishdesign' ),
							'as_subfield' => true,
							'field_type' => 'swishdesign_text_transform',
							'default' => array(
									'transform' => 'uppercase',
							),
					),
					'heading_five_font_color' => array(
							'label' => esc_html__( 'Heading Level 5 Font Color', 'swishdesign' ),
							'field_type' => 'color',
							'default' => '#353535',
					),
					'heading_six_font' => array(
							'label' => esc_html__( 'Heading Level 6 Font', 'swishdesign' ),
							'field_type' => 'swishdesign_font',
							'default' => $this->get_font_setting_defaults( 'heading_six_font' ),
					),
					'heading_six_font_size' => array(
							'label' => esc_html__( 'Size', 'swishdesign' ),
							'as_subfield' => true,
							'field_type' => 'swishdesign_font_size',
							'default' => array(
									'size' => '18',
									'unit' => 'px',
							),
					),
					'heading_six_text_transform' => array(
							'label' => esc_html__( 'Text Transform', 'swishdesign' ),
							'as_subfield' => true,
							'field_type' => 'swishdesign_text_transform',
							'default' => array(
									'transform' => 'none',
							),
					),
					'heading_six_font_color' => array(
							'label' => esc_html__( 'Heading Level 6 Font Color', 'swishdesign' ),
							'field_type' => 'color',
							'default' => '#005dab',
					),
					'links_color' => array(
						'label' => esc_html__( 'Link Color', 'swishdesign' ),
						'field_type' => 'color',
						'default' => '#353535',
					),
					'links_hover' => array(
							'label' => esc_html__( 'Link Hover', 'swishdesign' ),
							'field_type' => 'color',
							'default' => '#005dab',
					),
				),
			), // end of Typography.
			'general' => array(
				'config' => array(
					'title' => esc_html__( 'General Colors', 'swishdesign' ),
				),
				'fields' => array(
					'main_color' => array(
						'label' => esc_html__( 'Main color', 'swishdesign' ),
						'field_type' => 'color',
						'default' => '#47a0ff',
					),
					'accent_color1' => array(
						'label' => esc_html__( 'Accent color 1', 'swishdesign' ),
						'field_type' => 'color',
						'default' => '#ff47a0',
					),
					'accent_color2' => array(
						'label' => esc_html__( 'Accent color 2', 'swishdesign' ),
						'field_type' => 'color',
						'default' => '#01cb68',
					),
//					'forms_bg' => array(
//						'label' => esc_html__( 'Form background', 'swishdesign' ),
//						'field_type' => 'color',
//						'default' => '#47a0ff',
//					),
//					'forms_button_bg' => array(
//						'label' => esc_html__( 'Form button background', 'swishdesign' ),
//						'field_type' => 'color',
//						'default' => '#006fe6',
//					),
				),
			),// end of General.
			'header' => array(
				'config' => array(
					'title' => esc_html__( 'Header Colors', 'swishdesign' ),
				),
				'fields' => array(
					'header_bg' => array(
						'label' => esc_html__( 'Background color', 'swishdesign' ),
						'field_type' => 'color',
						'default' => '#ffffff',
					),
					'header_text_color' => array(
						'label' => esc_html__( 'Text color', 'swishdesign' ),
						'field_type' => 'color',
						'default' => '#353535',
					),
					'header_text_color_hover' => array(
							'label' => esc_html__( 'Text color hover', 'swishdesign' ),
							'field_type' => 'color',
							'default' => '#005dab',
					),
				),
			),// end of Header.
			'footer' => array(
				'config' => array(
					'title' => esc_html__( 'Footer Colors', 'swishdesign' ),
				),
				'fields' => array(
					'footer_bg' => array(
						'label' => esc_html__( 'Background color', 'swishdesign' ),
						'field_type' => 'color',
						'default' => '#e1e1e1',
					),
					'footer_heading_color' => array(
						'label' => esc_html__( 'Heading color', 'swishdesign' ),
						'field_type' => 'color',
						'default' => '#777777',
					),
					'footer_text_color' => array(
						'label' => esc_html__( 'Text color', 'swishdesign' ),
						'field_type' => 'color',
						'default' => '#777777',
					),
					'footer_links_color' => array(
						'label' => esc_html__( 'Link color', 'swishdesign' ),
						'field_type' => 'color',
						'default' => '#777777',
					),
					'footer_links_color_hover' => array(
							'label' => esc_html__( 'Link color hover', 'swishdesign' ),
							'field_type' => 'color',
							'default' => '#777777',
					),
				),
			),// end of Footer.
		);
	}

	/**
	 * Returns the font set.
	 *
	 * @return assoc
	 */
	public function get_font_set() {
		if ( ! $this->font_set ) {
			return qed_di( 'app' )->get_font_set();
		}
		return $this->font_set;
	}

	/**
	 * Returns list of options that contain font family settings used during the css generation process.
	 *
	 * @return array
	 */
	public function get_font_family_settings() {
		return array_keys( $this->font_default_settings );
	}

	/**
	 * Returns default value for option with font family settings.
	 *
	 * @param  string $option_name the option name.
	 * @return assoc
	 */
	public function get_font_setting_defaults( $option_name ) {

		if ( isset( $this->font_default_settings[ $option_name ] ) ) {
			return $this->font_default_settings[ $option_name ];
		} else {
			$return_data = array(
				'family' => 'Lato',
				'weight' => 'regular',
				'style' => 'normal',
			);
			switch ( $option_name ) {
				case 'main_font':
					$return_data = array(
						'family' => 'Raleway',
						'weight' => 'regular',
						'style' => 'normal',
					);
					break;
				case 'alternative_text_font':
					$return_data = array(
							'family' => 'Fira Sans',
							'weight' => 'regular',
							'style' => 'normal',
					);
					break;
				case 'heading_font':
					$return_data = array(
							'family' => 'harfang-pro',
							'weight' => 400,
							'style' => 'normal',
					);
					break;
				case 'heading_one_font':
					$return_data = array(
						'family' => 'harfang-pro',
						'weight' => 400,
						'style' => 'normal',
					);
					break;
				case 'heading_two_font':
					$return_data = array(
							'family' => 'Fira Sans',
							'weight' => 'regular',
							'style' => 'italic',
					);
					break;
				case 'heading_three_font':
					$return_data = array(
							'family' => 'Raleway',
							'weight' => 700,
							'style' => 'normal',
					);
					break;
				case 'heading_four_font':
					$return_data = array(
							'family' => 'Raleway',
							'weight' => 500,
							'style' => 'normal',
					);
					break;
				case 'heading_five_font':
					$return_data = array(
							'family' => 'Raleway',
							'weight' => 300,
							'style' => 'normal',
					);
					break;
				case 'heading_six_font':
					$return_data = array(
							'family' => 'Raleway',
							'weight' => 700,
							'style' => 'normal',
					);
					break;
			} // End switch().

			return $return_data;
		} // End if().
	}

	/**
	 * Customizer action hook function. TODO: Update desc.
	 *
	 * @param string $customizer customizer.
	 */
	public function hook_on_customize_register( $customizer ) {
		$this->wp_customizer = $customizer;

		$font_set = $this->get_font_set();

		$sections_config = $this->get_customizer_settings();

		foreach ( $sections_config as $section_key => $section_options ) {
			$section_config = ! empty( $section_options['config'] ) ? $section_options['config'] : array();
			$section_fields = ! empty( $section_options['fields'] ) ? $section_options['fields'] : array();

			if ( ! $section_fields ) {
				continue;
			}

			$customizer->add_section( $section_key, $section_config );

			foreach ( $section_fields as $field_key => $field_options ) {
				if ( isset( $field_options['field_type'] ) ) {
					$field_type = $field_options['field_type'];
					unset( $field_options['field_type'] );
				} else {
					$field_type = 'text';
				}

				if ( isset( $field_options['default'] ) ) {
					$default_value = $field_options['default'];
					unset( $field_options['default'] );
				} else {
					$default_value = '';
				}

				$customizer->add_setting( $field_key, array(
					'default' => $default_value,
				) );

				$field_options['section'] = $section_key;
				switch ( $field_type ) {
					case 'swishdesign_font':
						$field_options['font_set'] = $font_set;

						$customizer->add_control(
							new SD_Customize_Font_Control( $customizer, $field_key, $field_options )
						);
						break;

					case 'swishdesign_font_size':
						$customizer->add_control(
							new SD_Customize_Font_Size_Control( $customizer, $field_key, $field_options )
						);
						break;

					case 'swishdesign_text_transform':
						$customizer->add_control(
								new SD_Customize_Text_Transform_Control( $customizer, $field_key, $field_options )
						);
						break;

					case 'swishdesign_line_height':
						$customizer->add_control(
								new SD_Customize_Line_Height_Control( $customizer, $field_key, $field_options )
						);
						break;

					case 'swishdesign_color':
						$customizer->add_control(
							new SD_Customize_Color_Control( $customizer, $field_key, $field_options )
						);
						break;

					case 'color':
						$customizer->add_control(
							new WP_Customize_Color_Control( $customizer, $field_key, $field_options )
						);
						break;

					default:
						$customizer->add_control( $field_key, $field_options );
						break;
				}
			} // End foreach().
		} // End foreach().
	}

	/**
	 * Ajax reset method.
	 */
	public function action_ajax_reset() {
		if ( ! check_ajax_referer( 'customizer-reset', 'nonce', false ) ) {
			wp_send_json_error( 'invalid_nonce' );
		}

		if ( $this->wp_customizer ) {
			$settings = $this->wp_customizer->settings();
			foreach ( $settings as $setting ) {
				if ( 'theme_mod' == $setting->type ) {
					remove_theme_mod( $setting->id );
				}
			}
		}

		wp_send_json_success();
	}

	public function action_customize_controls_print_scripts(){
		wp_enqueue_script( 'theme-customizer', PARENT_URL . '/assets/sd/js/sd-theme-customizer.js', array( 'jquery' ), '20150704' );
		wp_localize_script( 'theme-customizer', 'ThemeCustomizerConfig', array(
			'resetBtn' => array(
				'text'   => esc_html__( 'Reset', 'swishdesign' ),
				'confirm' => esc_html__( 'This will reset all customizations made via customizer to this theme! Are you sure?', 'swishdesign' ),
				'nonce'   => wp_create_nonce( 'customizer-reset' ),
			),
		) );
	}

	/*public function hook_registerPreviewAssets()
	{
		wp_enqueue_script(
			'personal-brand-themecustomizer-preview',
			PARENT_URL . '/assets/sd/js/ThemeCustomizerPreview.js',
			array( 'jquery', 'customize-preview' ),
			time(),
			true
		);
	}

	public function hook_registerManageAssets()
	{
		$config = array(
			'adminAjaxUrl' => admin_url('admin_ajax.php')
		);
		wp_localize_script( 'personal-brand-themecustomizer-preview', 'ThemeCustomizerConfig', $config );
	}*/
}
