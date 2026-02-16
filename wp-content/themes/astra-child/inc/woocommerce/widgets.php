<?php

/**
 * Class Maksimer_Login_myaccount Widget
 */
class Mks_Filter_Products_By_Stock extends WP_Widget {
	function __construct() {
		parent::__construct(
			'mks_filter_products_by_stock', // Base ID
			__( 'Maksimer Filter products by stock', 'thaugland-lang' ) // Name
		);
	}


	public function widget( $args, $instance ) {
		$link_text = __( 'Vis kun på lager', 'maksimer-lang' );

		echo '<div class="maksimer-filter-by-stock widget woocommerce widget_layered_nav woocommerce-widget-layered-nav">';
		echo '<h2 class="widget-title">' . $instance['filter_stock_title'] . '</h2>';
		echo '<div class="widget-link-wrapper">';
		echo '<a class="maksimer-filter-products-by-stock" href="#">' . $link_text . '</a>';
		echo '</div>';
		echo '</div>';
	}


	public function form( $instance ) {
		$filter_stock_title = $instance['filter_stock_title'] ?? __( 'Lagerstatus', 'maksimer-lang' );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'filter_stock_title' ); ?>">
				<?php _e( 'Filter products by stock status' ); ?>
			</label>
			<input class="widefat"
			       id="<?php echo $this->get_field_id( 'filter_stock_title' ); ?>"
			       name="<?php echo $this->get_field_name( 'filter_stock_title' ); ?>"
			       type="text"
			       value="<?php echo esc_attr( $filter_stock_title ); ?>"
			/>
		</p>
		<?php
	}


	public function update( $new_instance, $old_instance ) {
		$instance                       = [];
		$instance['filter_stock_title'] = ( ! empty( $new_instance['filter_stock_title'] ) ) ? strip_tags( $new_instance['filter_stock_title'] ) : '';

		return $instance;
	}
}

/**
 * Register and load the widget
 */
function maksimer_register_widgets() {
	register_widget( 'mks_filter_products_by_stock' );
}

add_action( 'widgets_init', 'maksimer_register_widgets' );


function maksimer_filter_products_by_stock() {
	$is_request_okay = check_ajax_referer( 'maksimer_ajax_nonce', 'wp_nonce' );
	if ( ! $is_request_okay ) {
		wp_send_json_error( 'Bad request', 400 );
	}
	$category_id = $_POST['page_id'] ?? 0;
	$query_args  = $_POST['query_arg'] ?? '';


	if ( $category_id === 0 ) {
		wp_send_json_error( 'Invalid cat ID', 401 );
	}

	$term = get_term_by( 'id', $category_id, 'product_cat', 'ARRAY_A' );
	if ( ! $term ) {
		wp_send_json_error( 'Invalid Term', 402 );
	}

	$query_args =
			array(
				'taxonomy' => 'pa_merke',
				'field'    => 'slug',
				'terms'    => $query_args,
				'operator' => 'IN',
			);

	$ordering_args = WC()->query->get_catalog_ordering_args();


	$args = array(
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'ignore_sticky_posts' => 1,
		'orderby'             => $ordering_args['orderby'],
		'order'               => $ordering_args['order'],
		'posts_per_page'      => -1,
		'meta_query'          => array(
			array(
				'key'   => '_stock_status',
				'value' => 'instock',
			),
		),
		'tax_query'           => array(
			'relation' => 'AND',
			array(
				'taxonomy' => 'product_cat',
				'terms'    => array( esc_attr( $term['slug'] ) ),
				'field'    => 'slug',
				'operator' => 'IN',
			),
			$query_args
		),
	);

	if ( isset( $ordering_args['meta_key'] ) ) {
		$args['meta_key'] = $ordering_args['meta_key'];
	}
	$products = new WP_Query( $args );

	ob_start();
	if ( $products->have_posts() ) {
		while ( $products->have_posts() ) : $products->the_post();

			add_action( 'woocommerce_before_shop_loop_item', 'astra_woo_shop_thumbnail_wrap_start', 6 );
			add_action( 'woocommerce_before_shop_loop_item', 'woocommerce_show_product_loop_sale_flash', 9 );
			add_action( 'woocommerce_after_shop_loop_item', 'astra_woo_shop_thumbnail_wrap_end', 8 );
			add_action( 'woocommerce_shop_loop_item_title', 'astra_woo_shop_out_of_stock', 8 );
			remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title' );
			remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price' );
			remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
			add_action( 'woocommerce_after_shop_loop_item', 'astra_woo_woocommerce_shop_product_content' );

			wc_get_template_part( 'content', 'product' );

		endwhile;
	}

	$product_objs = ob_get_clean();

	wc_reset_loop();
	wp_reset_query();
	wp_reset_postdata();
	wp_send_json_success( $product_objs );
}

add_action( 'wp_ajax_filter_products_by_stock', 'maksimer_filter_products_by_stock' );
add_action( 'wp_ajax_nopriv_filter_products_by_stock', 'maksimer_filter_products_by_stock' );
