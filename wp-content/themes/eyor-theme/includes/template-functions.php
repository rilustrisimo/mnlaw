<?php
/**
 * Functions related to different part rendering.
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
// Rendering: helper functions
// -----------------------------------------------------------------#
if ( ! function_exists( 'qed_get_the_post_thumbnail' ) ) {
	/**
	 * Returns post featured image in requested size.
	 * Returns grey image in case if after import attachments have not been downloaded.
	 *
	 * @param  int    $post_id             post id.
	 * @param  string $size                code of the image size.
	 * @param  array  $attributes          attributes that should be applied for the img tag.
	 * @param  array  $undefined_size      size of the image that should be returned (used if $size is undefined).
	 * @return string
	 */
	function qed_get_the_post_thumbnail( $post_id = null, $size = 'full', array $attributes = array(), array $undefined_size = array( 770, 514 ) ) {
		$result = '';

		if ( null === $post_id ) {
			$post_id = get_the_ID();
		}

		if ( ! has_post_thumbnail( $post_id ) ) {
			return $result;
		}

		if ( $post_id ) {
			$result = get_the_post_thumbnail( $post_id, $size, $attributes );
		}

		if ( ! $result ) {
			$image_manger = qed_di( 'image_manager' );
			if ( ! $image_manger ) {
				return $result;
			}

			$size_details = $image_manger->get_image_size_details( $size );
			if ( $size_details ) {
				$width = isset( $size_details['width'] ) ? $size_details['width'] : null;
				$height = isset( $size_details['height'] ) ? $size_details['height'] : null;

				if ( $height > $width * 5 ) {
					$height = round( $width * 1.5 );
				}
			} else {
				$width = isset( $undefined_size[0] ) ? $undefined_size[0] : null;
				$height = isset( $undefined_size[1] ) ? $undefined_size[1] : null;
			}

			if ( $width && $height ) {
				$result = $image_manger->get_placehold_image( $width, $height, '', true, $attributes );
			}
			// Else throw new Exception("Image size {$size} not defined."); .
		}

		return $result;
	}
}

if ( ! function_exists( 'qed_placeholder_img_src' ) ) {
	/**
	 * Returns url to the placeholder image saved in theme options.
	 *
	 * @param  string $size image size code.
	 * @return string
	 */
	function qed_placeholder_img_src() {
		static $url;
		if ( null === $url ) {
			$url = qed_get_option( 'placeholder_image', 'option', '' );
			if ( ! $url ) {
				$url = get_template_directory_uri() . '/assets/images/placeholder.png';
			}
		}

		return $url;
	}
}

if ( ! function_exists( 'qed_placeholder_img' ) ) {
	/**
	 * Returns html with img that renders placeholder image.
	 *
	 * @param  string $size image size code.
	 * @return string
	 */
	function qed_placeholder_img( $size = 'large' ) {
		$dimensions = qed_di( 'image_manager' )->get_image_size_details( $size );
		if ( ! $dimensions ) {
			$dimensions = qed_di( 'image_manager' )->get_image_size_details( 'large' );
		}

		return apply_filters( 'qed_placeholder_img', '<img src="' . qed_placeholder_img_src( $size ) . '" alt="' . esc_attr__( 'Placeholder', 'swishdesign' ) . '" width="' . esc_attr( $dimensions['width'] ) . '" class="woocommerce-placeholder wp-post-image" height="' . esc_attr( $dimensions['height'] ) . '" />', $size, $dimensions );
	}
}

if ( ! function_exists( 'qed_render_template_part' ) ) {
	/**
	 * Analog for the get_template_part function.
	 * Allows pass params to view file.
	 *
	 * @param  string  $template_name    view name.
	 * @param  string  $template_postfix optional postfix.
	 * @param  array   $data            assoc array with variables that should be passed to view.
	 * @param  boolean $return          if result should be returned instead of outputting.
	 * @return string
	 */
	function qed_render_template_part( $template_name, $template_postfix = '', array $data = array(), $return = false ) {
		static $app;
		if ( ! $app ) {
			$app = qed_di( 'app' );
		}
		return $app->render_template_part( $template_name, $template_postfix, $data, $return );
	}
}

if ( ! function_exists( 'qed_the_content' ) ) {
	/**
	 * Determines what function should be used for content section rendering the_excerpt or
	 * the_content based on 'is_excerpt' theme option value.
	 *
	 * @return void
	 */
	function qed_the_content() {
		if ( qed_get_option( 'is_excerpt', 'option' ) ) {
			the_excerpt();
		} else {
			the_content( qed_get_option( 'excerpt_text', 'option' ) );
		}
	}
}

if ( ! function_exists( 'qed_wp_title_filter' ) ) {
	/**
	 * Title rendering filter function.
	 *
	 * @param  string $title
	 * @param  string $sep
	 * @param  string $seplocation
	 * @return string
	 */
	function qed_wp_title_filter( $title, $sep, $seplocation ) {
		if ( is_feed() ) {
			return $title;
		}

		if ( ! $title) {
			$title = get_bloginfo('name', 'display') . ( $sep && 'right' == $seplocation ? " $sep " : '' );
		}

		if (!$sep) {
			return trim($title);
		}

		global $page, $paged;
		$fullSep = $sep ? " $sep " : ' ';
		$firstSep = 'right' == $seplocation ? '' : $fullSep;

		if ( ( is_home() || is_front_page() ) && ($site_description = get_bloginfo( 'description', 'display' ))) {
			$title .= $firstSep . $site_description;
		} else {
			$title .= $firstSep . get_bloginfo( 'name', 'display' );
		}

		// Add a page number if necessary:
		if ( ( $paged > 1 || $page > 1 ) && ! is_404() ) {
			$title .= $fullSep . sprintf( esc_html__( 'Page %s', 'swishdesign' ), max( $paged, $page ) );
		}

		return $title;
	}
	// add_filter( 'wp_title', 'qed_wp_title_filter', 10, 3 );
}

if ( ! function_exists( 'qed_check_if_home' ) ) {
	/**
	 * Check current page is home page helper.
	 *
	 * @return string
	 */
	function qed_check_if_home() {
		$current = '';
		if ( is_front_page() && is_home() ) {
			// Default homepage.
			$current = 'default';
		} elseif ( is_front_page() ) {
			// static homepage.
			$current = 'home';
		} elseif ( is_home() ) {
			// blog page.
			$current = 'blog';
		}

		return $current;
	}
}

if ( ! function_exists( 'qed_show_blog_sidebar' ) ) {
	/**
	 * Check current page if blog home page and if set to show sidebar.
	 *
	 * @return string
	 */
	function qed_show_blog_sidebar() {
		$status = false;
		switch (true) {
			case 'blog' != qed_check_if_home() && qed_get_option('single_show_sidebar', 'option') && is_active_sidebar( 'sidebar' ):
				$status = true;
				break;
			case 'blog' == qed_check_if_home() && qed_get_option('blog_show_sidebar', 'option') && is_active_sidebar( 'sidebar' ):
				$status = true;
				break;
			default:
				$status = false;
				break;
		}

		return $status;
	}
}

if ( ! function_exists( 'qed_is_mobile' ) ) {
	/**
	 * Check device if mobile.
	 *
	 * @return bool
	 */
	function qed_is_mobile() {
		$detect = new Mobile_Detect;
		$mobile = false;
		if ( $detect->isMobile() || $detect->isTablet() ) {
			$mobile = true;
		}
		return $mobile;
	}
}

if ( ! function_exists( 'qed_ajax_action_get_header_section' ) ) {
	/**
	 * Ajax method to get the header section.
	 *
	 * @return mixed
	 */
	function qed_ajax_action_get_header_section() {
		$response = array(
			'success' => false,
		);

		$nonce = $_POST['headerSectionNonce'];
		$device_type = $_POST['type'];
		$page_id = (int) $_POST['pageId'];
		$section_meta_service = qed_di( 'header_section' );
		$section_meta = $section_meta_service ? $section_meta_service->get_section_meta_by_id( $page_id ) : array();
		$option_pre_key = ( $device_type ) ? 'mobile_' : '';

		$response['id'] = $page_id;
		$response['meta'] = $section_meta;


		$mode = isset( $section_meta[ $device_type ][ $option_pre_key . 'section_mode' ] ) ? $section_meta[ $device_type ][ $option_pre_key . 'section_mode' ] : 'hide';
		if ( 'banner' === $mode && empty( $section_meta[ $device_type ][ $option_pre_key . 'banner_image' ] ) ) {
			$mode = 'hide';
		}

		if ( wp_verify_nonce( $nonce, 'swish-header-section-nonce' ) ) {

			switch ( $mode ) {
				case 'banner':
					$response['type'] = 'banner';
					ob_start();
					qed_render_template_part( 'templates/header/ajax/banner', '', $section_meta );
					$response['html'] = ob_get_clean();
					$response['success'] = true;
					break;

				case 'slider':
					$is_home = ( 'home' === qed_check_if_home() || 'default' === qed_check_if_home() ) ? true : false;
					$slider_id = 'bxslider' . get_the_ID();
					$bxslider_options = array();
					if ( $is_home ) {
						$bxslider_options['pause'] = intval( $section_meta[ $device_type ][ $option_pre_key . 'slider_timeout' ] ) * 1000;
						$bxslider_options['speed'] = intval( $section_meta[ $device_type ][ $option_pre_key . 'slider_fadespeed' ] ) * 1000;
						$bxslider_options['mode'] = 'fade';
						$bxslider_options['infiniteLoop']   = true;
					}
					$bxslider_options['pager'] = false;
					$bxslider_options['controls'] = false;

					$response['type'] = 'slider';
					$response['config'] = array(
						'sliderSelector' => '#' . $slider_id,
						'sliderType'     => $device_type,
						'sliderOptions'  => $bxslider_options,
					);
					ob_start();
					qed_render_template_part( 'templates/header/ajax/slider', '', $section_meta );
					$response['html'] = ob_get_clean();
					$response['success'] = true;
					break;
				case 'hide':
					$response['type'] = 'hide';
					break;
			}
		};

		if ( ! $response['success'] ) {
			header( 'HTTP/1.1 400 Bad Request', true, 400 );
		}

		wp_send_json( $response );
		die();
	}
	add_action( 'wp_ajax_get-header-section', 'qed_ajax_action_get_header_section' );
	add_action( 'wp_ajax_nopriv_get-header-section', 'qed_ajax_action_get_header_section' );
}

if ( ! function_exists( 'qed_get_formatted_mobile_number' ) ) {
	/**
	 * Get formated mobile number mobile.
	 *
	 * @return string
	 */
	function qed_get_formatted_mobile_number( $number ) {
		return '+61' . preg_replace("/[\s]+/", "", $number);
	}
}

if ( ! function_exists( 'qed_limit_title_length' ) ) {
	/**
	 * Custom title text generation function.
	 *
	 * @param  string $string     text that should be truncated.
	 * @param  int    $char_limit max allowed number of characters.
	 * @return string
	 */
	function qed_limit_title_length( $string, $char_limit ) {
		if ( $string ) {
			if ( '0' === $char_limit || 0 === $char_limit ) {
				return '';
			}
			$string = strip_shortcodes( $string );
			if ( strlen($string) > $char_limit ) {
				$string = substr($string, 0, strrpos(substr($string, 0, $char_limit), ' ')) . '...';
			}
		}

		return $string;
	}
}

// -----------------------------------------------------------------#
// Rendering: excerpt
// -----------------------------------------------------------------#
if ( ! function_exists( 'qed_do_excerpt' ) ) {
	/**
	 * Custom excerpt text generation function.
	 *
	 * @param  string $string     text that should be truncated.
	 * @param  int    $word_limit max allowed number of words.
	 * @return string
	 */
	function qed_do_excerpt( $string, $word_limit ) {
		if ( $string ) {
			if ( '0' === $word_limit || 0 === $word_limit ) {
				return '';
			}
			$string = strip_shortcodes( $string );
			$string = $word_limit > 0 ? wp_trim_words( $string, $word_limit, '...') : wp_strip_all_tags( $string );
		}

		return $string;
	}
}

if ( ! function_exists( 'qed_get_short_description' ) ) {
	/**
	 * Returns short description for current post or for the specefied post.
	 *
	 * @param  WP_Post $forPost    optional post object (if empty - current post will be used).
	 * @param  int     $word_limit max allowed words count.
	 * @return string
	 */
	function qed_get_short_description( $forPost = null, $word_limit = null ) {
		if ( null === $forPost ) {
			$forPost = get_post();
		}

		if ( ! $forPost ) {
			return '';
		}

		$text = $forPost->post_excerpt ? $forPost->post_excerpt : $forPost->post_content;
		if ( $text ) {
			return qed_do_excerpt( $text, $word_limit );
		} else {
			return $text;
		}
	}
}

if ( ! function_exists( 'qed_excerpt_more_link' ) ) {
	/**
	 * Filter for formatting excerpt more link.
	 * Depends on theme options.
	 *
	 * @return string
	 */
	function qed_excerpt_more_link() {
		if ( qed_di( 'register' )->get_var( 'disable_excerpt_more_link' ) ) {
			return '';
		}
		static $moreText;
		if ( null === $moreText ) {
			$moreText = esc_html( qed_get_option( 'excerpt_text', 'option' ) );
		}

		return sprintf(
			'<div class="qed-excerpt-more-link-wrap padding-top text-center"><a href="%s" class="qedbtn qedbtn--small qedbtn--transparent">%s</a></div>',
			esc_url( get_permalink() ),
			esc_html( $moreText )
		);
	}
	add_filter( 'excerpt_more', 'qed_excerpt_more_link', 9, 1 );
}

if ( ! function_exists( 'qed_custom_excerpt_length' ) ) {
	/**
	 * Filter for excerpt_length hook.
	 *
	 * @param  int $length current length value.
	 * @return int
	 */
	function qed_custom_excerpt_length($length) {
		return qed_get_option( 'excerpt_length', 'option' );
	}
	add_filter( 'excerpt_length', 'qed_custom_excerpt_length', 999 );
}

if ( ! function_exists( 'qed_content_more_link_filter' ) ) {
	/**
	 * Filter for content more link text.
	 *
	 * @param  string $link     link html.
	 * @param  string $linkText text.
	 * @return string
	 */
	function qed_content_more_link_filter($link, $linkText = '') {
		if ( ! $link ) {
			return '';
		}

		static $moreText;
		if ( null === $moreText ) {
			$moreText = esc_html( qed_get_option( 'excerpt_text', 'option' ) );
		}

		return sprintf(
			'<div class="padding-top text-center"><a href="%s" class="qedbtn qedbtn--medium qedbtn--rounded qedbtn--transparent">%s<i class="qedbtn__icon qedbtn__icon--right fa fa-long-arrow-right"></i></a></div>',
			esc_url( get_permalink() ),
			esc_html( $moreText )
		);
	}
	add_filter( 'the_content_more_link', 'qed_content_more_link_filter', 10, 2 );
}

if ( ! function_exists( 'qed_fix_broken_p' ) ) {
	/**
	 * Removes and fixes broken P tags.
	 *
	 * @param  string $content
	 * @return string
	 */
	function qed_fix_broken_p( $content ) {
		// $is_vc_frontend = isset( $_GET['vc_editable'] ) && isset ( $_GET['vc_post_id'] );

		// to prevent processing content that contains revslider, as force_balance_tags will broke revslider javascript
		if ( strpos( $content, '<script' ) !== false || strpos( $content, 'class="rev_slider' ) !== false ) {
			return $content;
		}

		// removing broken P tags added by wpuatop
		$result = preg_replace(
			array(
				'`<p>\s*<div([^>]*)>(?:\s*</p>)?|<div([^>]*)>\s*</p>`', // <p><div></p>
				'`(<p>\s*)?</div>\s*</p>|<p>\s*</div>`', // <p></div></p>
			),
			array(
				'<div$1$2>',
				'</div>',
			),
			$content
		);
		// fixing unclosed/unopened P tags
		return force_balance_tags( $result );
	}
	add_filter( 'the_content', 'qed_fix_broken_p', 11, 1 );
}

if ( ! function_exists( 'qed_esc_text' ) ) {
	/**
	 * Escapes multi line text.
	 *
	 * @param  string  $text
	 * @param  string  $context      name of the context
	 * @param  boolean $is_mutliline is multi line text
	 * @return string
	 */
	function qed_esc_text( $text, $context = 'post', $is_mutliline = false ) {
		$result = '';
		if ($text) {
			$tagsWhiteList = wp_kses_allowed_html( $context );
			$result = wp_kses( $text, $tagsWhiteList );
			if ($is_mutliline) {
				$result = nl2br( $result );
			}
		}
		return $result ? force_balance_tags( $result ) : '';
	}
}

if ( ! function_exists( 'qed_kses_allowed_html_filter' ) ) {
	/**
	 * Applies specific tags settings based on context.
	 *
	 * @param  assoc  $tags    list of allowed tags.
	 * @param  string $context text content id.
	 * @return assoc
	 */
	function qed_kses_allowed_html_filter( $tags, $context = '' ) {
		switch ( $context ) {
			case 'option_input':
				$tags['span'] = array(
					'class' => true,
					'id' => true,
				);
				$tags['i'] = array(
					'class' => true,
					'id' => true,
				);
				$tags['a'] = array(
					'title' => true,
					'href' => true,
					'target' => true,
					'class' => true,
					'id' => true,
				);
				break;
		}
		return $tags;
	}
	add_filter( 'wp_kses_allowed_html', 'qed_kses_allowed_html_filter', 10, 2 );
}

// -----------------------------------------------------------------#
// Rendering: paginations
// -----------------------------------------------------------------#
if ( ! function_exists( 'qed_render_pagination' ) ) {
	/**
	 * Renders posts pagination.
	 *
	 * @param  string   $before prefix text.
	 * @param  string   $after  postfix text.
	 * @param  WP_Query $query  query.
	 * @return void
	 */
	function qed_render_pagination( $before = '', $after = '', $query = null ) {
		global $wp_query;
		if ( ! $query ) {
			$query = $wp_query;
		}

		if ( $query->max_num_pages <= 1 ) {
			return '';
		}

		$big = 999999999;
		$current = max( 1, get_query_var( 'paged' ) );
		$prev_link_text = '<i class="fa fa-angle-left"></i>';
		$next_link_text = '<i class="fa fa-angle-right"></i>';

		$paginate = paginate_links( array(
			'base' => str_replace( $big, '%#%', get_pagenum_link( $big, false ) ),
			'type' => 'array',
			'total' => $query->max_num_pages,
			'format' => '?paged=%#%',
			'mid_size' => 1,
			'current' => $current,
			'prev_text' => $prev_link_text,
			'next_text' => $next_link_text,
		) );

		$pages_html = '';
		$prev_link = '<div class="navigation__item navigation__prev navigation__item--disable"><span class="prev page-numbers">' . $prev_link_text . '</span></div>';
		$next_link = '<div class="navigation__item navigation__next navigation__item--disable"><span class="next page-numbers">' . $next_link_text . '</span></div>';

		foreach ( $paginate as $index => $page ) {
			if ( preg_match( '/class="prev page-numbers"/', $page ) ) {
				$prev_link = '<div class="navigation__item navigation__prev">' . $page . '</div>';
				continue;
			}

			if ( preg_match( '/class="next page-numbers"/', $page ) ) {
				$next_link = '<div class="navigation__item navigation__next">' . $page . '</div>';
				continue;
			}

			$pages_html .= '<div class="navigation__item">' . $page . '</div>';
		}

		printf(
			'%s<div class="navigation">' .
			'<div class="navigation__content"><div class="navigation__items">%s%s%s</div></div>' .
			'</div>%s',
			$before,
			$prev_link,
			$pages_html,
			$next_link,
			$after
		);
	}
}

if ( ! function_exists( 'qed_render_post_pagination' ) ) {
	/**
	 * Renders pagination for post pages.
	 *
	 * @return void
	 */
	function qed_render_post_pagination() {
		global $page, $numpages;
		if ( $numpages < 2 ) {
			return '';
		}

		$res = wp_link_pages(array(
			'before' => '',
			'after' => '',
			'separator' => "\n",
			'next_or_number' => 'number',
			'echo' => false,
		));

		$parts = explode( "\n", $res );
		if ( count( $parts ) < 2 ) {
			return '';
		}

		$activeIndex = $page -1;
		$itemsHtml = '';

		foreach ( $parts as $index => $itemHtml ) {
			if ( $index === $activeIndex ) {
				$itemsHtml .= '<div class="post-page-navigation__item"><span class="current">' . $page . '</span></div>';
			} else {
				$itemsHtml .= '<div class="post-page-navigation__item">' . $itemHtml . '</div>';
			}
		}

		$prev = $page - 1;
		$prevText = esc_html__( 'Previous', 'swishdesign' );
		if ( $prev ) {
			$linkPrev = '<div class="post-page-navigation__item post-page-navigation__prev">' . _wp_link_page( $prev ) . $prevText . '</a></div>';
		} else {
			$linkPrev = '<div class="post-page-navigation__item post-page-navigation__item--disable post-page-navigation__prev"><span>' . $prevText . '</span></div>';
		}

		$next = $page + 1;
		$nextText = esc_html__( 'Next', 'swishdesign' );
		if ( $next <= $numpages ) {
			$linkNext = '<div class="post-page-navigation__item post-page-navigation__next">' . _wp_link_page( $next ) . $nextText . '</a></div>';
		} else {
			$linkNext = '<div class="post-page-navigation__item post-page-navigation__item--disable post-page-navigation__next"><span>' . $nextText . '</span></div>';
		}

		printf(
			'<div class="post-page-navigation">' .
			'%s<div class="post-page-navigation__items">%s</div>%s' .
			'</div>',
			$linkPrev,
			$itemsHtml,
			$linkNext
		);
	}
}

if ( ! function_exists( 'qed_comments_pagination' ) ) {
	/**
	 * Comments pagination functionality.
	 *
	 * @return string
	 */
	function qed_comments_pagination() {
		$numpages = get_option( 'page_comments' ) ? get_comment_pages_count() : 0;
		if ( $numpages <= 1 ) {
			return '';
		}

		$prev_next_link_title = 'prev_next_link';
		$paginationLinks = paginate_comments_links(array(
			'show_all' => true,
			'type' => 'array',
			'echo' => false,
			'prev_text' => $prev_next_link_title,
			'next_text' => $prev_next_link_title,
		));

		$linksHtml = '';
		foreach ( $paginationLinks as $link ) {
			if ( false === strpos( $link, $prev_next_link_title ) ) {
				$linksHtml .= '<div class="comments__navigation__item">' . $link . '</div>';
			}
		}

		$nextLink = get_next_comments_link( esc_html__( 'next', 'swishdesign' ) );
		$prevLink = get_previous_comments_link( esc_html__( 'previous', 'swishdesign' ) );

		$nextLink = ( ! empty( $nextLink ))
			?
			'<div class="comments__navigation__item comments__navigation__next">' . $nextLink . '</div>'
			:
			'<div class="comments__navigation__item comments__navigation__item--disable comments__navigation__next"><span>' . esc_html__( 'next', 'swishdesign' ) . '</span></div>';

		$prevLink = ( ! empty( $prevLink ))
			?
			'<div class="comments__navigation__item comments__navigation__prev">' . $prevLink . '</div>'
			:
			'<div class="comments__navigation__item comments__navigation__item--disable comments__navigation__prev"><span>' . esc_html__( 'previous', 'swishdesign' ) . '</span></div>';

		printf(
			'<div class="comments__navigation">' .
			'<div class="comments__navigation__content padding-left padding-right">' .
			'%s<div class="comments__navigation__items">%s</div>%s' .
			'</div>' .
			'</div>',
			$prevLink,
			$linksHtml,
			$nextLink
		);
	}
}

if ( ! function_exists( 'qed_post_gallery_filter' ) ) {
	/**
	 * Filter used to customize galleries output.
	 *
	 * @param  string $empty
	 * @param  assoc  $attr gallery shortcode attributes
	 * @return string
	 */
	function qed_post_gallery_filter( $empty, $attr ) {
		global $post;

		extract( shortcode_atts( array(
			'order' => 'ASC',
			'orderby' => 'menu_order ID',
			'id' => $post ? $post->ID : 0,
			'include' => '',
			'exclude' => '',
			// custom attributes
			'layout' => 'default',
			'pagination' => '',
			'filter' => '',
			'single_page' => '',
			'columns' => 3,
		), $attr, 'gallery' ) );

		// get attachments set
		$queryArgs = array(
			'post_status' => 'inherit',
			'post_type' => 'attachment',
			'post_mime_type' => 'image',
			'order' => $order,
			'orderby' => $orderby,
		);

		$id = intval( $id );

		if ( $include ) {
			$queryArgs['include'] = $include;
		} else {
			$queryArgs['post_parent'] = $id;
			if ( $exclude ) {
				$queryArgs['exclude'] = $exclude;
			}
		}

		$attachments = get_posts( $queryArgs );
		if ( ! $attachments ) {
			if ( $include && ! is_feed() ) {
				// allow to render gallery with placeholders (for galleries that have been imported with content)
			} else {
				return '';
			}
		}
		// For RSS
		if ( is_feed() ) {
			$output = "\n";
			foreach ( $attachments as $attachment ) {
				$output .= wp_get_attachment_link( $attachment->ID ) . "\n";
			}
			return $output;
		}

		$defaultThumbSize = 'thumb_gallery';
		$defaultFullSize = 'full';

		$galleryLayouts = array(
			'default' => array(
				'showCategories' => true,
				'allowPagination' => true,
				'thumbSize' => $defaultThumbSize,
			),
		);

		if ( ! $layout || ! isset( $galleryLayouts[$layout] ) ) {
			$layout = 'default';
		}

		$layoutConfig = $galleryLayouts[$layout];
		$thumbSize = isset( $layoutConfig['thumbSize'] ) ? $layoutConfig['thumbSize'] : $defaultThumbSize;
		$fullSize = isset( $layoutConfig['fullSize'] ) ? $layoutConfig['fullSize'] : $defaultFullSize;

		$showCategories = ! empty( $layoutConfig['showCategories'] ) && qed_check( 'media_category_taxonomy_exists' );
		$is_filter = qed_di( 'shortcodes_helper' )->attribute_is_true( $filter );
		if ( $is_filter && ! $showCategories ) {
			$is_filter = false;
		}

		$is_pagination = qed_di( 'shortcodes_helper' )->attribute_is_true( $pagination );
		if ( empty( $layoutConfig['allowPagination'] ) && $is_pagination ) {
			$is_pagination = false;
		}

		$gallery_images = array();
		$full_categories_list = array();
		if ( $attachments ) {
			foreach ( $attachments as $attachment ) {
				$attachemntId = $attachment->ID;

				// Get image link to a specific sizes
				// Image attribute [0] => url [1] => width [2] => height
				$image_attributes_full = wp_get_attachment_image_src( $attachemntId, $fullSize );
				$image_attributes_custom_size = wp_get_attachment_image_src( $attachemntId, $thumbSize );
				$link_full = ! empty( $image_attributes_full[0] ) ? $image_attributes_full[0] : '';
				$link_custom_size = ! empty( $image_attributes_custom_size[0] ) ? $image_attributes_custom_size[0] : '';

				// categories
				$image_categories = array();
				if ( $showCategories ) {
					$taxonomies = get_the_terms( $attachemntId, 'media_category' ); // 'category'
					if ( $taxonomies ) {
						foreach ( $taxonomies as $taxonomy ) {
							$full_categories_list[$taxonomy->slug] = $taxonomy->name;
							$image_categories[$taxonomy->slug] = $taxonomy->name;
						}
					}
				}

				$alt = get_post_meta( $attachemntId, '_wp_attachment_image_alt', true );

				$gallery_images[] = array(
					'id' => $attachemntId,
					'link_full' => $link_full,
					'link_custom_size' => $link_custom_size,
					'title' => $attachment->post_title,
					'categories' => $image_categories,
					'alt' => $alt ? $alt : $attachment->post_title,
				);
			}
		} elseif ( $include ) {
			$imageManager = qed_di( 'image_manager' );
			$fullSizeDetails = $imageManager->get_image_size_details( $fullSize == 'full' ? 'large' : $fullSize );
			$thumbSizeDetails = $imageManager->get_image_size_details( $thumbSize );

			$includeIds = explode( ',', trim( $include,', ' ) );
			foreach ( $includeIds as $attachemntId ) {
				$dummyTitle = '#' . $attachemntId;
				$placeholdText = urlencode( $dummyTitle ); // find why additional encode is required

				$placeholdThumbUrl = $imageManager->get_placehold_image( $thumbSizeDetails['width'], $thumbSizeDetails['height'], $placeholdText );

				$fullImageUrl = $fullSizeDetails
					? $imageManager->get_placehold_image( $fullSizeDetails['width'], $fullSizeDetails['height'], $placeholdText )
					: $placeholdThumbUrl;

				$gallery_images[] = array(
					'id' => $attachemntId,
					'link_full' => $fullImageUrl,
					'link_custom_size' => $placeholdThumbUrl,
					'title' => $dummyTitle,
					'categories' => array(),
					'alt' => $dummyTitle,
				);
			}
		}

		if ( ! $gallery_images ) {
			return '';
		}

		$output = '';

		// get gallery id
		static $galleryCounter;
		if ( null == $galleryCounter ) {
			$galleryCounter = 1;
		} else {
			$galleryCounter++;
		}
		$galleryId = 'gallery_' . $galleryCounter;

		$classWithBanner = qed_di( 'register' )->get_var( 'is_banner' ) ? ' gallery--withbanner' : '';
		$classSinglePageMode = qed_di( 'shortcodes_helper' )->attribute_is_true( $single_page ) && $is_filter ? ' gallery--page' : '';
		$output .= '<div id="' . esc_attr( $galleryId ) . '" class="gallery' . esc_attr( $classSinglePageMode ) . esc_attr( $classWithBanner ) . '">';

		if ( $is_filter && $full_categories_list ) {
			$filterHtml = '<div class="gallery__navigation margin-bottom">' .
				'<ul>' .
				'<li class="gallery__navigation__item-current"><a href="#" data-filterid="all">' . esc_html__( 'all', 'swishdesign' ) . '</a></li>';

			foreach ( $full_categories_list as $category_slug => $category_name ) {
				$filterHtml .= '<li><a href="#" data-filterid="' . esc_attr( $category_slug ) . '">' . esc_html( $category_name ) . '</a></li>';
			}

			$filterHtml .= '</ul></div>';

			$output .= $filterHtml;
		}

		ob_start();
		include locate_template( 'templates/gallery/' . $layout . '.php' );
		$output .= ob_get_clean();

		if ( $is_pagination ) {
			wp_enqueue_script( 'jPages' );
			$output .= '<div class="pagination margin-top"></div>';
		}

		$output .= '</div>';

		return $output;
	}

	add_filter( 'post_gallery', 'qed_post_gallery_filter', 10, 2 );
}

if ( ! function_exists( 'qed_get_footer_columns' ) ) {
	/**
	 * Returns number of columns that should be rendered in the footer.
	 *
	 * @return int
	 */
	function qed_get_footer_columns() {
		$footerLayout = qed_get_option( 'footer_layout', 'option' );
		$laoutToColumns = array(
			'3columns' => 3,
			'2columns' => 2,
		);
		return isset( $laoutToColumns[$footerLayout] ) ? $laoutToColumns[$footerLayout] : 4;
	}
}

// -----------------------------------------------------------------#
// Rendering: form helper functions
// -----------------------------------------------------------------#
if ( ! function_exists( 'qed_load_datepicker_assets' ) ) {
	/**
	 * Loads assets related to the datepicker GUI component.
	 *
	 * @return void
	 */
	function qed_load_datepicker_assets() {
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'jquery-ui-datepicker-custom' );

		wp_localize_script( 'jquery-ui-datepicker', 'ThemeSDDatepickerCfg', array(
			'firstDay' => get_option('start_of_week', 0),
			'monthNames' => array(
				_x( 'January', 'datepicker', 'swishdesign' ),
				_x( 'February', 'datepicker', 'swishdesign' ),
				_x( 'March', 'datepicker', 'swishdesign' ),
				_x( 'April', 'datepicker', 'swishdesign' ),
				_x( 'May', 'datepicker', 'swishdesign' ),
				_x( 'June', 'datepicker', 'swishdesign' ),
				_x( 'July', 'datepicker', 'swishdesign' ),
				_x( 'August', 'datepicker', 'swishdesign' ),
				_x( 'September', 'datepicker', 'swishdesign' ),
				_x( 'October', 'datepicker', 'swishdesign' ),
				_x( 'November', 'datepicker', 'swishdesign' ),
				_x( 'December', 'datepicker', 'swishdesign' ),
			),
			'monthNamesShort' => array(
				_x( 'Jan', 'datepicker', 'swishdesign' ),
				_x( 'Feb', 'datepicker', 'swishdesign' ),
				_x( 'Mar', 'datepicker', 'swishdesign' ),
				_x( 'Apr', 'datepicker', 'swishdesign' ),
				_x( 'May', 'datepicker', 'swishdesign' ),
				_x( 'Jun', 'datepicker', 'swishdesign' ),
				_x( 'Jul', 'datepicker', 'swishdesign' ),
				_x( 'Aug', 'datepicker', 'swishdesign' ),
				_x( 'Sep', 'datepicker', 'swishdesign' ),
				_x( 'Oct', 'datepicker', 'swishdesign' ),
				_x( 'Nov', 'datepicker', 'swishdesign' ),
				_x( 'Dec', 'datepicker', 'swishdesign' ),
			),
			'dayNames' => array(
				_x( 'Sunday', 'datepicker', 'swishdesign' ),
				_x( 'Monday', 'datepicker', 'swishdesign' ),
				_x( 'Tuesday', 'datepicker', 'swishdesign' ),
				_x( 'Wednesday', 'datepicker', 'swishdesign' ),
				_x( 'Thursday', 'datepicker', 'swishdesign' ),
				_x( 'Friday', 'datepicker', 'swishdesign' ),
				_x( 'Saturday', 'datepicker', 'swishdesign' ),
			),
			'dayNamesShort' => array(
				_x( 'Sun', 'datepicker', 'swishdesign' ),
				_x( 'Mon', 'datepicker', 'swishdesign' ),
				_x( 'Tue', 'datepicker', 'swishdesign' ),
				_x( 'Wed', 'datepicker', 'swishdesign' ),
				_x( 'Thu', 'datepicker', 'swishdesign' ),
				_x( 'Fri', 'datepicker', 'swishdesign' ),
				_x( 'Sat', 'datepicker', 'swishdesign' ),
			),
			'dayNamesMin' => array(
				_x( 'Su', 'datepicker', 'swishdesign' ),
				_x( 'Mo', 'datepicker', 'swishdesign' ),
				_x( 'Tu', 'datepicker', 'swishdesign' ),
				_x( 'We', 'datepicker', 'swishdesign' ),
				_x( 'Th', 'datepicker', 'swishdesign' ),
				_x( 'Fr', 'datepicker', 'swishdesign' ),
				_x( 'Sa', 'datepicker', 'swishdesign' ),
			)
		) );
	}
}

// -----------------------------------------------------------------#
// Rendering: testimonials helper functions
// -----------------------------------------------------------------#
if ( ! function_exists( 'qed_is_display_testimonials' ) ) {
	/**
	 * Check if testimonials is activated on page.
	 *
	 * @return bool
	 */
	function qed_is_display_testimonials() {
		$current_id = get_the_ID();// Get the current page ID
		$active_on = qed_get_option('testimonials_display_on', 'option');

		switch( true ) {
			case in_array( $current_id, $active_on ):
				$status = true;
				break;
			default:
				$status = false;
				break;
		}

		return $status;
	}
}
