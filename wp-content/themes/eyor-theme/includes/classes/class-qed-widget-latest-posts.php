<?php
/**
 * Latest Posts widget component.
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
 * Class QED_Widget_Latest_Posts
 */
class QED_Widget_Latest_Posts extends WP_Widget {

	/**
	 * Size of the image that should be used in the widget.
	 *
	 * @var string
	 */
	private static $image_size = 'thumb_last_posts_widget';

	/**
	 * QED_Widget_Latest_Posts constructor.
	 */
	public function __construct() {
		parent::__construct(
			'last_posts_qed',
			'SwishDesign: ' . esc_html__( 'Latest Posts', 'swishdesign' ),
			array(
				'descriptions' => esc_html__( 'Latest Posts Widget', 'swishdesign' ),
			)
		);
	}

	/**
	 * Generate widget code.
	 *
	 * @param array $args widget options.
	 * @param array $instance widget instance.
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		extract( $instance );

		$query_arguments = array(
				'posts_per_page' => (int) $number > 0 ? $number : -1,
		);
		$query = new WP_Query( $query_arguments );
		$posts = $query->get_posts();
		$posts_html = '';

		foreach ( $posts as $post ) {
			$post_thumb = ('yes' === $is_show_img) ? qed_get_the_post_thumbnail( $post->ID , self::$image_size ) : '';
			$post_date = ('yes' === $is_show_date) ? get_the_date( null, $post->ID ) : '';
			$post_permalink = get_permalink( $post->ID );

			$image_wrap_html = '';
			if ( $post_thumb ) {
				$image_wrap_html = '<div class="widget-last-posts__item__container__item widget-last-posts__item__container__item--image">' .
				                   '<a href="' . esc_url( $post_permalink ) . '" class="widget-last-posts__item__image">' . $post_thumb . '</a>' .
				                   '</div>';
			}

			$posts_html .= '<div class="widget-last-posts__item">' .
			               '<div class="widget-last-posts__item__container' . ( $post_thumb ? '' : ' widget-last-posts__item__container--without-img' ) . '">' .
			               $image_wrap_html .
			               '<div class="widget-last-posts__item__container__item widget-last-posts__item__info">' .
			               '<div class="widget-last-posts__item__title"><a href="' . esc_url( $post_permalink ) . '">' . esc_html( get_the_title( $post->ID ) ) . '</a></div>' .
			               '<div class="widget-last-posts__item__date">' . esc_html( $post_date ) . '</div>' .
			               '</div>' .
			               '</div>' .
			               '</div>';
		}

		printf(
			'%s<div class="widget-last-posts">%s%s</div>%s',
			$before_widget,
			$title ? $before_title . esc_html( $title ) . $after_title : '',
			$posts_html,
			$after_widget
		);
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $new_instance;
		return $instance;
	}

	public function form( $instance ) {
		$default = array(
			'title' => '',
			'number' => 2,
			'is_show_date' => '',
			'is_show_img' => '',
		);

		$instance = wp_parse_args( (array) $instance, $default );

		$yes_no_options = array(
			'yes' => esc_html__( 'Yes', 'swishdesign' ),
			'no' => esc_html__( 'No', 'swishdesign' ),
		);

		echo '<p>' .
		     '<label for="' . esc_attr( $this->get_field_id( 'title' ) ) . '">' . esc_html__( 'Title', 'swishdesign' ) . ':</label>' .
		     '<input class="widefat" id="' . esc_attr( $this->get_field_id( 'title' ) ) . '" name="' . esc_attr( $this->get_field_name( 'title' ) ) . '" type="text" value="' . esc_attr( $instance['title'] ) . '">' .
		     '</p>' .
		     '<p>' .
		     '<label for="' . esc_attr( $this->get_field_id( 'number' ) ) . '">' . esc_html__( 'Number of posts to show', 'swishdesign' ) . ':</label>' .
		     '<input id="' . esc_attr( $this->get_field_id( 'number' ) ) . '" name="' . esc_attr( $this->get_field_name( 'number' ) ) . '" type="text" value="' . esc_attr( $instance['number'] ) . '" size="3">' .
		     '</p>' .
		     '<p>' .
		     '<label for="' . esc_attr( $this->get_field_id( 'is_show_date' ) ) . '">' . esc_html__( 'Display post date?', 'swishdesign' ) . '</label>' .
		     '<select class="widefat" id="' . esc_attr( $this->get_field_id( 'is_show_date' ) ) . '" name="' . esc_attr( $this->get_field_name( 'is_show_date' ) ) . '">' .
		     $this->render_options_html( $yes_no_options, $instance['is_show_date'] ) .
		     '</select>' .
		     '</p>' .
		     '<p>' .
		     '<label for="' . esc_attr( $this->get_field_id( 'is_show_img' ) ) . '">' . esc_html__( 'Display post image?', 'swishdesign' ) . '</label>' .
		     '<select class="widefat" id="' . esc_attr( $this->get_field_id( 'is_show_img' ) ) . '" name="' . esc_attr( $this->get_field_name( 'is_show_img' ) ) . '">' .
		     $this->render_options_html( $yes_no_options, $instance['is_show_img'] ) .
		     '</select>' .
		     '</p>';
	}

	public function render_options_html( array $options, $selectedValue = '' ) {
		$result = '';
		foreach ( $options as $val => $label ) {
			$selectedAttr = $val == $selectedValue ? ' selected="selected"' : '';
			$result .= '<option value="' . esc_attr( $val ) . '" ' . esc_attr( $selectedAttr ) . '>' . esc_html( $label ) . '</option>';
		}
		return $result;
	}
}
