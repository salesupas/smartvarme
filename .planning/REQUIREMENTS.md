# Requirements: Smartvarme Website Rebuild

**Defined:** 2026-02-11
**Core Value:** Fast page loads and snappy user experience

## v1 Requirements

Requirements for the modernized WordPress/WooCommerce site. Each maps to roadmap phases.

### Infrastructure & Foundation

- [ ] **INFRA-01**: Docker local development environment running on localhost:8080
- [ ] **INFRA-02**: Block theme with FSE (Full Site Editing) and theme.json configuration
- [ ] **INFRA-03**: @wordpress/scripts build system for asset compilation
- [ ] **INFRA-04**: WordPress 6.8+ on PHP 8.3+ and MariaDB 10.11 LTS
- [ ] **INFRA-05**: Database optimized with autoloaded data < 800KB
- [ ] **INFRA-06**: Migration toolchain with serialization-aware tools (WP-CLI search-replace)
- [ ] **INFRA-07**: Custom smartvarme-core plugin created for business logic

### Content System (FAQ/Blog)

- [ ] **CONT-01**: Gutenberg block editor with command palette and keyboard shortcuts
- [ ] **CONT-02**: 5-10 block patterns for common layouts (hero, FAQ section, product features, CTA, blog card)
- [ ] **CONT-03**: Reusable/synced blocks for repeating elements (contact CTAs, disclaimers)
- [ ] **CONT-04**: Native accordion blocks for FAQ sections
- [ ] **CONT-05**: FAQ schema markup for SEO (Google rich snippets)
- [ ] **CONT-06**: Custom domain-specific blocks (product comparison, energy savings calculator)
- [ ] **CONT-07**: Global styles system with locked design tokens (colors, typography, spacing)
- [ ] **CONT-08**: Mobile-responsive preview in block editor
- [ ] **CONT-09**: All existing FAQ content migrated from smartvarme_wp_zmmon.sql
- [ ] **CONT-10**: All existing blog posts migrated from smartvarme_wp_zmmon.sql

### WooCommerce & Products

- [ ] **WOO-01**: WooCommerce 10.x installed with HPOS (High Performance Order Storage) enabled
- [ ] **WOO-02**: All product data migrated including custom fields and metadata
- [ ] **WOO-03**: Product page templates (single product, archive, category pages)
- [ ] **WOO-04**: Product filtering and sorting functionality
- [ ] **WOO-05**: Product URLs preserved from current site (SEO requirement)
- [ ] **WOO-06**: Stock and delivery time display logic migrated
- [ ] **WOO-07**: Cart functionality with WooCommerce blocks
- [ ] **WOO-08**: Checkout flow optimized with payment gateway integration (DIBS/Nets)
- [ ] **WOO-09**: Order confirmation and transactional email templates

### Performance Optimization

- [ ] **PERF-01**: Image optimization pipeline with automatic WebP/AVIF conversion
- [ ] **PERF-02**: Page caching configured (WP Rocket or server-level)
- [ ] **PERF-03**: Asset loading control - disable unused CSS/JS per page
- [ ] **PERF-04**: Lazy loading for below-the-fold content (excluding hero/product images)
- [ ] **PERF-05**: Core Web Vitals targets achieved (LCP < 2.5s, INP < 200ms, CLS < 0.1)
- [ ] **PERF-06**: Page load times reduced by 50%+ vs current site
- [ ] **PERF-07**: Cache configuration excludes cart/checkout/account pages

### Plugin Consolidation

- [ ] **PLUG-01**: Complete feature inventory of all 25+ existing plugins
- [ ] **PLUG-02**: Plugin count reduced to under 15 essential plugins
- [ ] **PLUG-03**: All plugin functionality mapped to replacement strategy
- [ ] **PLUG-04**: Critical features moved to smartvarme-core custom plugin

### Migration & URL Preservation

- [ ] **MIG-01**: Complete URL inventory from current site (all pages, products, categories, archives)
- [ ] **MIG-02**: All URLs preserved - no broken links or 404 errors
- [ ] **MIG-03**: Database import from smartvarme_wp_zmmon.sql without data loss
- [ ] **MIG-04**: Serialized data migrated correctly (no corruption)
- [ ] **MIG-05**: Custom fields and metadata preserved for products
- [ ] **MIG-06**: 301 redirects configured for any URL structure changes

### Design & User Experience

- [ ] **UX-01**: Modern, clean visual design implemented
- [ ] **UX-02**: Improved typography and spacing throughout site
- [ ] **UX-03**: Responsive design for mobile, tablet, and desktop
- [ ] **UX-04**: Contact forms integrated into product pages
- [ ] **UX-05**: Search functionality (FiboSearch or equivalent)

## v2 Requirements

Deferred to future releases. Tracked but not in current roadmap.

### Advanced WooCommerce Features

- **WOO-ADV-01**: Custom product blocks (grid, featured product, comparison blocks)
- **WOO-ADV-02**: Advanced product filtering by heating capacity, efficiency, installation type
- **WOO-ADV-03**: Product recommendation engine
- **WOO-ADV-04**: Custom user account dashboard blocks

### Advanced Performance

- **PERF-ADV-01**: CDN integration (Cloudflare or BunnyCDN)
- **PERF-ADV-02**: Object caching with Redis or Memcached
- **PERF-ADV-03**: Database query optimization and monitoring
- **PERF-ADV-04**: Real User Monitoring (RUM) for performance tracking

### Custom Features & Integrations

- **FEAT-01**: ERP system integration for product sync and inventory
- **FEAT-02**: Custom reporting dashboard (sales by category, seasonal trends)
- **FEAT-03**: Advanced analytics and customer segmentation
- **FEAT-04**: Marketing automation integrations
- **FEAT-05**: Multi-language support (if Nordic expansion)

### Content Enhancements

- **CONT-ADV-01**: AI-powered content assistance (WordPress 6.9+ experimental)
- **CONT-ADV-02**: Content workflow and approval system
- **CONT-ADV-03**: Advanced SEO automation

## Out of Scope

Explicitly excluded. Documented to prevent scope creep.

| Feature | Reason |
|---------|--------|
| Headless WordPress architecture | High complexity, not needed - traditional WordPress sufficient for current needs |
| Complete platform migration (Shopify, etc.) | Staying on WordPress - better for content management and existing investment |
| Page builders (Elementor, Divi) | Performance killer - adds 500KB+, slow loads, vendor lock-in |
| Real-time product availability sync | Keep existing batch sync approach - not critical for heating products |
| Mobile native app | Web-first responsive design sufficient, app is future consideration |
| Custom checkout completely replacing WooCommerce | Too risky - use WooCommerce standard checkout with customizations |
| Migration to headless CMS | WordPress backend is working well for content management |

## Traceability

Which phases cover which requirements. Updated during roadmap creation.

| Requirement | Phase | Status |
|-------------|-------|--------|
| INFRA-01 | Phase 1 | Pending |
| INFRA-02 | Phase 1 | Pending |
| INFRA-03 | Phase 1 | Pending |
| INFRA-04 | Phase 1 | Pending |
| INFRA-05 | Phase 1 | Pending |
| INFRA-06 | Phase 1 | Pending |
| INFRA-07 | Phase 1 | Pending |
| CONT-01 | Phase 2 | Pending |
| CONT-02 | Phase 2 | Pending |
| CONT-03 | Phase 2 | Pending |
| CONT-04 | Phase 2 | Pending |
| CONT-05 | Phase 2 | Pending |
| CONT-06 | Phase 2 | Pending |
| CONT-07 | Phase 2 | Pending |
| CONT-08 | Phase 2 | Pending |
| CONT-09 | Phase 2 | Pending |
| CONT-10 | Phase 2 | Pending |
| MIG-01 | Phase 2, Phase 3 | Pending |
| MIG-02 | Phase 2, Phase 3 | Pending |
| MIG-03 | Phase 2 | Pending |
| MIG-04 | Phase 2 | Pending |
| MIG-05 | Phase 3 | Pending |
| MIG-06 | Phase 3 | Pending |
| WOO-01 | Phase 3 | Pending |
| WOO-02 | Phase 3 | Pending |
| WOO-03 | Phase 3 | Pending |
| WOO-04 | Phase 3 | Pending |
| WOO-05 | Phase 3 | Pending |
| WOO-06 | Phase 3 | Pending |
| WOO-07 | Phase 4 | Pending |
| WOO-08 | Phase 4 | Pending |
| WOO-09 | Phase 4 | Pending |
| UX-01 | Phase 5 | Pending |
| UX-02 | Phase 5 | Pending |
| UX-03 | Phase 5 | Pending |
| UX-04 | Phase 5 | Pending |
| UX-05 | Phase 5 | Pending |
| PERF-01 | Phase 6 | Pending |
| PERF-02 | Phase 6 | Pending |
| PERF-03 | Phase 6 | Pending |
| PERF-04 | Phase 6 | Pending |
| PERF-05 | Phase 6 | Pending |
| PERF-06 | Phase 6 | Pending |
| PERF-07 | Phase 6 | Pending |
| PLUG-01 | Phase 6 | Pending |
| PLUG-02 | Phase 6 | Pending |
| PLUG-03 | Phase 6 | Pending |
| PLUG-04 | Phase 6 | Pending |

**Coverage:**
- v1 requirements: 48 total
- Mapped to phases: 48 (100%)
- Unmapped: 0

**Note:** MIG-01 and MIG-02 (URL preservation) span multiple phases as they apply to both content URLs (Phase 2) and product URLs (Phase 3).

---
*Requirements defined: 2026-02-11*
*Last updated: 2026-02-11 after roadmap creation*
