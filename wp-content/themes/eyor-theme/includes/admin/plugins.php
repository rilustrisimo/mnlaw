<?php
/**
 * Theme dependency plugins integration.
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

// Including the TGM_Plugin_Activation class.
require PARENT_DIR . '/vendor/tgm-plugin-activation/class-tgm-plugin-activation.php';

/**
 * Register the required plugins for this theme.
 *
 * In this example, we register two plugins - one included with the TGMPA library
 * and one from the .org repo.
 *
 * The variable passed to tgmpa_register_plugins() should be an array of plugin
 * arrays.
 *
 * This function is hooked into tgmpa_init, which is fired within the
 * TGM_Plugin_Activation class constructor.
 */
function qed_register_required_plugins()
{
	$base_path = get_template_directory() . '/vendor/plugins/';

	/**
	 * Array of plugin arrays. Required keys are name and slug.
	 * If the source is NOT from the .org repo, then source is also required.
	 */
	$plugins = array(
		/*array(
			'name'      => 'WooCommerce',
			'slug'      => 'woocommerce',
			'required'  => true,
		),*/
		array(
			'name'      => 'Contact Form 7',
			'slug'      => 'contact-form-7',
			'required'  => true,
		),
		array(
			'name'      => 'Flamingo',
			'slug'      => 'flamingo',
			'required'  => true,
		),
		array(
			'name'      => 'Google Analytics Dashboard for WP ',
			'slug'      => 'google-analytics-dashboard-for-wp',
			'required'  => true,
		),
		array(
			'name'      => 'Mailchimp for WP',
			'slug'      => 'mailchimp-for-wp',
			'required'  => true,
		),
		/*array(
				'name'      => 'Slider Revolution',
				'slug'      => 'revslider',
				'source'    => $base_path . 'revslider.zip',
				'version'   => '5.3.1.5',
				'required'  => false,
		),*/
		array(
			'name'      => 'TinyMCE Advanced',
			'slug'      => 'tinymce-advanced',
			'required'  => true,
		),
		array(
			'name'      => 'WordPress Importer',
			'slug'      => 'wordpress-importer',
			'required'  => false,
		),
		array(
			'name'      => 'Widget Importer & Exporter',
			'slug'      => 'widget-importer-exporter',
			'required'  => false,
		),
		array(
			'name'               => 's2Member Framework', // The plugin name.
			'slug'               => 's2member', // The plugin slug (typically the folder name).
			'source'             => $base_path . 's2member.zip', // The plugin source.
			'required'           => false, // If false, the plugin is only 'recommended' instead of required.
			'version'            => '161129', // E.g. 1.0.0. If set, the active plugin must be this version or higher. If the plugin version is higher than the plugin version installed, the user will be notified to update the plugin.
			'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
			'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
			'external_url'       => '', // If set, overrides default API URL and points to an external URL.
			'is_callable'        => '', // If set, this callable will be be checked for availability to determine if a plugin is active.
		),
		/*array(
			'name'      => 'WordPress SEO by Yoast',
			'slug'      => 'wordpress-seo',
			'required'  => false,
		),*/
	);

	tgmpa( $plugins, array(
		'domain'            => 'swishdesign',                       // Text domain - likely want to be the same as your theme.
		'default_path'      => '',                          // Default absolute path to pre-packaged plugins
		'menu'              => 'install-required-plugins',  // Menu slug
		'has_notices'       => true,                        // Show admin notices or not
		'is_automatic'      => true,                        // Automatically activate plugins after installation or not
	) );
}

add_action( 'tgmpa_register', 'qed_register_required_plugins' );
