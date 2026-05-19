<?php
/**
 * Apartment Rental Services functions and definitions
 *
 * @package Apartment Rental Services
 */

/**
 * Set the content width based on the theme's design and stylesheet.
 */

if ( ! function_exists( 'apartment_rental_services_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support post thumbnails.
 */
function apartment_rental_services_setup() {
	global $content_width;
	if ( ! isset( $content_width ) )
		$content_width = 680;
	
	load_theme_textdomain( 'apartment-rental-services', get_template_directory() . '/languages' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( "responsive-embeds" );
	add_theme_support( 'align-wide' );
	add_theme_support( 'woocommerce' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'wp-block-styles');
	add_theme_support( 'custom-header', array(
		'default-text-color' => false,
		'header-text' => false,
	) );
	add_theme_support( 'custom-logo', array(
		'height'      => 150,
		'width'       => 150,
		'flex-height' => true,
	) );
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'apartment-rental-services' ),
	) );
	add_theme_support( 'custom-background', array(
		'default-color' => 'ffffff'
	) );
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	/*
	 * Enable support for Post Formats.
	 */
	add_theme_support( 'post-formats', array('image','video','gallery','audio',) );

	add_editor_style( 'editor-style.css' );

	// Theme Activation Notice
	global $pagenow;

	if (
		is_admin()
		&&
		('themes.php' == $pagenow)
		// &&
		// isset( $_GET['activated'] )
	) {
		add_action('admin_notices', 'apartment_rental_services_deprecated_hook_admin_notice');
	}
}
endif; // apartment_rental_services_setup
add_action( 'after_setup_theme', 'apartment_rental_services_setup' );

function apartment_rental_services_the_breadcrumb() {
    echo '<div class="breadcrumb my-3">';

    if (!is_home()) {
        echo '<a class="home-main align-self-center" href="' . esc_url(home_url()) . '">';
        bloginfo('name');
        echo "</a>";

        if (is_category() || is_single()) {
            the_category(' ');
            if (is_single()) {
                echo '<span class="current-breadcrumb mx-3">' . esc_html(get_the_title()) . '</span>';
            }
        } elseif (is_page()) {
            echo '<span class="current-breadcrumb mx-3">' . esc_html(get_the_title()) . '</span>';
        }
    }

    echo '</div>';
}

function apartment_rental_services_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Blog Sidebar', 'apartment-rental-services' ),
		'description'   => __( 'Appears on blog page sidebar', 'apartment-rental-services' ),
		'id'            => 'sidebar-1',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Page Sidebar', 'apartment-rental-services' ),
		'id'            => 'sidebar-2',
		'description'   => __( 'Add widgets here to appear in your sidebar on pages.', 'apartment-rental-services' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Sidebar 3', 'apartment-rental-services' ),
		'id'            => 'sidebar-3',
		'description'   => __( 'Add widgets here to appear in your sidebar on blog posts and archive pages.', 'apartment-rental-services' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
	
	$apartment_rental_services_widget_areas = get_theme_mod('apartment_rental_services_footer_widget_areas', '4');
	for ($apartment_rental_services_i=1; $apartment_rental_services_i <= 4; $apartment_rental_services_i++) {
		register_sidebar( array(
			'name'          => __( 'Footer Widget ', 'apartment-rental-services' ) . $apartment_rental_services_i,
			'id'            => 'footer-' . $apartment_rental_services_i,
			'description'   => '',
			'before_widget' => '<aside id="%1$s" class="ftr-4-box widget-column-4 %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		) );
	}

}
add_action( 'widgets_init', 'apartment_rental_services_widgets_init' );

// Change number of products per row to 4
add_filter('loop_shop_columns', 'apartment_rental_services_loop_columns');
if (!function_exists('apartment_rental_services_loop_columns')) {
    function apartment_rental_services_loop_columns() {
        $apartment_rental_services_colm = get_theme_mod('apartment_rental_services_products_per_row', 4); // Default to 4 if not set
        return $apartment_rental_services_colm;
    }
}

// Use the customizer setting to set the number of products per page
function apartment_rental_services_products_per_page($apartment_rental_services_cols) {
    $apartment_rental_services_cols = get_theme_mod('apartment_rental_services_products_per_page', 9); // Default to 9 if not set
    return $apartment_rental_services_cols;
}
add_filter('loop_shop_per_page', 'apartment_rental_services_products_per_page', 9);

function apartment_rental_services_scripts() {
	
	wp_enqueue_style( 'bootstrap-css', esc_url(get_template_directory_uri())."/css/bootstrap.css" );
	wp_enqueue_style('apartment-rental-services-style', get_stylesheet_uri(), array() );
		wp_style_add_data('apartment-rental-services-style', 'rtl', 'replace');

	require get_parent_theme_file_path( '/inc/color-scheme/custom-color-control.php' );
	wp_add_inline_style( 'apartment-rental-services-style',$apartment_rental_services_color_scheme_css );
	
	wp_enqueue_style( 'owl.carousel-css', esc_url(get_template_directory_uri())."/css/owl.carousel.css" );
	wp_enqueue_style( 'apartment-rental-services-default', esc_url(get_template_directory_uri())."/css/default.css" );
	wp_enqueue_style( 'apartment-rental-services-style', get_stylesheet_uri() );
	wp_enqueue_script( 'owl.carousel-js', esc_url(get_template_directory_uri()). '/js/owl.carousel.js', array('jquery') );
	wp_enqueue_script( 'bootstrap-js', esc_url(get_template_directory_uri()). '/js/bootstrap.js', array('jquery') );
	wp_enqueue_script( 'apartment-rental-services-theme', esc_url(get_template_directory_uri()) . '/js/theme.js' );
	wp_enqueue_style( 'font-awesome-css', esc_url(get_template_directory_uri())."/css/fontawesome-all.css" );
	wp_enqueue_style( 'apartment-rental-services-block-style', esc_url( get_template_directory_uri() ).'/css/blocks.css' );	
	/*--------------------------- Animations -------------------*/
    if ( get_theme_mod( 'apartment_rental_services_animation_enabled', true ) ) {
		wp_enqueue_script( 'wow-jquery', get_template_directory_uri() . '/js/wow.js', array('jquery'),'' ,true );
        wp_enqueue_style( 'animate-style', get_template_directory_uri().'/css/animate.css' );
    }

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	// font-family
	$apartment_rental_services_body_font = esc_html(get_theme_mod('apartment_rental_services_body_fonts'));

	if ($apartment_rental_services_body_font) {
	    wp_enqueue_style('apartment-rental-services-body-fonts', 'https://fonts.googleapis.com/css?family=' . urlencode($apartment_rental_services_body_font));
	} else {
	    wp_enqueue_style('playfair display-body', 'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900');
	}
}
add_action( 'wp_enqueue_scripts', 'apartment_rental_services_scripts' );

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Google Fonts
 */
require get_template_directory() . '/inc/gfonts.php';

/**
 * Sanitization Callbacks.
 */
require get_template_directory() . '/inc/sanitization-callbacks.php';

/**
 * Webfont-Loader.
 */
require get_template_directory() . '/inc/wptt-webfont-loader.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/upgrade-to-pro.php';

/**
 * select .
 */
require get_template_directory() . '/inc/select/category-dropdown-custom-control.php';

/**
 * Load TGM.
 */
require get_template_directory() . '/inc/tgm/tgm.php';

/**
 * Theme Info Page.
 */
require get_template_directory() . '/inc/addon.php';

function apartment_rental_services_setup_theme() {
	if ( ! defined( 'APARTMENT_RENTAL_SERVICES_PRO_NAME' ) ) {
		define( 'APARTMENT_RENTAL_SERVICES_PRO_NAME', __( 'About Apartment Rental Services', 'apartment-rental-services' ));
	}
	if ( ! defined( 'APARTMENT_RENTAL_SERVICES_PREMIUM_PAGE' ) ) {
	define('APARTMENT_RENTAL_SERVICES_PREMIUM_PAGE',__('https://www.theclassictemplates.com/products/apartment-rental-wordpress-theme','apartment-rental-services'));
	}
	if ( ! defined( 'APARTMENT_RENTAL_SERVICES_THEME_PAGE' ) ) {
	define('APARTMENT_RENTAL_SERVICES_THEME_PAGE',__('https://www.theclassictemplates.com/collections/best-wordpress-templates','apartment-rental-services'));
	}
	if ( ! defined( 'APARTMENT_RENTAL_SERVICES_SUPPORT' ) ) {
	define('APARTMENT_RENTAL_SERVICES_SUPPORT',__('https://wordpress.org/support/theme/apartment-rental-services/','apartment-rental-services'));
	}
	if ( ! defined( 'APARTMENT_RENTAL_SERVICES_REVIEW' ) ) {
	define('APARTMENT_RENTAL_SERVICES_REVIEW',__('https://wordpress.org/support/theme/apartment-rental-services/reviews/','apartment-rental-services'));
	}
	if ( ! defined( 'APARTMENT_RENTAL_SERVICES_PRO_DEMO' ) ) {
	define('APARTMENT_RENTAL_SERVICES_PRO_DEMO',__('https://live.theclassictemplates.com/apartment-rental-services-pro/','apartment-rental-services'));
	}
	if ( ! defined( 'APARTMENT_RENTAL_SERVICES_THEME_DOCUMENTATION' ) ) {
	define('APARTMENT_RENTAL_SERVICES_THEME_DOCUMENTATION',__('https://live.theclassictemplates.com/demo/docs/apartment-rental-services-free/','apartment-rental-services'));
	}
	if ( ! defined( 'APARTMENT_RENTAL_SERVICES_BUNDLE_PAGE' ) ) {
		define('APARTMENT_RENTAL_SERVICES_BUNDLE_PAGE',__('https://www.theclassictemplates.com/products/wordpress-theme-bundle','apartment-rental-services'));
	}
}
add_action( 'after_setup_theme', 'apartment_rental_services_setup_theme' );

/* Starter Content */
	add_theme_support( 'starter-content', array(
		'widgets' => array(
			'footer-1' => array(
				'categories',
			),
			'footer-2' => array(
				'archives',
			),
			'footer-3' => array(
				'meta',
			),
			'footer-4' => array(
				'search',
			),
		),
    ));
    
// logo
if ( ! function_exists( 'apartment_rental_services_the_custom_logo' ) ) :
/**
 * Displays the optional custom logo.
 *
 * Does nothing if the custom logo is not available.
 *
 */
function apartment_rental_services_the_custom_logo() {
	if ( function_exists( 'the_custom_logo' ) ) {
		the_custom_logo();
	}
}
endif;

/* Activation Notice */
function apartment_rental_services_deprecated_hook_admin_notice() {
	 $apartment_rental_services_theme = wp_get_theme();
	$apartment_rental_services_meta = get_option( 'apartment_rental_services_admin_notice' );

	if (!$apartment_rental_services_meta) {
    ?>
        <div id="apartment-rental-services-welcome-notice" class="getstrat updated notice notice-success welcome-notice is-dismissible notice-get-started-class">
            <div class="admin-image">
                <img style="width: 100%;max-width: 320px;line-height: 40px;display: inline-block;vertical-align: top;border: 2px solid #ddd;border-radius: 4px;" src="<?php echo esc_url(get_stylesheet_directory_uri()) .'/screenshot.png'; ?>" />
            </div>
            <div class="admin-content" >
                <h1><?php
				/* translators: 1: Theme name, 2: Theme version. */
				 printf( esc_html__( 'Welcome to %1$s %2$s', 'apartment-rental-services' ), esc_html($apartment_rental_services_theme->get( 'Name' )), esc_html($apartment_rental_services_theme->get( 'Version' ))); ?>
                </h1>
                <p><?php _e('Get Started With Theme By Clicking On Getting Started.', 'apartment-rental-services'); ?></p>
                <div style="display: grid;">
                    <a class="admin-notice-btn button button-hero upgrade-pro" target="_blank" href="<?php echo esc_url( APARTMENT_RENTAL_SERVICES_PREMIUM_PAGE ); ?>"><?php esc_html_e('Upgrade Pro', 'apartment-rental-services') ?><i class="dashicons dashicons-cart"></i></a>
                    <a class="admin-notice-btn button button-hero" href="<?php echo esc_url( admin_url( 'themes.php?page=apartment-rental-services' )); ?>"><?php esc_html_e( 'Get started', 'apartment-rental-services' ) ?><i class="dashicons dashicons-backup"></i></a>
                    <a class="admin-notice-btn button button-hero" target="_blank" href="<?php echo esc_url( APARTMENT_RENTAL_SERVICES_THEME_DOCUMENTATION ); ?>"><?php esc_html_e('Free Doc', 'apartment-rental-services') ?><i class="dashicons dashicons-visibility"></i></a>
                    <a  class="admin-notice-btn button button-hero" target="_blank" href="<?php echo esc_url( APARTMENT_RENTAL_SERVICES_PRO_DEMO ); ?>"><?php esc_html_e('View Demo', 'apartment-rental-services') ?><i class="dashicons dashicons-awards"></i></a>
                </div>
            </div>
			<div class="admin-bundle-image">
				<a href="<?php echo esc_url( APARTMENT_RENTAL_SERVICES_BUNDLE_PAGE ); ?>" target="_blank"><img src="<?php echo esc_url(get_stylesheet_directory_uri()) .'/images/image_1.webp'; ?>" /></a>
			</div>
        </div>
	<?php
    }
}
// Admin notice code START
function apartment_rental_services_dismissed_notice() {
	update_option( 'apartment_rental_services_admin_notice', true );
}
add_action( 'wp_ajax_apartment_rental_services_dismissed_notice', 'apartment_rental_services_dismissed_notice' );


//After Switch theme function
add_action('after_switch_theme', 'apartment_rental_services_getstart_setup_options');
function apartment_rental_services_getstart_setup_options () {
    update_option('apartment_rental_services_admin_notice', false );
}
// Admin notice code END