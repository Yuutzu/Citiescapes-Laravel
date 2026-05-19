<?php
/**
 * @package Apartment Rental Services
 */
?>

<?php
    $apartment_rental_services_post_date = esc_html(get_the_date());
    
    $apartment_rental_services_author_name = esc_html(get_the_author());

    $apartment_rental_services_single_post_show_date     = get_theme_mod('apartment_rental_services_single_post_date', true);
    $apartment_rental_services_single_post_show_comments = get_theme_mod('apartment_rental_services_single_post_comment', true);
    $apartment_rental_services_single_post_show_author   = get_theme_mod('apartment_rental_services_single_post_author', true);
    $apartment_rental_services_single_post_show_time     = get_theme_mod('apartment_rental_services_single_post_time', true);
?>

<article class="wow zoomIn" data-wow-duration="2s" id="post-<?php the_ID(); ?>" <?php post_class('single-post'); ?>>
    
    <?php 
    $apartment_rental_services_designation = get_post_meta($post->ID, 'apartment_rental_services_designation', true);
    
    if ($apartment_rental_services_designation) : ?>
        <p class="serv-content"><?php echo esc_html($apartment_rental_services_designation); ?></p>
    <?php endif; ?>
    <div class="social-icon text-start">
        <?php
        $apartment_rental_services_facebook_link = get_post_meta($post->ID, 'apartment_rental_services_facebook_link', true);
        $apartment_rental_services_twitter_link = get_post_meta($post->ID, 'apartment_rental_services_twitter_link', true);
        $apartment_rental_services_telegram_link = get_post_meta($post->ID, 'apartment_rental_services_telegram_link', true);

        if ($apartment_rental_services_facebook_link || $apartment_rental_services_twitter_link || $apartment_rental_services_telegram_link) :
        ?>
            <div class="meta-fields text-start">
                <?php if ($apartment_rental_services_facebook_link) : ?>
                    <a href="<?php echo esc_url($apartment_rental_services_facebook_link); ?>" target="_blank"><i class="fab fa-facebook"></i></a>
                <?php endif; ?>

                <?php if ($apartment_rental_services_twitter_link) : ?>
                    <a href="<?php echo esc_url($apartment_rental_services_twitter_link); ?>" target="_blank"><i class="fab fa-twitter"></i></a>
                <?php endif; ?>

                <?php if ($apartment_rental_services_telegram_link) : ?>
                    <a href="<?php echo esc_url($apartment_rental_services_telegram_link); ?>" target="_blank"><i class="fab fa-telegram"></i></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php if (has_post_thumbnail() ){ ?>
        <div class="post-thumb">
           <?php the_post_thumbnail(); ?>
        </div>
    <?php } ?>

    <?php if ('post' == get_post_type()) : ?>
        <?php if ( $apartment_rental_services_single_post_show_date || $apartment_rental_services_single_post_show_comments || $apartment_rental_services_single_post_show_author || $apartment_rental_services_single_post_show_time ) : ?>  
            <div class="postmeta">
                <?php if ($apartment_rental_services_single_post_show_date) : ?>
                <div class="post-date">
                    <i class="fas fa-calendar-alt"></i> &nbsp;<?php echo esc_html($apartment_rental_services_post_date); ?>
                </div>
                <?php endif; ?>
                <?php if ($apartment_rental_services_single_post_show_comments) : ?>
                <div class="post-comment">&nbsp; &nbsp;
                    <span><?php echo esc_html(get_theme_mod('apartment_rental_services_single_post_metabox_seperator', '|'));?></span>
                    <i class="fa fa-comment"></i> &nbsp; <?php comments_number(); ?>
                </div>
                <?php endif; ?>
                <?php if ($apartment_rental_services_single_post_show_author) : ?>
                    <div class="post-author">&nbsp; &nbsp;
                        <span><?php echo esc_html(get_theme_mod('apartment_rental_services_single_post_metabox_seperator', '|'));?></span>
                        <i class="fas fa-user"></i> &nbsp; <?php echo esc_html($apartment_rental_services_author_name); ?>
                    </div>
                <?php endif; ?>
                <?php if ($apartment_rental_services_single_post_show_time) : ?>
                    <div class="post-time">&nbsp; &nbsp;
                        <span><?php echo esc_html(get_theme_mod('apartment_rental_services_single_post_metabox_seperator', '|'));?></span>
                        <i class="fas fa-clock"></i> &nbsp; <?php echo get_the_time(); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    <div class="entry-content">
        <?php the_content(); ?>
        <?php
            wp_link_pages( array(
                'before' => '<div class="page-links">' . __( 'Pages:', 'apartment-rental-services' ),
                'after'  => '</div>',
            ) );
        ?>
        <div class="tags"><?php the_tags(); ?></div>
    </div>
    <footer class="entry-meta">
        <?php edit_post_link( __( 'Edit', 'apartment-rental-services' ), '<span class="edit-link">', '</span>' ); ?>
    </footer>
</article>