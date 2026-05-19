<?php
require get_template_directory() . '/inc/tgm/class-tgm-plugin-activation.php';
/**
 * Recommended plugins.
 */
function apartment_rental_services_register_recommended_plugins() {
	$plugins = array(
		array(
			'name'             => __( 'Classic Blog Grid', 'apartment-rental-services' ),
			'slug'             => 'classic-blog-grid',
			'source'           => '',
			'required'         => false,
			'force_activation' => false,
		)
	);
	$config = array();
	tgmpa( $plugins, $config );
}
add_action( 'tgmpa_register', 'apartment_rental_services_register_recommended_plugins' );