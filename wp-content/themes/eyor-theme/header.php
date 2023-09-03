<?php
/**
 * Header template part.
 *
 * @author    eyorsogood.com, Rouie Ilustrisimo
 * @package Eyorsogood_Design
 * @version   1.0.0
 */

get_template_part( 'templates/header/header', 'clean' );

$is_sticky_header = qed_get_option( 'sticky_header', 'option' );

if ( $is_sticky_header ) {
//	SD_Js_Client_Script::add_script( 'sticky-header', 'Theme.initStickyHeader();' );
	echo '<div class="header-wrap header-wrap--sticky-header">';
}
?>
<header class="header" role="banner">
	<div class="top_layer">
		<div class="header__content-wrap">
			<div class="container-fluid">
				<div class="row">
					<div class="col-md-3">
						<?php get_template_part( 'templates/header/logo' ); ?>
					</div><!-- .header__content -->
					<div class="col-md-6">
						<h1>Your Personal Injury Specialist</h1>
					</div><!-- .header__content -->
					<div class="col-md-3">
						
					</div><!-- .header__content -->
					<div class="clearfix"></div>
				</div>
			</div><!-- .container -->
		</div><!-- .header__content-wrap -->
	</div>
	<div class="clearfix"></div>
	<div class="middle_layer">
		<div class="header__content-wrap">
			<div class="container-fluid">
				<div class="row">
					<div class="col-md-12">
						<div class="content-popup-wrap">
							<span class="contact-popup-intro">Contact Us Today For a Free Initial Consultation</span><a id="contact-popup-btn" class="button button--default fancybox-inline" href="#contact-lightbox">Call Us Now</a>
						</div>
					</div>
				</div>
			</div>
		</div><!-- .header__content-wrap -->
	</div>
	<div class="clearfix"></div>
	<div class="bottom_layer">
		<div class="container">
			<div class="header__content-wrap">
				<div class="row">
					<div class="col-md-12 header__content">
						<?php if ( has_nav_menu( 'header-menu' ) ) : ?>
							<nav class="main-nav-header" role="navigation">
								<?php wp_nav_menu(array(
									'theme_location' => 'header-menu',
									'container' => 'ul',
									'menu_class' => 'main-nav',
									'menu_id' => 'navigation',
									'depth' => 3,
								)); ?>
							</nav>
						<?php endif; ?>
						<div class="clearfix"></div>
					</div><!-- .header__content -->
				</div>
			</div><!-- .header__content-wrap -->
		</div><!-- .container -->
	</div>
	<div class="clearfix"></div>
</header>
<?php if ( $is_sticky_header ) { echo '</div>'; }
SD_Js_Client_Script::add_script( 'initResizeHandler', 'Theme.initResizeHandler();' );
//SD_Js_Client_Script::add_script( 'initResizeHandler', 'Theme.initResizeHandler(' . wp_json_encode( $js_config ) . ');' );
get_template_part( 'templates/header/header', 'section' );
do_action('eyor_before_main_content');
