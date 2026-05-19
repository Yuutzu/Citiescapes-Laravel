<?php
/**
 * Apartment Rental Services Theme Customizer
 *
 * @package Apartment Rental Services
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function apartment_rental_services_customize_register( $wp_customize ) {

	function apartment_rental_services_sanitize_dropdown_pages( $page_id, $setting ) {
  		$page_id = absint( $page_id );
  		return ( 'publish' == get_post_status( $page_id ) ? $page_id : $setting->default );
	}

	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';

	wp_enqueue_style('apartment-rental-services-customize-controls', trailingslashit(esc_url(get_template_directory_uri())).'/css/customize-controls.css');

	if ( class_exists( 'WP_Customize_Control' ) ) {
		require_once get_template_directory() . '/inc/customizer-custom-controls.php';
	}

	// Enable / Disable Logo
	$wp_customize->add_setting('apartment_rental_services_logo_enable',array(
		'default' => true,
		'sanitize_callback' => 'apartment_rental_services_sanitize_checkbox',
	));
	$wp_customize->add_control( 'apartment_rental_services_logo_enable', array(
		'settings' => 'apartment_rental_services_logo_enable',
		'section'   => 'title_tagline',
		'label'     => __('Enable Logo','apartment-rental-services'),
		'type'      => 'checkbox'
	));

	//Logo
    $wp_customize->add_setting('apartment_rental_services_logo_width', array(
        'default' => '',
        'transport' => 'refresh',
        'sanitize_callback' => 'apartment_rental_services_sanitize_integer'
    ));
    $wp_customize->add_control(new Apartment_Rental_Services_Slider_Custom_Control($wp_customize, 'apartment_rental_services_logo_width', array(
    	'label'          => __( 'Logo Width', 'apartment-rental-services'),
        'section' => 'title_tagline',
        'settings' => 'apartment_rental_services_logo_width',
        'input_attrs' => array(
            'step' => 1,
            'min' => 0,
            'max' => 300,
        ),
    )));

	// color site title
	$wp_customize->add_setting('apartment_rental_services_sitetitle_color',array(
		'default' => '',
		'sanitize_callback' => 'apartment_rental_services_sanitize_hex_color',
		'capability' => 'edit_theme_options',
	));
	$wp_customize->add_control( 'apartment_rental_services_sitetitle_color', array(
	   'settings' => 'apartment_rental_services_sitetitle_color',
	   'section'   => 'title_tagline',
	   'label' => __('Site Title Color', 'apartment-rental-services'),
	   'type'      => 'color'
	));

	$wp_customize->add_setting('apartment_rental_services_title_enable',array(
		'default' => false,
		'sanitize_callback' => 'apartment_rental_services_sanitize_checkbox',
	));
	$wp_customize->add_control( 'apartment_rental_services_title_enable', array(
	   'settings' => 'apartment_rental_services_title_enable',
	   'section'   => 'title_tagline',
	   'label'     => __('Enable Site Title','apartment-rental-services'),
	   'type'      => 'checkbox'
	));

	// color site tagline
	$wp_customize->add_setting('apartment_rental_services_sitetagline_color',array(
		'default' => '',
		'sanitize_callback' => 'apartment_rental_services_sanitize_hex_color',
		'capability' => 'edit_theme_options',
	));
	$wp_customize->add_control( 'apartment_rental_services_sitetagline_color', array(
	   'settings' => 'apartment_rental_services_sitetagline_color',
	   'section'   => 'title_tagline',
	   'label' => __('Site Tagline Color', 'apartment-rental-services'),
	   'type'      => 'color'
	));

	$wp_customize->add_setting('apartment_rental_services_tagline_enable',array(
		'default' => false,
		'sanitize_callback' => 'apartment_rental_services_sanitize_checkbox',
	));
	$wp_customize->add_control( 'apartment_rental_services_tagline_enable', array(
	   'settings' => 'apartment_rental_services_tagline_enable',
	   'section'   => 'title_tagline',
	   'label'     => __('Enable Site Tagline','apartment-rental-services'),
	   'type'      => 'checkbox'
	));

	// woocommerce section
	$wp_customize->add_section('apartment_rental_services_woocommerce_page_settings', array(
		'title'    => __('WooCommerce Page Settings', 'apartment-rental-services'),
		'priority' => null,
		'panel'    => 'woocommerce',
	));

	$wp_customize->add_setting('apartment_rental_services_shop_page_sidebar',array(
		'default' => false,
		'sanitize_callback'	=> 'apartment_rental_services_sanitize_checkbox'
	));
	$wp_customize->add_control('apartment_rental_services_shop_page_sidebar',array(
		'type' => 'checkbox',
		'label' => __(' Check To Enable Shop page sidebar','apartment-rental-services'),
		'section' => 'apartment_rental_services_woocommerce_page_settings',
	));

    // shop page sidebar alignment
    $wp_customize->add_setting('apartment_rental_services_shop_page_sidebar_position', array(
		'default'           => 'Right Sidebar',
		'sanitize_callback' => 'apartment_rental_services_sanitize_choices',
	));
	$wp_customize->add_control('apartment_rental_services_shop_page_sidebar_position',array(
		'type'           => 'radio',
		'label'          => __('Shop Page Sidebar', 'apartment-rental-services'),
		'section'        => 'apartment_rental_services_woocommerce_page_settings',
		'choices'        => array(
			'Left Sidebar'  => __('Left Sidebar', 'apartment-rental-services'),
			'Right Sidebar' => __('Right Sidebar', 'apartment-rental-services'),
		),
	));	 

	$wp_customize->add_setting('apartment_rental_services_wooproducts_nav',array(
		'default' => 'Yes',
		'sanitize_callback'	=> 'apartment_rental_services_sanitize_choices'
	));
	$wp_customize->add_control('apartment_rental_services_wooproducts_nav',array(
		'type' => 'select',
		'label' => __('Shop Page Products Navigation','apartment-rental-services'),
		'choices' => array(
			 'Yes' => __('Yes','apartment-rental-services'),
			 'No' => __('No','apartment-rental-services'),
		 ),
		'section' => 'apartment_rental_services_woocommerce_page_settings',
	));	

	$wp_customize->add_setting( 'apartment_rental_services_single_page_sidebar',array(
		'default' => false,
		'sanitize_callback'	=> 'apartment_rental_services_sanitize_checkbox'
    ) );
    $wp_customize->add_control('apartment_rental_services_single_page_sidebar',array(
    	'type' => 'checkbox',
       	'label' => __('Check To Enable Single Product Page Sidebar','apartment-rental-services'),
		'section' => 'apartment_rental_services_woocommerce_page_settings'
    ));

	// single product page sidebar alignment
    $wp_customize->add_setting('apartment_rental_services_single_product_page_layout', array(
		'default'           => 'Right Sidebar',
		'sanitize_callback' => 'apartment_rental_services_sanitize_choices',
	));
	$wp_customize->add_control('apartment_rental_services_single_product_page_layout',array(
		'type'           => 'radio',
		'label'          => __('Single product Page Sidebar', 'apartment-rental-services'),
		'section'        => 'apartment_rental_services_woocommerce_page_settings',
		'choices'        => array(
			'Left Sidebar'  => __('Left Sidebar', 'apartment-rental-services'),
			'Right Sidebar' => __('Right Sidebar', 'apartment-rental-services'),
		),
	));

	$wp_customize->add_setting('apartment_rental_services_related_product_enable',array(
		'default' => true,
		'sanitize_callback'	=> 'apartment_rental_services_sanitize_checkbox'
	));
	$wp_customize->add_control('apartment_rental_services_related_product_enable',array(
		'type' => 'checkbox',
		'label' => __('Check To Enable Related product','apartment-rental-services'),
		'section' => 'apartment_rental_services_woocommerce_page_settings',
	));	

	$wp_customize->add_setting( 'apartment_rental_services_woo_product_img_border_radius', array(
        'default'              => '0',
        'transport'            => 'refresh',
        'sanitize_callback'    => 'apartment_rental_services_sanitize_integer'
    ) );
    $wp_customize->add_control(new Apartment_Rental_Services_Slider_Custom_Control( $wp_customize, 'apartment_rental_services_woo_product_img_border_radius',array(
		'label'	=> esc_html__('Product Img Border Radius','apartment-rental-services'),
		'section'=> 'apartment_rental_services_woocommerce_page_settings',
		'settings'=>'apartment_rental_services_woo_product_img_border_radius',
		'input_attrs' => array(
            'step'             => 1,
			'min'              => 0,
			'max'              => 100,
        ),
	)));

    // Add a setting for number of products per row
    $wp_customize->add_setting('apartment_rental_services_products_per_row', array(
	    'default'   => '4',
	    'transport' => 'refresh',
	    'sanitize_callback' => 'apartment_rental_services_sanitize_integer'  
    ));
   $wp_customize->add_control('apartment_rental_services_products_per_row', array(
	   'label'    => __('Products Per Row', 'apartment-rental-services'),
	   'section'  => 'apartment_rental_services_woocommerce_page_settings',
	   'settings' => 'apartment_rental_services_products_per_row',
	   'type'     => 'select',
	   'choices'  => array(
		      '2' => '2',
		'      3' => '3',
		      '4' => '4',
	  ),
   ) );

   // Add a setting for the number of products per page
   $wp_customize->add_setting('apartment_rental_services_products_per_page', array(
	  'default'   => '9',
	  'transport' => 'refresh',
	  'sanitize_callback' => 'apartment_rental_services_sanitize_integer'
   ));
   $wp_customize->add_control('apartment_rental_services_products_per_page', array(
	  'label'    => __('Products Per Page', 'apartment-rental-services'),
	  'section'  => 'apartment_rental_services_woocommerce_page_settings',
	  'settings' => 'apartment_rental_services_products_per_page',
	  'type'     => 'number',
	  'input_attrs' => array(
		 'min'  => 1,
		 'step' => 1,
	 ),
   ));	

   $wp_customize->add_setting('apartment_rental_services_product_sale_position',array(
	'default' => 'Left',
	'sanitize_callback' => 'apartment_rental_services_sanitize_choices'
	));
	$wp_customize->add_control('apartment_rental_services_product_sale_position',array(
		'type' => 'radio',
		'label' => __('Product Sale Position','apartment-rental-services'),
		'section' => 'apartment_rental_services_woocommerce_page_settings',
		'choices' => array(
			'Left' => __('Left','apartment-rental-services'),
			'Right' => __('Right','apartment-rental-services'),
		),
	) );    

	//Theme Options
	$wp_customize->add_panel( 'apartment_rental_services_panel_area', array(
		'priority' => 10,
		'capability' => 'edit_theme_options',
		'title' => __( 'Theme Options Panel', 'apartment-rental-services' ),
	) );

	//Site Layout Section
	$wp_customize->add_section('apartment_rental_services_site_layoutsec',array(
		'title'	=> __('Manage Site Layout Section ','apartment-rental-services'),
		'description' => __('<p class="sec-title">Manage Site Layout Section</p>','apartment-rental-services'),
		'priority'	=> 1,
		'panel' => 'apartment_rental_services_panel_area',
	));

	$wp_customize->add_setting('apartment_rental_services_preloader',array(
		'default' => false,
		'sanitize_callback' => 'apartment_rental_services_sanitize_checkbox',
	));
	$wp_customize->add_control( 'apartment_rental_services_preloader', array(
	   'section'   => 'apartment_rental_services_site_layoutsec',
	   'label'	=> __('Check to Show preloader','apartment-rental-services'),
	   'type'      => 'checkbox'
 	));

  	$wp_customize->add_setting('apartment_rental_services_box_layout',array(
		'default' => false,
		'sanitize_callback' => 'apartment_rental_services_sanitize_checkbox',
	));
	$wp_customize->add_control( 'apartment_rental_services_box_layout', array(
	   'section'   => 'apartment_rental_services_site_layoutsec',
	   'label'	=> __('Check to Show Box Layout','apartment-rental-services'),
	   'type'      => 'checkbox'
 	)); 

    // Add Settings and Controls for Page Layout
    $wp_customize->add_setting('apartment_rental_services_sidebar_page_layout',array(
	  'default' => 'full',
	  'sanitize_callback' => 'apartment_rental_services_sanitize_choices'
	));
	$wp_customize->add_control('apartment_rental_services_sidebar_page_layout',array(
		'type' => 'radio',
		'label'     => __('Theme Page Sidebar Position', 'apartment-rental-services'),
		'section' => 'apartment_rental_services_site_layoutsec',
		'choices' => array(
			'left' => __('Left','apartment-rental-services'),
			'right' => __('Right','apartment-rental-services'),
			'full' => __('No Sidebar','apartment-rental-services'),		
	),
	) );		

	$wp_customize->add_setting( 'apartment_rental_services_layoutsec_settings_upgraded_features',array(
		'sanitize_callback' => 'sanitize_text_field'
	));
	$wp_customize->add_control('apartment_rental_services_layoutsec_settings_upgraded_features', array(
		'type'=> 'hidden',
		'description' => "<span class='customizer-upgraded-features'>Unlock Premium Customization Features:
		   <a target='_blank' href='". esc_url(APARTMENT_RENTAL_SERVICES_PREMIUM_PAGE) ." '>Upgrade to Pro</a></span>",
		'section' => 'apartment_rental_services_site_layoutsec'
	));   	

    //Global Color
    $wp_customize->add_section('apartment_rental_services_global_color', array(
		'title'    => __('Manage Global Color Section', 'apartment-rental-services'),
		'panel'    => 'apartment_rental_services_panel_area',
	));

	$wp_customize->add_setting('apartment_rental_services_first_color', array(
		'default'           => '#E4C59E',
		'sanitize_callback' => 'sanitize_hex_color',
	));
	$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'apartment_rental_services_first_color', array(
		'label'    => __('Theme Color', 'apartment-rental-services'),
		'section'  => 'apartment_rental_services_global_color',
		'settings' => 'apartment_rental_services_first_color',
	)));		

	$wp_customize->add_setting('apartment_rental_services_second_color', array(
		'default'           => '#803D3B',
		'sanitize_callback' => 'sanitize_hex_color',
	));
	$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'apartment_rental_services_second_color', array(
		'label'    => __('Theme Color', 'apartment-rental-services'),
		'section'  => 'apartment_rental_services_global_color',
		'settings' => 'apartment_rental_services_second_color',
	)));		

	$wp_customize->add_setting( 'apartment_rental_services_global_color_settings_upgraded_features',array(
		'sanitize_callback' => 'sanitize_text_field'
	));
	$wp_customize->add_control('apartment_rental_services_global_color_settings_upgraded_features', array(
		'type'=> 'hidden',
		'description' => "<span class='customizer-upgraded-features'>Unlock Premium Customization Features:
		   <a target='_blank' href='". esc_url(APARTMENT_RENTAL_SERVICES_PREMIUM_PAGE) ." '>Upgrade to Pro</a></span>",
		'section' => 'apartment_rental_services_global_color'
	));   	

 	// Header Section
	$wp_customize->add_section('apartment_rental_services_header_section', array(
        'title' => __('Manage Header Section', 'apartment-rental-services'),
		'description' => __('<p class="sec-title">Manage Header Section</p>','apartment-rental-services'),
        'priority' => null,
		'panel' => 'apartment_rental_services_panel_area',
 	));

	// Sticky Header
	$wp_customize->add_setting( 'apartment_rental_services_sticky_header',array(
		'default' => false,
		'sanitize_callback' => 'apartment_rental_services_sanitize_checkbox',
		'capability' => 'edit_theme_options',
	));
	$wp_customize->add_control( 'apartment_rental_services_sticky_header',array(
		'settings' => 'apartment_rental_services_sticky_header',
		'section'   => 'apartment_rental_services_header_section',
		'label' => esc_html__( 'Check To Enable Sticky Header','apartment-rental-services' ),
		'type'      => 'checkbox'
	));

	$wp_customize->add_setting('apartment_rental_services_header_btn_text',array(
		'default' => '',
		'sanitize_callback' => 'sanitize_text_field',
		'capability' => 'edit_theme_options',
	));
	$wp_customize->add_control( 'apartment_rental_services_header_btn_text', array(
	   'settings' => 'apartment_rental_services_header_btn_text',
	   'section'   => 'apartment_rental_services_header_section',
	   'label' => __('Add Button Text', 'apartment-rental-services'),
	   'type'      => 'text'
	));

	$wp_customize->add_setting('apartment_rental_services_header_btn_url',array(
		'default' => '',
		'sanitize_callback' => 'esc_url_raw',
		'capability' => 'edit_theme_options',
	));
	$wp_customize->add_control( 'apartment_rental_services_header_btn_url', array(
	   'settings' => 'apartment_rental_services_header_btn_url',
	   'section'   => 'apartment_rental_services_header_section',
	   'label' => __('Add Button URL', 'apartment-rental-services'),
	   'type'      => 'url'
	));

	// header menu
	$wp_customize->add_setting('apartment_rental_services_menu_color',array(
		'default' => '',
		'sanitize_callback' => 'apartment_rental_services_sanitize_hex_color',
		'capability' => 'edit_theme_options',
	));
	$wp_customize->add_control( 'apartment_rental_services_menu_color', array(
	   'settings' => 'apartment_rental_services_menu_color',
	   'section'   => 'apartment_rental_services_header_section',
	   'label' => __('Menu Color', 'apartment-rental-services'),
	   'type'      => 'color'
	));

	// header menu hover color
	$wp_customize->add_setting('apartment_rental_services_menuhrv_color',array(
		'default' => '',
		'sanitize_callback' => 'apartment_rental_services_sanitize_hex_color',
		'capability' => 'edit_theme_options',
	));
	$wp_customize->add_control( 'apartment_rental_services_menuhrv_color', array(
	   'settings' => 'apartment_rental_services_menuhrv_color',
	   'section'   => 'apartment_rental_services_header_section',
	   'label' => __('Menu Hover Color', 'apartment-rental-services'),
	   'type'      => 'color'
	));

	// header sub menu color
	$wp_customize->add_setting('apartment_rental_services_submenu_color',array(
		'default' => '',
		'sanitize_callback' => 'apartment_rental_services_sanitize_hex_color',
		'capability' => 'edit_theme_options',
	));
	$wp_customize->add_control( 'apartment_rental_services_submenu_color', array(
	   'settings' => 'apartment_rental_services_submenu_color',
	   'section'   => 'apartment_rental_services_header_section',
	   'label' => __('SubMenu Color', 'apartment-rental-services'),
	   'type'      => 'color'
	));

	// header sub menu hover color
	$wp_customize->add_setting('apartment_rental_services_submenuhrv_color',array(
		'default' => '',
		'sanitize_callback' => 'apartment_rental_services_sanitize_hex_color',
		'capability' => 'edit_theme_options',
	));
	$wp_customize->add_control( 'apartment_rental_services_submenuhrv_color', array(
	   'settings' => 'apartment_rental_services_submenuhrv_color',
	   'section'   => 'apartment_rental_services_header_section',
	   'label' => __('SubMenu Hover Color', 'apartment-rental-services'),
	   'type'      => 'color'
	));

    // Menu Text Transform
    $wp_customize->add_setting( 'apartment_rental_services_menu_text_transform', array(
        'default'           => 'Capitalize',
		'transport' => 'refresh',
		'sanitize_callback' => 'apartment_rental_services_sanitize_choices'
    ));

    $wp_customize->add_control( 'apartment_rental_services_menu_text_transform', array(
        'label'    => __( 'Menu Text Transform', 'apartment-rental-services' ),
        'section'  => 'apartment_rental_services_header_section',
        'type'     => 'select',
        'choices'  => array(
			'None'       => __( 'None', 'apartment-rental-services' ),
            'Capitalize' => __( 'Capitalize', 'apartment-rental-services' ),
            'Uppercase'  => __( 'Uppercase', 'apartment-rental-services' ),
            'Lowercase'  => __( 'Lowercase', 'apartment-rental-services' ),
        ),
    ));

	$wp_customize->add_setting( 'apartment_rental_services_header_settings_upgraded_features',array(
		'sanitize_callback' => 'sanitize_text_field'
	));
	$wp_customize->add_control('apartment_rental_services_header_settings_upgraded_features', array(
		'type'=> 'hidden',
		'description' => "<span class='customizer-upgraded-features'>Unlock Premium Customization Features:
		   <a target='_blank' href='". esc_url(APARTMENT_RENTAL_SERVICES_PREMIUM_PAGE) ." '>Upgrade to Pro</a></span>",
		'section' => 'apartment_rental_services_header_section'
	));  	

	// Slider Section
	$wp_customize->add_section('apartment_rental_services_slider_section',array(
	    'title' => __('Manage Slider Section','apartment-rental-services'),
	    'priority'  => null,
	    'description'	=> __('<p class="sec-title">Manage Slider Section</p> Select Category from the Dropdowns for slider, Also use the given image dimension (1200 x 740).','apartment-rental-services'),
	    'panel' => 'apartment_rental_services_panel_area',
	));	

	$wp_customize->add_setting('apartment_rental_services_slider',array(
		'default' => true,
		'sanitize_callback' => 'apartment_rental_services_sanitize_checkbox',
		'capability' => 'edit_theme_options',
	));
	$wp_customize->add_control( 'apartment_rental_services_slider', array(
	   'settings' => 'apartment_rental_services_slider',
	   'section'   => 'apartment_rental_services_slider_section',
	   'label'     => __('Check To Enable This Section','apartment-rental-services'),
	   'type'      => 'checkbox'
	));

	$apartment_rental_services_categories = get_categories();
	$apartment_rental_services_cat_post = array();
	$apartment_rental_services_cat_post['0'] = 'Select';

	foreach ($apartment_rental_services_categories as $apartment_rental_services_category) {
	    $apartment_rental_services_cat_post[$apartment_rental_services_category->slug] = $apartment_rental_services_category->name;
	}

	$wp_customize->add_setting('apartment_rental_services_slider_cat', array(
	    'default' => '0',
	    'sanitize_callback' => 'apartment_rental_services_sanitize_choices',
	));

	$wp_customize->add_control('apartment_rental_services_slider_cat', array(
	    'type'    => 'select',
	    'choices' => $apartment_rental_services_cat_post,
	    'label'   => __('Select Category to display Latest Post', 'apartment-rental-services'),
	    'description' => __('<p class="sec-title">Choose a category, then reload the page.</p>', 'apartment-rental-services'),
	    'section' => 'apartment_rental_services_slider_section',
	));

	$apartment_rental_services_selected_category = get_theme_mod('apartment_rental_services_slider_cat', '0');

	if ($apartment_rental_services_selected_category && $apartment_rental_services_selected_category !== '0') {
	    $apartment_rental_services_posts = get_posts(array(
	        'category_name'  => $apartment_rental_services_selected_category,
	        'posts_per_page' => -1,
	    ));

	    foreach ($apartment_rental_services_posts as $apartment_rental_services_post) {
	        for ($apartment_rental_services_i = 1; $apartment_rental_services_i <= 3; $apartment_rental_services_i++) {
	            $setting_id = 'apartment_rental_services_slider_img' . $apartment_rental_services_post->ID . '_' . $apartment_rental_services_i;
	            $wp_customize->add_setting($setting_id, array(
	                'default'           => '',
	                'sanitize_callback' => 'esc_url_raw',
	            ));

	            $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, $setting_id, array(
	                'label'   => sprintf(__('Select Image %d for Post: %s', 'apartment-rental-services'), $apartment_rental_services_i, $apartment_rental_services_post->post_title),
	                'section' => 'apartment_rental_services_slider_section',
	            )));
	        }
	    }
	}

	$wp_customize->add_setting('apartment_rental_services_button_text',array(
		'default' => 'EXPLORE APARTMENT',
		'sanitize_callback' => 'sanitize_text_field',
		'capability' => 'edit_theme_options',
	));
	$wp_customize->add_control( 'apartment_rental_services_button_text', array(
	   'settings' => 'apartment_rental_services_button_text',
	   'section'   => 'apartment_rental_services_slider_section',
	   'label' => __('Add Button Text', 'apartment-rental-services'),
	   'type'      => 'text'
	));

	$wp_customize->add_setting('apartment_rental_services_button_link_slider',array(
        'default'=> '',
        'sanitize_callback' => 'esc_url_raw'
    ));
    $wp_customize->add_control('apartment_rental_services_button_link_slider',array(
        'label' => esc_html__('Add Button Link','apartment-rental-services'),
        'section'=> 'apartment_rental_services_slider_section',
        'type'=> 'url'
    ));

    $wp_customize->add_setting('apartment_rental_services_button_text2',array(
		'default' => 'TAKE A LOOK AT VR',
		'sanitize_callback' => 'sanitize_text_field',
		'capability' => 'edit_theme_options',
	));
	$wp_customize->add_control( 'apartment_rental_services_button_text2', array(
	   'settings' => 'apartment_rental_services_button_text2',
	   'section'   => 'apartment_rental_services_slider_section',
	   'label' => __('Add Button Text', 'apartment-rental-services'),
	   'type'      => 'text'
	));

	$wp_customize->add_setting('apartment_rental_services_button_link_slider2',array(
        'default'=> '',
        'sanitize_callback' => 'esc_url_raw'
    ));
    $wp_customize->add_control('apartment_rental_services_button_link_slider2',array(
        'label' => esc_html__('Add Button Link','apartment-rental-services'),
        'section'=> 'apartment_rental_services_slider_section',
        'type'=> 'url'
    ));

	$wp_customize->add_setting( 'apartment_rental_services_slider_settings_upgraded_features',array(
		'sanitize_callback' => 'sanitize_text_field'
	));
	$wp_customize->add_control('apartment_rental_services_slider_settings_upgraded_features', array(
		'type'=> 'hidden',
		'description' => "<span class='customizer-upgraded-features'>Unlock Premium Customization Features:
		   <a target='_blank' href='". esc_url(APARTMENT_RENTAL_SERVICES_PREMIUM_PAGE) ." '>Upgrade to Pro</a></span>",
		'section' => 'apartment_rental_services_slider_section'
	));  	

	// Services Section
	$wp_customize->add_section('apartment_rental_services_service_section', array(
	    'title'       => __('Manage Service Section', 'apartment-rental-services'),
	    'description' => __('<p class="sec-title">Manage Service Section</p>', 'apartment-rental-services'),
	    'priority'    => null,
	    'panel'       => 'apartment_rental_services_panel_area',
	));

	$wp_customize->add_setting('apartment_rental_services_disabled_trending_section', array(
	    'default'           => true,
	    'sanitize_callback' => 'apartment_rental_services_sanitize_checkbox',
	    'capability'        => 'edit_theme_options',
	));
	$wp_customize->add_control('apartment_rental_services_disabled_trending_section', array(
	    'settings' => 'apartment_rental_services_disabled_trending_section',
	    'section'  => 'apartment_rental_services_service_section',
	    'label'    => __('Check To Enable Section', 'apartment-rental-services'),
	    'type'     => 'checkbox',
	));

	$wp_customize->add_setting('apartment_rental_services_service_title', array(
	    'default'           => ' ',
	    'sanitize_callback' => 'sanitize_text_field',
	    'capability'        => 'edit_theme_options',
	));
	$wp_customize->add_control('apartment_rental_services_service_title', array(
	    'settings' => 'apartment_rental_services_service_title',
	    'section'  => 'apartment_rental_services_service_section',
	    'label'    => __('Add Service Title', 'apartment-rental-services'),
	    'type'     => 'text',
	));

	$wp_customize->add_setting('apartment_rental_services_service_text', array(
	    'default'           => ' ',
	    'sanitize_callback' => 'sanitize_text_field',
	    'capability'        => 'edit_theme_options',
	));
	$wp_customize->add_control('apartment_rental_services_service_text', array(
	    'settings' => 'apartment_rental_services_service_text',
	    'section'  => 'apartment_rental_services_service_section',
	    'label'    => __('Add Service Text', 'apartment-rental-services'),
	    'type'     => 'text',
	));

	$apartment_rental_services_categories = get_categories();
	$apartment_rental_services_cat_post = array();
	$apartment_rental_services_cat_post['0'] = 'Select';

	foreach ($apartment_rental_services_categories as $apartment_rental_services_category) {
	    $apartment_rental_services_cat_post[$apartment_rental_services_category->slug] = $apartment_rental_services_category->name;
	}

	$wp_customize->add_setting('apartment_rental_services_posts', array(
	    'default' => '0',
	    'sanitize_callback' => 'apartment_rental_services_sanitize_choices',
	));
	$wp_customize->add_control('apartment_rental_services_posts', array(
	    'type'    => 'select',
	    'choices' => $apartment_rental_services_cat_post,
	    'label'   => __('Select Category to display Latest Post', 'apartment-rental-services'),
	    'section' => 'apartment_rental_services_service_section',
	));

	$wp_customize->add_setting( 'apartment_rental_services_service_settings_upgraded_features',array(
		'sanitize_callback' => 'sanitize_text_field'
	));
	$wp_customize->add_control('apartment_rental_services_service_settings_upgraded_features', array(
		'type'=> 'hidden',
		'description' => "<span class='customizer-upgraded-features'>Unlock Premium Customization Features:
		   <a target='_blank' href='". esc_url(APARTMENT_RENTAL_SERVICES_PREMIUM_PAGE) ." '>Upgrade to Pro</a></span>",
		'section' => 'apartment_rental_services_service_section'
	));  	
	
	//Blog post
	$wp_customize->add_section('apartment_rental_services_blog_post_settings',array(
        'title' => __('Manage Post Section', 'apartment-rental-services'),
        'priority' => null,
        'panel' => 'apartment_rental_services_panel_area'
    ) );

	// Layouts Post
	$wp_customize->add_setting(
		'apartment_rental_services_blog_layout_option_setting',
		array(
			'default'           => 'Left',
			'sanitize_callback' => 'apartment_rental_services_sanitize_choices',
		)
	);

	$wp_customize->add_control(
		new Apartment_Rental_Services_Image_Radio_Control(
			$wp_customize,
			'apartment_rental_services_blog_layout_option_setting',
			array(
				'label'   => __( 'Post Layouts', 'apartment-rental-services' ),
				'section' => 'apartment_rental_services_blog_post_settings',
				'choices' => array(
					'Left' => get_template_directory_uri() . '/images/layout-1.png',
					'Center'    => get_template_directory_uri() . '/images/layout-2.png',
					'Right'   => get_template_directory_uri() . '/images/layout-3.png',
				),
			)
		)
	);

	$wp_customize->add_setting('apartment_rental_services_metafields_date', array(
	    'default' => true,
	    'sanitize_callback' => 'apartment_rental_services_sanitize_checkbox',
	));
	$wp_customize->add_control('apartment_rental_services_metafields_date', array(
	    'settings' => 'apartment_rental_services_metafields_date', 
	    'section'   => 'apartment_rental_services_blog_post_settings',
	    'label'     => __('Check to Enable Date', 'apartment-rental-services'),
	    'type'      => 'checkbox',
	));

	$wp_customize->add_setting('apartment_rental_services_metafields_comments', array(
		'default' => true,
		'sanitize_callback' => 'apartment_rental_services_sanitize_checkbox',
	));	
	$wp_customize->add_control('apartment_rental_services_metafields_comments', array(
		'settings' => 'apartment_rental_services_metafields_comments',
		'section'  => 'apartment_rental_services_blog_post_settings',
		'label'    => __('Check to Enable Comments', 'apartment-rental-services'),
		'type'     => 'checkbox',
	));

	$wp_customize->add_setting('apartment_rental_services_metafields_author', array(
		'default' => true,
		'sanitize_callback' => 'apartment_rental_services_sanitize_checkbox',
	));
	$wp_customize->add_control('apartment_rental_services_metafields_author', array(
		'settings' => 'apartment_rental_services_metafields_author',
		'section'  => 'apartment_rental_services_blog_post_settings',
		'label'    => __('Check to Enable Author', 'apartment-rental-services'),
		'type'     => 'checkbox',
	));		

	$wp_customize->add_setting('apartment_rental_services_metafields_time', array(
		'default' => true,
		'sanitize_callback' => 'apartment_rental_services_sanitize_checkbox',
	));
	$wp_customize->add_control('apartment_rental_services_metafields_time', array(
		'settings' => 'apartment_rental_services_metafields_time',
		'section'  => 'apartment_rental_services_blog_post_settings',
		'label'    => __('Check to Enable Time', 'apartment-rental-services'),
		'type'     => 'checkbox',
	));	

	$wp_customize->add_setting('apartment_rental_services_metabox_seperator',array(
		'default' => '|',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	$wp_customize->add_control('apartment_rental_services_metabox_seperator',array(
		'type' => 'text',
		'label' => __('Metabox Seperator','apartment-rental-services'),
		'description' => __('Ex: "/", "|", "-", ...','apartment-rental-services'),
		'section' => 'apartment_rental_services_blog_post_settings'
	)); 

    // Add Settings and Controls for Post Layout
	$wp_customize->add_setting('apartment_rental_services_sidebar_post_layout',array(
		'default' => 'right',
		'sanitize_callback' => 'apartment_rental_services_sanitize_choices'
	));
	$wp_customize->add_control('apartment_rental_services_sidebar_post_layout',array(
		'type' => 'radio',
		'label'     => __('Theme Post Sidebar Position', 'apartment-rental-services'),
		'description'   => __('This option work for blog page, archive page and search page.', 'apartment-rental-services'),
		'section' => 'apartment_rental_services_blog_post_settings',
		'choices' => array(
			'left' => __('Left','apartment-rental-services'),
			'right' => __('Right','apartment-rental-services'),
			'three-column' => __('Three Columns','apartment-rental-services'),
			'four-column' => __('Four Columns','apartment-rental-services'),
			'grid' => __('Grid Layout','apartment-rental-services'),
			'full' => __('No Sidebar','apartment-rental-services')
     ),
	) );

	$wp_customize->add_setting('apartment_rental_services_blog_post_description_option',array(
    	'default'   => 'Excerpt Content', 
        'sanitize_callback' => 'apartment_rental_services_sanitize_choices'
	));
	$wp_customize->add_control('apartment_rental_services_blog_post_description_option',array(
        'type' => 'radio',
        'label' => __('Post Description Length','apartment-rental-services'),
        'section' => 'apartment_rental_services_blog_post_settings',
        'choices' => array(
            'No Content' => __('No Content','apartment-rental-services'),
            'Excerpt Content' => __('Excerpt Content','apartment-rental-services'),
            'Full Content' => __('Full Content','apartment-rental-services'),
        ),
	) );

	$wp_customize->add_setting('apartment_rental_services_blog_post_thumb',array(
        'sanitize_callback' => 'apartment_rental_services_sanitize_checkbox',
        'default'           => 1,
    ));
    $wp_customize->add_control('apartment_rental_services_blog_post_thumb',array(
        'type'        => 'checkbox',
        'label'       => esc_html__('Show / Hide Blog Post Thumbnail', 'apartment-rental-services'),
        'section'     => 'apartment_rental_services_blog_post_settings',
    ));

	$wp_customize->add_setting( 'apartment_rental_services_blog_post_settings_upgraded_features',array(
		'sanitize_callback' => 'sanitize_text_field'
	));
	$wp_customize->add_control('apartment_rental_services_blog_post_settings_upgraded_features', array(
		'type'=> 'hidden',
		'description' => "<span class='customizer-upgraded-features'>Unlock Premium Customization Features:
		   <a target='_blank' href='". esc_url(APARTMENT_RENTAL_SERVICES_PREMIUM_PAGE) ." '>Upgrade to Pro</a></span>",
		'section' => 'apartment_rental_services_blog_post_settings'
	));	

	//Single Post Settings
	$wp_customize->add_section('apartment_rental_services_single_post_settings',array(
		'title' => __('Manage Single Post Section', 'apartment-rental-services'),
		'priority' => null,
		'panel' => 'apartment_rental_services_panel_area'
	));

	$wp_customize->add_setting('apartment_rental_services_single_post_date',array(
		'default' => true,
		'sanitize_callback'	=> 'apartment_rental_services_sanitize_checkbox'
	));
	$wp_customize->add_control('apartment_rental_services_single_post_date',array(
		'type' => 'checkbox',
		'label' => __('Enable / Disable Date ','apartment-rental-services'),
		'section' => 'apartment_rental_services_single_post_settings'
	));	

	$wp_customize->add_setting('apartment_rental_services_single_post_author',array(
		'default' => true,
		'sanitize_callback'	=> 'apartment_rental_services_sanitize_checkbox'
	));
	$wp_customize->add_control('apartment_rental_services_single_post_author',array(
		'type' => 'checkbox',
		'label' => __('Enable / Disable Author','apartment-rental-services'),
		'section' => 'apartment_rental_services_single_post_settings'
	));

	$wp_customize->add_setting('apartment_rental_services_single_post_comment',array(
		'default' => true,
		'sanitize_callback'	=> 'apartment_rental_services_sanitize_checkbox'
	));
	$wp_customize->add_control('apartment_rental_services_single_post_comment',array(
		'type' => 'checkbox',
		'label' => __('Enable / Disable Comments','apartment-rental-services'),
		'section' => 'apartment_rental_services_single_post_settings'
	));	

	$wp_customize->add_setting('apartment_rental_services_single_post_time',array(
		'default' => true,
		'sanitize_callback'	=> 'apartment_rental_services_sanitize_checkbox'
	));
	$wp_customize->add_control('apartment_rental_services_single_post_time',array(
		'type' => 'checkbox',
		'label' => __('Enable / Disable Time','apartment-rental-services'),
		'section' => 'apartment_rental_services_single_post_settings'
	));	

	$wp_customize->add_setting('apartment_rental_services_single_post_metabox_seperator',array(
		'default' => '|',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	$wp_customize->add_control('apartment_rental_services_single_post_metabox_seperator',array(
		'type' => 'text',
		'label' => __('Metabox Seperator','apartment-rental-services'),
		'description' => __('Ex: "/", "|", "-", ...','apartment-rental-services'),
		'section' => 'apartment_rental_services_single_post_settings'
	)); 

	$wp_customize->add_setting('apartment_rental_services_sidebar_single_post_layout',array(
    	'default' => 'right',
    	 'sanitize_callback' => 'apartment_rental_services_sanitize_choices'
	));
	$wp_customize->add_control('apartment_rental_services_sidebar_single_post_layout',array(
   		'type' => 'radio',
    	'label'     => __('Single post sidebar layout', 'apartment-rental-services'),
     	'section' => 'apartment_rental_services_single_post_settings',
     	'choices' => array(
			'full' => __('Full','apartment-rental-services'),
			'left' => __('Left','apartment-rental-services'),
			'right' => __('Right','apartment-rental-services'),
     ),
	));

	$wp_customize->add_setting( 'apartment_rental_services_single_post_settings_upgraded_features',array(
		'sanitize_callback' => 'sanitize_text_field'
	));
	$wp_customize->add_control('apartment_rental_services_single_post_settings_upgraded_features', array(
		'type'=> 'hidden',
		'description' => "<span class='customizer-upgraded-features'>Unlock Premium Customization Features:
		   <a target='_blank' href='". esc_url(APARTMENT_RENTAL_SERVICES_PREMIUM_PAGE) ." '>Upgrade to Pro</a></span>",
		'section' => 'apartment_rental_services_single_post_settings'
	)); 

	//Page Settings
	$wp_customize->add_section('apartment_rental_services_page_settings',array(
		'title' => __('Manage Page Section', 'apartment-rental-services'),
		'priority' => null,
		'panel' => 'apartment_rental_services_panel_area'
	));

	// Add Settings and Controls for Page Layout
	$wp_customize->add_setting('apartment_rental_services_sidebar_page_layout',array(
		'default' => 'full',
			'sanitize_callback' => 'apartment_rental_services_sanitize_choices'
	));
	$wp_customize->add_control('apartment_rental_services_sidebar_page_layout',array(
		'type' => 'radio',
		'label'     => __('Theme Page Sidebar Position', 'apartment-rental-services'),
		'section' => 'apartment_rental_services_page_settings',
		'choices' => array(
			'left' => __('Left','apartment-rental-services'),
			'right' => __('Right','apartment-rental-services'),
			'full' => __('No Sidebar','apartment-rental-services')
	),
	));	

	$wp_customize->add_setting( 'apartment_rental_services_page_settings_upgraded_features',array(
		'sanitize_callback' => 'sanitize_text_field'
	));
	$wp_customize->add_control('apartment_rental_services_page_settings_upgraded_features', array(
		'type'=> 'hidden',
		'description' => "<span class='customizer-upgraded-features'>Unlock Premium Customization Features:
		<a target='_blank' href='". esc_url(APARTMENT_RENTAL_SERVICES_PREMIUM_PAGE) ." '>Upgrade to Pro</a></span>",
		'section' => 'apartment_rental_services_page_settings'
	));

	// 404 Page Settings
	$wp_customize->add_section('apartment_rental_services_page_not_found', array(
		'title'	=> __('Manage 404 Page Section','apartment-rental-services'),
		'priority'	=> null,
		'panel' => 'apartment_rental_services_panel_area',
	));
	
	$wp_customize->add_setting('apartment_rental_services_page_not_found_heading',array(
		'default'=> __('404 Not Found','apartment-rental-services'),
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	$wp_customize->add_control('apartment_rental_services_page_not_found_heading',array(
		'label'	=> __('404 Heading','apartment-rental-services'),
		'section'=> 'apartment_rental_services_page_not_found',
		'type'=> 'text'
	));

	$wp_customize->add_setting('apartment_rental_services_page_not_found_content',array(
		'default'=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));

	$wp_customize->add_control('apartment_rental_services_page_not_found_content',array(
		'label'	=> __('404 Text','apartment-rental-services'),
		'input_attrs' => array(
			'placeholder' => __( 'Looks like you have taken a wrong turn.....Don\'t worry... it happens to the best of us.', 'apartment-rental-services' ),
		),
		'section'=> 'apartment_rental_services_page_not_found',
		'type'=> 'text'
	));

	$wp_customize->add_setting('apartment_rental_services_page_not_found_btn',array(
		'default' => 'Homepage',
		'sanitize_callback' => 'sanitize_text_field',
		'capability' => 'edit_theme_options',
	));
	$wp_customize->add_control( 'apartment_rental_services_page_not_found_btn', array(
	   'settings' => 'apartment_rental_services_page_not_found_btn',
	   'section'   => 'apartment_rental_services_page_not_found',
	   'label' => __('404 Button', 'apartment-rental-services'),
	   'type'      => 'text'
	));

	$wp_customize->add_setting( 'apartment_rental_services_page_not_found_settings_upgraded_features',array(
		'sanitize_callback' => 'sanitize_text_field'
	));
	$wp_customize->add_control('apartment_rental_services_page_not_found_settings_upgraded_features', array(
		'type'=> 'hidden',
		'description' => "<span class='customizer-upgraded-features'>Unlock Premium Customization Features:
			<a target='_blank' href='". esc_url(APARTMENT_RENTAL_SERVICES_PREMIUM_PAGE) ." '>Upgrade to Pro</a></span>",
		'section' => 'apartment_rental_services_page_not_found'
	));

	// No Results Page Settings
	$wp_customize->add_section('apartment_rental_services_no_results_found', array(
		'title'	=> __('Manage No Results Page Section','apartment-rental-services'),
		'priority'	=> null,
		'panel' => 'apartment_rental_services_panel_area',
	));
	
	$wp_customize->add_setting('apartment_rental_services_page_nothing_found_heading',array(
		'default'=> __('Nothing Found','apartment-rental-services'),
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	$wp_customize->add_control('apartment_rental_services_page_nothing_found_heading',array(
		'label'	=> __('Add Title','apartment-rental-services'),
		'section'=> 'apartment_rental_services_no_results_found',
		'type'=> 'text'
	));

	$wp_customize->add_setting('apartment_rental_services_nothing_found_content',array(
		'default'=> 'Sorry, but nothing matched your search terms. Please try again with some different keywords.',
		'sanitize_callback'	=> 'sanitize_text_field'
	));

	$wp_customize->add_control('apartment_rental_services_nothing_found_content',array(
		'label'	=> __('Add Text','apartment-rental-services'),
		'section'=> 'apartment_rental_services_no_results_found',
		'type'=> 'text'
	));

	$wp_customize->add_setting('apartment_rental_services_nothing_found_search',array(
		'default' => true,
		'sanitize_callback' => 'apartment_rental_services_sanitize_checkbox',
		'capability' => 'edit_theme_options',
	));
	$wp_customize->add_control( 'apartment_rental_services_nothing_found_search', array(
	   'settings' => 'apartment_rental_services_nothing_found_search',
	   'section'   => 'apartment_rental_services_no_results_found',
	   'label'     => __('Check To Enable Search','apartment-rental-services'),
	   'type'      => 'checkbox'
	));

	$wp_customize->add_setting( 'apartment_rental_services_no_results_settings_upgraded_features',array(
		'sanitize_callback' => 'sanitize_text_field'
	));
	$wp_customize->add_control('apartment_rental_services_no_results_settings_upgraded_features', array(
		'type'=> 'hidden',
		'description' => "<span class='customizer-upgraded-features'>Unlock Premium Customization Features:
		<a target='_blank' href='". esc_url(APARTMENT_RENTAL_SERVICES_PREMIUM_PAGE) ." '>Upgrade to Pro</a></span>",
		'section' => 'apartment_rental_services_no_results_found'
	));

	// Animation Section
	$wp_customize->add_section('apartment_rental_services_animation', array(
		'title'	=> __('Manage Animation Section','apartment-rental-services'),
		'description'	=> __('<p class="sec-title">Manage Animation Section</p>','apartment-rental-services'),
		'priority'	=> null,
		'panel' => 'apartment_rental_services_panel_area',
	));

	$wp_customize->add_setting('apartment_rental_services_animation_enabled',array(
		'default' => true,
		'sanitize_callback' => 'apartment_rental_services_sanitize_checkbox',
		'capability' => 'edit_theme_options',
	));
	$wp_customize->add_control( 'apartment_rental_services_animation_enabled', array(
	   'settings' => 'apartment_rental_services_animation_enabled',
	   'section'   => 'apartment_rental_services_animation',
	   'label'     => __('Check To Enable Animation','apartment-rental-services'),
	   'type'      => 'checkbox'
	));

	$wp_customize->add_setting( 'apartment_rental_services_animation_settings_upgraded_features',array(
		'sanitize_callback' => 'sanitize_text_field'
	));
	$wp_customize->add_control('apartment_rental_services_animation_settings_upgraded_features', array(
		'type'=> 'hidden',
		'description' => "<span class='customizer-upgraded-features'>Unlock Premium Customization Features:
		<a target='_blank' href='". esc_url(APARTMENT_RENTAL_SERVICES_PREMIUM_PAGE) ." '>Upgrade to Pro</a></span>",
		'section' => 'apartment_rental_services_animation'
	));

	// Footer Section
	$wp_customize->add_section('apartment_rental_services_footer', array(
		'title'	=> __('Manage Footer Section','apartment-rental-services'),
		'description'	=> __('<p class="sec-title">Manage Footer Section</p>','apartment-rental-services'),
		'priority'	=> null,
		'panel' => 'apartment_rental_services_panel_area',
	));

	$wp_customize->add_setting('apartment_rental_services_footer_widget', array(
	    'default' => true,
	    'sanitize_callback' => 'apartment_rental_services_sanitize_checkbox',
	));
	$wp_customize->add_control('apartment_rental_services_footer_widget', array(
	    'settings' => 'apartment_rental_services_footer_widget', // Corrected setting name
	    'section'   => 'apartment_rental_services_footer',
	    'label'     => __('Check to Enable Footer Widget', 'apartment-rental-services'),
	    'type'      => 'checkbox',
	));

	//  footer bg color
	$wp_customize->add_setting('apartment_rental_services_footerbg_color',array(
		'default' => '',
		'sanitize_callback' => 'apartment_rental_services_sanitize_hex_color',
		'capability' => 'edit_theme_options',
	));
	$wp_customize->add_control( 'apartment_rental_services_footerbg_color', array(
		'settings' => 'apartment_rental_services_footerbg_color',
		'section'   => 'apartment_rental_services_footer',
		'label' => __('Footer Background Color', 'apartment-rental-services'),
		'type'      => 'color'
	));

	$wp_customize->add_setting('apartment_rental_services_footer_bg_image',array(
        'default'   => '',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control( new WP_Customize_Image_Control($wp_customize,'apartment_rental_services_footer_bg_image',array(
        'label' => __('Footer Background Image','apartment-rental-services'),
        'section' => 'apartment_rental_services_footer',
    )));

	$wp_customize->add_setting('apartment_rental_services_footer_img_position',array(
		'default' => 'center center',
		'transport' => 'refresh',
		'sanitize_callback' => 'apartment_rental_services_sanitize_choices'
	));
	$wp_customize->add_control('apartment_rental_services_footer_img_position',array(
		'type' => 'select',
		'label' => __('Footer Image Position','apartment-rental-services'),
		'section' => 'apartment_rental_services_footer',
		'choices' 	=> array(
			'center center'   => esc_html__( 'Center', 'apartment-rental-services' ),
			'center top'   => esc_html__( 'Top', 'apartment-rental-services' ),
			'left center'   => esc_html__( 'Left', 'apartment-rental-services' ),
			'right center'   => esc_html__( 'Right', 'apartment-rental-services' ),
			'center bottom'   => esc_html__( 'Bottom', 'apartment-rental-services' ),
		),
	));	

	$wp_customize->add_setting('apartment_rental_services_copyright_line',array(
		'sanitize_callback' => 'sanitize_text_field',
	));
	$wp_customize->add_control( 'apartment_rental_services_copyright_line', array(
	   'section' 	=> 'apartment_rental_services_footer',
	   'label'	 	=> __('Copyright Line','apartment-rental-services'),
	   'type'    	=> 'text',
	   'priority' 	=> null,
    ));

	$wp_customize->add_setting('apartment_rental_services_copyright_link',array(
		'default' => '',
		'sanitize_callback' => 'sanitize_text_field',
	));	
	$wp_customize->add_control( 'apartment_rental_services_copyright_link', array(
	   'section' 	=> 'apartment_rental_services_footer',
	   'label'	 	=> __('Copyright Link','apartment-rental-services'),
	   'type'    	=> 'text',
	   'priority' 	=> null,
    ));

	//  footer title color
	$wp_customize->add_setting('apartment_rental_services_footertitle_color',array(
		'default' => '',
		'sanitize_callback' => 'apartment_rental_services_sanitize_hex_color',
		'capability' => 'edit_theme_options',
	));
	$wp_customize->add_control( 'apartment_rental_services_footertitle_color', array(
	   'settings' => 'apartment_rental_services_footertitle_color',
	   'section'   => 'apartment_rental_services_footer',
	   'label' => __('Title Color', 'apartment-rental-services'),
	   'type'      => 'color'
	));

	//  footer list color
	$wp_customize->add_setting('apartment_rental_services_footerlist_color',array(
		'default' => '',
		'sanitize_callback' => 'apartment_rental_services_sanitize_hex_color',
		'capability' => 'edit_theme_options',
	));
	$wp_customize->add_control( 'apartment_rental_services_footerlist_color', array(
	   'settings' => 'apartment_rental_services_footerlist_color',
	   'section'   => 'apartment_rental_services_footer',
	   'label' => __('List Color', 'apartment-rental-services'),
	   'type'      => 'color'
	));

	$wp_customize->add_setting('apartment_rental_services_scroll_hide', array(
        'default' => true,
        'sanitize_callback' => 'apartment_rental_services_sanitize_checkbox'
    ));
    $wp_customize->add_control( new WP_Customize_Control($wp_customize,'apartment_rental_services_scroll_hide',array(
        'label'          => __( 'Check To Show Scroll To Top', 'apartment-rental-services' ),
        'section'        => 'apartment_rental_services_footer',
        'settings'       => 'apartment_rental_services_scroll_hide',
        'type'           => 'checkbox',
    )));

	$wp_customize->add_setting('apartment_rental_services_scroll_position',array(
        'default' => 'Right',
        'sanitize_callback' => 'apartment_rental_services_sanitize_choices'
    ));
    $wp_customize->add_control('apartment_rental_services_scroll_position',array(
        'type' => 'radio',
        'section' => 'apartment_rental_services_footer',
        'label'	 	=> __('Scroll To Top Positions','apartment-rental-services'),
        'choices' => array(
            'Right' => __('Right','apartment-rental-services'),
            'Left' => __('Left','apartment-rental-services'),
            'Center' => __('Center','apartment-rental-services')
        ),
    ) );
	
	$wp_customize->add_setting('apartment_rental_services_scroll_text',array(
		'default'	=> __('TOP','apartment-rental-services'),
		'sanitize_callback'	=> 'sanitize_text_field',
	));	
	$wp_customize->add_control('apartment_rental_services_scroll_text',array(
		'label'	=> __('Scroll To Top Button Text','apartment-rental-services'),
		'section'	=> 'apartment_rental_services_footer',
		'type'		=> 'text'
	));

	$wp_customize->add_setting( 'apartment_rental_services_scroll_top_shape', array(
		'default'           => 'circle',
		'sanitize_callback' => 'sanitize_text_field',
	));
	$wp_customize->add_control( 'apartment_rental_services_scroll_top_shape', array(
		'label'    => __( 'Scroll to Top Button Shape', 'apartment-rental-services' ),
		'section'  => 'apartment_rental_services_footer',
		'settings' => 'apartment_rental_services_scroll_top_shape',
		'type'     => 'radio',
		'choices'  => array(
			'box'        => __( 'Box', 'apartment-rental-services' ),
			'curved' => __( 'Curved', 'apartment-rental-services'),
			'circle'     => __( 'Circle', 'apartment-rental-services' ),
		),
	));

	$wp_customize->add_setting('apartment_rental_services_footer_widget_areas',array(
		'default'           => 4,
		'sanitize_callback' => 'apartment_rental_services_sanitize_choices',
	));
	$wp_customize->add_control('apartment_rental_services_footer_widget_areas',array(
		'type'        => 'radio',
		'section' => 'apartment_rental_services_footer',
		'label'       => __('Footer widget area', 'apartment-rental-services'),
		'choices' => array(
		   '1'     => __('One', 'apartment-rental-services'),
		   '2'     => __('Two', 'apartment-rental-services'),
		   '3'     => __('Three', 'apartment-rental-services'),
		   '4'     => __('Four', 'apartment-rental-services')
		),
	));

	$wp_customize->add_setting( 'apartment_rental_services_footer_settings_upgraded_features',array(
		'sanitize_callback' => 'sanitize_text_field'
	));
	$wp_customize->add_control('apartment_rental_services_footer_settings_upgraded_features', array(
		'type'=> 'hidden',
		'description' => "<span class='customizer-upgraded-features'>Unlock Premium Customization Features:
		   <a target='_blank' href='". esc_url(APARTMENT_RENTAL_SERVICES_PREMIUM_PAGE) ." '>Upgrade to Pro</a></span>",
		'section' => 'apartment_rental_services_footer'
	));	

	// Footer Social Section
	$wp_customize->add_section('apartment_rental_services_footer_social_icons', array(
		'title'	=> __('Manage Footer Social Section','apartment-rental-services'),
		'description'	=> __('<p class="sec-title">Manage Footer Social Section</p>','apartment-rental-services'),
		'priority'	=> null,
		'panel' => 'apartment_rental_services_panel_area',
	));

	$wp_customize->add_setting('apartment_rental_services_footer_facebook_link',array(
		'default' => '',
		'sanitize_callback' => 'esc_url_raw',
		'capability' => 'edit_theme_options',
	));
	$wp_customize->add_control( 'apartment_rental_services_footer_facebook_link', array(
	   'settings' => 'apartment_rental_services_footer_facebook_link',
	   'section'   => 'apartment_rental_services_footer_social_icons',
	   'label' => __('Facebook Link', 'apartment-rental-services'),
	   'type'      => 'url'
	));

	$wp_customize->add_setting('apartment_rental_services_footer_instagram_link',array(
		'default' => '',
		'sanitize_callback' => 'esc_url_raw',
		'capability' => 'edit_theme_options',
	));
	$wp_customize->add_control( 'apartment_rental_services_footer_instagram_link', array(
	   'settings' => 'apartment_rental_services_footer_instagram_link',
	   'section'   => 'apartment_rental_services_footer_social_icons',
	   'label' => __('Instagram Link', 'apartment-rental-services'),
	   'type'      => 'url'
	));

	$wp_customize->add_setting('apartment_rental_services_footer_pinterest_link',array(
		'default' => '',
		'sanitize_callback' => 'esc_url_raw',
		'capability' => 'edit_theme_options',
	));
	$wp_customize->add_control( 'apartment_rental_services_footer_pinterest_link', array(
	   'settings' => 'apartment_rental_services_footer_pinterest_link',
	   'section'   => 'apartment_rental_services_footer_social_icons',
	   'label' => __('Pinterest Link', 'apartment-rental-services'),
	   'type'      => 'url'
	));

	$wp_customize->add_setting('apartment_rental_services_footer_twitter_link',array(
		'default' => '',
		'sanitize_callback' => 'esc_url_raw',
		'capability' => 'edit_theme_options',
	));
	$wp_customize->add_control( 'apartment_rental_services_footer_twitter_link', array(
	   'settings' => 'apartment_rental_services_footer_twitter_link',
	   'section'   => 'apartment_rental_services_footer_social_icons',
	   'label' => __('Twitter Link', 'apartment-rental-services'),
	   'type'      => 'url'
	));

	$wp_customize->add_setting('apartment_rental_services_footer_youtube_link',array(
		'default' => '',
		'sanitize_callback' => 'esc_url_raw',
		'capability' => 'edit_theme_options',
	));
	$wp_customize->add_control( 'apartment_rental_services_footer_youtube_link', array(
	   'settings' => 'apartment_rental_services_footer_youtube_link',
	   'section'   => 'apartment_rental_services_footer_social_icons',
	   'label' => __('Youtube Link', 'apartment-rental-services'),
	   'type'      => 'url'
	));

	$wp_customize->add_setting( 'apartment_rental_services_footer_social_settings_upgraded_features',array(
		'sanitize_callback' => 'sanitize_text_field'
	));
	$wp_customize->add_control('apartment_rental_services_footer_social_settings_upgraded_features', array(
		'type'=> 'hidden',
		'description' => "<span class='customizer-upgraded-features'>Unlock Premium Customization Features:
		   <a target='_blank' href='". esc_url(APARTMENT_RENTAL_SERVICES_PREMIUM_PAGE) ." '>Upgrade to Pro</a></span>",
		'section' => 'apartment_rental_services_footer_social_icons'
	));

	// Google Fonts
	$wp_customize->add_section( 'apartment_rental_services_google_fonts_section', array(
	'title'       => __( 'Google Fonts', 'apartment-rental-services' ),
	'priority'    => 24,
	) );

	$font_choices = array(
		'' => 'No Fonts',
		'Kaushan Script:' => 'Kaushan Script',
		'Emilys Candy:' => 'Emilys Candy',
		'Poppins:0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900' => 'Poppins',
		'Source Sans Pro:400,700,400italic,700italic' => 'Source Sans Pro',
		'Open Sans:400italic,700italic,400,700' => 'Open Sans',
		'Oswald:400,700' => 'Oswald',
		'Playfair Display:400,700,400italic' => 'Playfair Display',
		'Montserrat:400,700' => 'Montserrat',
		'Raleway:400,700' => 'Raleway',
		'Droid Sans:400,700' => 'Droid Sans',
		'Lato:400,700,400italic,700italic' => 'Lato',
		'Arvo:400,700,400italic,700italic' => 'Arvo',
		'Lora:400,700,400italic,700italic' => 'Lora',
		'Merriweather:400,300italic,300,400italic,700,700italic' => 'Merriweather',
		'Oxygen:400,300,700' => 'Oxygen',
		'PT Serif:400,700' => 'PT Serif',
		'PT Sans:400,700,400italic,700italic' => 'PT Sans',
		'PT Sans Narrow:400,700' => 'PT Sans Narrow',
		'Cabin:400,700,400italic' => 'Cabin',
		'Fjalla One:400' => 'Fjalla One',
		'Francois One:400' => 'Francois One',
		'Josefin Sans:400,300,600,700' => 'Josefin Sans',
		'Libre Baskerville:400,400italic,700' => 'Libre Baskerville',
		'Arimo:400,700,400italic,700italic' => 'Arimo',
		'Ubuntu:400,700,400italic,700italic' => 'Ubuntu',
		'Bitter:400,700,400italic' => 'Bitter',
		'Droid Serif:400,700,400italic,700italic' => 'Droid Serif',
		'Roboto:400,400italic,700,700italic' => 'Roboto',
		'Open Sans Condensed:700,300italic,300' => 'Open Sans Condensed',
		'Roboto Condensed:400italic,700italic,400,700' => 'Roboto Condensed',
		'Roboto Slab:400,700' => 'Roboto Slab',
		'Yanone Kaffeesatz:400,700' => 'Yanone Kaffeesatz',
		'Rokkitt:400' => 'Rokkitt',
	);

	$wp_customize->add_setting( 'apartment_rental_services_headings_fonts', array(
		'sanitize_callback' => 'apartment_rental_services_sanitize_fonts',
	));
	$wp_customize->add_control( 'apartment_rental_services_headings_fonts', array(
		'type' => 'select',
		'description' => __('Select your desired font for the headings.', 'apartment-rental-services'),
		'section' => 'apartment_rental_services_google_fonts_section',
		'choices' => $font_choices
	));

	$wp_customize->add_setting( 'apartment_rental_services_body_fonts', array(
		'sanitize_callback' => 'apartment_rental_services_sanitize_fonts'
	));
	$wp_customize->add_control( 'apartment_rental_services_body_fonts', array(
		'type' => 'select',
		'description' => __( 'Select your desired font for the body.', 'apartment-rental-services' ),
		'section' => 'apartment_rental_services_google_fonts_section',
		'choices' => $font_choices
	));	

}
add_action( 'customize_register', 'apartment_rental_services_customize_register' );

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function apartment_rental_services_customize_preview_js() {
	wp_enqueue_script( 'apartment_rental_services_customizer', esc_url(get_template_directory_uri()) . '/js/customize-preview.js', array( 'customize-preview' ), '20161510', true );
}
add_action( 'customize_preview_init', 'apartment_rental_services_customize_preview_js' );
