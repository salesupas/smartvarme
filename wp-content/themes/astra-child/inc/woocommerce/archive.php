<?php

/**
 * Print additional information beneath pagination on category pages
 */
function maksimer_print_additional_info() {
	if ( ! function_exists( 'get_field' ) ) {
		return;
	}

	$term = get_queried_object();

	if ( ! is_a( $term, 'WP_Term' ) ) {
		return;
	}
	$additional_info = get_field( 'additional_information', $term );

	if ( $additional_info ) {
		echo $additional_info;
	}
}

add_action( 'woocommerce_after_shop_loop', 'maksimer_print_additional_info', 20 );

/**
 * Remove sidebar on level 1 product categories
 *
 * @param $layout
 *
 * @return mixed|string
 */
function maksimer_hide_sidebar_on_level_1_categories( $layout ) {
	if ( ! ( is_woocommerce() && is_product_taxonomy() ) ) {
		return $layout;
	}
	$current_term = get_queried_object();

	if ( $current_term && $current_term->parent !== 0 ) {
		return $layout;
	}

	return 'no-sidebar';
}

/**
 * Load filters and actions only on woocommerce pages
 *
 * @return void
 */
function maksimer_modify_woocommerce_archive() {
	add_filter( 'astra_page_layout', 'maksimer_hide_sidebar_on_level_1_categories' );
}

add_action( 'woocommerce_before_main_content', 'maksimer_modify_woocommerce_archive');



function maksimer_resize_cat_thumb() {
	return 'full';
}

add_filter('subcategory_archive_thumbnail_size', 'maksimer_resize_cat_thumb');

/**
 * Adds the option to hide products onbackorder from the shortcode
 *
 * @param array $query_args
 * @param array $attributes
 * @param string $type
 *
 * @return array
 */
function maksimer_hide_backorder_products_from_shortcode( $query_args, $attributes, $type ) {
	if ( 'hide-onbackorder' === $attributes['class'] ) {
		$query_args['meta_query'] = array(
			array(
				'key'     => '_stock_status',
				'value'   => 'onbackorder',
				'compare' => 'NOT LIKE',
			),
		);
	}

	return $query_args;
}

add_filter( 'woocommerce_shortcode_products_query', 'maksimer_hide_backorder_products_from_shortcode', 10, 3 );

/**
 * @param object $result
 * @param WC_Shortcode_Products $shortcode
 *
 * @return object
 */
function maksimer_hide_bundle_products( $result, $shortcode ) {
	if ( $shortcode->get_attributes()[ 'class' ] === 'hide-onbackorder' ) {
		$ids = $result->ids;

		if ( ! empty( $ids ) ) {
			foreach ( $ids as $key => $id ) {
				$product = wc_get_product( $id );

				/**
				 * @var WC_Product_Bundle $product
				 */
				if ( $product->is_type( 'bundle' ) ) {
					$bundled_products = $product->get_bundled_items();

					if ( ! empty( $bundled_products ) ) {
						/**
						 * @var WC_Bundled_Item $bundled_product
						 */
						foreach ( $bundled_products as $bundled_product ) {
							// Remove all bundle products that have children that are on backorder
							if ($bundled_product->is_on_backorder() ) {
								unset( $result->ids[ $key ] );
								break;
							}
						}
					}
				}
			}

			// Update total amount of products after removing
			$result->total = count( $result->ids );
		}
	}

	return $result;
}

add_filter('woocommerce_shortcode_products_query_results', 'maksimer_hide_bundle_products', 10, 2);
