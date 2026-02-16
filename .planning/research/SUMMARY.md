# Project Research Summary

**Project:** Smartvarme WordPress/WooCommerce Modernization
**Domain:** E-commerce Content Management System
**Researched:** 2026-02-11
**Confidence:** HIGH

## Executive Summary

Smartvarme is a WordPress/WooCommerce e-commerce site requiring modernization from a legacy setup with 25+ plugins and poor performance. Expert recommendations converge on a **block-first, performance-focused, lean-plugin architecture** using WordPress 6.8+, PHP 8.3+, and WooCommerce 10.x with HPOS (High Performance Order Storage). The primary value proposition is achieving Core Web Vitals compliance (LCP < 2.5s, INP < 200ms, CLS < 0.1) while making content editing dramatically easier through custom Gutenberg blocks and block patterns.

The recommended approach is **incremental rollout by component type**: start with content-only pages to establish the block theme foundation and performance baseline, then migrate product display (view-only, no transaction risk), followed by cart/checkout (highest risk, requires A/B testing), and finally custom features/integrations. This phased approach minimizes revenue risk and allows validation at each stage. Critical success factors include pre-migration database cleanup (autoloaded data must be < 800KB), comprehensive URL preservation (all product variations, paginated pages, not just main pages), and cache warming before rollout (avoiding cold-cache user experience).

Key risks center on **data migration complexity** (serialized data corruption, WooCommerce custom field loss, URL structure preservation) and **performance regression** (unwarmed cache, TTFB degradation, excessive plugin loading). Mitigation requires serialization-aware migration tools (WP-CLI search-replace, not SQL find-replace), pre-launch database optimization, automated redirect testing for all URLs, and cache preloading before user traffic hits the new system. The research indicates this is a well-documented modernization pattern with established tooling, making execution risk LOW if best practices are followed systematically.

## Key Findings

### Recommended Stack

The modern WordPress/WooCommerce stack in 2026 centers on **native WordPress features over third-party abstractions**. WordPress 6.8+ with PHP 8.3/8.4 provides performance gains and native features (accordion blocks, command palette, improved FSE) that previously required plugins. WooCommerce 10.x defaults to HPOS (High Performance Order Storage), delivering 5x faster order creation and 40x faster filtering versus legacy post-based storage—critical for performance at scale.

**Core technologies:**
- **WordPress 6.8+ on PHP 8.3/8.4**: Latest stable with full PHP 8.3 support, native performance enhancements, and Core Web Vitals optimizations built-in
- **WooCommerce 10.x with HPOS**: High Performance Order Storage delivers 5x faster order creation, 40x faster backend filtering, 1.5x faster checkouts
- **MariaDB 10.11 LTS**: Long-term support until 2028, officially recommended for WordPress, 37% performance gain over legacy MySQL 5.x still used by many sites
- **Block Theme with FSE**: WordPress Full Site Editing generates cleaner code than page builders, provides visual editing, and enables design consistency via theme.json
- **@wordpress/scripts**: Zero-config build toolchain for custom block development, replaces manual webpack configuration with WordPress-optimized defaults
- **WP Rocket + ShortPixel + Cloudflare**: Performance stack for caching (Critical CSS generation), image optimization (54% compression vs 20-40% competitors), and CDN delivery

**Critical version dependencies:**
- PHP 8.3 minimum (8.4 preferred for longer support window through 2028)
- Node.js 20.x LTS required for @wordpress/scripts compatibility
- MariaDB 10.6+ required by WordPress, 10.11 LTS recommended

**What NOT to use:**
- PHP 7.x or older (end of life, security risk)
- Page builders (Elementor/Divi) for performance-critical sites (adds 500KB+ and 0.18s+ load time)
- 25+ plugins (target: under 15 by consolidating functionality)
- Legacy WooCommerce post-based order storage (migrate to HPOS)

### Expected Features

The feature landscape divides into **table stakes** (users expect these to exist), **differentiators** (competitive advantage), and **anti-features** (commonly requested but problematic).

**Must have (table stakes):**
- **Gutenberg Block Editor with block patterns**: Standard WordPress editing since 2019, 60%+ adoption, enables fast content creation with consistent design
- **Native Accordion Block for FAQs**: WordPress 6.9+ includes built-in accordion, no plugin needed
- **Reusable/Synced Blocks**: Essential for CTAs, social buttons, disclaimers that update everywhere automatically
- **Image optimization pipeline**: Automatic WebP/AVIF conversion on upload with compression (addresses #1 WordPress performance killer)
- **FAQ Schema Markup**: Automatic structured data for Google rich snippets (SEO competitive advantage)
- **Page caching + lazy loading**: Baseline performance requirements (LCP < 2.5s target)
- **Mobile-responsive preview**: Built into block editor, prevents mobile UX disasters

**Should have (competitive):**
- **Custom Gutenberg blocks**: Purpose-built blocks for product features, comparisons, energy savings calculators specific to heating systems—dramatically improves editor UX
- **Global Styles System**: Pre-defined color palettes, typography, spacing through theme.json—prevents non-technical editors from breaking design
- **Locked Block Patterns**: Structure locked but content editable—maintains design consistency with multiple editors
- **Asset Loading Control**: Disable unused CSS/JS per page type (can save 200-500KB per page)
- **Object Caching (Redis/Memcached)**: Server-level database query caching, massive performance gain for product pages
- **CDN Integration**: Cloudflare or BunnyCDN for global static asset delivery

**Defer (v2+):**
- **Full Site Editing everywhere**: Still evolving, learning curve, plugin incompatibilities—use selectively where it adds value
- **Headless WordPress architecture**: High complexity, only justified if need mobile app or multi-channel delivery
- **Multi-language content (WPML/Polylang)**: Only if international expansion confirmed
- **AI-powered content assistance**: WordPress 6.9+ experimental features—let others beta test
- **Real-time content analytics dashboard**: Focus on publishing great content first, then measure

**Anti-features (avoid):**
- **Heavy page builders (Elementor/Divi)**: Vendor lock-in, 500KB+ JavaScript loads, makes Core Web Vitals compliance difficult—use FSE + custom blocks instead
- **Plugin for every feature**: Average site has 20-30 plugins, only 10-15 needed—consolidate with multipurpose plugins or code snippets
- **Custom CSS on individual blocks**: Creates management nightmare, inconsistent design—use block style variations + global styles instead
- **Loading all plugin assets globally**: Wastes bandwidth, checkout doesn't need homepage hero script—use conditional loading per page type
- **Non-optimized image uploads**: Oversized images kill LCP—automatic WebP/AVIF conversion + compression on upload

### Architecture Approach

WordPress/WooCommerce modernization follows a **block-based component architecture** with clear separation of concerns: theme handles presentation (layout, design, visual elements), plugin contains business logic (custom features, integrations, site-specific functionality), and custom Gutenberg blocks bridge the two (reusable UI components with domain-specific logic).

**Major components:**
1. **Block Theme (FSE-enabled)**: Visual presentation layer using HTML templates (not PHP), theme.json for design tokens, pattern library for reusable layouts. Generates cleaner code than classic themes, enables visual editing in Site Editor, loads only styles for rendered blocks (performance win).

2. **Custom Plugin (smartvarme-core)**: Business logic container persisting across theme changes. Includes WooCommerce customizations via hooks/filters, REST API endpoints for custom integrations, ERP/shipping provider connections, and domain-specific functionality. Never put business logic in theme—switching themes should only affect appearance.

3. **Custom Gutenberg Blocks**: Self-contained UI components (product grids, hero sections, FAQ accordions, energy calculators) using React-based block development with @wordpress/scripts build pipeline. Each block has its own JavaScript, styles, and logic. Maximum reusability, editor-friendly, future-proof.

4. **Caching Strategy (multi-layer)**: Page cache (WP Rocket or server-level), object cache (Redis/Memcached for database queries), browser cache (static assets with long TTL), and CDN cache (edge delivery). WooCommerce-aware configuration excludes cart/checkout/account pages but caches product pages and categories.

5. **Asset Build System**: @wordpress/scripts (webpack-based) for development, compiling modern JavaScript (ES6+) and SCSS, with code splitting and minification. Consider Vite migration in optimization phase (2.6x faster builds, 19% smaller bundles).

**Key architectural patterns:**
- **Component-Based Block Development**: Treat each UI element as self-contained, reusable block
- **Hooks & Filters Over Core Modification**: Never modify WordPress/WooCommerce core files, always extend via hooks
- **Conditional Asset Loading**: Load CSS/JS only on pages where needed, not globally
- **Block Templates with Template Hierarchy**: Use HTML block templates for visual editing, PHP templates only for complex logic
- **REST API for Integrations**: Use WordPress REST API for custom endpoints, not admin-ajax

### Critical Pitfalls

Research identified six **critical pitfalls** that can derail WordPress/WooCommerce modernization, plus numerous technical debt patterns and integration gotchas.

1. **Serialized Data Corruption During URL/Domain Changes**: WordPress stores complex data (arrays, objects) as serialized strings with character counts that must match exactly. Simple SQL find-replace corrupts serialization, breaking widgets, theme options, and plugin settings. **Prevention**: Never use simple SQL find-replace—use serialization-aware tools (WP-CLI search-replace, Better Search Replace plugin, WP Migrate DB Pro). Test in staging first.

2. **Autoloaded Data Bloat Killing Performance**: wp_options table stores site-wide settings WordPress loads on every page request. Plugins/themes often autoload everything, accumulating 3-10+ MB, causing slow TTFB, 502 errors when using object cache (1MB buffer limit). **Prevention**: Audit wp_options before migration, target < 800KB autoloaded data, remove options from uninstalled plugins, disable object caching if autoloaded data > 800KB until cleaned.

3. **Plugin Reduction Without Functionality Verification**: Teams reduce from 25+ to 10-15 plugins but fail to map every feature removed plugins provided. Obscure functionality (reading time, accordion blocks, custom post type UI) discovered missing after launch. **Prevention**: Create complete feature inventory before plugin removal, test each plugin in isolation, map removed functionality to replacement strategy (core feature, custom code, different plugin), involve actual users in verification.

4. **URL Preservation Testing Limited to Homepage/Main Pages**: Teams test main pages but miss thousands of auto-generated URLs (product variations, paginated archives, tag combinations, WooCommerce account pages, RSS feeds). Google Search Console shows hundreds of 404s weeks after launch, destroying SEO. **Prevention**: Export complete URL list from current site using Screaming Frog or server access logs (not just XML sitemap), create automated redirect testing script, verify WooCommerce-specific URLs (variations, filters, pagination), monitor GSC for 3-6 months post-launch.

5. **Incremental Rollout Without Cache/CDN Warming Strategy**: First users in rollout experience slow, uncached pages while cache builds organically. Real users have worse experience than old site even though testing showed good performance. **Prevention**: Pre-build cache before rollout begins using cache warming tools, test "cold cache" performance (clear all cache, verify acceptable first visitor experience), warm cache for all URLs not just main navigation, configure CDN to aggressively cache static assets.

6. **WooCommerce Data Migration Missing Custom Fields and Metadata**: Product titles/descriptions/prices migrate successfully, but custom fields (shipping dimensions, manufacturer data, B2B pricing, product badges, variation metadata) don't migrate or migrate incorrectly. Checkout breaks because expected metadata missing. **Prevention**: Inventory all product metadata before migration using WP All Export, test with most complex variable product (50+ variations), use WooCommerce-aware migration tools (WP All Import Pro with WooCommerce Add-On), verify product relationships (categories, cross-sells, upsells).

**Additional critical patterns:**
- **Technical debt**: Never combine CSS/JS files (outdated HTTP/1.1 optimization, breaks HTTP/3 multiplexing), never skip database backup before migration, never use generic MySQL export/import (corrupts serialized data)
- **Integration gotchas**: Verify webhook URLs update for payment gateways, configure SMTP before migration (test transactional emails), warm cache before enabling CDN, audit autoloaded data before enabling object cache
- **Performance traps**: Don't pre-build cache leads to terrible first visitor experience, TTFB must be < 600ms (requires hosting/optimization), lazy load only below-the-fold content (exclude hero/product images above fold)
- **Security mistakes**: Never commit wp-config.php to version control, update plugin licenses after domain migration, verify SSL across entire site (mixed content warnings), password-protect and noindex staging environments

## Implications for Roadmap

Based on research findings, the recommended approach is **6-phase incremental rollout** ordered by dependency and risk level. Content system first (establishes foundation, lowest risk), product display second (view-only, no transactions), cart/checkout third (highest risk, requires A/B testing), user accounts fourth (depends on checkout working), custom features fifth (site-specific), and optimization sixth (clean up tech debt).

### Phase 1: Foundation & Content System (Weeks 1-3)

**Rationale:** Content pages have no e-commerce dependencies, represent lowest risk, and establish the block theme foundation and performance baseline. Database cleanup and migration toolchain must be established before any content migration begins to avoid serialized data corruption and autoloaded data bloat pitfalls.

**Delivers:**
- Block theme skeleton with theme.json configuration and global styles
- Asset build system (@wordpress/scripts) with compilation pipeline
- Database optimization (autoloaded data < 800KB target)
- Migration toolchain (WP-CLI search-replace, staging environment setup)
- 5-10 block patterns for common layouts (hero, FAQ section, product feature, CTA, blog card)
- Reusable blocks for repeating elements (contact CTAs, disclaimers)
- Image optimization pipeline (automatic WebP/AVIF conversion + compression)
- Basic caching configuration (WP Rocket or server-level)

**Addresses features:**
- Gutenberg Block Editor (table stakes)
- Block Patterns (table stakes)
- Reusable Blocks (table stakes)
- Image optimization (table stakes)
- Global Styles System (differentiator)

**Avoids pitfalls:**
- Serialized data corruption (by establishing serialization-aware migration toolchain)
- Autoloaded data bloat (by auditing and cleaning wp_options table before migration)
- Plugin reduction missing features (by creating complete feature inventory)

**Research flags:** This phase follows well-documented patterns. Standard WordPress migration procedures. **Skip research-phase** for this phase—research is complete.

---

### Phase 2: Product Display System (Weeks 4-6)

**Rationale:** Products are view-only with no transaction risk, establishing the product presentation layer. WooCommerce HPOS migration happens here (compatibility mode first, then full migration), and custom product blocks provide domain-specific editing capabilities for heating system products.

**Delivers:**
- Custom product blocks (grid, featured product, comparison, energy calculator)
- Product page templates (single product, archive, category)
- WooCommerce block customizations via hooks/filters
- HPOS migration (compatibility mode → full HPOS)
- Product filtering and sorting functionality
- FAQ schema markup for product pages (SEO advantage)

**Uses stack elements:**
- WooCommerce 10.x with HPOS
- Custom Gutenberg block development (@wordpress/scripts)
- Conditional asset loading (product-specific scripts only on product pages)

**Implements architecture components:**
- Custom Plugin (smartvarme-core) with WooCommerce hooks
- Custom Gutenberg Blocks for product display

**Addresses features:**
- Custom Gutenberg Blocks (differentiator)
- FAQ Schema Markup (table stakes)
- Native Accordion Block for product FAQs (table stakes)

**Avoids pitfalls:**
- WooCommerce data migration missing custom fields (by inventorying all product metadata and testing with complex variable products)
- Plugin functionality missing (by verifying all WooCommerce customizations still work)

**Research flags:** HPOS migration is well-documented by WooCommerce. Custom block development follows standard Gutenberg patterns. **Skip research-phase** for this phase—research is complete.

---

### Phase 3: Cart & Checkout (Weeks 7-9)

**Rationale:** Highest risk area with direct revenue impact. Requires thorough testing and gradual rollout (A/B testing recommended). Cache configuration must exclude cart/checkout/account pages but maintain performance elsewhere. Payment gateway webhook verification critical.

**Delivers:**
- Custom cart block/template with WooCommerce integration
- Checkout flow optimization (fewer steps, clear progress indicators)
- Payment gateway integration verification (Stripe, PayPal webhook URLs, 3D Secure flow)
- Order confirmation and transactional email templates
- Custom checkout fields if needed (delivery instructions, company info)
- Cart abandonment tracking setup

**Uses stack elements:**
- WooCommerce REST API for cart updates
- AJAX for dynamic cart content (no full page reload)
- Cache exclusion rules for cart/checkout (WP Rocket configuration)

**Addresses features:**
- Standard WooCommerce checkout flow (table stakes)
- Email functionality (table stakes)

**Avoids pitfalls:**
- Incremental rollout without cache warming (by pre-building cache for all non-checkout pages before traffic hits)
- Cache misconfiguration breaking personalization (by excluding user-specific pages from cache)
- Payment gateway integration issues (by verifying webhook URLs update and testing failed payment scenarios)

**Research flags:** Checkout optimization could benefit from conversion rate research. **Consider research-phase** for checkout flow best practices specific to heating systems industry (B2B vs B2C considerations, quote request vs immediate purchase).

---

### Phase 4: User Accounts & My Account (Weeks 10-11)

**Rationale:** Depends on checkout working properly (users must complete orders before order history matters). Lower urgency than transaction flow. Cache configuration must handle logged-in user experience (most WooCommerce customers log in before purchase).

**Delivers:**
- Account pages templates (order history, address management, account dashboard)
- Order tracking and status updates
- Address management with validation
- Account dashboard blocks (recent orders, saved addresses, preferences)
- Logged-in user cache handling (fragment caching for personalized areas)

**Addresses features:**
- Standard WooCommerce account functionality (table stakes)

**Avoids pitfalls:**
- Cache configuration breaking personalization (by using AJAX for dynamic content, fragment caching for personalized sections)
- Not caching logged-in users (by implementing fragment caching—avoid serving slow uncached experience to customers)

**Research flags:** Standard WooCommerce account pages. **Skip research-phase** for this phase—well-documented patterns.

---

### Phase 5: Custom Features & Integrations (Weeks 12-14)

**Rationale:** Site-specific functionality can be rolled out independently once core e-commerce flow works. ERP integration, custom reporting, specialized product features specific to heating systems business.

**Delivers:**
- ERP integration (product sync, inventory updates, order export)
- Custom reporting dashboard (sales by product category, seasonal trends)
- Advanced filtering (by heating capacity, efficiency rating, installation type)
- Specialized blocks (installation timeline, energy savings comparison, ROI calculator)
- Marketing integrations (email marketing, analytics, customer segmentation)

**Uses stack elements:**
- REST API for ERP integration (async product sync via webhooks)
- Custom Plugin architecture (all integrations in smartvarme-core plugin)
- WP-Cron for scheduled tasks (daily product sync, weekly reports)

**Addresses features:**
- Custom Gutenberg Blocks for domain-specific patterns (differentiator)
- Advanced Block Variations (differentiator)

**Avoids pitfalls:**
- Integration gotchas by verifying webhook endpoints, testing async workflows, monitoring cron job execution

**Research flags:** ERP integration specifics unknown. **Needs research-phase** during planning to understand ERP system API, data mapping requirements, sync frequency, error handling.

---

### Phase 6: Plugin Consolidation & Optimization (Weeks 15-16)

**Rationale:** Clean up tech debt after core functionality proven stable. Remove unnecessary plugins, consolidate functionality into custom plugin, final performance optimization pass. URL preservation monitoring ongoing (3-6 months).

**Delivers:**
- Plugin audit and removal (target: under 15 plugins remaining)
- Functionality consolidation into smartvarme-core plugin
- Database optimization (query optimization, transient cleanup, revision limits)
- Final caching configuration (verify 80%+ cache hit ratio)
- Asset loading optimization (critical CSS, deferred JavaScript, code splitting)
- Performance monitoring setup (Real User Monitoring for actual user metrics)

**Uses stack elements:**
- Asset Loading Control plugins (Asset CleanUp or Perfmatrics)
- Database optimization tools (WP-Optimize, Query Monitor)
- Performance monitoring (Google Search Console Core Web Vitals, GTmetrix)

**Addresses features:**
- Asset Loading Control (differentiator)
- Performance monitoring (ongoing)

**Avoids pitfalls:**
- Performance worse than old site (by systematic benchmarking and optimization)
- All-in-one performance plugin trap (by using targeted plugins for specific optimizations)
- URL preservation issues (by monitoring GSC 404 errors for 3-6 months post-launch)

**Research flags:** Performance optimization follows standard patterns. **Skip research-phase** for this phase—research is complete.

---

### Phase Ordering Rationale

**Dependency-driven order:**
- Foundation (Phase 1) must come first: establishes theme, build system, migration toolchain, and performance baseline
- Content before products: content pages are lowest risk, validate approach before e-commerce
- Products before cart/checkout: users must view products before purchasing, establishes product data layer
- Cart/checkout before accounts: users must complete orders before order history matters
- Custom features after core e-commerce: site-specific functionality depends on working e-commerce foundation
- Optimization last: clean up tech debt after core functionality proven stable

**Risk-based ordering:**
- Phase 1-2: Low risk (content and view-only products, no transactions)
- Phase 3: High risk (checkout has direct revenue impact, requires A/B testing)
- Phase 4-6: Medium risk (depends on Phase 3 working, but lower immediate business impact)

**Architecture-based grouping:**
- Phase 1: Presentation layer (theme, patterns, styles)
- Phase 2: Component layer (custom blocks, WooCommerce integration)
- Phase 3-4: Business logic layer (transactions, user accounts)
- Phase 5: Integration layer (ERP, marketing, custom features)
- Phase 6: Infrastructure layer (optimization, monitoring, cleanup)

**Pitfall avoidance:**
- Phase 1 addresses serialized data corruption, autoloaded data bloat, and plugin reduction verification before any migration
- Phase 2 addresses WooCommerce custom field migration with complete product metadata inventory
- Phase 3 addresses cache warming and payment integration before rollout
- All phases: URL preservation testing ongoing with 3-6 month monitoring window

### Research Flags

**Phases likely needing deeper research during planning:**
- **Phase 3 (Cart/Checkout)**: Conversion rate optimization research specific to heating systems industry—B2B vs B2C purchase patterns, quote request workflows vs immediate purchase, seasonal buying patterns
- **Phase 5 (Custom Features/Integrations)**: ERP system integration research—specific ERP API documentation, data mapping requirements, sync strategies, error handling patterns

**Phases with standard patterns (skip research-phase):**
- **Phase 1 (Foundation)**: Standard WordPress migration and theme development—well-documented, established tooling
- **Phase 2 (Product Display)**: Standard WooCommerce HPOS migration and custom block development—official WooCommerce documentation comprehensive
- **Phase 4 (User Accounts)**: Standard WooCommerce account pages—well-documented patterns
- **Phase 6 (Optimization)**: Standard WordPress performance optimization—extensive best practices documentation

## Confidence Assessment

| Area | Confidence | Notes |
|------|------------|-------|
| Stack | **HIGH** | WordPress 6.8+, PHP 8.3+, WooCommerce 10.x with HPOS are officially documented with clear version requirements. Performance benchmarks verified (5x faster orders with HPOS, 54% compression with ShortPixel). Official WordPress.org requirements, WooCommerce developer docs, and verified performance testing sources. |
| Features | **HIGH** | Feature landscape well-researched with official WordPress Developer Handbook, WooCommerce documentation, and Core Web Vitals optimization guides. Table stakes (block editor, accordion, reusable blocks) are core WordPress features. Differentiators (custom blocks, global styles, asset control) supported by developer documentation and performance case studies. |
| Architecture | **HIGH** | Block-based component architecture is standard WordPress development pattern documented in official WordPress Developer Handbook, WooCommerce Block Development docs, and theme development best practices. Build order follows established incremental migration patterns. Performance targets based on 2026 Google Core Web Vitals standards. |
| Pitfalls | **HIGH** | Critical pitfalls verified through multiple sources: official WordPress migration guides, WooCommerce migration documentation, SEO migration best practices, and WordPress performance optimization case studies. Serialized data corruption, autoloaded data bloat, and HPOS migration pitfalls extensively documented with specific prevention tools and warning signs. |

**Overall confidence:** **HIGH**

All four research areas achieved HIGH confidence through convergence of official documentation (WordPress.org, WooCommerce developer docs), verified performance testing, and established migration patterns. Stack recommendations align with official WordPress requirements. Feature landscape validated through WordPress Core updates (6.9 includes accordion block, command palette). Architecture patterns match official WordPress Developer Handbook. Pitfalls verified through migration case studies and official troubleshooting documentation.

### Gaps to Address

**Minor gaps requiring validation during implementation:**

1. **ERP System Integration Specifics**: Research covers general REST API integration patterns for ERP systems but lacks specifics about Smartvarme's actual ERP system. Need to identify ERP vendor, API documentation, authentication method, data sync requirements during Phase 5 planning. **How to handle**: Run `/gsd:research-phase` when planning Phase 5 with specific ERP system name and integration requirements.

2. **B2B vs B2C Purchase Patterns**: Research covers general WooCommerce checkout optimization but heating systems may have B2B requirements (quote requests, bulk ordering, contractor accounts) different from standard B2C e-commerce. **How to handle**: Validate during Phase 3 planning whether quote request workflow needed, or if standard immediate purchase sufficient. Consider `/gsd:research-phase` for checkout flow if B2B requirements confirmed.

3. **Hosting Environment Configuration**: Research recommends Redis/Memcached object caching and server-level optimizations (Varnish, NGINX FastCGI) but current hosting environment unknown. Performance targets assume quality managed hosting or VPS with control. **How to handle**: Audit current hosting capabilities during Phase 1. If shared hosting without Redis/Memcached, either upgrade hosting or adjust Phase 6 optimization expectations.

4. **Migration Downtime Window**: Research covers incremental rollout strategies but doesn't specify acceptable downtime window for DNS cutover or maintenance mode duration. **How to handle**: Confirm business constraints (can site be in maintenance mode for 1-2 hours during off-peak?) and whether blue-green deployment needed during Phase 1 planning.

5. **Seasonal Traffic Patterns**: Heating systems likely have strong seasonal patterns (peak in fall/winter preparation, low in summer) affecting optimal launch timing and traffic volume expectations for cache warming. **How to handle**: Request historical traffic data (Google Analytics) during Phase 1 to inform rollout timing and cache warming strategy.

**All gaps are minor and addressable during phase planning—no gaps prevent roadmap creation or affect overall approach.**

## Sources

### Primary Sources (HIGH Confidence)

**Official WordPress/WooCommerce Documentation:**
- [WordPress.org Requirements](https://wordpress.org/about/requirements/) — PHP 8.3, MariaDB 10.6+/MySQL 8.0+ official requirements
- [WordPress Developer Handbook - Block Editor](https://developer.wordpress.org/block-editor/) — Custom block development, @wordpress/scripts
- [WooCommerce Developer Docs - HPOS](https://developer.woocommerce.com/docs/features/high-performance-order-storage/) — High Performance Order Storage migration
- [WooCommerce Block Development](https://developer.woocommerce.com/docs/block-development/) — Block-based architecture patterns
- [WooCommerce Performance Optimization](https://developer.woocommerce.com/docs/best-practices/performance/performance-optimization/) — Caching, scaling, optimization
- [WordPress.org Performance Lab Plugin](https://wordpress.org/plugins/performance-lab/) — Official performance features, Core Web Vitals optimization

**WordPress Core/Community:**
- [Make WordPress Core - PHP 8 Support](https://make.wordpress.org/core/2025/04/09/php-8-support-clarification/) — PHP compatibility policy
- [WordPress Developer Blog: What's New for Developers (February 2026)](https://developer.wordpress.org/news/2026/02/whats-new-for-developers-february-2026/) — WordPress 6.9 features

### Secondary Sources (HIGH-MEDIUM Confidence)

**Performance and Optimization:**
- [WordPress Performance Optimization: The Ultimate 2026 Guide - Next3Offload](https://next3offload.com/blog/wordpress-performance-optimization/) — Comprehensive optimization techniques
- [15 Proven WordPress Performance Optimization Tips That Actually Work in 2025 - OddJar](https://oddjar.com/15-proven-wordpress-performance-optimization-tips-that-actually-work-in-2025/) — WebP/AVIF adoption, caching strategies
- [WP Rocket: Google Core Web Vitals for WordPress](https://wp-rocket.me/google-core-web-vitals-wordpress/) — Core Web Vitals optimization
- [WPBeginner: How to Optimize Core Web Vitals for WordPress](https://www.wpbeginner.com/wp-tutorials/how-to-optimize-core-web-vitals-for-wordpress-ultimate-guide/) — LCP, INP, CLS targets

**Migration and Pitfalls:**
- [SEO Migration Strategy: A Complete Guide for 2026 - Influize](https://www.influize.com/blog/seo-migration-strategy) — URL preservation, redirect testing
- [WooCommerce Website Migration Without Downtime or Data Loss - Pressable](https://pressable.com/blog/woocommerce-migration-avoid-downtime-and-data-loss/) — WooCommerce-specific migration
- [Search and Replace on WordPress Database and Dealing with Serialized Data - Managing WP](https://managingwp.io/2023/03/23/search-and-replace-on-a-wordpress-database-and-dealing-with-serialized-data/) — Serialization pitfalls
- [WordPress Database Optimization: Autoload and Table Bloat - DCHost](https://www.dchost.com/blog/en/wordpress-database-optimization-guide-wp_options-autoload-and-table-bloat/) — Autoloaded data optimization

**Architecture and Development:**
- [Kinsta: Building Custom Gutenberg Blocks](https://kinsta.com/blog/gutenberg-blocks/) — Custom block development
- [WordPress Block Themes vs Classic Themes - WPZOOM](https://www.wpzoom.com/blog/block-themes-vs-classic-themes/) — Block theme architecture
- [Component Architecture with WordPress and Gutenberg - Exemplifi](https://www.exemplifi.io/insights/component-architecture-with-wordpress-and-gutenberg/) — Component patterns

**Stack Recommendations:**
- [Best WordPress Tech Stack for 2025 - Knihter](https://www.knihter.com/insights/wordpress/choosing-the-right-tech-stack-for-wordpress-in-2025-trends-and-considerations/) — Modern hosting and architecture
- [WooCommerce Optimization Guide 2025 - Inspry](https://www.inspry.com/woocommerce-optimization-guide/) — Caching, image optimization, CDN
- [HPOS in WooCommerce 2025: Should You Switch? - ThriveWP](https://thrivewp.com/woocommerce-hpos-2025-guide/) — 80-90% faster performance for 50k+ order stores
- [WordPress Image Optimization Plugins 2025 - OddJar](https://oddjar.com/wordpress-image-optimization-plugins-2025-comparison/) — ShortPixel 54% compression benchmark

### Tertiary Sources (MEDIUM Confidence)

**Trends and Future Considerations:**
- [WordPress in 2026: Leading Platform for Scalable Content Marketing - CHEIT Group](https://www.cheitgroup.com/blog/wordpress-2026-scalable-content-marketing) — WordPress 2026 outlook
- [Headless WordPress: The Future of WooCommerce Development? - Convesio](https://convesio.com/guides/headless-wordpress-the-future-of-woocommerce-development/) — Headless architecture considerations
- [WooCommerce Trends of 2026 - Zetamatic](https://zetamatic.com/blog/2025/12/woocommerce-trends-of-2026/) — E-commerce trends

**Build Tools:**
- [Vite vs. Webpack: A Head-to-Head Comparison - Kinsta](https://kinsta.com/blog/vite-vs-webpack/) — Build tool comparison for Phase 6 optimization consideration

---

*Research completed: 2026-02-11*
*Ready for roadmap: YES*
*Recommended approach: 6-phase incremental rollout (content → products → checkout → accounts → custom features → optimization)*
*Next step: Roadmap creation with phase breakdowns, timelines, and validation criteria*
