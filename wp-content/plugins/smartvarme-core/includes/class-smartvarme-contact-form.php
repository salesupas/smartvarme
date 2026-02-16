<?php
/**
 * Contact Form Integration
 *
 * Adds product inquiry form to WooCommerce single product pages.
 * Works with Formidable Forms for form rendering.
 *
 * @package Smartvarme_Core
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Smartvarme_Contact_Form {

	/**
	 * Constructor - register hooks
	 */
	public function __construct() {
		// Product inquiry form removed per user request
		// No longer displaying contact form on product pages
	}

	/**
	 * Display product inquiry form on single product pages
	 */
	public function display_product_inquiry_form() {
		// Only if Formidable Forms is active
		if ( ! class_exists( 'FrmForm' ) && ! function_exists( 'FrmFormsController::get_form_shortcode' ) ) {
			return;
		}

		global $product, $wpdb;
		if ( ! $product ) {
			return;
		}

		// Get "Spørsmål om produktet" form (ID 6) or first available form
		$form_id = 6; // Default to "Spørsmål om produktet" form

		// Verify form exists
		$form_exists = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM {$wpdb->prefix}frm_forms WHERE id = %d AND status = 'published'",
			$form_id
		) );

		// If form 6 doesn't exist, get first published form
		if ( ! $form_exists ) {
			$form_id = $wpdb->get_var(
				"SELECT id FROM {$wpdb->prefix}frm_forms WHERE status = 'published' ORDER BY id ASC LIMIT 1"
			);
		}

		?>
		<div class="product-inquiry-wrapper" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>" data-product-name="<?php echo esc_attr( $product->get_name() ); ?>" data-product-url="<?php echo esc_url( get_permalink() ); ?>">
			<h3 class="product-inquiry-title">
				<?php esc_html_e( 'Har du spørsmål om dette produktet?', 'smartvarme-core' ); ?>
			</h3>
			<p class="product-inquiry-subtitle">
				<?php esc_html_e( 'Våre eksperter hjelper deg gjerne. Fyll ut skjemaet nedenfor.', 'smartvarme-core' ); ?>
			</p>
			<?php
			if ( $form_id ) {
				echo do_shortcode( '[formidable id=' . absint( $form_id ) . ']' );
			} else {
				// Fallback: simple mailto link if no form exists
				$subject = rawurlencode( 'Spørsmål om ' . $product->get_name() );
				$body    = rawurlencode( 'Produktlenke: ' . get_permalink() );
				echo '<p><a href="mailto:post@smartvarme.no?subject=' . $subject . '&body=' . $body . '" class="wp-element-button">';
				esc_html_e( 'Send oss en e-post', 'smartvarme-core' );
				echo '</a></p>';
			}
			?>
		</div>
		<?php
	}

	/**
	 * Inject JavaScript to add product context to form submissions
	 */
	public function inject_product_context_script() {
		if ( ! is_product() || ! class_exists( 'FrmForm' ) ) {
			return;
		}

		global $product;
		if ( ! $product ) {
			return;
		}
		?>
		<script>
		document.addEventListener('DOMContentLoaded', function() {
			var wrapper = document.querySelector('.product-inquiry-wrapper');
			if (!wrapper) return;

			var form = wrapper.querySelector('form.frm-show-form');
			if (!form) return;

			// Add hidden fields for product context
			var fields = [
				{ name: 'product_name', value: wrapper.dataset.productName || '' },
				{ name: 'product_id', value: wrapper.dataset.productId || '' },
				{ name: 'product_url', value: window.location.href }
			];

			fields.forEach(function(field) {
				var input = document.createElement('input');
				input.type = 'hidden';
				input.name = field.name;
				input.value = field.value;
				form.appendChild(input);
			});
		});
		</script>
		<?php
	}
}
