<?php
/**
 * Definition of shortcodes that generate own content based on data stored in DB.
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

$sc_helper = QEDDTP()->shortcodes_helper();

if ( ! shortcode_exists( 'latest_posts' ) ) {
	/**
	 * Latest posts shortcode rendering function.
	 *
	 * @param  array  $atts     shortcode attributes.
	 * @param  string $content  shortcode content text.
	 * @return string
	 */
	function qeddpt_shortcode_latest_posts( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'title' => '',
			'title_underline' => 'on',
			'post_ids' => '',
			'number' => 1,
			'category' => '',
			'translate' => '1',
			'read_more_text' => esc_html__( 'Read more', 'swishdesign' ),
			'words_limit' => 25,
			'ignore_sticky_posts' => '1',
			'orderby' => 'date',
			'order' => 'DESC',
			'css_class' => '',
			'view' => '',
		), $atts );

		$helper = QEDDTP()->shortcodes_helper();
		$atts['ignore_sticky_posts'] = $helper->attribute_is_true( $atts['ignore_sticky_posts'] );

		$query_arguments = array(
			'post_type' => 'post',
			'post__in' => ! empty( $atts['post_ids'] ) ? explode( ',', $atts['post_ids'] ) : '',
			'posts_per_page' => $atts['number'] > 0 ? $atts['number'] : -1,
			'orderby' => sanitize_title( $atts['orderby'] ),
			'order' => sanitize_title( $atts['order'] ),
			'tax_query' => array(),
			'ignore_sticky_posts' => $atts['ignore_sticky_posts'],
		);

		if ( ! empty( $atts['category'] ) ) {
			$query_arguments['tax_query'][] = array(
				'taxonomy' => 'category',
				'terms' => array_map( 'sanitize_title', explode( ',', $atts['category'] ) ),
				'field' => 'slug',
				'operator' => 'IN',
			);
		}

		/*
		if ( $helper->attribute_is_true( $atts['translate'] ) ) {
			$queryArguments = apply_filters( 'widget_posts_args', $query_arguments );
		}
		*/

		$query = new WP_Query( $query_arguments );
		$atts['title_underline'] = $helper->attribute_is_true( $atts['title_underline'] );
		$atts['items'] = $query->get_posts();

		return $helper->render_view( 'templates/shortcodes/latest_posts', $atts['view'], $atts );
	}

	$sc_helper->add_shortcode( 'latest_posts', 'qeddpt_shortcode_latest_posts', array(
		'name' => esc_html__( 'Latest Posts', 'swishdesign' ),
		'params' => array(
			'title' => array(
				'value' => 'Latest Posts',
			),
			'title_underline' => array(
				'type' => 'dropdown',
				'value' => array(
					'on',
					'off',
				),
			),
			'category' => array(
				'description' => esc_html__( 'Filter items from specific category (enter category slug).', 'swishdesign' ),
			),
			'post_ids' => array(
				'description' => esc_html__( 'Specify exact ids of items that should be displayed separated by comma.', 'swishdesign' ),
			),
			'number' => array(
				'value' => '1',
			),
			'read_more_text' => array(
				'value' => 'Read more',
			),
			'words_limit' => array(
				'value' => '25',
			),
			'ignore_sticky_posts' => array(
				'type' => 'dropdown',
				'value' => array(
					'on',
					'off',
				),
			),
			/*'translate' => array(
				'type' => 'dropdown',
				'value' => array(
					'on',
					'off',
				),
			),*/
			'order' => array(
				'type' => 'dropdown',
				'value' => $sc_helper->get_order_values( 'article_order' ),
			),
			'orderby' => array(
				'type' => 'dropdown',
				'value' => $sc_helper->get_order_values( 'article_orderby' ),
			),
			'css_class' => array(),
		),
		'display_on_menu' => true,
	) );
}

if ( ! shortcode_exists( 'more_posts' ) ) {
	/**
	 * Latest posts shortcode rendering function.
	 *
	 * @param  array  $atts     shortcode attributes.
	 * @param  string $content  shortcode content text.
	 * @return string
	 */
	function qeddpt_shortcode_more_posts( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'title' => '',
			'title_underline' => 'on',
			'post_ids' => '',
			'number' => 1,
			'category' => '',
			'translate' => '1',
			'read_more_text' => esc_html__( 'Read more', 'swishdesign' ),
			'words_limit' => 10,
			'ignore_sticky_posts' => '1',
			'orderby' => 'date',
			'order' => 'DESC',
			'css_class' => '',
			'view' => '',
		), $atts );

		$helper = QEDDTP()->shortcodes_helper();
		$atts['ignore_sticky_posts'] = $helper->attribute_is_true( $atts['ignore_sticky_posts'] );

		$query_arguments = array(
			'post_type' => 'post',
			'post__in' => ! empty( $atts['post_ids'] ) ? explode( ',', $atts['post_ids'] ) : '',
			'posts_per_page' => $atts['number'] > 0 ? $atts['number'] : -1,
			'orderby' => sanitize_title( $atts['orderby'] ),
			'order' => sanitize_title( $atts['order'] ),
			'tax_query' => array(),
			'ignore_sticky_posts' => $atts['ignore_sticky_posts'],
		);

		if ( ! empty( $atts['category'] ) ) {
			$query_arguments['tax_query'][] = array(
				'taxonomy' => 'category',
				'terms' => array_map( 'sanitize_title', explode( ',', $atts['category'] ) ),
				'field' => 'slug',
				'operator' => 'IN',
			);
		}

		/*
		if ( $helper->attribute_is_true( $atts['translate'] ) ) {
			$queryArguments = apply_filters( 'widget_posts_args', $query_arguments );
		}
		*/

		$query = new WP_Query( $query_arguments );
		$atts['title_underline'] = $helper->attribute_is_true( $atts['title_underline'] );
		$atts['items'] = $query->get_posts();

		return $helper->render_view( 'templates/shortcodes/more_posts', $atts['view'], $atts );
	}

	$sc_helper->add_shortcode( 'more_posts', 'qeddpt_shortcode_more_posts', array(
		'name' => esc_html__( 'More Posts', 'swishdesign' ),
		'params' => array(
			'title' => array(
				'value' => 'More Posts',
			),
			'title_underline' => array(
				'type' => 'dropdown',
				'value' => array(
					'on',
					'off',
				),
			),
			'category' => array(
				'description' => esc_html__( 'Filter items from specific category (enter category slug).', 'swishdesign' ),
			),
			'post_ids' => array(
				'description' => esc_html__( 'Specify exact ids of items that should be displayed separated by comma.', 'swishdesign' ),
			),
			'number' => array(
				'value' => '1',
			),
			'read_more_text' => array(
				'value' => 'Read more',
			),
			'words_limit' => array(
				'value' => '25',
			),
			'ignore_sticky_posts' => array(
				'type' => 'dropdown',
				'value' => array(
					'on',
					'off',
				),
			),
		/*'translate' => array(
			'type' => 'dropdown',
			'value' => array(
				'on',
				'off',
			),
		),*/
			'order' => array(
				'type' => 'dropdown',
				'value' => $sc_helper->get_order_values( 'article_order' ),
			),
			'orderby' => array(
				'type' => 'dropdown',
				'value' => $sc_helper->get_order_values( 'article_orderby' ),
			),
			'css_class' => array(),
		),
		'display_on_menu' => false,
	) );
}

if ( ! shortcode_exists( 'testimonials' ) ) {
	/**
	 * Testimonials shortcode rendering function.
	 *
	 * @param  array  $atts     shortcode attributes.
	 * @param  string $content  shortcode content text.
	 * @return string
	 */
	function qeddpt_shortcode_testimonials( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'title' => ( qed_get_option( 'testimonial_section_title', 'option' ) ) ? qed_get_option( 'testimonial_section_title', 'option' ) : '',
			'words_limit' => ( qed_get_option( 'is_testimonial_excerpt', 'option' ) && qed_get_option( 'testimonial_excerpt_length', 'option' ) ) ? qed_get_option( 'testimonial_excerpt_length', 'option' ) : 25,
			'orderby' => ( qed_get_option( 'testimonial_orderby', 'option' ) ) ? qed_get_option( 'testimonial_orderby', 'option' ) : 'date',
			'order' => ( qed_get_option( 'testimonial_order', 'option' ) ) ? qed_get_option( 'testimonial_order', 'option' ) : 'DESC',
			'transition' => ( qed_get_option( 'testimonial_type', 'option' ) ) ? qed_get_option( 'testimonial_type', 'option' ) : 'static',
			'view' => '',
		), $atts );

		$helper = QEDDTP()->shortcodes_helper();

		$query_arguments = array(
			'post_type' => 'qed_testimonials',
			'posts_per_page' => -1,
			'orderby' => sanitize_title( $atts['orderby'] ),
			'order' => sanitize_title( $atts['order'] ),
		);

		$query = new WP_Query( $query_arguments );
		$atts['items'] = $query->get_posts();
		$atts['instance'] = $helper->generate_id();

		return $helper->render_view( 'templates/shortcodes/testimonials', $atts['view'], $atts );
	}

	$sc_helper->add_shortcode( 'testimonials', 'qeddpt_shortcode_testimonials', array(
		'name' => esc_html__( 'Testimonials', 'swishdesign' ),
		'params' => array(
			'title' => array(
				'value' => 'Testimonials',
			),
			'words_limit' => array(
				'value' => '25',
			),
			'order' => array(
				'type' => 'dropdown',
				'value' => $sc_helper->get_order_values( 'article_order' ),
			),
			'orderby' => array(
				'type' => 'dropdown',
				'value' => $sc_helper->get_order_values( 'testimonial_orderby' ),
			),
		),
		'display_on_menu' => true,
	) );
}
