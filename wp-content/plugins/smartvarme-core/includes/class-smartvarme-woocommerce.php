<?php
/**
 * WooCommerce Integration Class
 *
 * Handles all WooCommerce-specific customizations for the Smartvarme plugin.
 *
 * @package Smartvarme_Core
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Smartvarme WooCommerce Integration
 */
class Smartvarme_WooCommerce {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Declare HPOS compatibility
		add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );

		// Register product attributes and custom fields
		add_action( 'init', array( $this, 'register_product_attributes' ) );
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'add_custom_product_fields' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_custom_product_fields' ) );

		// Add bundle accessories admin interface
		add_action( 'woocommerce_product_options_related', array( $this, 'add_bundle_accessories_field' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_bundle_accessories_field' ) );

		// Display stock/delivery info on single product pages
		add_action( 'woocommerce_single_product_summary', array( $this, 'display_stock_delivery' ), 15 );

		// Display energy label icon on single product pages
		add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'display_energy_icon' ) );

		// Display energy label icon on archive/category pages
		add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'display_energy_icon_loop' ), 15 );

		// Display accessory/linked products on single product pages
		add_action( 'woocommerce_after_single_product_summary', array( $this, 'display_accessory_products' ), 15 );

		// Customize product archive/catalog
		add_filter( 'woocommerce_catalog_orderby', array( $this, 'customize_sorting_options' ) );
		add_filter( 'woocommerce_get_catalog_ordering_args', array( $this, 'handle_custom_sorting' ) );
		add_filter( 'loop_shop_per_page', array( $this, 'set_products_per_page' ), 20 );

		// Handle product URL redirects (safety net for any URL structure changes)
		add_action( 'template_redirect', array( $this, 'handle_product_redirects' ), 1 );

		// Ensure WooCommerce pages exist with blocks
		add_action( 'admin_init', array( $this, 'ensure_woocommerce_pages' ), 99 );

		// Empty cart message in Norwegian
		add_filter( 'wc_empty_cart_message', array( $this, 'empty_cart_message_norwegian' ) );

		// Customize price display format
		add_filter( 'woocommerce_price_format', array( $this, 'custom_price_format' ) );
		add_filter( 'wc_price_args', array( $this, 'custom_price_args' ) );

		// Set Norway as default country
		add_filter( 'default_checkout_billing_country', array( $this, 'set_default_country_norway' ) );
		add_filter( 'default_checkout_shipping_country', array( $this, 'set_default_country_norway' ) );

		// Add placeholders to checkout fields
		add_filter( 'woocommerce_checkout_fields', array( $this, 'add_checkout_field_placeholders' ) );

		// Verify cache exclusions for dynamic pages
		$this->verify_cache_exclusions();

		// Load custom checkout fields and email customizations
		require_once SMARTVARME_CORE_PATH . 'includes/woocommerce/checkout-fields.php';
		require_once SMARTVARME_CORE_PATH . 'includes/woocommerce/email-customization.php';

		// Load contact form integration
		require_once SMARTVARME_CORE_PATH . 'includes/class-smartvarme-contact-form.php';
		new Smartvarme_Contact_Form();
	}

	/**
	 * Declare HPOS compatibility
	 */
	public function declare_hpos_compatibility() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
				'custom_order_tables',
				SMARTVARME_CORE_PATH . 'smartvarme-core.php',
				true
			);
		}
	}

	/**
	 * Register product attributes
	 */
	public function register_product_attributes() {
		// Only run if wc_create_attribute exists
		if ( ! function_exists( 'wc_create_attribute' ) ) {
			return;
		}

		// Register pa_effekt attribute
		$effekt_attr = array(
			'name'         => 'Effekt',
			'slug'         => 'pa_effekt',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => true,
		);

		$effekt_result = wc_create_attribute( $effekt_attr );
		// Silently skip if already exists (WP_Error with code 'duplicate')
		if ( is_wp_error( $effekt_result ) && $effekt_result->get_error_code() !== 'duplicate' ) {
			error_log( 'Failed to create pa_effekt attribute: ' . $effekt_result->get_error_message() );
		}

		// Register pa_energiklasse attribute
		$energiklasse_attr = array(
			'name'         => 'Energiklasse',
			'slug'         => 'pa_energiklasse',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => true,
		);

		$energiklasse_result = wc_create_attribute( $energiklasse_attr );
		// Silently skip if already exists
		if ( is_wp_error( $energiklasse_result ) && $energiklasse_result->get_error_code() !== 'duplicate' ) {
			error_log( 'Failed to create pa_energiklasse attribute: ' . $energiklasse_result->get_error_message() );
		}

		// Register taxonomies for Gutenberg compatibility
		$taxonomy_args = array(
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'query_var'         => true,
			'rewrite'           => false,
			'show_admin_column' => true,
		);

		register_taxonomy( 'pa_effekt', array( 'product' ), $taxonomy_args );
		register_taxonomy( 'pa_energiklasse', array( 'product' ), $taxonomy_args );
	}

	/**
	 * Add custom product fields to admin
	 */
	public function add_custom_product_fields() {
		// Only run if woocommerce_wp_text_input function exists
		if ( ! function_exists( 'woocommerce_wp_text_input' ) ) {
			return;
		}

		global $post;

		echo '<div class="options_group">';

		// Effekt (kW) field
		woocommerce_wp_text_input(
			array(
				'id'          => '_effekt_kw',
				'label'       => __( 'Effekt (kW)', 'smartvarme-core' ),
				'placeholder' => 'f.eks. 6.5',
				'desc_tip'    => true,
				'description' => __( 'Effektbehov i kW for energikalkulator', 'smartvarme-core' ),
				'type'        => 'text',
			)
		);

		// Delivery time field
		woocommerce_wp_text_input(
			array(
				'id'          => '_delivery_time',
				'label'       => __( 'Leveringstid', 'smartvarme-core' ),
				'placeholder' => 'f.eks. 2-5 virkedager',
				'desc_tip'    => true,
				'description' => __( 'Forventet leveringstid', 'smartvarme-core' ),
				'type'        => 'text',
			)
		);

		echo '</div>';
	}

	/**
	 * Save custom product fields
	 *
	 * @param int $post_id Product post ID.
	 */
	public function save_custom_product_fields( $post_id ) {
		// Verify nonce (WooCommerce sets this)
		if ( ! isset( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( $_POST['woocommerce_meta_nonce'], 'woocommerce_save_data' ) ) {
			return;
		}

		// Check user permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save _effekt_kw
		if ( isset( $_POST['_effekt_kw'] ) ) {
			update_post_meta( $post_id, '_effekt_kw', sanitize_text_field( wp_unslash( $_POST['_effekt_kw'] ) ) );
		}

		// Save _delivery_time
		if ( isset( $_POST['_delivery_time'] ) ) {
			update_post_meta( $post_id, '_delivery_time', sanitize_text_field( wp_unslash( $_POST['_delivery_time'] ) ) );
		}
	}

	/**
	 * Display stock and delivery time on single product pages
	 */
	public function display_stock_delivery() {
		global $product;

		if ( ! $product ) {
			return;
		}

		// Get custom meta fields
		$delivery_time           = get_post_meta( $product->get_id(), '_delivery_time', true );
		$stock_display_override  = get_post_meta( $product->get_id(), '_stock_display_override', true );
		$stock_status            = $product->get_stock_status();

		// Map stock status to Norwegian text
		$stock_text  = '';
		$stock_class = '';

		if ( ! empty( $stock_display_override ) ) {
			$stock_text  = $stock_display_override;
			$stock_class = 'custom';
		} else {
			switch ( $stock_status ) {
				case 'instock':
					$stock_text  = 'På lager';
					$stock_class = 'in-stock';
					break;
				case 'outofstock':
					$stock_text  = 'Ikke på lager';
					$stock_class = 'out-of-stock';
					break;
				case 'onbackorder':
					$stock_text  = 'På bestilling';
					$stock_class = 'on-backorder';
					break;
				default:
					$stock_text  = 'Kontakt oss';
					$stock_class = 'unknown';
					break;
			}
		}

		// Output stock/delivery display directly
		echo '<div class="smartvarme-stock-delivery">';
		echo '<div class="stock-status ' . esc_attr( $stock_class ) . '">';
		echo '<span class="stock-icon">●</span>';
		echo '<span class="stock-text">' . esc_html( $stock_text ) . '</span>';
		echo '</div>';

		if ( ! empty( $delivery_time ) ) {
			echo '<div class="delivery-time">';
			echo '<span class="delivery-icon">🚚</span>';
			echo '<span class="delivery-text">Leveringstid: ' . esc_html( $delivery_time ) . '</span>';
			echo '</div>';
		}

		echo '</div>';
	}

	/**
	 * Display energy label icon on single product pages
	 *
	 * Displays the energy classification icon and links to product datasheet and energy link
	 * based on ACF fields: energi_selection, produktdatablad, and energi_link
	 */
	public function display_energy_icon() {
		global $product;

		if ( ! $product || ! function_exists( 'get_field' ) ) {
			return;
		}

		$product_id      = $product->get_id();
		$selection       = get_field( 'energi_selection', $product_id );
		$options         = get_field( 'energi_efficiency_options', 'option' );
		$produktdatablad = get_field( 'produktdatablad', $product_id );
		$energi_link     = get_field( 'energi_link', $product_id );

		// Only display if selection exists and is not "Ingen ikon" (0)
		if ( empty( $selection ) || $selection === '0' || empty( $options ) ) {
			return;
		}

		// Build output
		$output = '<div class="energi_wrapper">';

		// Loop through options to find matching icon
		foreach ( $options as $option ) {
			if ( isset( $option['text'] ) && $option['text'] === $selection ) {
				$icon_url = isset( $option['icon'] ) ? $option['icon'] : '';

				if ( ! empty( $icon_url ) ) {
					// Wrap icon in energy link if available
					if ( ! empty( $energi_link ) ) {
						$output .= '<a target="_blank" rel="noopener" href="' . esc_url( $energi_link ) . '" class="energi-icon-link">';
					}

					$output .= '<img loading="lazy" width="69" height="43" src="' . esc_url( $icon_url ) . '" alt="' . esc_attr( $selection ) . '" class="energi-icon icon--left" />';

					if ( ! empty( $energi_link ) ) {
						$output .= '</a>';
					}

					// Add product datasheet link if available
					if ( ! empty( $produktdatablad ) ) {
						$output .= '<div class="energi-datasheet">';
						$output .= '<a target="_blank" rel="noopener" href="' . esc_url( $produktdatablad ) . '" class="energi-datasheet-link">';
						$output .= __( 'Produktdatablad', 'smartvarme-core' );
						$output .= '</a>';
						$output .= '</div>';
					}
				}
				break;
			}
		}

		$output .= '</div>';

		echo $output;
	}

	/**
	 * Display energy label icon on archive/category pages (simplified version)
	 *
	 * Shows only the energy icon without link or product datasheet for compact display
	 */
	public function display_energy_icon_loop() {
		global $product;

		if ( ! $product || ! function_exists( 'get_field' ) ) {
			return;
		}

		$product_id = $product->get_id();
		$selection  = get_field( 'energi_selection', $product_id );
		$options    = get_field( 'energi_efficiency_options', 'option' );

		// Only display if selection exists and is not "Ingen ikon" (0)
		if ( empty( $selection ) || $selection === '0' || empty( $options ) ) {
			return;
		}

		// Build output (icon only, no link on archive pages)
		$output = '<div class="energi_wrapper">';

		// Loop through options to find matching icon
		foreach ( $options as $option ) {
			if ( isset( $option['text'] ) && $option['text'] === $selection ) {
				$icon_url = isset( $option['icon'] ) ? $option['icon'] : '';

				if ( ! empty( $icon_url ) ) {
					// Just display the icon without any link wrapper
					$output .= '<img loading="lazy" width="50" height="31" src="' . esc_url( $icon_url ) . '" alt="' . esc_attr( $selection ) . '" class="energi-icon icon--left" />';
				}
				break;
			}
		}

		$output .= '</div>';

		echo $output;
	}

	/**
	 * Customize sorting options with Norwegian labels
	 *
	 * @param array $options Existing sorting options.
	 * @return array Modified sorting options.
	 */
	public function customize_sorting_options( $options ) {
		// Replace default labels with Norwegian
		$options = array(
			'menu_order' => 'Standard sortering',
			'popularity' => 'Sorter etter popularitet',
			'rating'     => 'Sorter etter vurdering',
			'date'       => 'Sorter etter nyeste',
			'price'      => 'Sorter etter pris: lav til høy',
			'price-desc' => 'Sorter etter pris: høy til lav',
			'stock_status' => 'På lager først',
		);

		return $options;
	}

	/**
	 * Handle custom sorting logic (stock status sorting)
	 *
	 * @param array $args Ordering args.
	 * @return array Modified ordering args.
	 */
	public function handle_custom_sorting( $args ) {
		$orderby_value = isset( $_GET['orderby'] ) ? wc_clean( $_GET['orderby'] ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );

		if ( 'stock_status' === $orderby_value ) {
			$args['meta_key'] = '_stock_status';
			$args['orderby']  = 'meta_value';
			$args['order']    = 'ASC'; // instock comes before outofstock alphabetically
		}

		return $args;
	}

	/**
	 * Set products per page to 12 (4 rows of 3)
	 *
	 * @return int Number of products per page.
	 */
	public function set_products_per_page() {
		return 12;
	}

	/**
	 * Handle product URL redirects for any URL structure changes
	 *
	 * This is a safety net that catches broken product URLs by matching the slug
	 * and redirecting to the correct permalink with a 301 status.
	 */
	public function handle_product_redirects() {
		if ( ! is_404() ) {
			return;
		}

		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$slug        = basename( rtrim( $request_uri, '/' ) );

		// Try to find product by slug
		$product_query = new WP_Query(
			array(
				'post_type'      => 'product',
				'name'           => $slug,
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		if ( $product_query->have_posts() ) {
			$product_query->the_post();
			$new_url = get_permalink();
			wp_reset_postdata();

			// Only redirect if the URLs are different
			if ( $new_url && $new_url !== home_url( $request_uri ) ) {
				wp_redirect( $new_url, 301 );
				exit;
			}
		}
	}

	/**
	 * Ensure WooCommerce pages exist with block content
	 *
	 * Runs once on admin_init to create or update cart, checkout, and my-account pages.
	 * Uses a transient flag to avoid re-checking on every admin page load.
	 */
	public function ensure_woocommerce_pages() {
		// Only run if WooCommerce functions exist
		if ( ! function_exists( 'wc_get_page_id' ) ) {
			return;
		}

		// Check if we've already run this check
		if ( get_transient( 'smartvarme_wc_pages_checked' ) ) {
			return;
		}

		// Cart page
		$cart_id = wc_get_page_id( 'cart' );
		if ( $cart_id <= 0 || get_post_status( $cart_id ) !== 'publish' ) {
			// Create new cart page
			$cart_id = wp_insert_post(
				array(
					'post_title'   => 'Handlekurv',
					'post_content' => '<!-- wp:woocommerce/cart /-->',
					'post_status'  => 'publish',
					'post_type'    => 'page',
					'post_author'  => 1,
				)
			);
			if ( $cart_id && ! is_wp_error( $cart_id ) ) {
				update_option( 'woocommerce_cart_page_id', $cart_id );
			}
		} else {
			// Check if existing page uses old shortcode
			$post = get_post( $cart_id );
			if ( $post && strpos( $post->post_content, '[woocommerce_cart]' ) !== false ) {
				wp_update_post(
					array(
						'ID'           => $cart_id,
						'post_content' => '<!-- wp:woocommerce/cart /-->',
					)
				);
			}
		}

		// Checkout page
		$checkout_id = wc_get_page_id( 'checkout' );
		if ( $checkout_id <= 0 || get_post_status( $checkout_id ) !== 'publish' ) {
			// Create new checkout page
			$checkout_id = wp_insert_post(
				array(
					'post_title'   => 'Kasse',
					'post_content' => '<!-- wp:woocommerce/checkout /-->',
					'post_status'  => 'publish',
					'post_type'    => 'page',
					'post_author'  => 1,
				)
			);
			if ( $checkout_id && ! is_wp_error( $checkout_id ) ) {
				update_option( 'woocommerce_checkout_page_id', $checkout_id );
			}
		} else {
			// Check if existing page uses old shortcode
			$post = get_post( $checkout_id );
			if ( $post && strpos( $post->post_content, '[woocommerce_checkout]' ) !== false ) {
				wp_update_post(
					array(
						'ID'           => $checkout_id,
						'post_content' => '<!-- wp:woocommerce/checkout /-->',
					)
				);
			}
		}

		// My Account page
		$account_id = wc_get_page_id( 'myaccount' );
		if ( $account_id <= 0 || get_post_status( $account_id ) !== 'publish' ) {
			// Create new my account page
			$account_id = wp_insert_post(
				array(
					'post_title'   => 'Min konto',
					'post_content' => '<!-- wp:woocommerce/customer-account /-->',
					'post_status'  => 'publish',
					'post_type'    => 'page',
					'post_author'  => 1,
				)
			);
			if ( $account_id && ! is_wp_error( $account_id ) ) {
				update_option( 'woocommerce_myaccount_page_id', $account_id );
			}
		}

		// Set transient flag to prevent re-checking (expires in 7 days)
		set_transient( 'smartvarme_wc_pages_checked', true, WEEK_IN_SECONDS );
	}

	/**
	 * Empty cart message in Norwegian with link to shop
	 *
	 * @param string $message Default empty cart message.
	 * @return string Modified message.
	 */
	public function empty_cart_message_norwegian( $message ) {
		if ( function_exists( 'wc_get_page_id' ) ) {
			$shop_url = get_permalink( wc_get_page_id( 'shop' ) );
			return 'Din handlekurv er tom. <a href="' . esc_url( $shop_url ) . '">Utforsk v&aring;re produkter</a>';
		}
		return $message;
	}

	/**
	 * Customize price format to Norwegian style with "kr" prefix and ",-" suffix
	 *
	 * @param string $format Price format.
	 * @return string Modified format.
	 */
	public function custom_price_format( $format ) {
		// Format: currency symbol (kr) + space + price + ,-
		return '%1$s&nbsp;%2$s,-';
	}

	/**
	 * Customize price arguments to remove decimals and use Norwegian separators
	 *
	 * @param array $args Price arguments.
	 * @return array Modified arguments.
	 */
	public function custom_price_args( $args ) {
		$args['decimals'] = 0;
		$args['thousand_separator'] = '&nbsp;'; // Non-breaking space
		$args['decimal_separator'] = ',';
		return $args;
	}

	/**
	 * Set Norway as default country for checkout
	 *
	 * @param string $country Default country code.
	 * @return string Norway country code (NO).
	 */
	public function set_default_country_norway( $country ) {
		return 'NO';
	}

	/**
	 * Add placeholders to checkout fields
	 */
	public function add_checkout_field_placeholders( $fields ) {
		// Billing fields with Norwegian placeholders
		$placeholders = array(
			'billing_first_name' => 'Fornavn',
			'billing_last_name'  => 'Etternavn',
			'billing_address_1'  => 'Gateadresse',
			'billing_address_2'  => 'Adresselinje 2 (valgfritt)',
			'billing_postcode'   => 'Postnummer',
			'billing_city'       => 'Poststed',
			'billing_phone'      => 'Telefon',
			'billing_email'      => 'E-postadresse',
		);

		// Apply placeholders to billing fields
		foreach ( $placeholders as $field_key => $placeholder ) {
			if ( isset( $fields['billing'][ $field_key ] ) ) {
				$fields['billing'][ $field_key ]['placeholder'] = $placeholder;
			}
		}

		return $fields;
	}

	/**
	 * Verify cache exclusions for WooCommerce dynamic pages
	 *
	 * Sets nocache headers for cart, checkout, and my-account pages to ensure
	 * they are never served from cache.
	 */
	public function verify_cache_exclusions() {
		add_action(
			'template_redirect',
			function() {
				if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() ) ) {
					nocache_headers();
				}
			}
		);
	}

	/**
	 * Display accessory/linked products on single product pages
	 *
	 * Shows upsells, cross-sells, and bundle-sells as "Tilbehør" section
	 */
	public function display_accessory_products() {
		global $product;

		if ( ! $product ) {
			return;
		}

		// Get upsell and cross-sell products
		$upsell_ids = $product->get_upsell_ids();
		$crosssell_ids = $product->get_cross_sell_ids();

		// Get bundle-sells (WooCommerce Product Bundles)
		$bundle_ids = get_post_meta( $product->get_id(), '_wc_pb_bundle_sell_ids', true );
		if ( ! is_array( $bundle_ids ) ) {
			$bundle_ids = array();
		}

		// Combine all (bundle-sells take priority, then upsells, then cross-sells)
		$accessory_ids = array_unique( array_merge( $bundle_ids, $upsell_ids, $crosssell_ids ) );

		if ( empty( $accessory_ids ) ) {
			return;
		}

		// Get custom title for bundle-sells, or use default
		$section_title = get_post_meta( $product->get_id(), '_wc_pb_bundle_sells_title', true );
		if ( empty( $section_title ) ) {
			$section_title = 'Tilbehør';
		}

		// Limit to 8 products
		$accessory_ids = array_slice( $accessory_ids, 0, 8 );

		?>
		<section class="product-accessories">
			<h2><?php echo esc_html( $section_title ); ?></h2>
			<div class="woocommerce">
				<ul class="products columns-4">
					<?php
					foreach ( $accessory_ids as $accessory_id ) {
						$accessory_product = wc_get_product( $accessory_id );
						if ( ! $accessory_product ) {
							continue;
						}

						// Set up post data for template
						$GLOBALS['post'] = get_post( $accessory_id );
						setup_postdata( $GLOBALS['post'] );

						// Use WooCommerce template
						wc_get_template_part( 'content', 'product' );
					}
					wp_reset_postdata();
					?>
				</ul>
			</div>
		</section>
		<?php
	}

	/**
	 * Add bundle accessories field in product admin
	 *
	 * Displays in the "Linked Products" tab
	 */
	public function add_bundle_accessories_field() {
		global $post;

		// Get current values
		$bundle_ids = get_post_meta( $post->ID, '_wc_pb_bundle_sell_ids', true );
		if ( ! is_array( $bundle_ids ) ) {
			$bundle_ids = array();
		}

		$bundle_title = get_post_meta( $post->ID, '_wc_pb_bundle_sells_title', true );
		if ( empty( $bundle_title ) ) {
			$bundle_title = 'Tilbehør';
		}

		// Get product objects for selected products
		$bundle_products = array();
		foreach ( $bundle_ids as $bundle_id ) {
			$product = wc_get_product( $bundle_id );
			if ( $product ) {
				$bundle_products[ $bundle_id ] = $product->get_formatted_name();
			}
		}

		?>
		<p class="form-field">
			<label><?php esc_html_e( 'Tilbehørsprodukter (Bundle-sells)', 'smartvarme-core' ); ?></label>
			<select class="wc-product-search" multiple="multiple" style="width: 50%;"
			        id="bundle_sell_ids" name="bundle_sell_ids[]"
			        data-placeholder="<?php esc_attr_e( 'Søk etter produkter&hellip;', 'smartvarme-core' ); ?>"
			        data-action="woocommerce_json_search_products_and_variations">
				<?php
				foreach ( $bundle_products as $product_id => $product_name ) {
					echo '<option value="' . esc_attr( $product_id ) . '" selected="selected">' . esc_html( $product_name ) . '</option>';
				}
				?>
			</select>
			<span class="description">
				<?php esc_html_e( 'Velg produkter som skal vises som tilbehør til dette produktet.', 'smartvarme-core' ); ?>
			</span>
		</p>

		<p class="form-field">
			<label for="bundle_sells_title"><?php esc_html_e( 'Tilbehør seksjonstittel', 'smartvarme-core' ); ?></label>
			<input type="text" class="short" name="bundle_sells_title" id="bundle_sells_title"
			       value="<?php echo esc_attr( $bundle_title ); ?>"
			       placeholder="<?php esc_attr_e( 'Tilbehør', 'smartvarme-core' ); ?>" />
			<span class="description">
				<?php esc_html_e( 'Tittel som vises over tilbehørsprodukter (f.eks. "Tilbehør til Quadro 2").', 'smartvarme-core' ); ?>
			</span>
		</p>
		<?php
	}

	/**
	 * Save bundle accessories field
	 *
	 * @param int $post_id Product ID
	 */
	public function save_bundle_accessories_field( $post_id ) {
		// Save bundle product IDs
		if ( isset( $_POST['bundle_sell_ids'] ) && is_array( $_POST['bundle_sell_ids'] ) ) {
			$bundle_ids = array_map( 'intval', $_POST['bundle_sell_ids'] );
			update_post_meta( $post_id, '_wc_pb_bundle_sell_ids', $bundle_ids );
		} else {
			delete_post_meta( $post_id, '_wc_pb_bundle_sell_ids' );
		}

		// Save bundle title
		if ( isset( $_POST['bundle_sells_title'] ) ) {
			$title = sanitize_text_field( $_POST['bundle_sells_title'] );
			if ( ! empty( $title ) ) {
				update_post_meta( $post_id, '_wc_pb_bundle_sells_title', $title );
			} else {
				delete_post_meta( $post_id, '_wc_pb_bundle_sells_title' );
			}
		}
	}
}
