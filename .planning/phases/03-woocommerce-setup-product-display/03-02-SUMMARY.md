---
phase: 03-woocommerce-setup-product-display
plan: 02
subsystem: ui
tags: [woocommerce, fse, block-templates, product-display, scss, norwegian]

# Dependency graph
requires:
  - phase: 03-01
    provides: HPOS enabled, product attributes (pa_effekt, pa_energiklasse), custom meta fields (_effekt_kw, _delivery_time)
  - phase: 01-foundation
    provides: Block theme with FSE templates, theme.json, build system, header/footer parts
provides:
  - Single product page FSE template with two-column layout
  - Stock and delivery time display with Norwegian labels
  - Product archive/shop page with 3-column responsive grid
  - Product category pages with category title and description
  - Norwegian sorting options including custom "På lager først"
  - 12 products per page default
  - Responsive product card styles with hover effects
affects: [03-03, phase-04, phase-05-design]

# Tech tracking
tech-stack:
  added: []
  patterns:
    - FSE templates for WooCommerce product display
    - Custom WooCommerce template parts in theme/woocommerce/ directory
    - Norwegian localization via filters (woocommerce_catalog_orderby)
    - Stock status custom sorting via meta_key query manipulation
    - Responsive grid layouts with CSS Grid and media queries

key-files:
  created:
    - wp-content/themes/smartvarme-theme/templates/single-product.html
    - wp-content/themes/smartvarme-theme/templates/archive-product.html
    - wp-content/themes/smartvarme-theme/templates/taxonomy-product_cat.html
    - wp-content/themes/smartvarme-theme/woocommerce/single-product/stock-delivery.php
  modified:
    - wp-content/plugins/smartvarme-core/includes/class-smartvarme-woocommerce.php
    - wp-content/themes/smartvarme-theme/src/style.scss

key-decisions:
  - "Stock/delivery display hooked at priority 15 (between price and excerpt) in single product summary"
  - "Norwegian stock labels: På lager (green), Ikke på lager (red), På bestilling (yellow)"
  - "12 products per page (4 rows × 3 columns) for optimal browsing"
  - "Custom stock_status sorting option for in-stock-first filtering"
  - "Product images: consistent 300px height with object-fit: cover"
  - "3-column responsive grid: 3 cols desktop, 2 cols tablet, 1 col mobile"

patterns-established:
  - "WooCommerce template overrides in theme/woocommerce/ directory loaded via wc_get_template()"
  - "Stock/delivery info box: surface background (#f8f9fa), gold accent border for delivery time"
  - "Product card hover effects: shadow + 2px translateY for engagement"
  - "Norwegian sorting labels using woocommerce_catalog_orderby filter"
  - "Custom sorting logic via woocommerce_get_catalog_ordering_args filter"

# Metrics
duration: 3m 15s
completed: 2026-02-12
---

# Phase 3 Plan 2: Product Display & Filtering Summary

**WooCommerce product pages with Norwegian stock/delivery display, 3-column responsive archive grid, and custom "På lager først" sorting**

## Performance

- **Duration:** 3m 15s
- **Started:** 2026-02-12T08:44:20Z
- **Completed:** 2026-02-12T08:47:35Z
- **Tasks:** 2
- **Files modified:** 8

## Accomplishments
- Single product page with two-column layout displaying all product data including custom stock/delivery information
- Norwegian stock status labels with color-coded indicators (green/red/yellow)
- Product archive and category pages with responsive 3-column grid (12 products per page)
- Norwegian sorting options including custom "På lager først" for in-stock-first filtering
- Fully responsive product displays across all breakpoints

## Task Commits

Each task was committed atomically:

1. **Task 1: Create single product template with stock/delivery display** - `d0b7e7bf` (feat)
2. **Task 2: Create archive and category templates with sorting** - `ad130bf7` (feat)

## Files Created/Modified

**Created:**
- `wp-content/themes/smartvarme-theme/templates/single-product.html` - FSE template for single product pages with two-column layout (60/40 split)
- `wp-content/themes/smartvarme-theme/templates/archive-product.html` - FSE template for shop page with 3-column product grid
- `wp-content/themes/smartvarme-theme/templates/taxonomy-product_cat.html` - FSE template for product category pages with category title/description
- `wp-content/themes/smartvarme-theme/woocommerce/single-product/stock-delivery.php` - Custom template part displaying stock status and delivery time with Norwegian labels

**Modified:**
- `wp-content/plugins/smartvarme-core/includes/class-smartvarme-woocommerce.php` - Added hooks for stock/delivery display, Norwegian sorting options, and products per page
- `wp-content/themes/smartvarme-theme/src/style.scss` - Added responsive styles for product pages, archive grids, stock/delivery info box, and pagination

## Decisions Made

1. **Stock/delivery hook priority 15** - Positioned between price (10) and excerpt (20) for optimal visual hierarchy
2. **Norwegian stock labels** - "På lager" (green), "Ikke på lager" (red), "På bestilling" (yellow) for clear status communication
3. **12 products per page** - 4 rows × 3 columns provides optimal browsing without overwhelming users
4. **Custom stock sorting** - "På lager først" option uses meta_key query to prioritize in-stock products
5. **Consistent 300px image height** - Maintains equal-height product cards established in Phase 1
6. **Gold accent for delivery time** - 3px border-left using brand gold (#f7a720) for visual prominence
7. **Responsive breakpoints** - 768px (2 cols), 480px (1 col) for mobile-optimized browsing

## Deviations from Plan

None - plan executed exactly as written.

## Issues Encountered

None - all tasks completed successfully with all verifications passing.

## User Setup Required

None - no external service configuration required.

## Next Phase Readiness

Ready for Phase 3 Plan 3 (Product comparison and calculator features). All product display infrastructure is in place:
- Single product pages render with all data including custom fields
- Archive and category pages provide browsable product grids
- Sorting and filtering foundation established
- Norwegian localization applied to all user-facing text
- Responsive layouts tested across breakpoints

Product comparison block and energy calculator can now integrate with these display templates.

## Self-Check: PASSED

All files verified to exist:
- ✓ wp-content/themes/smartvarme-theme/templates/single-product.html
- ✓ wp-content/themes/smartvarme-theme/templates/archive-product.html
- ✓ wp-content/themes/smartvarme-theme/templates/taxonomy-product_cat.html
- ✓ wp-content/themes/smartvarme-theme/woocommerce/single-product/stock-delivery.php

All commits verified:
- ✓ d0b7e7bf (Task 1)
- ✓ ad130bf7 (Task 2)

---
*Phase: 03-woocommerce-setup-product-display*
*Completed: 2026-02-12*
