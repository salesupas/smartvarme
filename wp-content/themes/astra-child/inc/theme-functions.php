<?php
function maksimer_wc_format_stock_for_display( $product ) {
	$display      = __( 'In stock', 'woocommerce' );
	$stock_amount = $product->get_stock_quantity();
	$low_stock    = '';

	switch ( get_option( 'woocommerce_stock_format' ) ) {
		case 'low_amount':
			if ( $stock_amount <= wc_get_low_stock_amount( $product ) ) {
				/* translators: %s: stock amount */
				$display = sprintf( __( '%s igjen på lager', 'woocommerce' ), wc_format_stock_quantity_for_display( $stock_amount, $product ) );
				$low_stock = 'low-stock';
			}
			break;
		case '':
			/* translators: %s: stock amount */
			$display = sprintf( __( '%s in stock', 'woocommerce' ), wc_format_stock_quantity_for_display( $stock_amount, $product ) );
			break;
	}

	return [
		$display,
		$low_stock
	];
}

/**
 * Add checkbox to 'Advanced' product tab
 * Save checkbox value
 */

add_action( 'woocommerce_product_options_advanced', 'maksimer_custom_fields' );

function maksimer_custom_fields() {
	$product = wc_get_product( get_the_ID() );
	?>
	<div class="options_group">
		<?php
		woocommerce_wp_checkbox(
			array(
				'id'          => 'pipe_link_enable',
				'value'       => get_post_meta( $product->get_id(), 'pipe_link_enable', true ),
				'label'       => __( 'Show link to pipe calculator', 'woocommerce' ),
				'desc_tip'    => true,
				'description' => __( 'A button on the product is displayed, which includes link to the pipe calculator.', 'woocommerce' ),
				'type'        => 'checkbox',
			)
		);
		?>
	</div>
	<?php
}

add_action( 'woocommerce_process_product_meta', 'maksimer_save_custom_product_fields' );

function maksimer_save_custom_product_fields( $post_id ) {
	$show_calc = isset( $_POST['pipe_link_enable'] ) ? $_POST['pipe_link_enable'] : 'no';
	update_post_meta( $post_id, 'pipe_link_enable', $show_calc );
}

/**
 * Show link to pipe calculator
 */

add_action( 'woocommerce_product_meta_end', 'maksimer_pipe_link' );

function maksimer_pipe_link() {
	global $product;
	$id = $product->get_id();

	$show_button = get_post_meta( $id, 'pipe_link_enable', true );

	//if ( wc_string_to_bool( $show_button ) ) {
	//	echo '<a class="open_pipe_calculator_button button alt" href="' . get_home_url() . '/pipe-kalkulator/?fireplace=' . $id . '">Trenger du stålpipe til denne peisen? <b>Klikk her for å gå til vår pipekalkulator<b></a>';
	//}
echo '<a class="open_pipe_calculator_button button alt" href="https://pipekalkulator.smartvarme.no/?fireplace=' . $id . '">Trenger du stålpipe til denne peisen? <b>Klikk her for å gå til vår pipekalkulator</b></a>';


}

/* Allow Shop Managers to manage_options */
function maksimer_add_shop_manager_user_editing_capability() {
	$shop_manager = get_role( 'shop_manager' );
	$shop_manager->add_cap( 'unfiltered_html' );
}

add_action( 'admin_init', 'maksimer_add_shop_manager_user_editing_capability' );

function maksimer_shop_loop_show_stock() {
	global $product;
	echo wc_get_stock_html( $product );
}
add_action( 'astra_woo_shop_title_after', 'maksimer_shop_loop_show_stock', 10 );
