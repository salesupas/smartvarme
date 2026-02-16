<?php

/**
 * Template for added to cart popup
 */


if ( ! $_POST['productID'] ) {
	return;
}

$added_product = wc_get_product( $_POST['productID'] );
?>

<div id="added-to-cart-popup" class="product-added-to-cart-popup popup open">
	<div class="container">
		<div class="wrapper">
			<div class="inner">
				<div class="product-added-info">
					<a href="#" class="close-popup">
						<img src="<?php echo get_stylesheet_directory_uri() . '/assets/icons/close-popup.svg'; ?>"/>
					</a>
					<div class="product-added-actions">
						<span><?php echo __( 'Lagt i handlekurv', 'maksimer-lang' ); ?></span>
						<a class="go-to-checkout" href="<?php echo wc_get_checkout_url(); ?>"><?php echo __( 'Til kassen', 'maksimer-lang' ); ?></a>
						<a href="#added-to-cart-popup" class="toggle-popup-window continue-shopping"><?php _e( 'Fortsett å handle', 'maksimer-lang' ); ?></a>
					</div>
					<div class="added-info">
						<div class="added-info-wrapper">
							<h4><?php echo $added_product->get_title(); ?></h4>
							<div class="image">
								<?php echo $added_product->get_image(); ?>
							</div>
							<div class="price">
								<?php echo $added_product->get_price_html(); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="bg"></div>
</div>
