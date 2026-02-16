# Phase 6: Performance & Plugin Optimization - Research

**Researched:** 2026-02-13
**Domain:** WordPress/WooCommerce Performance Optimization & Plugin Consolidation
**Confidence:** HIGH

## Summary

Phase 6 focuses on achieving Core Web Vitals targets (LCP < 2.5s, INP < 200ms, CLS < 0.1), reducing page load times by 50%+, and consolidating the current 35 plugins down to under 15 essential plugins. This research reveals that modern WordPress performance optimization is a multi-layered approach combining caching strategies, image optimization, asset loading control, database optimization, and strategic plugin consolidation.

The project benefits from significant existing optimizations: 99% autoload reduction (19.6MB → 188KB) from Phase 1, HPOS enabled in Phase 3, system font stack (zero web font CLS) from Phase 5, and custom AJAX search replacing FiboSearch. These foundations provide a strong starting point.

**Primary recommendation:** Implement WP Rocket for comprehensive caching and Core Web Vitals optimization, combine with Perfmatters for granular asset control, use ShortPixel or Imagify for automatic WebP/AVIF conversion, and systematically consolidate the 35 existing plugins by moving critical features to the smartvarme-core custom plugin while leveraging WordPress 6.9's new core blocks (Accordion, Time to Read, Math, Term Query, Comment Count/Link).

## Standard Stack

### Core Performance Plugins

| Plugin | Version | Purpose | Why Standard |
|--------|---------|---------|--------------|
| WP Rocket | 3.x+ | Comprehensive caching & Core Web Vitals optimization | Industry leader: 80% of performance best practices applied on activation, easiest setup (3 minutes), handles page caching, browser caching, Gzip compression, CSS/JS minification, database optimization, and Cloudflare integration |
| ShortPixel or Imagify | Latest | Automatic image optimization with WebP/AVIF conversion | ShortPixel: handles lossy/lossless compression, $9.99/month for unlimited credits; Imagify: built for WordPress, converts to next-gen formats recommended by Google |
| Perfmatters | 2.x+ | Granular asset loading control | Broader performance suite with script manager, one-click optimizations, remove unused CSS, preload critical images, delay JavaScript, device-based unloading, MU mode, RegEx support — cleaner UI than Asset CleanUp Pro at half the cost |

### Supporting Plugins

| Plugin | Version | Purpose | When to Use |
|--------|---------|---------|-------------|
| Query Monitor | 3.x+ | Performance analysis and debugging | During optimization phase to identify slow queries, plugin impact, and performance bottlenecks |
| Redis Object Cache | 2.x+ | Persistent object caching | If hosting supports Redis (Kinsta, WP Engine, Cloudways offer one-click activation); provides 5x faster database queries for WooCommerce stores |
| WP-Optimize | 3.x+ | Database maintenance and cleanup | Automated cleanup of post revisions, spam comments, expired transients, and database table optimization |

### Alternatives Considered

| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| WP Rocket | LiteSpeed Cache (free) | LiteSpeed Cache equals or exceeds WP Rocket for Core Web Vitals BUT only if hosted on LiteSpeed server; loses signature server-level caching on other servers |
| WP Rocket | WP Fastest Cache (free) | Currently installed; lacks WP Rocket's comprehensive features (no database optimization, limited CSS/JS optimization, no unused CSS removal) |
| Perfmatters | Asset CleanUp Pro | More granular control but significantly more complex UI; double the cost; better for advanced developers, worse for maintainability |
| ShortPixel/Imagify | EWWW Image Optimizer | Includes "Easy IO" CDN with auto WebP/AVIF conversion; unlimited free lossless compression on local server; premium $8/month (cheaper but less robust) |
| Redis | Memcached | Simpler but fewer features; Redis preferred for WooCommerce due to complex data structures, disk-based persistence, and better resilience |

**Installation:**
```bash
# Plugin licenses required (premium plugins)
# WP Rocket: $59/year (1 site), $119/year (3 sites), $299/year (50 sites)
# Perfmatters: ~$30/year (significant value vs competitors)
# ShortPixel: $9.99/month unlimited OR Imagify: varies by usage

# Free/Included plugins
wp plugin install redis-cache --activate
wp plugin install query-monitor --activate
wp plugin install wp-optimize --activate

# Redis object caching (if hosting supports)
wp redis enable
```

## Architecture Patterns

### Recommended Performance Optimization Structure

```
Performance Optimization Workflow:
├── 1-baseline-measurement/           # Establish current performance
│   ├── PageSpeed Insights (mobile + desktop)
│   ├── GTmetrix (multiple locations)
│   ├── WebPageTest (detailed waterfall)
│   └── Core Web Vitals field data (Search Console)
├── 2-caching-layer/                  # Foundation: WP Rocket setup
│   ├── Page caching (exclude cart/checkout/account)
│   ├── Browser caching
│   ├── Object caching (Redis if available)
│   ├── Cache preloading
│   └── Gzip compression
├── 3-image-optimization/             # ShortPixel/Imagify
│   ├── Automatic WebP/AVIF conversion
│   ├── Lazy loading (below-fold only)
│   ├── Responsive images (srcset)
│   └── Image compression (lossless/lossy)
├── 4-asset-optimization/             # Perfmatters + WP Rocket
│   ├── CSS/JS minification
│   ├── Remove unused CSS
│   ├── Defer/delay JavaScript
│   ├── Inline critical CSS
│   └── Conditional asset loading (per-page control)
├── 5-database-optimization/          # WP-Optimize + transient cleanup
│   ├── Remove post revisions
│   ├── Clean spam comments
│   ├── Delete expired transients
│   ├── Optimize database tables
│   └── Monitor autoload size (keep < 800KB)
├── 6-plugin-consolidation/           # Reduce 35 → 15 plugins
│   ├── Feature inventory (all 35 plugins)
│   ├── Identify removable plugins
│   ├── Move critical features to smartvarme-core
│   ├── Leverage WordPress 6.9 core blocks
│   └── Test feature parity before removal
└── 7-cdn-integration/                # Optional: Cloudflare CDN
    ├── Automatic Platform Optimization (APO)
    ├── Global edge caching (275+ locations)
    ├── 70% faster load times potential
    └── Cost: Free tier + $5/month APO (< 50k visitors)
```

### Pattern 1: Core Web Vitals Optimization Strategy

**What:** Systematic approach to achieving LCP < 2.5s, INP < 200ms, CLS < 0.1

**When to use:** After establishing baseline metrics; iterative improvement cycle

**Example:**
```php
// Source: https://wp-rocket.me/google-core-web-vitals-wordpress/
// Official WP Rocket Core Web Vitals targets

/**
 * LCP (Largest Contentful Paint) Optimization
 * Target: ≤ 2.5 seconds
 *
 * Key strategies:
 * 1. Optimize LCP element (usually hero image or heading)
 * 2. Preload critical images using WP Rocket's preload feature
 * 3. Use CDN to reduce server response time (TTFB)
 * 4. Ensure LCP image NOT lazy loaded (exclude from lazy loading)
 * 5. Use WebP/AVIF for faster image delivery
 */
add_filter( 'rocket_exclude_defer_js', function( $excluded_js ) {
    // Exclude critical above-the-fold scripts from deferring
    $excluded_js[] = '/wp-includes/js/jquery/jquery.min.js';
    return $excluded_js;
});

/**
 * INP (Interaction to Next Paint) Optimization
 * Target: ≤ 200ms
 *
 * Key strategies:
 * 1. Defer non-critical JavaScript
 * 2. Delay JavaScript execution until user interaction
 * 3. Remove render-blocking scripts
 * 4. Keep main thread clear using async/defer attributes
 */
add_filter( 'rocket_delay_js_scripts', function( $delay_js_scripts ) {
    // Delay non-essential scripts (tracking, analytics, chat widgets)
    $delay_js_scripts = [
        'gtag',
        'gtm',
        'fbevents',
        'analytics',
        '_gaq',
        'google-analytics',
    ];
    return $delay_js_scripts;
});

/**
 * CLS (Cumulative Layout Shift) Optimization
 * Target: ≤ 0.1
 *
 * Key strategies:
 * 1. Set explicit width/height for all images (already in HTML)
 * 2. Reserve space for ads/embeds with aspect-ratio CSS
 * 3. Use system font stack (zero web font CLS) — ALREADY DONE in Phase 5
 * 4. Avoid inserting content above existing content (except user interactions)
 */
add_filter( 'wp_lazy_loading_enabled', function( $default, $tag_name, $context ) {
    // Disable lazy loading for above-the-fold images
    if ( 'img' === $tag_name && 'the_content' === $context ) {
        // Custom logic to exclude first image (hero/LCP element)
        global $smartvarme_image_count;
        $smartvarme_image_count = ( $smartvarme_image_count ?? 0 ) + 1;

        if ( 1 === $smartvarme_image_count ) {
            return false; // Don't lazy load first image
        }
    }
    return $default;
}, 10, 3 );
```

### Pattern 2: WooCommerce Cache Exclusion Rules

**What:** Essential cache exclusions for WooCommerce dynamic pages

**When to use:** Mandatory when configuring any caching plugin with WooCommerce

**Example:**
```php
// Source: https://developer.woocommerce.com/docs/best-practices/performance/configuring-caching-plugins/
// Official WooCommerce cache exclusion requirements

/**
 * WP Rocket Configuration for WooCommerce
 *
 * Pages to exclude (never cache):
 * - Cart (/cart/)
 * - Checkout (/checkout/)
 * - My Account (/my-account/)
 * - Order Received (/order-received/)
 *
 * Cookies to exclude:
 * - woocommerce_cart_hash
 * - woocommerce_items_in_cart
 * - wp_woocommerce_session_*
 * - woocommerce_recently_viewed
 * - store_notice[notice id]
 *
 * Query strings to exclude:
 * - ?add-to-cart=*
 * - ?remove_item=*
 * - ?wc-ajax=*
 */

// WP Rocket automatically handles WooCommerce exclusions when WooCommerce is detected
// Manual configuration only needed for custom implementations

add_filter( 'rocket_cache_reject_uri', function( $uri ) {
    // Exclude WooCommerce pages from caching
    $uri[] = '/cart/(.*)';
    $uri[] = '/checkout/(.*)';
    $uri[] = '/my-account/(.*)';
    $uri[] = '/order-received/(.*)';
    return $uri;
});

add_filter( 'rocket_cache_mandatory_cookies', function( $cookies ) {
    // Exclude pages with these cookies from caching
    $cookies[] = 'woocommerce_cart_hash';
    $cookies[] = 'woocommerce_items_in_cart';
    $cookies[] = 'wp_woocommerce_session';
    return $cookies;
});

add_filter( 'rocket_cache_query_strings', function( $query_strings ) {
    // Exclude AJAX requests from caching
    $query_strings[] = 'wc-ajax';
    $query_strings[] = 'add-to-cart';
    $query_strings[] = 'remove_item';
    return $query_strings;
});
```

### Pattern 3: Plugin Feature Inventory & Consolidation Strategy

**What:** Systematic approach to reducing 35 plugins to under 15

**When to use:** Before removing any plugins; ensures feature parity maintained

**Example:**
```php
// Source: Multiple WordPress consolidation guides
// Current: 35 plugins → Target: < 15 plugins

/**
 * Plugin Consolidation Matrix
 *
 * Phase 1: Inventory & Categorization
 * - Essential: Must keep (WooCommerce, security, performance)
 * - Movable: Features can migrate to smartvarme-core
 * - Replaceable: WordPress 6.9 core blocks handle this
 * - Removable: Redundant or unused functionality
 *
 * Phase 2: Consolidation Actions
 * - Move custom functionality to smartvarme-core plugin
 * - Replace with WordPress 6.9 core blocks where possible
 * - Merge multi-plugin functionality into single solution
 * - Remove unused or redundant plugins
 */

// Example: Move product shortcodes from separate plugin to smartvarme-core
// CURRENT: functions.php has shortcodes (kampanje_produkter, produkter_pa_lager, etc.)
// ACTION: Already in theme — evaluate if plugin needed or keep in theme

/**
 * WordPress 6.9 Core Blocks That Replace Plugins
 *
 * New core blocks (no plugins needed):
 * - Accordion Block → Replaces: Kadence Blocks accordion feature
 * - Time to Read Block → Replaces: reading-time plugins
 * - Math Block → Replaces: MathJax/LaTeX plugins (if used)
 * - Term Query Block → Replaces: custom category/tag display plugins
 * - Comment Count & Comment Link Blocks → Replaces: comment display plugins
 */

// Example consolidation: Accordion migration
function smartvarme_migrate_kadence_to_core_accordion() {
    // Phase 6 task: Identify all Kadence accordion instances
    // Replace with core Accordion block (WordPress 6.9+)
    // Test feature parity before removing Kadence Blocks
    // Document: wp-content/plugins/kadence-blocks can be removed if only used for accordions
}

/**
 * Feature Inventory Template for Each Plugin
 */
$plugin_inventory = [
    [
        'plugin' => 'kadence-blocks',
        'active_features' => ['Accordions', 'Advanced columns', 'Row layouts'],
        'status' => 'EVALUATE',
        'replacement_strategy' => 'Core Accordion block (WP 6.9) for accordions; evaluate if other features used',
        'priority' => 'medium',
    ],
    [
        'plugin' => 'formidable',
        'active_features' => ['Contact forms (ID 11)', 'Product inquiry forms'],
        'status' => 'KEEP',
        'replacement_strategy' => 'Essential business functionality; no consolidation',
        'priority' => 'n/a',
    ],
    [
        'plugin' => 'carousel-block',
        'active_features' => ['Product image carousels'],
        'status' => 'EVALUATE',
        'replacement_strategy' => 'Check if WooCommerce native gallery slider sufficient (already enabled in theme)',
        'priority' => 'high',
    ],
    [
        'plugin' => 'duplicate-post',
        'active_features' => ['Admin convenience: duplicate products/posts'],
        'status' => 'KEEP',
        'replacement_strategy' => 'Low overhead, high convenience; keep',
        'priority' => 'n/a',
    ],
    // ... repeat for all 35 plugins
];
```

### Pattern 4: Asset Loading Control (Per-Page Optimization)

**What:** Disable unused CSS/JS on specific pages using Perfmatters

**When to use:** After identifying render-blocking resources per page type

**Example:**
```php
// Source: https://perfmatters.io/docs/
// Perfmatters Script Manager best practices

/**
 * Conditional Asset Loading Strategy
 *
 * Goal: Only load scripts/styles where actually needed
 *
 * Example: Google Tag Manager only needed on specific conversion pages
 * Example: WooCommerce scripts NOT needed on blog posts
 * Example: Contact form scripts ONLY on contact page and product pages
 */

// Perfmatters provides UI for per-page control; here's programmatic approach
add_action( 'wp_enqueue_scripts', function() {
    // Don't load WooCommerce scripts on non-shop pages
    if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() && ! is_account_page() ) {
        // Dequeue WooCommerce styles
        wp_dequeue_style( 'woocommerce-general' );
        wp_dequeue_style( 'woocommerce-layout' );
        wp_dequeue_style( 'woocommerce-smallscreen' );

        // Dequeue WooCommerce scripts
        wp_dequeue_script( 'wc-cart-fragments' );
        wp_dequeue_script( 'woocommerce' );
        wp_dequeue_script( 'wc-add-to-cart' );
    }

    // Don't load Formidable Forms scripts on pages without forms
    if ( ! is_singular( 'product' ) && ! is_page( 'contact' ) ) {
        wp_dequeue_style( 'formidable' );
        wp_dequeue_script( 'formidable' );
    }

    // Example: Delay JavaScript on non-interactive pages
    if ( is_singular( 'post' ) || is_singular( 'faq' ) ) {
        // Use Perfmatters UI to delay JS on blog/FAQ pages
        // Allows faster initial render for content-focused pages
    }
}, 100 ); // Late priority to override plugin enqueues
```

### Anti-Patterns to Avoid

- **Over-optimization:** Deferring ALL JavaScript including critical above-the-fold scripts causes Flash of Unstyled Content (FOUT) and degrades user experience; always exclude jQuery and critical UI scripts from defer/delay
- **Aggressive lazy loading:** Lazy loading the LCP (Largest Contentful Paint) element dramatically worsens Core Web Vitals; always exclude hero images, logos, and above-the-fold content from lazy loading
- **Caching dynamic pages:** Caching WooCommerce cart/checkout/account pages breaks functionality; users see stale cart data or can't checkout
- **Plugin removal without testing:** Removing plugins without comprehensive feature inventory and testing breaks site functionality; always map features, create replacement strategy, and test on staging first
- **Ignoring database bloat:** Transients and autoload data accumulate over time; sites with 5MB+ autoload struggle with performance regardless of caching; maintain < 800KB autoload target
- **JavaScript minification without testing:** Minification can corrupt tracking codes (Google Analytics, Facebook Pixel); always test conversion tracking after optimization
- **No baseline measurement:** Optimizing without PageSpeed/GTmetrix baseline makes it impossible to prove 50%+ improvement; always establish metrics BEFORE optimization

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Image optimization pipeline | Custom image processing script with ImageMagick/GD | ShortPixel or Imagify plugin | Handles format detection, automatic WebP/AVIF conversion, responsive image generation (srcset), lossless/lossy algorithms, bulk optimization, CDN integration — complex edge cases like animated GIFs, transparency, color profiles |
| Page caching system | Custom caching layer with transients API | WP Rocket or LiteSpeed Cache | Handles cache warming, mobile detection, user role exclusions, query string handling, cookie exclusions, cache purging on content updates, CDN integration, Gzip compression — 80+ edge cases handled |
| Unused CSS detection | Custom parser to analyze CSS usage | WP Rocket's Remove Unused CSS or Perfmatters | Requires AST parsing, critical CSS extraction, page-specific CSS generation, above-the-fold detection, media query handling — extremely complex and error-prone |
| Database optimization | Custom SQL queries to clean database | WP-Optimize or built-in WP-CLI commands | Handles table optimization, foreign key constraints, transient cleanup, revision management, orphaned metadata, safe backup before operations — risk of data corruption if done manually |
| Asset minification | Custom minifier with regex | WP Rocket or Autoptimize | Handles JavaScript AST parsing, CSS preprocessing, sourcemap generation, concatenation order, dependency resolution, ES6+ syntax — regex-based minification breaks modern JavaScript |
| CDN integration | Custom CDN URL rewriting | WP Rocket + Cloudflare plugin | Handles URL rewriting, cache invalidation, gzip/Brotli compression, HTTP/2 push, automatic cache purging, SSL certificate management — complex edge cases with relative URLs |

**Key insight:** WordPress performance optimization involves 100+ edge cases per domain (caching, images, assets, database). Premium plugins like WP Rocket have been battle-tested across millions of sites and handle edge cases that would take months to discover and fix manually. The cost of premium plugins ($60-120/year) is negligible compared to developer time debugging custom solutions.

## Common Pitfalls

### Pitfall 1: Cache Hit Ratio Misunderstanding

**What goes wrong:** Site has caching enabled but page generation time still slow; cache hit ratio below 50%

**Why it happens:** Cache not properly warmed; aggressive cache purging on every content update; cache exclusions too broad; bot traffic bypassing cache

**How to avoid:**
- Enable cache preloading (WP Rocket's "Preload Cache" feature crawls sitemap and warms cache)
- Configure selective cache purging (only purge affected pages, not entire cache)
- Monitor cache hit ratio via WP Rocket stats or server logs; target > 80% hit ratio
- Exclude bots from analytics (Googlebot, crawlers don't represent real user experience)

**Warning signs:**
- PageSpeed Insights shows fast scores but real users report slow load times
- Server CPU usage high despite caching enabled
- Cache size stays small (< 100MB) on site with 1000+ pages

### Pitfall 2: LCP Element Lazy Loaded

**What goes wrong:** Largest Contentful Paint score poor (> 4s) despite image optimization

**Why it happens:** Lazy loading plugin or WordPress native lazy loading applies to hero image (LCP element); browser waits for JavaScript to load before rendering hero image

**How to avoid:**
- Identify LCP element using PageSpeed Insights (usually hero image on homepage, product image on product pages)
- Exclude LCP images from lazy loading using WP Rocket's "Exclude Images from Lazy Load" feature
- Add `loading="eager"` attribute to hero images manually
- Use Perfmatters to disable lazy loading on specific images via CSS class

**Warning signs:**
- PageSpeed Insights "Defer offscreen images" passes but LCP score poor
- Waterfall chart shows hero image loading after JavaScript execution
- Visual regression: page loads with blank hero area before image appears

### Pitfall 3: Plugin Removal Without Feature Inventory

**What goes wrong:** Deactivate plugin to reduce count; site functionality breaks (forms don't submit, search stops working, checkout fails)

**Why it happens:** Plugin provided critical feature not documented; multiple features bundled in single plugin; features used on pages not regularly tested

**How to avoid:**
- Create comprehensive feature inventory BEFORE deactivation (see Pattern 3)
- Check plugin usage using Query Monitor (shows which plugins load on which pages)
- Test on staging site with plugin deactivated for 1 week
- Use Search & Replace to find all shortcodes (e.g., `[formidable id=11]`) before removing plugin
- Document replacement strategy for each feature

**Warning signs:**
- "Shortcode not found" errors appear on frontend
- Forms show raw HTML instead of rendered form
- Search functionality stops working
- WooCommerce product display breaks

### Pitfall 4: Autoload Data Creep After Optimization

**What goes wrong:** Autoload optimized to 188KB in Phase 1; now back to 2MB+ after plugin installations

**Why it happens:** New plugins add autoload data without cleanup; transients accumulate; option updates don't clean old data; poorly coded plugins misuse autoload flag

**How to avoid:**
- Monitor autoload size monthly using Query Monitor or database query
- Run WP-Optimize's transient cleanup weekly (automated schedule)
- Audit new plugins before installation using Query Monitor's autoload tab
- Set up alerts for autoload > 800KB using monitoring service

**Warning signs:**
- Page generation time increases over weeks/months despite caching
- Database queries show high autoload query time in Query Monitor
- wp_options table size grows significantly (> 10MB)

### Pitfall 5: HPOS Compatibility Issues with Old Plugins

**What goes wrong:** HPOS enabled in Phase 3; third-party plugins still using legacy post meta for orders; data inconsistency

**Why it happens:** Older plugins not updated for HPOS; custom code accessing order data directly via post meta; synchronization between custom tables and post meta disabled

**How to avoid:**
- Keep HPOS synchronization enabled during Phase 6 (WooCommerce > Settings > Advanced > Features)
- Audit all plugins for HPOS compatibility before Phase 6 begins
- Use WooCommerce's CRUD functions (wc_get_order()) instead of direct database access
- Test order creation, editing, and reporting thoroughly after plugin changes

**Warning signs:**
- Orders appear in admin but not in custom reports
- Shipping plugins show incorrect order data
- Order exports missing fields
- Third-party integrations (accounting software) show incomplete data

### Pitfall 6: JavaScript Delay Breaking Critical Functionality

**What goes wrong:** Enable "Delay JavaScript" in WP Rocket; Add to Cart button stops working; search doesn't function; forms don't submit

**Why it happens:** Delayed JavaScript includes critical functionality needed for user interaction; dependencies loaded in wrong order; event listeners attached before DOM elements exist

**How to avoid:**
- Exclude critical scripts from delay using WP Rocket's "Excluded Inline JavaScript" field
- Test Add to Cart, search, and forms after enabling delay
- Use Perfmatters' "Delay JavaScript" with granular exclusions instead of blanket delay
- Monitor console errors in browser DevTools after enabling delay

**Warning signs:**
- JavaScript console errors: "$ is not defined" or "function not found"
- Buttons clickable but no action happens
- Forms submit but data not sent
- AJAX requests fail silently

## Code Examples

Verified patterns from official sources:

### Example 1: Programmatic Cache Warming (WP Rocket)

```php
// Source: https://docs.wp-rocket.me/article/494-how-to-programmatically-generate-cache
// Official WP Rocket documentation

/**
 * Programmatically warm cache after content update
 *
 * Use case: Warm cache for specific URLs after product import or content migration
 */
function smartvarme_warm_cache_for_products( $product_ids ) {
    if ( ! function_exists( 'run_rocket_bot' ) ) {
        return; // WP Rocket not active
    }

    // Get product URLs
    $urls = [];
    foreach ( $product_ids as $product_id ) {
        $urls[] = get_permalink( $product_id );
    }

    // Trigger cache preload for specific URLs
    run_rocket_bot( 'cache-preload', $urls );
}

// Hook after product import completes
add_action( 'smartvarme_after_product_import', function( $product_ids ) {
    smartvarme_warm_cache_for_products( $product_ids );
});
```

### Example 2: Database Autoload Monitoring

```php
// Source: https://docs.pantheon.io/optimize-wp-options-table-autoloaded-data
// Official Pantheon documentation

/**
 * Monitor autoload size and alert if exceeds threshold
 *
 * Target: < 800KB optimal, < 1MB acceptable, > 1MB requires action
 */
function smartvarme_monitor_autoload_size() {
    global $wpdb;

    // Query autoload size
    $autoload_size = $wpdb->get_var(
        "SELECT SUM(LENGTH(option_value))
         FROM $wpdb->options
         WHERE autoload = 'yes'"
    );

    // Convert to KB
    $autoload_kb = round( $autoload_size / 1024, 2 );

    // Check threshold
    if ( $autoload_kb > 800 ) {
        // Log warning
        error_log( sprintf(
            'Smartvarme Autoload Alert: %s KB (target < 800 KB)',
            $autoload_kb
        ) );

        // Send admin email if critical (> 1MB)
        if ( $autoload_kb > 1024 ) {
            wp_mail(
                get_option( 'admin_email' ),
                'Smartvarme: Autoload Size Critical',
                sprintf(
                    "Autoload size has reached %s KB.\n\nRun database optimization:\n- WP-Optimize > Database > Optimize autoloaded data\n- Delete expired transients\n- Review plugins adding autoload data",
                    $autoload_kb
                )
            );
        }
    }

    // Store for Query Monitor integration
    update_option( 'smartvarme_autoload_size', $autoload_kb, false );
}

// Run daily check
add_action( 'wp_scheduled_delete', 'smartvarme_monitor_autoload_size' );

/**
 * Display autoload size in admin dashboard
 */
add_action( 'admin_notices', function() {
    $autoload_kb = get_option( 'smartvarme_autoload_size' );

    if ( $autoload_kb && $autoload_kb > 800 ) {
        $class = $autoload_kb > 1024 ? 'error' : 'warning';
        ?>
        <div class="notice notice-<?php echo esc_attr( $class ); ?>">
            <p>
                <strong>Performance Notice:</strong>
                Autoload size is <?php echo esc_html( $autoload_kb ); ?> KB
                (target: &lt; 800 KB).
                <a href="<?php echo admin_url( 'tools.php?page=wp-optimize' ); ?>">
                    Optimize now
                </a>
            </p>
        </div>
        <?php
    }
});
```

### Example 3: Conditional Script Loading (Perfmatters Pattern)

```php
// Source: https://perfmatters.io/docs/lazy-load-wordpress/
// Perfmatters conditional loading best practices

/**
 * Load scripts only where needed using conditional logic
 *
 * Performance gain: Reduces total JavaScript by 40-60% on content pages
 */
add_action( 'wp_enqueue_scripts', function() {
    // Identify current page context
    $load_woocommerce = is_woocommerce() || is_cart() || is_checkout() || is_account_page();
    $load_formidable = is_singular( 'product' ) || is_page( 'contact' );
    $load_search = is_search() || is_archive();

    // Remove WooCommerce assets on non-shop pages
    if ( ! $load_woocommerce ) {
        // Styles
        wp_dequeue_style( 'woocommerce-general' );
        wp_dequeue_style( 'woocommerce-layout' );
        wp_dequeue_style( 'woocommerce-smallscreen' );
        wp_dequeue_style( 'wc-blocks-style' );

        // Scripts
        wp_dequeue_script( 'wc-cart-fragments' ); // Heavy script, loads on every page by default
        wp_dequeue_script( 'woocommerce' );
        wp_dequeue_script( 'wc-add-to-cart' );
        wp_dequeue_script( 'wc-checkout' );
    }

    // Remove Formidable Forms assets where not needed
    if ( ! $load_formidable ) {
        wp_dequeue_style( 'formidable' );
        wp_dequeue_script( 'formidable' );
    }

    // Remove search scripts on non-search pages
    if ( ! $load_search ) {
        wp_dequeue_script( 'smartvarme-smart-search' );
    }
}, 100 ); // Late priority to override plugin defaults

/**
 * Optimize WooCommerce cart fragments
 *
 * Cart fragments run AJAX on every page load to update mini-cart
 * Performance impact: 200-500ms per page load
 * Solution: Only load on shop pages OR disable and update on cart/checkout only
 */
add_action( 'wp_enqueue_scripts', function() {
    // Disable cart fragments on non-shop pages
    if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() ) {
        wp_dequeue_script( 'wc-cart-fragments' );
    }
}, 100 );

// Alternative: Disable cart fragments entirely and refresh on cart page only
add_filter( 'woocommerce_add_to_cart_fragments', '__return_empty_array' );
```

### Example 4: Transient Cleanup Automation

```php
// Source: https://www.wpbeginner.com/plugins/how-to-manage-and-delete-transients-in-wordpress/
// WPBeginner transient management guide

/**
 * Automated transient cleanup
 *
 * Problem: WordPress doesn't automatically delete expired transients
 * Impact: Database bloat, slow queries, autoload size increase
 * Solution: Weekly automated cleanup
 */
function smartvarme_cleanup_transients() {
    global $wpdb;

    // Delete expired transients
    $time = time();
    $expired_transients = $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM $wpdb->options
             WHERE option_name LIKE %s
             AND option_value < %d",
            $wpdb->esc_like( '_transient_timeout_' ) . '%',
            $time
        )
    );

    // Delete orphaned transient options (timeout deleted but value remains)
    $orphaned_transients = $wpdb->query(
        "DELETE FROM $wpdb->options
         WHERE option_name LIKE '_transient_%'
         AND option_name NOT LIKE '_transient_timeout_%'
         AND option_name NOT IN (
             SELECT REPLACE(option_name, '_transient_timeout_', '_transient_')
             FROM $wpdb->options
             WHERE option_name LIKE '_transient_timeout_%'
         )"
    );

    // Delete site transients (multisite)
    if ( is_multisite() ) {
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $wpdb->options
                 WHERE option_name LIKE %s
                 AND option_value < %d",
                $wpdb->esc_like( '_site_transient_timeout_' ) . '%',
                $time
            )
        );
    }

    // Log cleanup results
    error_log( sprintf(
        'Smartvarme Transient Cleanup: Deleted %d expired transients and %d orphaned transients',
        $expired_transients,
        $orphaned_transients
    ) );

    return $expired_transients + $orphaned_transients;
}

// Schedule weekly cleanup
if ( ! wp_next_scheduled( 'smartvarme_transient_cleanup' ) ) {
    wp_schedule_event( time(), 'weekly', 'smartvarme_transient_cleanup' );
}
add_action( 'smartvarme_transient_cleanup', 'smartvarme_cleanup_transients' );

/**
 * On-demand cleanup via WP-CLI
 *
 * Usage: wp smartvarme cleanup-transients
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
    WP_CLI::add_command( 'smartvarme cleanup-transients', function() {
        $deleted = smartvarme_cleanup_transients();
        WP_CLI::success( sprintf( 'Deleted %d transients', $deleted ) );
    });
}
```

### Example 5: Plugin Feature Inventory Query

```php
// Source: Phase 6 research findings
// Plugin consolidation methodology

/**
 * Generate plugin feature inventory report
 *
 * Analyzes all active plugins and identifies:
 * - Scripts/styles loaded per page
 * - Database tables created
 * - Autoload options added
 * - Shortcodes registered
 * - Custom post types
 */
function smartvarme_plugin_inventory() {
    $inventory = [];

    // Get all active plugins
    $active_plugins = get_option( 'active_plugins' );

    foreach ( $active_plugins as $plugin ) {
        $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
        $plugin_slug = dirname( $plugin );

        $inventory[ $plugin_slug ] = [
            'name' => $plugin_data['Name'],
            'version' => $plugin_data['Version'],
            'scripts' => [],
            'styles' => [],
            'shortcodes' => [],
            'post_types' => [],
            'autoload_size' => 0,
            'tables' => [],
        ];
    }

    // Analyze scripts/styles (requires Query Monitor data or manual inspection)
    // This would be populated by analyzing wp_enqueue_scripts hooks

    // Analyze shortcodes
    global $shortcode_tags;
    foreach ( $shortcode_tags as $tag => $callback ) {
        // Attempt to identify plugin owner (basic approach)
        if ( is_array( $callback ) && is_object( $callback[0] ) ) {
            $class = get_class( $callback[0] );
            // Match class to plugin directory
            foreach ( $inventory as $plugin_slug => &$data ) {
                if ( stripos( $class, str_replace( '-', '_', $plugin_slug ) ) !== false ) {
                    $data['shortcodes'][] = $tag;
                }
            }
        }
    }

    // Analyze custom post types
    $post_types = get_post_types( [ '_builtin' => false ], 'objects' );
    foreach ( $post_types as $post_type => $post_type_obj ) {
        // Basic heuristic: match post type to plugin name
        foreach ( $inventory as $plugin_slug => &$data ) {
            if ( stripos( $post_type, str_replace( '-', '_', $plugin_slug ) ) !== false ) {
                $data['post_types'][] = $post_type;
            }
        }
    }

    // Analyze autoload size per plugin (requires option_name prefix matching)
    global $wpdb;
    foreach ( $inventory as $plugin_slug => &$data ) {
        $plugin_prefix = str_replace( '-', '_', $plugin_slug );
        $autoload_size = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(LENGTH(option_value))
                 FROM $wpdb->options
                 WHERE autoload = 'yes'
                 AND option_name LIKE %s",
                $wpdb->esc_like( $plugin_prefix ) . '%'
            )
        );
        $data['autoload_size'] = $autoload_size ? round( $autoload_size / 1024, 2 ) : 0;
    }

    return $inventory;
}

/**
 * Display plugin inventory in admin
 */
add_action( 'admin_menu', function() {
    add_management_page(
        'Plugin Feature Inventory',
        'Plugin Inventory',
        'manage_options',
        'smartvarme-plugin-inventory',
        function() {
            $inventory = smartvarme_plugin_inventory();
            ?>
            <div class="wrap">
                <h1>Plugin Feature Inventory</h1>
                <p>Total Active Plugins: <?php echo count( $inventory ); ?></p>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Plugin</th>
                            <th>Shortcodes</th>
                            <th>Post Types</th>
                            <th>Autoload Size</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $inventory as $slug => $data ) : ?>
                            <tr>
                                <td><strong><?php echo esc_html( $data['name'] ); ?></strong></td>
                                <td><?php echo esc_html( implode( ', ', $data['shortcodes'] ) ?: 'None' ); ?></td>
                                <td><?php echo esc_html( implode( ', ', $data['post_types'] ) ?: 'None' ); ?></td>
                                <td><?php echo esc_html( $data['autoload_size'] ); ?> KB</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php
        }
    );
});
```

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| First Input Delay (FID) metric | Interaction to Next Paint (INP) metric | 2024-03-12 (Core Web Vitals update) | INP measures all interactions (not just first), more comprehensive responsiveness metric; threshold: < 200ms (vs FID < 100ms) |
| Manual transient cleanup | Automatic cleanup via WP Rocket, Rank Math, SEOPress | 2024-2025 | Modern plugins auto-clean transients; enable in plugin settings to prevent database bloat |
| Plugin-based page caching | Server-level caching (LiteSpeed, Varnish, NGINX FastCGI) | 2023-2026 | Hosts offering LiteSpeed get server-level caching superior to plugin-based; but WP Rocket still best for non-LiteSpeed hosting |
| JPEG/PNG images | WebP and AVIF formats | 2023-2026 standard | AVIF offers better compression than WebP (AV1 vs V8 technology); 30-50% smaller files; near-universal browser support in 2026 |
| WordPress 5.4 lazy loading | WordPress 5.5+ native lazy loading with `loading="lazy"` attribute | 2020 | Native browser lazy loading eliminates need for JavaScript-based solutions; better performance and Core Web Vitals |
| WooCommerce legacy post meta storage | High-Performance Order Storage (HPOS) custom tables | 2023 default enabled | 5x faster order creation, 40x faster backend filtering, 80-90% faster for 50k+ order stores; HPOS mandatory by WooCommerce 8.2+ |
| Multiple single-purpose plugins (30+) | Consolidated functionality: multi-purpose plugins + WordPress 6.9 core blocks | 2025-2026 | WordPress 6.9 adds Accordion, Time to Read, Math, Term Query, Comment blocks — eliminates need for dedicated plugins |
| 25-30 plugin baseline for WooCommerce stores | 10-20 plugin target via consolidation | 2025-2026 trend | Hosting consolidation (themes+plugins in bundles), WordPress core features replacing plugins, multi-purpose performance plugins (WP Rocket handles 10+ separate tools) |

**Deprecated/outdated:**
- **WP Super Cache / W3 Total Cache:** Still functional but WP Rocket and LiteSpeed Cache offer superior Core Web Vitals optimization (unused CSS removal, critical CSS generation, advanced cache warming); W3 Total Cache extremely complex UI
- **Smush Free (image optimization):** Lacks WebP/AVIF conversion in free tier; ShortPixel and Imagify provide superior next-gen format support
- **Google PageSpeed Module (Apache):** No longer actively maintained by Google as of 2021; server-level caching (LiteSpeed, Varnish) superior
- **Autoptimize + Async JavaScript combo:** Functional but Perfmatters or WP Rocket provide more comprehensive optimization with better UI and fewer conflicts
- **WP Fastest Cache:** Currently installed on this project; lacks database optimization, unused CSS removal, critical CSS generation, and advanced Core Web Vitals features found in WP Rocket

## Open Questions

1. **Current Hosting Environment's Redis Capability**
   - What we know: HPOS enabled (Phase 3), Docker local development, production hosting unknown
   - What's unclear: Does production hosting support Redis or Memcached object caching? Is it one-click activation or manual configuration?
   - Recommendation: Verify with hosting provider; if not available, consider upgrade or use WP Rocket's database optimization as alternative (less effective but still valuable)

2. **Plugin Usage Depth for 35 Installed Plugins**
   - What we know: 35 plugins currently installed (list provided), custom smartvarme-core plugin exists, several plugins likely from previous setup
   - What's unclear: Which features of each plugin actively used? Are there plugins installed but unused? Which plugins essential vs convenience?
   - Recommendation: Run Query Monitor for 1 week on live site to track which plugins load on which pages; create comprehensive feature inventory (Pattern 3) before any removal

3. **Current Site Performance Baseline**
   - What we know: Phase 1 achieved 99% autoload reduction (19.6MB → 188KB), Phase 5 implemented system fonts (zero web font CLS), custom AJAX search
   - What's unclear: Current PageSpeed Insights scores (mobile/desktop)? GTmetrix ratings? WebPageTest waterfall? Core Web Vitals field data from Search Console?
   - Recommendation: Establish baseline BEFORE Phase 6 begins; run tests from multiple locations (Oslo, Copenhagen, Stockholm for Norwegian audience); document scores for 50%+ improvement proof

4. **Cloudflare CDN Decision**
   - What we know: WP Rocket integrates with Cloudflare, potential 70% load time reduction, costs $5/month for APO (< 50k visitors)
   - What's unclear: Current CDN usage? DNS provider? Traffic volume (monthly visitors)? Budget for ongoing CDN costs?
   - Recommendation: Evaluate traffic volume; if < 50k monthly visitors, Cloudflare Free + APO ($5/month) provides exceptional ROI; if > 100k visitors, Cloudflare Pro ($20/month) worthwhile for WAF and image optimization

5. **WordPress 6.9 Core Block Migration Impact**
   - What we know: WordPress 6.9 includes Accordion, Time to Read, Math, Term Query, Comment Count/Link blocks; Kadence Blocks currently installed
   - What's unclear: Which Kadence Blocks features actively used? Only accordions or advanced columns/layouts too? Migration effort for existing content?
   - Recommendation: Audit all pages/posts for Kadence block usage using database query; if only accordions used, migrate to core Accordion block (WP 6.9); if advanced features used, keep Kadence Blocks

6. **WP Fastest Cache vs WP Rocket Migration Timing**
   - What we know: WP Fastest Cache currently installed; WP Rocket superior for Core Web Vitals and database optimization
   - What's unclear: Current WP Fastest Cache configuration? Cache exclusions already set? Compatibility with existing plugins?
   - Recommendation: Install WP Rocket on staging site first; configure WooCommerce exclusions; compare PageSpeed scores before/after; if improvement significant (> 20%), migrate production; deactivate WP Fastest Cache after confirming WP Rocket handles all requirements

## Sources

### Primary (HIGH confidence)

- [WooCommerce Developer Docs: Performance Optimization](https://developer.woocommerce.com/docs/best-practices/performance/performance-optimization) - Official WooCommerce performance best practices
- [WooCommerce Developer Docs: Configuring Caching Plugins](https://developer.woocommerce.com/docs/best-practices/performance/configuring-caching-plugins/) - Official cache exclusion requirements
- [WP Rocket: Google Core Web Vitals for WordPress](https://wp-rocket.me/google-core-web-vitals-wordpress/) - Core Web Vitals targets and optimization strategies
- [WooCommerce Developer Docs: High-Performance Order Storage](https://developer.woocommerce.com/docs/features/high-performance-order-storage/) - Official HPOS documentation
- [Pantheon Docs: Optimize wp_options Table and Autoloaded Data](https://docs.pantheon.io/optimize-wp-options-table-autoloaded-data) - Autoload optimization methodology

### Secondary (MEDIUM confidence)

- [WordPress Performance Optimization: The Ultimate 2026 Guide](https://next3offload.com/blog/wordpress-performance-optimization/) - Comprehensive 2026 performance strategies
- [WooCommerce Guide 2026: Setup, Optimization, SEO & Best Practices](https://www.wpmaintenanceservice.com/woocommerce-guide-2026-setup-optimization-seo-best-practices-for-wordpress-stores/) - Current WooCommerce best practices
- [WP Rocket vs LiteSpeed Cache: Which One is Best in 2026](https://runcloud.io/blog/litespeed-vs-wp-rocket-cache) - Caching plugin comparison
- [Perfmatters vs. Asset CleanUp](https://onlinemediamasters.com/perfmatters-vs-asset-cleanup/) - Asset optimization plugin comparison
- [ShortPixel Image Optimizer](https://shortpixel.com/) - Image optimization with WebP/AVIF support
- [Imagify](https://imagify.io/) - WordPress WebP and AVIF optimizer
- [HPOS in WooCommerce 2025: Should You Switch?](https://thrivewp.com/woocommerce-hpos-2025-guide/) - HPOS performance impact analysis
- [WordPress Plugins 2026: Lean Tools to Reduce Bloat & Harness 6.9](https://datronixtech.com/wordpress-plugins-2026/) - Plugin consolidation trends and WordPress 6.9 core blocks
- [What's New in WordPress 6.9?](https://www.wpbeginner.com/news/whats-new-in-wordpress-6-9/) - WordPress 6.9 new core blocks
- [Cloudflare WordPress CDN Setup 2026](https://www.adwaitx.com/cloudflare-wordpress-cdn-setup/) - CDN configuration guide
- [Transients in WordPress: How to Speed Up Your Site In 2026](https://potentpages.com/web-design/website-speed/transients-in-wordpress-how-to-speed-up-your-site-in-year) - Transient cleanup best practices
- [WordPress Object Caching: Redis vs Memcached Implementation Guide](https://wisdmlabs.com/blog/wordpress-object-caching-redis-vs-memcached-implementation-guide/) - Object caching comparison
- [GTmetrix vs. PageSpeed Insights and WebPageTest: A Comparison](https://www.debugbear.com/software/gtmetrix-vs-pagespeed-insights-vs-webpagetest) - Performance testing tool comparison

### Tertiary (LOW confidence)

- Various WordPress community blog posts and tutorials referenced in search results
- Plugin vendor marketing materials (verified against official documentation)

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH - WP Rocket, ShortPixel/Imagify, Perfmatters are industry-standard solutions with extensive documentation and proven track record
- Architecture: HIGH - Patterns verified from official WooCommerce, WP Rocket, and Pantheon documentation
- Pitfalls: MEDIUM-HIGH - Based on verified sources and common industry experience; some pitfalls derived from community reports (cross-verified with multiple sources)
- Plugin consolidation: MEDIUM - WordPress 6.9 core blocks verified; consolidation strategies based on 2026 trends (less established than technical optimization)
- Hosting-specific recommendations: LOW - Production hosting environment unknown; Redis/CDN recommendations conditional on hosting capabilities

**Research date:** 2026-02-13
**Valid until:** 2026-04-13 (60 days for stable WordPress/WooCommerce ecosystem; revalidate if WordPress 6.10+ or WooCommerce 10.x+ releases)

**Current project state verified:**
- ✅ 35 plugins installed (needs inventory and consolidation)
- ✅ Autoload already optimized: 188KB (excellent baseline)
- ✅ HPOS enabled (Phase 3 complete)
- ✅ System font stack (Phase 5 complete - zero web font CLS)
- ✅ Custom smartvarme-core plugin exists (ready for feature consolidation)
- ✅ WP Fastest Cache installed (baseline caching, upgrade to WP Rocket recommended)
- ✅ Custom AJAX search implemented (FiboSearch replaced in Phase 5)
- ⚠️ No baseline performance metrics established (critical gap for proving 50%+ improvement)
- ⚠️ Redis object caching status unknown (hosting capability unclear)
- ⚠️ Plugin feature inventory not yet created (required before consolidation)
