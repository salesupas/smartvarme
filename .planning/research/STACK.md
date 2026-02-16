# Stack Research

**Domain:** WordPress/WooCommerce E-commerce Site Modernization
**Researched:** 2026-02-11
**Confidence:** HIGH

## Recommended Stack

### Core Technologies

| Technology | Version | Purpose | Why Recommended |
|------------|---------|---------|-----------------|
| WordPress | 6.8+ | CMS & Content Management | Latest version with full PHP 8.3 support, native performance enhancements (Interactivity API, automatic image sizing, predictive page loading), and mature FSE/block editor capabilities. Version 6.8+ includes Core Web Vitals optimizations built-in. |
| PHP | 8.3 or 8.4 | Server-side language | PHP 8.3 is officially recommended by WordPress.org (reached 10% adoption threshold in July 2025). PHP 8.4 offers longer support window (until 2028) with beta support in WP 6.8+. Provides significant performance gains over PHP 7.x. Avoid PHP 8.2 and older as they approach EOL. |
| MariaDB | 10.11 LTS | Database | WordPress officially requires 10.6+ but recommends 10.11 LTS for long-term support until 2028. Better performance than legacy MySQL 5.x (37% of WP sites still run EOL database versions). Alternative: MySQL 8.0 or 8.4 LTS. |
| WooCommerce | 10.x+ with HPOS | E-commerce platform | WooCommerce 10.x defaults to High Performance Order Storage (HPOS), delivering 5x faster order creation, 40x faster backend filtering, and 1.5x faster checkouts compared to legacy post-based storage. Critical for performance at scale. |
| Node.js | 20.x LTS | Build tools & block development | WordPress Core now requires Node 20.x+ (EOL April 2026). Active LTS required for @wordpress/scripts compatibility. Enables modern block development workflow. |

### Supporting Libraries

| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| @wordpress/scripts | Latest (29.x+) | Build toolchain for blocks/themes | Essential for custom block development. Zero-config webpack, Babel, ESLint, PostCSS bundler. Replaces manual webpack configuration with WordPress-optimized defaults. |
| Performance Lab | 4.0+ | Performance optimization plugin | Official WordPress Performance Team plugin. Testing ground for features entering core. Enables Image Prioritizer, Modern Image Formats (WebP), Web Worker Offloading, Enhanced Responsive Images, and Speculative Loading. Use for cutting-edge performance features. |
| WP Rocket | 3.16+ | Caching & optimization | Premium caching plugin ($59/year). Best overall Core Web Vitals results due to automatic Critical CSS generation and delayed JavaScript execution. Works on any hosting. Use when not on LiteSpeed hosting. Alternative: LiteSpeed Cache (free, but LiteSpeed server required). |
| ShortPixel | Latest | Image optimization | Best-in-class JPEG compression (54% reduction vs 20-40% competitors). Automatic WebP/AVIF conversion with fallbacks. Built-in global CDN. Free tier: 100 images/month. Superior to Imagify, Smush, EWWW for compression quality. |
| Cloudflare | Free/Pro | CDN & Security | Industry-standard CDN with global edge network. Free tier includes DDoS protection, SSL, and basic caching. Can combine with BunnyCDN for static asset delivery if needed. Alternative: BunnyCDN (cheaper, faster for pure CDN use). |

### Development Tools

| Tool | Purpose | Notes |
|------|---------|-------|
| @wordpress/env | Local WordPress environment | Docker-based local dev environment. Zero-config WordPress instances for testing. Requires Docker Desktop. Alternative: LocalWP, DDEV. |
| @wordpress/create-block | Block scaffolding | CLI tool for generating custom block boilerplate with build pipeline configured. Use for starting new custom blocks. |
| npm/npx | Package management | Automatically installed with Node.js. npm for dependencies, npx for running @wordpress CLI tools. |
| wp-cli | WordPress command-line | Server-side WordPress automation. Essential for HPOS migration, plugin updates, database operations. Pre-installed on quality managed hosting. |

### WooCommerce-Specific

| Component | Technology | Purpose | Notes |
|-----------|------------|---------|-------|
| Order Storage | HPOS (High Performance Order Storage) | Custom database tables for orders | Enabled by default in WooCommerce 10.x. Migrate existing stores via compatibility mode (sync both tables during transition). Check plugin compatibility before full migration. |
| REST API | WooCommerce REST API v3 | Headless/API integrations | Use for custom frontends or mobile apps. Alternative: WPGraphQL for WooCommerce if building React/Next.js frontend. |

## Installation

```bash
# Core WordPress (via hosting or local)
# Download from wordpress.org or use @wordpress/env

# Development environment setup
npm install -g @wordpress/env
npx @wordpress/env start

# Block development tools
npm install --save-dev @wordpress/scripts
npm install -g @wordpress/create-block

# Create new custom block
npx @wordpress/create-block my-custom-block

# Build block for production
npm run build
```

## Alternatives Considered

| Recommended | Alternative | When to Use Alternative |
|-------------|-------------|-------------------------|
| WP Rocket | LiteSpeed Cache | Only if hosting uses LiteSpeed server. LiteSpeed Cache is 10-15% faster on LiteSpeed hosts and free, but completely incompatible with Apache/Nginx hosting. |
| ShortPixel | Imagify | If already using WP Rocket (same company). Imagify has unlimited plan at $9.99/month vs ShortPixel's credit system. Imagify compression quality slightly lower. |
| Cloudflare | BunnyCDN | If you need pure CDN without DNS/security management. BunnyCDN is cheaper ($1/TB vs Cloudflare's $20-200/month paid tiers) and simpler for static asset delivery. Can use both together. |
| @wordpress/scripts | Custom Webpack | Only if you need highly specialized build configurations not supported by wp-scripts. Adds maintenance burden as you must keep webpack/Babel configs updated manually. |
| Block Theme (FSE) | Classic Theme | Only if you have complex custom PHP templates that can't be converted to blocks. 68% of WordPress professionals now prefer FSE themes. Classic themes receive less WordPress core development attention in 2025+. |
| Native Blocks | Page Builders (Elementor, Divi) | Only for simple sites where performance isn't critical. Page builders add 0.18+ seconds load time and 32+ MB page weight. Consider Beaver Builder if page builder required (lighter than Elementor/Divi). |

## What NOT to Use

| Avoid | Why | Use Instead |
|-------|-----|-------------|
| PHP 7.x or older | Reached End of Life (EOL). No security updates. WordPress 6.8+ requires 7.2.24 minimum but strongly recommends 8.3+. Exposes site to vulnerabilities. | PHP 8.3 or 8.4 |
| MySQL 5.x or MariaDB <10.6 | End of Life. 37% of WordPress sites run EOL database versions. Security risk and performance limitations. | MariaDB 10.11 LTS or MySQL 8.0/8.4 |
| Legacy WooCommerce order storage (wp_posts) | 5x slower order creation, 40x slower filtering. Bottleneck for stores with 50k+ orders. WooCommerce moving away from this architecture. | HPOS (High Performance Order Storage) |
| 25+ plugins | Original site pain point. Each plugin adds HTTP requests, database queries, and potential conflicts. Modern WordPress FSE + Performance Lab replaces many single-purpose plugins. | Consolidate to <15 essential plugins. Use native blocks instead of plugin-based features where possible. |
| Elementor/Divi for performance-critical sites | Adds significant page weight (32+ MB) and load time (0.18+ seconds). Makes Core Web Vitals compliance difficult. Modern block editor offers similar capabilities with better performance. | Native WordPress Block Editor (Gutenberg) with custom blocks or FSE theme |
| Old caching plugins (W3 Total Cache, WP Super Cache) | Outdated codebases, complex configuration, fewer Core Web Vitals optimizations. Industry has moved to modern solutions with automatic optimization. | WP Rocket or LiteSpeed Cache |
| GD library for images | Legacy PHP image processor. Slower than ImageMagick for WebP/AVIF conversion. Lower compression quality. | ImageMagick (most quality hosts have this enabled) |
| Theme builders (Oxygen, Bricks) | While newer than Elementor/Divi, still adds abstraction layer. Block themes with FSE are WordPress's future. Theme builders lock you into proprietary systems. | Block Theme + Full Site Editing (FSE) |

## Stack Patterns by Variant

### For Maximum Performance (Core Web Vitals Critical)
- PHP 8.4 (fastest available)
- MariaDB 10.11 LTS
- Block Theme (Twenty Twenty-Five child theme or custom FSE theme)
- WP Rocket + Cloudflare CDN
- ShortPixel with WebP/AVIF enabled
- Performance Lab plugin with all modules enabled
- HPOS for WooCommerce
- Target: <10 total plugins

**Rationale:** Native WordPress features + minimal abstraction layers = fastest possible site. Block themes generate cleaner, smaller code than classic themes or page builders.

### For Content Editor Experience
- Block Theme with custom blocks for FAQ/blog content
- ACF (Advanced Custom Fields) Pro for complex custom fields (if needed)
- Gutenberg editor with custom block patterns
- Performance Lab → Enhanced Responsive Images for automatic image optimization in editor

**Rationale:** Modern block editor offers intuitive drag-and-drop editing without page builders. Custom blocks provide brand-consistent components editors can't break. Addresses "hard to edit content" pain point.

### For Existing Site Migration (Incremental Rollout)
**Phase 1 - Infrastructure:**
- Upgrade PHP to 8.3+
- Upgrade MariaDB to 10.11
- Install WP Rocket + configure caching
- Install ShortPixel, bulk optimize existing images
- Install Performance Lab plugin

**Phase 2 - WooCommerce:**
- Update WooCommerce to 10.x
- Enable HPOS compatibility mode (sync both tables)
- Test checkout/order processing on staging
- Switch to HPOS-only storage
- Verify all plugins compatible with HPOS

**Phase 3 - Content System:**
- Install block theme (child of Twenty Twenty-Five)
- Develop custom blocks for FAQ/blog layouts
- Migrate FAQ/blog content to block editor
- Test editing experience with client
- Preserve existing URLs (SEO requirement)

**Rationale:** Minimizes risk. Infrastructure upgrades immediately improve performance. WooCommerce HPOS migration separate from content migration. Can roll back HPOS if issues arise before content migration begins.

### For Headless/API-Driven Architecture (Future Consideration)
- WordPress backend (WP REST API or WPGraphQL)
- Next.js or Astro frontend
- Cloudflare for CDN + edge caching
- WPGraphQL for efficient data queries

**Rationale:** Only pursue if you need mobile app, multiple frontend experiences, or extreme performance. Adds significant complexity. WPGraphQL becoming canonical plugin on WordPress.org in 2025, ensuring long-term support. Not recommended for initial modernization—consider post-migration if needed.

## Version Compatibility

| Package A | Compatible With | Notes |
|-----------|-----------------|-------|
| WordPress 6.8+ | PHP 8.3 (full support) | PHP 8.4 beta support. PHP 8.5 beta support coming in WP 6.9 (December 2025). |
| WordPress 6.8+ | MariaDB 10.6+ or MySQL 8.0+ | Tested with MariaDB 10.11 LTS and 11.4, MySQL 8.0 LTS and 8.4 |
| WooCommerce 10.x | WordPress 6.6+ | HPOS stable. Requires WordPress 6.6 minimum for full feature support. |
| @wordpress/scripts 29.x | Node.js 20.x+ | Requires Active LTS. Incompatible with Node <20. npm 10.x automatically included. |
| WP Rocket | Any server (Apache, Nginx, LiteSpeed) | Server-agnostic. Works universally. |
| LiteSpeed Cache | LiteSpeed server ONLY | Server-level caching requires LiteSpeed web server. Non-functional on Apache/Nginx. |
| Performance Lab | WordPress 6.4+ | Requires WordPress 6.4 minimum. Best with 6.8+ for all features. |
| ShortPixel | WordPress 5.3+ | Wide compatibility. Works with all major caching plugins. |

## Sources

### Official Documentation (HIGH confidence)
- [WordPress.org Requirements](https://wordpress.org/about/requirements/) — PHP 8.3, MariaDB 10.6+/MySQL 8.0+ official requirements
- [WordPress Developer Handbook - @wordpress/scripts](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/) — Build tools documentation
- [WordPress Developer Handbook - Node.js Environment](https://developer.wordpress.org/block-editor/getting-started/devenv/nodejs-development-environment/) — Node.js requirements
- [WooCommerce Developer Docs - HPOS](https://developer.woocommerce.com/docs/features/high-performance-order-storage/) — HPOS official documentation
- [WordPress.org Performance Lab Plugin](https://wordpress.org/plugins/performance-lab/) — Version 4.0.1, official features
- [Make WordPress Core - PHP 8 Support](https://make.wordpress.org/core/2025/04/09/php-8-support-clarification/) — PHP compatibility policy

### Verified Web Sources (HIGH-MEDIUM confidence)
- [15 Proven WordPress Performance Optimization Tips That Actually Work in 2025](https://oddjar.com/15-proven-wordpress-performance-optimization-tips-that-actually-work-in-2025/) — Performance best practices, WebP/AVIF adoption
- [Best WordPress Tech Stack for 2025](https://www.knihter.com/insights/wordpress/choosing-the-right-tech-stack-for-wordpress-in-2025-trends-and-considerations/) — Modern hosting and architecture
- [Kinsta - WordPress Agency Tech Stack](https://kinsta.com/blog/wordpress-agency-tech-stack/) — Professional development stack recommendations
- [2025 WooCommerce Optimization Guide](https://www.inspry.com/woocommerce-optimization-guide/) — Caching, image optimization, CDN strategies
- [WooCommerce Developer Blog - Performance at Scale](https://developer.woocommerce.com/2025/10/01/improving-woocommerce-performance-at-scale/) — HPOS performance gains (5x faster orders, 1.5x faster checkouts)
- [A Developer's Guide: The Future of WordPress Gutenberg Block Editor](https://webdevstudios.com/2025/11/25/a-developers-guide-the-future-of-the-wordpress-gutenberg-block-editor/) — Block development trends
- [Kinsta - WordPress Web Development Trends for 2025](https://kinsta.com/blog/web-development-trends/) — Lightweight builds, fewer plugins, block-first approach (68% adoption)
- [HPOS in WooCommerce 2025: Should You Switch?](https://thrivewp.com/woocommerce-hpos-2025-guide/) — 80-90% faster performance for 50k+ order stores, migration considerations
- [WordPress Performance Lab Plugin: Essential Features](https://supersoju.com/blog/2025/10/10/wordpress-performance-lab-plugin-essential-performance-features-you-should-be-using/) — Performance Lab module details
- [WordPress and PHP Compatibility Guide](https://www.mindpathtech.com/blog/wordpress-php-compatibility-guide/) — PHP 8.3/8.4 compatibility, EOL dates
- [LiteSpeed Cache VS WP Rocket Comparison](https://blogvault.net/litespeed-cache-vs-wp-rocket/) — Server requirements, performance differences (10-15% faster on LiteSpeed)
- [WordPress Caching Plugins 2025: Speed Test Results](https://oddjar.com/wordpress-caching-plugins-2025-performance-comparison/) — WP Rocket best Core Web Vitals due to Critical CSS
- [Cloudflare vs BunnyCDN for WordPress](https://www.dchost.com/blog/en/cloudflare-vs-bunnycdn-vs-cloudfront-best-cdn-choice-for-wordpress-and-woocommerce/) — CDN comparison, combining strategies
- [WordPress Image Optimization Plugins 2025](https://oddjar.com/wordpress-image-optimization-plugins-2025-comparison/) — ShortPixel 54% compression vs 20-40% competitors, AVIF/WebP support
- [WordPress Full Site Editing Guide](https://olliewp.com/wordpress-full-site-editing/) — FSE benefits, block theme architecture
- [Benefits of WordPress Block Themes in 2025](https://feltmedia.com/articles/benefits-of-wordpress-block-themes-in-2025/) — Performance gains, cleaner code
- [WordPress Page Builders 2025: Elementor vs Divi vs Beaver Builder](https://oddjar.com/wordpress-page-builders-2025-comparison/) — Page builder performance impact (0.18s delay, 32MB weight)
- [Understanding WPGraphQL and REST API](https://kinsta.com/blog/wpgraphql-vs-wp-rest-api/) — Headless WordPress architecture patterns
- [WordPress Headless in 2025](https://www.augustinfotech.com/blogs/headless-wordpress-in-2025-benefits-setup-and-use-cases/) — WPGraphQL canonical plugin status

### Community Sources (MEDIUM confidence)
- [Make WordPress Core - Node.js/npm Updates](https://make.wordpress.org/core/2023/12/20/updating-wordpress-to-use-more-modern-versions-of-node-js-npm-2/) — Node 20.x requirement rationale
- [GitHub - WordPress/performance](https://github.com/WordPress/performance) — Performance Lab source repository
- [rtCAMP Handbook - Build Tools](https://rtcamp.com/handbook/developing-for-block-editor-and-site-editor/build-tools-and-optimizations/) — @wordpress/scripts advanced usage

---
*Stack research for: Smartvarme WordPress/WooCommerce Modernization*
*Researched: 2026-02-11*
