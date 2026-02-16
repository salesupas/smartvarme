# Phase 4: Cart & Checkout - Research

**Researched:** 2026-02-12
**Domain:** WooCommerce Cart & Checkout blocks, DIBS/Nexi payment gateway integration, transactional emails
**Confidence:** HIGH

## Summary

Phase 4 implements the complete transaction flow from cart to order confirmation using WooCommerce Cart and Checkout blocks (default since WooCommerce 8.3), integrates the existing DIBS Easy/Nexi Checkout payment gateway with blocks support, and configures order confirmation emails in Norwegian. The research reveals that WooCommerce blocks checkout became the default in November 2023, requiring payment gateways to register block-specific integrations alongside traditional shortcode support. Critical findings include the DIBS Easy plugin's blocks support via redirect flow (added in version 2.8.0, February 2024), the necessity to exclude cart/checkout pages from caching for dynamic content updates, and WooCommerce's recent performance optimizations that reduced checkout page load times by up to 95% through smarter caching and asynchronous data loading.

The critical path involves: (1) Implementing Cart and Checkout blocks using WooCommerce's block-based checkout (replacing shortcodes), (2) Verifying DIBS Easy plugin blocks compatibility and configuring test mode for Norwegian market, (3) Excluding dynamic pages (cart, checkout, my-account) from WP Fastest Cache using cookie-based exclusions, (4) Customizing order confirmation emails using WooCommerce hooks with Norwegian language (nb_NO) strings, (5) Testing complete checkout flow with test transactions verifying email delivery, and (6) Monitoring mini-cart AJAX updates and ensuring cache exclusions work correctly for dynamic content.

**Primary recommendation:** Use native WooCommerce Cart and Checkout blocks (default since 8.3), configure DIBS Easy in test mode with redirect flow for blocks checkout, implement cache exclusions for woocommerce_items_in_cart and wp_woocommerce_session cookies in WP Fastest Cache, customize transactional email templates via hooks (not template overrides), test complete transaction flow in staging with DIBS test account, and validate that dynamic cart/checkout content updates without cache interference.

## Standard Stack

### Core
| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| WooCommerce Cart Block | 10.5+ (core) | Shopping cart display | Default cart experience since 8.3; progressive rendering reduces layout shifts, no React dependency with Interactivity API |
| WooCommerce Checkout Block | 10.5+ (core) | Checkout form and payment | Default checkout since 8.3; block-based extensibility, payment gateway integration via registerPaymentMethod |
| DIBS Easy for WooCommerce (Nexi Checkout) | 2.13.1+ | Payment gateway | Already installed; blocks support via redirect flow (2.8.0+), HPOS compatible (2.7.0+), Norwegian market support |
| WooCommerce Transactional Emails | Core | Order confirmation emails | Built-in email system with hooks for customization; 10+ email types including order processing, completed, cancelled |

### Supporting
| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| WP Fastest Cache | Current | Page caching with WooCommerce exclusions | Already installed; automatic cart/checkout exclusions, cookie-based dynamic content handling |
| Loco Translate | Current | Email translation to Norwegian | Already installed; translate WooCommerce email strings to nb_NO without .po/.mo file editing |
| Mini-Cart Block | 10.5+ (core) | Header cart widget | Replaces cart fragments API; no React dependency, better performance, auto-updates via Interactivity API |
| Additional Checkout Fields API | WC 8.0+ (core) | Custom checkout fields | Use woocommerce_register_additional_checkout_field() for delivery notes, installation preferences |

### Alternatives Considered
| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| Checkout blocks | Legacy shortcode checkout | Blocks are default since 8.3; shortcode deprecated path, no new features, worse performance |
| DIBS redirect flow | DIBS embedded/overlay flow | Redirect required for blocks (embedded only works with shortcode); redirect simpler, PCI compliant |
| Hook-based email customization | Template overrides | Hooks upgrade-safe; template overrides break on WooCommerce updates, require manual merging |
| WP Fastest Cache | WP Rocket or W3 Total Cache | WP Fastest Cache already installed and configured; works well with WooCommerce auto-exclusions |
| Additional Checkout Fields API | Checkout Field Editor plugin | API native in WC 8.0+; plugin adds complexity, potential conflicts, extra maintenance |

**Installation:**
```bash
# WooCommerce and DIBS already installed from Phase 3
# Verify DIBS version supports blocks
wp plugin list --name="dibs-easy-for-woocommerce" --fields=name,version,status

# Loco Translate already installed for translations
# WP Fastest Cache already installed for caching

# No additional plugin installations required
# All core functionality available in WooCommerce 10.5+
```

## Architecture Patterns

### Recommended Project Structure
```
wp-content/
├── themes/
│   └── smartvarme-theme/              # From Phase 2
│       ├── patterns/
│       │   ├── cart-empty.php         # Empty cart message pattern
│       │   └── checkout-notice.php    # Checkout notice/banner pattern
│       └── functions.php              # WooCommerce hooks for emails, checkout
└── plugins/
    ├── dibs-easy-for-woocommerce/     # Already installed (2.13.1)
    │   ├── blocks/                    # Block-based checkout support
    │   └── readme.txt                 # Version 2.8.0+ has blocks support
    ├── wp-fastest-cache/              # Already installed
    │   └── (WooCommerce auto-exclusions active)
    └── smartvarme-core/               # From Phase 1
        ├── includes/
        │   ├── woocommerce/
        │   │   ├── checkout-fields.php       # Custom checkout fields
        │   │   ├── email-customization.php   # Email hooks and filters
        │   │   └── cart-validation.php       # Cart item validation
        │   └── emails/                # Email template customization
        │       └── (Use hooks, not template overrides)
        └── languages/
            ├── smartvarme-core-nb_NO.po      # Norwegian translations
            └── smartvarme-core-nb_NO.mo
```

### Pattern 1: Cart Block Implementation
**What:** Replace shortcode [woocommerce_cart] with Cart block for better performance
**When to use:** All cart pages (default since WooCommerce 8.3)
**Example:**
```php
// Source: https://woocommerce.com/document/woocommerce-store-editing/customizing-cart-and-checkout/cart-block/

// Cart block is default for new pages; convert existing shortcode pages:
// 1. Edit Cart page in WordPress admin
// 2. If page uses [woocommerce_cart] shortcode, delete it
// 3. Add "Cart" block from WooCommerce blocks category
// 4. Configure block settings (show shipping calculator, coupon field, etc.)

// Customize cart block appearance via theme.json
// themes/smartvarme-theme/theme.json
{
  "version": 2,
  "settings": {
    "blocks": {
      "woocommerce/cart": {
        "color": {
          "palette": [
            { "name": "Primary", "slug": "primary", "color": "#1e3a8a" }
          ]
        },
        "typography": {
          "fontSize": "16px"
        }
      }
    }
  }
}

// Customize cart totals via hooks (functions.php or smartvarme-core)
add_filter('woocommerce_cart_totals_fee_html', 'smartvarme_format_cart_fee', 10, 2);
function smartvarme_format_cart_fee($fee_html, $fee) {
    // Add custom formatting or tooltip to cart fees
    return $fee_html . ' <span class="fee-info" title="Leveringskostnad">ⓘ</span>';
}

// Customize empty cart message
add_filter('wc_empty_cart_message', 'smartvarme_empty_cart_message');
function smartvarme_empty_cart_message($message) {
    return '<p>Din handlekurv er tom. <a href="/produkter/">Utforsk våre varmepumper</a></p>';
}
```

### Pattern 2: Checkout Block Implementation
**What:** Implement block-based checkout with DIBS payment gateway integration
**When to use:** All checkout flows (required for blocks-compatible payment gateways)
**Example:**
```php
// Source: https://developer.woocommerce.com/docs/block-development/extensible-blocks/cart-and-checkout-blocks/

// Checkout block setup (WooCommerce admin):
// 1. WooCommerce > Settings > Advanced > Features
// 2. Verify "Enable the checkout block" is ON (default since 8.3)
// 3. Pages > Edit Checkout page
// 4. Remove [woocommerce_checkout] shortcode if exists
// 5. Add "Checkout" block from WooCommerce category

// DIBS Easy automatically registers with checkout block (version 2.8.0+)
// Verification: Check WooCommerce > Settings > Payments > Nexi Checkout
// Enable "Redirect" checkout flow (required for blocks compatibility)

// Add custom checkout field (functions.php or smartvarme-core)
add_action('woocommerce_init', 'smartvarme_register_checkout_fields');
function smartvarme_register_checkout_fields() {
    if (!function_exists('woocommerce_register_additional_checkout_field')) {
        return; // WooCommerce 8.0+ required
    }

    // Delivery instructions field
    woocommerce_register_additional_checkout_field([
        'id' => 'smartvarme/delivery-instructions',
        'label' => 'Leveringsinstruksjoner',
        'location' => 'order', // Appears in "Order information" section
        'type' => 'textarea',
        'required' => false,
        'attributes' => [
            'placeholder' => 'F.eks. levering til bakdør, ring før levering',
            'maxlength' => 500,
        ],
    ]);

    // Installation preference (for heat pumps)
    woocommerce_register_additional_checkout_field([
        'id' => 'smartvarme/installation-preference',
        'label' => 'Installasjonsønske',
        'location' => 'order',
        'type' => 'select',
        'required' => false,
        'options' => [
            ['value' => '', 'label' => 'Velg...'],
            ['value' => 'self', 'label' => 'Jeg installerer selv'],
            ['value' => 'contact', 'label' => 'Kontakt meg for installasjon'],
            ['value' => 'later', 'label' => 'Bestiller installasjon senere'],
        ],
    ]);
}

// Display custom fields in order admin and emails
add_action('woocommerce_admin_order_data_after_billing_address', 'smartvarme_display_custom_fields_admin');
function smartvarme_display_custom_fields_admin($order) {
    $delivery_instructions = $order->get_meta('smartvarme/delivery-instructions');
    $installation_pref = $order->get_meta('smartvarme/installation-preference');

    if ($delivery_instructions) {
        echo '<p><strong>Leveringsinstruksjoner:</strong><br>' . esc_html($delivery_instructions) . '</p>';
    }
    if ($installation_pref) {
        $labels = [
            'self' => 'Jeg installerer selv',
            'contact' => 'Kontakt meg for installasjon',
            'later' => 'Bestiller installasjon senere',
        ];
        echo '<p><strong>Installasjonsønske:</strong> ' . esc_html($labels[$installation_pref] ?? $installation_pref) . '</p>';
    }
}
```

### Pattern 3: DIBS Payment Gateway Blocks Integration
**What:** Configure DIBS Easy/Nexi Checkout for blocks checkout with test mode
**When to use:** Payment gateway setup and testing before production
**Example:**
```php
// Source: https://github.com/krokedil/dibs-easy-for-woocommerce
// Plugin already supports blocks via redirect flow (version 2.8.0+)

// Configuration steps:
// 1. WooCommerce > Settings > Payments > Nexi Checkout > Manage
// 2. Enable test mode: Check "Enable test mode"
// 3. Set checkout flow: Select "Redirect" (required for blocks)
// 4. Enter test credentials from DIBS portal
//    - Test Checkout Key
//    - Test Secret Key
// 5. Set language: "Norwegian (nb-NO)" for Norwegian checkout
// 6. Configure auto-capture (optional): Enable to charge immediately

// DIBS automatically registers with blocks checkout
// Verify in WooCommerce > Settings > Payments that Nexi Checkout shows

// Test mode verification (functions.php or smartvarme-core)
add_action('woocommerce_checkout_before_customer_details', 'smartvarme_dibs_test_notice');
function smartvarme_dibs_test_notice() {
    $dibs_settings = get_option('woocommerce_dibs_easy_settings');
    if (isset($dibs_settings['testmode']) && $dibs_settings['testmode'] === 'yes') {
        echo '<div class="woocommerce-info">';
        echo '<strong>TEST MODUS:</strong> Betalinger behandles i testmiljø. Bruk testkort.';
        echo '</div>';
    }
}

// Test card numbers for DIBS Easy test mode:
// Visa: 4111 1111 1111 1111
// Mastercard: 5555 5555 5555 4444
// CVV: any 3 digits, Expiry: any future date

// DIBS webhook configuration (automatic)
// Plugin automatically registers webhooks unless site is localhost
// Verify in DIBS portal > Webhooks that order status URLs are registered

// HPOS compatibility (already declared in plugin 2.7.0+)
// No additional configuration needed
```

### Pattern 4: Cache Exclusion for Dynamic Pages
**What:** Exclude cart, checkout, my-account pages from WP Fastest Cache
**When to use:** Critical for WooCommerce - prevents stale cart data and checkout issues
**Example:**
```php
// Source: https://www.wpfastestcache.com/tutorial/woocommerce-settings/
// WP Fastest Cache automatically excludes WooCommerce pages

// Verify exclusions (WP Fastest Cache > Settings > Exclude):
// Pages excluded: /cart/, /checkout/, /my-account/
// Query strings excluded: add-to-cart=, remove_item=, wc-ajax=

// Cookies excluded (automatic):
// - woocommerce_cart_hash
// - woocommerce_items_in_cart
// - wp_woocommerce_session_*

// Manual cookie exclusion if needed (functions.php)
add_filter('wpfc_exclude_cookies', 'smartvarme_exclude_woocommerce_cookies');
function smartvarme_exclude_woocommerce_cookies($cookies) {
    // WP Fastest Cache should handle these automatically
    // Add only if experiencing caching issues
    $woo_cookies = [
        'woocommerce_cart_hash',
        'woocommerce_items_in_cart',
        'wp_woocommerce_session',
        'woocommerce_recently_viewed',
    ];
    return array_merge($cookies, $woo_cookies);
}

// Verify cache exclusions work
// Test procedure:
// 1. Clear all cache
// 2. Add product to cart
// 3. Navigate to different pages
// 4. Check mini-cart shows correct count
// 5. Go to cart page - should show added product
// 6. Inspect HTTP headers - cart/checkout should have "Cache-Control: no-cache"

// Debug cache issues (temporary, remove after testing)
add_action('wp_footer', 'smartvarme_debug_cache_headers');
function smartvarme_debug_cache_headers() {
    if (is_cart() || is_checkout() || is_account_page()) {
        echo '<!-- WooCommerce Page: Cache should be disabled -->';
        echo '<!-- Cookies: ' . implode(', ', array_keys($_COOKIE)) . ' -->';
    }
}
```

### Pattern 5: Order Confirmation Email Customization
**What:** Customize WooCommerce transactional emails with Norwegian text and branding
**When to use:** All order-related emails (processing, completed, cancelled)
**Example:**
```php
// Source: https://woocommerce.com/posts/how-to-customize-emails-in-woocommerce/

// Customize email content via hooks (functions.php or smartvarme-core)
// DO NOT copy template files - use hooks for upgrade safety

// Add custom content after order table
add_action('woocommerce_email_after_order_table', 'smartvarme_email_delivery_info', 10, 4);
function smartvarme_email_delivery_info($order, $sent_to_admin, $plain_text, $email) {
    // Only show to customer on "processing" and "completed" emails
    if ($sent_to_admin || !in_array($email->id, ['customer_processing_order', 'customer_completed_order'])) {
        return;
    }

    if ($plain_text) {
        echo "\n\nLEVERINGSINFORMASJON\n";
        echo "Forventet leveringstid: 2-5 virkedager\n";
        echo "Sporingslenke vil bli sendt når pakken er utlevert til transportør.\n";
    } else {
        echo '<h2 style="color: #1e3a8a; margin-top: 30px;">Leveringsinformasjon</h2>';
        echo '<p><strong>Forventet leveringstid:</strong> 2-5 virkedager</p>';
        echo '<p>Sporingslenke vil bli sendt når pakken er utlevert til transportør.</p>';
    }

    // Show custom checkout fields if they exist
    $delivery_instructions = $order->get_meta('smartvarme/delivery-instructions');
    if ($delivery_instructions) {
        if ($plain_text) {
            echo "Dine leveringsinstruksjoner: " . $delivery_instructions . "\n";
        } else {
            echo '<p><strong>Dine leveringsinstruksjoner:</strong><br>' . esc_html($delivery_instructions) . '</p>';
        }
    }
}

// Customize email footer text
add_filter('woocommerce_email_footer_text', 'smartvarme_email_footer_text');
function smartvarme_email_footer_text($footer_text) {
    return 'Takk for at du handler hos Smartvarme. Spørsmål? Kontakt oss på post@smartvarme.no eller 123 45 678.';
}

// Add company logo to email header
add_filter('woocommerce_email_header_image', 'smartvarme_email_logo');
function smartvarme_email_logo($header_image) {
    return get_stylesheet_directory_uri() . '/assets/images/smartvarme-logo-email.png';
}

// Customize email subject lines
add_filter('woocommerce_email_subject_customer_processing_order', 'smartvarme_email_subject_processing', 10, 2);
function smartvarme_email_subject_processing($subject, $order) {
    return sprintf('Takk for din ordre #%s - Smartvarme', $order->get_order_number());
}

add_filter('woocommerce_email_subject_customer_completed_order', 'smartvarme_email_subject_completed', 10, 2);
function smartvarme_email_subject_completed($subject, $order) {
    return sprintf('Din ordre #%s er behandlet - Smartvarme', $order->get_order_number());
}

// Translate email strings via Loco Translate
// Navigate to: Loco Translate > Plugins > WooCommerce
// Edit Norwegian (nb_NO) translation
// Search for email-related strings:
// - "Your order" → "Din ordre"
// - "Order details" → "Ordredetaljer"
// - "Billing address" → "Fakturaadresse"
// - "Shipping address" → "Leveringsadresse"

// Test emails
// WooCommerce > Settings > Emails > [Email Type] > Send test email
// Or use WP Mail Logging plugin to preview all sent emails
```

### Pattern 6: Mini-Cart Block Integration
**What:** Implement Mini-Cart block in header for real-time cart updates
**When to use:** Site header/navigation to show cart contents
**Example:**
```php
// Source: https://developer.woocommerce.com/2023/06/16/best-practices-for-the-use-of-the-cart-fragments-api/

// Mini-Cart block uses Interactivity API (no cart fragments)
// Better performance than legacy widget
// Automatic AJAX updates without cart fragments API

// Add Mini-Cart block to header (FSE theme)
// 1. Appearance > Editor > Header template
// 2. Add "Mini-Cart" block from WooCommerce category
// 3. Configure drawer style (drawer opens on click)
// 4. Customize colors and typography via theme.json

// For classic themes, add via shortcode or PHP
// functions.php or header.php
function smartvarme_header_mini_cart() {
    if (function_exists('woocommerce_mini_cart')) {
        echo '<div class="smartvarme-mini-cart">';
        echo do_blocks('<!-- wp:woocommerce/mini-cart /-->');
        echo '</div>';
    }
}

// Customize mini-cart drawer content
add_filter('woocommerce_widget_cart_item_quantity', 'smartvarme_mini_cart_quantity', 10, 3);
function smartvarme_mini_cart_quantity($quantity, $cart_item, $cart_item_key) {
    // Add product SKU to mini-cart items
    $product = $cart_item['data'];
    if ($product && $product->get_sku()) {
        $quantity .= '<br><small>SKU: ' . esc_html($product->get_sku()) . '</small>';
    }
    return $quantity;
}

// Customize mini-cart empty message
add_filter('woocommerce_mini_cart_empty_message', 'smartvarme_mini_cart_empty');
function smartvarme_mini_cart_empty($message) {
    return 'Din handlekurv er tom. <a href="/produkter/">Se våre produkter</a>';
}

// Mini-cart auto-updates when:
// - Product added to cart via AJAX
// - Quantity changed in mini-cart drawer
// - Product removed from cart
// NO additional JavaScript needed - Interactivity API handles this
```

### Anti-Patterns to Avoid

- **Using shortcode checkout with blocks theme:** Blocks checkout is default since 8.3; shortcode deprecated, lacks new features, worse performance
- **Overriding email template files:** Copy templates to theme breaks on WooCommerce updates; use hooks instead for upgrade-safe customization
- **Caching cart/checkout pages:** Causes stale cart data, checkout failures, payment issues; always exclude from all cache layers
- **DIBS embedded flow with blocks checkout:** Embedded checkout only works with shortcode; blocks require redirect or overlay flow
- **Custom cart fragments for mini-cart:** Deprecated; use Mini-Cart block with Interactivity API for better performance
- **Testing in production without sandbox mode:** Always use payment gateway test/sandbox mode; production testing creates real charges
- **Manual translation of email templates:** Use Loco Translate or .po/.mo files; editing templates directly breaks on updates
- **Skipping cache exclusion testing:** Cache issues appear intermittently; always test add-to-cart, cart updates, checkout flow with cache enabled

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Cart functionality | Custom cart with AJAX | WooCommerce Cart block | Handles taxes, coupons, shipping, stock validation, multi-currency, extensive edge cases |
| Checkout form | Custom checkout fields and validation | WooCommerce Checkout block + Additional Fields API | PCI compliance, payment gateway integration, fraud detection, order processing workflow |
| Payment gateway integration | Custom payment API calls | WooCommerce payment gateway API + DIBS plugin | Security, compliance, webhook handling, order status sync, refund processing |
| Email templates | Custom email sending | WooCommerce transactional emails + hooks | Multi-language, order data integration, retry logic, template versioning, plain text/HTML |
| Cache exclusion | Manual cache bypass scripts | WP Fastest Cache auto-exclusions | Cookie detection, query string handling, page detection, compatibility with multiple cache plugins |
| Mini-cart updates | Custom cart fragments | Mini-Cart block with Interactivity API | Performance optimization, AJAX handling, drawer UI, quantity updates, real-time totals |

**Key insight:** WooCommerce Cart and Checkout blocks are the result of years of e-commerce development handling edge cases like: tax calculation for digital/physical products, coupon stacking rules, shipping method selection based on cart contents, payment gateway tokenization for subscriptions, stock management with concurrent orders, and multi-currency support. Custom implementations inevitably rediscover these edge cases the hard way, often in production when real money is at stake. The Additional Checkout Fields API (WC 8.0+) provides validation, sanitization, and conditional logic built-in, eliminating the need for custom field handling. Payment gateway integration involves PCI compliance, webhook verification, fraud detection, and refund workflows - complexities that payment plugins handle out of the box.

## Common Pitfalls

### Pitfall 1: Cached Cart/Checkout Pages Showing Stale Data
**What goes wrong:** Cart shows wrong item count, checkout displays previous customer's data, payment fails due to cached totals
**Why it happens:** Cache plugin caches dynamic WooCommerce pages that should always be fresh (cart, checkout, my-account)
**How to avoid:**
1. Verify WP Fastest Cache has WooCommerce auto-exclusions enabled (should be default)
2. Check WP Fastest Cache > Exclude > Pages tab shows /cart/, /checkout/, /my-account/
3. Verify cookies are excluded: woocommerce_cart_hash, woocommerce_items_in_cart, wp_woocommerce_session
4. Test add-to-cart from multiple browsers/incognito to verify cart isolation
5. Clear all cache after WooCommerce settings changes
6. Check HTTP headers on cart/checkout: should include "Cache-Control: no-cache" or similar
**Warning signs:**
- Mini-cart shows 0 items after adding product, then correct count after page refresh
- Checkout shows previous test customer's address pre-filled
- Cart totals don't update when quantity changed
- Multiple customers seeing same cart contents
- Payment gateway errors about price mismatch

### Pitfall 2: DIBS Embedded Checkout Not Working with Blocks
**What goes wrong:** DIBS checkout doesn't render, shows error, or redirects to standard checkout
**Why it happens:** DIBS embedded/overlay checkout flows only work with shortcode checkout, not blocks checkout
**How to avoid:**
1. Check DIBS Easy plugin version is 2.8.0+ (blocks support added February 2024)
2. Configure DIBS checkout flow as "Redirect" in WooCommerce > Settings > Payments > Nexi Checkout
3. Verify Checkout page uses Checkout block, not [woocommerce_checkout] shortcode
4. Test checkout flow - should redirect to DIBS hosted page, then return to site
5. DO NOT use "Embedded" or "Overlay" flows with blocks checkout (only works with shortcode)
6. Enable DIBS test mode first, verify redirect flow works before production
**Warning signs:**
- Checkout page blank or shows errors
- Payment button doesn't appear
- Clicking "Place order" does nothing
- Console errors about missing DIBS payment ID
- DIBS settings show "Embedded" flow selected

### Pitfall 3: Order Confirmation Emails Not Sent or Wrong Language
**What goes wrong:** Customers don't receive order emails, emails in English instead of Norwegian, missing order details
**Why it happens:** Email settings not configured, wrong language files, SMTP issues, email template errors
**How to avoid:**
1. Test email functionality: WooCommerce > Settings > Emails > [Email Type] > Send test email
2. Install WP Mail Logging or similar to capture all outgoing emails for debugging
3. Verify WooCommerce language is set to Norwegian (nb_NO) in Settings > General
4. Check Loco Translate has Norwegian translations for WooCommerce email strings
5. Use hooks for email customization, NOT template file overrides
6. Test with real email addresses (not example.com) to avoid spam filters
7. Configure SMTP (WP Mail SMTP plugin) if site emails unreliable
**Warning signs:**
- No emails in customer inbox (check spam folder)
- Emails arrive in English despite Norwegian site
- Order details missing from email body
- Broken layout in email (HTML rendering issues)
- Emails delayed by hours or days
- Test emails work but real order emails don't

### Pitfall 4: Payment Gateway Test Mode Not Enabled in Production
**What goes wrong:** Real credit cards charged during testing, customer data sent to production gateway
**Why it happens:** Forgot to enable test mode in DIBS settings, or switched to production keys too early
**How to avoid:**
1. ALWAYS enable test mode first: WooCommerce > Settings > Payments > Nexi Checkout > Enable test mode
2. Use DIBS test credentials (not production keys) during development
3. Add visual indicator on checkout when test mode active (see Pattern 3)
4. Create DIBS test account: https://portal.dibspayment.eu/test-user-create
5. Test with DIBS test cards (Visa: 4111 1111 1111 1111, Mastercard: 5555 5555 5555 4444)
6. Only disable test mode after full UAT approval and before go-live
7. Document test-to-production checklist with payment gateway settings
**Warning signs:**
- Real charges appearing in DIBS production portal during testing
- Test orders showing real payment IDs
- DIBS webhook errors about invalid test transactions in production
- Customers reporting unexpected charges
- Test mode checkbox unchecked in settings

### Pitfall 5: Custom Checkout Fields Not Saved to Order
**What goes wrong:** Additional checkout fields (delivery instructions, installation preference) don't appear in order confirmation, admin, or emails
**Why it happens:** Using old filter-based approach instead of Additional Checkout Fields API, or incorrect field registration
**How to avoid:**
1. Use woocommerce_register_additional_checkout_field() (WC 8.0+, see Pattern 2)
2. Hook into 'woocommerce_init' action (not 'init' - too early)
3. Set 'location' parameter ('contact', 'address', or 'order') - determines where field data saves
4. Access field data via $order->get_meta('namespace/field-id') - use full ID with namespace
5. Display in emails via woocommerce_email_after_order_table hook
6. Display in admin via woocommerce_admin_order_data_after_billing_address hook
7. Test field saving: place test order, check order meta in database wp_wc_orders_meta table
**Warning signs:**
- Field appears in checkout but not in order confirmation email
- Field data missing in WooCommerce > Orders > [Order] view
- get_meta() returns empty value for custom field
- Field validation works but data doesn't save
- Field shows in one location but not others (email vs admin)

### Pitfall 6: Mini-Cart Not Updating After Add-to-Cart
**What goes wrong:** Mini-cart shows old count/total, doesn't update until page refresh
**Why it happens:** Using legacy cart widget instead of Mini-Cart block, cart fragments disabled, JavaScript errors
**How to avoid:**
1. Use Mini-Cart block (not legacy WooCommerce cart widget)
2. Ensure WooCommerce 7.8+ (cart fragments optimized, not enqueued on all pages)
3. Check browser console for JavaScript errors blocking Interactivity API
4. Verify add-to-cart buttons use WooCommerce standard AJAX (not custom)
5. Test cart updates: add product, check mini-cart updates without refresh
6. If using cart fragments for custom widget, follow WooCommerce best practices (minimize data)
7. Consider Mini-Cart block migration for better performance (no React dependency)
**Warning signs:**
- Mini-cart shows "0 items" after adding product, correct count after refresh
- Cart icon doesn't update until clicking another link
- Console errors about cart fragments or WooCommerce scripts
- Mini-cart drawer doesn't open or shows old contents
- Slow page loads with cart fragments on every page

## Code Examples

Verified patterns from official sources:

### Cart Block Page Setup
```php
// Source: https://woocommerce.com/document/woocommerce-store-editing/customizing-cart-and-checkout/cart-block/

// Create Cart page with Cart block (WordPress admin)
// Pages > Add New > Title: "Handlekurv" (Norwegian for "Cart")
// Add Cart block: Click (+) > Search "Cart" > Select "Cart" from WooCommerce
// Configure block:
// - Settings > Show shipping calculator: ON
// - Settings > Show coupon field: ON
// - Settings > Show item quantity: ON

// Or programmatically create cart page
function smartvarme_create_cart_page() {
    // Check if cart page already exists
    $cart_page_id = wc_get_page_id('cart');
    if ($cart_page_id > 0) {
        return; // Cart page exists
    }

    // Create cart page with Cart block
    $cart_page = array(
        'post_title' => 'Handlekurv',
        'post_content' => '<!-- wp:woocommerce/cart /-->',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_author' => 1,
    );
    $cart_page_id = wp_insert_post($cart_page);

    // Set as WooCommerce cart page
    update_option('woocommerce_cart_page_id', $cart_page_id);
}
```

### Checkout Block Page Setup
```php
// Source: https://developer.woocommerce.com/docs/block-development/extensible-blocks/cart-and-checkout-blocks/

// Create Checkout page with Checkout block
// Pages > Add New > Title: "Kasse" (Norwegian for "Checkout")
// Add Checkout block: Click (+) > Search "Checkout" > Select "Checkout" from WooCommerce
// Configure block:
// - Settings > Show company field: ON (for B2B customers)
// - Settings > Show phone field: ON
// - Settings > Require phone: ON (Norwegian postal service needs contact)

// Or programmatically
function smartvarme_create_checkout_page() {
    $checkout_page_id = wc_get_page_id('checkout');
    if ($checkout_page_id > 0) {
        return;
    }

    $checkout_page = array(
        'post_title' => 'Kasse',
        'post_content' => '<!-- wp:woocommerce/checkout /-->',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_author' => 1,
    );
    $checkout_page_id = wp_insert_post($checkout_page);

    update_option('woocommerce_checkout_page_id', $checkout_page_id);
}
```

### Additional Checkout Fields Registration
```php
// Source: https://developer.woocommerce.com/docs/block-development/extensible-blocks/cart-and-checkout-blocks/additional-checkout-fields/

// Register custom checkout fields (WooCommerce 8.0+)
// Place in smartvarme-core/includes/woocommerce/checkout-fields.php

add_action('woocommerce_init', 'smartvarme_register_additional_checkout_fields');
function smartvarme_register_additional_checkout_fields() {
    if (!function_exists('woocommerce_register_additional_checkout_field')) {
        return; // Requires WooCommerce 8.0+
    }

    // Delivery instructions
    woocommerce_register_additional_checkout_field([
        'id' => 'smartvarme/delivery-instructions',
        'label' => 'Leveringsinstruksjoner (valgfritt)',
        'location' => 'order', // Saved only to order, not customer profile
        'type' => 'textarea',
        'required' => false,
        'attributes' => [
            'placeholder' => 'F.eks. levering til bakdør, ring før levering',
            'maxlength' => 500,
            'rows' => 3,
        ],
    ]);

    // Installation preference for heat pumps
    woocommerce_register_additional_checkout_field([
        'id' => 'smartvarme/installation-preference',
        'label' => 'Installasjonsønske',
        'location' => 'order',
        'type' => 'select',
        'required' => false,
        'options' => [
            ['value' => '', 'label' => 'Velg...'],
            ['value' => 'self', 'label' => 'Jeg installerer selv'],
            ['value' => 'contact', 'label' => 'Kontakt meg for å bestille installasjon'],
            ['value' => 'later', 'label' => 'Jeg bestiller installasjon senere'],
        ],
    ]);

    // Phone number validation (Norwegian format)
    woocommerce_register_additional_checkout_field([
        'id' => 'smartvarme/mobile-phone',
        'label' => 'Mobiltelefon (for SMS-sporing)',
        'location' => 'contact', // Saved to customer profile
        'type' => 'tel',
        'required' => false,
        'attributes' => [
            'pattern' => '[0-9]{8}', // Norwegian mobile: 8 digits
            'placeholder' => '12345678',
        ],
    ]);
}

// Display custom fields in order admin
add_action('woocommerce_admin_order_data_after_billing_address', 'smartvarme_display_custom_fields_admin', 10, 1);
function smartvarme_display_custom_fields_admin($order) {
    $delivery_instructions = $order->get_meta('smartvarme/delivery-instructions');
    $installation_pref = $order->get_meta('smartvarme/installation-preference');
    $mobile_phone = $order->get_meta('smartvarme/mobile-phone');

    if ($delivery_instructions) {
        echo '<p><strong>Leveringsinstruksjoner:</strong><br>';
        echo esc_html($delivery_instructions) . '</p>';
    }

    if ($installation_pref) {
        $labels = [
            'self' => 'Installerer selv',
            'contact' => 'Kontakt for installasjon',
            'later' => 'Bestiller senere',
        ];
        echo '<p><strong>Installasjonsønske:</strong> ';
        echo esc_html($labels[$installation_pref] ?? $installation_pref) . '</p>';
    }

    if ($mobile_phone) {
        echo '<p><strong>Mobiltelefon:</strong> ' . esc_html($mobile_phone) . '</p>';
    }
}

// Display in customer emails
add_action('woocommerce_email_after_order_table', 'smartvarme_email_custom_fields', 10, 4);
function smartvarme_email_custom_fields($order, $sent_to_admin, $plain_text, $email) {
    if ($sent_to_admin) {
        return; // Only show to customer
    }

    $delivery_instructions = $order->get_meta('smartvarme/delivery-instructions');
    $installation_pref = $order->get_meta('smartvarme/installation-preference');

    if (!$delivery_instructions && !$installation_pref) {
        return; // No custom fields
    }

    if ($plain_text) {
        if ($delivery_instructions) {
            echo "\nLeveringsinstruksjoner: " . $delivery_instructions . "\n";
        }
        if ($installation_pref) {
            $labels = [
                'self' => 'Installerer selv',
                'contact' => 'Kontakt for installasjon',
                'later' => 'Bestiller senere',
            ];
            echo "Installasjonsønske: " . ($labels[$installation_pref] ?? $installation_pref) . "\n";
        }
    } else {
        echo '<h2 style="color: #1e3a8a;">Dine ønsker</h2>';
        if ($delivery_instructions) {
            echo '<p><strong>Leveringsinstruksjoner:</strong><br>';
            echo esc_html($delivery_instructions) . '</p>';
        }
        if ($installation_pref) {
            $labels = [
                'self' => 'Installerer selv',
                'contact' => 'Kontakt for installasjon',
                'later' => 'Bestiller senere',
            ];
            echo '<p><strong>Installasjonsønske:</strong> ';
            echo esc_html($labels[$installation_pref] ?? $installation_pref) . '</p>';
        }
    }
}
```

### Order Confirmation Email Customization
```php
// Source: https://woocommerce.com/posts/how-to-customize-emails-in-woocommerce/

// Place in smartvarme-core/includes/woocommerce/email-customization.php

// Customize email header image
add_filter('woocommerce_email_header_image', 'smartvarme_email_header_image');
function smartvarme_email_header_image($header_image) {
    // Use custom logo for emails
    return get_stylesheet_directory_uri() . '/assets/images/smartvarme-logo-email.png';
}

// Customize email colors via WooCommerce settings
// WooCommerce > Settings > Emails
// Base color: #1e3a8a (Smartvarme blue)
// Background color: #f7f7f7
// Body background: #ffffff
// Body text color: #333333

// Add delivery information after order table
add_action('woocommerce_email_after_order_table', 'smartvarme_email_delivery_info', 10, 4);
function smartvarme_email_delivery_info($order, $sent_to_admin, $plain_text, $email) {
    // Only show to customer on processing/completed emails
    if ($sent_to_admin) {
        return;
    }

    if (!in_array($email->id, ['customer_processing_order', 'customer_completed_order'])) {
        return;
    }

    // Get shipping method
    $shipping_methods = $order->get_shipping_methods();
    $shipping_method = reset($shipping_methods);
    $shipping_name = $shipping_method ? $shipping_method->get_name() : '';

    if ($plain_text) {
        echo "\n========================================\n";
        echo "LEVERINGSINFORMASJON\n";
        echo "========================================\n\n";
        echo "Forventet leveringstid: 2-5 virkedager\n";
        if ($shipping_name) {
            echo "Leveringsmetode: " . $shipping_name . "\n";
        }
        echo "\nDu vil motta sporingslenke på e-post når pakken er utlevert til transportør.\n";
        echo "Spørsmål om levering? Kontakt oss på post@smartvarme.no eller ring 123 45 678.\n";
    } else {
        ?>
        <div style="margin-top: 30px; padding: 20px; background-color: #f7f7f7; border-left: 4px solid #1e3a8a;">
            <h2 style="color: #1e3a8a; margin-top: 0;">📦 Leveringsinformasjon</h2>
            <p><strong>Forventet leveringstid:</strong> 2-5 virkedager</p>
            <?php if ($shipping_name): ?>
                <p><strong>Leveringsmetode:</strong> <?php echo esc_html($shipping_name); ?></p>
            <?php endif; ?>
            <p>Du vil motta sporingslenke på e-post når pakken er utlevert til transportør.</p>
            <p style="margin-bottom: 0;">
                <strong>Spørsmål om levering?</strong><br>
                Kontakt oss på <a href="mailto:post@smartvarme.no">post@smartvarme.no</a> eller ring 123 45 678.
            </p>
        </div>
        <?php
    }
}

// Customize email subject lines
add_filter('woocommerce_email_subject_customer_processing_order', 'smartvarme_email_subject_processing', 10, 2);
function smartvarme_email_subject_processing($subject, $order) {
    return sprintf('Takk for din ordre #%s hos Smartvarme', $order->get_order_number());
}

add_filter('woocommerce_email_subject_customer_completed_order', 'smartvarme_email_subject_completed', 10, 2);
function smartvarme_email_subject_completed($subject, $order) {
    return sprintf('Din ordre #%s er behandlet og sendt - Smartvarme', $order->get_order_number());
}

// Customize email footer text
add_filter('woocommerce_email_footer_text', 'smartvarme_email_footer_text');
function smartvarme_email_footer_text($footer_text) {
    return 'Takk for at du handler hos Smartvarme – din partner for energieffektive varmepumper.';
}

// Add payment method details to email
add_action('woocommerce_email_before_order_table', 'smartvarme_email_payment_method', 10, 4);
function smartvarme_email_payment_method($order, $sent_to_admin, $plain_text, $email) {
    if ($sent_to_admin || $email->id !== 'customer_processing_order') {
        return;
    }

    $payment_method = $order->get_payment_method_title();

    if ($plain_text) {
        echo "Betalingsmetode: " . $payment_method . "\n\n";
    } else {
        echo '<p style="margin-bottom: 20px;"><strong>Betalingsmetode:</strong> ' . esc_html($payment_method) . '</p>';
    }
}
```

### WP Fastest Cache Configuration Verification
```php
// Source: https://www.wpfastestcache.com/tutorial/woocommerce-settings/

// Verify WP Fastest Cache excludes WooCommerce pages
// Place in smartvarme-core/includes/woocommerce/cache-validation.php

// Admin notice if cache exclusions not configured correctly
add_action('admin_notices', 'smartvarme_cache_exclusion_notice');
function smartvarme_cache_exclusion_notice() {
    // Only show to administrators
    if (!current_user_can('manage_options')) {
        return;
    }

    // Check if WP Fastest Cache is active
    if (!defined('WPFC_MAIN_PATH')) {
        return;
    }

    // Get WP Fastest Cache options
    $wpfc_options = get_option('WpFastestCache');

    // Check if WooCommerce pages are excluded
    // WP Fastest Cache should auto-exclude these, but verify
    $required_exclusions = ['/cart/', '/checkout/', '/my-account/'];
    $wpfc_exclude_pages = isset($wpfc_options['wpfc_exclude_pages']) ? $wpfc_options['wpfc_exclude_pages'] : [];

    $missing_exclusions = [];
    foreach ($required_exclusions as $page) {
        $found = false;
        if (is_array($wpfc_exclude_pages)) {
            foreach ($wpfc_exclude_pages as $excluded) {
                if (strpos($excluded, $page) !== false) {
                    $found = true;
                    break;
                }
            }
        }
        if (!$found) {
            $missing_exclusions[] = $page;
        }
    }

    if (!empty($missing_exclusions)) {
        ?>
        <div class="notice notice-warning">
            <p><strong>Smartvarme Advarsel:</strong> WP Fastest Cache mangler WooCommerce-ekskluderinger:</p>
            <ul>
                <?php foreach ($missing_exclusions as $page): ?>
                    <li><code><?php echo esc_html($page); ?></code></li>
                <?php endforeach; ?>
            </ul>
            <p>Gå til <strong>WP Fastest Cache > Exclude</strong> og legg til disse sidene manuelt.</p>
        </div>
        <?php
    }
}

// Test cache exclusions (add to cart test)
// Access via: /wp-admin/admin.php?page=smartvarme-cache-test
add_action('admin_menu', 'smartvarme_cache_test_menu');
function smartvarme_cache_test_menu() {
    add_submenu_page(
        'woocommerce',
        'Cache Test',
        'Cache Test',
        'manage_options',
        'smartvarme-cache-test',
        'smartvarme_cache_test_page'
    );
}

function smartvarme_cache_test_page() {
    ?>
    <div class="wrap">
        <h1>WooCommerce Cache Test</h1>
        <p>Test om handlekurv og kasse er korrekt ekskludert fra cache.</p>

        <h2>Test 1: Cookie Detection</h2>
        <p>Følgende WooCommerce-cookies skal være satt når du har produkter i handlekurven:</p>
        <ul>
            <li><code>woocommerce_items_in_cart</code>: <?php echo isset($_COOKIE['woocommerce_items_in_cart']) ? '✅ Satt' : '❌ Ikke satt'; ?></li>
            <li><code>woocommerce_cart_hash</code>: <?php echo isset($_COOKIE['woocommerce_cart_hash']) ? '✅ Satt' : '❌ Ikke satt'; ?></li>
            <li><code>wp_woocommerce_session_*</code>: <?php
                $session_cookie = false;
                foreach ($_COOKIE as $key => $value) {
                    if (strpos($key, 'wp_woocommerce_session_') === 0) {
                        $session_cookie = true;
                        break;
                    }
                }
                echo $session_cookie ? '✅ Satt' : '❌ Ikke satt';
            ?></li>
        </ul>

        <h2>Test 2: Page Cache Headers</h2>
        <p>Sjekk om handlekurv og kasse har korrekte cache-headere:</p>
        <ul>
            <li>Handlekurv: <a href="<?php echo wc_get_cart_url(); ?>" target="_blank">Test handlekurv</a></li>
            <li>Kasse: <a href="<?php echo wc_get_checkout_url(); ?>" target="_blank">Test kasse</a></li>
            <li>Min konto: <a href="<?php echo wc_get_page_permalink('myaccount'); ?>" target="_blank">Test min konto</a></li>
        </ul>
        <p><em>Åpne Network-fanen i utviklerverktøy og sjekk Response Headers for disse sidene. De skal ha "Cache-Control: no-cache" eller lignende.</em></p>

        <h2>Test 3: Mini-Cart Update Test</h2>
        <ol>
            <li>Tøm handlekurven</li>
            <li>Tøm all cache (WP Fastest Cache > Delete Cache)</li>
            <li>Gå til produktside og legg til produkt i handlekurv</li>
            <li>Sjekk om mini-cart oppdateres umiddelbart (uten sideoppdatering)</li>
            <li>Naviger til en annen side - mini-cart skal fortsatt vise riktig antall</li>
        </ol>

        <h2>Test 4: Full Checkout Test</h2>
        <ol>
            <li>Legg til produkt i handlekurv</li>
            <li>Gå til handlekurv - sjekk at produkt vises</li>
            <li>Gå til kasse - sjekk at totaler stemmer</li>
            <li>Fullfør testordre (bruk DIBS testmodus)</li>
            <li>Sjekk at e-postbekreftelse mottas</li>
        </ol>
    </div>
    <?php
}
```

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| Shortcode cart/checkout | Cart & Checkout blocks | WooCommerce 8.3 (Nov 2023) | Default for new stores; better performance, block-based customization, progressive rendering |
| Cart fragments API for all widgets | Interactivity API for Mini-Cart | WooCommerce 7.8 (Jun 2023) | Cart fragments only load when needed; Mini-Cart uses Interactivity API with no React, better performance |
| Custom checkout fields via filters | Additional Checkout Fields API | WooCommerce 8.0 (Mar 2023) | Native validation, sanitization, conditional logic, saved to customer profile or order |
| Template overrides for emails | Hooks-based email customization | Best practice since WC 3.0 | Template overrides break on updates; hooks upgrade-safe, easier to maintain |
| DIBS shortcode-only checkout | DIBS blocks redirect flow | DIBS plugin 2.8.0 (Feb 2024) | Blocks checkout support via redirect; embedded/overlay only work with shortcode |
| Manual cache exclusions | Auto-exclusions in cache plugins | Ongoing improvement | WP Fastest Cache, WP Rocket auto-detect WooCommerce pages and cookies |
| Payment gateway server-side only | Block-based client + server | WooCommerce 5.6+ (Aug 2021) | registerPaymentMethod() client-side, AbstractPaymentMethodType server-side for blocks |

**Deprecated/outdated:**
- **Shortcode cart/checkout ([woocommerce_cart], [woocommerce_checkout])**: Deprecated in favor of blocks; still works but lacks new features, worse performance
- **Cart fragments API for custom widgets**: Use Mini-Cart block with Interactivity API; cart fragments enqueued on all pages (performance issue)
- **woocommerce_checkout_fields filter for complex fields**: Use Additional Checkout Fields API (WC 8.0+) for native validation and conditional logic
- **DIBS embedded checkout with blocks**: Only works with shortcode; blocks checkout requires redirect or overlay flow
- **Direct email template file editing**: Templates break on WooCommerce updates; use hooks (woocommerce_email_* actions/filters)

## Open Questions

1. **How many products in average order? Does cart need quantity selector or simple add/remove?**
   - What we know: Heat pump products likely 1-2 items per order (high-value products)
   - What's unclear: Whether customers order accessories, spare parts, or bundles requiring quantity changes
   - Recommendation: Enable quantity selector in cart block; heat pump customers may order multiple units for larger buildings

2. **What additional checkout fields are required beyond delivery instructions and installation preference?**
   - What we know: Delivery instructions and installation preference identified (see Pattern 2)
   - What's unclear: Whether B2B customers need organization number, tax ID, or project reference fields
   - Recommendation: Review current site checkout form, identify all custom fields, map to Additional Checkout Fields API

3. **Are there any WooCommerce order status customizations needed for installation workflow?**
   - What we know: Standard WooCommerce statuses (pending, processing, completed, cancelled)
   - What's unclear: Whether installation orders need custom statuses like "awaiting installation", "installation scheduled"
   - Recommendation: Use standard statuses initially; add custom statuses only if installation tracking needed (can be Phase 5)

4. **What email templates need Norwegian translation beyond order confirmation?**
   - What we know: Order processing, completed, cancelled emails are standard
   - What's unclear: Whether customer invoice emails, refund emails, or account emails need customization
   - Recommendation: Translate all 10 WooCommerce email types to Norwegian via Loco Translate; customize only order-related emails with hooks

5. **Does DIBS/Nexi require specific checkout field validation (e.g., Norwegian postal codes, phone format)?**
   - What we know: DIBS plugin handles payment data validation
   - What's unclear: Whether Norwegian postal code format (4 digits) or mobile phone format (8 digits) need custom validation
   - Recommendation: Test DIBS checkout with Norwegian addresses; add validation only if DIBS API rejects Norwegian formats

6. **Are subscription products or recurring payments needed for heat pump maintenance contracts?**
   - What we know: WooCommerce Subscriptions not mentioned in plugin list
   - What's unclear: Whether maintenance contracts, warranty extensions, or service plans use subscriptions
   - Recommendation: Subscriptions are complex; defer to future phase if not critical for launch

## Sources

### Primary (HIGH confidence)
- [WooCommerce Developer Docs: Cart and Checkout Blocks](https://developer.woocommerce.com/docs/block-development/extensible-blocks/cart-and-checkout-blocks/) - Block architecture and extensibility
- [WooCommerce Developer Docs: Additional Checkout Fields](https://developer.woocommerce.com/docs/block-development/extensible-blocks/cart-and-checkout-blocks/additional-checkout-fields/) - Custom field registration API
- [WooCommerce Developer Docs: Payment Method Integration](https://developer.woocommerce.com/docs/block-development/extensible-blocks/cart-and-checkout-blocks/checkout-payment-methods/payment-method-integration/) - Payment gateway blocks integration
- [WooCommerce Developer Docs: Configuring Caching Plugins](https://developer.woocommerce.com/docs/best-practices/performance/configuring-caching-plugins/) - Cache exclusion requirements
- [WooCommerce: How to Customize Emails](https://woocommerce.com/posts/how-to-customize-emails-in-woocommerce/) - Email customization via hooks
- [DIBS Easy for WooCommerce: Plugin Readme](file:///wp-content/plugins/dibs-easy-for-woocommerce/readme.txt) - Plugin version 2.13.1, blocks support confirmed
- [WP Fastest Cache: WooCommerce Settings](https://www.wpfastestcache.com/tutorial/woocommerce-settings/) - WooCommerce auto-exclusions

### Secondary (MEDIUM confidence)
- [Essential Blocks: WooCommerce Checkout Flow Optimization Guide (2026)](https://essential-blocks.com/woocommerce-checkout-flow-optimization-guide) - Performance best practices
- [WooCommerce Developer Blog: Improving Performance at Scale](https://developer.woocommerce.com/2025/10/01/improving-woocommerce-performance-at-scale/) - Recent performance improvements
- [WooCommerce Developer Blog: Best Practices for Cart Fragments API](https://developer.woocommerce.com/2023/06/16/best-practices-for-the-use-of-the-cart-fragments-api/) - Mini-cart performance
- [Krokedil Documentation: Nexi Checkout](https://docs.krokedil.com/nets-easy-for-woocommerce/) - DIBS setup guide
- [InstaWP: How to Test WooCommerce Checkout (2026)](https://instawp.com/how-to-test-woocommerce-checkout/) - Testing best practices
- [WooCommerce: Testing Orders](https://woocommerce.com/document/managing-orders/testing-orders/) - Test order procedures

### Tertiary (LOW confidence)
- Various blog posts on WooCommerce email customization and checkout optimization - Used for supplementary examples

## Metadata

**Confidence breakdown:**
- Cart & Checkout blocks implementation: HIGH - Official WooCommerce docs, confirmed default since 8.3, verified in 10.5
- DIBS/Nexi payment gateway integration: HIGH - Plugin installed locally (2.13.1), readme confirms blocks support (2.8.0+), HPOS compatible (2.7.0+)
- Cache exclusion configuration: HIGH - Official WooCommerce caching guide, WP Fastest Cache auto-exclusions documented
- Email customization: HIGH - Official WooCommerce email customization guide, hook references verified
- Norwegian language support: MEDIUM - General WooCommerce nb_NO support confirmed, DIBS plugin supports Norwegian, specific email translations need verification
- Performance optimization: MEDIUM-HIGH - Recent WooCommerce performance improvements documented (10.1, 10.2), Interactivity API for Mini-Cart confirmed

**Research date:** 2026-02-12
**Valid until:** 2026-03-12 (30 days - WooCommerce monthly release cycle, next version 10.6 expected)

**Notes:**
- WooCommerce 10.5 released Feb 2026 - latest stable version
- Cart & Checkout blocks default since 8.3 (Nov 2023)
- DIBS Easy plugin version 2.13.1 installed locally with blocks support (2.8.0+) and HPOS compatibility (2.7.0+)
- Additional Checkout Fields API available in WooCommerce 8.0+ (Mar 2023)
- Phase 3 completed: HPOS enabled, product data migrated, product templates working
- WP Fastest Cache already installed - auto-exclusions for WooCommerce should work out of the box
- Norwegian (nb_NO) language activated for core and WooCommerce
- Test with DIBS sandbox mode before production - test account creation: https://portal.dibspayment.eu/test-user-create
