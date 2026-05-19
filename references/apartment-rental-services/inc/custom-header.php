<?php
/**
 * @package Apartment Rental Services
 * Setup the WordPress core custom header feature.
 *
 * @uses apartment_rental_services_header_style()
 */
function apartment_rental_services_custom_header_setup() {
	add_theme_support( 'custom-header', apply_filters( 'apartment_rental_services_custom_header_args', array(
		'default-text-color'     => 'fff',
		'width'                  => 2500,
		'height'                 => 400,
		'wp-head-callback'       => 'apartment_rental_services_header_style',
	) ) );
}
add_action( 'after_setup_theme', 'apartment_rental_services_custom_header_setup' );

if ( ! function_exists( 'apartment_rental_services_header_style' ) ) :
/**
 * Styles the header image and text displayed on the blog
 *
 * @see apartment_rental_services_custom_header_setup().
 */
function apartment_rental_services_header_style() {
	$apartment_rental_services_header_text_color = get_header_textcolor();

	?>
	<style type="text/css">
	<?php
		//Check if user has defined any header image.
		if ( get_header_image() || get_header_textcolor() ) :
	?>
		.page-template-template-home-page .header, .mainhead {
			background: url(<?php echo esc_url( get_header_image() ); ?>) no-repeat !important;
			background-position: center top;
			background-size: cover !important;
		}

	<?php endif; ?>	

	h1.site-title a, p.site-title a{
		color: <?php echo esc_attr(get_theme_mod('apartment_rental_services_sitetitle_color')); ?> !important;
	}

	.site-description{
		color: <?php echo esc_attr(get_theme_mod('apartment_rental_services_sitetagline_color')); ?> !important;
	}

	.main-nav ul li a {
		color: <?php echo esc_attr(get_theme_mod('apartment_rental_services_menu_color')); ?> !important;
	}

	.main-nav a:hover{
		color: <?php echo esc_attr(get_theme_mod('apartment_rental_services_menuhrv_color')); ?> !important;
	}

	.main-nav ul ul a{
		color: <?php echo esc_attr(get_theme_mod('apartment_rental_services_submenu_color')); ?> !important;
	}

	.main-nav ul ul a:hover {
		color: <?php echo esc_attr(get_theme_mod('apartment_rental_services_submenuhrv_color')); ?> !important;
	}

	#footer h3 {
		color: <?php echo esc_attr(get_theme_mod('apartment_rental_services_footertitle_color')); ?> !important;

	}

	#footer ul li a {
		color: <?php echo esc_attr(get_theme_mod('apartment_rental_services_footerlist_color')); ?>;

	}
	#footer {
		background-color: <?php echo esc_attr(get_theme_mod('apartment_rental_services_footerbg_color')); ?>;
	}
	

	</style>
	<?php 
}
endif;