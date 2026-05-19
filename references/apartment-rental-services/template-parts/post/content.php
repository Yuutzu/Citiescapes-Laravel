<?php
/**
 * @package Apartment Rental Services
 */
?>

<?php
    $apartment_rental_services_post_date = esc_html(get_the_date());
    $apartment_rental_services_year = esc_html(get_the_date('Y'));
    $apartment_rental_services_month = esc_html(get_the_date('m'));

    $apartment_rental_services_author_id = esc_attr(get_the_author_meta('ID'));
    $apartment_rental_services_author_link = esc_url(get_author_posts_url($apartment_rental_services_author_id));
    $apartment_rental_services_author_name = esc_html(get_the_author());

    $apartment_rental_services_blog_post_thumb =  get_theme_mod( 'apartment_rental_services_blog_post_thumb', 1 );

?>

<article class="wow zoomIn" data-wow-duration="2s" id="post-<?php the_ID(); ?>" <?php post_class('blog-post'); ?>>
    <div class="listarticle">
        <?php if ( $apartment_rental_services_blog_post_thumb == 1 ) { ?> 
            <div class="post-thumb">
                <a href="<?php the_permalink(); ?>">
                    <?php 
                    if ( has_post_thumbnail() ) {
                        the_post_thumbnail();
                    } else { 
                    ?>
                        <img src="<?php echo esc_url( get_template_directory_uri() . '/images/slider.png' ); ?>" alt="<?php the_title_attribute(); ?>">
                    <?php 
                    } 
                    ?>
                </a>
            </div>
        <?php } ?>
        <header class="entry-header">
            <h2 class="single_title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
            <?php if ('post' == get_post_type() && ( get_theme_mod('apartment_rental_services_metafields_date', true) || get_theme_mod('apartment_rental_services_metafields_comments', true) || get_theme_mod('apartment_rental_services_metafields_author', true) || get_theme_mod('apartment_rental_services_metafields_time', true))) : ?>
                <div class="postmeta">
                    <?php if (get_theme_mod('apartment_rental_services_metafields_date', true)) : ?>
                        <div class="post-date">
                            <a href="<?php echo esc_url(get_month_link($apartment_rental_services_year, $apartment_rental_services_month)); ?>">
                           <i class="fas fa-calendar-alt"></i> &nbsp;<?php echo esc_html($apartment_rental_services_post_date); ?>
                                <span class="screen-reader-text"><?php echo esc_html($apartment_rental_services_post_date); ?></span>
                            </a>
                        </div>
                    <?php endif; ?>  
                    <?php if (get_theme_mod('apartment_rental_services_metafields_comments', true)) : ?>  
                        <div class="post-comment">&nbsp; &nbsp;
                            <a href="<?php echo esc_url(get_comments_link()); ?>">
                            <span><?php echo esc_html(get_theme_mod('apartment_rental_services_metabox_seperator', '|'));?></span><i class="fa fa-comment"></i> &nbsp; <?php comments_number(); ?>
                                <span class="screen-reader-text"><?php comments_number(); ?></span>
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php if (get_theme_mod('apartment_rental_services_metafields_author', true)) : ?>
                        <div class="post-author">&nbsp; &nbsp;
                            <a href="<?php echo $apartment_rental_services_author_link; ?>">
                            <span><?php echo esc_html(get_theme_mod('apartment_rental_services_metabox_seperator', '|'));?></span><i class="fas fa-user"></i> &nbsp; <?php echo esc_html($apartment_rental_services_author_name); ?>
                                <span class="screen-reader-text"><?php echo esc_html($apartment_rental_services_author_name); ?></span>
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php if (get_theme_mod('apartment_rental_services_metafields_time', true)) : ?>
                        <div class="post-time">&nbsp; &nbsp;
                            <a href="#">
                            <span><?php echo esc_html(get_theme_mod('apartment_rental_services_metabox_seperator', '|'));?></span><i class="fas fa-clock"></i> &nbsp; <?php echo esc_html(get_the_time()); ?>
                                <span class="screen-reader-text"><?php echo esc_html(get_the_time()); ?></span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </header>
        <?php if ( is_search() || !is_single() ) : // Only display Excerpts for Search ?>
        <div class="entry-summary">
           	<?php if(get_theme_mod('apartment_rental_services_blog_post_description_option') == 'Full Content'){ ?>
                <div class="entry-content"><?php
                    $apartment_rental_services_content = get_the_content(); ?>
                    <p><?php echo wpautop($apartment_rental_services_content); ?></p>  
                </div>
             <?php }
            if(get_theme_mod('apartment_rental_services_blog_post_description_option', 'Excerpt Content') == 'Excerpt Content'){ ?>
                <?php if(get_the_excerpt()) { ?>
                    <div class="entry-content"> 
                        <p><?php $apartment_rental_services_excerpt = get_the_excerpt(); echo esc_html($apartment_rental_services_excerpt); ?></p>
                    </div>
                <?php }?>
            <?php }?>           
        </div>
        <?php else : ?>
        <div class="entry-content">
            <?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'apartment-rental-services' ) ); ?>
            <?php
                wp_link_pages( array(
                    'before' => '<div class="page-links">' . __( 'Pages:', 'apartment-rental-services' ),
                    'after'  => '</div>',
                ) );
            ?>
        </div>
        <?php endif; ?>
        <div class="clear"></div>    
    </div>
</article>