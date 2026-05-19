<?php
/**
 * The Template Name: Home Page
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package Apartment Rental Services
 */

get_header(); ?>

<div id="content" >
    <?php
        $apartment_rental_services_hidepageboxes = get_theme_mod('apartment_rental_services_slider', true);
        $apartment_rental_services_catData = get_theme_mod('apartment_rental_services_slider_cat');
        if ($apartment_rental_services_hidepageboxes && $apartment_rental_services_catData) { ?>
        <section id="slider-cat">
            <div class="slideimg">
                <div class="owl-carousel m-0">
                    <?php
                        $apartment_rental_services_page_query = new WP_Query(
                            array(
                                'category_name' => esc_attr($apartment_rental_services_catData),
                                'posts_per_page' => -1,
                            )
                        );
                        while ($apartment_rental_services_page_query->have_posts()) : $apartment_rental_services_page_query->the_post(); ?>
                            <div class="imagebox position-relative">
                                <?php if(has_post_thumbnail()){
                                    the_post_thumbnail('full', array('class' => 'post-image'));
                                } else { ?>
                                    <img src="<?php echo esc_url(get_template_directory_uri()); ?>/images/slider.png" alt="<?php echo esc_attr( 'slider', 'apartment-rental-services'); ?>" class="post-image"/>
                                <?php } ?>
                                <div class="container">
                                    <div class="row">
                                        <div class="col-lg-7 col-md-6 col-12 align-self-center mb-md-0 mb-2 slider-post-content">
                                            <div class="text-content">
                                                <h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
                                                <div class="sliderbtn pt-4">
                                                    <?php 
                                                        $apartment_rental_services_button_text = get_theme_mod('apartment_rental_services_button_text', 'EXPLORE APARTMENT');
                                                        $apartment_rental_services_button_link_slider = get_theme_mod('apartment_rental_services_button_link_slider', ''); 
                                                        if (empty($apartment_rental_services_button_link_slider)) {
                                                            $apartment_rental_services_button_link_slider = esc_url(get_permalink());
                                                        }
                                                        if ($apartment_rental_services_button_text || !empty($apartment_rental_services_button_link_slider)) { ?>
                                                        <?php if(get_theme_mod('apartment_rental_services_button_text', 'EXPLORE APARTMENT') != ''){ ?>
                                                            <a href="<?php echo esc_url($apartment_rental_services_button_link_slider); ?>" class="post-btn1 me-3">
                                                                <?php echo esc_html($apartment_rental_services_button_text); ?>
                                                                <span class="screen-reader-text"><?php echo esc_html($apartment_rental_services_button_text); ?></span>
                                                            </a>
                                                        <?php } ?>
                                                    <?php } ?>
                                                    <?php 
                                                        $apartment_rental_services_button_text2 = get_theme_mod('apartment_rental_services_button_text2', 'TAKE A LOOK AT VR');
                                                        $apartment_rental_services_button_link_slider2 = get_theme_mod('apartment_rental_services_button_link_slider2', ''); 
                                                        if (empty($apartment_rental_services_button_link_slider2)) {
                                                            $apartment_rental_services_button_link_slider2 = esc_url(get_permalink());
                                                        }
                                                        if ($apartment_rental_services_button_text2 || !empty($apartment_rental_services_button_link_slider2)) { ?>
                                                        <?php if(get_theme_mod('apartment_rental_services_button_text2', 'TAKE A LOOK AT VR') != ''){ ?>
                                                            <a href="<?php echo esc_url($apartment_rental_services_button_link_slider2); ?>" class="post-btn2">
                                                                <?php echo esc_html($apartment_rental_services_button_text2); ?>
                                                                <span class="screen-reader-text"><?php echo esc_html($apartment_rental_services_button_text2); ?></span>
                                                            </a>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-5 col-md-6 col-12 align-self-center slider-img-col">
                                            <?php for ($apartment_rental_services_i = 1; $apartment_rental_services_i <= 3; $apartment_rental_services_i++) { ?>
                                                <div class="slideimg<?php echo $apartment_rental_services_i; ?>">
                                                    <?php 
                                                    $apartment_rental_services_image_setting = 'apartment_rental_services_slider_img' . get_the_ID() . '_' . $apartment_rental_services_i;
                                                    $apartment_rental_services_image_url = get_theme_mod($apartment_rental_services_image_setting);
                                                    if (empty($apartment_rental_services_image_url)) { ?>
                                                        <div class="default-bg"></div>
                                                    <?php } else { ?>
                                                        <img src="<?php echo esc_url($apartment_rental_services_image_url); ?>" alt="<?php echo esc_attr( 'slider', 'apartment-rental-services'); ?>"/>
                                                    <?php } ?>
                                                    <span class="img-border"></span>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile;
                        wp_reset_postdata();
                    ?>
                </div>
            </div>
        </section>
    <?php } ?>

    <!-- Service Section -->
    <?php
        $apartment_rental_services_hide_trending_section = get_theme_mod('apartment_rental_services_disabled_trending_section', true);
        $apartment_rental_services_post_cat = get_theme_mod('apartment_rental_services_posts');
        if ($apartment_rental_services_hide_trending_section && $apartment_rental_services_post_cat) { ?>
        <section id="service-section" class="my-5">
            <div class="container">
                <div class="blog-bx">
                    <?php if (get_theme_mod('apartment_rental_services_service_title') != "") { ?>
                        <h2 class="service-title pb-2 text-capitalize text-center"><?php echo esc_html(get_theme_mod('apartment_rental_services_service_title', 'apartment-rental-services')); ?></h2>
                    <?php } ?>
                    <?php if (get_theme_mod('apartment_rental_services_service_text') != "") { ?>
                        <p class="pb-4 text-center service-text"><?php echo esc_html(get_theme_mod('apartment_rental_services_service_text', 'apartment-rental-services')); ?></p>
                    <?php } ?>
                </div> 
                <div class="container">
                    <div class="row">
                        <?php
                            $apartment_rental_services_page_query = new WP_Query(
                                array(
                                    'category_name' => esc_attr($apartment_rental_services_post_cat),
                                    'posts_per_page' => -1,
                                )
                            );
                            $apartment_rental_services_post_counter = 1;
                            while ($apartment_rental_services_page_query->have_posts()) : $apartment_rental_services_page_query->the_post(); ?>
                                    <div class="col-lg-4 col-md-6 col-12 align-self-center mb-3 services">
                                        <div class="service-content">
                                            <div class="service-image">
                                                <?php if(has_post_thumbnail()){
                                                  the_post_thumbnail('full');
                                                  } else{?>
                                                  <img src="<?php echo esc_url(get_template_directory_uri()); ?>/images/post-image.png" alt="<?php echo esc_attr( 'post-image', 'apartment-rental-services'); ?>"/>
                                                <?php } ?>
                                            </div>
                                            <h3 class="pt-2"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                            <?php
                                                $apartment_rental_services_trimexcerpt  = get_the_excerpt();
                                                $apartment_rental_services_shortexcerpt = wp_trim_words($apartment_rental_services_trimexcerpt, $apartment_rental_services_num_words = 5);
                                                echo '<p class="post-text">' . esc_html($apartment_rental_services_shortexcerpt) . '</p>';
                                            ?>
                                        </div>
                                    </div>
                                <?php $apartment_rental_services_post_counter++; ?>
                        <?php endwhile;
                        wp_reset_postdata();
                        ?>
                    </div>
                </div>
            </div>
        </section>
    <?php } ?>
</div>
<?php get_footer(); ?>