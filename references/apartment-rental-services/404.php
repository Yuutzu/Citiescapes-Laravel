<?php
/**
 * The template for displaying 404 pages (Not Found).
 *
 * @package Apartment Rental Services
 */

get_header(); ?>

<div class="container">
    <div id="content" class="contentsecwrap">
        <section class="site-main page-not-found">
            <header class="page-header">
                <h1 class="entry-title">
                    <?php echo esc_html(get_theme_mod('apartment_rental_services_page_not_found_heading',__('404 Not Found','apartment-rental-services')));?>
                </h1>
            </header>
            <div class="page-content">
                <p>
                    <?php echo esc_html(get_theme_mod('apartment_rental_services_page_not_found_content',__( 'Looks like you have taken a wrong turn.....Don\'t worry... it happens to the best of us.', 'apartment-rental-services' ))); ?>
                </p>
                <?php if( get_theme_mod('apartment_rental_services_page_not_found_btn','Homepage') != ''){ ?>
                    <div class="not-found-btn mt-3 mb-4 mx-0">
                        <a href="<?php echo esc_url( home_url() ); ?>" class="button py-2 px-3"><?php echo esc_html(get_theme_mod('apartment_rental_services_page_not_found_btn',__('Homepage','apartment-rental-services')));?><span class="screen-reader-text"><?php echo esc_html(get_theme_mod('apartment_rental_services_page_not_found_btn',__('Homepage','apartment-rental-services')));?></span></a>
                    </div>
                <?php } ?>
            </div>
        </section>
        <div class="clear"></div>
    </div>
</div>

<?php get_footer(); ?>