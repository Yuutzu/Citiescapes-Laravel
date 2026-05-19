<?php
/*
 * @package Apartment Rental Services
 */


 function apartment_rental_services_admin_enqueue_scripts() {
    wp_enqueue_style( 'apartment-rental-services-admin-style', esc_url( get_template_directory_uri() ).'/css/addon.css' );

    // Admin notice code START
	wp_register_script('apartment-rental-services-notice', esc_url(get_template_directory_uri()) . '/js/notice.js', array('jquery'), time(), true);
	wp_enqueue_script('apartment-rental-services-notice');
	// Admin notice code END

}
add_action( 'admin_enqueue_scripts', 'apartment_rental_services_admin_enqueue_scripts' );

function apartment_rental_services_theme_info_menu_link() {

    $apartment_rental_services_theme = wp_get_theme();
    add_theme_page(
        /* translators: 1: Theme name. */
        sprintf( esc_html__( 'Welcome to %1$s', 'apartment-rental-services' ), $apartment_rental_services_theme->get( 'Name' )),
        esc_html__( 'Theme Demo Import', 'apartment-rental-services' ),
        'edit_theme_options',
        'apartment-rental-services',
        'apartment_rental_services_theme_info_page'
    );
}
add_action( 'admin_menu', 'apartment_rental_services_theme_info_menu_link' );

function apartment_rental_services_theme_info_page() {

    $apartment_rental_services_theme = wp_get_theme();
    ?>
<div class="wrap theme-info-wrap">
    <h1><?php printf( esc_html__( 'Welcome to %1$s', 'apartment-rental-services' ), esc_html($apartment_rental_services_theme->get( 'Name' ))); ?>
    </h1>
    <p class="theme-description">
    <?php esc_html_e( 'Do you want to configure this theme? Look no further, our easy-to-follow theme documentation will walk you through it.', 'apartment-rental-services' ); ?>
    </p>
    <div class="columns-wrapper clearfix theme-demo">
        <div class="column column-quarter clearfix start-box"> 
            <div class="demo-import">
                <div class="theme-name">
                    <h2><?php echo esc_html( $apartment_rental_services_theme->get( 'Name' ) ); ?></h2>
                    <p class="version"><?php esc_html_e( 'Version', 'apartment-rental-services' ); ?>: <?php echo esc_html( wp_get_theme()->get( 'Version' ) ); ?></p>	
                </div>
                <?php
                    $apartment_rental_services_demo_content_file = apply_filters(
                        'apartment_rental_services_demo_content_path',
                        get_parent_theme_file_path( '/inc/demo-content.php' )
                    );
                    require $apartment_rental_services_demo_content_file;             
                ?>               
                <div id="demo-import-loader">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/images/status.gif'); ?>" alt="<?php echo esc_attr( 'Loading...', 'apartment-rental-services'); ?>" />
                </div>
            </div>
        </div>
        <div class="column column-half clearfix">
            <div class="important-link">
                <div class="main-box columns-wrapper clearfix">

                    <div class="themelink column column-half column-border clearfix">
                        <p><strong><?php esc_html_e( 'Free Theme Documentation', 'apartment-rental-services' ); ?></strong></p>
                        <p><?php esc_html_e( 'Need more details? Please check our full documentation for detailed theme setup.', 'apartment-rental-services' ); ?></p>
                        <a href="<?php echo esc_url( APARTMENT_RENTAL_SERVICES_THEME_DOCUMENTATION ); ?>" target="_blank">
                        <?php esc_html_e( 'Documentation', 'apartment-rental-services' ); ?>
                        </a>
                    </div>

                    <div class="themelink column column-half column-padding clearfix">
                        <p><strong><?php esc_html_e( 'Need Help?', 'apartment-rental-services' ); ?></strong></p>
                        <p><?php esc_html_e( 'Go to our support forum to help you out in case of queries and doubts regarding our theme.', 'apartment-rental-services' ); ?></p>
                        <a href="<?php echo esc_url( APARTMENT_RENTAL_SERVICES_SUPPORT ); ?>" target="_blank">
                        <?php esc_html_e( 'Contact Us', 'apartment-rental-services' ); ?>
                        </a>
                    </div>
                </div>
                <hr>
                <div class="main-box columns-wrapper clearfix">

                    <div class="themelink column column-half column-border clearfix">
                        <p><strong><?php esc_html_e( 'Pro version of our theme', 'apartment-rental-services' ); ?></strong></p>
                        <p><?php esc_html_e( 'Are you excited for our theme? Then we will proceed for pro version of theme.', 'apartment-rental-services' ); ?></p>
                        <a class="get-premium" href="<?php echo esc_url( APARTMENT_RENTAL_SERVICES_PREMIUM_PAGE ); ?>" target="_blank">
                        <?php esc_html_e( 'Get Premium', 'apartment-rental-services' ); ?>
                        </a>
                    </div>

                    <div class="themelink column column-half column-padding clearfix">
                        <p><strong><?php esc_html_e( 'Leave us a review', 'apartment-rental-services' ); ?></strong></p>
                        <p><?php esc_html_e( 'Are you enjoying our theme? We would love to hear your feedback.', 'apartment-rental-services' ); ?></p>
                        <a href="<?php echo esc_url( APARTMENT_RENTAL_SERVICES_REVIEW ); ?>" target="_blank">
                        <?php esc_html_e( 'Rate This Theme', 'apartment-rental-services' ); ?>
                        </a>
                    </div>

                </div>
            </div>
        </div>
        <div class="column column-quarter clearfix start-box"> 
            <div class="bundle-info">
                <img src="<?php echo esc_url( get_template_directory_uri().'/images/bundle.png'); ?>" alt="<?php echo esc_attr( 'screenshot', 'apartment-rental-services'); ?>" class="bundle-image"/>
                <div class="bundle-content themelink">
                    <h3><?php esc_html_e( 'WordPress Theme Bundle', 'apartment-rental-services' ); ?></h3>
                    <small><b><?php esc_html_e( 'Get access to a collection of 100+ stunning WordPress themes for just $99 — featuring designs for every business niche!', 'apartment-rental-services' ); ?></small></b>
                    <a class="get-premium" href="<?php echo esc_url( APARTMENT_RENTAL_SERVICES_BUNDLE_PAGE ); ?>" target="_blank">
                    <?php esc_html_e( 'Get Bundle at 20% OFF', 'apartment-rental-services' ); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div id="getting-started">
        <div class="section">
            <h3><?php 
            /* translators: %s: Theme name. */
            printf( esc_html__( 'Getting started with %s', 'apartment-rental-services' ),
            esc_html($apartment_rental_services_theme->get( 'Name' ))); ?></h3>
            <div class="columns-wrapper clearfix">
                <div class="column column-half clearfix">
                    <div class="section themelink">
                        <div class="">
                            <a class="" href="<?php echo esc_url( APARTMENT_RENTAL_SERVICES_PREMIUM_PAGE ); ?>" target="_blank"><?php esc_html_e( 'Get Premium', 'apartment-rental-services' ); ?></a>
                            <a href="<?php echo esc_url( APARTMENT_RENTAL_SERVICES_PRO_DEMO ); ?>" target="_blank"><?php esc_html_e( 'View Demo', 'apartment-rental-services' ); ?></a>
                            <a class="get-premium" href="<?php echo esc_url( APARTMENT_RENTAL_SERVICES_BUNDLE_PAGE ); ?>" target="_blank"><?php esc_html_e( 'Bundle of 100+ Themes at $99', 'apartment-rental-services' ); ?></a>
                        </div>
                        <div class="theme-description-1"><?php echo esc_html($apartment_rental_services_theme->get( 'Description' )); ?></div>
                    </div>
                </div>
                <div class="column column-half clearfix">
                    <img src="<?php echo esc_url( $apartment_rental_services_theme->get_screenshot() ); ?>" alt="<?php echo esc_attr( 'screenshot', 'apartment-rental-services'); ?>"/>
                </div>
            </div>
        </div>
    </div>
    <hr>
    <div id="theme-author">
      <p><?php
        /* translators: 1: Theme name, 2: Author name, 3: Call to action text. */
        printf( esc_html__( '%1$s is proudly brought to you by %2$s. If you like this theme, %3$s :)', 'apartment-rental-services' ),
            esc_html($apartment_rental_services_theme->get( 'Name' )),
            '<a target="_blank" href="' . esc_url( 'https://www.theclassictemplates.com/', 'apartment-rental-services' ) . '">classictemplate</a>',
            '<a target="_blank" href="' . esc_url(APARTMENT_RENTAL_SERVICES_REVIEW ) . '" title="' . esc_attr__( 'Rate it', 'apartment-rental-services' ) . '">' . esc_html_x( 'rate it', 'If you like this theme, rate it', 'apartment-rental-services' ) . '</a>'
        );
        ?></p>
    </div>
</div>
<?php
}
?>