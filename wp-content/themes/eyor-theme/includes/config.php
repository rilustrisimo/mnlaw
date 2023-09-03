<?php
/**
 * Main application configuration file.
 * Used to configure set of services that available in the application.
 *
 * @author    eyorsogood.com, Rouie Ilustrisimo
 * @package   SwishDesign
 * @version   1.0.0
 */

return array(
	'app' => array(
		'QED_Application',
	),
	'register' => array(
		'SD_Register',
		array(
			'data' => array(
				'main_less_file' => '/assets/less/main.less',
				'autoinit_services' => array(
					//'theme_customizer',
					'image_manager',
					'icons_manager',
				),
			),
		),
	),
	'header_section' => array(
		'QED_Header_Section',
	),
	'focus_button_section' => array(
		'QED_Focus_Button_Section',
	),
	'accordion_section' => array(
		'QED_Accordion_Section',
	),
	'breadcrumbs' => array(
		'SD_Breadcrumbs',
		array(
			'show_on_home' => false,
			'page_type_formats' => array(
				'home' => esc_html__( 'Home', 'swishdesign' ),
				'category' => esc_html__( 'Category %s', 'swishdesign' ),
				'search' => esc_html__( 'Result search "%s"', 'swishdesign' ),
				'tag' => esc_html__( 'Tag', 'swishdesign' ) . ' "%s"',
				'author' => esc_html__( 'Author %s', 'swishdesign' ),
				'404' => esc_html__( 'Error 404', 'swishdesign' ),
				'format' => esc_html__( 'Format %s', 'swishdesign' ),
			),
		),
	),
	'icons_manager' => array(
		'SD_Font_Icons_Manager',
		array(
			'font_file_url' => PARENT_URL . '/assets/csslib/font-awesome.min.css',
			'pattern' => '/\.(sd-(?:\w+(?:-)?)+):before\s*{\s*content/',
			'cache_key' => 'qed-font-icons-list',
		),
	),
	'image_manager' => array(
		'SD_Image_Manager',
		array(
			'sizes' => array(
				'blog_grid_thumb' => array(
						'width' => 358,
						'height' => 240,
						'crop' => true,
				),
				'featured_single' => array(
						'width' => 940,
						'height' => 406,
						'crop' => true,
				),
				'focus_button_home' => array(
					'width' => 316,
					'height' => 223,
					'crop' => true,
				),
				'focus_button_inner' => array(
					'width' => 378,
					'height' => 254,
					'crop' => true,
				),
			),
		),
	),
//	'theme_customizer' => array(
//		'QED_Theme_Customizer',
//	),
	'shortcodes_helper' => array(
		'QED_Shortcodes_Helper_Service',
	),
	'shortcodes_register' => array(
		'SD_Shortcodes_Register',
	),
	'shortcodes_tiny_mce_integrator' => array(
		'SD_Shortcodes_TinyMCE_Integrator',
		array(
			'register_service' => '@shortcodes_register',
		),
	),
);
