<?php
/**
 * Theme menus file.
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

add_theme_support( 'menus' );
register_nav_menus(array(
    'header-menu' => esc_html__( 'Header Menu', 'swishdesign' ),
    //'footer-menu' => esc_html__( 'Footer Menu', 'swishdesign' ),
));
