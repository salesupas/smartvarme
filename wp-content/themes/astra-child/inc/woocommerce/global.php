<?php

/**
 * Modify stock status
 *
 * @param $markup
 * @param $product
 *
 * @return false|mixed|string
 */
function maksimer_astra_woo_product_in_stock( $markup, $product ) {
	if ( $product ) {
		$product_avail     = $product->get_availability();
		$stock_quantity    = $product->get_stock_quantity();
		$availability      = $product_avail['availability'];
		$avail_class       = $product_avail['class'];
		$minimum_stock     = floatval( $product->get_meta( 'maco_minimumstock' ) );
		$maco_deliverydays = floatval( $product->get_meta( 'maco_deliverydays' ) );

		ob_start(); ?>
		<p class="ast-stock-detail">
			<?php
			
			echo do_shortcode('[unimicro_delivery_days]');

			/*
			if ( $product->managing_stock() && $stock_quantity <= 0 && 'available-on-backorder' === $avail_class ) :
				if ( $maco_deliverydays > 0 ) : ?>
					<span class="stock <?php echo esc_html( $avail_class ); ?>">
					<?php echo __( 'Bestillingsvare, ta kontakt for leveringstid', 'maksimer-lang' ) ?>
					(<a href="mailto:<?php echo __( 'ordre@smartvarme.no', 'maksimer-lang' ) ?>"><?php echo __( 'ordre@smartvarme.no', 'maksimer-lang' ) ?></a>)
				</span>
				<?php
				elseif ( $minimum_stock > 0 ) : ?>
					<span class="stock <?php echo esc_html( $avail_class ); ?>">
					<?php echo __( 'Bestillingsvare, 1-2 uker leveringstid.', 'maksimer-lang' ); ?>
				</span>
				<?php
				elseif ( $stock_quantity <= 0 ) : ?>
					<span class="stock <?php echo esc_html( $avail_class ); ?>">
					<?php echo __( 'Bestillingsvare', 'maksimer-lang' ); ?>
				</span>
				<?php
				endif;
			else:

				if ( ! $product->is_in_stock() ) {
					$availability = [ __( 'Bestillingsvare', 'woocommerce' ) ];
				} elseif ( $product->managing_stock() ) {
					$availability = maksimer_wc_format_stock_for_display( $product );
				} else {
					$availability = [ '' ];
				}

				?>
				<span class="stock <?php echo esc_html( $avail_class ) . ' ';
				echo $availability[1] ?? '' ?>">

				<?php echo $availability[0] ?>
			</span>
				<?php

				if ( ! is_product() & !is_admin()) {
					display_energi_icon();
				}
			endif;
			*/
			?>
		</p>
		<?php
		$markup = ob_get_clean();
	}

	return $markup;
}

add_filter( 'woocommerce_get_stock_html', 'maksimer_astra_woo_product_in_stock', 15, 2 );




 
function add_discount_campaigning_show() {
    echo do_shortcode('[unimicro_discount_campaigning_show]');
} 
add_action('woocommerce_before_add_to_cart_form', 'add_discount_campaigning_show', 15);
add_action('woocommerce_after_shop_loop_item', 'add_discount_campaigning_show', 15);








/**
 * Remove GTM Meta Data from wishlist table on My Account page
 *
 * @param $item_data
 *
 * @return array|mixed
 */
function maksimer_remove_gtm_meta_data( $item_data ) {
	if ( ! is_array( $item_data ) || ! count( $item_data ) > 0 ) {
		return $item_data;
	}

	//keys to exclude
	$gtm_keys = [ 'gtm4wp_id', 'gtm4wp_name', 'gtm4wp_sku', 'gtm4wp_category', 'gtm4wp_price' ];

	foreach ( $item_data as $key => $data ) {
		if ( in_array( $key, $gtm_keys ) ) {
			unset( $item_data[ $key ] );
		}
	}

	return $item_data;
}

add_filter( 'tinvwl_wishlist_item_meta_post', 'maksimer_remove_gtm_meta_data' );


add_action( 'after_setup_theme', 'remove_breadcrumbs_from_yoast_woocommerce' );

function remove_breadcrumbs_from_yoast_woocommerce() {
	global $yoast_woo_seo;

	//remove breadcrumbs set from YOAST woocommerce addon
	if ( isset( $yoast_woo_seo ) ) {
		remove_action( 'send_headers', array( $yoast_woo_seo, 'handle_breadcrumbs_replacements' ) );
	}
}

/**
 * Hiding uncategorized and utleie category from shop
 *
 * @param $terms
 * @param $taxonomies
 * @param $args
 *
 * @return array|mixed
 */
function maksimer_hide_terms( $terms, $taxonomies, $args ) {
	if ( in_array( 'product_cat', $taxonomies ) && ! is_admin() ) {
		foreach ( $terms as $key => $term ) {
			// If current terms are not instance of WP_Term then break loop
			if ( ! ( $term instanceof WP_Term ) ) {
				break;
			}

			// Unset categories with provided slug in array
			if ( in_array( $term->slug, [ 'uncategorized', 'utleie' ] ) ) {
				unset( $terms[ $key ] );
			}
		}
	}

	return $terms;
}

add_filter( 'get_terms', 'maksimer_hide_terms', 10, 3 );

function maksimer_display_price_filter() {
	if ( is_shop() || is_product_taxonomy() ) {
		$query_obj_id = get_queried_object_id();
		$term         = get_term_by( 'term_id', $query_obj_id, 'product_cat', 'ARRAY_A' );

		if ( in_array( $term['slug'], [ 'peis', 'peisinnsatser' ] ) ) {
			add_filter( 'body_class', function ( $classes ) {
				$classes[] = 'maksimer-show-stock-filter';

				return $classes;
			} );

			return;
		}

		if ( $term['parent'] > 0 ) {
			while ( $term['parent'] > 0 ) {
				$term = get_term_by( 'term_id', $term['parent'], 'product_cat', 'ARRAY_A' );
				if ( in_array( $term['slug'], [ 'peis', 'peisinnsatser' ] ) ) {
					add_filter( 'body_class', function ( $classes ) {
						$classes[] = 'maksimer-show-stock-filter';

						return $classes;
					} );

					return;
				}
			}
		}
	}
}

add_action( 'wp', 'maksimer_display_price_filter' );

if ( function_exists( 'acf_add_options_page' ) ) {
	$parent = acf_add_options_page(
		array(
			'page_title' => __( 'Options', 'maksimer-lang' ),
			'menu_title' => __( 'Options', 'maksimer-lang' ),
			'menu_slug'  => 'maksimer-options-settings',
			'capability' => 'edit_posts',
			'redirect'   => false,
			'icon_url'   => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE5LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJtYWtzaW1lciIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiDQoJIHdpZHRoPSI0NTAuNzA5cHgiIGhlaWdodD0iNDczLjM4NnB4IiB2aWV3Qm94PSIwIDAgNDUwLjcwOSA0NzMuMzg2IiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA0NTAuNzA5IDQ3My4zODY7Ig0KCSB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxwYXRoIGlkPSJtYWtzaW1lcl8xXyIgc3R5bGU9ImZpbGw6IzlFQTNBODsiIGQ9Ik0yMzIuNTksMjE1LjU5NGw4OC4wNzUtMTcxLjI1MmMwLjM4OC0wLjk2OSwwLjkyOC0xLjgyNiwxLjUyMy0yLjY1Ng0KCWMyLjE0My0zLjU5Nyw2LjA0NS02LjAwNCwxMC41NDItNi4wMDRoNjkuODk1YzYuODA3LDAsMTIuMzQxLDUuNTA1LDEyLjM0MSwxMi4zMTN2Mzc3LjM5NWMwLDYuODA2LTUuNTM0LDEyLjMxMy0xMi4zNDEsMTIuMzEzDQoJaC02OS44OTVjLTYuODA3LDAtMTIuMzI3LTUuNTA3LTEyLjMyNy0xMi4zMTNWMjczLjQ4YzAtMS45MDgtMS41MzYtMy40MzEtMy40MzEtMy40MzFoLTAuOTk2Yy0xLjI0NSwwLTIuMzM4LDAuNjY0LTIuOTMzLDEuNjYNCgljLTAuMTUzLDAuMTk0LTAuMjY0LDAuNDE1LTAuMzYsMC42MDlsLTM4LjQ3Nyw3NC42ODJjLTUuNzgzLDEyLjAwOC0xNC43NzUsMTguMTUxLTIxLjg4NywxOC4xNTFIMTk4LjM5DQoJYy03LjEyNSwwLTE0LjMyLTMuOTAyLTIxLjE0LTE2LjY4NWwtMzkuMjM2LTc2LjE0OWMtMC4wOTgtMC4xOTQtMC4yMDgtMC40MTUtMC4zNDYtMC42MDljLTAuNTk1LTAuOTk2LTEuNjc1LTEuNjYtMi45MzQtMS42Ng0KCWgtMC45OTZjLTEuODk2LDAtMy40MzEsMS41MjMtMy40MzEsMy40MzF2MTUxLjkxYzAsNi44MDYtNS41MiwxMi4zMTMtMTIuMzQxLDEyLjMxM0g0OC4wN2MtNi44MDYsMC0xMi4zMjctNS41MDctMTIuMzI3LTEyLjMxMw0KCVY0Ny45OTVjMC02LjgwNyw1LjUyMS0xMi4zMTMsMTIuMzI3LTEyLjMxM2g2OS44OTVjNC41MTEsMCw4LjM5OCwyLjQwNywxMC41NTYsNi4wMDRjMC41OTUsMC44MywxLjEyMSwxLjY4NywxLjUwOSwyLjY1Ng0KCWw4OC4wODksMTcxLjI1MkMyMjIuMTMxLDIyMS45MywyMjkuMDQ4LDIyMS45MywyMzIuNTksMjE1LjU5NHoiLz4NCjwvc3ZnPg0K',
		)
	);
	$child  = acf_add_options_sub_page(
		array(
			'page_title'  => __( 'Energi Settings', 'maksimer-lang' ),
			'menu_title'  => __( 'Energi', 'maksimer-lang' ),
			'parent_slug' => $parent['menu_slug'],
		)
	);
}
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command(
		'populate_repeater_field',
		function () {
			$values    = [
				'A-Left-DarkGreen',
				'A-Left-LightGreen',
				'A-Left-MediumGreen',
				'A-Left-Yellow',
				'A+-Left-DarkGreen',
				'A+-Left-MediumGreen',
				'A+-Leftt-LightGreen',
				'A++-Left-DarkGreen',
				'A++-Left-MediumGreen',
				'A+++-Left-DarkGreen',
				'B-Left-LightGreen',
				'B-Left-LightOrange',
				'B-Left-MediumGreen',
				'B-Left-Yellow',
				'C-Left-DarkOrange',
				'C-Left-LightGreen',
				'C-Left-DarkOrange',
				'C-Left-LightGreen',
				'C-Left-LightOrange',
				'C-Left-Yellow',
				'D-Left-DarkOrange',
				'D-Left-LightOrange',
				'D-Left-Red',
				'D-Left-Yellow',
				'E-Left-DarkOrange',
				'E-Left-LightOrange',
				'E-Left-Red',
				'F-Left-DarkOrange',
				'F-Left-Red',
				'G-Left-Red',
			];
			$new_texts = array();
			foreach ( $values as $value ) {
				$new_text    = array(
					'field_656e16c8bff8b' => $value,
				);
				$new_texts[] = $new_text;
			}
			update_field( 'field_656e16b1bff8a', $new_texts, 'option' );
			WP_CLI::success( 'Repeater field populated successfully.' );
		}
	);
}
function acf_load_energi_selection_field_choices( $field ) {
	// Reset choices
	$field['choices'] = array();
	$field['choices']['0'] = 'Ingen ikon';

	// Check to see if Repeater has rows of data to loop over
	if ( have_rows( 'energi_efficiency_options', 'option' ) ) {
		// Execute repeatedly as long as the below statement is true
		while ( have_rows( 'energi_efficiency_options', 'option' ) ) {
			// Return an array with all values after the loop is complete
			the_row();
			// Variables
			$value = get_sub_field( 'text' );
			$label = get_sub_field( 'text' );
			// Append to choices
			$field['choices'][ $value ] = $label;
		}
	}

	// Return the field
	return $field;
}

add_filter( 'acf/load_field/name=energi_selection', 'acf_load_energi_selection_field_choices' );
function display_energi_icon() {
	global $product;
	$selection       = get_field( 'energi_selection', $product->get_id() );
	$options         = get_field( 'energi_efficiency_options', 'option' );
	$produktdatablad = get_field( 'produktdatablad', $product->get_id() );
	$energi_link     = get_field( 'energi_link', $product->get_id() );
	if ( $selection && $options ) :
		$layout = '';
		$layout .= '<div class="energi_wrapper">';
		foreach ( $options as $value ) {
			if ( $value['text'] === $selection ) {
				if ( $energi_link ) {
					$layout .= '<a target="_blank" href="' . $energi_link . '"><img loading="lazy" width="69" height="43" src="' . $value['icon'] . '" alt="' . $value['text'] . '" class="icon icon--left" /></a>';
				} else {
					$layout .= '<img loading="lazy" width="69" height="43" src="' . $value['icon'] . '" alt="' . $value['text'] . '" class="icon icon--left" />';
				}
				if ( is_product() && $produktdatablad ) {
					$layout .= '<a target="_blank" href="' . $produktdatablad . '">' . __( 'Produktdatablad', 'ledpro-common' ) . '</a>';
				}
			}
		}
		$layout .= '</div>';
		echo $layout;
	endif;
}

add_action( 'woocommerce_before_add_to_cart_button', 'display_energi_icon' );

/**
 * Getting template of added to cart popup on product page
 */
function maksimer_woocommerce_after_added_to_cart() {
	if ( isset( $_POST['add-to-cart'] ) ) {
		get_template_part( 'inc/woocommerce/popup-added-to-cart' );
	}
}

add_action( 'wp_footer', 'maksimer_woocommerce_after_added_to_cart', 10, 3 );

/**
 * Displaying added to cart popup on product loop ( with Ajax )
 */
function maksimer_loop_added_to_cart_popup() {
	$added_product = wc_get_product( $_POST['productID'] );
	$response      = '';
	if ( $added_product ) :
		ob_start();
		get_template_part( 'inc/woocommerce/popup-added-to-cart' );
		$response = ob_get_clean();
	endif;
	echo $response;

	die();
}

add_action( 'wp_ajax_maksimer_loop_added_to_cart_popup', 'maksimer_loop_added_to_cart_popup' );
add_action( 'wp_ajax_nopriv_maksimer_loop_added_to_cart_popup', 'maksimer_loop_added_to_cart_popup' );

function get_on_sale_product_ids() {
	global $wpdb;

	$product_ids = $wpdb->get_col(
		"
        SELECT ID FROM $wpdb->posts
        WHERE post_type = 'product'
        AND post_status = 'publish'
    "
	);

	$product_id_list = [];
	foreach ( $product_ids as $product_id ) {
		$product = wc_get_product( $product_id );

		if ( $product && $product->is_on_sale() ) {
			$product_id_list[] = $product_id;
		}
	}

	return $product_id_list;
}

function fetch_onsale_products() {
	$product_ids_on_sale = get_on_sale_product_ids();

	$args = array(
		'post_type'      => 'product',
		'post__in'       => $product_ids_on_sale,
		'posts_per_page' => - 1,
		'post_status'    => 'publish',
		'tax_query'      => array(
			array(
				'taxonomy' => 'product_visibility',
				'terms'    => array( 'exclude-from-catalog' ),
				'field'    => 'name',
				'operator' => 'NOT IN',
			),
		),
	);

	$loop = new WP_Query( $args );

	ob_start();

	if ( $loop->have_posts() ) {
		echo '<div class="woocommerce">';
		woocommerce_product_loop_start();
		while ( $loop->have_posts() ) :
			$loop->the_post();
			wc_get_template_part( 'content', 'product' );
		endwhile;
		woocommerce_product_loop_end();
		echo '</div>';
	} else {
		echo __( 'No products found' );
	}

	wp_reset_query();

	return ob_get_clean();
}

add_shortcode( 'maksimer_onsale_products', 'fetch_onsale_products' );
