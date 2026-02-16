# Architecture Research

**Domain:** WordPress/WooCommerce E-commerce Site
**Researched:** 2026-02-11
**Confidence:** HIGH

## Standard Architecture for Modern WordPress/WooCommerce

### System Overview

```
┌─────────────────────────────────────────────────────────────┐
│                     PRESENTATION LAYER                       │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  Block Theme (FSE) / Hybrid Theme Architecture       │   │
│  │  - Template Parts (header, footer, sidebar)          │   │
│  │  - Page Templates (block-based)                      │   │
│  │  - Pattern Library (reusable compositions)           │   │
│  └──────────────┬────────────────────────────────────────┘   │
│                 │                                            │
├─────────────────┴────────────────────────────────────────────┤
│                     COMPONENT LAYER                          │
│  ┌────────────┐  ┌────────────┐  ┌────────────┐             │
│  │   Custom   │  │ WooCommerce│  │   Content  │             │
│  │   Blocks   │  │   Blocks   │  │   Blocks   │             │
│  └─────┬──────┘  └─────┬──────┘  └─────┬──────┘             │
│        │                │                │                   │
├────────┴────────────────┴────────────────┴───────────────────┤
│                     BUSINESS LOGIC LAYER                     │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐       │
│  │  Custom      │  │  WooCommerce │  │  WordPress   │       │
│  │  Plugin(s)   │  │  Core        │  │  Core        │       │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘       │
│         │                  │                  │              │
├─────────┴──────────────────┴──────────────────┴──────────────┤
│                        DATA LAYER                            │
│  ┌──────────────────────────────────────────────────────┐    │
│  │              MySQL Database + Object Cache           │    │
│  │         (Redis/Memcached for performance)            │    │
│  └──────────────────────────────────────────────────────┘    │
├─────────────────────────────────────────────────────────────┤
│                   INFRASTRUCTURE LAYER                       │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐    │
│  │   CDN    │  │ Caching  │  │  PHP 8.2+│  │  Server  │    │
│  │ (Assets) │  │  Layer   │  │  Opcache │  │  Config  │    │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘    │
└─────────────────────────────────────────────────────────────┘
```

### Component Responsibilities

| Component | Responsibility | Typical Implementation |
|-----------|----------------|------------------------|
| **Block Theme** | Visual presentation, layout structure, design consistency | FSE-enabled theme with theme.json, HTML templates, minimal PHP |
| **Custom Blocks** | Reusable UI components with specific functionality | Gutenberg blocks using @wordpress/scripts, React-based |
| **Custom Plugin(s)** | Site-specific business logic, integrations, features | Standalone WordPress plugin with proper architecture |
| **WooCommerce Core** | E-commerce functionality (products, cart, checkout, orders) | WooCommerce plugin with custom hooks/filters |
| **Pattern Library** | Pre-composed block arrangements for consistency | Block patterns stored in theme or plugin |
| **Asset Build System** | Compile, optimize, and bundle frontend assets | @wordpress/scripts (webpack) or Vite for modern builds |
| **Caching Layer** | Serve static/cached content, reduce database queries | Plugin (WP Rocket) + server (Redis/Memcached) + CDN |

## Recommended Project Structure

### Theme Structure (Block Theme with Hybrid Support)

```
wp-content/themes/smartvarme-theme/
├── assets/                    # Build output directory
│   ├── css/                  # Compiled CSS
│   ├── js/                   # Compiled JavaScript
│   └── images/               # Optimized images
├── src/                      # Source files for build
│   ├── blocks/               # Custom block development
│   │   ├── product-grid/    # Individual block folders
│   │   │   ├── index.js     # Block registration
│   │   │   ├── edit.js      # Editor component
│   │   │   ├── save.js      # Frontend output
│   │   │   └── style.scss   # Block styles
│   │   └── hero-section/
│   ├── js/                   # Global scripts
│   │   └── main.js          # Entry point
│   └── scss/                 # Global styles
│       ├── base/            # Resets, typography
│       ├── components/      # Reusable components
│       └── utilities/       # Helpers, mixins
├── patterns/                 # Block patterns
│   ├── header-default.php
│   ├── footer-default.php
│   └── hero-cta.php
├── parts/                    # Template parts (FSE)
│   ├── header.html
│   ├── footer.html
│   └── sidebar.html
├── templates/                # Page templates (FSE)
│   ├── index.html
│   ├── single.html
│   ├── archive-product.html
│   └── page-checkout.html
├── inc/                      # PHP functionality
│   ├── setup.php            # Theme setup
│   ├── enqueue.php          # Asset loading
│   └── customizer.php       # Customizer options
├── functions.php             # Theme functions (minimal)
├── style.css                 # Theme header/metadata
├── theme.json                # Global settings/styles (FSE)
├── package.json              # Build dependencies
└── webpack.config.js         # Build configuration
```

### Plugin Structure (Custom Functionality)

```
wp-content/plugins/smartvarme-core/
├── src/                      # Source files
│   ├── blocks/              # Plugin-provided blocks
│   ├── integrations/        # Third-party integrations
│   │   ├── erp/            # ERP system connection
│   │   └── shipping/       # Shipping providers
│   ├── admin/              # Admin UI components
│   └── public/             # Frontend components
├── includes/                # Core PHP functionality
│   ├── class-plugin.php    # Main plugin class
│   ├── api/                # REST API endpoints
│   ├── woocommerce/        # WooCommerce customizations
│   │   ├── class-product-sync.php
│   │   ├── class-custom-fields.php
│   │   └── hooks/          # WooCommerce hooks
│   └── utils/              # Utility functions
├── assets/                  # Build output
│   ├── css/
│   └── js/
├── languages/               # Translation files
├── smartvarme-core.php      # Plugin entry point
├── uninstall.php            # Cleanup on uninstall
└── package.json             # Build dependencies
```

### Structure Rationale

- **Theme focuses on presentation**: All design, layout, and visual elements live in the theme. Switching themes should only affect appearance, not functionality.

- **Plugin contains business logic**: Custom features, integrations, and site-specific functionality live in a plugin. This persists across theme changes.

- **Block-based architecture**: Both theme and plugin can register custom blocks, providing flexibility and future-proofing as WordPress moves toward Full Site Editing.

- **Separation of source and build**: `src/` contains editable source files; `assets/` contains compiled production files. This enables modern development workflows.

- **Component isolation**: Each custom block is self-contained with its own JavaScript, styles, and logic, making maintenance easier.

## Architectural Patterns

### Pattern 1: Component-Based Block Development

**What:** Treat each UI element as a self-contained, reusable block with its own logic, styles, and template.

**When to use:** For any repeating UI pattern that needs to be editable in the block editor (product grids, hero sections, CTAs, feature lists).

**Trade-offs:**
- **Pros:** Maximum reusability, editor-friendly, future-proof, easier maintenance
- **Cons:** More initial setup, requires understanding Gutenberg block API

**Example:**
```javascript
// src/blocks/product-highlight/index.js
import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import save from './save';
import './style.scss';

registerBlockType('smartvarme/product-highlight', {
    title: 'Product Highlight',
    icon: 'products',
    category: 'woocommerce',
    attributes: {
        productId: { type: 'number' },
        showPrice: { type: 'boolean', default: true },
        ctaText: { type: 'string', default: 'Shop Now' }
    },
    edit: Edit,
    save: save
});
```

### Pattern 2: Hooks & Filters Over Core Modification

**What:** Use WordPress/WooCommerce hooks and filters to extend functionality without modifying core files.

**When to use:** Always. Never modify core WordPress or WooCommerce files.

**Trade-offs:**
- **Pros:** Update-safe, follows WordPress standards, maintainable
- **Cons:** Requires learning hook system, occasionally less direct than editing core

**Example:**
```php
// plugins/smartvarme-core/includes/woocommerce/hooks/product-display.php

// Add custom field to product display
add_action('woocommerce_single_product_summary', 'smartvarme_add_delivery_info', 25);
function smartvarme_add_delivery_info() {
    global $product;
    $delivery_days = get_post_meta($product->get_id(), '_delivery_days', true);
    if ($delivery_days) {
        echo '<div class="delivery-info">Leveres på ' . esc_html($delivery_days) . ' dager</div>';
    }
}

// Modify product query for custom sorting
add_filter('woocommerce_product_query_meta_query', 'smartvarme_custom_product_sorting');
function smartvarme_custom_product_sorting($meta_query) {
    if (is_shop() && isset($_GET['orderby']) && $_GET['orderby'] === 'delivery') {
        $meta_query[] = [
            'key' => '_delivery_days',
            'type' => 'NUMERIC'
        ];
    }
    return $meta_query;
}
```

### Pattern 3: Conditional Asset Loading

**What:** Load CSS and JavaScript only on pages where they're needed, not globally.

**When to use:** For all custom scripts and styles, especially block-specific assets.

**Trade-offs:**
- **Pros:** Significantly faster page loads, better performance scores
- **Cons:** Requires more careful enqueue logic

**Example:**
```php
// themes/smartvarme-theme/inc/enqueue.php

function smartvarme_enqueue_assets() {
    // Only load on product pages
    if (is_product()) {
        wp_enqueue_script(
            'smartvarme-product-viewer',
            get_theme_file_uri('/assets/js/product-viewer.js'),
            ['jquery'],
            '1.0.0',
            true
        );
    }

    // Only load checkout scripts on checkout
    if (is_checkout()) {
        wp_enqueue_script(
            'smartvarme-checkout',
            get_theme_file_uri('/assets/js/checkout.js'),
            ['wc-checkout'],
            '1.0.0',
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'smartvarme_enqueue_assets');
```

### Pattern 4: Template Hierarchy with Block Templates

**What:** Use WordPress template hierarchy with modern block templates (HTML) instead of traditional PHP templates where possible.

**When to use:** For new projects or when modernizing existing themes. Provides visual editing in Site Editor.

**Trade-offs:**
- **Pros:** Visual editing, no-code customization, modern WordPress approach
- **Cons:** Less PHP flexibility for complex logic, learning curve for developers used to PHP templates

**Example:**
```html
<!-- templates/single-product.html -->
<!-- wp:template-part {"slug":"header"} /-->

<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
    <!-- wp:woocommerce/product-image-gallery /-->

    <!-- wp:woocommerce/product-price /-->

    <!-- wp:woocommerce/add-to-cart-form /-->

    <!-- wp:woocommerce/product-details /-->
</div>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer"} /-->
```

### Pattern 5: REST API for Custom Integrations

**What:** Use WordPress REST API for custom endpoints rather than admin-ajax for modern integrations.

**When to use:** For any custom AJAX functionality, third-party integrations, or headless features.

**Trade-offs:**
- **Pros:** RESTful, cacheable, modern standard, better for headless future
- **Cons:** Slightly more boilerplate than admin-ajax

**Example:**
```php
// plugins/smartvarme-core/includes/api/class-inventory-api.php

add_action('rest_api_init', function() {
    register_rest_route('smartvarme/v1', '/inventory/(?P<sku>[a-zA-Z0-9-]+)', [
        'methods' => 'GET',
        'callback' => 'smartvarme_get_inventory',
        'permission_callback' => '__return_true',
        'args' => [
            'sku' => [
                'required' => true,
                'validate_callback' => function($param) {
                    return is_string($param);
                }
            ]
        ]
    ]);
});

function smartvarme_get_inventory($request) {
    $sku = $request['sku'];
    $product = wc_get_product_id_by_sku($sku);

    if (!$product) {
        return new WP_Error('not_found', 'Product not found', ['status' => 404]);
    }

    return rest_ensure_response([
        'sku' => $sku,
        'stock' => wc_get_product($product)->get_stock_quantity(),
        'in_stock' => wc_get_product($product)->is_in_stock()
    ]);
}
```

## Data Flow

### Request Flow (Traditional)

```
[User Request]
    ↓
[WordPress Core] → [Load Theme] → [Apply Hooks/Filters]
    ↓                                      ↓
[Query Database] ← [WooCommerce] ← [Custom Plugin]
    ↓
[Template Hierarchy] → [Block Rendering] → [Asset Loading]
    ↓
[Output Cache] → [CDN] → [Browser]
```

### Block Editor Data Flow

```
[User in Editor]
    ↓
[Gutenberg/Block Editor] ← [Block Definitions]
    ↓                              ↓
[Edit Component (React)] ← [Block Attributes]
    ↓
[Save Function] → [Database (post_content)]
    ↓
[Frontend Render] → [Cached HTML]
```

### WooCommerce Product Flow

```
[Product Created/Updated]
    ↓
[WooCommerce Actions] → [Custom Plugin Hooks]
    ↓                           ↓
[Database Write] → [Cache Invalidation] → [Object Cache Update]
    ↓
[Frontend Display] ← [Query with Cache] ← [Template]
```

### Key Data Flows

1. **Product Display Flow:** Database → WooCommerce → Object Cache (Redis) → Template → Block Rendering → CDN → Browser. First load hits database; subsequent loads serve from cache.

2. **Cart/Checkout Flow:** User Action → AJAX/REST API → WooCommerce Session → Database Write → Response → Frontend Update (no full page reload). Sessions bypass most caching.

3. **Asset Loading Flow:** Browser Request → Check CDN → Check Browser Cache → Load from Server → Compile/Minify → Serve with Cache Headers → Browser Caches. Build process pre-optimizes assets.

4. **Admin Edit Flow:** Block Editor → React Components → Attributes → Save to post_content → Database → Cache Clear → Frontend Re-render. Editor changes immediately visible.

## Build Order for Incremental Rollout

### Phase 1: Foundation & Content System (Weeks 1-3)

**Build:**
1. New block theme skeleton with theme.json configuration
2. Basic template structure (header, footer, page, post)
3. Core content blocks (hero, text, images, CTAs)
4. Pattern library for common layouts
5. Asset build system (@wordpress/scripts or Vite)
6. Basic performance optimization (caching, image optimization)

**Deploy:** Parallel to existing site initially, then migrate static/content pages first

**Rationale:** Content pages have no e-commerce dependencies, lowest risk, establishes foundation

### Phase 2: Product Display System (Weeks 4-6)

**Build:**
1. Custom product blocks (grid, list, featured product)
2. Product page templates (single product, archive)
3. WooCommerce block customizations
4. Product filtering and sorting
5. Product search integration

**Deploy:** Launch on category pages and product archives

**Rationale:** Products are view-only, no transaction risk, establishes product presentation layer

### Phase 3: Cart & Checkout (Weeks 7-9)

**Build:**
1. Custom cart block/template
2. Checkout flow optimization
3. Payment gateway integration verification
4. Order confirmation and emails
5. Custom checkout fields if needed

**Deploy:** Staged rollout with A/B testing

**Rationale:** Highest risk area, requires thorough testing, gradual rollout minimizes revenue impact

### Phase 4: User Accounts & My Account (Weeks 10-11)

**Build:**
1. Account pages templates
2. Order history display
3. Address management
4. Account dashboard blocks

**Deploy:** Full deployment once checkout proven stable

**Rationale:** Depends on checkout working properly, lower urgency than transaction flow

### Phase 5: Custom Features & Integrations (Weeks 12-14)

**Build:**
1. ERP integration (if applicable)
2. Custom reporting
3. Advanced filtering
4. Specialized product features
5. Marketing integrations

**Deploy:** Feature by feature as completed

**Rationale:** Site-specific functionality, can be rolled out independently

### Phase 6: Plugin Consolidation & Optimization (Weeks 15-16)

**Build:**
1. Audit and remove unnecessary plugins
2. Consolidate functionality into custom plugin
3. Performance optimization pass
4. Database optimization
5. Final caching configuration

**Deploy:** Backend optimization, transparent to users

**Rationale:** Clean up tech debt, optimize performance, prepare for maintenance phase

### Incremental Deployment Strategy

**Approach:** Blue-Green with Gradual Rollout

1. **Parallel Development:** Build new architecture alongside existing site
2. **Component Testing:** Test each phase in staging with production data clone
3. **Canary Releases:** Deploy to small percentage of traffic initially
4. **Feature Flags:** Use flags to enable/disable new features without deployment
5. **Rollback Plan:** Always maintain ability to revert to previous version
6. **Monitoring:** Track performance metrics and errors in real-time

**URL Structure:**
- Existing site: `smartvarme.no`
- New build: `new.smartvarme.no` (during development)
- Gradual rollout: Route percentage of traffic to new system using load balancer or CDN rules
- Content-first pages go first, then products, then checkout

## Scaling Considerations

| Scale | Architecture Adjustments |
|-------|--------------------------|
| **0-10k visitors/month** | Shared hosting with basic caching plugin (WP Rocket) sufficient. Use standard WooCommerce setup. Focus on image optimization and minification. |
| **10k-100k visitors/month** | Managed WordPress hosting (Kinsta, WP Engine) with Redis object cache. Enable CDN for assets. Optimize database queries. Consider query monitor for bottlenecks. |
| **100k-500k visitors/month** | Application-level caching, database read replicas, separate media server or cloud storage (S3). Advanced CDN with edge caching. Database optimization critical. |
| **500k+ visitors/month** | Consider headless architecture for frontend, dedicated database server, load balancing, advanced caching strategies, microservices for integrations. Full DevOps setup. |

### Scaling Priorities

1. **First bottleneck: Database Queries**
   - **Symptom:** Slow product pages, long TTFB
   - **Fix:** Implement Redis object cache, optimize WooCommerce queries, add database indexes for custom fields, use transients for expensive queries
   - **Estimated improvement:** 40-60% faster page loads

2. **Second bottleneck: Asset Loading**
   - **Symptom:** High page weight, slow initial paint
   - **Fix:** Implement CDN, enable GZIP/Brotli compression, lazy load images, code splitting for JavaScript, defer non-critical CSS
   - **Estimated improvement:** 30-50% faster initial render

3. **Third bottleneck: Server Response Time**
   - **Symptom:** High TTFB even with caching
   - **Fix:** Upgrade hosting tier, implement Varnish cache, use application-level caching, optimize PHP with OPcache and PHP 8.2+
   - **Estimated improvement:** Sub-200ms TTFB

4. **Fourth bottleneck: Third-Party Scripts**
   - **Symptom:** High Total Blocking Time, poor Interaction to Next Paint
   - **Fix:** Audit and remove unnecessary plugins, lazy load analytics/marketing scripts, use Partytown for web workers, consolidate tracking
   - **Estimated improvement:** 20-40% better INP scores

## Anti-Patterns to Avoid

### Anti-Pattern 1: Putting Business Logic in Theme

**What people do:** Add custom functionality, integrations, or business rules directly in theme's `functions.php` or template files.

**Why it's wrong:** Switching themes breaks functionality. Mixing concerns makes maintenance difficult. Logic and presentation should be separate.

**Do this instead:** Create a custom plugin for all business logic and integrations. Theme should only handle presentation and design. Use child theme for theme customizations, plugin for features.

### Anti-Pattern 2: Installing Too Many Plugins

**What people do:** Install separate plugins for every small feature, ending up with 30+ active plugins.

**Why it's wrong:** Each plugin adds load time, increases attack surface, creates compatibility issues, complicates updates, degrades performance significantly.

**Do this instead:** Consolidate functionality into one well-architected custom plugin. Use multi-purpose plugins where appropriate. Audit plugins regularly and remove unused ones. Target: Under 15 active plugins.

### Anti-Pattern 3: Ignoring Block Editor

**What people do:** Continue building with page builders (Elementor, Divi) or pure PHP templates, ignoring Gutenberg.

**Why it's wrong:** Block editor is WordPress future. Page builders add significant performance overhead. Missing out on native WordPress features and improvements.

**Do this instead:** Embrace block-based architecture. Build custom blocks for unique needs. Use theme.json for design tokens. Leverage Full Site Editing. Enable content team to compose pages visually.

### Anti-Pattern 4: No Build Process

**What people do:** Write JavaScript and CSS directly in theme, load unminified files, no bundling or optimization.

**Why it's wrong:** Unoptimized assets hurt performance. No modern JavaScript features. Difficult dependency management. Poor developer experience.

**Do this instead:** Use @wordpress/scripts or Vite for build process. Write modern JavaScript (ES6+), use SCSS for styles, implement code splitting, minify and optimize automatically. Version control source files, deploy compiled assets.

### Anti-Pattern 5: Modifying Core Files

**What people do:** Edit WordPress core or WooCommerce plugin files directly to change functionality.

**Why it's wrong:** Updates overwrite changes. Breaks update process. Creates security vulnerabilities. Makes debugging impossible.

**Do this instead:** Always use hooks and filters. WordPress has thousands of actions and filters. If needed functionality isn't available via hook, propose it to WordPress/WooCommerce core or wrap in custom function. Never touch core.

### Anti-Pattern 6: Global JavaScript and CSS Loading

**What people do:** Enqueue all scripts and styles globally on every page.

**Why it's wrong:** Checkout doesn't need homepage hero script. Blog posts don't need product viewer. Wastes bandwidth, slows page loads, hurts performance scores.

**Do this instead:** Use conditional loading based on page type. Load scripts only where needed. Use `wp_enqueue_script` conditions, localize scripts with only necessary data, implement critical CSS for above-the-fold content.

### Anti-Pattern 7: Ignoring Caching Compatibility

**What people do:** Build features that bypass or break caching, add dynamic content everywhere, use uncacheable queries.

**Why it's wrong:** Caching is critical for WooCommerce performance. Dynamic content kills cache effectiveness. Every uncached request hits database.

**Do this instead:** Design for caching from start. Use AJAX/REST API for dynamic elements. Fragment caching for personalized sections. Exclude only cart/checkout from caching. Test with caching enabled.

## Theme Architecture Decision: Block Theme vs Classic Theme

### Recommendation: Hybrid Approach (Block Theme with Classic Fallbacks)

**Why Hybrid:**

1. **Future-Proof:** Block themes are WordPress direction. Full Site Editing is becoming standard.
2. **Flexibility:** Can use visual editor for most layouts, custom code when needed.
3. **Performance:** Block themes load only styles for rendered blocks, faster than classic themes.
4. **Gradual Adoption:** Can migrate incrementally without full rewrite.

**Implementation:**

```
smartvarme-theme/
├── templates/              # Block templates (HTML)
│   ├── index.html
│   ├── single-product.html
│   └── page.html
├── parts/                  # Template parts
│   ├── header.html
│   └── footer.html
├── templates-php/          # PHP fallbacks for complex logic
│   └── archive-complex.php
├── theme.json             # Global settings (required for block theme)
└── functions.php          # Minimal PHP logic
```

**Block Theme Benefits:**
- Visual editing in Site Editor
- No code needed for layout changes
- Consistent design system via theme.json
- Better performance through selective style loading

**When to Use PHP Templates:**
- Complex conditional logic
- Heavy WooCommerce customization
- Dynamic content based on user state
- Gradual migration from existing classic theme

## Asset Optimization Strategy

### Build Tool: @wordpress/scripts (Current) → Consider Vite (Future)

**Current State:** Using @wordpress/scripts (webpack-based) - already in package.json

**Recommendation:** Stay with @wordpress/scripts initially, evaluate Vite migration in Phase 6

**Why:**
- @wordpress/scripts is WordPress standard, well-supported, integrates seamlessly
- Vite is 2.6x faster for builds, 19% smaller bundles, better DX
- Migration effort not justified for Phase 1, but valuable for Phase 6 optimization

### Optimization Techniques

| Technique | Implementation | Impact |
|-----------|----------------|--------|
| **Image Optimization** | WebP/AVIF format, lazy loading, responsive images, CDN delivery | 40-70% page weight reduction |
| **Code Splitting** | Dynamic imports, route-based splitting, async components | 30-50% faster initial load |
| **CSS Optimization** | Critical CSS inline, defer non-critical, purge unused | 20-40% faster First Contentful Paint |
| **JavaScript Optimization** | Minification, tree shaking, defer loading, async scripts | 25-45% faster Time to Interactive |
| **Caching Strategy** | Browser cache (static assets), object cache (database queries), page cache (HTML output), CDN cache (edge) | 60-80% faster repeat visits |
| **Database Optimization** | Query optimization, indexes on custom fields, transients for expensive queries, object cache integration | 40-60% faster database operations |

### Asset Loading Strategy

**Critical Path:**
1. Inline critical CSS (above-the-fold styles)
2. Load theme.json styles (automatic with block themes)
3. Defer all JavaScript
4. Load fonts with font-display: swap
5. Preconnect to external domains

**Non-Critical:**
1. Lazy load images below fold
2. Defer analytics and marketing scripts
3. Load block-specific styles only on pages with those blocks
4. Use `wp_enqueue_script` with conditional checks

**Example Configuration:**

```php
// themes/smartvarme-theme/inc/enqueue.php

function smartvarme_optimize_assets() {
    // Defer all scripts by default
    add_filter('script_loader_tag', function($tag, $handle) {
        if (is_admin()) return $tag;
        if (str_contains($handle, 'jquery')) return $tag; // Don't defer jQuery
        return str_replace(' src', ' defer src', $tag);
    }, 10, 2);

    // Preconnect to external domains
    add_action('wp_head', function() {
        echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
        echo '<link rel="preconnect" href="https://cdn.smartvarme.no">';
    }, 1);

    // Lazy load images
    add_filter('wp_get_attachment_image_attributes', function($attr) {
        $attr['loading'] = 'lazy';
        return $attr;
    });
}
add_action('after_setup_theme', 'smartvarme_optimize_assets');
```

### Performance Targets

Based on 2026 standards:

| Metric | Target | Critical Threshold |
|--------|--------|-------------------|
| Largest Contentful Paint (LCP) | < 2.0s | < 2.5s |
| Interaction to Next Paint (INP) | < 200ms | < 500ms |
| Cumulative Layout Shift (CLS) | < 0.1 | < 0.25 |
| First Contentful Paint (FCP) | < 1.5s | < 3.0s |
| Time to First Byte (TTFB) | < 200ms | < 600ms |
| Total Page Size | < 1.5MB | < 3.0MB |
| Total Requests | < 50 | < 100 |

**Monitoring:** Use Chrome Lighthouse, PageSpeed Insights, GTmetrix weekly. Track Core Web Vitals in Google Search Console.

## Integration Points

### External Services

| Service | Integration Pattern | Notes |
|---------|---------------------|-------|
| **Payment Gateways** | WooCommerce payment gateway API | Use official plugins when available, custom integration via WC gateway class |
| **Shipping Providers** | WooCommerce shipping method API | REST API integration, cache rates, async label generation |
| **ERP System** | Custom REST API + Webhooks | Async product sync, inventory updates, order export |
| **Email Marketing** | WooCommerce hooks + API | Subscribe on checkout, order events trigger campaigns |
| **Analytics** | REST API + GTM | Server-side tracking for accuracy, client-side for UX events |
| **CDN/Cloud Storage** | Plugin (WP Offload Media) | Offload images to S3/CloudFlare, serve via CDN |

### Internal Boundaries

| Boundary | Communication | Notes |
|----------|---------------|-------|
| **Theme ↔ Custom Plugin** | Hooks/filters, template functions | Theme calls plugin functions, plugin provides filters for customization |
| **Custom Plugin ↔ WooCommerce** | WooCommerce hooks/filters, REST API | Extend via actions, modify via filters, never modify core |
| **Blocks ↔ Backend** | REST API endpoints | Block editor calls custom endpoints, uses WP REST API structure |
| **Frontend ↔ Cart** | WooCommerce AJAX, REST API | Use WC's built-in AJAX for cart updates, REST API for custom queries |
| **Admin ↔ Database** | WordPress database abstraction ($wpdb) | Always use $wpdb->prepare(), never raw queries |

### Communication Patterns

**Synchronous (Immediate):**
- User actions requiring instant feedback (add to cart, form submissions)
- Product page loads
- Checkout process

**Asynchronous (Background):**
- ERP product sync (scheduled WP Cron)
- Inventory updates (webhook-triggered)
- Email notifications (queued)
- Order exports (scheduled)
- Analytics data (batched)

**Event-Driven:**
- WooCommerce action hooks for order lifecycle
- Custom events for plugin communication
- Webhook endpoints for external integrations

## Sources

### Official Documentation (HIGH Confidence)
- [WooCommerce Block Development](https://developer.woocommerce.com/docs/block-development/)
- [WooCommerce Project Structure](https://developer.woocommerce.com/docs/getting-started/project-structure/)
- [WooCommerce Performance Optimization](https://developer.woocommerce.com/docs/best-practices/performance/performance-optimization/)
- [WooCommerce Extension Performance Best Practices](https://developer.woocommerce.com/docs/best-practices/performance/performance-best-practices/)

### Architecture & Development (MEDIUM-HIGH Confidence)
- [Flynt Component-Based WordPress](https://flyntwp.com/)
- [Component Architecture with WordPress and Gutenberg](https://www.exemplifi.io/insights/component-architecture-with-wordpress-and-gutenberg/)
- [WordPress Plugin Best Practices](https://developer.wordpress.org/plugins/plugin-basics/best-practices/)

### Performance & Optimization (MEDIUM Confidence)
- [WordPress Performance Optimization: The Ultimate 2026 Guide](https://next3offload.com/blog/wordpress-performance-optimization/)
- [WordPress in 2026: Leading Platform for Scalable Content Marketing](https://www.cheitgroup.com/blog/wordpress-2026-scalable-content-marketing)
- [WordPress Performance Optimization 2026: 7 Fixes for a Faster Site](https://www.ctaflow.com/blog/wordpress-performance-guide-2026/)

### Build Tools & Modern Development (MEDIUM Confidence)
- [WordPress with Vite (Build/HMR)](https://marcwieland.name/wordpress-with-vite-build-hmr/)
- [Web Development Tools: Automating Frontend Builds with Webpack and Vite 2026](https://johal.in/web-development-tools-automating-frontend-builds-with-webpack-and-vite-2026/)
- [Vite vs. Webpack: A Head-to-Head Comparison](https://kinsta.com/blog/vite-vs-webpack/)

### WooCommerce Trends & Architecture (MEDIUM Confidence)
- [WooCommerce Trends of 2026: The Future of E-commerce](https://zetamatic.com/blog/2025/12/woocommerce-trends-of-2026/)
- [Headless WordPress: The Future of WooCommerce Development?](https://convesio.com/guides/headless-wordpress-the-future-of-woocommerce-development/)
- [How to optimize performance for WooCommerce stores](https://developer.woocommerce.com/docs/best-practices/performance/performance-optimization/)

### Block Themes & FSE (MEDIUM Confidence)
- [WordPress Block Themes vs Classic Themes: 4 Key Differences](https://www.wpzoom.com/blog/block-themes-vs-classic-themes/)
- [WordPress FSE Block Themes vs Classic Themes](https://nexterwp.com/blog/wordpress-fse-block-themes-vs-classic-themes/)
- [Comparison: Block Themes Vs Classic Themes For WordPress 2026](https://themehunk.com/block-themes-vs-classic-themes-for-wordpress/)

### Plugin Architecture & Best Practices (MEDIUM Confidence)
- [WordPress Architecture: Tips, Plugins & Best Practices 2026](https://www.bluehost.com/blog/wordpress-architecture/)
- [Theme vs Plugin in WordPress: Key Differences Explained](https://www.lexo.ch/blog/2025/03/theme-vs-plugin-in-wordpress-the-real-difference-and-when-you-need-each-with-code-examples/)
- [Latest Trends in WordPress Development for 2026](https://wpdeveloper.com/latest-trends-in-wordpress-development/)

### Deployment & Migration (MEDIUM-LOW Confidence)
- [Incremental migration from WordPress for a dev-first approach](https://vercel.com/blog/incremental-migration-from-wordpress-for-a-dev-first-approach)
- [WordPress Continuous Integration for Seamless Development](https://seahawkmedia.com/wordpress/continuous-integration-and-development/)
- [Streamline WordPress Deployment Using WP-CLI and SSH – 2026](https://www.wewp.io/wp-cli-ssh-wordpress-deployments-2026/)

---
*Architecture research for: Smartvarme WordPress/WooCommerce rebuild*
*Researched: 2026-02-11*
