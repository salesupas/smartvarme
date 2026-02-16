# Phase 5: Design & User Experience - Research

**Researched:** 2026-02-12
**Domain:** WordPress block theme design systems, responsive design, contact forms, search functionality
**Confidence:** HIGH

## Summary

Phase 5 implements a modern visual design system on top of the functional site structure completed in Phases 1-4. The research reveals that WordPress 6.9+ with theme.json v3 provides comprehensive design token management, fluid typography, and spacing systems that eliminate most custom CSS needs. The focus is on enhancing the existing minimal theme.json (gold button #f7a720, 1400px wide layout) into a complete design system with consistent typography, strategic spacing, responsive layouts, integrated contact forms, and performant search.

The critical success factors are: (1) leveraging theme.json's fluid typography and spacing scales to achieve responsive design without media query complexity, (2) using CSS clamp() for viewport-based scaling, (3) implementing WPForms or Contact Form 7 for product page inquiry forms with WooCommerce integration, (4) integrating FiboSearch for fast WooCommerce-aware search, and (5) ensuring design choices support Core Web Vitals targets (LCP < 2.5s, INP < 200ms, CLS < 0.1).

**Primary recommendation:** Enhance existing theme.json with fluid typography (settings.typography.fluid: true), implement 8px-based spacing scale using spacingScale configuration, add custom CSS only for responsive breakpoints (320px, 768px, 1440px) that theme.json cannot handle, use WPForms for WooCommerce product inquiry forms, and install FiboSearch free version for AJAX search with upgrade path to Pro for 10× performance.

## Standard Stack

### Core
| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| WordPress | 6.9.1+ | CMS platform | Current stable (Feb 2026), native Accordion blocks, fluid typography, theme.json v3 |
| theme.json | v3 | Design system configuration | Official FSE design token system, generates CSS variables and utility classes |
| CSS clamp() | Native CSS | Fluid typography/spacing | Modern CSS function for viewport-based scaling, WordPress 6.1+ generates automatically |
| WPForms Lite | Latest free | Contact forms | 5M+ installs, WooCommerce integration, drag-and-drop builder, Norwegian translation |
| FiboSearch | 1.32.2+ free | WooCommerce search | 3.7M+ downloads, AJAX live search, mobile-first, Norwegian support (multilingual WPML/Polylang) |

### Supporting
| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| Contact Form 7 | 6.1.5 | Contact forms (alternative) | If lightweight/free is priority over ease-of-use; has Norwegian translation |
| WPForms Pro | Paid | Advanced forms | If need Stripe payments, advanced WooCommerce integration, or conditional logic |
| FiboSearch Pro | Paid | Search performance | If catalog > 1000 products; 10× faster search with inverted index, fuzzy search |
| ShortPixel/Imagify | Latest | Image optimization | For WebP/AVIF conversion to improve LCP (Core Web Vitals) |
| Query Monitor | Latest | Performance monitoring | For CLS/LCP debugging during design implementation |

### Alternatives Considered
| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| WPForms | Contact Form 7 | CF7 is free and lightweight but text-based UI vs visual builder; less WooCommerce integration |
| FiboSearch | SearchWP | SearchWP has more customization but FiboSearch is WooCommerce-native and faster for products |
| theme.json fluid typography | Custom media queries | Media queries offer pixel-perfect control but require more CSS, less maintainable |
| WPForms | Formidable Forms | Formidable more powerful for advanced forms, but WPForms better WooCommerce integration |

**Installation:**
```bash
# Contact form plugin (choose one)
wp plugin install wpforms-lite --activate
# OR
wp plugin install contact-form-7 --activate

# Search plugin
wp plugin install ajax-search-for-woocommerce --activate  # FiboSearch

# Image optimization (optional, for Core Web Vitals)
wp plugin install shortpixel-image-optimiser --activate
# OR
wp plugin install imagify --activate

# Performance monitoring
wp plugin install query-monitor --activate
```

## Architecture Patterns

### Recommended Project Structure
```
wp-content/themes/smartvarme-theme/
├── theme.json               # Enhanced with fluid typography, spacing scale
├── style.css                # Minimal custom CSS for breakpoint overrides
├── assets/
│   └── css/
│       └── responsive.css   # Media queries theme.json can't handle
├── templates/               # FSE templates (from Phase 1-4)
│   ├── index.html
│   ├── single.html
│   ├── page.html
│   └── ...
├── parts/                   # Template parts
│   ├── header.html
│   └── footer.html
└── patterns/                # Block patterns (from Phase 2)
    ├── hero-section.php
    ├── faq-section.php
    └── ...
```

### Pattern 1: Fluid Typography Configuration (theme.json v3)
**What:** Viewport-based font scaling using CSS clamp() generated by WordPress
**When to use:** For all text elements to achieve responsive typography without media queries
**Example:**
```json
{
  "$schema": "https://schemas.wp.org/trunk/theme.json",
  "version": 3,
  "settings": {
    "typography": {
      "fluid": true,
      "customFontSize": false,
      "fontSizes": [
        {
          "slug": "small",
          "size": "0.875rem",
          "name": "Small",
          "fluid": false
        },
        {
          "slug": "medium",
          "size": "1rem",
          "name": "Medium",
          "fluid": {
            "min": "0.875rem",
            "max": "1rem"
          }
        },
        {
          "slug": "large",
          "size": "1.25rem",
          "name": "Large",
          "fluid": {
            "min": "1.125rem",
            "max": "1.5rem"
          }
        },
        {
          "slug": "x-large",
          "size": "1.75rem",
          "name": "Extra Large",
          "fluid": {
            "min": "1.5rem",
            "max": "2.25rem"
          }
        },
        {
          "slug": "xx-large",
          "size": "2.5rem",
          "name": "Heading",
          "fluid": {
            "min": "2rem",
            "max": "3rem"
          }
        }
      ]
    }
  }
}
```

**How it works:** WordPress converts `"fluid": true` into CSS like:
```css
--wp--preset--font-size--large: clamp(1.125rem, 1.125rem + ((1vw - 0.48rem) * 0.722), 1.5rem);
```

**Source:** [WordPress Typography Settings](https://developer.wordpress.org/themes/global-settings-and-styles/settings/typography/)

### Pattern 2: 8px Spacing Scale Configuration
**What:** Consistent spacing system using algorithmic scale generation
**When to use:** For all spacing (margin, padding, gap) to maintain visual rhythm
**Example:**
```json
{
  "version": 3,
  "settings": {
    "spacing": {
      "customSpacingSize": false,
      "padding": true,
      "margin": true,
      "blockGap": true,
      "units": ["px", "em", "rem", "%"],
      "spacingScale": {
        "operator": "*",
        "increment": 1.5,
        "steps": 7,
        "mediumStep": 1.5,
        "unit": "rem"
      }
    }
  }
}
```

**Generated scale:**
- `--wp--preset--spacing--20`: 0.44rem (≈7px)
- `--wp--preset--spacing--30`: 0.67rem (≈11px)
- `--wp--preset--spacing--40`: 1rem (16px)
- `--wp--preset--spacing--50`: 1.5rem (24px) ← mediumStep
- `--wp--preset--spacing--60`: 2.25rem (36px)
- `--wp--preset--spacing--70`: 3.38rem (54px)
- `--wp--preset--spacing--80`: 5.06rem (81px)

**Alternative - Manual Spacing Sizes:**
```json
{
  "settings": {
    "spacing": {
      "spacingSizes": [
        { "slug": "xs", "size": "0.5rem", "name": "XS (8px)" },
        { "slug": "small", "size": "1rem", "name": "Small (16px)" },
        { "slug": "medium", "size": "1.5rem", "name": "Medium (24px)" },
        { "slug": "large", "size": "2rem", "name": "Large (32px)" },
        { "slug": "xl", "size": "3rem", "name": "XL (48px)" },
        { "slug": "xxl", "size": "4rem", "name": "XXL (64px)" }
      ]
    }
  }
}
```

**Source:** [WordPress Spacing Settings](https://developer.wordpress.org/themes/global-settings-and-styles/settings/spacing/)

### Pattern 3: Responsive Breakpoints with Custom CSS
**What:** Media queries for layouts theme.json cannot handle
**When to use:** Navigation hamburger menus, grid column changes, layout shifts
**Example:**
```css
/* style.css or assets/css/responsive.css */

/* Mobile-first base styles (320px+) */
.wp-block-columns {
  flex-direction: column;
}

/* Tablet (768px+) */
@media (min-width: 768px) {
  .wp-block-columns {
    flex-direction: row;
  }

  .wp-site-blocks {
    padding-left: 2rem;
    padding-right: 2rem;
  }
}

/* Desktop (1440px+) */
@media (min-width: 1440px) {
  .wp-site-blocks {
    padding-left: 4rem;
    padding-right: 4rem;
  }

  /* Navigation block hamburger override */
  .wp-block-navigation__responsive-container-open {
    display: none;
  }
}

/* Avoid CSS for responsive text/spacing - use theme.json fluid instead */
```

**Sources:**
- [Responsive Breakpoints in WordPress](https://codecanel.com/responsive-breakpoints-in-wordpress/)
- [Using Additional CSS in Block Themes](https://alwaysopen.design/additional-css-wordpress-blocks/)

### Pattern 4: WPForms Product Inquiry Form on WooCommerce Pages
**What:** Contact form embedded on product pages with product context
**When to use:** For "Request Quote", "Ask Question", or "Custom Order" on product pages
**Example:**
```php
// In woocommerce/single-product/meta.php override or via hook
add_action( 'woocommerce_product_meta_end', 'smartvarme_product_inquiry_form' );

function smartvarme_product_inquiry_form() {
    global $product;

    // Display WPForms shortcode with product ID passed
    echo '<div class="product-inquiry-section">';
    echo '<h3>' . __( 'Har du spørsmål om dette produktet?', 'smartvarme' ) . '</h3>';
    echo do_shortcode( '[wpforms id="123" product_id="' . $product->get_id() . '"]' );
    echo '</div>';
}
```

**WPForms configuration:**
1. Create form in WP Admin > WPForms
2. Add "Single Line Text" field (hidden) for product_id
3. Add "Paragraph Text" field for inquiry message
4. Add "Email" field for customer contact
5. Enable WooCommerce integration: Settings > Integrations > WooCommerce

**Alternative - Contact Form 7:**
```php
// Contact Form 7 with WooCommerce integration
add_action( 'woocommerce_product_meta_end', 'smartvarme_cf7_inquiry_form' );

function smartvarme_cf7_inquiry_form() {
    global $product;

    echo do_shortcode( '[contact-form-7 id="456" html_class="product-inquiry-form"]' );

    // Pass product data via JavaScript
    ?>
    <script>
        document.addEventListener( 'wpcf7submit', function( event ) {
            if ( '456' == event.detail.contactFormId ) {
                // Product ID: <?php echo $product->get_id(); ?>
                // Track inquiry submission
            }
        }, false );
    </script>
    <?php
}
```

**Sources:**
- [WPForms WooCommerce Integration](https://integrately.com/integrations/woocommerce/wpforms)
- [WooCommerce Product Enquiry Form](https://woocommerce.com/document/product-enquiry-form-for-woocommerce/)

### Pattern 5: FiboSearch Implementation
**What:** AJAX live search replacing WordPress default search
**When to use:** All search instances (header, widget, shortcode)
**Example:**
```php
// Replace theme search with FiboSearch in header.html template part
<!-- wp:search {"label":"Søk","buttonText":"Søk"} /-->

// After FiboSearch activation, automatic replacement happens
// Or use explicit shortcode:
<!-- wp:shortcode -->
[fibosearch]
<!-- /wp:shortcode -->

// PHP function in functions.php
function smartvarme_header_search() {
    echo do_shortcode( '[fibosearch]' );
}
```

**FiboSearch configuration:**
1. Install plugin: `wp plugin install ajax-search-for-woocommerce --activate`
2. Configure: Settings > FiboSearch
   - Enable "Search in SKU"
   - Enable "Search in short description"
   - Enable "Show product images"
   - Enable "Show price"
   - Set autocomplete: "Show on type"
   - Mobile layout: "Mobile optimized"
3. Styling: Appearance > FiboSearch > Colors (match gold #f7a720)
4. Multilingual: Enable Norwegian (nb_NO) if using WPML/Polylang

**Performance upgrade path:**
- Free version: Standard WooCommerce search (suitable for < 1000 products)
- Pro version: Inverted index search (10× faster, for > 1000 products, fuzzy search)

**Source:** [FiboSearch Plugin Page](https://wordpress.org/plugins/ajax-search-for-woocommerce/)

### Anti-Patterns to Avoid

- **Writing media queries for typography/spacing when theme.json fluid can handle it:** Bloats CSS, harder to maintain, misses editor preview integration
- **Using fixed px font sizes:** Breaks accessibility (user font size preferences), not responsive
- **Leaving `"custom": true` in color/typography settings:** Allows editors to pick arbitrary colors/sizes outside brand palette
- **Not setting `customSpacingSize: false`:** Editors can input random spacing values breaking design rhythm
- **Installing page builders (Elementor, Divi) for design control:** Adds 500KB+ bloat, kills performance, vendor lock-in
- **Using multiple contact form plugins:** Database bloat, security surface area, maintenance burden
- **Not optimizing images for Core Web Vitals:** Large images kill LCP; must convert to WebP/AVIF
- **Ignoring mobile-first CSS:** Desktop-first media queries harder to maintain, mobile penalty

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Fluid typography | Custom CSS clamp() calculations | theme.json `"fluid": true` | WordPress auto-generates clamp() with accessibility features, viewport math, editor integration |
| Spacing system | Hard-coded spacing values | theme.json spacingScale or spacingSizes | Generates CSS variables, enforces consistency, controls editor UI |
| Contact forms | Custom form HTML/PHP | WPForms or Contact Form 7 | Spam protection, validation, integrations, email templates, accessibility |
| WooCommerce search | Custom search query modifications | FiboSearch or SearchWP | AJAX live search, product-specific algorithms, mobile optimization, analytics |
| Responsive images | Manual picture/srcset | WordPress native + ShortPixel/Imagify | Automatic srcset generation, WebP/AVIF conversion, lazy loading |
| Design tokens | CSS variables manually defined | theme.json color/typography/spacing | Auto-generates variables AND utility classes, editor preview, global styles UI |
| Style variations | Multiple theme installs | theme.json style variations | Users can switch color schemes via Site Editor without code changes |

**Key insight:** WordPress 6.9+ with theme.json v3 eliminates 70-80% of custom CSS needs. Fluid typography + spacing scales + CSS clamp() achieve responsive design without extensive media queries. Focus custom CSS on layout patterns theme.json can't control (navigation toggles, grid columns, flexbox wrapping). Design tokens in theme.json are source of truth for frontend AND editor.

## Common Pitfalls

### Pitfall 1: Overusing Custom CSS Instead of theme.json
**What goes wrong:** Developers write custom CSS for typography and spacing that theme.json could handle, resulting in inconsistent editor preview, more code to maintain, and missing out on accessibility features.
**Why it happens:** Developers unfamiliar with theme.json v3 capabilities default to CSS, or don't trust WordPress to generate responsive values.
**How to avoid:**
1. Always check if theme.json can handle it before writing CSS
2. Use fluid typography for all text: `"fluid": true` or per-size fluid objects
3. Use spacingScale for all spacing - generates consistent rhythm
4. Reserve custom CSS for layout patterns only (flexbox, grid, navigation)
**Warning signs:** CSS file has font-size media queries, manual clamp() calculations, hard-coded spacing values like `margin: 24px`

**Sources:**
- [WordPress Global Styles Guide](https://stepfoxthemes.com/wordpress-global-styles-guide/)
- [10up Block Spacing Best Practices](https://gutenberg.10up.com/guides/handeling-block-spacing/)

### Pitfall 2: Not Configuring Design Token Constraints
**What goes wrong:** Editors select arbitrary colors, font sizes, or spacing values outside the design system, creating visual inconsistency across the site.
**Why it happens:** Default theme.json has `"custom": true` for colors and `"customFontSize": true` for typography, allowing unrestricted values.
**How to avoid:**
1. Set `"custom": false` in color settings - forces palette use
2. Set `"customFontSize": false` - forces preset sizes
3. Set `"customSpacingSize": false` - forces spacing scale
4. Set `"defaultPalette": false` - removes WordPress default colors
**Warning signs:** Site has 20+ different colors, text sizes not in preset scale, spacing values like `padding: 17px`

**Example configuration:**
```json
{
  "settings": {
    "color": {
      "custom": false,
      "defaultPalette": false
    },
    "typography": {
      "customFontSize": false
    },
    "spacing": {
      "customSpacingSize": false
    }
  }
}
```

**Source:** [Full Site Editing Global Styles](https://fullsiteediting.com/lessons/global-styles/)

### Pitfall 3: Ignoring Core Web Vitals Impact of Design Choices
**What goes wrong:** Design choices kill performance metrics - large hero images tank LCP, layout shifts from web fonts hurt CLS, heavy animations impact INP.
**Why it happens:** Designers focus on aesthetics without measuring performance impact.
**How to avoid:**
1. **LCP (< 2.5s):** Optimize hero images to WebP/AVIF, use `fetchpriority="high"` on LCP image, lazy load below-fold images
2. **CLS (< 0.1):** Reserve space for images with width/height attributes, use `font-display: swap` carefully, avoid layout-shifting animations
3. **INP (< 200ms):** Minimize JavaScript, avoid heavy click handlers, use passive event listeners
4. Test with Query Monitor plugin during development
5. Validate with PageSpeed Insights before deployment
**Warning signs:** PageSpeed Insights shows red CWV scores, LCP > 3s, CLS > 0.2, INP > 300ms

**Sources:**
- [WordPress Core Web Vitals Optimization](https://wp-rocket.me/google-core-web-vitals-wordpress/)
- [Core Web Vitals Guide 2026](https://skyseodigital.com/core-web-vitals-optimization-complete-guide-for-2026/)

### Pitfall 4: Using Fixed Breakpoints When Fluid Design Suffices
**What goes wrong:** Excessive media queries for layouts that could use CSS clamp(), flexbox wrapping, or CSS Grid auto-fit for responsive behavior.
**Why it happens:** Habit from pre-clamp() era, or not understanding modern CSS responsive capabilities.
**How to avoid:**
1. Use `clamp()` for responsive sizing: `width: clamp(300px, 50vw, 600px)`
2. Use flexbox with `flex-wrap: wrap` for responsive grids
3. Use CSS Grid `auto-fit`: `grid-template-columns: repeat(auto-fit, minmax(300px, 1fr))`
4. Reserve media queries for true layout changes (navigation, sidebar hide/show)
**Warning signs:** CSS has 10+ media queries, responsive behavior could work with flexbox/grid

**Example - Responsive without media queries:**
```css
/* Responsive grid without breakpoints */
.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(min(300px, 100%), 1fr));
  gap: var(--wp--preset--spacing--medium);
}

/* Responsive padding with clamp */
.content-wrapper {
  padding-inline: clamp(1rem, 5vw, 4rem);
}
```

**Source:** [Fluid Typography CSS Clamp](https://www.smashingmagazine.com/2022/01/modern-fluid-typography-css-clamp/)

### Pitfall 5: Contact Form Plugin Bloat
**What goes wrong:** Installing multiple form plugins (WPForms + Contact Form 7 + Gravity Forms) or feature-heavy plugins when simple forms suffice.
**Why it happens:** Different team members install preferred plugins, or adding features "just in case."
**How to avoid:**
1. **Choose ONE contact form plugin** - WPForms Lite OR Contact Form 7, not both
2. Start with free version, upgrade to Pro only when specific paid feature needed
3. For simple inquiry forms, Contact Form 7 sufficient
4. For WooCommerce integration, conditional logic, payment processing: WPForms Pro
5. Deactivate unused form plugins after migration
**Warning signs:** Multiple form plugins active, database has wp_cf7_*, wp_wpforms_*, wp_gf_* tables, admin UI slow

**Decision matrix:**
- **Contact Form 7:** Free, lightweight (800KB), text-based UI, basic WooCommerce integration via addons
- **WPForms Lite:** Free, drag-and-drop UI, better UX, basic forms, limited WooCommerce
- **WPForms Pro:** Paid, full WooCommerce integration, Stripe payments, conditional logic, file uploads

**Source:** [WPForms vs Contact Form 7 Comparison](https://www.wpbeginner.com/opinion/contact-form-7-vs-wpforms/)

### Pitfall 6: Search Plugin Configuration Mismatch
**What goes wrong:** Installing FiboSearch but not configuring it for Norwegian language, not enabling SKU search, or not styling it to match theme gold color.
**Why it happens:** Plugin activated but configuration skipped, assuming defaults are sufficient.
**How to avoid:**
1. After FiboSearch activation: Settings > FiboSearch
2. Enable: "Search in product SKU", "Search in short description", "Search in long description"
3. Enable: "Show product images", "Show product price", "Show product description"
4. Set mobile layout: "Mobile optimized"
5. Styling: Set accent color to #f7a720 (gold button color)
6. Test: Search for Norwegian heating product terms ("varmepumpe", "peis", "varmeovn")
7. Multilingual: If using WPML/Polylang, configure language switching in search results
**Warning signs:** Search returns incomplete results, SKU searches fail, mobile search not optimized, search bar doesn't match theme design

**Source:** [FiboSearch Documentation](https://wordpress.org/plugins/ajax-search-for-woocommerce/)

### Pitfall 7: Not Testing Responsive Design on Real Devices
**What goes wrong:** Design looks perfect on desktop browser dev tools but broken on actual mobile devices (iPhone, Android).
**Why it happens:** Browser dev tools simulate but don't perfectly replicate real device rendering, touch interactions, or viewport quirks.
**How to avoid:**
1. Test on physical devices: iPhone (Safari), Android (Chrome)
2. Test target breakpoints: 320px (iPhone SE), 768px (iPad), 1440px (desktop)
3. Test touch interactions: tap targets min 44×44px, form inputs, navigation
4. Use browserstack.com or similar for device testing if physical devices unavailable
5. WordPress editor responsive preview is starting point, not validation
**Warning signs:** Reports of mobile issues from users, navigation broken on iOS, forms difficult to use on mobile

**Source:** [WordPress Mobile-Friendly Support](https://wordpress.com/support/make-your-website-mobile-friendly/)

## Code Examples

Verified patterns from official sources:

### Complete Enhanced theme.json with Fluid Typography and Spacing Scale
```json
{
  "$schema": "https://schemas.wp.org/trunk/theme.json",
  "version": 3,
  "settings": {
    "appearanceTools": true,
    "useRootPaddingAwareAlignments": true,
    "color": {
      "custom": false,
      "defaultPalette": false,
      "palette": [
        {
          "slug": "primary",
          "color": "#1a1a1a",
          "name": "Primary (Dark)"
        },
        {
          "slug": "secondary",
          "color": "#767676",
          "name": "Secondary (Gray)"
        },
        {
          "slug": "accent",
          "color": "#e63946",
          "name": "Accent (Red)"
        },
        {
          "slug": "gold",
          "color": "#f7a720",
          "name": "Gold (Brand)"
        },
        {
          "slug": "background",
          "color": "#ffffff",
          "name": "Background (White)"
        },
        {
          "slug": "surface",
          "color": "#f8f9fa",
          "name": "Surface (Light Gray)"
        }
      ]
    },
    "typography": {
      "fluid": true,
      "lineHeight": true,
      "customFontSize": false,
      "fontSizes": [
        {
          "slug": "small",
          "size": "0.875rem",
          "name": "Small",
          "fluid": false
        },
        {
          "slug": "medium",
          "size": "1rem",
          "name": "Medium",
          "fluid": {
            "min": "0.875rem",
            "max": "1rem"
          }
        },
        {
          "slug": "large",
          "size": "1.25rem",
          "name": "Large",
          "fluid": {
            "min": "1.125rem",
            "max": "1.5rem"
          }
        },
        {
          "slug": "x-large",
          "size": "1.75rem",
          "name": "Extra Large",
          "fluid": {
            "min": "1.5rem",
            "max": "2.25rem"
          }
        },
        {
          "slug": "xx-large",
          "size": "2.5rem",
          "name": "Heading",
          "fluid": {
            "min": "2rem",
            "max": "3rem"
          }
        }
      ]
    },
    "spacing": {
      "units": ["px", "em", "rem", "%"],
      "padding": true,
      "margin": true,
      "blockGap": true,
      "customSpacingSize": false,
      "spacingScale": {
        "operator": "*",
        "increment": 1.5,
        "steps": 7,
        "mediumStep": 1.5,
        "unit": "rem"
      }
    },
    "layout": {
      "contentSize": "1140px",
      "wideSize": "1400px"
    }
  },
  "styles": {
    "color": {
      "background": "var(--wp--preset--color--background)",
      "text": "var(--wp--preset--color--primary)"
    },
    "typography": {
      "fontSize": "var(--wp--preset--font-size--medium)",
      "lineHeight": "1.6"
    },
    "spacing": {
      "padding": {
        "top": "var(--wp--preset--spacing--50)",
        "bottom": "var(--wp--preset--spacing--50)"
      },
      "blockGap": "var(--wp--preset--spacing--50)"
    },
    "elements": {
      "button": {
        "color": {
          "background": "var(--wp--preset--color--gold)",
          "text": "var(--wp--preset--color--primary)"
        },
        "typography": {
          "fontSize": "var(--wp--preset--font-size--medium)",
          "fontWeight": "600"
        },
        "spacing": {
          "padding": {
            "top": "0.75rem",
            "bottom": "0.75rem",
            "left": "1.5rem",
            "right": "1.5rem"
          }
        },
        "border": {
          "radius": "4px"
        }
      },
      "link": {
        "color": {
          "text": "var(--wp--preset--color--gold)"
        },
        ":hover": {
          "color": {
            "text": "var(--wp--preset--color--accent)"
          }
        }
      },
      "heading": {
        "typography": {
          "fontWeight": "700",
          "lineHeight": "1.2"
        },
        "spacing": {
          "margin": {
            "bottom": "var(--wp--preset--spacing--40)"
          }
        }
      }
    },
    "blocks": {
      "core/heading": {
        "typography": {
          "fontWeight": "700"
        }
      },
      "core/paragraph": {
        "spacing": {
          "margin": {
            "bottom": "var(--wp--preset--spacing--40)"
          }
        }
      },
      "core/button": {
        "variations": {
          "outline": {
            "color": {
              "background": "transparent",
              "text": "var(--wp--preset--color--gold)"
            },
            "border": {
              "color": "var(--wp--preset--color--gold)",
              "width": "2px"
            }
          }
        }
      }
    }
  }
}
```

**Source:** [WordPress Global Settings & Styles](https://developer.wordpress.org/block-editor/how-to-guides/themes/global-settings-and-styles/)

### Responsive CSS for Layout Breakpoints
```css
/* style.css - Mobile-first responsive overrides */

/* Base styles (mobile 320px+) */
.wp-site-blocks {
  padding-left: 1rem;
  padding-right: 1rem;
}

.wp-block-columns {
  gap: var(--wp--preset--spacing--40);
}

/* Navigation mobile hamburger */
.wp-block-navigation__responsive-container {
  background-color: var(--wp--preset--color--background);
}

/* Tablet (768px+) */
@media (min-width: 768px) {
  .wp-site-blocks {
    padding-left: 2rem;
    padding-right: 2rem;
  }

  .wp-block-columns {
    gap: var(--wp--preset--spacing--50);
  }

  /* Two-column product grid */
  .woocommerce-products-header + .products {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* Desktop (1440px+) */
@media (min-width: 1440px) {
  .wp-site-blocks {
    padding-left: 4rem;
    padding-right: 4rem;
  }

  .wp-block-columns {
    gap: var(--wp--preset--spacing--60);
  }

  /* Three-column product grid */
  .woocommerce-products-header + .products {
    grid-template-columns: repeat(3, 1fr);
  }

  /* Hide hamburger menu on desktop */
  .wp-block-navigation__responsive-container-open {
    display: none;
  }

  .wp-block-navigation__responsive-container:not(.is-menu-open) {
    display: block;
    position: static;
    width: 100%;
    background-color: transparent;
  }
}

/* Accessibility - Reduce motion */
@media (prefers-reduced-motion: reduce) {
  * {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
```

**Source:** [Responsive Controls in Block Themes](https://olliewp.com/a-native-and-iterative-approach-to-responsive-control-in-wordress/)

### WPForms Product Inquiry Form Integration
```php
// functions.php - Add inquiry form to product pages

/**
 * Add product inquiry form below WooCommerce product meta
 */
add_action( 'woocommerce_single_product_summary', 'smartvarme_product_inquiry_form', 35 );

function smartvarme_product_inquiry_form() {
    global $product;

    // Only show on products (not variations)
    if ( ! $product || ! $product->is_type( 'simple' ) && ! $product->is_type( 'variable' ) ) {
        return;
    }

    ?>
    <div class="product-inquiry-wrapper" style="margin-top: var(--wp--preset--spacing--50); padding: var(--wp--preset--spacing--50); background-color: var(--wp--preset--color--surface); border-radius: 8px;">
        <h3 style="margin-top: 0; color: var(--wp--preset--color--primary);">
            <?php _e( 'Har du spørsmål om dette produktet?', 'smartvarme' ); ?>
        </h3>
        <p style="color: var(--wp--preset--color--secondary);">
            <?php _e( 'Våre eksperter er klare til å hjelpe deg. Fyll ut skjemaet nedenfor.', 'smartvarme' ); ?>
        </p>

        <?php
        // WPForms shortcode - replace 123 with actual form ID
        echo do_shortcode( '[wpforms id="123"]' );
        ?>

        <input type="hidden" name="wpforms[product_id]" value="<?php echo esc_attr( $product->get_id() ); ?>">
        <input type="hidden" name="wpforms[product_name]" value="<?php echo esc_attr( $product->get_name() ); ?>">
        <input type="hidden" name="wpforms[product_url]" value="<?php echo esc_url( get_permalink() ); ?>">
    </div>
    <?php
}

/**
 * Pass product data to WPForms via JavaScript
 */
add_action( 'wp_footer', 'smartvarme_wpforms_product_data' );

function smartvarme_wpforms_product_data() {
    if ( ! is_product() ) {
        return;
    }

    global $product;
    ?>
    <script>
    document.addEventListener( 'DOMContentLoaded', function() {
        var form = document.querySelector( '.product-inquiry-wrapper form' );
        if ( form ) {
            // Add hidden fields for product context
            var productId = '<?php echo esc_js( $product->get_id() ); ?>';
            var productName = '<?php echo esc_js( $product->get_name() ); ?>';

            // Create hidden field for product context
            var hiddenField = document.createElement( 'input' );
            hiddenField.type = 'hidden';
            hiddenField.name = 'product_context';
            hiddenField.value = 'Product: ' + productName + ' (ID: ' + productId + ')';
            form.appendChild( hiddenField );
        }
    });
    </script>
    <?php
}
```

**Source:** [WPForms WooCommerce Integration](https://integrately.com/integrations/woocommerce/wpforms)

### FiboSearch Configuration and Styling
```php
// functions.php - FiboSearch customization

/**
 * Replace default search with FiboSearch in header
 */
add_filter( 'get_search_form', 'smartvarme_fibosearch_form' );

function smartvarme_fibosearch_form( $form ) {
    // Replace with FiboSearch shortcode
    return do_shortcode( '[fibosearch]' );
}

/**
 * Customize FiboSearch output
 */
add_filter( 'dgwt/wcas/settings', 'smartvarme_fibosearch_settings' );

function smartvarme_fibosearch_settings( $settings ) {
    // Enable SKU search
    $settings['search_in_product_sku'] = true;

    // Show images
    $settings['show_product_image'] = true;

    // Show price
    $settings['show_product_price'] = true;

    // Minimum characters before search
    $settings['min_chars'] = 2;

    // Norwegian language
    $settings['search_submit_text'] = 'Søk';

    return $settings;
}
```

**Custom CSS for FiboSearch styling:**
```css
/* assets/css/fibosearch.css */

/* Match theme gold color */
.dgwt-wcas-search-wrapp .dgwt-wcas-sf-wrapp input[type="search"]:focus {
    border-color: var(--wp--preset--color--gold);
}

.dgwt-wcas-search-wrapp .dgwt-wcas-search-submit {
    background-color: var(--wp--preset--color--gold);
    color: var(--wp--preset--color--primary);
}

.dgwt-wcas-search-wrapp .dgwt-wcas-search-submit:hover {
    background-color: var(--wp--preset--color--accent);
}

/* Mobile-first responsive search */
.dgwt-wcas-search-wrapp {
    max-width: 100%;
}

@media (min-width: 768px) {
    .dgwt-wcas-search-wrapp {
        max-width: 600px;
    }
}

/* Match spacing scale */
.dgwt-wcas-suggestions-wrapp {
    padding: var(--wp--preset--spacing--40);
    gap: var(--wp--preset--spacing--30);
}
```

**Source:** [FiboSearch Plugin Documentation](https://wordpress.org/plugins/ajax-search-for-woocommerce/)

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| Fixed px font sizes | Fluid typography with CSS clamp() | WordPress 6.1 (2022) | Responsive text without media queries, accessibility for user font size preferences |
| Manual CSS custom properties | theme.json generated variables | WordPress 5.9 (2022) | Single source of truth, editor integration, automatic utility classes |
| Media queries for all responsive | CSS clamp() + flexbox/grid | Modern CSS (2020+) | Less CSS, more maintainable, better accessibility |
| Desktop-first responsive | Mobile-first responsive | Industry standard (2015+) | Better mobile performance, progressive enhancement |
| Page builders (Elementor, Divi) | Block theme + FSE | WordPress 5.9+ (2022) | Better performance (no 500KB+ bloat), no vendor lock-in |
| Contact Form 7 text-based UI | WPForms drag-and-drop | WPForms growth (2016+) | Faster form building, better UX, easier for non-developers |
| Basic WordPress search | FiboSearch AJAX search | FiboSearch popularity (2018+) | Live results, WooCommerce-aware, mobile-optimized |
| JPEG/PNG images | WebP/AVIF images | Browser support (2020+) | 50% smaller files, better Core Web Vitals (LCP) |
| Interaction to Next Paint (INP) | Replaces First Input Delay (FID) | March 2024 | Better measure of runtime responsiveness, stricter threshold |

**Deprecated/outdated:**
- **Fixed breakpoints for typography:** Use fluid typography instead
- **theme.json version 2:** Use version 3 for WordPress 6.6+ (current is 6.9.1)
- **First Input Delay (FID):** Replaced by INP in Core Web Vitals
- **Desktop-first CSS:** Mobile-first is standard
- **Manual CSS variables for design tokens:** Use theme.json generation
- **Gravity Forms:** Still works but WPForms more popular and better WooCommerce integration
- **JPEG-only images:** Must support WebP/AVIF for 2026 performance standards

## Open Questions

### 1. Typography Font Family Selection
**What we know:** Current theme.json has no custom font families defined, using system fonts.
**What's unclear:** Should Smartvarme use custom web fonts (Google Fonts, Adobe Fonts) or stick with system fonts for performance?
**Recommendation:**
- **Start with system fonts** for best Core Web Vitals (no font download, no CLS from font swapping)
- If brand requires custom fonts, use `font-display: swap` and preload font files
- System font stack: `-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif`
- Consider font subsetting if custom fonts needed (Latin + Norwegian characters only)

### 2. Mobile Navigation Pattern
**What we know:** WordPress Navigation block has hamburger menu for mobile, but styling/behavior unknown.
**What's unclear:** Should hamburger menu slide in, overlay, or push content? What breakpoint for mobile menu?
**Recommendation:**
- **Use WordPress Navigation block default** (overlay hamburger at < 600px)
- Test on 320px devices (iPhone SE) to ensure touch targets 44×44px minimum
- Consider custom breakpoint at 768px if default doesn't work for Norwegian text length
- Avoid custom JavaScript - use CSS-only for better performance (INP)

### 3. Contact Form vs WooCommerce Native Inquiry
**What we know:** Requirements specify "contact forms integrated into product pages" but WooCommerce has native product inquiry plugins.
**What's unclear:** Should we use separate contact form plugin or WooCommerce-specific inquiry plugin?
**Recommendation:**
- **Use WPForms Lite** as starting point (free, WooCommerce integration exists, Norwegian translation)
- If WPForms insufficient, consider WooCommerce Product Enquiry Form plugin
- Avoid mixing multiple solutions - pick one and commit
- Start simple (email contact), add complexity (quote requests, file uploads) only if needed

### 4. Search Performance Requirements
**What we know:** FiboSearch free version suitable for < 1000 products, Pro for larger catalogs.
**What's unclear:** How many products does Smartvarme have? What's growth projection?
**Recommendation:**
- **Measure current product count:** `wp post list --post_type=product --format=count`
- If < 500 products: FiboSearch free sufficient
- If 500-1000 products: FiboSearch free, monitor performance
- If > 1000 products: Budget for FiboSearch Pro (€59/year single site)
- Test search performance with Query Monitor to validate choice

### 5. Image Optimization Timing
**What we know:** Core Web Vitals require optimized images (WebP/AVIF), ShortPixel/Imagify can convert.
**What's unclear:** Should image optimization happen in Phase 5 (Design) or Phase 6 (Performance)?
**Recommendation:**
- **Phase 5:** Install image optimization plugin (ShortPixel or Imagify free)
- **Phase 5:** Convert new uploads to WebP automatically
- **Phase 6:** Bulk convert existing images (performance phase focus)
- This prevents new images from degrading LCP while deferring bulk conversion
- Rationale: Design phase adds hero images, product images - optimize at source

### 6. Style Variations for Future Flexibility
**What we know:** theme.json v3 supports style variations (alternate color schemes in /styles folder).
**What's unclear:** Does Smartvarme need multiple color schemes? (e.g., dark mode, high contrast)
**Recommendation:**
- **Phase 5:** Create single default style (current gold/white scheme)
- **Future consideration:** Add style variations if accessibility requirements demand (high contrast mode)
- **Not priority:** Multiple color schemes add complexity without clear business value
- Keep option open by using theme.json properly (easy to add variations later)

## Sources

### Primary (HIGH confidence)
**Official WordPress Documentation:**
- [WordPress Typography Settings](https://developer.wordpress.org/themes/global-settings-and-styles/settings/typography/) - Fluid typography, font sizes, theme.json v3
- [WordPress Spacing Settings](https://developer.wordpress.org/themes/global-settings-and-styles/settings/spacing/) - spacingScale, spacingSizes, configuration
- [WordPress Global Settings & Styles](https://developer.wordpress.org/block-editor/how-to-guides/themes/global-settings-and-styles/) - Complete theme.json reference
- [WordPress Style Variations](https://developer.wordpress.org/themes/global-settings-and-styles/style-variations/) - Alternative color schemes
- [Everything About Spacing in Block Themes](https://developer.wordpress.org/news/2023/03/everything-you-need-to-know-about-spacing-in-block-themes/) - Spacing best practices

**WordPress Plugins (Official):**
- [FiboSearch Plugin Page](https://wordpress.org/plugins/ajax-search-for-woocommerce/) - v1.32.2, features, performance specs
- [Contact Form 7 Plugin Page](https://wordpress.org/plugins/contact-form-7/) - v6.1.5, Norwegian translation, 10M+ installs

**Performance (Official):**
- [Core Web Vitals Documentation](https://developers.google.com/search/docs/appearance/core-web-vitals) - Google official metrics, thresholds
- [WordPress Core Web Vitals Guide](https://wp-rocket.me/google-core-web-vitals-wordpress/) - LCP, INP, CLS optimization

### Secondary (MEDIUM confidence)
**Design Systems & Best Practices:**
- [WordPress Global Styles Guide - StepFox](https://stepfoxthemes.com/wordpress-global-styles-guide/) - Design token implementation
- [Block Theme Development Best Practices - StepFox](https://stepfoxthemes.com/block-theme-development-practices/) - Modern patterns
- [10up Block Spacing Best Practices](https://gutenberg.10up.com/guides/handeling-block-spacing/) - Industry standards
- [10up Global Styles Reference](https://gutenberg.10up.com/reference/Themes/theme-json/) - Theme.json examples

**Responsive Design:**
- [Responsive Controls in Block Themes - Ollie](https://olliewp.com/a-native-and-iterative-approach-to-responsive-control-in-wordress/) - Native responsive patterns
- [Responsive Breakpoints in WordPress](https://codecanel.com/responsive-breakpoints-in-wordpress/) - Common breakpoints, media queries
- [Using Additional CSS in Block Themes](https://alwaysopen.design/additional-css-wordpress-blocks/) - Custom CSS placement

**Typography & Spacing:**
- [Modern Fluid Typography - Smashing Magazine](https://www.smashingmagazine.com/2022/01/modern-fluid-typography-css-clamp/) - CSS clamp() deep dive
- [Fluid Typography in Block Themes](https://gutenbergmarket.com/news/understanding-fluid-typography-in-wordpress-block-themes) - WordPress implementation
- [Responsive CSS Calc and Clamp - Crocoblock](https://crocoblock.com/blog/wordpress-responsive-css-calc-clamp/) - Practical examples

**Contact Forms & Search:**
- [WPForms vs Contact Form 7 - WP Beginner](https://www.wpbeginner.com/opinion/contact-form-7-vs-wpforms/) - Feature comparison
- [WPForms WooCommerce Integration](https://integrately.com/integrations/woocommerce/wpforms) - Integration capabilities
- [Best WooCommerce Search Plugins](https://searchwp.com/best-woocommerce-search-plugins/) - FiboSearch alternatives

**Design Trends 2026:**
- [WordPress Design Trends 2026 - WebomindApps](https://www.webomindapps.com/blog/top-10-wordpress-web-design-trends-2026.html) - Clean, minimal, spacing
- [Modern WordPress Design Trends - Omnime](https://omni.me.uk/modern-wordpress-design-trends/) - Block-based, global styles
- [Minimalist Web Design Trends 2026](https://www.digitalsilk.com/digital-trends/minimalist-web-design-trends/) - White space, typography

### Tertiary (LOW confidence - verify during implementation)
**Community Resources:**
- [WordPress Block Patterns Guide - Bluehost](https://www.bluehost.com/blog/wordpress-block-patterns/) - Pattern concepts
- [WordPress Performance Optimization 2026 - Next3Offload](https://next3offload.com/blog/wordpress-performance-optimization/) - Performance techniques
- [Core Web Vitals Optimization Guide - Sky SEO](https://skyseodigital.com/core-web-vitals-optimization-complete-guide-for-2026/) - Optimization strategies

## Metadata

**Confidence breakdown:**
- **Standard stack: HIGH** - theme.json v3, fluid typography, FiboSearch, WPForms all verified from official sources with version numbers and capabilities
- **Architecture: HIGH** - Fluid typography, spacing scale, responsive breakpoints all documented in official WordPress handbooks with code examples
- **Contact forms: HIGH** - WPForms and Contact Form 7 comparison verified with official plugin pages, download stats, Norwegian translation confirmed
- **Search: HIGH** - FiboSearch capabilities verified from plugin page, performance specs (10× Pro) confirmed, WooCommerce integration documented
- **Responsive design: MEDIUM-HIGH** - Breakpoint strategies verified, but theme.json media query limitation requires custom CSS validation
- **Design trends: MEDIUM** - 2026 trends (minimalism, spacing, block-based) consistent across multiple sources but subjective
- **Image optimization: MEDIUM** - WebP/AVIF benefits documented, but specific plugin performance comparisons vary

**Research date:** 2026-02-12
**Valid until:** 2026-04-12 (WordPress 7.0 releases April 9, 2026 - may introduce new design features)

**WordPress version context:**
- Current stable: WordPress 6.9.1 (released February 3, 2026)
- Next major: WordPress 7.0 (scheduled April 9, 2026)
- theme.json version: 3 (current for WordPress 6.6+)
- Notable 6.9 features used: Native Accordion blocks, fluid typography refinements
- Notable 7.0 features: Always-iframed editor (may affect custom CSS blocks)

**Notes for planner:**
- Phase 5 builds on existing minimal theme.json - don't start from scratch
- Gold button color #f7a720 already in palette - use as accent throughout
- 1400px wide layout already configured - maintain consistency
- Norwegian (nb_NO) primary language - verify all plugin translations
- Design must support Core Web Vitals targets (Phase 6 measures, Phase 5 implements)
- No page builders - stick to block theme + FSE approach
- Contact forms and search are functional requirements, not optional enhancements
- Responsive testing must include physical devices, not just browser dev tools
