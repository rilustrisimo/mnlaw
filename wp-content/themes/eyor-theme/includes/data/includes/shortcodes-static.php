<?php
/**
 * Definition of shortcodes that generate own content based on own params/theme settings values.
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

$shortoces_nl_escaper = QEDDTP()->shortcodes_helper()->nl_escaper();
$sc_helper = QEDDTP()->shortcodes_helper();

if ( ! shortcode_exists( 'row' ) && ! shortcode_exists( 'column' ) ) {
	//QEDDTP()->require_file( '/classes/class-qeddtp-shortcodes-row.php' );

	$sc_helper->add_shortcode( 'row', array( 'QEDDTP_Shortcodes_Row', 'do_shortcode_row' ), array(
		'name' => esc_html__( 'Columns', 'swishdesign' ),
		'params' => array(
			'columns' => array(
				'value' => '2',
			),
			'css_class' => array(),
		),
		'display_on_menu' => true,
	) );

	add_shortcode( 'column', array( 'QEDDTP_Shortcodes_Row', 'do_shortcode_column' ) );

	if ( $shortoces_nl_escaper ) {
		$shortoces_nl_escaper->register_nested_shortcodes( 'row','column' );
	}
}

if ( ! shortcode_exists( 'accordion' ) && ! shortcode_exists( 'accordion_item' ) ) {
	//QEDDTP()->require_file( '/classes/class-qeddtp-shortcodes-accordion.php' );

	$sc_helper->add_shortcode( 'accordion', array( 'QEDDTP_Shortcodes_Accordion', 'accordion_do_shortcode' ), array(
		'name' => esc_html__( 'Accordion', 'swishdesign' ),
		'params' => array(
			'content' => array(
				'type' => 'textarea',
				'value' => '[accordion_item title="Title 1" is_active="on"]Lorem ipsum 1[/accordion_item]' .
							'[accordion_item title="Title 2"]Lorem ipsum 2[/accordion_item]' .
							'[accordion_item title="Title 3"]Lorem ipsum 3[/accordion_item]',
			),
			'style' => array(
				'type' => 'dropdown',
				'value' => array(
					'theme-default',
					'with-shadow',
					'with-border',
				),
			),
			'css_class' => array(),
		),
		'display_on_menu' => true,
	) );

	add_shortcode( 'accordion_item', array( 'QEDDTP_Shortcodes_Accordion', 'accordion_item_do_shortcode' ) );
}

if ( ! shortcode_exists( 'tabs' ) && ! shortcode_exists( 'tab_item' ) ) {
	//QEDDTP()->require_file( '/classes/class-qeddtp-shortcodes-tabs.php' );

	$sc_helper->add_shortcode( 'tabs', array( 'QEDDTP_Shortcodes_Tabs', 'tabs_do_shortcode' ), array(
		'name' => esc_html__( 'Tabs', 'swishdesign' ),
		'params' => array(
			'content' => array(
				'type' => 'textarea',
				'value' => '[tab_item title="Title 1" is_active="on"]Lorem ipsum 1[/tab_item]' .
							'[tab_item title="Title 2"]Lorem ipsum 2[/tab_item]' .
							'[tab_item title="Title 3"]Lorem ipsum 3[/tab_item]',
			),
			'style' => array(
				'type' => 'dropdown',
				'value' => array(
					'with-shadow',
					'with-border',
				),
			),
			'css_class' => array(),
		),
		'display_on_menu' => true,
	) );

	add_shortcode( 'tab_item', array( 'QEDDTP_Shortcodes_Tabs', 'tab_item_do_shortcode' ) );
}

if ( ! shortcode_exists( 'google_map' ) ) {
	/**
	 * Google map shortcode rendering function.
	 *
	 * @param  array  $atts     shortcode attributes.
	 * @param  string $content  shortcode content text.
	 * @return string
	 */
	function qeddtp_shortcode_google_map( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'address' => '',
			'coordinates' => '40.764324,-73.973057',
			'zoom' => '10',
			'height' => '400',
			'width_mode' => 'box-width',
			'css_class' => '',
			'view' => '',
		), $atts );

		return QEDDTP()->shortcodes_helper()->render_view( 'templates/shortcodes/google_map', $atts['view'], $atts );
	}

	$sc_helper->add_shortcode( 'google_map', 'qeddtp_shortcode_google_map', array(
		'name' => esc_html__( 'Google Map', 'swishdesign' ),
		'params' => array(
			'address' => array(
				'description' => esc_html__( 'The address will show up when clicking on the map marker.', 'swishdesign' ),
			),
			'coordinates' => array(
				'value' => '40.764324,-73.973057',
				'description' => esc_html__( 'Coordinates separated by comma.', 'swishdesign' ),
				'required' => true,
			),
			'zoom' => array(
				'value' => 10,
				'description' => esc_html__( 'Number in range from 1 up to 21.', 'swishdesign' ),
				'required' => true,
			),
			'height' => array(
				'value' => '400',
			),
			'width_mode' => array(
				'type' => 'dropdown',
				'value' => array(
					'box-width',
					'full-width',
				),
			),
			'css_class' => array(),
		),
		'display_on_menu' => true,
	) );
}

if ( ! shortcode_exists( 'google_map_embed' ) ) {
	/**
	 * Renders [google_map_embed] shortcode.
	 *
	 * @param  array  $atts     shortcode attributes.
	 * @param  string $content  shortcode content text.
	 * @return string
	 */
	function qeddtp_shortcode_google_map_embed( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'src' => '',
			'height' => '',
			'css_class' => '',
			'view' => '',
		), $atts );

		return QEDDTP()->shortcodes_helper()->render_view( 'templates/shortcodes/google_map_embed', $atts['view'], $atts );
	}

	$sc_helper->add_shortcode( 'google_map_embed', 'qeddtp_shortcode_google_map_embed', array(
		'name' => esc_html__( 'Google Map Embed', 'swishdesign' ),
		'params' => array(
			'src' => array(),
			'height' => array(
				'value' => '450',
			),
			'css_class' => array(),
		),
		'display_on_menu' => true,
	) );
}

if ( ! shortcode_exists( 'title' ) ) {
	/**
	 * Title shortcode rendering function.
	 *
	 * @param  array  $atts     shortcode attributes.
	 * @param  string $content  shortcode content text.
	 * @return string
	 */
	function qeddtp_shortcode_title( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'text' => '',
			'subtitle' => '',
			'size' => 'big',
			'position' => 'left',
			'decoration' => 'on',
			'underline' => 'on',
			'style' => 'dark',
			'css_class' => '',
			'view' => '',
		), $atts );

		$helper = QEDDTP()->shortcodes_helper();

		$atts['decoration'] = $helper->attribute_is_true( $atts['decoration'] );
		$atts['underline'] = $helper->attribute_is_true( $atts['underline'] );

		return $helper->render_view( 'templates/shortcodes/title', $atts['view'], $atts );
	}

	$sc_helper->add_shortcode( 'title', 'qeddtp_shortcode_title', array(
		'name' => esc_html__( 'Title', 'swishdesign' ),
		'params' => array(
			'text' => array(),
			'subtitle' => array(),
			'size' => array(
				'type' => 'dropdown',
				'value' => array(
					'big',
					'small',
				),
			),
			'position' => array(
				'type' => 'dropdown',
				'value' => array(
					'left',
					'center',
				),
			),
			'decoration' => array(
				'type' => 'dropdown',
				'value' => array(
					'on',
					'off',
				),
			),
			'underline' => array(
				'type' => 'dropdown',
				'value' => array(
					'on',
					'off',
				),
			),
			'style' => array(
				'type' => 'dropdown',
				'value' => array(
					'dark',
					'light',
				),
			),
			'css_class' => array(),
		),
		'display_on_menu' => false,
	) );
}

if ( ! shortcode_exists( 'social_icons' ) ) {
	/**
	 * Social icons shortcode rendering function.
	 *
	 * @param  array  $atts     shortcode attributes.
	 * @param  string $content  shortcode content text.
	 * @return string
	 */
	function qeddtp_shortcode_social_icons( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'title' => '',
			'facebook_url' => '',
			'twitter_url' => '',
			'googleplus_url' => '',
			'youtube_url' => '',
			'pinterest_url' => '',
			'linkedin_url' => '',
			'instagram_url' => '',
			'dribbble_url' => '',
			'tumblr_url' => '',
			'vk_url' => '',
			'tripadvisor_url' => '',
			'css_class' => '',
			'view' => '',
		), $atts );

		return QEDDTP()->shortcodes_helper()->render_view( 'templates/shortcodes/social_icons', $atts['view'], $atts );
	}

	$sc_helper->add_shortcode( 'social_icons', 'qeddtp_shortcode_social_icons', array(
		'name' => esc_html__( 'Social Icons', 'swishdesign' ),
		'params' => array(
			'title' => array(
				'value' => 'Follow Us',
			),
			'facebook_url' => array(),
			'twitter_url' => array(),
			'googleplus_url' => array(),
			'youtube_url' => array(),
			'pinterest_url' => array(),
			'linkedin_url' => array(),
			'instagram_url' => array(),
			'dribbble_url' => array(),
			'tumblr_url' => array(),
			'vk_url' => array(),
			'tripadvisor_url' => array(),
			'css_class' => array(),
		),
	) );
}

if ( ! shortcode_exists( 'icon_tick' ) ) {
	/**
	 * Icon tick (+/- check icon) shortcode rendering function.
	 *
	 * @param  array  $atts     shortcode attributes.
	 * @param  string $content  shortcode content text.
	 * @return string
	 */
	function qeddtp_shortcode_icon_tick( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'state' => 'on',
			'css_class' => '',
			'view' => '',
		), $atts );

		$helper = QEDDTP()->shortcodes_helper();

		$atts['state'] = $helper->attribute_is_true( $atts['state'] );

		return $helper->render_view( 'templates/shortcodes/icon_tick', $atts['view'], $atts );
	}

	$sc_helper->add_shortcode( 'icon_tick', 'qeddtp_shortcode_icon_tick', array(
		'name' => esc_html__( 'Icon Tick', 'swishdesign' ),
		'params' => array(
			'state' => array(
				'type' => 'dropdown',
				'value' => array(
					'on',
					'off',
				),
			),
			'css_class' => array(),
		),
	) );
}
//
//if ( ! shortcode_exists( 'icons_set' ) ) {
//	/**
//	 * Icons set shortcode rendering function.
//	 * Container for set [icon_item] shortcodes.
//	 *
//	 * @param  array  $atts     shortcode attributes.
//	 * @param  string $content  shortcode content text.
//	 * @return string
//	 */
//	function qeddtp_shortcode_icons_set( $atts, $content = null ) {
//		$atts = shortcode_atts( array(
//			'row_size' => 3,
//			'css_class' => '',
//			'view' => '',
//		), $atts );
//
//		$GLOBALS['__tmp_icons_set'] = array();
//		do_shortcode( $content );
//		$atts['items'] = $GLOBALS['__tmp_icons_set'];
//		unset( $GLOBALS['__tmp_icons_set'] );
//
//		// need improve regexp - [^"]*, as this one does not allow to use " character in text attribute
//		/*$items = array();
//		if ( preg_match_all('`\[icon_item(?: icon="([^"]*)")?(?: text="([^"]*)")?\]`s', $content, $matches) ) {
//			foreach ($matches[1] as $_item_index => $icon_class) {
//				$item_text = $matches[2][$_item_index];
//				if ( ! $item_text && ! $icon_class ) {
//					continue;
//				}
//				$items[] = array(
//					'icon_class' => $icon_class,
//					'text' => $item_text,
//				);
//			}
//		}*/
//
//		return QEDDTP()->shortcodes_helper()->render_view( 'templates/shortcodes/icons_set', $atts['view'], $atts );
//	}
//
//	$sc_helper->add_shortcode( 'icons_set', 'qeddtp_shortcode_icons_set', array(
//		'name' => esc_html__( 'Icons Set', 'swishdesign' ),
//		'params' => array(
//			'row_size' => array(
//				'type' => 'dropdown',
//				'value' => array(
//					'2',
//					'3',
//					'4',
//				),
//			),
//			'content' => array(
//				'type' => 'textarea',
//				'value' => '[icon_item icon="sd-earth" title="Item1"]text[/icon_item]' .
//				           '[icon_item icon="sd-heart" title="Item2"]text[/icon_item]' .
//				           '[icon_item icon="sd-lifebuoy" title="Item3"]text[/icon_item]',
//			),
//			'css_class' => array(),
//		),
//	) );
//}
//
//if ( ! shortcode_exists( 'icon_item' ) ) {
//	/**
//	 * Icon item shortcode rendering function.
//	 * Used inside [icons_set] shortcode to preset set of icons.
//	 *
//	 * @param  array  $atts     shortcode attributes.
//	 * @param  string $content  shortcode content text.
//	 * @return string
//	 */
//	function qeddtp_shortcode_icon_item( $atts, $content = null ) {
//		shortcode_atts( array(
//			'icon' => '',
//			'title' => '',
//			'title_url' => '',
//		), $atts );
//		$atts['content'] = $content;
//		$GLOBALS['__tmp_icons_set'][] = $atts;
//		return '';
//	}
//
//	add_shortcode( 'icon_item', 'qeddtp_shortcode_icon_item' );
//}
//
if ( ! shortcode_exists( 'qed_btn' ) ) {
	/**
	 * Button shortcode rendering function.
	 *
	 * @param  array  $atts     shortcode attributes.
	 * @param  string $content  shortcode content text.
	 * @return string
	 */
	function qeddtp_shortcode_qed_btn( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'text' => '',
			'url' => '',
			'type' => '',
			'style' => 'default',
			'size' => 'large',
			'corners' => '',
			'light' => '',
			'transparent' => '',
			'icon_class' => '',
			'icon_align' => 'left',
			'css_class' => '',
			'view' => '',
		), $atts );

		$helper = QEDDTP()->shortcodes_helper();

		$atts['light'] = $helper->attribute_is_true( $atts['light'] );
		$atts['transparent'] = $helper->attribute_is_true( $atts['transparent'] );
		$atts['content'] = $content;

		return $helper->render_view( 'templates/shortcodes/qed_btn', $atts['view'], $atts );
	}

	$sc_helper->add_shortcode( 'qed_btn', 'qeddtp_shortcode_qed_btn', array(
		'name' => esc_html__( 'Button', 'swishdesign' ),
		'params' => array(
			'text' => array(),
			'url' => array(),
			'type' => array(
				'type' => 'dropdown',
				'value' => array(
					'link',
					'link in a new tab',
					'button',
					'submit',
				),
			),
			'css_class' => array(),
			'style' => array(
				'type' => 'dropdown',
				'value' => array(
					'',
					'primary',
					'secondary1',
					'secondary2',
				),
			),
			'size' => array(
				'type' => 'dropdown',
				'value' => array(
					'',
					'medium',
					'small',
				),
			),
			'corners' => array(
				'type' => 'dropdown',
				'value' => array(
					'',
					'rounded',
				),
			),
			'light' => array(
				'type' => 'dropdown',
				'value' => array(
					'off',
					'on',
				),
			),
			'transparent' => array(
				'type' => 'dropdown',
				'value' => array(
					'off',
					'on',
				),
			),
			'icon_class' => array(),
			'icon_align' => array(
				'type' => 'dropdown',
				'value' => array(
					'left',
					'right',
				),
			),
		),
	) );
}
//
//if ( ! shortcode_exists( 'qed_icon' ) ) {
//	/**
//	 * Renders [qed_icon] shortcode.
//	 *
//	 * @param  array  $atts     shortcode attributes.
//	 * @param  string $content  shortcode content text.
//	 * @return string
//	 */
//	function qeddtp_shortcode_qed_icon( $atts, $content = null ) {
//		$atts = shortcode_atts( array(
//			'icon' => '',
//			'css_class' => '',
//			'view' => '',
//		), $atts );
//
//		return QEDDTP()->shortcodes_helper()->render_view( 'templates/shortcodes/qed_icon', $atts['view'], $atts );
//	}
//
//	$sc_helper->add_shortcode( 'qed_icon', 'qeddtp_shortcode_qed_icon', array(
//		'name' => esc_html__( 'Swish Design Icons', 'swishdesign' ),
//		'params' => array(
//			'icon' => array(
//				'type' => 'iconpicker',
//				'settings' => array(
//					'emptyIcon' => false,
//					'type' => 'swishdesign',
//				),
//				// hack for iconpicker selector, allows load icons list via ajax request
//				'values' => 'qed_get_swishdesign_icons',
//			),
//			'css_class' => array(),
//		),
//	) );
//
//	function qeddtp_shortcode_ajax_qed_icon_get_icons_list() {
//		echo wp_json_encode( array(
//			'list' => apply_filters( 'vc_iconpicker-type-swishdesign', array(), false )
//		) );
//
//		exit();
//	}
//	add_action( 'wp_ajax_qed_get_swishdesign_icons', 'qeddtp_shortcode_ajax_qed_icon_get_icons_list' );
//}
