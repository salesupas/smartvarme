# Roadmap: Smartvarme Website Rebuild

## Overview

The rebuild follows a foundation-first, incremental rollout strategy that minimizes risk while delivering measurable performance improvements. Starting with development infrastructure and migration toolchain, we progress through content system modernization, WooCommerce setup with HPOS migration, transaction flow optimization, design implementation, and finally performance tuning with plugin consolidation. Each phase delivers verifiable capabilities that build toward the core goal: a noticeably faster, easier-to-manage WordPress/WooCommerce site.

## Phases

**Phase Numbering:**
- Integer phases (1, 2, 3): Planned milestone work
- Decimal phases (2.1, 2.2): Urgent insertions (marked with INSERTED)

Decimal phases appear between their surrounding integers in numeric order.

- [x] **Phase 1: Foundation & Infrastructure** - Development environment, build system, and migration toolchain ✅
- [x] **Phase 2: Content System & Migration** - Modern content editing with Gutenberg blocks and FAQ/blog migration ✅
- [x] **Phase 3: WooCommerce Setup & Product Display** - Product data migration and display templates ✅
- [x] **Phase 4: Cart & Checkout** - Transaction flow with payment integration ✅
- [ ] **Phase 5: Design & User Experience** - Modern visual design, typography, search, and forms (1/2 complete)
- [ ] **Phase 6: Performance & Plugin Optimization** - Final performance tuning and plugin consolidation

## Phase Details

### Phase 1: Foundation & Infrastructure
**Goal**: Establish development environment, block theme skeleton, build system, and migration toolchain to support all subsequent work

**Depends on**: Nothing (first phase)

**Requirements**: INFRA-01, INFRA-02, INFRA-03, INFRA-04, INFRA-05, INFRA-06, INFRA-07

**Success Criteria** (what must be TRUE):
  1. Developer can run WordPress site locally at localhost:8080 using Docker
  2. Theme compiles successfully using @wordpress/scripts build command
  3. Database can be imported from smartvarme_wp_zmmon.sql without serialization corruption
  4. Custom smartvarme-core plugin exists and activates without errors
  5. wp_options autoloaded data is under 800KB (measured using Query Monitor or database query)

**Plans**: 2 plans in 2 waves

Plans:
- [x] 01-01-PLAN.md -- Docker environment + Block theme skeleton + Build system + Plugin boilerplate ✅
- [x] 01-02-PLAN.md -- Database import, migration toolchain, autoload optimization + verification ✅

### Phase 2: Content System & Migration
**Goal**: Enable fast, easy content creation using modern Gutenberg blocks and migrate all existing FAQ/blog content

**Depends on**: Phase 1 (requires working development environment and migration toolchain)

**Requirements**: CONT-01, CONT-02, CONT-03, CONT-04, CONT-05, CONT-06, CONT-07, CONT-08, CONT-09, CONT-10, MIG-01, MIG-02 (for content URLs), MIG-03, MIG-04

**Success Criteria** (what must be TRUE):
  1. Content editor can create new FAQ article using Gutenberg block patterns in under 5 minutes
  2. Content editor can create new blog post using Gutenberg blocks without developer help
  3. All existing FAQ articles from old site are accessible at their original URLs
  4. All existing blog posts from old site are accessible at their original URLs
  5. FAQ page renders FAQ schema markup (verified in Google Rich Results Test)
  6. Synced patterns can be created and edited in one place, updates propagate to all instances
  7. Product comparison block allows side-by-side comparison of 2-3 products
  8. Energy calculator block calculates recommended heat pump size and suggests products

**Plans**: 5 plans in 3 waves (includes 1 gap closure plan)

Plans:
- [x] 02-01-PLAN.md -- Design tokens, pattern infrastructure, 5 block patterns (FAQ, hero, CTA, blog grid, product features), and synced patterns
- [x] 02-02-PLAN.md -- FAQ migration (Kadence to native accordion), FAQ schema markup, blog template enhancements (Wave 2)
- [x] 02-04-PLAN.md -- Custom domain blocks: product comparison and energy calculator (Wave 2, parallel with 02-02)
- [x] 02-05-PLAN.md -- Gap closure: FAQ custom post type with archive and single templates (Wave 2.5, after user feedback)
- [x] 02-03-PLAN.md -- URL verification, FAQ schema validation, synced patterns verification, custom blocks verification, and human verification of complete workflow

### Phase 3: WooCommerce Setup & Product Display
**Goal**: Migrate all product data with WooCommerce HPOS and create product display templates

**Depends on**: Phase 2 (requires theme foundation and migration toolchain proven with content)

**Requirements**: WOO-01, WOO-02, WOO-03, WOO-04, WOO-05, WOO-06, MIG-01 (product URLs), MIG-02 (product URLs), MIG-05, MIG-06

**Success Criteria** (what must be TRUE):
  1. All products from old site are accessible at their original URLs with correct data
  2. Product custom fields (stock display, delivery time) render correctly on product pages
  3. Product archive pages load with working filtering and sorting
  4. Variable products with 10+ variations display all variations correctly
  5. WooCommerce HPOS is enabled and orders are stored in custom tables (verified in WooCommerce > Settings > Advanced > Features)

**Plans**: 3 plans in 3 waves

Plans:
- [x] 03-01-PLAN.md -- HPOS enablement, product attribute registration, custom field infrastructure ✅
- [x] 03-02-PLAN.md -- Product display templates (single, archive, category) with stock/delivery display and sorting ✅
- [x] 03-03-PLAN.md -- Product URL verification, redirect safety net, comprehensive Phase 3 verification ✅

### Phase 4: Cart & Checkout
**Goal**: Complete transaction flow from cart to order confirmation with payment gateway integration

**Depends on**: Phase 3 (requires product data and display working)

**Requirements**: WOO-07, WOO-08, WOO-09

**Success Criteria** (what must be TRUE):
  1. User can add product to cart and view cart page with correct totals
  2. User can complete checkout flow and place test order successfully
  3. User receives order confirmation email after successful purchase
  4. Payment gateway (DIBS/Nets) processes test transaction without errors
  5. Cache is properly excluded from cart, checkout, and order confirmation pages (verified by checking dynamic content updates)

**Plans**: 2 plans in 2 waves

Plans:
- [x] 04-01-PLAN.md -- Cart & Checkout block pages, Mini-Cart in header, cache exclusions ✅
- [x] 04-02-PLAN.md -- Custom checkout fields, DIBS payment gateway config, Norwegian email customization, end-to-end verification ✅

### Phase 5: Design & User Experience
**Goal**: Implement modern visual design with improved typography, spacing, responsive layouts, search, and contact forms

**Depends on**: Phase 4 (requires complete site structure to apply design system)

**Requirements**: UX-01, UX-02, UX-03, UX-04, UX-05

**Success Criteria** (what must be TRUE):
  1. Site displays clean, modern design that looks noticeably different from current site
  2. Typography and spacing are consistent across all page types (blog, product, FAQ)
  3. Site is fully responsive on mobile (320px width), tablet (768px), and desktop (1440px)
  4. Contact forms are integrated into product pages and submit successfully
  5. Search functionality returns relevant results for common heating product queries

**Plans**: 2 plans in 2 waves

Plans:
- [x] 05-01-PLAN.md -- Design system enhancement: fluid typography, spacing scale, element/block styles, mobile-first responsive CSS ✅
- [ ] 05-02-PLAN.md -- Contact forms (Formidable Forms), search (FiboSearch), header integration, visual verification checkpoint

### Phase 6: Performance & Plugin Optimization
**Goal**: Achieve Core Web Vitals targets, reduce page load times by 50%+, and consolidate plugins to under 15

**Depends on**: Phase 5 (requires complete site to optimize)

**Requirements**: PERF-01, PERF-02, PERF-03, PERF-04, PERF-05, PERF-06, PERF-07, PLUG-01, PLUG-02, PLUG-03, PLUG-04

**Success Criteria** (what must be TRUE):
  1. Homepage achieves Core Web Vitals "good" rating (LCP < 2.5s, INP < 200ms, CLS < 0.1) in PageSpeed Insights
  2. Product page loads in 50% less time than current site (measured with GTmetrix or WebPageTest)
  3. Active plugin count is under 15 (verified in Plugins admin screen)
  4. All critical features from removed plugins work correctly (verified by feature inventory checklist)
  5. Cache hit ratio is above 80% after cache warming (verified via WP Rocket stats or server logs)

**Plans**: TBD

Plans:
- [ ] 06-01: TBD
- [ ] 06-02: TBD

## Progress

**Execution Order:**
Phases execute in numeric order: 1 -> 2 -> 3 -> 4 -> 5 -> 6

| Phase | Plans Complete | Status | Completed |
|-------|----------------|--------|-----------|
| 1. Foundation & Infrastructure | 2/2 | ✅ Complete | 2026-02-11 |
| 2. Content System & Migration | 5/5 | ✅ Complete | 2026-02-12 |
| 3. WooCommerce Setup & Product Display | 3/3 | ✅ Complete | 2026-02-12 |
| 4. Cart & Checkout | 2/2 | ✅ Complete | 2026-02-12 |
| 5. Design & User Experience | 1/2 | 🔄 In Progress | 2026-02-13 |
| 6. Performance & Plugin Optimization | 0/TBD | Not started | - |
