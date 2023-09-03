<?php
/**
 * Theme core file.
 *
 * @author    eyorsogood.com, Rouie Ilustrisimo
 * @package Eyorsogood_Design
 * @version   1.0.0
 */

/**
 * No direct access to this file.
 *
 * @since 1.0.0
 */
defined( 'ABSPATH' ) || die();

require PARENT_DIR . '/includes/loader.php';
require PARENT_DIR . '/includes/data/loader.php';

/**
 * Returns dependency injection container/element from container by key.
 *
 * @param  string $key dependency key.
 * @return mixed
 */
function &qed_di( $key = null ) {
	static $di;
	if ( ! $di ) {
		$di = new JuiceContainer();
	}
	if ( $key ) {
		$result = $di[ $key ];
		return $result;
	}
	return $di;
}

/**
 * Initialize dependency injector callback.
 *
 * @param array $di dependency injector.
 * @param mixed $config di config.
 */
function qed_init_di_callback( $di, $config ) {
	if ( $config ) {
		foreach ( $config as $key => $value ) {
			$instance = null;
			$class = '';
			$typeof = gettype( $value );
			switch ( $typeof ) {
				case 'string':
					$class = $value;
					break;

				case 'array':
					$class = array_shift( $value );
					break;

				default:
					$instance = $value;
					$class = get_class( $instance );
					break;
			}
			$di_key = is_string( $key ) ? $key : $class;
			if ( isset( $di[ $di_key ] ) ) {
				continue;
			}

			$di[ $di_key ] = $instance ? $instance : JuiceDefinition::create( $class, $value );
		}
	}
}
add_action( 'qed_init_di', 'qed_init_di_callback', 10, 2 );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function qed_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'qed_content_width', 708 );
}
add_action( 'after_setup_theme', 'qed_content_width', 0 );

// -----------------------------------------------------------------#
// Theme settings functions
// -----------------------------------------------------------------#
if ( ! function_exists( 'qed_get_option' ) ) {
	/**
	 * Returns theme option value.
	 *
	 * @param  string $name    option name.
	 * @param  mixed  $default default value.
	 * @return mixed
	 */
	function qed_get_option( $name, $id = null, $default = null ) {
		$option = ( $id ) ? $id:'';
		if ( get_field( $name, $option ) ) {
			return get_field( $name, $option );
		} else {
			return $default;
		}
	}
}

if ( ! function_exists( 'qed_filter_after_theme_setup' ) ) {
	/**
	 * Init theme function.
	 *
	 * @return void
	 */
	function qed_filter_after_theme_setup() {
		load_theme_textdomain( 'swishdesign', PARENT_DIR . '/languages' );

		do_action( 'qed_init_di', qed_di(), require PARENT_DIR . '/includes/config.php' );
		$autoinit_services = qed_di( 'register' )->get_var( 'autoinit_services' );
		if ( $autoinit_services ) {
			foreach ( $autoinit_services as $service_id ) {
				qed_di( $service_id );
			}
		}

		// Initing ACF theme options.
		if( function_exists('acf_add_options_page') ) {

			acf_add_options_page(array(
				'page_title' 	=> 'Theme General Settings',
				'menu_title'	=> 'Theme Settings',
				'menu_slug' 	=> 'theme-general-settings',
				'capability'	=> 'edit_posts',
				'redirect'		=> false
			));

			acf_add_options_sub_page(array(
				'page_title' 	=> 'Theme Header Settings',
				'menu_title'	=> 'Header Settings',
				'parent_slug'	=> 'theme-general-settings',
			));

			acf_add_options_sub_page(array(
				'page_title' 	=> 'Theme Footer Settings',
				'menu_title'	=> 'Footer Settings',
				'parent_slug'	=> 'theme-general-settings',
			));

			acf_add_options_sub_page(array(
				'page_title' 	=> 'Theme Blog Settings',
				'menu_title'	=> 'Blog Settings',
				'parent_slug'	=> 'theme-general-settings',
			));

			acf_add_options_sub_page(array(
				'page_title' 	=> 'Theme Single Post Settings',
				'menu_title'	=> 'Single Post Settings',
				'parent_slug'	=> 'theme-general-settings',
			));

			acf_add_options_sub_page(array(
				'page_title' 	=> 'Theme Feature Settings',
				'menu_title'	=> 'Features',
				'parent_slug'	=> 'theme-general-settings',
			));

			acf_add_options_sub_page(array(
				'page_title' 	=> 'Theme Social Media Settings',
				'menu_title'	=> 'Social Media',
				'parent_slug'	=> 'theme-general-settings',
			));

			acf_add_options_sub_page(array(
				'page_title' 	=> 'Theme Social Sharing Settings',
				'menu_title'	=> 'Social Sharing',
				'parent_slug'	=> 'theme-general-settings',
			));

		}

		//qed_di( 'register' )->set_var( '_vp_theme_option', $vp_theme_option );
	}

	add_action( 'after_setup_theme', 'qed_filter_after_theme_setup' );
}

if ( ! function_exists( 'qed_action_init' ) ) {
	/**
	 * Callback for 'init' action.
	 *
	 * @return void
	 */
	function qed_action_init() {
		if ( qed_check( 'eyor_category_taxonomy_exists' ) ) {
			// TODO: Add custom taxonomy init here.
		}

		// TODO: Add woocommerce support
//		if ( qed_check( 'woocommerce_active' ) ) {

//		}

		if ( is_admin() ) {
			qed_init_tiny_mce_integration();
		}
	}
	add_action( 'init', 'qed_action_init' );
}

if ( ! function_exists( 'qed_init_tiny_mce_integration' ) ) {

	/**
	 * Initialize TinyMCE shortcodes integration.
	 */
	function qed_init_tiny_mce_integration() {

		// To init shortcodes menu for tinyMCE.
		$integrator = qed_di( 'shortcodes_tiny_mce_integrator' );
		if ( $integrator && $integrator->register_service ) {
			$shortcodes_register = $integrator->register_service;
			$load_shortcodes = apply_filters( 'qed_shortcodes_register_preload_list', array() );
			if ( $load_shortcodes ) {
				$shortcodes_register->add( '_edit_', esc_html__( 'Edit', 'swishdesign' ), array() );

				foreach ( $load_shortcodes as $shortcode => $details ) {
					$shortcodes_register->add( $shortcode, $details['name'], $details['params'] );
				}
			}
		}
	}
}

if ( is_admin() ) {
	require 'admin/plugins.php';
}

require 'theme-options-functions.php';

require 'template-functions.php';

/**
 * Load WooCommerce compatibility file.
 */
if ( class_exists( 'WooCommerce' ) ) {
	require PARENT_DIR . '/includes/woocommerce.php';
}


add_theme_support( 'title-tag' );
add_theme_support( 'automatic-feed-links' );
add_theme_support( 'post-thumbnails' );
add_theme_support( 'html5', array( 'gallery', 'caption', 'search-form' ) );

require PARENT_DIR . '/includes/functions/menus.php';
require PARENT_DIR . '/includes/functions/enqueues.php';
require PARENT_DIR . '/includes/functions/widgets.php';
require PARENT_DIR . '/includes/functions/helpers.php';
require PARENT_DIR . '/includes/functions/actions.php';
