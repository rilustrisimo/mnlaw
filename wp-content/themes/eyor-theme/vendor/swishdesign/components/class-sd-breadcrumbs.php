<?php
/**
 * Breadcrumbs generation class.
 *
 * @author    eyorsogood.com, Rouie Ilustrisimo
 * @package   SwishDesign/Components
 * @version   1.0.0
 */

/**
 * No direct access to this file.
 *
 * @since 1.0.0 SwishDesign/Components
 */
defined( 'ABSPATH' ) || die();

/**
 * Class Breadcrumbs
 */
class SD_Breadcrumbs extends SD_Component {

	/**
	 * Is category links should have title attribute.
	 *
	 * @var bool
	 */
	public $categories_allow_titles = true;

	/**
	 * Is breadcrumbs should be rendered on the home page?
	 *
	 * @var bool
	 */
	public $show_on_home = true;

	/**
	 * Is breadcrumbs should be rendered on the 404 page?
	 *
	 * @var bool
	 */
	public $show_on_404 = false;

	/**
	 * Is breadcrumbs should contain link to the home page?
	 *
	 * @var bool
	 */
	public $show_home_link = true;

	/**
	 * Text of the elements delimiter.
	 *
	 * @var string
	 */
	public $elements_delimiter = '';

	/**
	 * Flag. If 'woocommerce_breadcrumb' can be used to get breadcrumbs.
	 *
	 * @see get_html
	 * @var bool
	 */
	public $allow_user_woocommerce_breadcrumb = true;

	/**
	 * Mapper for diff page types formatting strings.
	 *
	 * @var assoc
	 */
	public $page_type_formats = array(
		'home' => 'Home',
		'category' => 'Category %s',
		'search' => 'Result search "%s"',
		'tag' => 'Tag "%s"',
		'author' => 'Author %s',
		'404' => 'Error 404',
		'format' => 'Format %s',
	);

	/**
	 * Returns html that represents breadcrumbs element.
	 *
	 * @return string
	 */
	public function get_html() {
		if ( $this->allow_user_woocommerce_breadcrumb && function_exists( 'woocommerce_breadcrumb' ) ) {
			return $this->get_woocommerce_breadcrumb();
		}

		$elements = array();

		global $post;
		$home_url = home_url( '/' );
		$parent_id = isset( $post->post_parent ) ? $post->post_parent : null;

		if ( is_front_page() ) { // is home page.
			if ( $this->show_on_home ) {
				$elements[] = $this->get_type_format( 'home' );
				// $elements[] = $this->render_link($home_url, $this->get_type_format('home'));
			};
		} else {
			if ( $this->show_home_link ) {
				$elements[] = $this->render_link( $home_url, $this->get_type_format( 'home' ), 'rel="v:url" property="v:title"' );
			}

			if ( is_home() ) { // is blog page.
				if ( $blog_page_id = get_option( 'page_for_posts' ) ) {
					$elements[] = get_the_title( $blog_page_id );
				}
			} elseif ( is_single() ) {
				if ( is_attachment() ) {
					if ( $parent_post = get_post( $parent_id ) ) {
						if ( $cat = get_the_category( $parent_post->ID ) ) {
							if ( $parent_categories = $this->get_parent_categories( $cat[0] ) ) {
								$elements = array_merge( $elements, $parent_categories );
							}
						}
					}
					$elements[] = $this->render_link( get_permalink( $parent_post ), $parent_post->post_title );
					$elements[] = get_the_title();
				} else {
					$post_type = get_post_type();
					if ( 'post' !== $post_type ) {
						if ( $archive_url = $this->get_archive_url_for_post_type( $post_type ) ) {
							$post_type = get_post_type_object( $post_type );
							$elements[] = $this->render_link(
								$archive_url,
								$post_type->labels->singular_name
							);
						}
					} else {
						if ( $cat = get_the_category() ) {
							if ( $parent_categories = $this->get_parent_categories( $cat[0] ) ) {
								$elements = array_merge( $elements, $parent_categories );
							}
						} elseif ( $blog_page_id = get_option( 'page_for_posts' ) ) {
							$elements[] = $this->render_link(
								get_page_link( $blog_page_id ),
								get_the_title( $blog_page_id )
							);
						}
					}
					$elements[] = get_the_title();
				}
			} elseif ( is_page() ) {
				if ( $parent_id > 0 ) {
					$frontpage_id = get_option( 'page_on_front' );
					if ( $parent_id !== $frontpage_id ) {
						$parent_elements = array();
						while ( $parent_id ) {
							$page = get_page( $parent_id );
							if ( $parent_id != $frontpage_id ) {
								$parent_elements[] = $this->render_link(
									get_permalink( $page->ID ),
									get_the_title( $page->ID )
								);
							}
							$parent_id = $page->post_parent;
						}
						if ( $parent_elements ) {
							$elements = array_merge( $elements, array_reverse( $parent_elements ) );
						}
					}
				}
				$elements[] = get_the_title();
			} elseif ( is_archive() ) {
				if ( is_category() ) {
					$own_category = get_category( get_query_var( 'cat' ), false );
					if ( $own_category->parent > 0 ) {
						if ( $parent_categories = $this->get_parent_categories( $own_category->parent ) ) {
							$elements = array_merge( $elements, $parent_categories );
						}
					}

					$elements[] = sprintf(
						$this->get_type_format( 'category' ),
						single_cat_title( '', false )
					);
				} elseif ( is_tag() ) {
					$elements[] = sprintf(
						$this->get_type_format( 'tag' ),
						single_tag_title( '', false )
					);
				} elseif ( is_author() ) {
					global $author;
					$userdata = get_userdata( $author );
					$elements[] = sprintf(
						$this->get_type_format( 'author' ),
						$userdata->display_name
					);
				} elseif ( is_date() ) {
					$date_parts = array();
					if ( is_day() ) {
						$date_parts['day'] = get_the_time( 'd' );

						$date_parts['month'] = $this->render_link(
							get_month_link( get_the_time( 'Y' ),get_the_time( 'm' ) ),
							get_the_time( 'F' )
						);
					} elseif ( is_month() ) {
						$date_parts['month'] = get_the_time( 'F' );
					}

					if ( $date_parts ) {
						$date_parts['year'] = $this->render_link(
							get_year_link( get_the_time( 'Y' ) ),
							get_the_time( 'Y' )
						);
					} else {
						$date_parts = array(
							'year' => get_the_time( 'Y' ),
						);
					}
					while ( $date_parts ) {
						$elements[] = array_pop( $date_parts );
					}
				} else {
					$format = get_post_format();
					if ( $format ) {
						// $elements[] = $this->render_link(get_post_format_link($format), sprintf($this->get_type_format('format'), $format));
						$elements[] = sprintf( $this->get_type_format( 'format' ), $format );
					} elseif ( $post_type = get_post_type() ) {
						if ( $post_type_obj = get_post_type_object( $post_type ) ) {
							$elements[] = $post_type_obj->labels->name;
						}
					}
				} // End if().
			} elseif ( is_search() ) {
				$elements[] = sprintf(
					$this->get_type_format( 'search' ),
					get_search_query()
				);
			} elseif ( is_404() ) {
				if ( $this->show_on_404 ) {
					$elements[] = $this->get_type_format( '404' );
				} else {
					$elements = array();
				}
			} // End if().
		} // End if().

		return $this->render_elements( $elements );
	}

	public function get_woocommerce_breadcrumb() {
		ob_start();
		woocommerce_breadcrumb(array(
			'delimiter' => $this->elements_delimiter,
			'wrap_before' => '<ul>',
			'wrap_after' => '</ul>',
			'before' => '<li>',
			'after' => '</li>',
			'home' => $this->get_type_format( 'home' ),
		));
		// to prevent breadcrumbs second time breadcrumbs rendering.
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
		return ob_get_clean();
	}

	/**
	 * Returns archive url for specefied post type.
	 *
	 * @param  string $post_type the post type.
	 * @return string
	 */
	protected function get_archive_url_for_post_type( $post_type ) {
		return get_post_type_archive_link( $post_type );
	}

	/**
	 * Returns array of the link that represents parent categories.
	 *
	 * @param  string $category_id the category id.
	 * @return array
	 */
	public function get_parent_categories( $category_id ) {
		$result = array();

		if ( $categories_text = get_category_parents( $category_id, true, '=DEL=' ) ) {
			$categories = explode( '=DEL=', $categories_text );
			foreach ( $categories as $cat_link ) {
				if ( ! $cat_link ) {
					continue;
				}
				if ( ! $this->categories_allow_titles ) {
					$result[] = preg_replace( '/ title="(.*?)"/', '', $cat_link );
				} else {
					$result[] = $cat_link;
				}
			}
		}

		return $result;
	}

	/**
	 * Returns formatting string for specefied type of string.
	 *
	 * @param  string $name type of the string.
	 * @return string
	 */
	protected function get_type_format( $name ) {
		return isset( $this->page_type_formats[ $name ] ) ? $this->page_type_formats[ $name ] : '%s';
	}

	/**
	 * Returns list html text.
	 *
	 * @param string|array $elements the elements.
	 * @return string
	 */
	public function render_elements( $elements ) {
		if ( $elements ) {
			$li_html = '';
			$last_element = array_pop( $elements );
			if ( $elements ) {
				$li_html = '<li>' . join( '</li>' . $this->elements_delimiter . '<li>', $elements ) . '</li>';
				$li_html .= $this->elements_delimiter;
			}
			$li_html .= '<li class="active">' . $last_element . '</li>';
			return '<ul>' . $li_html . '</ul>';
		}
		return '';
	}

	/**
	 * Returns link html text.
	 *
	 * @param  string       $href       url address.
	 * @param  string       $title      text of the link.
	 * @param  string|array $attributes link attributes.
	 * @return string
	 */
	protected function render_link( $href, $title, $attributes = null ) {
		$attributes_text = '';
		if ( $attributes ) {
			if ( is_array( $attributes ) ) {
				$parts = array();
				foreach ( $attributes as $key => $value ) {
					$parts[] = $key . '="' . $value . '"';
				}
				$attributes_text = ' ' . join( ' ', $parts );
			} else {
				$attributes_text = ' ' . $attributes;
			}
		}

		return '<a href="' . $href . '"' . $attributes_text . '>' . $title . '</a>';
	}

}
