<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div class="container">
 *
 * @package Apartment Rental Services
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php if ( function_exists( 'wp_body_open' ) ) {
  wp_body_open();
} else {
  do_action( 'wp_body_open' );
} ?>

<?php if ( get_theme_mod('apartment_rental_services_preloader', false) != "") { ?>
  <div id="preloader">
    <div id="status">&nbsp;</div>
  </div>
<?php }?>

<a class="screen-reader-text skip-link" href="#content"><?php esc_html_e( 'Skip to content', 'apartment-rental-services' ); ?></a>

<div id="pageholder" <?php if( get_theme_mod( 'apartment_rental_services_box_layout', false) != "" ) { echo 'class="boxlayout"'; } ?>>

<div class="mainhead <?php if( get_theme_mod( 'apartment_rental_services_sticky_header', false) == 1 ) { ?> header-sticky"<?php } else { ?>close-sticky <?php } ?>">
  <div class="container">
    <div class="header px-3">
      <div class="row m-0">
        <div class="col-xxl-2 col-xl-2 col-lg-2 col-md-2 col-sm-12 col-12 align-self-center ps-0">
          <div class="apartment-rental-services-logo text-center text-md-start">
            <?php if (get_theme_mod('apartment_rental_services_logo_enable', true)) { ?>
              <?php apartment_rental_services_the_custom_logo(); ?>
            <?php } ?>
            <div class="site-branding-text">
              <?php if (get_theme_mod('apartment_rental_services_title_enable', false)) { ?>
                <?php if (is_front_page() && is_home()) : ?>
                  <h1 class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a></h1>
                <?php else : ?>
                  <p class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a></p>
                <?php endif; ?>
              <?php } ?>
              <?php $apartment_rental_services_description = get_bloginfo('description', 'display');
              if ($apartment_rental_services_description || is_customize_preview()) : ?>
                <?php if (get_theme_mod('apartment_rental_services_tagline_enable', false)) { ?>
                  <span class="site-description"><?php echo esc_html($apartment_rental_services_description); ?></span>
                <?php } ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="col-xxl-8 col-xl-8 col-lg-8 col-md-6 col-sm-4 col-4 align-self-center">
          <div class="toggle-nav text-center">
            <?php if (has_nav_menu('primary')) { ?>
              <button role="tab"><?php esc_html_e('Menu', 'apartment-rental-services'); ?></button>
            <?php } ?>
          </div>
          <div id="mySidenav" class="nav sidenav">
            <nav id="site-navigation" class="main-nav" role="navigation" aria-label="<?php esc_attr_e('Top Menu', 'apartment-rental-services'); ?>">
              <ul class="mobile_nav">
                <?php wp_nav_menu(array(
                  'theme_location' => 'primary',
                  'container_class' => 'main-menu',
                  'items_wrap' => '%3$s',
                  'fallback_cb' => 'wp_page_menu',
                )); ?>
              </ul>
              <a href="javascript:void(0)" class="close-button"><?php esc_html_e('CLOSE', 'apartment-rental-services'); ?></a>
            </nav>
          </div>
        </div>
        <div class="col-xxl-2 col-xl-2 col-lg-2 col-md-4 col-sm-8 col-8 align-self-center text-end service-btn px-0 py-2">
          <?php if ( get_theme_mod('apartment_rental_services_header_btn_text') != "" || get_theme_mod('apartment_rental_services_header_btn_url') != "") { ?> 
            <a href="<?php echo esc_url(get_theme_mod ('apartment_rental_services_header_btn_url','')); ?>"><?php echo esc_html(get_theme_mod ('apartment_rental_services_header_btn_text','Download CV','apartment-rental-services')); ?><i class="fa-solid fa-comment-dots ms-3"></i></a>
          <?php }?>
        </div>
      </div>
    </div>
  </div>
</div>