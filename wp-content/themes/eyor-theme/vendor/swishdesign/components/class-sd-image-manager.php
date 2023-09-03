<?php
/**
 * Class allows define theme specific image sizes that will be generated on the 1-st request.
 * Sizes added via add_image_size function - generated all together on the upload image event,
 * but usually image used in some specific context with some specific size. So to prevent this overhead
 * this class has been designed.
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
 * Class SD_Image_Manager
 */
class SD_Image_Manager extends SD_Component {

	/**
	 * List of the theme custom sizes.
	 * Size code used as a key, each element contains size details.
	 *
	 * @see addImageSize
	 * @var array
	 */
	protected $sizes = array();

	/**
	 * Sets the config options.
	 *
	 * @param array $config configuration options.
	 */
	public function set_config( array $config ) {
		foreach ( $config as $option => $value ) {
			switch ( $option ) {
				case 'sizes':
					foreach ( $value as $_size_name => $_size_details ) {
						$this->add_custom_image_size(
							$_size_name,
							isset( $_size_details['width'] ) ? $_size_details['width'] : 0,
							isset( $_size_details['height'] ) ? $_size_details['height'] : 0,
							isset( $_size_details['crop'] ) ? $_size_details['crop'] : false
						);
					}
					break;

				default:
					$this->$option = $value;
					break;
			}
		}
	}

	/**
	 * Init method.
	 *
	 * @return bool
	 */
	public function init() {
		if ( parent::init() ) {
			add_action( 'delete_attachment', array( $this, 'remove_custom_image_sizes' ) );
			add_filter( 'image_downsize', array( $this, 'filter_image_downsize' ), 10, 3 );
			return true;
		}
		return false;
	}

	/**
	 * Get image size details.
	 *
	 * @param string $size the image size.
	 * @return null|array
	 */
	public function get_image_size_details( $size ) {
		if ( is_array( $size ) ) {
			if ( ! isset( $size['width'] ) && isset( $size[0] ) ) {
				$size['width'] = $size[0];
			}

			if ( ! isset( $size['height'] ) && isset( $size[1] ) ) {
				$size['height'] = $size[1];
			}

			return $size;
		}

		static $default_size = array(
			'thumbnail' => '',
			'medium' => '',
			'large' => '',
		);

		if ( isset( $default_size[ $size ] ) ) {
			if ( '' === $default_size[ $size ] ) {
				$width = get_option( $size . '_size_w' );
				$height = get_option( $size . '_size_h' );
				$crop = get_option( $size . '_crop' );

				if ( $width || $height ) {
					$default_size[ $size ] = array(
						'width' => $width,
						'height' => $height,
						'crop' => $crop,
					);
				} else {
					$default_size[ $size ] = null;
				}
			}
			return $default_size[ $size ];
		}

		// checking is size defined in general sizes list.
		global $_wp_additional_image_sizes;
		if ( isset( $_wp_additional_image_sizes[ $size ] ) ) {
			return $_wp_additional_image_sizes[ $size ];
		}

		return $this->get_custom_image_sizes( $size );
	}

	/**
	 * Adds custom image size.
	 *
	 * @param string  $size the image size.
	 * @param int     $width the image width.
	 * @param int     $height the image height.
	 * @param boolean $crop flag to determine if image should be cropped.
	 * @return Theme_Image_Manager
	 */
	public function add_custom_image_size( $size, $width = 0, $height = 0, $crop = false ) {
		if ( $size && ! has_image_size( $size ) ) {
			$this->sizes[ $size ] = array(
				'width' => absint( $width ),
				'height' => absint( $height ),
				'crop' => $crop,
			);
		}

		return $this;
	}

	/**
	 * Returns custom image size if size code passed or all defined sizes if code is missed out.
	 *
	 * @param string $size optional param.
	 * @return array|null
	 */
	public function get_custom_image_sizes( $size = '' ) {
		if ( ! $size ) {
			return $this->sizes;
		} else {
			if ( is_array( $size ) ) {
				return $size;
			}

			return isset( $this->sizes[ $size ] ) ? $this->sizes[ $size ] : null;
		}
	}

	/**
	 * Returns true if size is a custom image size.
	 *
	 * @param string $size the image size.
	 * @return bool
	 */
	public function is_custom_image_size( $size ) {
		return is_string( $size ) && isset( $this->sizes[ $size ] );
	}

	/**
	 * Removes generated custom image sizes.
	 *
	 * @param int $post_id the post id.
	 * @return void
	 */
	public function remove_custom_image_sizes( $post_id ) {
		$file_dir = get_attached_file( $post_id );
		if ( empty( $file_dir ) ) {
			return;
		}

		$image_sizes = $this->get_custom_image_sizes();

		if ( ! $image_sizes ) {
			return;
		}

		$file_dir_info = pathinfo( $file_dir );
		$file_base_dir = isset( $file_dir_info['dirname'] ) ? $file_dir_info['dirname'] : '';
		$file_name = isset( $file_dir_info['filename'] ) ? $file_dir_info['filename'] : '';
		$file_ext = isset( $file_dir_info['extension'] ) ? $file_dir_info['extension'] : '';

		foreach ( $image_sizes as $size ) {
			$crop = $size['crop'];
			$width = $size['width'];
			$height = $size['height'];

			if ( false == $crop ) {
				// get image size after cropping.
				list( $orig_w, $orig_h ) = getimagesize( $file_dir );
				$dims = image_resize_dimensions( $orig_w, $orig_h, $width, $height, $crop );
				$width = $dims[4];
				$height = $dims[5];
			}

			$file = $file_base_dir . '/' . $file_name . '-' . $width . 'x' . $height . '.' . $file_ext;

			if ( ! file_exists( $file ) ) {
				continue;
			}

			if ( unlink( $file ) ) {
				// files remove.
			} else {
				// files not remove.
			}
		}
	}

	/**
	 * Returns a placeholder image.
	 *
	 * @param string     $width placeholder width.
	 * @param string     $height placeholder height.
	 * @param string     $text placeholder text.
	 * @param bool|false $as_image_element flag to determine if this should be an image element.
	 * @param array      $attributes the attributes for the image element.
	 *
	 * @return string
	 */
	public function getPlaceholdImage( $width, $height, $text = '', $as_image_element = false, array $attributes = array() ) {
		if ( empty( $width ) && empty( $height ) ) {
			return '';
		}

		$url = 'http://placehold.it/' . $width . 'x' . $height . ( $text ? '&text=' . rawurlencode( $text ) : '');

		if ( $as_image_element ) {
			$attributes_text = '';
			if ( $attributes ) {
				foreach ( $attributes as $name => $attribute_value ) {
					$attributes_text .= ' ' . $name . '="' . esc_attr( $attribute_value ) . '"';
				}
			}
			return '<img src="' . $url . '" alt="image of ' . ($width . 'x' . $height) . '"' . $attributes_text . '>';
		} else {
			return $url;
		}
	}

	/**
	 * Get the attachment id from the url.
	 *
	 * @param string    $url the url.
	 * @param bool|true $check_host flag to determine if we are going to check if the url is from our host.
	 * @return null
	 */
	public function get_attachment_id_by_url( $url, $check_host = true ) {
		if ( $check_host ) {
			// checking that image belongs to the our host.
			$current_host = str_ireplace( 'www.', '', wp_parse_url( home_url(), PHP_URL_HOST ) );
			$file_host = str_ireplace( 'www.', '', wp_parse_url( $url, PHP_URL_HOST ) );
			if ( $current_host != $file_host ) {
				return null;
			}
		}

		// split the $url into two parts with the wp-content directory as the separator.
		$parsed_url  = explode( wp_parse_url( WP_CONTENT_URL, PHP_URL_PATH ), $url );

		if ( empty( $parsed_url[1] ) ) {
			return null;
		}

		// searching in the DB for any attachment GUID with a partial path match.
		global $wpdb;
		$attachment = $wpdb->get_col( $wpdb->prepare(
			"SELECT ID FROM `{$wpdb->posts}` " .
			'WHERE `post_type`=%s AND `guid` LIKE %s;',
			'attachment',
			'%' . $wpdb->esc_like( $parsed_url[1] )
		) );

		// Returns null if no attachment is found.
		return isset( $attachment[0] ) ? $attachment[0] : null;
	}

	/**
	 * [Description here] TODO: Create description
	 *
	 * @param bool   $false flag for the filter.
	 * @param int    $id attachment id.
	 * @param string $size the image size.
	 * @return array|null
	 */
	public function filter_image_downsize( $false, $id, $size ) {
		if ( ! $this->is_custom_image_size( $size ) ) {
			return null;
		}

		$cur_meta = wp_get_attachment_metadata( $id );

		$img_url = wp_get_attachment_url( $id );
		if ( isset( $cur_meta['sizes'][ $size ] ) ) {
			$cur_size = $cur_meta['sizes'][ $size ];
			return array(
				str_replace( wp_basename( $img_url ), $cur_size['file'], $img_url ),
				$cur_size['width'],
				$cur_size['height'],
				true,
			);
		}

		// via editor.
		$editor = wp_get_image_editor( get_attached_file( $id ) );
		if ( is_wp_error( $editor ) ) {
			return null;
		}

		$new_sizes = array();
		$new_sizes[ $size ] = $this->get_custom_image_sizes( $size );

		$new_sizes = $editor->multi_resize( $new_sizes );
		if ( $new_sizes ) {
			$created_size = $new_sizes[ $size ];
			$cur_meta['sizes'][ $size ] = $created_size;
			wp_update_attachment_metadata( $id, $cur_meta );

			return array(
				str_replace( wp_basename( $img_url ), $created_size['file'], $img_url ),
				$created_size['width'],
				$created_size['height'],
				true,
			);
		}

		return null;
	}

}
