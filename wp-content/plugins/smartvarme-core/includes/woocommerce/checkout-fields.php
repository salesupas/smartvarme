<?php
/**
 * Custom Checkout Fields for Smartvarme
 *
 * Registers additional checkout fields for delivery instructions and installation preferences.
 * Uses WooCommerce Additional Checkout Fields API (WooCommerce 8.0+).
 *
 * @package Smartvarme_Core
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Register custom checkout fields
 */
add_action( 'woocommerce_init', function() {
	// Check if Additional Checkout Fields API is available (WC 8.0+)
	if ( ! function_exists( 'woocommerce_register_additional_checkout_field' ) ) {
		return;
	}

	// Register delivery instructions field
	woocommerce_register_additional_checkout_field(
		array(
			'id'            => 'smartvarme/delivery-instructions',
			'label'         => 'Leveringsinstruksjoner (valgfritt)',
			'location'      => 'order',
			'type'          => 'textarea',
			'required'      => false,
			'attributes'    => array(
				'placeholder' => 'F.eks. levering til bakdør, ring for levering',
				'maxlength'   => 500,
			),
		)
	);

	// Register installation preference field
	woocommerce_register_additional_checkout_field(
		array(
			'id'            => 'smartvarme/installation-preference',
			'label'         => 'Installasjonsønske',
			'location'      => 'order',
			'type'          => 'select',
			'required'      => false,
			'options'       => array(
				''                                           => 'Velg...',
				'self_install'                               => 'Jeg installerer selv',
				'contact_for_installation'                   => 'Kontakt meg for å bestille installasjon',
				'order_installation_later'                   => 'Jeg bestiller installasjon senere',
			),
		)
	);
} );

/**
 * Display custom checkout fields in admin order view
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', function( $order ) {
	// Get custom field values
	$delivery_instructions = $order->get_meta( 'smartvarme/delivery-instructions' );
	$installation_preference = $order->get_meta( 'smartvarme/installation-preference' );

	// Display delivery instructions if provided
	if ( ! empty( $delivery_instructions ) ) {
		echo '<div class="address"><p><strong>Leveringsinstruksjoner:</strong><br>';
		echo esc_html( $delivery_instructions );
		echo '</p></div>';
	}

	// Display installation preference if selected
	if ( ! empty( $installation_preference ) ) {
		// Map values to Norwegian labels
		$preference_labels = array(
			'self_install'                  => 'Jeg installerer selv',
			'contact_for_installation'      => 'Kontakt meg for å bestille installasjon',
			'order_installation_later'      => 'Jeg bestiller installasjon senere',
		);

		$preference_label = isset( $preference_labels[ $installation_preference ] )
			? $preference_labels[ $installation_preference ]
			: $installation_preference;

		echo '<div class="address"><p><strong>Installasjonsønske:</strong><br>';
		echo esc_html( $preference_label );
		echo '</p></div>';
	}
}, 10, 1 );

/**
 * Display test mode notice on checkout page
 */
add_action( 'woocommerce_before_checkout_form', function() {
	$dibs_settings = get_option( 'woocommerce_dibs_easy_settings' );
	if ( ! empty( $dibs_settings['testmode'] ) && $dibs_settings['testmode'] === 'yes' ) {
		wc_print_notice(
			'<strong>TESTMODUS:</strong> Betalinger behandles i testmiljø. Bruk testkort: 4111 1111 1111 1111',
			'notice'
		);
	}
} );
