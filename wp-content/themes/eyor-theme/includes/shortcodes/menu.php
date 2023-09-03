<?php
/**
 * Shortcodes menu definition.
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

$shortcodes_register = qed_di( 'shortcodes_register' );
if ( ! qed_di( 'shortcodes_tiny_mce_integrator' ) || ! $shortcodes_register ) {
	return;
}

//$typography_menu = esc_html__( 'Typography', 'swishdesign' ) . '.';

//$tables_menu = esc_html__( 'Tables', 'swishdesign' ) . '.';

$general_menu = esc_html__( 'General', 'swishdesign' ) . '.';

$other_menu = esc_html__( 'Other', 'swishdesign' ) . '.';

$external_api_menu = esc_html__( 'External Services', 'swishdesign' ) . '.';

$category_order_mode = array(
	'ASC',
	'DESC',
);

$category_orderby_mode = array(
	'name',
	'id',
	'slug',
	'count',
	'term_group',
	'category__in',
);

$article_order_mode = array(
	'DESC',
	'ASC',
);

$article_orderby_mode = array(
	'date',
	'title',
	'name',
	'modified',
	'rand',
	'comment_count',
	'post__in',
);
$testimonial_orderby_mode = array(
		'date',
		'name',
		'modified',
		'rand',
);

$shortcodes_register
	->add( '_edit_', esc_html__( 'Edit', 'swishdesign' ) )
	->add( 'row', $general_menu . esc_html__( 'Columns', 'swishdesign' ), array(
		'columns' => '2',
		'css_class' => '',
	))

	->add( 'accordion', $general_menu . esc_html__( 'Accordion', 'swishdesign' ), array(
		'content' => join( PHP_EOL, array(
				'[accordion_item title="Title 1" is_active="on"]Lorem ipsum 1[/accordion_item]',
				'[accordion_item title="Title 2"]Lorem ipsum 2[/accordion_item]',
				'[accordion_item title="Title 3"]Lorem ipsum 3[/accordion_item]',
		)),
		'style' => array(
				'type' => 'select',
				'values' => array(
						'with-shadow',
						'with-border',
				),
		),
		'css_class' => '',
	))
	->add( 'latest_posts', $general_menu . esc_html__( 'Latest Posts', 'swishdesign' ), array(
		'title' => esc_html__( 'Latest Posts', 'swishdesign' ),
		'title_underline' => array(
				'type' => 'boolean',
				'default' => 'on',
		),
		'category' => array(
				'help' => esc_html__( 'Filter items from specific category (enter category slug).', 'swishdesign' ),
				'default' => '',
		),
		'post_ids' => array(
				'help' => esc_html__( 'Specify exact ids of items that should be displayed separated by comma.', 'swishdesign' ),
				'default' => '',
		),
		'number' => '1',
		'read_more_text' => esc_html__( 'Read more', 'swishdesign' ),
		'words_limit' => '25',
		'ignore_sticky_posts' => array(
				'type' => 'boolean',
				'default' => 'on',
		),
		'translate' => array(
				'type' => 'boolean',
				'default' => 'on',
		),
		'order' => array(
				'type' => 'select',
				'values' => $article_order_mode,
		),
		'orderby' => array(
				'type' => 'select',
				'values' => $article_orderby_mode,
		),
		'css_class' => '',
	))
	->add( 'testimonials', $general_menu . esc_html__( 'Testimonials', 'swishdesign' ), array(
		'title' => esc_html__( 'Testimonials', 'swishdesign' ),
		'words_limit' => '25',
		'order' => array(
				'type' => 'select',
				'values' => $article_order_mode,
		),
		'orderby' => array(
				'type' => 'select',
				'values' => $testimonial_orderby_mode,
		),
		'css_class' => '',
	))
	->add( 'tabs', $general_menu . esc_html__( 'Tabs', 'swishdesign' ), array(
		'content' => join( PHP_EOL, array(
				'[tab_item title="Title 1" is_active="on"]Lorem ipsum 1[/tab_item]',
				'[tab_item title="Title 2"]Lorem ipsum 2[/tab_item]',
				'[tab_item title="Title 3"]Lorem ipsum 3[/tab_item]',
		)),
		'style' => array(
				'type' => 'select',
				'values' => array(
						'with-shadow',
						'with-border',
				),
		),
		'css_class' => '',
	))

//	->add( 'title', $typography_menu . esc_html__( 'Title', 'swishdesign' ), array(
//		'text' => '',
//		'subtitle' => '',
//		'size' => array(
//			'type' => 'select',
//			'values' => array(
//				'big',
//				'small',
//			),
//		),
//		'position' => array(
//			'type' => 'select',
//			'values' => array(
//				'left',
//				'center',
//			),
//		),
//		'decoration' => array(
//			'type' => 'boolean',
//			'default' => 'on',
//		),
//		'underline' => array(
//			'type' => 'boolean',
//			'default' => 'on',
//		),
//		'style' => array(
//			'type' => 'select',
//			'values' => array(
//				'dark',
//				'light',
//			),
//		),
//		'css_class' => '',
//	))

//	->add( 'at_btn', $typography_menu . esc_html__( 'Button', 'swishdesign' ), array(
//		'text' => '',
//		'url' => '',
//		'type' => array(
//			'type' => 'select',
//			'values' => array(
//				'link',
//				'button',
//				'submit',
//			),
//		),
//		'css_class' => '',
//		'style' => array(
//			'type' => 'select',
//			'values' => array(
//				'',
//				'primary',
//				'secondary1',
//				'secondary2',
//			),
//		),
//		'size' => array(
//			'type' => 'select',
//			'values' => array(
//				'',
//				'medium',
//				'small',
//			),
//		),
//		'corners' => array(
//			'type' => 'select',
//			'values' => array(
//				'',
//				'rounded',
//			),
//		),
//		'light' => array(
//			'type' => 'boolean',
//			'default' => 'off',
//		),
//		'transparent' => array(
//			'type' => 'boolean',
//			'default' => 'off',
//		),
//		'icon_class' => '',
//		'icon_align' => array(
//			'type' => 'select',
//			'values' => array(
//				'left',
//				'right',
//			),
//		),
//	))

//  TODO: Change this and integrate Mailchimp for WP.
//	->add( 'mailchimp_form', $external_api_menu . esc_html__( 'MailChimp Form', 'swishdesign' ), array(
//		'form_id' => array(
//			'required' => true,
//		),
//		/*'mailchimp_list_id' => array(
//			'required' => true,
//		),*/
//		'button_text' => esc_html__( 'Submit', 'swishdesign' ),
//		'title' => '',
//		'content' => '',
//		'css_class' => '',
//		'width_mode' => array(
//			'type' => 'select',
//			'values' => array(
//				'box-width',
//				'full-width',
//			),
//		),
//		'bg_url' => array(
//			'type' => 'image_url',
//			'help' => esc_html__( 'Select image that should be used as background.', 'swishdesign' ),
//			'default' => '',
//		),
//		'bg_repeat' => array(
//			'type' => 'select',
//			'values' => array(
//				'repeat',
//				'no-repeat',
//				'repeat-x',
//				'repeat-y',
//			),
//		),
//	))
	->add( 'google_map', $external_api_menu . esc_html__( 'Google Map', 'swishdesign' ), array(
		'address' => array(
			'help' => esc_html__( 'The address will show up when clicking on the map marker.', 'swishdesign' ),
		),
		'coordinates' => array(
			'help' => esc_html__( 'Coordinates separated by comma.', 'swishdesign' ),
			'default' => '40.764324,-73.973057',
			'required' => true,
		),
		'zoom' => array(
			'help' => esc_html__( 'Number in range from 1 up to 21.', 'swishdesign' ),
			'default' => '10',
			'required' => true,
		),
		'height' => array(
			'default' => '400',
		),
		'width_mode' => array(
			'type' => 'select',
			'values' => array(
				'box-width',
				'full-width',
			),
		),
		'css_class' => '',
	));
