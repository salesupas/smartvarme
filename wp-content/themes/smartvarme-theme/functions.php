<?php
/**
 * Smartvarme Theme functions and definitions
 *
 * @package Smartvarme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Theme setup
 */
function smartvarme_theme_setup() {
	// Enable synced pattern support (WordPress 6.3+)
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'align-wide' );

	// WooCommerce support
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'smartvarme_theme_setup' );

/**
 * Set products per page to 48 for all WooCommerce archives
 */
function smartvarme_products_per_page() {
	return 48;
}
add_filter( 'loop_shop_per_page', 'smartvarme_products_per_page', 20 );

/**
 * Enqueue theme scripts and styles
 */
function smartvarme_enqueue_assets() {
	$asset_file = get_template_directory() . '/build/index.asset.php';

	// Check if build assets exist
	if ( ! file_exists( $asset_file ) ) {
		return;
	}

	$asset = include $asset_file;

	// Enqueue JavaScript
	wp_enqueue_script(
		'smartvarme-scripts',
		get_template_directory_uri() . '/build/index.js',
		$asset['dependencies'],
		$asset['version'],
		true
	);

	// Enqueue frontend styles
	if ( file_exists( get_template_directory() . '/build/style-index.css' ) ) {
		wp_enqueue_style(
			'smartvarme-styles',
			get_template_directory_uri() . '/build/style-index.css',
			array(),
			$asset['version']
		);
	}

	// Enqueue smart search script
	wp_enqueue_script(
		'smartvarme-smart-search',
		get_template_directory_uri() . '/js/smart-search.js',
		array(),
		'1.0.0',
		true
	);

	// Localize script for AJAX
	wp_localize_script(
		'smartvarme-smart-search',
		'smartvarme_search',
		array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'smartvarme_search_nonce' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'smartvarme_enqueue_assets' );

/**
 * Enqueue editor assets
 */
function smartvarme_enqueue_editor_assets() {
	$asset_file = get_template_directory() . '/build/index.asset.php';

	// Check if build assets exist
	if ( ! file_exists( $asset_file ) ) {
		return;
	}

	$asset = include $asset_file;

	// Enqueue editor styles
	if ( file_exists( get_template_directory() . '/build/editor-index.css' ) ) {
		wp_enqueue_style(
			'smartvarme-editor-styles',
			get_template_directory_uri() . '/build/editor-index.css',
			array(),
			$asset['version']
		);
	}
}
add_action( 'enqueue_block_editor_assets', 'smartvarme_enqueue_editor_assets' );

/**
 * Register navigation menus
 */
function smartvarme_register_menus() {
	register_nav_menus(
		array(
			'primary'          => __( 'Primary Menu', 'smartvarme' ),
			'footer-products'  => __( 'Footer Menu - Produkter', 'smartvarme' ),
			'footer-services'  => __( 'Footer Menu - Tjenester', 'smartvarme' ),
			'footer-info'      => __( 'Footer Menu - Informasjon', 'smartvarme' ),
		)
	);
}
add_action( 'after_setup_theme', 'smartvarme_register_menus' );

/**
 * AJAX handler for live search
 */
function smartvarme_live_search() {
	// Verify nonce
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'smartvarme_search_nonce' ) ) {
		wp_send_json_error( 'Invalid nonce' );
		return;
	}

	$query = isset( $_POST['query'] ) ? sanitize_text_field( $_POST['query'] ) : '';

	if ( empty( $query ) || strlen( $query ) < 2 ) {
		wp_send_json_error( 'Query too short' );
		return;
	}

	$results = array(
		'categories' => array(),
		'brands'     => array(),
		'products'   => array(),
	);

	// Search product categories
	$categories = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'search'     => $query,
			'number'     => 50, // Show many more categories
		)
	);

	if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) {
		foreach ( $categories as $category ) {
			$results['categories'][] = array(
				'name'        => $category->name,
				'url'         => get_term_link( $category ),
				'count'       => $category->count,
				'description' => $category->description,
			);
		}
	}

	// Search brands (product_tag or pa_merke attribute)
	// First try product tags
	$brands = get_terms(
		array(
			'taxonomy'   => 'product_tag',
			'hide_empty' => false,
			'search'     => $query,
			'number'     => 20,
		)
	);

	if ( ! is_wp_error( $brands ) && ! empty( $brands ) ) {
		foreach ( $brands as $brand ) {
			$results['brands'][] = array(
				'name'  => $brand->name,
				'url'   => get_term_link( $brand ),
				'count' => $brand->count,
				'type'  => 'Merke',
			);
		}
	}

	// Also search for brand attribute if it exists
	$brand_attributes = get_terms(
		array(
			'taxonomy'   => 'pa_merke',
			'hide_empty' => false,
			'search'     => $query,
			'number'     => 20,
		)
	);

	if ( ! is_wp_error( $brand_attributes ) && ! empty( $brand_attributes ) ) {
		foreach ( $brand_attributes as $brand ) {
			$results['brands'][] = array(
				'name'  => $brand->name,
				'url'   => get_term_link( $brand ),
				'count' => $brand->count,
				'type'  => 'Merke',
			);
		}
	}

	// Search products
	$products_query = new WP_Query(
		array(
			'post_type'      => 'product',
			'posts_per_page' => 20, // Show many more products
			's'              => $query,
			'post_status'    => 'publish',
		)
	);

	if ( $products_query->have_posts() ) {
		while ( $products_query->have_posts() ) {
			$products_query->the_post();
			$product = wc_get_product( get_the_ID() );

			if ( ! $product ) {
				continue;
			}

			$results['products'][] = array(
				'name'        => get_the_title(),
				'url'         => get_permalink(),
				'image'       => get_the_post_thumbnail_url( get_the_ID(), 'medium' ),
				'price'       => $product->get_price_html(),
				'description' => wp_trim_words( get_the_excerpt(), 15, '...' ),
				'in_stock'    => $product->is_in_stock(),
			);
		}
		wp_reset_postdata();
	}

	wp_send_json_success( $results );
}
add_action( 'wp_ajax_smartvarme_live_search', 'smartvarme_live_search' );
add_action( 'wp_ajax_nopriv_smartvarme_live_search', 'smartvarme_live_search' );

/**
 * Build hierarchical menu HTML
 */
function smartvarme_build_menu_html( $items, $parent_id = 0 ) {
	$html = '';

	foreach ( $items as $item ) {
		if ( $item->menu_item_parent == $parent_id ) {
			$has_children = smartvarme_has_children( $items, $item->ID );
			$classes      = array( 'wp-block-navigation-item' );

			if ( $item->current ) {
				$classes[] = 'current-menu-item';
			}
			if ( $has_children ) {
				$classes[] = 'menu-item-has-children';
			}

			$html .= sprintf(
				'<li class="%s"><a class="wp-block-navigation-item__content" href="%s">%s</a>',
				esc_attr( implode( ' ', $classes ) ),
				esc_url( $item->url ),
				esc_html( $item->title )
			);

			// Add submenu if has children
			if ( $has_children ) {
				$html .= '<ul class="sub-menu">';
				$html .= smartvarme_build_menu_html( $items, $item->ID );
				$html .= '</ul>';
			}

			$html .= '</li>';
		}
	}

	return $html;
}

/**
 * Check if menu item has children
 */
function smartvarme_has_children( $items, $parent_id ) {
	foreach ( $items as $item ) {
		if ( $item->menu_item_parent == $parent_id ) {
			return true;
		}
	}
	return false;
}

/**
 * Auto-assign menus to navigation blocks using simple counter
 */
function smartvarme_auto_assign_menu( $block_content, $block, $instance ) {
	// Only target navigation blocks without a ref
	if ( 'core/navigation' !== $block['blockName'] || ! empty( $block['attrs']['ref'] ) ) {
		return $block_content;
	}

	global $smartvarme_nav_block_counter;

	// Initialize counter
	if ( ! isset( $smartvarme_nav_block_counter ) ) {
		$smartvarme_nav_block_counter = 0;
	}

	$smartvarme_nav_block_counter++;

	$locations = get_nav_menu_locations();
	$menu_location = null;
	$menu_class = '';
	$debug_info = array();

	$debug_info['nav_block_number'] = $smartvarme_nav_block_counter;
	$debug_info['locations_available'] = ! empty( $locations ) ? implode( ', ', array_keys( $locations ) ) : 'none';

	// Assign menus based on navigation block order:
	// Block 1 = Header (primary menu)
	// Block 2 = Footer Products
	// Block 3 = Footer Services
	if ( $smartvarme_nav_block_counter === 1 ) {
		// First nav block = Header Primary Menu
		$menu_location = isset( $locations['primary'] ) ? 'primary' : null;
		$menu_class = 'primary-menu';
		$debug_info['context'] = 'header-primary';
	} elseif ( $smartvarme_nav_block_counter === 2 ) {
		// Second nav block = Footer Products
		$menu_location = isset( $locations['footer-products'] ) ? 'footer-products' : null;
		$menu_class = 'footer-products-menu';
		$debug_info['context'] = 'footer-products';
	} elseif ( $smartvarme_nav_block_counter === 3 ) {
		// Third nav block = Footer Services
		$menu_location = isset( $locations['footer-services'] ) ? 'footer-services' : null;
		$menu_class = 'footer-services-menu';
		$debug_info['context'] = 'footer-services';
	}

	$debug_info['menu_location'] = $menu_location ? $menu_location : 'NOT SET';

	// Inject the menu if we have a location
	if ( $menu_location && isset( $locations[ $menu_location ] ) ) {
		$menu = wp_get_nav_menu_object( $locations[ $menu_location ] );
		$debug_info['menu_found'] = $menu ? 'yes (ID: ' . $menu->term_id . ', Name: ' . $menu->name . ')' : 'no';

		if ( $menu ) {
			$menu_items = wp_get_nav_menu_items( $menu->term_id );
			$debug_info['menu_items_count'] = $menu_items ? count( $menu_items ) : 0;

			if ( $menu_items ) {
				$menu_inner_html = smartvarme_build_menu_html( $menu_items, 0 );

				// Strip out default nav items
				$block_content = preg_replace(
					'/<li[^>]*class="[^"]*wp-block-navigation-item[^"]*"[^>]*>.*?<\/li>/s',
					'',
					$block_content
				);

				// Replace ul and add menu class
				$block_content = preg_replace_callback(
					'/<ul([^>]*class=["\'])([^"\']*)(["\'"][^>]*)>.*?<\/ul>/s',
					function( $matches ) use ( $menu_inner_html, $menu_class ) {
						$classes = $matches[2];
						if ( strpos( $classes, $menu_class ) === false ) {
							$classes .= ' ' . $menu_class;
						}
						return '<ul' . $matches[1] . trim( $classes ) . $matches[3] . '>' . $menu_inner_html . '</ul>';
					},
					$block_content
				);

				$debug_info['menu_injected'] = 'yes';
			}
		}
	} else {
		$debug_info['menu_found'] = 'no - location not set or menu not assigned';
	}

	// Add debug comment to HTML
	$debug_comment = '<!-- SMARTVARME MENU DEBUG: ' . wp_json_encode( $debug_info ) . ' -->';
	$block_content = $debug_comment . $block_content;

	return $block_content;
}
add_filter( 'render_block', 'smartvarme_auto_assign_menu', 10, 3 );

/**
 * Add WooCommerce taxonomies to nav menus
 */
function smartvarme_add_woocommerce_nav_menu_meta_boxes() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	// Add product categories to nav menu
	add_meta_box(
		'add-product-cat',
		__( 'Produktkategorier', 'smartvarme' ),
		'wp_nav_menu_item_taxonomy_meta_box',
		'nav-menus',
		'side',
		'default',
		array( 'taxonomy' => 'product_cat' )
	);

	// Add product tags to nav menu
	add_meta_box(
		'add-product-tag',
		__( 'Produkttags', 'smartvarme' ),
		'wp_nav_menu_item_taxonomy_meta_box',
		'nav-menus',
		'side',
		'default',
		array( 'taxonomy' => 'product_tag' )
	);
}
add_action( 'admin_head-nav-menus.php', 'smartvarme_add_woocommerce_nav_menu_meta_boxes' );

/**
 * Register block pattern categories
 */
function smartvarme_register_pattern_categories() {
	register_block_pattern_category(
		'smartvarme',
		array( 'label' => __( 'Smartvarme', 'smartvarme' ) )
	);

	register_block_pattern_category(
		'smartvarme-faq',
		array( 'label' => __( 'FAQ', 'smartvarme' ) )
	);

	register_block_pattern_category(
		'smartvarme-cta',
		array( 'label' => __( 'Oppfordring til handling', 'smartvarme' ) )
	);
}
add_action( 'init', 'smartvarme_register_pattern_categories' );

/**
 * Custom Product Display Shortcodes
 */

/**
 * Display products on sale / campaign
 * Usage: [kampanje_produkter limit="12"]
 */
function smartvarme_campaign_products_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'limit'   => 12,
			'columns' => 3,
		),
		$atts
	);

	$args = array(
		'post_type'      => 'product',
		'posts_per_page' => intval( $atts['limit'] ),
		'post__in'       => array_merge( array( 0 ), wc_get_product_ids_on_sale() ),
	);

	$query = new WP_Query( $args );

	if ( ! $query->have_posts() ) {
		return '<p>Ingen kampanjeprodukter for øyeblikket.</p>';
	}

	ob_start();
	?>
	<div class="woocommerce">
		<ul class="products columns-<?php echo esc_attr( $atts['columns'] ); ?>">
			<?php
			while ( $query->have_posts() ) {
				$query->the_post();
				wc_get_template_part( 'content', 'product' );
			}
			wp_reset_postdata();
			?>
		</ul>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'kampanje_produkter', 'smartvarme_campaign_products_shortcode' );

/**
 * Display products in stock
 * Usage: [produkter_pa_lager limit="12" category="peiser"]
 */
function smartvarme_in_stock_products_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'limit'    => 12,
			'columns'  => 3,
			'category' => '',
		),
		$atts
	);

	$args = array(
		'post_type'      => 'product',
		'posts_per_page' => intval( $atts['limit'] ),
		'meta_query'     => array(
			array(
				'key'     => '_stock_status',
				'value'   => 'instock',
				'compare' => '=',
			),
		),
	);

	if ( ! empty( $atts['category'] ) ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => $atts['category'],
			),
		);
	}

	$query = new WP_Query( $args );

	if ( ! $query->have_posts() ) {
		return '<p>Ingen produkter på lager for øyeblikket.</p>';
	}

	ob_start();
	?>
	<div class="woocommerce">
		<ul class="products columns-<?php echo esc_attr( $atts['columns'] ); ?>">
			<?php
			while ( $query->have_posts() ) {
				$query->the_post();
				wc_get_template_part( 'content', 'product' );
			}
			wp_reset_postdata();
			?>
		</ul>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'produkter_pa_lager', 'smartvarme_in_stock_products_shortcode' );

/**
 * Display products by category
 * Usage: [produkter_kategori category="peiser" limit="12"]
 */
function smartvarme_category_products_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'category' => '',
			'limit'    => 12,
			'columns'  => 3,
			'orderby'  => 'date',
			'order'    => 'DESC',
		),
		$atts
	);

	if ( empty( $atts['category'] ) ) {
		return '<p>Vennligst spesifiser en kategori.</p>';
	}

	$args = array(
		'post_type'      => 'product',
		'posts_per_page' => intval( $atts['limit'] ),
		'orderby'        => $atts['orderby'],
		'order'          => $atts['order'],
		'tax_query'      => array(
			array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => $atts['category'],
			),
		),
	);

	$query = new WP_Query( $args );

	if ( ! $query->have_posts() ) {
		return '<p>Ingen produkter funnet i denne kategorien.</p>';
	}

	ob_start();
	?>
	<div class="woocommerce">
		<ul class="products columns-<?php echo esc_attr( $atts['columns'] ); ?>">
			<?php
			while ( $query->have_posts() ) {
				$query->the_post();
				wc_get_template_part( 'content', 'product' );
			}
			wp_reset_postdata();
			?>
		</ul>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'produkter_kategori', 'smartvarme_category_products_shortcode' );

/**
 * Display featured products
 * Usage: [utvalgte_produkter limit="8"]
 */
function smartvarme_featured_products_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'limit'   => 8,
			'columns' => 4,
		),
		$atts
	);

	$args = array(
		'post_type'      => 'product',
		'posts_per_page' => intval( $atts['limit'] ),
		'tax_query'      => array(
			array(
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => 'featured',
			),
		),
	);

	$query = new WP_Query( $args );

	if ( ! $query->have_posts() ) {
		return '<p>Ingen utvalgte produkter.</p>';
	}

	ob_start();
	?>
	<div class="woocommerce">
		<ul class="products columns-<?php echo esc_attr( $atts['columns'] ); ?>">
			<?php
			while ( $query->have_posts() ) {
				$query->the_post();
				wc_get_template_part( 'content', 'product' );
			}
			wp_reset_postdata();
			?>
		</ul>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'utvalgte_produkter', 'smartvarme_featured_products_shortcode' );

/**
 * Add contact buttons and callback form to single product pages
 */
function smartvarme_product_contact_section() {
	global $product;

	if ( ! $product ) {
		return;
	}

	// Build email link with product info
	$product_name = $product->get_name();
	$product_url  = get_permalink();
	$subject      = 'Spørsmål om ' . $product_name;
	$body         = "Hei,\n\nJeg har et spørsmål om følgende produkt:\n\n" . $product_name . "\n" . $product_url . "\n\n";
	$mailto       = 'mailto:post@smartvarme.no?subject=' . rawurlencode( $subject ) . '&body=' . rawurlencode( $body );

	?>
	<div class="product-contact-section">
		<div class="contact-heading">
			<h3>Har du spørsmål om dette produktet?</h3>
		</div>
		<div class="contact-buttons">
			<a href="<?php echo esc_url( $mailto ); ?>" class="contact-button email-button">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
					<polyline points="22,6 12,13 2,6"></polyline>
				</svg>
				Send oss en e-post
			</a>
			<button type="button" class="contact-button callback-button" id="toggle-callback-form">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
				</svg>
				Ring meg innen 1 time
			</button>
		</div>
		<div class="callback-form-container" id="callback-form" style="display: none;">
			<?php
			// Display Formidable Form ID 11
			if ( shortcode_exists( 'formidable' ) ) {
				echo do_shortcode( '[formidable id=11]' );
			} else {
				echo '<p style="padding: 1rem; background: #fff3cd; border-radius: 4px;">Formidable Forms plugin må være aktivert for å vise tilbakeringsskjemaet.</p>';
			}
			?>
		</div>
	</div>
	<?php
}
add_action( 'woocommerce_single_product_summary', 'smartvarme_product_contact_section', 35 );

/**
 * Add JavaScript for callback form toggle
 */
function smartvarme_product_contact_scripts() {
	if ( is_product() ) {
		?>
		<script>
		document.addEventListener('DOMContentLoaded', function() {
			const toggleButton = document.getElementById('toggle-callback-form');
			const formContainer = document.getElementById('callback-form');
			
			if (toggleButton && formContainer) {
				toggleButton.addEventListener('click', function() {
					if (formContainer.style.display === 'none') {
						formContainer.style.display = 'block';
						toggleButton.classList.add('active');
						// Smooth scroll to form
						setTimeout(function() {
							formContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
						}, 100);
					} else {
						formContainer.style.display = 'none';
						toggleButton.classList.remove('active');
					}
				});
			}
		});
		</script>
		<?php
	}
}
add_action( 'wp_footer', 'smartvarme_product_contact_scripts' );

/**
 * Add pipe calculator link to product pages
 */
function smartvarme_pipe_calculator_link() {
	global $product;

	if ( ! $product ) {
		return;
	}

	// Get product ID
	$product_id = $product->get_id();

	// Check if this is a fireplace/stove product (you can add category check here if needed)
	// For now, show on all products
	$calculator_url = 'https://pipekalkulator.smartvarme.no/?fireplace=' . $product_id;

	?>
	<div class="pipe-calculator-section">
		<div class="pipe-calculator-icon">
			<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<rect x="2" y="7" width="20" height="15" rx="2" ry="2"></rect>
				<polyline points="17 2 12 7 7 2"></polyline>
			</svg>
		</div>
		<div class="pipe-calculator-content">
			<p class="pipe-calculator-text">
				Trenger du stålpipe til denne peisen?
			</p>
			<a href="<?php echo esc_url( $calculator_url ); ?>" class="pipe-calculator-button" target="_blank" rel="noopener">
				<strong>Klikk her for å gå til vår pipekalkulator</strong>
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<line x1="5" y1="12" x2="19" y2="12"></line>
					<polyline points="12 5 19 12 12 19"></polyline>
				</svg>
			</a>
		</div>
	</div>
	<?php
}
add_action( 'woocommerce_single_product_summary', 'smartvarme_pipe_calculator_link', 40 );
