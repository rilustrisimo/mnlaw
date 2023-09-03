<?php
/**
 * Theme widgets file.
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

// -----------------------------------------------------------------#
// Widgets registration
// -----------------------------------------------------------------#
if ( ! function_exists( 'qed_register_widgets' ) ) {
	/**
	 * Hook for widgets registration.
	 *
	 * @return void
	 */
	function qed_register_widgets() {
		// Make a Wordpress built-in Text widget process shortcodes.
		add_filter( 'widget_text', 'shortcode_unautop' );
		add_filter( 'widget_text', 'do_shortcode', 11 );

		register_widget( 'QED_Widget_Latest_Posts' );
		register_widget( 'QED_Widget_Contact_Us' );
		register_widget( 'QED_Widget_Advanced_Text' );

		// if ( class_exists( 'woocommerce' ) ) {
			// TODO: Widget for woocommerce.
		// }

		register_sidebar(array(
			'id'            => 'sidebar',
			'name'          => esc_html__( 'Sidebar', 'swishdesign' ),
			'description'   => esc_html__( 'Sidebar located on the right side of blog page.', 'swishdesign' ),
			'before_widget' => '<div id="%1$s" class="widget block-after-indent %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget__title">',
			'after_title'   => '</h3>',
		));

		register_sidebar(array(
			'id'            => 'footer1',
			'name'          => sprintf( esc_html__( 'Footer %s', 'swishdesign' ), 1 ),
			'description'   => esc_html__( 'Located in 1st column on 4-columns footer layout.', 'swishdesign' ),
			'before_widget' => '<div id="%1$s" class="widget block-after-indent %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget__title">',
			'after_title'   => '</h3>',
		));

		$footer_columns_count = qed_get_footer_columns();
		if ( $footer_columns_count >= 2 ) {
			register_sidebar(array(
				'id'            => 'footer2',
				'name'          => sprintf( esc_html__( 'Footer %s', 'swishdesign' ), 2 ),
				'description'   => esc_html__( 'Located in 2nd column on 4-columns footer layout.', 'swishdesign' ),
				'before_widget' => '<div id="%1$s" class="widget block-after-indent %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3 class="widget__title">',
				'after_title'   => '</h3>',
			));
		}

		if ( $footer_columns_count >= 3 ) {
			register_sidebar(array(
				'id'            => 'footer3',
				'name'          =>sprintf( esc_html__( 'Footer %s', 'swishdesign' ), 3 ),
				'description'   => esc_html__( 'Located in 3rd column on 4-columns footer layout.', 'swishdesign' ),
				'before_widget' => '<div id="%1$s" class="widget block-after-indent %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3 class="widget__title">',
				'after_title'   => '</h3>',
			));
		}

		if ( $footer_columns_count >= 4 ) {
			register_sidebar(array(
				'id'            => 'footer4',
				'name'          => sprintf( esc_html__( 'Footer %s', 'swishdesign' ), 4 ),
				'description'   => esc_html__( 'Located in 4th column on 4-columns footer layout.', 'swishdesign' ),
				'before_widget' => '<div id="%1$s" class="widget block-after-indent %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3 class="widget__title">',
				'after_title'   => '</h3>',
			));
		}
	}
	add_action( 'widgets_init', 'qed_register_widgets' );
}
