<?php
/**
 * Email Customization for Smartvarme
 *
 * Customizes WooCommerce transactional emails with Norwegian subjects,
 * delivery information, and branded footer text.
 *
 * @package Smartvarme_Core
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Customize email subject for processing order
 *
 * @param string   $subject Email subject.
 * @param WC_Order $order Order object.
 * @return string Modified subject.
 */
add_filter( 'woocommerce_email_subject_customer_processing_order', function( $subject, $order ) {
	if ( ! $order ) {
		return $subject;
	}
	return sprintf( 'Takk for din ordre #%s hos Smartvarme', $order->get_order_number() );
}, 10, 2 );

/**
 * Customize email subject for completed order
 *
 * @param string   $subject Email subject.
 * @param WC_Order $order Order object.
 * @return string Modified subject.
 */
add_filter( 'woocommerce_email_subject_customer_completed_order', function( $subject, $order ) {
	if ( ! $order ) {
		return $subject;
	}
	return sprintf( 'Din ordre #%s er behandlet og sendt - Smartvarme', $order->get_order_number() );
}, 10, 2 );

/**
 * Add delivery information after order table in customer emails
 *
 * @param WC_Order $order Order object.
 * @param bool     $sent_to_admin Whether the email is being sent to admin.
 * @param bool     $plain_text Whether it's a plain text email.
 * @param WC_Email $email Email object.
 */
add_action( 'woocommerce_email_after_order_table', function( $order, $sent_to_admin, $plain_text, $email ) {
	// Only show for customer emails (not admin)
	if ( $sent_to_admin ) {
		return;
	}

	// Only show for processing and completed order emails
	if ( ! in_array( $email->id, array( 'customer_processing_order', 'customer_completed_order' ), true ) ) {
		return;
	}

	// Get custom field values
	$delivery_instructions = $order->get_meta( 'smartvarme/delivery-instructions' );
	$installation_preference = $order->get_meta( 'smartvarme/installation-preference' );

	// Get shipping method
	$shipping_method = '';
	$shipping_items = $order->get_shipping_methods();
	if ( ! empty( $shipping_items ) ) {
		$shipping_item = reset( $shipping_items );
		$shipping_method = $shipping_item->get_name();
	}

	// Map installation preference to Norwegian labels
	$preference_labels = array(
		'self_install'                  => 'Jeg installerer selv',
		'contact_for_installation'      => 'Kontakt meg for å bestille installasjon',
		'order_installation_later'      => 'Jeg bestiller installasjon senere',
	);

	$preference_label = isset( $preference_labels[ $installation_preference ] )
		? $preference_labels[ $installation_preference ]
		: '';

	// Output delivery information section
	if ( $plain_text ) {
		// Plain text format
		echo "\n\n";
		echo "========================================\n";
		echo "LEVERINGSINFORMASJON\n";
		echo "========================================\n\n";
		echo "Forventet leveringstid: 2-5 virkedager\n\n";

		if ( ! empty( $shipping_method ) ) {
			echo "Fraktmetode: " . $shipping_method . "\n\n";
		}

		if ( ! empty( $delivery_instructions ) ) {
			echo "Dine leveringsinstruksjoner:\n";
			echo $delivery_instructions . "\n\n";
		}

		if ( ! empty( $preference_label ) ) {
			echo "Installasjonsønske: " . $preference_label . "\n\n";
		}

		echo "========================================\n\n";
	} else {
		// HTML format
		?>
		<div style="margin: 30px 0; padding: 20px; background-color: #f7f7f7; border-left: 4px solid #1e3a8a;">
			<h2 style="margin-top: 0; color: #1e3a8a;">Leveringsinformasjon</h2>

			<p style="margin: 10px 0;">
				<strong>Forventet leveringstid:</strong> 2-5 virkedager
			</p>

			<?php if ( ! empty( $shipping_method ) ) : ?>
				<p style="margin: 10px 0;">
					<strong>Fraktmetode:</strong> <?php echo esc_html( $shipping_method ); ?>
				</p>
			<?php endif; ?>

			<?php if ( ! empty( $delivery_instructions ) ) : ?>
				<p style="margin: 10px 0;">
					<strong>Dine leveringsinstruksjoner:</strong><br>
					<?php echo nl2br( esc_html( $delivery_instructions ) ); ?>
				</p>
			<?php endif; ?>

			<?php if ( ! empty( $preference_label ) ) : ?>
				<p style="margin: 10px 0;">
					<strong>Installasjonsønske:</strong> <?php echo esc_html( $preference_label ); ?>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}
}, 10, 4 );

/**
 * Customize email footer text
 *
 * @param string $footer_text Default footer text.
 * @return string Modified footer text.
 */
add_filter( 'woocommerce_email_footer_text', function( $footer_text ) {
	return 'Takk for at du handler hos Smartvarme - din partner for energieffektive varmeløsninger.';
} );
