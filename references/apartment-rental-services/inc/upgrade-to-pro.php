<?php
/**
 * Upgrade to pro options
 */
function apartment_rental_services_upgrade_pro_options( $wp_customize ) {

	$wp_customize->add_section(
		'upgrade_premium',
		array(
			'title'    => esc_html__( 'About Apartment Rental Services', 'apartment-rental-services' ),
			'priority' => 1,
		)
	);

	class Apartment_Rental_Services_Pro_Button_Customize_Control extends WP_Customize_Control {
		public $type = 'upgrade_premium';

		function render_content() {
			?>
			<div class="pro_info">
				<ul>
				
					<li><a class="upgrade-to-pro  pro-btn" href="<?php echo esc_url( APARTMENT_RENTAL_SERVICES_PREMIUM_PAGE ); ?>" target="_blank"><i class="dashicons dashicons-cart"></i><?php esc_html_e( 'Upgrade Pro', 'apartment-rental-services' ); ?> </a></li>

					<li><a class="upgrade-to-pro" href="<?php echo esc_url( APARTMENT_RENTAL_SERVICES_PRO_DEMO ); ?>" target="_blank"><i class="dashicons dashicons-awards"></i><?php esc_html_e( 'Premium Demo', 'apartment-rental-services' ); ?> </a></li>

					<li><a class="upgrade-to-pro" href="<?php echo esc_url( APARTMENT_RENTAL_SERVICES_REVIEW ); ?>" target="_blank"><i class="dashicons dashicons-star-filled"></i><?php esc_html_e( 'Rate Us', 'apartment-rental-services' ); ?> </a></li>

					<li><a class="upgrade-to-pro" href="<?php echo esc_url( APARTMENT_RENTAL_SERVICES_SUPPORT ); ?>" target="_blank"><i class="dashicons dashicons-lightbulb"></i><?php esc_html_e( 'Support Forum', 'apartment-rental-services' ); ?> </a></li>

					<li><a class="upgrade-to-pro" href="<?php echo esc_url(APARTMENT_RENTAL_SERVICES_THEME_PAGE ); ?>" target="_blank"><i class="dashicons dashicons-admin-appearance"></i><?php esc_html_e( 'Theme Page', 'apartment-rental-services' ); ?> </a></li>

					<li><a class="upgrade-to-pro" href="<?php echo esc_url( APARTMENT_RENTAL_SERVICES_THEME_DOCUMENTATION ); ?>" target="_blank"><i class="dashicons dashicons-visibility"></i><?php esc_html_e( 'Theme Documentation', 'apartment-rental-services' ); ?> </a></li>

				</ul>
			</div>
			<?php
		}
	}

	$wp_customize->add_setting(
		'pro_info_buttons',
		array(
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'apartment_rental_services_sanitize_text',
		)
	);

	$wp_customize->add_control(
		new Apartment_Rental_Services_Pro_Button_Customize_Control(
			$wp_customize,
			'pro_info_buttons',
			array(
				'section' => 'upgrade_premium',
			)
		)
	);
}
add_action( 'customize_register', 'apartment_rental_services_upgrade_pro_options' );
