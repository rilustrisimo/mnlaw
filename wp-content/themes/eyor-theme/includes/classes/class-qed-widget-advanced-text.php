<?php
/**
 * Widget text defined inside 'Text' option with some additional settings.
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
 * Class QED_Widget_Advanced_Text
 */
class QED_Widget_Advanced_Text extends QED_Widget_Base {

	/**
	 * QED_Widget_Advanced_Text constructor.
	 */
	public function __construct() {
		$this->fields_config = array(
			'title' => array(
				'label' => __( 'Title', 'swishdesign' ),
			),
			'text' => array(
				'label' => __( 'Content', 'swishdesign' ),
				'type' => 'textarea',
			),
			'style' => array(
				'label' => __( 'Style', 'swishdesign' ),
				'type' => 'select',
				'options' => array(
					'plain' => __( 'Plain', 'swishdesign' ),
					'standard' => __( 'Boxed', 'swishdesign' ),
				),
			),
			'css_class' => array(
				'label' => __( 'CSS class', 'swishdesign' ),
			),
			'filter' => array(
				'label' => __( 'Automatically add paragraphs', 'swishdesign' ),
				'type' => 'checkbox',
			),
			'hide_if_empty' => array(
				'label' => __( 'Hide empty', 'swishdesign' ),
				'type' => 'checkbox',
			),
		);

		parent::__construct(
			'render_shortcode_qed',
			'SwishDesign: ' . esc_html__( 'Text Advanced', 'swishdesign' ),
			array(
				// 'classname' => '',
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
		$instance = $this->merge_instance( $instance );

		$content = ! empty( $instance['text'] ) ? apply_filters( 'widget_text', $instance['text'], $instance, $this ) : '';
		if ( ! empty( $instance['hide_if_empty'] ) && ! $content ) {
			return;
		}

		$css_class = ! empty( $instance['css_class'] ) ? $instance['css_class'] : '';
		switch ( $instance['style'] ) {
			case 'plain':
				if ( ! empty( $instance['title'] ) ) {
					$instance['title'] = '';
				}

				$plain_css_class = 'widget--plain';
				if ( $css_class ) {
					$css_class .= ' ' . $plain_css_class;
				} else {
					$css_class = $plain_css_class;
				}
				break;
		}

		if ( $css_class ) {
			$args['before_widget'] = str_replace( ' class="', sprintf( ' class="%s ', esc_attr( $css_class ) ), $args['before_widget'] );
		}

		$this->widget_start( $args, $instance );
		echo ! empty( $instance['filter'] ) ?  wpautop( $content ) : $content;
		$this->widget_end( $args );
	}
}
