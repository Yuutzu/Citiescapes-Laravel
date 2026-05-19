<?php
/**
 * Custom template tags for this theme.
 *
 * Eventually, some of the functionality here could be replaced by core features
 *
 * @package Apartment Rental Services
 */

if ( ! function_exists( 'apartment_rental_services_the_attached_image' ) ) :
/**
 * Prints the attached image with a link to the next attached image.
 */
function apartment_rental_services_the_attached_image() {
	$post                = get_post();
	$attachment_size     = apply_filters( 'apartment_rental_services_attachment_size', array( 1200, 1200 ) );
	$next_attachment_url = wp_get_attachment_url();
	$attachment_ids 	 = get_posts( array(
		'post_parent'    => $post->post_parent,
		'fields'         => 'ids',
		'numberposts'    => -1,
		'post_status'    => 'inherit',
		'post_type'      => 'attachment',
		'post_mime_type' => 'image',
		'order'          => 'ASC',
		'orderby'        => 'menu_order ID'
	) );

	wp_reset_postdata();

	// If there is more than 1 attachment in a gallery...
	if ( count( $attachment_ids ) > 1 ) {
		foreach ( $attachment_ids as $attachment_id ) {
			if ( $attachment_id == $post->ID ) {
				$next_id = current( $attachment_ids );
				break;
			}
		}

		// get the URL of the next image attachment...
		if ( $next_id )
			$next_attachment_url = get_attachment_link( $next_id );

		// or get the URL of the first image attachment.
		else
			$next_attachment_url = get_attachment_link( array_shift( $attachment_ids ) );
	}

	printf( '<a href="%1$s" rel="attachment">%2$s</a>',
		esc_url( $next_attachment_url ),
		wp_get_attachment_image( $post->ID, $attachment_size )
	);
}
endif;

/**
 * Returns true if a blog has more than 1 category
 */
function apartment_rental_services_categorized_blog() {
	if ( false === ( $apartment_rental_services_all_the_cool_cats = get_transient( 'apartment_rental_services_all_the_cool_cats' ) ) ) {
		$apartment_rental_services_all_the_cool_cats = get_categories( array(
			'hide_empty' => 1,
		) );

		$apartment_rental_services_all_the_cool_cats = count( $apartment_rental_services_all_the_cool_cats );

		set_transient( 'apartment_rental_services_all_the_cool_cats', $apartment_rental_services_all_the_cool_cats );
	}

	if ( '1' != $apartment_rental_services_all_the_cool_cats ) {
		return true;
	} else {
		return false;
	}
}

if ( ! function_exists( 'apartment_rental_services_the_custom_logo' ) ) :
	/**
	 * Displays the optional custom logo.
	 *
	 * Does nothing if the custom logo is not available.
	 *
	 * @since apartment-rental-services
	 */
	function apartment_rental_services_the_custom_logo() {
		if ( function_exists( 'the_custom_logo' ) ) {
			if( has_custom_logo() ){
				the_custom_logo();
			}else{
				echo "<a href='".esc_url( home_url() )."' rel='home'><img src='".esc_url(get_template_directory_uri())."/images/Logo.png' alt='" . esc_attr__("Apartment Rental Services logo", 'apartment-rental-services') . "'/></a>";
			}
		}
	}
endif;

/**
 * Flush out the transients used in apartment_rental_services_categorized_blog
 */
function apartment_rental_services_category_transient_flusher() {
	// Like, beat it. Dig?
	delete_transient( 'apartment_rental_services_all_the_cool_cats' );
}
add_action( 'edit_category', 'apartment_rental_services_category_transient_flusher' );
add_action( 'save_post',     'apartment_rental_services_category_transient_flusher' );