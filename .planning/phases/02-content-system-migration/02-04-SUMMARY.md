---
phase: 02-content-system-migration
plan: 04
subsystem: content-editor
tags: [gutenberg-blocks, woocommerce-integration, interactive-widgets, domain-logic]
dependency_graph:
  requires:
    - "02-01 (Block pattern infrastructure)"
    - "WooCommerce product data"
  provides:
    - "Product comparison block (smartvarme/product-comparison)"
    - "Energy calculator block (smartvarme/energy-calculator)"
    - "Interactive product selection UI"
    - "Heat loss calculation logic"
  affects:
    - "Content editor capabilities"
    - "Landing page templates"
    - "Product marketing pages"
tech_stack:
  added:
    - "@wordpress/create-block scaffolding"
    - "@wordpress/scripts build system"
    - "Server-side block rendering (render_callback)"
    - "WooCommerce REST API integration (apiFetch)"
  patterns:
    - "Dynamic blocks with PHP render callbacks"
    - "Block attributes defined in block.json"
    - "InspectorControls for editor settings"
    - "WooCommerce product queries in render logic"
key_files:
  created:
    - "wp-content/plugins/smartvarme-core/blocks/product-comparison/index.php"
    - "wp-content/plugins/smartvarme-core/blocks/product-comparison/src/edit.js"
    - "wp-content/plugins/smartvarme-core/blocks/product-comparison/src/style.scss"
    - "wp-content/plugins/smartvarme-core/blocks/product-comparison/block.json"
    - "wp-content/plugins/smartvarme-core/blocks/energy-calculator/index.php"
    - "wp-content/plugins/smartvarme-core/blocks/energy-calculator/src/edit.js"
    - "wp-content/plugins/smartvarme-core/blocks/energy-calculator/src/style.scss"
    - "wp-content/plugins/smartvarme-core/blocks/energy-calculator/block.json"
  modified:
    - "wp-content/plugins/smartvarme-core/smartvarme-core.php"
decisions:
  - "Use @wordpress/create-block for scaffolding instead of manual setup"
  - "Server-side rendering for blocks that query databases (Pattern 3 from research)"
  - "Dynamic blocks with render_callback to avoid frontend JavaScript complexity"
  - "WooCommerce REST API for product selection in editor, PHP queries for frontend"
  - "Heat loss calculation: 100W/m² (poor), 70W/m² (medium), 50W/m² (good insulation)"
  - "Fixed block.json paths to reference build/ directory for compiled assets"
metrics:
  duration: "8m 22s"
  tasks_completed: 3
  files_created: 30
  commits: 3
  build_time: "~1.4s per block"
  completed_at: "2026-02-12T06:46:51Z"
---

# Phase 02 Plan 04: Custom Gutenberg Blocks Summary

**One-liner:** Product comparison and energy calculator blocks with server-side rendering, WooCommerce integration, and heat loss calculation logic.

## Overview

Created two domain-specific Gutenberg blocks to enable content editors to add interactive widgets without custom code. Both blocks use server-side rendering for database queries and calculations, with modern React-based editor UIs.

## What Was Built

### Product Comparison Block (smartvarme/product-comparison)

**Editor Experience:**
- InspectorControls panel for selecting 2-3 products from WooCommerce catalog
- Product dropdown populated via WooCommerce REST API (`/wc/v3/products`)
- Add/remove product buttons
- Live preview showing selected product count

**Frontend Output:**
- Server-side rendered comparison table
- Columns: Price, Effekt (kW), Energiklasse, Leveringstid
- Uses `wc_get_product()` to fetch product data
- Styled with responsive table layout (primary color header, striped rows)

**Technical Implementation:**
- `@wordpress/create-block` scaffolding
- Dynamic block registration via `register_block_type( __DIR__ )`
- Attributes: `productIds` (array)
- Render callback: `smartvarme_render_product_comparison()`

### Energy Calculator Block (smartvarme/energy-calculator)

**Editor Experience:**
- InspectorControls for default values (house size, insulation)
- RangeControl for square meters (20-500m², step 10)
- SelectControl for insulation quality (poor/medium/good)
- Preview shows current default settings

**Frontend Output:**
- Form inputs for house size and insulation quality
- Heat loss calculation using simplified formula:
  - Poor insulation (før 1970): 100W/m²
  - Medium insulation (1970-2000): 70W/m²
  - Good insulation (etter 2000): 50W/m²
- Result displays recommended kW capacity
- Top 3 product recommendations queried from WooCommerce
- Product cards with image, name, price, power rating, link

**Technical Implementation:**
- Server-side calculation via `calculate_heat_need()` function
- Form submission reloads page with query parameters
- WooCommerce meta query for products with `_effekt_kw >= calculated_kw`
- Attributes: `defaultSquareMeters` (number), `defaultInsulation` (string)

### Integration

**Plugin Loading:**
- Added `smartvarme_load_custom_blocks()` function to main plugin file
- Loads via `plugins_loaded` hook
- Requires block index.php files from `blocks/` directory

**Block Registration:**
- Both blocks registered on WordPress `init` hook
- Verified via WP-CLI: 2 smartvarme/* blocks in registry
- Test page created with both blocks, loads successfully (HTTP 200)

## Key Achievements

1. **Modern Block Development:** Used @wordpress/create-block for proper scaffolding and build setup
2. **Server-Side Rendering:** Implemented render callbacks for database queries and calculations
3. **WooCommerce Integration:** Product selection in editor, product queries on frontend
4. **Domain Logic:** Heat loss calculation specific to heat pump sizing
5. **Editor Experience:** InspectorControls for block configuration
6. **Responsive Styling:** SCSS with CSS custom properties for theming

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 3 - Blocking] Missing @babel/runtime dependency**
- **Found during:** Task 1 build
- **Issue:** Webpack failed with "Cannot find module '@babel/runtime/helpers/interopRequireDefault'"
- **Fix:** Added `@babel/runtime` to devDependencies in package.json
- **Files modified:** `blocks/product-comparison/package.json`, `blocks/energy-calculator/package.json`
- **Commit:** Included in Task 1 and Task 2 commits

**2. [Rule 3 - Blocking] Incorrect src/ directory structure**
- **Found during:** Task 1 build
- **Issue:** @wordpress/create-block with --no-plugin flag created files in root, not src/
- **Fix:** Created src/ directory and moved source files (edit.js, index.js, save.js, style.scss, editor.scss)
- **Files modified:** Block directory structure
- **Commit:** Included in Task 1 and Task 2 commits

**3. [Rule 3 - Blocking] Wrong block.json import path**
- **Found during:** Task 1 build
- **Issue:** index.js imported `./block.json` but it's in parent directory after moving to src/
- **Fix:** Changed import to `../block.json`
- **Files modified:** `src/index.js` in both blocks
- **Commit:** Included in Task 1 and Task 2 commits

**4. [Rule 3 - Blocking] Block.json asset paths incorrect**
- **Found during:** Task 3 verification
- **Issue:** block.json referenced `file:./index.js` but assets are in build/ directory
- **Fix:** Updated paths to `file:./build/index.js`, `file:./build/index.css`, `file:./build/style-index.css`
- **Files modified:** `block.json` in both blocks
- **Commit:** Task 3 (d068eb24)

**5. [Rule 3 - Blocking] Wrong register_block_type directory**
- **Found during:** Task 3 verification
- **Issue:** Used `__DIR__ . '/build'` but block.json is in block root
- **Fix:** Changed to `__DIR__` (WordPress finds block.json and references build assets via block.json paths)
- **Files modified:** `index.php` in both blocks
- **Commit:** Task 3 (d068eb24)

## Verification Results

**Task 1 Verification (Product Comparison):**
- ✅ Build output exists: `blocks/product-comparison/build/index.js` (2.9KB)
- ✅ PHP syntax valid: No errors in index.php
- ✅ Block registration present: `register_block_type` found
- ✅ WooCommerce query present: `wc_get_product` found
- ✅ Build succeeds: webpack compiled successfully in 616ms

**Task 2 Verification (Energy Calculator):**
- ✅ Build output exists: `blocks/energy-calculator/build/index.js` (2.5KB)
- ✅ PHP syntax valid: No errors in index.php
- ✅ Calculation logic present: `calculate_heat_need` function found
- ✅ Product query present: `wc_get_products` found
- ✅ Build succeeds: webpack compiled successfully in 751ms

**Task 3 Verification (Integration):**
- ✅ Block loading function exists: `smartvarme_load_custom_blocks` in main plugin file
- ✅ Blocks registered: WP-CLI reports 2 smartvarme/* blocks
- ✅ Block names: `smartvarme/product-comparison`, `smartvarme/energy-calculator`
- ✅ Test page created: 1 page found with title "Test: Custom Blocks"
- ✅ Page loads: HTTP 200 at http://localhost:8080/test-custom-blocks/

## Testing Notes

**Blocks appear in editor:** Both blocks searchable by Norwegian titles:
- "Produktsammenligning" → Product comparison block
- "Energikalkulator" → Energy calculator block

**Block functionality:**
- Product comparison allows selecting products via InspectorControls
- Energy calculator shows default settings and preview in editor
- Frontend rendering handled by PHP render callbacks (not yet fully tested with actual WooCommerce data)

**Known limitations:**
- Product attributes (`pa_effekt`, `pa_energiklasse`) may not exist yet in WooCommerce taxonomy
- Product meta `_effekt_kw` needs to be populated for energy calculator recommendations
- These will be addressed in Phase 3 (Product & E-commerce) when WooCommerce is configured

## Technical Decisions

**Why server-side rendering?**
- Blocks query databases (WooCommerce products)
- Pattern 3 from research: "Use server-side rendering for blocks that query databases"
- Avoids frontend JavaScript complexity and improves performance
- Editor UI is React-based, but frontend is pure PHP/HTML

**Why @wordpress/create-block?**
- Official WordPress scaffolding tool
- Includes proper build setup with @wordpress/scripts
- Follows WordPress block development best practices
- Automatic webpack configuration

**Why separate blocks instead of one combined widget?**
- Single Responsibility Principle: Each block has one clear purpose
- Flexibility: Editors can use product comparison or energy calculator independently
- Maintainability: Easier to test and modify isolated functionality

## Performance Impact

**Build time:** ~1.4 seconds per block (webpack compilation)
**Bundle sizes:**
- Product comparison: 2.9KB JS + 743B CSS
- Energy calculator: 2.5KB JS + 1.8KB CSS
**Server-side queries:** WooCommerce product queries cached by WooCommerce

## Next Steps

These blocks are now available in the editor. Next plans will:
- Create landing page templates that use these blocks (Plan 02-05 or similar)
- Configure WooCommerce product attributes in Phase 3
- Add product metadata (`_effekt_kw`) for calculator recommendations
- Test blocks with real product data

## Self-Check: PASSED

**Created files verified:**
```bash
✓ wp-content/plugins/smartvarme-core/blocks/product-comparison/index.php (83 lines)
✓ wp-content/plugins/smartvarme-core/blocks/product-comparison/src/edit.js (77 lines)
✓ wp-content/plugins/smartvarme-core/blocks/product-comparison/src/style.scss (27 lines)
✓ wp-content/plugins/smartvarme-core/blocks/product-comparison/block.json (24 lines)
✓ wp-content/plugins/smartvarme-core/blocks/product-comparison/build/index.js (2900 bytes)
✓ wp-content/plugins/smartvarme-core/blocks/energy-calculator/index.php (111 lines)
✓ wp-content/plugins/smartvarme-core/blocks/energy-calculator/src/edit.js (48 lines)
✓ wp-content/plugins/smartvarme-core/blocks/energy-calculator/src/style.scss (75 lines)
✓ wp-content/plugins/smartvarme-core/blocks/energy-calculator/block.json (28 lines)
✓ wp-content/plugins/smartvarme-core/blocks/energy-calculator/build/index.js (2480 bytes)
```

**Commits verified:**
```bash
✓ 98c729a6: feat(02-04): create product comparison block with server-side rendering
✓ 3c8f053e: feat(02-04): create energy calculator block with heat loss calculation
✓ d068eb24: feat(02-04): wire custom blocks into plugin and verify registration
```

**Block registration verified via WP-CLI:**
```bash
✓ smartvarme/product-comparison registered
✓ smartvarme/energy-calculator registered
```

All files exist, commits are in git history, blocks are registered in WordPress.

## Commits

- **98c729a6:** feat(02-04): create product comparison block with server-side rendering
- **3c8f053e:** feat(02-04): create energy calculator block with heat loss calculation
- **d068eb24:** feat(02-04): wire custom blocks into plugin and verify registration
