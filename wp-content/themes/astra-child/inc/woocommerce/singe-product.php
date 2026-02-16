<?php

/**
 * Adds Gravity form
 */
function maksimer_add_gravity_form() {
	if( function_exists('gravity_form') ) {
		echo '<div class="maksimer_gf_button_wrapper">';
		echo '<a id="maksimer_gf_button" href="#">Spørsmål om produktet?</a>';
		echo  '</div>';

		gravity_form( 1, false, false, false, '', true );
	}
}
add_action('woocommerce_single_product_summary', 'maksimer_add_gravity_form', 70);

function maksimer_grouped_product_column_label($value, $grouped_product_child) {
//	var_dump($value);

	return $value . wc_get_stock_html( $grouped_product_child );
}
add_filter('woocommerce_grouped_product_list_column_label', 'maksimer_grouped_product_column_label', 10, 2);

function maksimer_grouped_product_column_price($value) {
	var_dump($value);

	return $value;
}
//add_filter('woocommerce_grouped_product_list_column_price', 'maksimer_grouped_product_column_price');
