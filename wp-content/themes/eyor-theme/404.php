<?php
/**
 * Template page for 404 page.
 *
 * @author    eyorsogood.com, Rouie Ilustrisimo
 * @package Eyorsogood_Design
 * @version   1.0.0
 */

qed_di( 'app' )->add_body_class( 'page-404' );

get_header();

get_template_part( 'templates/content', 'none' );

get_footer();
