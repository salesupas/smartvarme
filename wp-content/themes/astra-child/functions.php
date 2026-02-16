<?php

require_once __DIR__ . '/inc/theme-functions.php'; // Theme functions
require_once __DIR__ . '/inc/theme-enqueue.php'; // Enqueue's all the scripts
require_once __DIR__ . '/inc/woocommerce.php'; // Enqueue's all the scripts


// Disables the block editor from managing widgets in the Gutenberg plugin.
add_filter( 'gutenberg_use_widgets_block_editor', '__return_false' );
// Disables the block editor from managing widgets.
add_filter( 'use_widgets_block_editor', '__return_false' );
// Allow shop managers to access Gravity Forms
function wd_gravity_forms_roles() {
	$role = get_role( 'shop_manager' );
	$role->add_cap( 'gform_full_access' );
}
add_action( 'admin_init', 'wd_gravity_forms_roles' );

add_action('wp_enqueue_scripts', 'child_theme_styles', 20);
function child_theme_styles() {
  wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css', array(), wp_get_theme(get_template())->get('Version'));
    wp_enqueue_style('child-style', get_stylesheet_uri(), array('parent-style'));
}

add_filter( 'astra_addon_override_single_product_layout', '__return_false' );

function unimicro_delivery_days_show() {
    global $product;

    if ( ! $product ) {
        return '';
    }
	$stock_html = "";

    
	$delivery_days = get_post_meta($product->get_id(), 'leveringsdager', true);
    $minimum_stock_quantity = get_post_meta($product->get_id(), 'minimum_stock_quantity', true);
    $_stock = get_post_meta($product->get_id(), '_stock', true);
	
	if(empty($delivery_days) || strtolower($delivery_days)=="null"){
		$delivery_days = "";
	}
	if(empty($minimum_stock_quantity) || strtolower($minimum_stock_quantity)=="null"){
		$minimum_stock_quantity = "";
	}
		
	
	if ($_stock <= 0) {	  
			if ($delivery_days > 0) {
				$stock_html = esc_html($delivery_days) . ' dagers leveringstid';
			}else{
				$stock_html = '3 dagers leveringstid';
			}  
	} 
	
    return $stock_html;
}

add_action( 'astra_woo_single_title_after', 'custom_stock_display', 9 );
add_action('woocommerce_after_shop_loop_item', 'custom_stock_display', 20);

function custom_stock_display() {
    global $product;
    
      if ($product->is_type('simple')) {
    if ($product->get_stock_quantity() > 3) {
        echo '<p class="ast-stock-detail">
                <span class="stock in-stock">På lager</span>
              </p>';
    } else if ($product->get_stock_quantity() > 0) {
        echo '<p class="ast-stock-detail">
                <span class="stock in-stock">' . $product->get_stock_quantity() . ' igjen på lager</span>
              </p>';
    }
    else {
         echo '<p class="ast-stock-detail">
             <span class="stock in-stock">' . unimicro_delivery_days_show() . '</span>
              </p>';
    }
      }
}

add_action('pre_get_posts', 'exclude_child_products_from_category');
function exclude_child_products_from_category($q) {
    if (!is_admin() && $q->is_main_query() && is_product_category()) {
        $current_cat = get_queried_object();
        $tax_query = array(
            array(
                'taxonomy'         => 'product_cat',
                'field'            => 'term_id',
                'terms'            => $current_cat->term_id,
                'include_children' => false,
            )
        );
        $q->set('tax_query', $tax_query);
    }
}

add_action('woocommerce_product_meta_end', 'add_custom_contact_form_with_button');

function add_custom_contact_form_with_button() {
    ?>
    <div class="contact-from-wrapper">
        <div class="contact-trigger-wrapper">
            <button id="contact-trigger-button" type="button" class="contact-circle-button">
               <img class="question-icon" src="https://smartvarme.no/wp-content/uploads/2025/07/contact_icon.svg" />
            </button>
        </div>
        
        <div id="contact-dropdown" style="display:none; margin-top: 15px;">
            <div class="maksimer_gf_button_wrapper">
                <button id="contact-form-button" type="button">Spørsmål om produktet?</button>
            </div>
            <div id="contact-form-container" style="display:none; margin-top: 15px;">
                <?php echo do_shortcode('[formidable id="6"]'); ?>
            </div>
        
            <div class="maksimer_gf_button_wrapper" style="margin-top:24px;">
                <button id="contact-form-button2" type="button">Ring meg innen 1 time åpningstiden</button>
            </div>
            <div id="contact-form-container2" style="display:none; margin-top: 15px;">
             <?php echo do_shortcode('[formidable id="11"]'); ?>
            </div>
        </div>
    </div>

    <script>
        jQuery(document).ready(function($){
            $('#contact-trigger-button').on('click', function(){
                $('#contact-dropdown').slideToggle();
            });
            
            $('#contact-form-button').on('click', function(){
                $('#contact-form-container').slideToggle();
            });
            $('#contact-form-button2').on('click', function(){
                $('#contact-form-container2').slideToggle();
            });
        });
    </script>
    <?php
}


// Add FiboSearch to the mobile header
add_action( 'wp_footer', function() { ?>
	<script>
		jQuery(document).ready(function(){
			moveDivOnResize();
		});

		function moveDivOnResize() {
			const fibosearch = jQuery('.ast-main-header-wrap .dgwt-wcas-search-wrapp').first();
			const mobile_header = jQuery('.ast-mobile-header-wrap .site-header-primary-section-right');

			if (jQuery(window).width() < 921 && !mobile_header.find('.dgwt-wcas-search-wrapp').length) {
				mobile_header.append(fibosearch);
			} else if ( jQuery(window).width() >= 921  && !jQuery('[data-section="dgwt_wcas_ajax_search-3"]').find('.dgwt-wcas-search-wrapp').length ) {
				jQuery('[data-item-id="dgwt_wcas_ajax_search-3"]').append(fibosearch);
			}
		}

		jQuery(window).on('resize', moveDivOnResize);
	</script>
<?php }, 9999 );

//add_filter('woocommerce_shipping_calculator_enable_city', '__return_false');
//add_filter( 'woocommerce_shipping_calculator_enable_country', '__return_false' ); 

add_action('wp_footer', function() {
    if (is_cart()) {
        $shipping_postcode = WC()->customer->get_shipping_postcode();
        ?>
        <script>
        (function($){
            function isPostcodeValid() {
                var postcode = <?php echo json_encode($shipping_postcode); ?>;
                return postcode && postcode.length > 0;
            }

            function toggleCheckoutButton() {
                var $checkoutBtn = $('.checkout-button');
                  $checkoutBtn.addClass('show');
                
                if (isPostcodeValid()) {
                    $checkoutBtn.prop('disabled', false).removeClass('disabled');
                } else {
                    $checkoutBtn.prop('disabled', true).addClass('disabled');
                }
            }

            $(document).ready(function(){
                toggleCheckoutButton();
                $(document.body).on('updated_cart_totals', function() {
                    location.reload();
                });
            });
        })(jQuery);
        </script>
        <?php
    }
});

add_action('wp_footer', function() {
    if (!is_checkout()) {
        return;
    }

    $shipping_postcode = WC()->customer ? WC()->customer->get_shipping_postcode() : '';

    if (!empty($shipping_postcode)) {
        return;
    }
    ?>
    <div id="postcode-modal-bg">
        <div id="postcode-modal">
            <h2>Postnummer mangler</h2>
            <p>Vennligst oppgi postnummer i handlekurven før du går videre til kassen.</p>
            <button class="button" id="postcode-modal-btn">Gå til handlekurv</button>
        </div>
    </div>
    <script>
        document.getElementById('postcode-modal-btn').addEventListener('click', function() {
            window.location.href = '/handlekurv';
        });
    </script>
    <?php
});

add_action('wp_footer', function() {
  if (is_checkout()) {
    $postal_code = WC()->customer->get_shipping_postcode();
    if (!empty($postal_code)) : ?>
      <script>
        function setPostalCode() {
          var postalInput = document.getElementById('registrationManualPostalCode');
          if(postalInput && !postalInput.value) {
            postalInput.value = '<?php echo esc_js($postal_code); ?>';
            var event = new Event('change', { bubbles: true });
            postalInput.dispatchEvent(event);
            return true;
          }
          return false;
        }
        
        var attempts = 0;
        var maxAttempts = 20;
        var interval = setInterval(function() {
          if (setPostalCode() || attempts >= maxAttempts) {
            clearInterval(interval);
          }
          attempts++;
        }, 200);
        
        document.addEventListener('DOMContentLoaded', setPostalCode);
        
        window.addEventListener('load', setPostalCode);
      </script>
    <?php endif;
  }
});

add_action('woocommerce_proceed_to_checkout', 'add_text_above_proceed_to_checkout_button');
function add_text_above_proceed_to_checkout_button() {
     $postal_code = WC()->customer->get_shipping_postcode();
    if (empty($postal_code)) {
    echo '<p style="margin-bottom:10px; text-align: center; color: #c50000; font-weight:bold;">Beregn frakt for å fortsette</p>';
    }
}

add_filter( 'formatted_woocommerce_price', 'custom_formatted_price', 10, 5 );

function custom_formatted_price( $formatted_price, $price, $decimals, $decimal_separator, $thousand_separator ) {
    $suffix = $decimal_separator . '00';
    if ( substr( $formatted_price, -strlen( $suffix ) ) === $suffix ) {
        $formatted_price = substr( $formatted_price, 0, -strlen( $suffix ) );
    }
    return $formatted_price;
}

add_filter( 'woocommerce_order_button_text', 'change_text_order_button' );
function change_text_order_button() {
    return 'Fullfør bestilling'; 
}

add_filter( 'dgwt/wcas/product/thumbnail_src', function($src, $product_id) {
    $thumbnail_url = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'medium' );
    if ( is_array( $thumbnail_url ) && !empty( $thumbnail_url[0] ) ) {
        $src = $thumbnail_url[0];
    }
    return $src;
}, 10, 2);




