<?php
/**
 * Performance optimization module
 *
 * Handles code-based performance optimizations:
 * - Conditional asset loading per page type
 * - Lazy loading control for LCP elements
 * - Autoload size monitoring
 * - Transient cleanup automation
 * - WordPress bloat removal
 *
 * @package Smartvarme_Core
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Smartvarme_Performance class
 */
class Smartvarme_Performance {

	/**
	 * Image counter for lazy loading control
	 *
	 * @var int
	 */
	private static $image_count = 0;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Conditional asset loading
		add_action( 'wp_enqueue_scripts', array( $this, 'optimize_asset_loading' ), 100 );

		// Lazy loading control
		add_filter( 'wp_lazy_loading_enabled', array( $this, 'control_lazy_loading' ), 10, 3 );
		add_filter( 'wp_img_tag_add_decoding_attr', array( $this, 'add_async_decoding' ), 10, 3 );

		// Preload LCP images
		add_action( 'wp_head', array( $this, 'preload_lcp_image' ), 1 );

		// Autoload monitoring
		add_action( 'admin_notices', array( $this, 'monitor_autoload_size' ) );

		// Transient cleanup
		add_action( 'init', array( $this, 'schedule_transient_cleanup' ) );
		add_action( 'smartvarme_transient_cleanup', array( $this, 'cleanup_transients' ) );

		// Remove WordPress bloat
		add_action( 'init', array( $this, 'remove_bloat' ) );

		// WP Rocket integration
		$this->configure_wp_rocket();

		// Image optimization (WebP output)
		$this->configure_image_optimization();
	}

	/**
	 * Optimize asset loading per page type
	 *
	 * Dequeue unnecessary scripts/styles on non-relevant pages
	 */
	public function optimize_asset_loading() {
		// Check if WooCommerce functions exist
		$is_woocommerce_page = false;
		if ( function_exists( 'is_woocommerce' ) && function_exists( 'is_cart' ) && function_exists( 'is_checkout' ) && function_exists( 'is_account_page' ) ) {
			$is_woocommerce_page = is_woocommerce() || is_cart() || is_checkout() || is_account_page();
		}

		// Dequeue WooCommerce assets on non-shop pages
		if ( ! $is_woocommerce_page ) {
			// Styles
			wp_dequeue_style( 'woocommerce-general' );
			wp_dequeue_style( 'woocommerce-layout' );
			wp_dequeue_style( 'woocommerce-smallscreen' );
			// DO NOT dequeue wc-blocks-style - needed for cart/checkout blocks

			// Scripts
			// Note: Keep wc-cart-fragments if mini-cart is in header (needed for cart count updates)
			// For now, we'll dequeue it on non-shop pages to reduce overhead
			wp_dequeue_script( 'wc-cart-fragments' );
			wp_dequeue_script( 'woocommerce' );
			wp_dequeue_script( 'wc-add-to-cart' );
		}

		// Check if page has forms (product pages, contact page)
		$has_form = is_singular( 'product' ) || is_page( 'contact' ) || is_page( 'kontakt' );

		// Dequeue Formidable Forms on pages without forms
		if ( ! $has_form ) {
			wp_dequeue_style( 'formidable' );
			wp_dequeue_script( 'formidable' );
		}

		// Note: Search script must load on all pages since search form is in header
		// Do not dequeue smartvarme-smart-search
	}

	/**
	 * Control lazy loading to exclude LCP elements
	 *
	 * @param bool   $default Whether to add lazy loading attribute.
	 * @param string $tag_name The tag name.
	 * @param string $context Additional context.
	 * @return bool
	 */
	public function control_lazy_loading( $default, $tag_name, $context ) {
		// Only process images in content
		if ( 'img' !== $tag_name || 'the_content' !== $context ) {
			return $default;
		}

		// Increment counter
		self::$image_count++;

		// Disable lazy loading for first image (likely LCP element)
		if ( 1 === self::$image_count ) {
			return false;
		}

		return $default;
	}

	/**
	 * Add async decoding to below-fold images
	 *
	 * @param string $value The decoding attribute value.
	 * @param string $image The HTML img tag.
	 * @param string $context Additional context.
	 * @return string
	 */
	public function add_async_decoding( $value, $image, $context ) {
		// Add async decoding for images after the first one
		if ( self::$image_count > 1 ) {
			return 'async';
		}

		return $value;
	}

	/**
	 * Preload LCP image for faster rendering
	 */
	public function preload_lcp_image() {
		// On single product pages, preload featured image
		if ( is_singular( 'product' ) ) {
			global $post;
			if ( $post && has_post_thumbnail( $post->ID ) ) {
				$image_id = get_post_thumbnail_id( $post->ID );
				$image_src = wp_get_attachment_image_src( $image_id, 'large' );

				if ( $image_src ) {
					echo '<link rel="preload" as="image" href="' . esc_url( $image_src[0] ) . '" fetchpriority="high">' . "\n";
				}
			}
		}

		// On homepage, preload first content image if identifiable
		if ( is_front_page() && has_post_thumbnail() ) {
			$image_id = get_post_thumbnail_id();
			$image_src = wp_get_attachment_image_src( $image_id, 'large' );

			if ( $image_src ) {
				echo '<link rel="preload" as="image" href="' . esc_url( $image_src[0] ) . '" fetchpriority="high">' . "\n";
			}
		}
	}

	/**
	 * Monitor autoload size and display admin notice if too large
	 */
	public function monitor_autoload_size() {
		// Only check once per day
		$last_check = get_transient( 'smartvarme_autoload_check' );
		if ( false !== $last_check ) {
			return;
		}

		// Set transient for 24 hours
		set_transient( 'smartvarme_autoload_check', time(), DAY_IN_SECONDS );

		// Query autoload size
		global $wpdb;
		$autoload_size = $wpdb->get_var(
			"SELECT SUM(LENGTH(option_value))
			 FROM {$wpdb->options}
			 WHERE autoload = 'yes'"
		);

		// Convert to KB
		$size_kb = round( $autoload_size / 1024, 2 );

		// Store size
		update_option( 'smartvarme_autoload_size_kb', $size_kb, false );

		// Display notice if over threshold
		if ( $size_kb > 800 ) {
			$class = $size_kb > 1024 ? 'error' : 'warning';
			?>
			<div class="notice notice-<?php echo esc_attr( $class ); ?>">
				<p>
					<strong>Performance Notice:</strong>
					Autoload size is <?php echo esc_html( $size_kb ); ?> KB (target: &lt; 800 KB).
					Consider running database optimization or reviewing plugins that add autoload data.
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Schedule transient cleanup cron
	 */
	public function schedule_transient_cleanup() {
		// Schedule weekly cleanup if not already scheduled
		if ( ! wp_next_scheduled( 'smartvarme_transient_cleanup' ) ) {
			wp_schedule_event( time(), 'weekly', 'smartvarme_transient_cleanup' );
		}
	}

	/**
	 * Clean up expired transients
	 */
	public function cleanup_transients() {
		global $wpdb;

		// Delete expired transients
		$time = time();
		$expired = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options}
				 WHERE option_name LIKE %s
				 AND option_value < %d",
				$wpdb->esc_like( '_transient_timeout_' ) . '%',
				$time
			)
		);

		// Delete orphaned transient values
		$orphaned = $wpdb->query(
			"DELETE FROM {$wpdb->options}
			 WHERE option_name LIKE '_transient_%'
			 AND option_name NOT LIKE '_transient_timeout_%'
			 AND option_name NOT IN (
				 SELECT REPLACE(option_name, '_transient_timeout_', '_transient_')
				 FROM {$wpdb->options}
				 WHERE option_name LIKE '_transient_timeout_%'
			 )"
		);

		// Log results
		error_log( sprintf(
			'Smartvarme Transient Cleanup: Deleted %d expired transients and %d orphaned transients',
			(int) $expired,
			(int) $orphaned
		) );
	}

	/**
	 * Remove unnecessary WordPress features for performance
	 */
	public function remove_bloat() {
		// Remove emoji scripts
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

		// Remove RSD link
		remove_action( 'wp_head', 'rsd_link' );

		// Remove wlwmanifest link
		remove_action( 'wp_head', 'wlwmanifest_link' );

		// Remove WordPress version
		remove_action( 'wp_head', 'wp_generator' );

		// Disable XML-RPC
		add_filter( 'xmlrpc_enabled', '__return_false' );

		// Remove oEmbed discovery links
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

		// Remove REST API link from head (keep API functional)
		remove_action( 'wp_head', 'rest_output_link_wp_head' );
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );

		// Disable self-pingbacks
		add_action( 'pre_ping', array( $this, 'disable_self_pingbacks' ) );
	}

	/**
	 * Disable self-pingbacks
	 *
	 * @param array $links Links to ping.
	 */
	public function disable_self_pingbacks( &$links ) {
		$home = get_option( 'home' );
		foreach ( $links as $key => $link ) {
			if ( 0 === strpos( $link, $home ) ) {
				unset( $links[ $key ] );
			}
		}
	}

	/**
	 * Configure WP Rocket integration filters
	 *
	 * Adds cache exclusions for WooCommerce dynamic pages,
	 * JS delay exclusions for critical scripts, and lazy loading exclusions.
	 */
	private function configure_wp_rocket() {
		// Only register WP Rocket filters if the plugin is active
		if ( ! defined( 'WP_ROCKET_VERSION' ) ) {
			return;
		}

		// Cache exclusions for WooCommerce dynamic pages
		add_filter( 'rocket_cache_reject_uri', function( $uris ) {
			$uris[] = '/handlekurv/(.*)';    // Norwegian cart
			$uris[] = '/kasse/(.*)';         // Norwegian checkout
			$uris[] = '/min-konto/(.*)';     // Norwegian my-account
			$uris[] = '/cart/(.*)';          // English fallback
			$uris[] = '/checkout/(.*)';
			$uris[] = '/my-account/(.*)';
			$uris[] = '/order-received/(.*)';
			return $uris;
		} );

		// Exclude critical scripts from JS delay
		add_filter( 'rocket_delay_js_exclusions', function( $exclusions ) {
			$exclusions[] = 'jquery-core';
			$exclusions[] = 'jquery-migrate';
			$exclusions[] = 'jquery';
			$exclusions[] = 'wc-add-to-cart';
			$exclusions[] = 'smartvarme-smart-search';
			$exclusions[] = 'smart-search';
			$exclusions[] = '/wp-admin/admin-ajax.php';
			$exclusions[] = 'admin-ajax';
			$exclusions[] = 'formidable';
			return $exclusions;
		} );

		// Exclude search and jQuery from minification/concatenation
		add_filter( 'rocket_exclude_js', function( $excluded_js ) {
			$excluded_js[] = '/wp-includes/js/jquery/jquery.js';
			$excluded_js[] = '/wp-content/themes/smartvarme-theme/js/smart-search.js';
			return $excluded_js;
		} );

		// Exclude search script from defer (critical for instant search)
		add_filter( 'rocket_exclude_defer_js', function( $excluded_js ) {
			$excluded_js[] = '/wp-content/themes/smartvarme-theme/js/smart-search.js';
			return $excluded_js;
		} );

		// Delay non-essential scripts (analytics, tracking)
		add_filter( 'rocket_delay_js_scripts', function( $scripts ) {
			$scripts[] = 'gtag';
			$scripts[] = 'gtm';
			$scripts[] = 'fbevents';
			$scripts[] = 'google-analytics';
			$scripts[] = 'analytics';
			return $scripts;
		} );

		// Exclude LCP images from WP Rocket lazy loading
		add_filter( 'rocket_lazyload_excluded_attributes', function( $attributes ) {
			$attributes[] = 'data-no-lazy="true"';
			$attributes[] = 'fetchpriority="high"';
			return $attributes;
		} );
	}

	/**
	 * Configure image optimization for WebP output
	 *
	 * Enables WordPress native WebP conversion for uploaded images.
	 * WordPress 6.x includes native WebP support since 5.8+.
	 */
	private function configure_image_optimization() {
		// Enable WebP output for uploaded images (WordPress native)
		add_filter( 'image_editor_output_format', function( $formats ) {
			$formats['image/jpeg'] = 'image/webp';
			$formats['image/png'] = 'image/webp';
			return $formats;
		} );
	}
}
