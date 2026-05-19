<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package Apartment Rental Services
 */
?>
<div id="footer">
  <?php 
    $apartment_rental_services_footer_widget_enabled = get_theme_mod('apartment_rental_services_footer_widget', true);
    if ($apartment_rental_services_footer_widget_enabled !== false && $apartment_rental_services_footer_widget_enabled !== '') { ?>

    <?php 
        $apartment_rental_services_widget_areas = get_theme_mod('apartment_rental_services_footer_widget_areas', '4');
        if ($apartment_rental_services_widget_areas == '3') {
            $apartment_rental_services_cols = 'col-lg-4 col-md-6';
        } elseif ($apartment_rental_services_widget_areas == '4') {
            $apartment_rental_services_cols = 'col-lg-3 col-md-6';
        } elseif ($apartment_rental_services_widget_areas == '2') {
            $apartment_rental_services_cols = 'col-lg-6 col-md-6';
        } else {
            $apartment_rental_services_cols = 'col-lg-12 col-md-12';
        }
    ?>

    <div class="footer-widget">
        <div class="container">
          <div class="row wow bounceInUp delay-3000" data-wow-duration="2s">
            <!-- Footer 1 -->
            <div class="<?php echo esc_attr($apartment_rental_services_cols); ?> footer-block">
                <?php if (is_active_sidebar('footer-1')) : ?>
                    <?php dynamic_sidebar('footer-1'); ?>
                <?php else : ?>
                    <aside id="categories" class="widget py-3" role="complementary" aria-label="<?php esc_attr_e('footer1', 'apartment-rental-services'); ?>">
                        <h3 class="widget-title"><?php esc_html_e('Categories', 'apartment-rental-services'); ?></h3>
                        <ul>
                            <?php wp_list_categories('title_li='); ?>
                        </ul>
                    </aside>
                <?php endif; ?>
            </div>

            <!-- Footer 2 -->
            <div class="<?php echo esc_attr($apartment_rental_services_cols); ?> footer-block">
                <?php if (is_active_sidebar('footer-2')) : ?>
                    <?php dynamic_sidebar('footer-2'); ?>
                <?php else : ?>
                    <aside id="archives" class="widget py-3" role="complementary" aria-label="<?php esc_attr_e('footer2', 'apartment-rental-services'); ?>">
                        <h3 class="widget-title"><?php esc_html_e('Archives', 'apartment-rental-services'); ?></h3>
                        <ul>
                            <?php wp_get_archives(array('type' => 'monthly')); ?>
                        </ul>
                    </aside>
                <?php endif; ?>
            </div>

            <!-- Footer 3 -->
            <div class="<?php echo esc_attr($apartment_rental_services_cols); ?> footer-block">
                <?php if (is_active_sidebar('footer-3')) : ?>
                    <?php dynamic_sidebar('footer-3'); ?>
                <?php else : ?>
                    <aside id="meta" class="widget py-3" role="complementary" aria-label="<?php esc_attr_e('footer3', 'apartment-rental-services'); ?>">
                        <h3 class="widget-title"><?php esc_html_e('Meta', 'apartment-rental-services'); ?></h3>
                        <ul>
                            <?php wp_register(); ?>
                            <li><?php wp_loginout(); ?></li>
                            <?php wp_meta(); ?>
                        </ul>
                    </aside>
                <?php endif; ?>
            </div>

            <!-- Footer 4 -->
            <div class="<?php echo esc_attr($apartment_rental_services_cols); ?> footer-block">
                <?php if (is_active_sidebar('footer-4')) : ?>
                    <?php dynamic_sidebar('footer-4'); ?>
                <?php else : ?>
                    <aside id="search-widget" class="widget py-3" role="complementary" aria-label="<?php esc_attr_e('footer4', 'apartment-rental-services'); ?>">
                        <h3 class="widget-title"><?php esc_html_e('Search', 'apartment-rental-services'); ?></h3>
                        <?php the_widget('WP_Widget_Search'); ?>
                    </aside>
                <?php endif; ?>
            </div>
          </div>
        </div>
    </div>

    <?php } ?>
    <div class="clear"></div>
  <div class="copywrap text-center">
    <?php $apartment_rental_services_social_links_present = get_theme_mod('apartment_rental_services_footer_facebook_link') || get_theme_mod('apartment_rental_services_footer_instagram_link') || get_theme_mod('apartment_rental_services_footer_pinterest_link') || get_theme_mod('apartment_rental_services_footer_twitter_link') || get_theme_mod('apartment_rental_services_footer_youtube_link'); ?>
    <div class="container copywrap-info <?php echo $apartment_rental_services_social_links_present ? '' : 'center-content'; ?>">
      <p>
        <a href="<?php 
          $apartment_rental_services_copyright_link = get_theme_mod('apartment_rental_services_copyright_link', '');
          if (empty($apartment_rental_services_copyright_link)) {
              echo esc_url('https://www.theclassictemplates.com/products/free-apartment-wordpress-theme');
          } else {
              echo esc_url($apartment_rental_services_copyright_link);
          } ?>" target="_blank">
          <?php echo esc_html(get_theme_mod('apartment_rental_services_copyright_line', __('Apartment WordPress Theme', 'apartment-rental-services'))); ?>
        </a> 
        <?php echo esc_html('By Classic Templates', 'apartment-rental-services'); ?>
      </p>
      <?php if ( $apartment_rental_services_social_links_present ) { ?>
            <div class="footer-social d-flex gap-3">
                <?php if ( get_theme_mod('apartment_rental_services_footer_facebook_link') ) { ?>
                    <a title="<?php echo esc_attr('facebook', 'apartment-rental-services'); ?>" target="_blank" href="<?php echo esc_url(get_theme_mod('apartment_rental_services_footer_facebook_link')); ?>"><i class="fa-brands fa-facebook-f"></i></a> 
                <?php } ?>
                <?php if ( get_theme_mod('apartment_rental_services_footer_instagram_link') ) { ?> 
                    <a title="<?php echo esc_attr('instagram', 'apartment-rental-services'); ?>" target="_blank" href="<?php echo esc_url(get_theme_mod('apartment_rental_services_footer_instagram_link')); ?>"><i class="fa-brands fa-instagram"></i></a>
                <?php } ?>
                <?php if ( get_theme_mod('apartment_rental_services_footer_pinterest_link') ) { ?>
                    <a title="<?php echo esc_attr('pinterest', 'apartment-rental-services'); ?>" target="_blank" href="<?php echo esc_url(get_theme_mod('apartment_rental_services_footer_pinterest_link')); ?>"><i class="fa-brands fa-pinterest"></i></a>
                <?php } ?>
                <?php if ( get_theme_mod('apartment_rental_services_footer_twitter_link') ) { ?> 
                    <a title="<?php echo esc_attr('twitter', 'apartment-rental-services'); ?>" target="_blank" href="<?php echo esc_url(get_theme_mod('apartment_rental_services_footer_twitter_link')); ?>"><i class="fa-brands fa-twitter"></i></a>
                <?php } ?>
                <?php if ( get_theme_mod('apartment_rental_services_footer_youtube_link') ) { ?>
                    <a title="<?php echo esc_attr('youtube', 'apartment-rental-services'); ?>" target="_blank" href="<?php echo esc_url(get_theme_mod('apartment_rental_services_footer_youtube_link')); ?>"><i class="fa-brands fa-youtube"></i></a>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
  </div>
</div>

<?php if(get_theme_mod('apartment_rental_services_scroll_hide',true)){ ?>
    <a id="button" class="scroll-btn"><?php echo esc_html( get_theme_mod('apartment_rental_services_scroll_text',__('TOP', 'apartment-rental-services' )) ); ?></a>
<?php } ?>
  
<?php wp_footer(); ?>
</body>
</html>
