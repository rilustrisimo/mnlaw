<?php
/**
 * Registers custom post types and taxonomies.
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
defined('ABSPATH') || die();

class QEDDTP_Data_Types_Registrator {

    /**
     * Internal flag that determines if data types has been inited.
     *
     * @var boolean
     */
    private $inited = false;

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'init' ) );
    }

    /**
     * Callback for init event.
     *
     * @return void
     */
    public function init() {
        if ( $this->inited ) {
            return;
        }
        $this->inited = true;

        $this->register_media_categories();
        // TODO: Add check for FAQ functionality.
        //$this->register_faq();
        $this->register_sd_header_section();

        if ( $this->get_option( 'is_testimonial_enabled', 'option' ) ) {
            $this->register_testimonials();
            add_filter( 'enter_title_here', array( $this, 'register_testimonial_default_title' ) );
            add_filter( 'post_updated_messages', array( $this, 'register_testimonial_updated_messages' ) );
        }
    }

    /**
     * Returns theme option value.
     *
     * @param  string $name    option name.
     * @param  mixed  $default default value.
     * @return mixed
     */
    protected function get_option( $name, $id = null, $default = null ) {
        $option = ( $id ) ? $id:'';
        if ( get_field( $name, $option ) ) {
            return get_field( $name, $option );
        } else {
            return $default;
        }
    }

    /**
     * Registers 'madia_category' taxonomy for attachments.
     *
     * @return void
     */
    protected function register_media_categories() {
        register_taxonomy( 'media_category', 'attachment', array(
            'labels' => array(
                'name' => esc_html__( 'Media Categories', 'swishdesign' ),
                'singular_name' => esc_html__( 'Media Category', 'swishdesign' ),
            ),
            'hierarchical' => true,
            'show_admin_column' => true,
            'query_var' => false,
        ) );
    }

    /**
     * Registers 'faq' custom post type and 'faq_category' taxonomy.
     *
     * @return void
     */
    protected function register_faq() {
        register_post_type('faq', array(
            'label' => esc_html__( 'FAQs', 'swishdesign' ),
            'labels' => array(
                'add_new' => esc_html__( 'Add New Question', 'swishdesign' ),
                'edit_item' => esc_html__( 'Edit Question', 'swishdesign' ),
            ),
            'exclude_from_search' => true,
            'publicly_queryable' => true,
            'public' => true,
            'show_ui' => true,
            'show_in_nav_menus' => false,
            'has_archive' => false,
            'menu_icon' => PARENT_URL . '/data-types/assets/images/ico-faq.png',
            'menu_position' => 9,
            'rewrite' => array(
                'slug' => 'faq',
                'with_front' => false,
            ),
            'supports' => array(
                'title',
                'editor',
                'page-attributes',
            ),
        ));

        register_taxonomy( 'faq_category', 'faq', array(
            'hierarchical' => true,
            'label' => esc_html__( 'FAQ Categories', 'swishdesign' ),
            'singular_name' => esc_html__( 'FAQ Category', 'swishdesign' ),
            'rewrite' => true,
            'query_var' => true,
            'show_in_nav_menus' => true,
        ));
    }

    protected function register_sd_header_section() {
        register_post_type('sd_header_section', array(
            'labels' => array(
                'name' => __('Header Sections', 'swishdesign'),
                'add_new' => esc_html__( 'Add New Header Section', 'swishdesign' ),
                'edit_item' => esc_html__( 'Edit Header Section', 'swishdesign' ),
                'singular_name' => __('Header Section', 'swishdesign'),
                'all_items' => __('All Header Sections', 'swishdesign'),
                'add_new_item' => __('Add New Header Section', 'swishdesign'),
                'edit' => __('Edit', 'swish'),
                'new_item' => __('New Header Section', 'swishdesign'),
                'view' => __('View Header Section', 'swishdesign'),
                'view_item' => __('View Header Section', 'swishdesign'),
                'search_items' => __('Search Header Sections', 'swishdesign'),
                'not_found' => __('No Header Sections found', 'swishdesign'),
                'not_found_in_trash' => __('No Header Sections found in trash', 'swishdesign'),
                'parent' => __('Parent Header Section', 'swishdesign')
            ),
            'public' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'has_archive' => false,
            'query_var' => false,
            'rewrite' => false,
            'show_ui' => true,
            'show_in_nav_menus' => false,
            'menu_icon' => 'dashicons-slides',
            'menu_position' => 10,
            'supports' => array(
                'title',
            ),
        ));
    }

    protected function register_testimonials() {
        register_post_type('qed_testimonials', array(
            'labels' => array(
                'name' => __('Testimonials', 'swishdesign'),
                'add_new' => esc_html__( 'Add New Testimonial', 'swishdesign' ),
                'edit_item' => esc_html__( 'Edit Testimonial', 'swishdesign' ),
                'singular_name' => __('Testimonial', 'swishdesign'),
                'all_items' => __('All Testimonials', 'swishdesign'),
                'add_new_item' => __('Add New Testimonial', 'swishdesign'),
                'edit' => __('Edit', 'swish'),
                'new_item' => __('New Testimonial', 'swishdesign'),
                'view' => __('View Testimonial', 'swishdesign'),
                'view_item' => __('View Testimonial', 'swishdesign'),
                'search_items' => __('Search Testimonials', 'swishdesign'),
                'not_found' => __('No Testimonials found', 'swishdesign'),
                'not_found_in_trash' => __('No Testimonials found in trash', 'swishdesign'),
                'parent' => __('Parent Testimonial', 'swishdesign')
            ),
            'public' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'has_archive' => false,
            'query_var' => false,
            'rewrite' => false,
            'show_ui' => true,
            'show_in_nav_menus' => false,
            'menu_icon' => 'dashicons-format-status',
            'menu_position' => 30,
            'supports' => array(
                'title',
                'editor',
                'thumbnail',
            ),
        ));
    }

    public function register_testimonial_default_title( $title ){
        $screen = get_current_screen();
        if ( 'qed_testimonials' === $screen->post_type ) {
            $title = 'Enter name here';
        }
        return $title;
    }

    public function register_testimonial_updated_messages( $messages ) {
        global $post, $post_ID;

        $messages['qed_testimonials'] = array(
            0 => '', // Unused. Messages start at index 1.
            1 => sprintf( __('Testimonial updated. <a href="%s">View Testimonial</a>', 'swish'), esc_url( get_permalink($post_ID) ) ),
            2 => __('Custom field updated.', 'swish'),
            3 => __('Custom field deleted.', 'swish'),
            4 => __('Testimonial updated.', 'swish'),
            /* translators: %s: date and time of the revision */
            5 => isset($_GET['revision']) ? sprintf( __('Testimonial restored to revision from %s', 'swish'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
            6 => sprintf( __('Testimonial published. <a href="%s">View Testimonial</a>', 'swish'), esc_url( get_permalink($post_ID) ) ),
            7 => __('Testimonial saved.', 'swish'),
            8 => sprintf( __('Testimonial submitted. <a target="_blank" href="%s">Preview Testimonial</a>', 'swish'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
            9 => sprintf( __('Testimonial scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Testimonial</a>', 'swish'),
                // translators: Publish box date format, see http://php.net/date
                date_i18n( __('M j, Y @ G:i', 'swish'), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
            10 => sprintf( __('Testimonial draft updated. <a target="_blank" href="%s">Preview Testimonial</a>', 'swish'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
        );

        return $messages;
    }

}