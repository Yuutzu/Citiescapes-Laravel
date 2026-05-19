<?php

$apartment_rental_services_first_color = get_theme_mod('apartment_rental_services_first_color');
$apartment_rental_services_second_color = get_theme_mod('apartment_rental_services_second_color');
$apartment_rental_services_color_scheme_css = '';

/*------------------ Global First Color -----------*/

if ($apartment_rental_services_first_color) {
  $apartment_rental_services_color_scheme_css .= ':root {';
  $apartment_rental_services_color_scheme_css .= '--first-theme-color: ' . esc_attr($apartment_rental_services_first_color) . ' !important;';
  $apartment_rental_services_color_scheme_css .= '} ';
}

/*------------------ Global Second Color -----------*/
  
  if ($apartment_rental_services_second_color) {
    $apartment_rental_services_color_scheme_css .= ':root {';
    $apartment_rental_services_color_scheme_css .= '--second-theme-color: ' . esc_attr($apartment_rental_services_second_color) . ' !important;';
    $apartment_rental_services_color_scheme_css .= '} ';
  }

// Sticky Header
$apartment_rental_services_resp_stickyheader = get_theme_mod( 'apartment_rental_services_sticky_header',false);
if($apartment_rental_services_resp_stickyheader != true){
    $apartment_rental_services_color_scheme_css .='.header-fixed{';
        $apartment_rental_services_color_scheme_css .='position:static;';
    $apartment_rental_services_color_scheme_css .='} ';
}
if($apartment_rental_services_resp_stickyheader == true){
    $apartment_rental_services_color_scheme_css .='@media screen and (max-width:575px) {';
    $apartment_rental_services_color_scheme_css .='.header-fixed{';
        $apartment_rental_services_color_scheme_css .='position:fixed;';
    $apartment_rental_services_color_scheme_css .='} }';
}else if($apartment_rental_services_resp_stickyheader == false){
    $apartment_rental_services_color_scheme_css .='@media screen and (max-width:575px){';
    $apartment_rental_services_color_scheme_css .='.header-fixed{';
        $apartment_rental_services_color_scheme_css .='position:static;';
    $apartment_rental_services_color_scheme_css .='} }';
}

//---------------------------------Logo-Max-height--------- 
  $apartment_rental_services_logo_width = get_theme_mod('apartment_rental_services_logo_width');

  if($apartment_rental_services_logo_width != false){

    $apartment_rental_services_color_scheme_css .='.apartment-rental-services-logo img{';

      $apartment_rental_services_color_scheme_css .='width: '.esc_html($apartment_rental_services_logo_width).'px;';

    $apartment_rental_services_color_scheme_css .='}';
  }

  // by default header
  $apartment_rental_services_slider = get_theme_mod('apartment_rental_services_slider', true);

  if($apartment_rental_services_slider = true){

  $apartment_rental_services_color_scheme_css .='.page-template .mainhead{';

    $apartment_rental_services_color_scheme_css .='position: static; background-color: #ffffff;';

  $apartment_rental_services_color_scheme_css .='}';

  }

/*--------------------------- Woocommerce Product Image Border Radius -------------------*/

$apartment_rental_services_woo_product_img_border_radius = get_theme_mod('apartment_rental_services_woo_product_img_border_radius');
  if($apartment_rental_services_woo_product_img_border_radius != false){
    $apartment_rental_services_color_scheme_css .='.woocommerce ul.products li.product a img{';
    $apartment_rental_services_color_scheme_css .='border-radius: '.esc_attr($apartment_rental_services_woo_product_img_border_radius).'px;';
    $apartment_rental_services_color_scheme_css .='}';
}    

/*--------------------------- Woocommerce Product Sale Position -------------------*/    

$apartment_rental_services_product_sale_position = get_theme_mod( 'apartment_rental_services_product_sale_position','Left');
if($apartment_rental_services_product_sale_position == 'Right'){
    $apartment_rental_services_color_scheme_css .='.woocommerce ul.products li.product .onsale{';
        $apartment_rental_services_color_scheme_css .='left:auto !important; right:1em !important;';
    $apartment_rental_services_color_scheme_css .='}';
}else if($apartment_rental_services_product_sale_position == 'Left'){
    $apartment_rental_services_color_scheme_css .='.woocommerce ul.products li.product .onsale {';
        $apartment_rental_services_color_scheme_css .='right:auto !important; left:1em !important;';
    $apartment_rental_services_color_scheme_css .='}';
}   

/*--------------------------- Shop page pagination -------------------*/

$apartment_rental_services_wooproducts_nav = get_theme_mod('apartment_rental_services_wooproducts_nav', 'Yes');
if($apartment_rental_services_wooproducts_nav == 'No'){
  $apartment_rental_services_color_scheme_css .='.woocommerce nav.woocommerce-pagination{';
    $apartment_rental_services_color_scheme_css .='display: none;';
  $apartment_rental_services_color_scheme_css .='}';
}

/*--------------------------- Related Product -------------------*/

$apartment_rental_services_related_product_enable = get_theme_mod('apartment_rental_services_related_product_enable',true);
if($apartment_rental_services_related_product_enable == false){
  $apartment_rental_services_color_scheme_css .='.related.products{';
    $apartment_rental_services_color_scheme_css .='display: none;';
  $apartment_rental_services_color_scheme_css .='}';
}

/*--------------------------- Footer background image -------------------*/

$apartment_rental_services_footer_bg_image = get_theme_mod('apartment_rental_services_footer_bg_image');
if($apartment_rental_services_footer_bg_image != false){
    $apartment_rental_services_color_scheme_css .='#footer{';
        $apartment_rental_services_color_scheme_css .='background: url('.esc_attr($apartment_rental_services_footer_bg_image).');';
        $apartment_rental_services_color_scheme_css .= 'background-size: cover;';  
    $apartment_rental_services_color_scheme_css .='}';
}

/*--------------------------- Footer image position -------------------*/

$apartment_rental_services_footer_img_position = get_theme_mod('apartment_rental_services_footer_img_position','center center');
if($apartment_rental_services_footer_img_position != false){
    $apartment_rental_services_color_scheme_css .='#footer{';
        $apartment_rental_services_color_scheme_css .='background-position: '.esc_attr($apartment_rental_services_footer_img_position).';';
    $apartment_rental_services_color_scheme_css .='}';
}	

/*--------------------------- Scroll to top positions -------------------*/

$apartment_rental_services_scroll_position = get_theme_mod( 'apartment_rental_services_scroll_position','Right');
if($apartment_rental_services_scroll_position == 'Right'){
    $apartment_rental_services_color_scheme_css .='#button{';
        $apartment_rental_services_color_scheme_css .='right: 20px;';
    $apartment_rental_services_color_scheme_css .='}';
}else if($apartment_rental_services_scroll_position == 'Left'){
    $apartment_rental_services_color_scheme_css .='#button{';
        $apartment_rental_services_color_scheme_css .='left: 20px;';
    $apartment_rental_services_color_scheme_css .='}';
}else if($apartment_rental_services_scroll_position == 'Center'){
    $apartment_rental_services_color_scheme_css .='#button{';
        $apartment_rental_services_color_scheme_css .='right: 50%;left: 50%;';
    $apartment_rental_services_color_scheme_css .='}';
}

/*--------------------------- Scroll to Top Button Shape -------------------*/

$apartment_rental_services_scroll_top_shape = get_theme_mod('apartment_rental_services_scroll_top_shape', 'circle');
if($apartment_rental_services_scroll_top_shape == 'box' ){
  $apartment_rental_services_color_scheme_css .='#button{';
    $apartment_rental_services_color_scheme_css .=' border-radius: 0%';
  $apartment_rental_services_color_scheme_css .='}';
}elseif($apartment_rental_services_scroll_top_shape == 'curved' ){
  $apartment_rental_services_color_scheme_css .='#button{';
    $apartment_rental_services_color_scheme_css .=' border-radius: 20%';
  $apartment_rental_services_color_scheme_css .='}';
}elseif($apartment_rental_services_scroll_top_shape == 'circle' ){
  $apartment_rental_services_color_scheme_css .='#button{';
    $apartment_rental_services_color_scheme_css .=' border-radius: 50%;';
  $apartment_rental_services_color_scheme_css .='}';
}

/*--------------------------- Menu Typography -------------------*/

$apartment_rental_services_theme_lay = get_theme_mod( 'apartment_rental_services_menu_text_transform','Capitalize');
if($apartment_rental_services_theme_lay == 'Uppercase'){
    $apartment_rental_services_color_scheme_css .='.main-nav a{';
        $apartment_rental_services_color_scheme_css .='text-transform: uppercase;';
    $apartment_rental_services_color_scheme_css .='}';
}else if($apartment_rental_services_theme_lay == 'Lowercase'){
    $apartment_rental_services_color_scheme_css .='.main-nav a{';
        $apartment_rental_services_color_scheme_css .='text-transform: lowercase;';
    $apartment_rental_services_color_scheme_css .='}';
}
else if($apartment_rental_services_theme_lay == 'Capitalize'){
    $apartment_rental_services_color_scheme_css .='.main-nav a{';
        $apartment_rental_services_color_scheme_css .='text-transform: capitalize;';
    $apartment_rental_services_color_scheme_css .='}';
}

/*--------------------------- Post Layout -------------------*/

$apartment_rental_services_blog_layouts = get_theme_mod('apartment_rental_services_blog_layout_option_setting', 'Left');
if ($apartment_rental_services_blog_layouts == 'Left') {
    $apartment_rental_services_color_scheme_css .= '.postsec-list .listarticle{';
    $apartment_rental_services_color_scheme_css .= 'text-align:left;';
    $apartment_rental_services_color_scheme_css .= '}';
} elseif ($apartment_rental_services_blog_layouts == 'Center') {
    $apartment_rental_services_color_scheme_css .= '.postsec-list .listarticle{';
    $apartment_rental_services_color_scheme_css .= 'text-align:center;';
    $apartment_rental_services_color_scheme_css .= '}';
} elseif ($apartment_rental_services_blog_layouts == 'Right') {
    $apartment_rental_services_color_scheme_css .= '.postsec-list .listarticle{';
    $apartment_rental_services_color_scheme_css .= 'text-align:right;';
    $apartment_rental_services_color_scheme_css .= '}';
}