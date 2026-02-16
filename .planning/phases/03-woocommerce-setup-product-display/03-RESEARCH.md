# Phase 3: WooCommerce Setup & Product Display - Research

**Researched:** 2026-02-12
**Domain:** WooCommerce HPOS implementation, product migration, template customization
**Confidence:** HIGH

## Summary

Phase 3 focuses on enabling WooCommerce High Performance Order Storage (HPOS), migrating product data with custom fields, and creating product display templates. The research reveals that HPOS is enabled by default in WooCommerce 8.2+ (October 2023), with a well-documented two-phase migration process (compatibility mode → full HPOS). Critical findings include WooCommerce 10.5's category permalink changes affecting URL structures, variable product display behavior changes at 30+ variations, and the importance of template hooks over direct template overrides.

The critical path involves: (1) Enabling HPOS with compatibility mode first, allowing background synchronization of existing orders, (2) Migrating all product data including custom fields (stock display, delivery time) using WP-CLI or WooCommerce import tools, (3) Creating child theme template overrides for product pages and archives using hooks over direct template edits, (4) Configuring product filtering and sorting for archive pages, (5) Verifying all product URLs are preserved and implementing 301 redirects for any changes, and (6) Testing variable products with 10+ variations to ensure all variations display correctly.

**Primary recommendation:** Enable HPOS in compatibility mode immediately, migrate products using WP-CLI preserving custom fields and metadata, use child theme with hooks for template customization (avoid direct template overrides), implement product filtering via WooCommerce Product Filters or similar plugin, and verify URL preservation with automated checks before full HPOS switchover.

## Standard Stack

### Core
| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| WooCommerce | 10.5+ | E-commerce platform | Latest stable (Feb 2026); HPOS enabled by default since 8.2; 10.5 adds analytics improvements |
| HPOS (High Performance Order Storage) | Core feature (8.2+) | Custom order tables | 5x faster order creation, 40x faster order lookups, 1.5x faster checkout |
| WP-CLI | 2.x | Product migration | Serialization-aware database operations, batch processing for large datasets |
| Child Theme | Custom | Template overrides | Safe customization without losing changes on theme updates |

### Supporting
| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| WooCommerce Product Filters | Latest | Archive filtering/sorting | Official WooCommerce extension for advanced filtering by attributes, price, stock |
| WooCommerce Better Variations | Latest | Variable product display | For products with 10+ variations needing grid/table view instead of dropdowns |
| WP All Import | Pro or Free | Product data migration | When migrating from external sources or complex CSV imports with custom fields |
| Redirection | Latest | URL redirects | For tracking and managing 301 redirects if URL structure changes |
| WooCommerce Lead Time | Latest (if needed) | Delivery time display | Only if custom stock/delivery logic needs per-product/variation timing |

### Alternatives Considered
| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| HPOS | Legacy posts table | HPOS is 5x faster for orders; legacy only for incompatible plugins |
| Child theme hooks | Direct template overrides | Hooks are upgrade-safe; overrides needed only for major markup changes |
| WooCommerce Product Filters | FacetWP or custom solution | Official plugin has better HPOS integration; FacetWP more flexible |
| WP All Import | Manual CSV import | WP All Import handles complex field mapping; manual import sufficient for simple products |

**Installation:**
```bash
# WooCommerce already installed from Phase 1
# Enable HPOS via WooCommerce > Settings > Advanced > Features

# Install supporting plugins (via WP-CLI)
wp plugin install woocommerce-product-filters --activate
wp plugin install redirection --activate

# For product migration from CSV
wp plugin install wp-all-import --activate
# OR use WooCommerce native import
# WooCommerce > Products > Import

# For enhanced variable products (if needed)
wp plugin install woocommerce-better-variations --activate
```

## Architecture Patterns

### Recommended Project Structure
```
wp-content/
├── themes/
│   └── smartvarme-theme/           # From Phase 2
│       ├── woocommerce/            # WooCommerce template overrides
│       │   ├── single-product/     # Product page components
│       │   │   ├── title.php
│       │   │   ├── price.php
│       │   │   └── meta.php
│       │   ├── archive-product.php  # Shop/archive pages
│       │   ├── content-product.php  # Product loop item
│       │   └── single-product.php   # Single product layout
│       ├── patterns/               # Block patterns for products
│       │   ├── product-hero.php
│       │   └── product-features.php
│       └── functions.php           # WooCommerce hooks/filters
└── plugins/
    └── smartvarme-core/            # From Phase 1
        ├── includes/
        │   ├── woocommerce/        # WooCommerce customizations
        │   │   ├── stock-display.php     # Custom stock logic
        │   │   ├── delivery-time.php     # Delivery time display
        │   │   └── product-meta.php      # Custom field handling
        │   └── migration/          # Migration scripts
        │       ├── product-migrator.php
        │       └── url-validator.php
        └── assets/
            └── woocommerce/        # WooCommerce-specific styles/scripts
```

### Pattern 1: Enable HPOS with Compatibility Mode
**What:** Two-phase migration from posts table to HPOS custom tables
**When to use:** All existing WooCommerce stores (new installs have HPOS enabled by default)
**Example:**
```php
// Source: https://developer.woocommerce.com/docs/features/high-performance-order-storage/enable-hpos/

// Step 1: Enable compatibility mode via admin or code
// WooCommerce > Settings > Advanced > Features
// Enable: "Enable compatibility mode (synchronizes orders to the posts table)"

// Step 2: Monitor synchronization (automatic background process)
// Checks: WooCommerce > Status > Scheduled Actions
// Action: wc_schedule_pending_batch_process (checks for orders needing backfill)
// Action: wc_run_batch_process (performs synchronization, 25 orders per batch)

// Step 3: Verify HPOS is active
use Automattic\WooCommerce\Utilities\OrderUtil;

if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
    // HPOS is enabled and active
    error_log('HPOS is active');
} else {
    // Still using posts table
    error_log('Using legacy posts storage');
}

// Step 4: Switch to HPOS after full synchronization
// WooCommerce > Settings > Advanced > Features
// Change: "Order data storage" to "High-Performance Order Storage"
// Keep compatibility mode ON temporarily for rollback safety
```

### Pattern 2: HPOS-Compatible Order Queries
**What:** Using WooCommerce CRUD methods instead of WordPress post functions
**When to use:** Any code that accesses order data (required for HPOS compatibility)
**Example:**
```php
// Source: https://developer.woocommerce.com/docs/features/high-performance-order-storage/recipe-book/

// ❌ OLD: Direct post queries (breaks with HPOS)
$order_posts = get_posts(array(
    'post_type' => 'shop_order',
    'post_status' => 'wc-completed',
));

// ✅ NEW: WooCommerce order queries (HPOS compatible)
$orders = wc_get_orders(array(
    'status' => 'completed',
    'limit' => -1,
));

// Accessing order data
foreach ($orders as $order) {
    $order_id = $order->get_id();
    $total = $order->get_total();
    $customer = $order->get_billing_first_name();
}

// Updating order metadata
$order = wc_get_order($order_id);
$order->update_meta_data('_custom_field', $value);
$order->save(); // IMPORTANT: Always call save()
```

### Pattern 3: Child Theme WooCommerce Template Override
**What:** Override WooCommerce templates in child theme without losing updates
**When to use:** When markup changes are needed that can't be achieved with hooks
**Example:**
```php
// Source: https://developer.woocommerce.com/docs/theming/theme-development/template-structure/

// Directory structure:
// wp-content/themes/smartvarme-theme/woocommerce/single-product/price.php

// Copy from: wp-content/plugins/woocommerce/templates/single-product/price.php
// Paste into: wp-content/themes/smartvarme-theme/woocommerce/single-product/price.php
// Customize as needed

// IMPORTANT: Check template version compatibility
// WooCommerce > Status > System Status > Templates
// Shows outdated template overrides that need updating

// ✅ BETTER: Use hooks instead of template overrides when possible
// functions.php or smartvarme-core plugin

// Add custom content after price
add_action('woocommerce_single_product_summary', 'smartvarme_add_delivery_time', 15);
function smartvarme_add_delivery_time() {
    global $product;
    $delivery_time = get_post_meta($product->get_id(), '_delivery_time', true);

    if ($delivery_time) {
        echo '<div class="product-delivery-time">';
        echo '<strong>Leveringstid:</strong> ' . esc_html($delivery_time);
        echo '</div>';
    }
}

// Modify product title
add_filter('the_title', 'smartvarme_modify_product_title', 10, 2);
function smartvarme_modify_product_title($title, $id) {
    if (is_singular('product') && $id == get_the_ID()) {
        $stock_status = get_post_meta($id, '_stock_status', true);
        if ($stock_status === 'outofstock') {
            $title .= ' (Utsolgt)';
        }
    }
    return $title;
}
```

### Pattern 4: Product Migration with Custom Fields
**What:** Migrate products preserving custom metadata and fields
**When to use:** When moving products from old site or importing from CSV
**Example:**
```bash
# Source: https://www.wpallimport.com/documentation/how-to-migrate-woocommerce-products/

# Method 1: WP-CLI export/import (same WordPress install)
# Export products from old database
wp export --post_type=product --dir=/tmp/products

# Import to new database (preserves all metadata automatically)
wp import /tmp/products/export.xml --authors=create

# Method 2: CSV import with custom fields
# WooCommerce > Products > Import
# Map CSV columns to product fields:
# Column: meta:_delivery_time → Custom field for delivery time
# Column: meta:_stock_display_override → Custom stock display text
# Column: meta:_custom_sku → Any custom product metadata

# Method 3: WP All Import Pro (complex mappings)
# Install and activate WP All Import
# New Import > Choose file > Select product type
# Map custom fields using drag-and-drop interface
# Preview and test import with 1-2 products first
# Run full import
```

### Pattern 5: Variable Product Setup with Many Variations
**What:** Configure variable products with 10+ variations for optimal display
**When to use:** Products with multiple attributes (size, color, model) creating many combinations
**Example:**
```php
// Source: https://woocommerce.com/document/variable-product/

// Performance consideration: Products with 30+ variations
// Dynamic dropdowns work for ≤30 variations (dropdown updates based on selection)
// Static dropdowns for >30 variations (all options shown regardless)

// Creating variable product:
// 1. Product Data > Variable Product
// 2. Attributes tab > Add attributes (e.g., "Size", "Color")
// 3. Check "Used for variations"
// 4. Variations tab > Generate variations from all attributes
// 5. Set individual variation data (price, SKU, stock, image)

// Display variations in table/grid (requires plugin like WooCommerce Better Variations)
add_filter('woocommerce_variable_product_layout', 'smartvarme_variation_grid_layout');
function smartvarme_variation_grid_layout($layout) {
    // Change from 'dropdown' to 'grid' display
    return 'grid';
}

// Custom stock message per variation
add_filter('woocommerce_get_availability_text', 'smartvarme_custom_stock_text', 10, 2);
function smartvarme_custom_stock_text($availability, $product) {
    if ($product->is_type('variation')) {
        $custom_stock = get_post_meta($product->get_id(), '_custom_stock_message', true);
        if ($custom_stock) {
            return $custom_stock;
        }
    }
    return $availability;
}
```

### Pattern 6: URL Preservation and Redirects
**What:** Maintain existing product URLs or implement 301 redirects for changes
**When to use:** Migration or URL structure changes (critical for SEO)
**Example:**
```php
// Source: https://developer.woocommerce.com/2026/01/13/product-permalink-changes-coming-in-woocommerce-10-5/

// WooCommerce 10.5 change: Uses deepest category for permalinks
// Old URL: /shop/electronics/product-name/
// New URL: /shop/electronics/phones/smartphones/product-name/
// WooCommerce automatically creates 301 redirects for old URLs

// Check current permalink structure
// Settings > Permalinks > Product permalink base
// Options: Default, Shop base, Shop base with category, Custom base

// Verify all URLs are preserved (run before HPOS switchover)
// Use WP-CLI to generate URL inventory
wp post list --post_type=product --format=csv --fields=ID,post_name,guid > product-urls.csv

// Compare with old site URLs
// Any differences need 301 redirects via Redirection plugin
// Or programmatically:
add_action('template_redirect', 'smartvarme_redirect_old_urls');
function smartvarme_redirect_old_urls() {
    global $wp_query;

    if ($wp_query->is_404()) {
        $old_url = $_SERVER['REQUEST_URI'];

        // Map old URLs to new URLs
        $redirects = array(
            '/old-product-url/' => '/shop/category/new-product-url/',
            '/another-old-url/' => '/shop/new-url/',
        );

        if (isset($redirects[$old_url])) {
            wp_redirect($redirects[$old_url], 301);
            exit;
        }
    }
}
```

### Anti-Patterns to Avoid

- **Direct database queries for orders:** Always use `wc_get_order()` and `wc_get_orders()` instead of `$wpdb->get_results()` on posts table — breaks with HPOS
- **Deactivating extensions before HPOS migration:** Keep all WooCommerce extensions (Subscriptions, Bookings) active during HPOS transition to prevent data loss
- **Editing WooCommerce core templates:** Always use child theme overrides or hooks — core edits lost on plugin updates
- **Skipping compatibility mode:** Going straight to HPOS without compatibility mode removes rollback safety net
- **Manual URL find/replace on serialized data:** Use WP-CLI `search-replace` which handles serialization correctly
- **Cherry-picking database tables:** Always export/import full database — partial tables cause data corruption

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Product filtering | Custom filter UI with AJAX | WooCommerce Product Filters or FacetWP | Complex query caching, URL persistence, HPOS compatibility, attribute handling |
| Order data access | Direct `$wpdb` queries | WooCommerce CRUD (`wc_get_order()`, `wc_get_orders()`) | HPOS requires custom table queries; CRUD methods handle both legacy and HPOS |
| Variable product display | Custom variation selector | WooCommerce Better Variations or native dropdowns | Dynamic availability logic, price updates, stock checks, image switching |
| URL redirects | Manual .htaccess rules | Redirection plugin | Tracks 404s, manages redirects in database, logs hits, easy testing |
| Delivery time logic | Custom post meta display | WooCommerce Lead Time plugin | Per-product, per-variation, stock-based timing, order email integration |
| Product import | Custom CSV parser | WooCommerce CSV Import or WP All Import | Handles variations, attributes, images, gallery, metadata, error handling |

**Key insight:** WooCommerce has extensive APIs for order and product data that abstract storage details (HPOS vs posts table). Custom solutions bypass these abstractions and break when storage backend changes. Always use WooCommerce CRUD methods (`wc_get_*`, `$object->save()`) instead of WordPress post functions or direct database queries.

## Common Pitfalls

### Pitfall 1: HPOS Migration Without Compatibility Testing
**What goes wrong:** Extensions or custom code break after switching to HPOS, causing order management failures
**Why it happens:** Code uses direct `get_post()` or `$wpdb` queries on posts table instead of WooCommerce CRUD methods
**How to avoid:**
1. Enable compatibility mode first (synchronizes both tables)
2. Audit all custom code for `get_post`, `get_post_meta`, `wp_update_post`, `$wpdb->posts` queries
3. Replace with `wc_get_order()`, `$order->get_meta()`, `$order->save()`, `wc_get_orders()`
4. Test all order operations with HPOS enabled in compatibility mode
5. Check WooCommerce > Status > Features > Extensions compatibility list
**Warning signs:**
- Orders not appearing in admin after HPOS switch
- Custom order fields missing or not saving
- Third-party plugins showing errors about missing post data
- Performance degradation instead of improvement

### Pitfall 2: Product URL Structure Changes Breaking SEO
**What goes wrong:** WooCommerce 10.5 permalink changes cause products to have different URLs, breaking incoming links and search rankings
**Why it happens:** WooCommerce 10.5 prioritizes deepest category in permalink structure, changing URLs for products in multiple categories
**How to avoid:**
1. Before enabling HPOS, inventory all product URLs with `wp post list --post_type=product`
2. After migration, compare URLs and identify differences
3. WooCommerce auto-creates 301 redirects, but verify with tools like Screaming Frog
4. Use Redirection plugin to track 404s and add manual redirects if needed
5. Test critical product pages with `curl -I` to verify 200 or 301 responses
**Warning signs:**
- 404 errors in Google Search Console
- Traffic drops to specific product pages
- Broken internal links in content
- Search rankings declining for product pages

### Pitfall 3: Variable Products with 30+ Variations Performance Issues
**What goes wrong:** Products with many variations load slowly, dropdowns don't update dynamically, customers can't find specific options
**Why it happens:** WooCommerce disables dynamic dropdowns for 30+ variations, and default display becomes overwhelming
**How to avoid:**
1. For products with 30+ variations, use table/grid display (WooCommerce Better Variations)
2. Split products into multiple simpler products if variations aren't truly related
3. Use layered navigation/filtering on archive pages instead of single-product dropdowns
4. Enable variation price caching (WooCommerce 10.5 feature)
5. Consider custom variation display for unique use cases
**Warning signs:**
- Long page load times on variable products
- Customers contacting support unable to select options
- High bounce rate on variable product pages
- Dropdowns showing all options regardless of previous selections

### Pitfall 4: Custom Fields Lost During Product Migration
**What goes wrong:** Custom product metadata (delivery time, stock display, custom SKUs) disappears after migration
**Why it happens:** Export tools don't capture all metadata, or import mapping is incorrect
**How to avoid:**
1. Before migration, document all custom fields: `wp post list --post_type=product --field=ID | xargs -I {} wp post meta list {}`
2. Export with WP-CLI or WP All Import Pro (preserves all metadata)
3. Test import with 2-3 products first, verify all custom fields present
4. Use WooCommerce CSV format with `meta:field_name` column headers
5. After import, spot-check products to verify custom fields
**Warning signs:**
- Products missing custom information on front-end
- Custom fields empty in product admin
- Customers seeing incorrect stock or delivery information
- Orders missing custom product metadata

### Pitfall 5: Template Overrides Becoming Outdated
**What goes wrong:** WooCommerce updates templates with security fixes or new features, but child theme overrides don't get updates
**Why it happens:** Once a template is copied to child theme, it no longer receives automatic updates
**How to avoid:**
1. Use hooks instead of template overrides whenever possible
2. Only override templates when markup changes are absolutely necessary
3. Check WooCommerce > Status > System Status > Templates after WooCommerce updates
4. Review WooCommerce release notes for template changes
5. Compare child theme templates with plugin templates using `diff` or version control
**Warning signs:**
- "Outdated templates" warning in WooCommerce Status
- Template version numbers differ between child theme and plugin
- Missing features after WooCommerce updates
- Display bugs on product pages after updates

### Pitfall 6: Database Import Without Serialization-Aware Search-Replace
**What goes wrong:** URLs, file paths, or settings with serialized data (arrays, objects) become corrupted, breaking site functionality
**Why it happens:** Regular find/replace (MySQL REPLACE(), phpMyAdmin search) doesn't update string length in serialized data
**How to avoid:**
1. Always use WP-CLI `search-replace` for URL/domain changes: `wp search-replace 'oldsite.com' 'newsite.com'`
2. Or use Better Search Replace plugin (handles serialization)
3. Never use MySQL REPLACE() or manual database editing for serialized data
4. Test database import on staging first
5. Verify serialized data: `wp db query "SELECT * FROM wp_options WHERE option_value LIKE '%s:%'"` should show valid serialization
**Warning signs:**
- White screen of death after database import
- Settings reset to defaults
- Theme options disappearing
- WooCommerce settings not saving
- Products missing attributes or variations

## Code Examples

Verified patterns from official sources:

### Declaring HPOS Compatibility in Plugin
```php
// Source: https://developer.woocommerce.com/docs/features/high-performance-order-storage/recipe-book/
// In plugin main file (smartvarme-core.php)

add_action('before_woocommerce_init', function() {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'custom_order_tables',
            __FILE__,
            true // true = compatible, false = incompatible
        );
    }
});
```

### HPOS-Compatible Meta Box for Orders
```php
// Source: https://developer.woocommerce.com/docs/features/high-performance-order-storage/recipe-book/

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;

add_action('add_meta_boxes', 'smartvarme_add_order_meta_box');

function smartvarme_add_order_meta_box() {
    $screen = class_exists('\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController')
        && wc_get_container()->get(CustomOrdersTableController::class)->custom_orders_table_usage_is_enabled()
        ? wc_get_page_screen_id('shop-order')
        : 'shop_order';

    add_meta_box(
        'smartvarme_delivery_info',
        'Leveringsinformasjon',
        'smartvarme_render_delivery_meta_box',
        $screen,
        'side',
        'high'
    );
}

function smartvarme_render_delivery_meta_box($post_or_order_object) {
    $order = ($post_or_order_object instanceof WP_Post)
        ? wc_get_order($post_or_order_object->ID)
        : $post_or_order_object;

    $delivery_date = $order->get_meta('_delivery_date');

    echo '<p><strong>Ønsket leveringsdato:</strong></p>';
    echo '<input type="date" name="delivery_date" value="' . esc_attr($delivery_date) . '" />';
}
```

### Custom Product Field Display
```php
// Source: https://litextension.com/blog/woocommerce-custom-fields/

// Add custom field to product edit page
add_action('woocommerce_product_options_general_product_data', 'smartvarme_add_custom_field');
function smartvarme_add_custom_field() {
    woocommerce_wp_text_input(array(
        'id' => '_delivery_time',
        'label' => 'Leveringstid (dager)',
        'placeholder' => 'f.eks. 2-5',
        'desc_tip' => true,
        'description' => 'Forventet leveringstid for dette produktet',
        'type' => 'text',
    ));
}

// Save custom field
add_action('woocommerce_process_product_meta', 'smartvarme_save_custom_field');
function smartvarme_save_custom_field($post_id) {
    $delivery_time = isset($_POST['_delivery_time']) ? sanitize_text_field($_POST['_delivery_time']) : '';
    update_post_meta($post_id, '_delivery_time', $delivery_time);
}

// Display custom field on product page
add_action('woocommerce_single_product_summary', 'smartvarme_display_delivery_time', 25);
function smartvarme_display_delivery_time() {
    global $product;
    $delivery_time = get_post_meta($product->get_id(), '_delivery_time', true);

    if ($delivery_time) {
        echo '<div class="product-delivery-time">';
        echo '<strong>Leveringstid:</strong> ' . esc_html($delivery_time) . ' dager';
        echo '</div>';
    }
}
```

### Product Archive Filtering Hook
```php
// Source: https://woocommerce.com/document/managing-products/feature-filter-and-sort-products/

// Customize default product sorting options
add_filter('woocommerce_catalog_orderby', 'smartvarme_custom_sorting_options');
function smartvarme_custom_sorting_options($sortby) {
    unset($sortby['popularity']); // Remove popularity sort
    unset($sortby['rating']); // Remove rating sort

    $sortby['stock'] = 'På lager først';
    $sortby['newest'] = 'Nyeste først';

    return $sortby;
}

// Handle custom sorting
add_filter('woocommerce_get_catalog_ordering_args', 'smartvarme_custom_sorting_args');
function smartvarme_custom_sorting_args($args) {
    $orderby_value = isset($_GET['orderby']) ? wc_clean($_GET['orderby']) : '';

    switch ($orderby_value) {
        case 'stock':
            $args['meta_key'] = '_stock_status';
            $args['orderby'] = 'meta_value';
            $args['order'] = 'DESC';
            break;
        case 'newest':
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            break;
    }

    return $args;
}
```

### Batch Product Import via WP-CLI
```bash
# Source: https://www.wpallimport.com/documentation/how-to-migrate-woocommerce-products/

# Export products from current database (Phase 2 already imported database)
wp export --post_type=product --dir=./exports/products

# Verify export contains all products
wp post list --post_type=product --format=count

# Import products to new site (if migrating between installs)
wp import ./exports/products/*.xml --authors=create

# Verify all custom fields preserved
wp post list --post_type=product --post__in=123 | xargs wp post meta list

# Batch update product metadata (example: set all products in stock)
wp post list --post_type=product --format=ids | xargs -d ' ' -I % wp post meta update % _stock_status instock
```

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| Orders in wp_posts table | HPOS custom tables (wp_wc_orders) | WooCommerce 8.2 (Oct 2023) | 5x faster order creation, 40x faster lookups, required for large stores |
| Template overrides only | Hooks + selective overrides | Ongoing best practice | Better upgrade compatibility, less maintenance |
| Manual product filtering | WooCommerce Product Filters blocks | WooCommerce 8.0+ (Mar 2023) | Block-based filtering, better HPOS compatibility |
| CSV import limitations | Enhanced CSV import + REST API | WooCommerce 9.0+ | Better variation support, metadata handling |
| Shallow category permalinks | Deep category permalinks | WooCommerce 10.5 (Feb 2026) | More specific URLs, automatic 301 redirects |
| Direct variation price calculation | Variation price caching | WooCommerce 10.5 (Feb 2026) | Faster variable product page loads |
| 30 variation dropdown threshold | Still 30 variation threshold | Unchanged | Consider table/grid display for 30+ variations |

**Deprecated/outdated:**
- **Direct `$wpdb` queries on wp_posts for orders**: Use `wc_get_orders()` — required for HPOS compatibility
- **`get_post_meta()` for order data**: Use `$order->get_meta()` — HPOS stores order meta in wp_wc_orders_meta
- **WooCommerce Blocks < 8.0**: Update to latest for best HPOS and FSE compatibility
- **Third-party product tables without HPOS support**: Verify plugins declare HPOS compatibility or find alternatives

## Open Questions

1. **Which WooCommerce extensions are active on the current site?**
   - What we know: Formidable Forms for contact forms, DIBS payment gateway, FiboSearch for product search
   - What's unclear: Whether these are HPOS-compatible, which versions are installed
   - Recommendation: Audit all WooCommerce extensions with `wp plugin list` and check WooCommerce.com compatibility database before HPOS migration

2. **What custom product fields exist beyond stock and delivery time?**
   - What we know: Stock display and delivery time custom logic exist
   - What's unclear: Field names, data types, where stored (post meta vs custom tables)
   - Recommendation: Query database for all product post_meta: `SELECT DISTINCT meta_key FROM wp_postmeta WHERE post_id IN (SELECT ID FROM wp_posts WHERE post_type='product')`

3. **How many total products and variations exist?**
   - What we know: 794 products imported in Phase 1
   - What's unclear: How many are variable products, total variation count, max variations per product
   - Recommendation: Check `wp post list --post_type=product --format=count` and `wp post list --post_type=product_variation --format=count`

4. **Are product images already migrated or need special handling?**
   - What we know: Database imported in Phase 1 (14,838 posts including 794 products)
   - What's unclear: Whether product images are in wp-content/uploads and correctly linked
   - Recommendation: Verify image paths with `wp media regenerate --yes` and check for broken images

5. **What is the current product URL structure?**
   - What we know: URLs must be preserved (MIG-01, MIG-02, WOO-05)
   - What's unclear: Current permalink format (shop base with category vs custom)
   - Recommendation: Check Settings > Permalinks > Product permalink base and generate URL inventory before migration

## Sources

### Primary (HIGH confidence)
- [WooCommerce Developer Docs: Enable HPOS](https://developer.woocommerce.com/docs/features/high-performance-order-storage/enable-hpos/) - HPOS migration steps and compatibility mode
- [WooCommerce Developer Docs: HPOS Recipe Book](https://developer.woocommerce.com/docs/features/high-performance-order-storage/recipe-book/) - Code patterns for HPOS compatibility
- [WooCommerce Developer Docs: HPOS Guide for Large Stores](https://developer.woocommerce.com/docs/features/high-performance-order-storage/guide-large-store/) - Performance and scale considerations
- [WooCommerce Developer Docs: Template Structure](https://developer.woocommerce.com/docs/theming/theme-development/template-structure/) - Template override best practices
- [WooCommerce Documentation: Variable Products](https://woocommerce.com/document/variable-product/) - Variable product setup and management
- [WooCommerce Developer Blog: WooCommerce 10.5 Release](https://developer.woocommerce.com/2026/02/06/woocommerce-10-5-improving-analytics-and-admin-performance/) - Latest version features
- [WooCommerce Developer Blog: Product Permalink Changes](https://developer.woocommerce.com/2026/01/13/product-permalink-changes-coming-in-woocommerce-10-5/) - URL structure changes in 10.5

### Secondary (MEDIUM confidence)
- [WP All Import Documentation: Migrate WooCommerce Products](https://www.wpallimport.com/documentation/how-to-migrate-woocommerce-products/) - Product migration patterns
- [Barn2 Blog: WooCommerce Product Variations Guide](https://barn2.com/blog/woocommerce-product-variations/) - Variable product best practices
- [Shopping Cart Migration: WooCommerce URL Structure](https://www.shopping-cart-migration.com/carts-reviews/build-proper-woocommerce-url-structure) - URL configuration recommendations
- [LitExtension: WooCommerce Custom Fields](https://litextension.com/blog/woocommerce-custom-fields/) - Custom field implementation
- [Acowebs: WooCommerce Custom Fields Guide](https://acowebs.com/woocommerce-custom-fields-a-comprehensive-guide-for-woocommerce-store-owners/) - Custom field patterns
- [WisdmLabs: WooCommerce Migration Pitfalls](https://wisdmlabs.com/blog/woocommerce-migration-pitfalls/) - Common migration mistakes

### Tertiary (LOW confidence)
- Various plugin documentation (WooCommerce Better Variations, Product Filters, Lead Time) - Plugin-specific implementations
- Community tutorials and blog posts - Supplementary examples and use cases

## Metadata

**Confidence breakdown:**
- HPOS implementation: HIGH - Official WooCommerce docs with code examples, verified in 10.5
- Template customization: HIGH - Official template documentation and hook references
- Product migration: MEDIUM-HIGH - Mix of official docs and verified third-party tools (WP All Import)
- Variable product handling: HIGH - Official WooCommerce documentation
- Norwegian language support: MEDIUM - General WooCommerce translation support confirmed, specific nb_NO + HPOS not explicitly documented
- URL preservation: HIGH - Official 10.5 release notes document permalink changes and redirects

**Research date:** 2026-02-12
**Valid until:** 2026-03-12 (30 days - WooCommerce release cycle is monthly, next version 10.6 may introduce changes)

**Notes:**
- WooCommerce 10.5 released Feb 2026 - latest stable version
- HPOS enabled by default since WooCommerce 8.2 (Oct 2023)
- Product import/export requires testing with actual data to verify all custom fields preserved
- Phase 2 already completed database import (794 products), so focus is on HPOS enablement and template customization
- Norwegian (nb_NO) language support exists for WooCommerce core; verify HPOS admin strings are translated
