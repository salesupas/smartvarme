<?php
/**
 * Stock and Delivery Time Display
 *
 * Template part for displaying custom stock status and delivery time on single product pages.
 *
 * @package Smartvarme_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if ( ! $product ) {
	return;
}

// Get custom meta fields
$delivery_time = get_post_meta( $product->get_id(), '_delivery_time', true );
$stock_display_override = get_post_meta( $product->get_id(), '_stock_display_override', true );

// Get stock status
$stock_status = $product->get_stock_status();

// Map stock status to Norwegian text
$stock_text = '';
$stock_class = '';

if ( ! empty( $stock_display_override ) ) {
	// Use custom override text if set
	$stock_text = $stock_display_override;
	$stock_class = 'custom';
} else {
	// Default stock status text
	switch ( $stock_status ) {
		case 'instock':
			$stock_text = 'På lager';
			$stock_class = 'in-stock';
			break;
		case 'outofstock':
			$stock_text = 'Ikke på lager';
			$stock_class = 'out-of-stock';
			break;
		case 'onbackorder':
			$stock_text = 'På bestilling';
			$stock_class = 'on-backorder';
			break;
		default:
			$stock_text = 'Kontakt oss';
			$stock_class = 'unknown';
			break;
	}
}
?>

<div class="smartvarme-stock-delivery">
	<div class="stock-status <?php echo esc_attr( $stock_class ); ?>">
		<span class="stock-icon">●</span>
		<span class="stock-text"><?php echo esc_html( $stock_text ); ?></span>
	</div>

	<?php if ( ! empty( $delivery_time ) ) : ?>
		<div class="delivery-time">
			<span class="delivery-icon">🚚</span>
			<span class="delivery-text">Leveringstid: <?php echo esc_html( $delivery_time ); ?></span>
		</div>
	<?php endif; ?>
</div>
