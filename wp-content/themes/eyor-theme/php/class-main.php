<?php
/**
 * * Main Class. Classes and functions for Labyog.
 *
 * @author    eyorsogood.com, Rouie Ilustrisimo
 * @package   Eyorsogood
 * @version   1.0.0
 */

/**
 * No direct access to this file.
 *
 * @since 1.0.0
 */
defined( 'ABSPATH' ) || die();

/**
 * Class Labyog
 */
class Theme {
    protected $user;
    protected $post_types = array(
        /**
         * added classes here
         */
        /*
        array(
            'post_type'		=> 'supplies',
            'singular_name' => 'Supply',
            'plural_name'	=> 'Supplies',
            'menu_icon' 	=> 'dashicons-portfolio',
            'supports'		=> array( 'title', 'thumbnail'),
            'title_acf'     => 'field_63e5c719d10ce',
            'header'        => array(
                'supply_name' => 'Equipment / Supply Name',
                'department' => 'Department',
                'section' => 'Section',
                'type' => 'Type',
                'purchased_date' => 'Purchased Date',
                'date_added' => 'Date Added',
                'price_per_unit' => 'Price Per Unit'
            )
        )
        */
    );
    

    function __autoload() {
        $classes = array();

        foreach($classes as $value){
            require_once PARENT_DIR . '/php/class-'. $value .'.php';
        }
    }

	/**
	 * Constructor runs when this class instantiates.
	 *
	 * @param array $config Data via config file.
	 */
	public function __construct( array $config = array() ) {
        $this->__autoload();
        $this->initActions();
        $this->initFilters();
        $this->user = wp_get_current_user();
    }

    protected function initActions() {
        /**
         * 
         * function should be public when adding to an action hook.
         */

        add_action( 'init', array($this, 'createPostTypes')); 
        
    }

    protected function initFilters() {
        /**
         * Place filters here
         */

    }

    public function createQuery($posttype, $meta_query = array(), $numberposts = -1, $orderby = 'date', $order = 'DESC') {
        $args = array(
            'orderby'			=> $orderby,
            'order'				=> $order,
            'numberposts'	=> $numberposts,
            'post_type'		=> $posttype,
            'meta_query'    => array($meta_query),
            'posts_per_page' => $numberposts
        );

        $the_query = new WP_Query( $args );

        return $the_query;
    }

    public function createPostQuery($postType, $postPerPage, $pagination = false, $meta_query = array()) {
        $rows = array();
        $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

        $args = array(
            'post_type' => $postType,
            'post_status' => array('publish'),
            'posts_per_page' => $postPerPage,
            'paged' => $paged,
            'orderby'			=> 'date',
            'order'				=> 'DESC',
            'meta_query'        => $meta_query
        );

        $pagi = '';
    
        $the_query = new WP_Query( $args );
        // The Loop
        if ( $the_query->have_posts() ) {
            while ( $the_query->have_posts() ) {
                $the_query->the_post();
                $fields = get_fields(get_the_ID());
    
                $rows[get_the_ID()] = $fields;
            } // end while
        } // endif
    
        if($pagination){
            $pagi = '<div class="pagination">'.paginate_links( array(
                'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
                'total'        => $the_query->max_num_pages,
                'current'      => max( 1, get_query_var( 'paged' ) ),
                'format'       => '?paged=%#%',
                'show_all'     => false,
                'type'         => 'plain',
                'end_size'     => 2,
                'mid_size'     => 1,
                'prev_next'    => true,
                'prev_text'    => sprintf( '<i></i> %1$s', __( '<i class="fas fa-angle-double-left"></i>', 'text-domain' ) ),
                'next_text'    => sprintf( '%1$s <i></i>', __( '<i class="fas fa-angle-double-right"></i>', 'text-domain' ) ),
                'add_args'     => false,
                'add_fragment' => '',
            ) ).'</div>';
        }
    
        // Reset Post Data
        wp_reset_postdata();
    
        return array($rows, $pagi);
    }

    public function initAcfScripts(){
        return acf_form_head();
    }

    public function createAcfForm($fieldGroupId, $postType, $button = 'Submit', $redirect = null){
        return 	acf_form(array(
            'post_id'		=> 'new_post',
            'post_title'	=> false,
            'post_content'	=> false,
            'field_groups'	=> array($fieldGroupId),
            //'submit_value'	=> $button,
            'html_submit_button' => '<a href="#" class="acf-button button button-primary button-large">'.$button.'</a>',
            'new_post'		=> array(
                'post_type'		=> $postType,
                'post_status'	=> 'publish'
            ),
            'form' => true,
            'return' => (is_null($redirect))?get_permalink(get_the_ID()):home_url('/'.$redirect),
            'updated_message' => __("Account Created", 'acf'),
        ));
    }

    public function updateAcfForm($postid, $fieldGroupId, $button = 'Update', $redirect = null) {
        return acf_form(array(
            'post_id'		=> $postid,
            'post_title'	=> false,
            'post_content'	=> false,
            'field_groups'	=> array($fieldGroupId),
            'submit_value'	=> $button,
            //'html_submit_button' => '<a href="#" class="acf-button button button-primary button-large">'.$button.'</a>',
            'form' => true,
            'return' => (is_null($redirect))?get_permalink(get_the_ID()):home_url('/'.$redirect)
        ));
    }

    public function createPostTypes() {
        /*
        * Added Theme Post Types
        *
        */
        // Uncomment the $a_post_types declaration to register your custom post type
        
        $a_post_types = $this->post_types;

        if( !empty( $a_post_types ) ) {
            foreach( $a_post_types as $a_post_type ) {
                $a_defaults = array(
                    'supports'		=> $a_post_type['supports'],
                    'has_archive'	=> TRUE
                );
    
                $a_post_type = wp_parse_args( $a_post_type, $a_defaults );
    
                if( !empty( $a_post_type['post_type'] ) ) {
    
                    $a_labels = array(
                        'name'				=> $a_post_type['plural_name'],
                        'singular_name'		=> $a_post_type['singular_name'],
                        'menu_name'			=> $a_post_type['plural_name'],
                        'name_admin_bar'		=> $a_post_type['singular_name'],
                        'add_new_item'			=> 'Add New '.$a_post_type['singular_name'],
                        'new_item'			=> 'New '.$a_post_type['singular_name'],
                        'edit_item'			=> 'Edit '.$a_post_type['singular_name'],
                        'view_item'			=> 'View '.$a_post_type['singular_name'],
                        'all_items'			=> 'All '.$a_post_type['plural_name'],
                        'search_items'			=> 'Search '.$a_post_type['plural_name'],
                        'parent_item_colon'		=> 'Parent '.$a_post_type['plural_name'],
                        'not_found'			=> 'No '.$a_post_type['singular_name'].' found',
                        'not_found_in_trash'	=> 'No '.$a_post_type['singular_name'].' found in Trash'
                    );
    
                    $a_args = array(
                        'labels'				=> $a_labels,
                        'show_in_menu'			=> true,
                        'show_ui'				=> true,
                        'rewrite'				=> array( 'slug' => $a_post_type['post_type'] ),
                        'capability_type'		=> 'post',
                        'has_archive'			=> $a_post_type['has_archive'],
                        'supports'				=> $a_post_type['supports'],
                        'publicly_queryable' 	=> true,
                        'public' 				=> true,
                        'query_var' 			=> true,
                        'menu_icon'				=> $a_post_type['menu_icon']
                    );
    
                    register_post_type( $a_post_type['post_type'], $a_args );
                }
            }
        }
    }

    public function my_save_post( $post_id ) {	

        if(isset($_POST['_acf_post_id'])) {
            /**
             * get post details
             */
            $post_values = get_post($post_id);


            /**
             * bail out if not a custom type and admin
             */
            $types = array();

            foreach($this->post_types as $pt):
                $types[] = $pt['post_type']; 
            endforeach;
            
            if(!(in_array($post_values->post_type, $types))){
                return;
            }

            if($_POST['_acf_post_id'] == "new_post"){
                /**
                 * applicant set values
                 */

                foreach($this->post_types as $pt):
                    if($post_values->post_type == $pt['post_type']){
                        /**
                         * update post
                         */

                        if(is_array($pt['title_acf']) && is_object(get_field($pt['title_acf'][0], $_POST['acf'][$pt['title_acf'][1]]))):
                            $fobj = get_field($pt['title_acf'][0], $_POST['acf'][$pt['title_acf'][1]]);
                            $title = (is_array($pt['title_acf']))?$fobj->post_title:$_POST['acf'][$pt['title_acf']];
                        else:
                            $title = (is_array($pt['title_acf']))?get_field($pt['title_acf'][0], $_POST['acf'][$pt['title_acf'][1]]):$_POST['acf'][$pt['title_acf']];
                        endif;
    
                        $my_post = array(
                            'ID'           => $post_id,
                            'post_title'   => $title
                        );
    
                        wp_update_post( $my_post );
                    }
                endforeach;

                /**
                 *  Clear POST data
                 */
                unset($_POST);

                /**
                 * notifications
                 */
         
            }
            else if($_POST['_acf_post_id'] == $post_id) {

                foreach($this->post_types as $pt):
                    if($post_values->post_type == $pt['post_type']){
                        /**
                         * update post
                         */
                        if(is_array($pt['title_acf']) && is_object(get_field($pt['title_acf'][0], $_POST['acf'][$pt['title_acf'][1]]))):
                            $fobj = get_field($pt['title_acf'][0], $_POST['acf'][$pt['title_acf'][1]]);
                            $title = (is_array($pt['title_acf']))?$fobj->post_title:$_POST['acf'][$pt['title_acf']];
                        else:
                            $title = (is_array($pt['title_acf']))?get_field($pt['title_acf'][0], $_POST['acf'][$pt['title_acf'][1]]):$_POST['acf'][$pt['title_acf']];
                        endif;

                        
    
                        $my_post = array(
                            'ID'           => $post_id,
                            'post_title'   => $title
                        );
    
                        wp_update_post( $my_post );
                    }
                endforeach;

                /**
                 *  Clear POST data
                 */
                unset($_POST);

                /**
                 * notifications
                 */

            }
        }
    }

    public function initSession() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        return true;
    }
}
