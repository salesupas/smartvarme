# Phase 2: Content System & Migration - Research

**Researched:** 2026-02-11
**Domain:** WordPress Gutenberg block development, content migration, and structured data
**Confidence:** HIGH

## Summary

Phase 2 focuses on implementing a modern Gutenberg-based content system and migrating existing FAQ/blog content from the old site. The research reveals a mature WordPress block ecosystem with native accordion blocks (WordPress 6.9+), comprehensive block pattern support, and robust migration tooling via WP-CLI.

The critical path involves: (1) Setting up custom block development using @wordpress/create-block with @wordpress/scripts build system, (2) Creating block patterns for common layouts and synced patterns for repeating elements, (3) Implementing FAQ schema markup via SEO plugin (Rank Math or SEOPress), (4) Configuring theme.json for design token management, and (5) Migrating content using WP-CLI's serialization-aware search-replace.

**Primary recommendation:** Use native WordPress 6.9+ Accordion blocks for FAQ sections, develop 5-10 custom block patterns (not custom blocks initially) for rapid content creation, implement FAQ schema via Rank Math/SEOPress, and migrate content using WP-CLI to preserve serialized data integrity.

## Standard Stack

### Core
| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| WordPress | 6.9.1+ (7.0 available April 2026) | CMS core | 6.9 adds native Accordion block; 6.9.1 is current stable (Feb 2026) |
| @wordpress/create-block | Latest (@latest) | Block scaffolding | Official WordPress tool, preconfigured build setup |
| @wordpress/scripts | Latest (bundled) | Build system | Official build tool, webpack + Babel + ESLint preconfigured |
| WP-CLI | 2.x | Content migration | Gold standard for serialization-aware database operations |
| Node.js | 20.10.0+ | Build environment | Required by @wordpress/create-block |
| npm | 10.2.3+ | Package management | Required by @wordpress/create-block |

### Supporting
| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| Rank Math | Latest free | FAQ schema markup | All-in-one SEO + schema (18+ schema types including FAQ) |
| SEOPress | Latest | FAQ schema markup | Alternative to Rank Math, strong GDPR/European privacy |
| Advanced Accordion Block | Latest (if needed) | Enhanced accordions | Only if native Accordion block insufficient for advanced FAQ UX |

### Alternatives Considered
| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| Native Accordion block | Third-party accordion plugins | Native is WordPress 6.9+ only; plugins offer more styling options but add bloat |
| Rank Math | Yoast SEO | Yoast has automatic schema graph, but Rank Math has 18 schema types vs limited free Yoast |
| Block patterns | Custom blocks | Patterns are faster to create, no code required; custom blocks needed for dynamic/interactive features |
| WP-CLI migration | WP Migrate DB Pro plugin | WP-CLI faster for large DBs, no timeout issues, better for 1GB+ databases |

**Installation:**
```bash
# Block development (from plugin directory)
npx @wordpress/create-block@latest my-custom-blocks
cd my-custom-blocks
npm start  # Development
npm run build  # Production

# WP-CLI (already in Docker container from Phase 1)
# No additional installation needed

# SEO plugin (via WP Admin or WP-CLI)
wp plugin install rank-math --activate
# OR
wp plugin install wp-seopress --activate
```

## Architecture Patterns

### Recommended Project Structure
```
wp-content/
├── themes/
│   └── smartvarme-theme/
│       ├── theme.json           # Design tokens, global styles
│       ├── patterns/            # Block patterns (hero, FAQ, CTA, etc.)
│       │   ├── hero-section.php
│       │   ├── faq-section.php
│       │   ├── cta-block.php
│       │   └── blog-card.php
│       └── parts/               # Template parts (header, footer)
└── plugins/
    └── smartvarme-core/         # From Phase 1
        ├── blocks/              # Custom blocks (if needed)
        │   ├── product-comparison/
        │   └── energy-calculator/
        └── includes/
            └── schema/          # Custom schema if not using plugin
```

### Pattern 1: Block Pattern Registration
**What:** Register reusable block layouts that appear in the pattern inserter
**When to use:** For common layouts (hero sections, FAQ sections, CTAs) that need consistent design but variable content
**Example:**
```php
// Source: https://developer.wordpress.org/block-editor/reference-guides/block-api/block-patterns/
// In theme functions.php or plugin
function smartvarme_register_block_patterns() {
    register_block_pattern(
        'smartvarme/faq-section',
        array(
            'title'       => __( 'FAQ Section', 'smartvarme' ),
            'description' => __( 'Accordion-based FAQ section with schema markup', 'smartvarme' ),
            'categories'  => array( 'text' ),
            'keywords'    => array( 'faq', 'accordion', 'questions' ),
            'content'     => '<!-- wp:group {"layout":{"type":"constrained"}} -->
                <div class="wp-block-group">
                    <!-- wp:heading {"level":2} -->
                    <h2>Ofte stilte spørsmål</h2>
                    <!-- /wp:heading -->

                    <!-- wp:core/accordion -->
                    <!-- wp:core/accordion-item -->
                    <details class="wp-block-accordion-item">
                        <!-- wp:core/accordion-heading -->
                        <summary class="wp-block-accordion-heading"><h3>Spørsmål 1</h3></summary>
                        <!-- /wp:core/accordion-heading -->

                        <!-- wp:core/accordion-panel -->
                        <div class="wp-block-accordion-panel">
                            <!-- wp:paragraph -->
                            <p>Svar tekst her...</p>
                            <!-- /wp:paragraph -->
                        </div>
                        <!-- /wp:core/accordion-panel -->
                    </details>
                    <!-- /wp:core/accordion-item -->
                    <!-- /wp:core/accordion -->
                </div>
                <!-- /wp:group -->',
        )
    );
}
add_action( 'init', 'smartvarme_register_block_patterns' );
```

### Pattern 2: Synced Pattern (Reusable Block) for Global CTAs
**What:** Content that updates globally when edited once
**When to use:** For contact CTAs, disclaimers, promotional banners that appear on multiple pages
**Example:**
```
// Created via UI: Block Editor → Patterns → Create Pattern → Enable "Sync"
// Once created, insert anywhere and edits propagate to all instances
// Use for: Contact forms, signup CTAs, legal disclaimers
```

### Pattern 3: Dynamic Block for Custom Features
**What:** Server-side rendered block with PHP callback
**When to use:** For product comparisons, calculators, or content requiring database queries
**Example:**
```php
// Source: https://developer.wordpress.org/block-editor/getting-started/fundamentals/static-dynamic-rendering/
// In plugin blocks/product-comparison/index.php
register_block_type( __DIR__ . '/build', array(
    'render_callback' => 'smartvarme_render_product_comparison',
) );

function smartvarme_render_product_comparison( $attributes, $content ) {
    // Server-side rendering - queries database, calculates data
    $products = get_posts( array( 'post_type' => 'product', 'posts_per_page' => 3 ) );

    ob_start();
    ?>
    <div class="product-comparison">
        <?php foreach ( $products as $product ) : ?>
            <div class="product-card">
                <h3><?php echo esc_html( $product->post_title ); ?></h3>
                <!-- Product data here -->
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
```

### Pattern 4: Theme.json Design Token Configuration
**What:** Single source of truth for colors, typography, spacing
**When to use:** Always - establishes design system constraints
**Example:**
```json
// Source: https://developer.wordpress.org/block-editor/how-to-guides/themes/global-settings-and-styles/
{
    "version": 3,
    "settings": {
        "color": {
            "custom": false,
            "defaultPalette": false,
            "palette": [
                {
                    "slug": "primary",
                    "color": "#your-brand-color",
                    "name": "Primary"
                },
                {
                    "slug": "secondary",
                    "color": "#your-secondary-color",
                    "name": "Secondary"
                }
            ]
        },
        "typography": {
            "customFontSize": false,
            "fontSizes": [
                {
                    "slug": "small",
                    "size": "0.875rem",
                    "name": "Small"
                },
                {
                    "slug": "medium",
                    "size": "1rem",
                    "name": "Medium"
                },
                {
                    "slug": "large",
                    "size": "1.5rem",
                    "name": "Large"
                }
            ]
        },
        "spacing": {
            "customSpacingSize": false,
            "spacingSizes": [
                {
                    "slug": "small",
                    "size": "1rem",
                    "name": "Small"
                },
                {
                    "slug": "medium",
                    "size": "2rem",
                    "name": "Medium"
                },
                {
                    "slug": "large",
                    "size": "4rem",
                    "name": "Large"
                }
            ]
        }
    }
}
```

### Pattern 5: WP-CLI Content Migration with Serialization Handling
**What:** Safe database URL replacement that preserves serialized data
**When to use:** When migrating content between environments or domains
**Example:**
```bash
# Source: https://developer.wordpress.org/cli/commands/search-replace/
# ALWAYS dry-run first to preview changes
wp search-replace 'https://old-smartvarme.com' 'http://localhost:8080' \
  --dry-run \
  --all-tables \
  --report-changed-only

# After verifying dry-run output, execute real replacement
wp search-replace 'https://old-smartvarme.com' 'http://localhost:8080' \
  --skip-columns=guid \
  --all-tables

# Export content to WXR for backup
wp export --dir=./migration-backup --start_date=2020-01-01

# Import content from WXR
wp import migration-backup/export.xml \
  --authors=create \
  --rewrite_urls
```

### Anti-Patterns to Avoid

- **Creating custom blocks for static layouts:** Use block patterns instead - faster to create, no build step, content editors can modify
- **Hand-coding FAQ schema:** Use Rank Math or SEOPress - automatic schema generation, validates with Google Rich Results Test
- **Using SQL REPLACE() for migrations:** Breaks serialized data - ALWAYS use WP-CLI search-replace
- **Setting `"custom": true` in theme.json color settings:** Opens color picker to all colors, breaks design consistency - use `"custom": false` with brand palette only
- **Trailing commas in theme.json:** Most common JSON syntax error - validate JSON before deploying
- **Not using `--dry-run` before search-replace:** Can corrupt database - ALWAYS preview changes first

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| FAQ schema markup | Custom JSON-LD generators | Rank Math or SEOPress | Auto-generates FAQPage schema, validates with Google, 18+ schema types in Rank Math free |
| Accordion UI for FAQs | Custom JavaScript accordions | WordPress 6.9+ native Accordion block | Built into core, accessible, theme-customizable via CSS, no external dependencies |
| Database URL replacement | Custom SQL scripts | WP-CLI search-replace | Handles serialized data (widget data, options, meta), prevents data corruption |
| Block build system | Custom webpack configs | @wordpress/scripts | Maintained by WordPress core team, includes Babel, ESLint, Prettier, Sass, auto-updated |
| Reusable content sections | Custom shortcodes | Synced Patterns (WordPress 6.3+) | Native Gutenberg feature, visual editor, updates propagate globally |
| Design token management | Hard-coded CSS variables | theme.json | Single source of truth, generates CSS variables automatically, controls editor UI |
| Content export/import | Custom XML parsers | WP-CLI export/import (WXR format) | Standard WordPress format, handles authors, terms, posts, comments, attachments |

**Key insight:** WordPress 6.9+ (current) and 7.0 (April 2026) have native solutions for most content management needs. Custom solutions add maintenance burden and miss WordPress core updates. The ecosystem matured significantly in 2025-2026 with native accordions, improved pattern system, and theme.json v3.

## Common Pitfalls

### Pitfall 1: Breaking Serialized Data During Migration
**What goes wrong:** Using SQL `REPLACE()` to change URLs corrupts serialized data, causing widgets to disappear, plugin settings to reset, and meta data to become inaccessible.
**Why it happens:** Serialized PHP data stores string lengths (e.g., `s:23:"https://oldsite.com"`). Changing the URL changes the length, but `REPLACE()` doesn't update the length counter, causing PHP unserialization to fail.
**How to avoid:** ALWAYS use WP-CLI `wp search-replace` for database operations. It deserializes data before modification and reserializes afterward.
**Warning signs:** After migration, widgets missing from sidebar, theme customizer settings gone, plugin options reset to defaults, page builder content blank.

### Pitfall 2: Creating Custom Blocks When Block Patterns Suffice
**What goes wrong:** Developers build custom blocks with JavaScript for static layouts (hero sections, FAQ layouts, blog cards), adding build complexity and maintenance overhead.
**Why it happens:** Misunderstanding the difference between patterns (static layout templates) and blocks (interactive components). Patterns can achieve 80% of use cases without code.
**How to avoid:** Use this decision tree: (1) Is content static or dynamic? Static = pattern. (2) Does it query database or update in real-time? No = pattern. (3) Is it interactive (calculator, filter)? No = pattern. Only create custom blocks for truly dynamic/interactive features.
**Warning signs:** Build step required for simple layout changes, content editors can't modify layouts, long development time for simple sections.

### Pitfall 3: Inconsistent Design Tokens Between Editor and Frontend
**What goes wrong:** Colors and spacing available in the block editor don't match the frontend design system, leading to editor choices that break on the live site.
**Why it happens:** Not using theme.json as single source of truth, or leaving `"custom": true` which allows arbitrary values.
**How to avoid:** Set all theme.json settings to `"custom": false` and `"defaultPalette": false`, register ONLY brand colors/fonts/spacing. Use CSS variables generated by theme.json (`--wp--preset--color--primary`) in frontend styles.
**Warning signs:** Content editors complain frontend looks different than editor preview, design inconsistencies across pages, colors not in brand palette appearing on site.

### Pitfall 4: Trailing Commas in theme.json
**What goes wrong:** WordPress fails to parse theme.json, resulting in no styles loading, broken block editor UI, or white screen.
**Why it happens:** JSON doesn't allow trailing commas (unlike JavaScript). Easy to introduce when copy-pasting or editing arrays/objects.
**How to avoid:** Validate theme.json with a JSON linter before deploying. Use an IDE with JSON validation (VS Code has built-in validation).
**Warning signs:** Block editor loads with no styles, theme.json changes don't apply, white screen in editor or frontend.

### Pitfall 5: Not Using `--dry-run` Before WP-CLI search-replace
**What goes wrong:** Incorrect search-replace pattern corrupts production database, replacing unintended strings (e.g., replacing "var" replaces "smartvarme" to "smartheatme").
**Why it happens:** Skipping the preview step to save time, not understanding regex implications, or using overly broad search terms.
**How to avoid:** ALWAYS run with `--dry-run` first. Review the output line-by-line. Use precise search strings (full URLs, not fragments). Use `--report-changed-only` to reduce noise.
**Warning signs:** Post content contains unexpected text changes, broken internal links, corrupted JSON data in custom fields.

### Pitfall 6: Using Dynamic Blocks for Static Content
**What goes wrong:** Pages load slowly because PHP render callbacks execute on every page load for every block instance, even when content doesn't change.
**Why it happens:** Developers default to dynamic blocks because they're familiar with PHP, not understanding the performance implications.
**How to avoid:** Use static blocks by default. Only use dynamic blocks when content changes based on time, user, or database queries. Quote: "Blocks meant to live on a site not directly maintained by a developer should use static rendering by default."
**Warning signs:** Slow page load times with many block instances, high server CPU usage, pages with 10+ dynamic blocks taking seconds to render.

### Pitfall 7: Ignoring Norwegian Language Translation Workflow
**What goes wrong:** Custom blocks display English strings in Norwegian site, breaking user experience.
**Why it happens:** Not implementing WordPress internationalization (`__()`, `_e()`) functions in custom blocks, or not generating translation files.
**How to avoid:** Use `wp.i18n.__()` in JavaScript blocks and `__()` in PHP. Set text domain in block registration. Generate `.pot` files with `wp i18n make-pot`. WordPress.org auto-parses plugins for translation strings.
**Warning signs:** English text in block editor on Norwegian site, block labels not translated, custom blocks don't match core block language.

## Code Examples

Verified patterns from official sources:

### FAQ Schema Markup (Rank Math Method)
```php
// Source: https://www.seopress.org/newsroom/featured-stories/faq-schema-wordpress/
// Rank Math auto-generates FAQ schema from accordion blocks
// No code required - configure via Rank Math > Schema > FAQ

// Manual method if not using plugin:
function smartvarme_faq_schema() {
    if ( is_singular( 'faq' ) ) {
        $schema = array(
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => array()
        );

        // Populate from ACF fields or custom fields
        $faqs = get_field( 'faq_items' ); // Example with ACF
        foreach ( $faqs as $faq ) {
            $schema['mainEntity'][] = array(
                '@type'          => 'Question',
                'name'           => $faq['question'],
                'acceptedAnswer' => array(
                    '@type' => 'Answer',
                    'text'  => $faq['answer']
                )
            );
        }

        echo '<script type="application/ld+json">' . json_encode( $schema ) . '</script>';
    }
}
add_action( 'wp_head', 'smartvarme_faq_schema' );
```

### Block Pattern Registration with Norwegian Translation
```php
// Source: https://developer.wordpress.org/block-editor/reference-guides/block-api/block-patterns/
function smartvarme_register_patterns() {
    // CTA Pattern
    register_block_pattern(
        'smartvarme/contact-cta',
        array(
            'title'       => __( 'Contact CTA', 'smartvarme' ),
            'description' => __( 'Call-to-action block for contact inquiries', 'smartvarme' ),
            'categories'  => array( 'call-to-action' ),
            'keywords'    => array( 'contact', 'cta', 'kontakt' ),
            'content'     => '<!-- wp:group {"backgroundColor":"primary","textColor":"white","layout":{"type":"constrained"}} -->
                <div class="wp-block-group has-primary-background-color has-white-color has-text-color has-background">
                    <!-- wp:heading {"level":2} -->
                    <h2>' . __( 'Trenger du hjelp?', 'smartvarme' ) . '</h2>
                    <!-- /wp:heading -->

                    <!-- wp:paragraph -->
                    <p>' . __( 'Våre eksperter er klare til å hjelpe deg med å velge riktig varmepumpe.', 'smartvarme' ) . '</p>
                    <!-- /wp:paragraph -->

                    <!-- wp:buttons -->
                    <div class="wp-block-buttons">
                        <!-- wp:button {"className":"is-style-outline"} -->
                        <div class="wp-block-button is-style-outline">
                            <a class="wp-block-button__link">' . __( 'Kontakt oss', 'smartvarme' ) . '</a>
                        </div>
                        <!-- /wp:button -->
                    </div>
                    <!-- /wp:buttons -->
                </div>
                <!-- /wp:group -->',
        )
    );
}
add_action( 'init', 'smartvarme_register_patterns' );
```

### WP-CLI Content Migration Script
```bash
#!/bin/bash
# Source: https://developer.wordpress.org/cli/commands/search-replace/
# migrate-content.sh - Safe content migration with serialization handling

set -e  # Exit on error

OLD_URL="https://smartvarme.no"
NEW_URL="http://localhost:8080"
BACKUP_DIR="./migration-backup-$(date +%Y%m%d-%H%M%S)"

echo "=== WordPress Content Migration ==="
echo "From: $OLD_URL"
echo "To: $NEW_URL"
echo ""

# Step 1: Export current content as backup
echo "Step 1: Creating WXR backup..."
mkdir -p "$BACKUP_DIR"
wp export --dir="$BACKUP_DIR" --start_date=2020-01-01
echo "✓ Backup created in $BACKUP_DIR"
echo ""

# Step 2: Database backup
echo "Step 2: Creating database backup..."
wp db export "$BACKUP_DIR/database-backup.sql"
echo "✓ Database backed up"
echo ""

# Step 3: Dry-run search-replace
echo "Step 3: Running dry-run search-replace..."
wp search-replace "$OLD_URL" "$NEW_URL" \
  --dry-run \
  --all-tables \
  --report-changed-only \
  --verbose
echo ""

# Step 4: Confirm before proceeding
read -p "Does the dry-run output look correct? (y/N) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Migration cancelled."
    exit 1
fi

# Step 5: Execute search-replace
echo "Step 5: Executing search-replace..."
wp search-replace "$OLD_URL" "$NEW_URL" \
  --skip-columns=guid \
  --all-tables
echo "✓ URLs replaced"
echo ""

# Step 6: Flush cache
echo "Step 6: Flushing cache..."
wp cache flush
wp rewrite flush
echo "✓ Cache flushed"
echo ""

echo "=== Migration Complete ==="
echo "Backup location: $BACKUP_DIR"
echo "Test the site thoroughly before deploying to production."
```

### Custom Block Development with @wordpress/scripts
```bash
# Source: https://developer.wordpress.org/block-editor/getting-started/devenv/get-started-with-create-block/
# Creating a custom product comparison block

# Navigate to plugins directory
cd wp-content/plugins

# Scaffold new block
npx @wordpress/create-block@latest product-comparison \
  --namespace="smartvarme" \
  --title="Product Comparison" \
  --description="Compare heating products side-by-side"

cd product-comparison

# Development mode (watches for changes)
npm start

# Production build (minified, optimized)
npm run build

# Linting
npm run lint:js
npm run lint:css

# Format code
npm run format
```

### Dynamic Block with Server-Side Rendering
```php
// Source: https://developer.wordpress.org/block-editor/getting-started/fundamentals/static-dynamic-rendering/
// blocks/energy-calculator/index.php

function smartvarme_register_energy_calculator() {
    register_block_type( __DIR__ . '/build', array(
        'render_callback' => 'smartvarme_render_energy_calculator',
        'attributes'      => array(
            'squareMeters' => array(
                'type'    => 'number',
                'default' => 100,
            ),
            'insulation' => array(
                'type'    => 'string',
                'default' => 'medium',
            ),
        ),
    ) );
}
add_action( 'init', 'smartvarme_register_energy_calculator' );

function smartvarme_render_energy_calculator( $attributes ) {
    $square_meters = absint( $attributes['squareMeters'] );
    $insulation    = sanitize_text_field( $attributes['insulation'] );

    // Server-side calculation
    $energy_need = smartvarme_calculate_energy_need( $square_meters, $insulation );
    $recommended_kw = smartvarme_calculate_kw_need( $energy_need );

    ob_start();
    ?>
    <div class="energy-calculator">
        <div class="calculator-result">
            <h3><?php _e( 'Anbefalte produkter', 'smartvarme' ); ?></h3>
            <p><?php printf(
                __( 'For %d kvm med %s isolasjon anbefaler vi %d kW varmepumpe.', 'smartvarme' ),
                $square_meters,
                $insulation,
                $recommended_kw
            ); ?></p>
        </div>
        <?php
        // Query recommended products based on kW
        $products = smartvarme_get_recommended_products( $recommended_kw );
        if ( $products ) : ?>
            <div class="recommended-products">
                <?php foreach ( $products as $product ) : ?>
                    <div class="product-card">
                        <h4><?php echo esc_html( $product->post_title ); ?></h4>
                        <!-- Product details -->
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
```

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| Reusable Blocks (terminology) | Synced Patterns | WordPress 6.3 (August 2023) | Naming change only - functionality identical |
| Details block for accordions | Native Accordion block (4-block structure) | WordPress 6.9 (December 2025) | Core feature, no plugin needed, theme-customizable |
| theme.json version 2 | theme.json version 3 | WordPress 6.6 (July 2024) | Improved design token system, better CSS variable generation |
| Manual schema markup | SEO plugin auto-generation | Ongoing (2024-2026) | Rank Math/SEOPress generate schema from blocks automatically |
| Always-iframed editor (opt-in) | Always-iframed editor (mandatory) | WordPress 7.0 (April 2026) | All blocks render in iframe regardless of API version |
| WXR 1.2 format | WXR 1.2 format (stable) | No change | Standard format since WordPress 3.0, still current |
| Node.js 18.x requirement | Node.js 20.10.0+ requirement | @wordpress/create-block 2024 | Aligns with Node.js LTS schedule |

**Deprecated/outdated:**
- **"Reusable Blocks" terminology**: Now called "Synced Patterns" - UI and documentation updated in WordPress 6.3+
- **Third-party accordion plugins for basic FAQs**: WordPress 6.9 native Accordion block sufficient for most use cases
- **Manual theme.json version 1**: Version 3 is current as of WordPress 6.6; version 2 still supported but use version 3 for new themes
- **Block API version 2 to avoid iframe**: WordPress 7.0 makes iframe mandatory for all blocks regardless of API version

## Open Questions

### 1. FAQ Content Structure in Old Database
**What we know:** Requirements specify migrating from `smartvarme_wp_zmmon.sql`, but FAQ content structure unknown.
**What's unclear:** Are FAQs stored as custom post type, regular posts, ACF fields, or page builder content? This affects migration strategy.
**Recommendation:** Inspect `smartvarme_wp_zmmon.sql` to identify FAQ storage method. If page builder content (Elementor, Divi), may need to rebuild manually as block patterns since page builder serialized data won't convert to Gutenberg blocks automatically.

### 2. Existing Plugin Dependencies for FAQ/Blog
**What we know:** Phase 1 mentions 25+ plugins need consolidation, but specific FAQ/blog plugins unknown.
**What's unclear:** Which plugins provide current FAQ functionality? Are they compatible with Gutenberg or classic editor only?
**Recommendation:** Run `wp plugin list` on old site to identify FAQ-related plugins. If using classic editor plugins (shortcodes, custom meta boxes), migration requires converting to Gutenberg blocks/patterns.

### 3. Design Token Values for theme.json
**What we know:** Need to implement locked design tokens for colors, typography, spacing.
**What's unclear:** What are the actual brand colors, font families, and spacing scale for Smartvarme?
**Recommendation:** Extract from current site CSS or request from designer. Without these values, theme.json can't be completed. Default to conservative scale (8px baseline grid, 1.5 typographic scale) if brand guidelines unavailable.

### 4. Norwegian Translation Completeness
**What we know:** WordPress core and WooCommerce have nb_NO translations via GlotPress.
**What's unclear:** Are Rank Math, SEOPress, and other SEO plugins fully translated to Norwegian?
**Recommendation:** Verify plugin translations at translate.wordpress.org before selecting. SEOPress has European focus (GDPR) which may include better Norwegian support. Fall back to English UI strings if translations incomplete, or contribute translations to WordPress.org.

### 5. URL Structure for Migrated Content
**What we know:** Requirements specify preserving original URLs (MIG-02).
**What's unclear:** What are the current FAQ and blog URL structures? (e.g., `/faq/question-slug/` vs `/sporsmal/slug/` vs `/category/faq/slug/`)
**Recommendation:** Export URL list from old site with `wp post list --post_type=post,page --format=csv --fields=ID,post_type,post_name,guid`. Compare against new site after migration. Create 301 redirects for any mismatches using Redirection plugin or `.htaccess`.

## Sources

### Primary (HIGH confidence)
- [WordPress Block Editor Handbook - Block Patterns API](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-patterns/) - Pattern registration, properties, current API
- [WordPress Block Editor Handbook - Global Settings & Styles (theme.json)](https://developer.wordpress.org/block-editor/how-to-guides/themes/global-settings-and-styles/) - Design token configuration, version 3 API
- [WP-CLI Commands - search-replace](https://developer.wordpress.org/cli/commands/search-replace/) - Serialization handling, migration best practices
- [WP-CLI Commands - export](https://developer.wordpress.org/cli/commands/export/) - WXR export format, content backup
- [WP-CLI Commands - import](https://developer.wordpress.org/cli/commands/import/) - WXR import, author mapping
- [@wordpress/create-block - Block Editor Handbook](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-create-block/) - Block scaffolding tool, current requirements
- [@wordpress/scripts - Block Editor Handbook](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/) - Build system, development workflow
- [Static or Dynamic Rendering of a Block - Block Editor Handbook](https://developer.wordpress.org/block-editor/getting-started/fundamentals/static-dynamic-rendering/) - Block rendering approaches
- [WordPress Developer Blog - Styling Accordions in WordPress 6.9](https://developer.wordpress.org/news/2025/10/styling-accordions-in-wordpress-6-9/) - Native accordion block structure, release date

### Secondary (MEDIUM confidence)
- [WordPress VIP - Configuring Design Systems: Theme.json](https://wpvip.com/blog/using-a-design-system-with-the-wordpress-block-editor-pt-1-theme-json/) - Design token best practices, verified against official docs
- [10up - WP Block Editor Best Practices - Custom Blocks](https://gutenberg.10up.com/reference/Blocks/custom-blocks/) - Static vs dynamic blocks, industry best practices
- [Rank Math vs Yoast 2026 Comparison](https://onlinemediamasters.com/rank-math-vs-yoast/) - Schema plugin capabilities, verified with official plugin pages
- [SEOPress - FAQ Schema WordPress](https://www.seopress.org/newsroom/featured-stories/faq-schema-wordpress/) - Schema implementation patterns
- [WordPress.org Documentation - Synced Patterns (Reusable Blocks)](https://wordpress.org/documentation/article/reusable-blocks/) - Official user documentation, updated January 2026
- [WordPress.org Documentation - Comparing Patterns, Template Parts, and Synced Patterns](https://wordpress.org/documentation/article/comparing-patterns-template-parts-and-reusable-blocks/) - When to use each approach
- [WordPress News - WordPress 6.9.1 Maintenance Release](https://wordpress.org/news/2026/02/wordpress-6-9-1-maintenance-release/) - Current stable version
- [WordPress Developer News - What's New for Developers (February 2026)](https://developer.wordpress.org/news/2026/02/whats-new-for-developers-february-2026/) - WordPress 7.0 beta features

### Tertiary (LOW confidence - requires validation)
- [Bluehost - WordPress Block Patterns 2026 Guide](https://www.bluehost.com/blog/wordpress-block-patterns/) - Tutorial content, use for concepts only
- [Kinsta - Building Gutenberg Blocks Tutorial](https://kinsta.com/blog/gutenberg-blocks/) - Third-party tutorial, verify code examples against official docs
- [Managing WP - Search and Replace on Serialized Data](https://managingwp.io/2023/03/23/search-and-replace-on-a-wordpress-database-and-dealing-with-serialized-data/) - 2023 content, principles still valid but verify commands
- [Duplicator - WordPress Search & Replace Plugins](https://duplicator.com/wordpress-search-replace-plugin/) - Plugin comparison, verify capabilities with official plugin pages

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH - Official WordPress tools (@wordpress/create-block, WP-CLI) with verified documentation and release dates
- Architecture: HIGH - All patterns sourced from official WordPress Block Editor Handbook and verified with current WordPress 6.9.1 behavior
- Pitfalls: HIGH - Serialization issues well-documented across official and community sources; block pattern misconceptions verified via 10up best practices
- Schema markup: MEDIUM - Plugin capabilities verified but specific Norwegian translation completeness unconfirmed
- Migration specifics: MEDIUM - General migration patterns HIGH confidence, but old site database structure unknown until inspected

**Research date:** 2026-02-11
**Valid until:** 2026-03-31 (WordPress 7.0 releases April 9, 2026 - may introduce new block features)

**WordPress version context:**
- Current stable: WordPress 6.9.1 (released February 3, 2026)
- Next major: WordPress 7.0 (scheduled April 9, 2026)
- Notable 6.9 features used: Native Accordion block, Synced Patterns terminology
- Notable 7.0 features: Always-iframed editor, viewport-based block visibility (may affect Phase 2 planning if delayed to post-April)
