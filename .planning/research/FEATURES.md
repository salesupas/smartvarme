# Feature Research: WordPress/WooCommerce Content System Modernization

**Domain:** WordPress content management (FAQ/blog editing)
**Researched:** 2026-02-11
**Confidence:** HIGH

## Feature Landscape

### Table Stakes (Users Expect These)

Features editors assume exist. Missing these = painful content editing experience.

| Feature | Why Expected | Complexity | Notes | Performance Impact |
|---------|--------------|------------|-------|-------------------|
| Gutenberg Block Editor | Standard WordPress editing since 2019, 60%+ adoption rate | LOW | Core WordPress feature, no plugins needed | Neutral - built into core |
| Reusable Blocks (Synced) | Essential for consistency across pages (CTAs, social buttons) | LOW | Built-in feature - changes update everywhere automatically | Positive - reduces duplication |
| Block Patterns (Unsynced) | Pre-built layouts for consistent design without structural lock-in | LOW | Speeds up page creation dramatically | Positive - templates prevent bloat |
| Accordion/Collapsible FAQ Block | Native collapsible sections for FAQ content with anchor support | LOW | WordPress 6.9+ includes native accordion block | Positive - reduces initial page load |
| Rich Text Editing | Inline formatting, links, basic styling within blocks | LOW | Built into core RichText component | Neutral |
| Media Library Management | Drag-drop upload, image editing, WebP/AVIF format support | LOW | Core feature, but needs optimization plugins | Depends - requires image optimization |
| Draft/Preview/Publish Workflow | Standard content lifecycle with revision history | LOW | Core WordPress functionality | Neutral |
| Block-Level Notes/Comments | Real-time collaboration comments on specific blocks | MEDIUM | WordPress 6.9+ feature - reduces email back-and-forth | Neutral - editing feature only |
| Mobile-Responsive Preview | View content on different screen sizes before publishing | LOW | Built into block editor | Neutral |
| Keyboard Shortcuts | Speed up editing with keyboard-first workflows | LOW | Built into Gutenberg (Ctrl+K/Cmd+K Command Palette) | Positive - faster editing |

### Differentiators (Competitive Advantage)

Features that make content editing truly easy and set the site apart.

| Feature | Value Proposition | Complexity | Notes | Performance Impact |
|---------|-------------------|------------|-------|-------------------|
| Custom Gutenberg Blocks | Purpose-built blocks for product features, testimonials, FAQs specific to heating systems | MEDIUM-HIGH | Requires React/JS development but dramatically improves editor UX | Positive - only load needed assets |
| Global Styles System | Pre-defined color palettes, typography, spacing - prevents styling inconsistencies | LOW-MEDIUM | WordPress FSE feature - empowers non-technical editors | Positive - enforces design system |
| Locked Block Patterns | Pre-built layouts where structure is locked but content editable | MEDIUM | Perfect for maintaining design consistency with multiple editors | Positive - prevents layout bloat |
| Content-Specific Block Variations | Different variations of same block (e.g., FAQ with/without icons, blog card layouts) | MEDIUM | Provides flexibility without overwhelming editors | Neutral with proper loading |
| FAQ Schema Markup (Automatic) | Auto-generates structured data for Google rich snippets | LOW-MEDIUM | SEO advantage - appears in search results | Positive - better organic traffic |
| Command Palette (Ctrl+K) | Jump anywhere, do anything without mouse - extended across entire admin | LOW | WordPress 6.9+ enhancement - power user feature | Neutral |
| Asset Loading Control | Disable specific CSS/JS on specific pages/post types | MEDIUM | Plugins like Asset CleanUp - can save 200-500KB per page | VERY POSITIVE - major performance gain |
| Block-Level Lazy Loading Strategy | Intelligent lazy loading that excludes above-fold content | MEDIUM | Critical for LCP scores - requires careful implementation | VERY POSITIVE if done correctly |
| Content Delivery Network (CDN) | Cloudflare/BunnyCDN for global static asset delivery | MEDIUM | Essential for international performance | VERY POSITIVE - reduces TTFB |
| Object Caching (Redis/Memcached) | Server-level database query caching | HIGH | Requires server configuration - massive performance gain | VERY POSITIVE - reduces DB load |

### Anti-Features (Commonly Requested, Often Problematic)

Features that seem good but hurt performance or complicate editing.

| Feature | Why Requested | Why Problematic | Alternative | Performance Impact |
|---------|---------------|-----------------|-------------|-------------------|
| Heavy Page Builder Plugins (Elementor/Divi) | "More design control" and visual editing | Bloated code, vendor lock-in, 500KB+ JavaScript loads | Use FSE + custom blocks + block patterns | VERY NEGATIVE - major bloat |
| Plugin for Every Feature | "Quick solution" mentality | Average site has 20-30 plugins, only 10-15 needed. Each adds HTTP requests | Consolidate with multipurpose plugins or code snippets | NEGATIVE - death by 1000 cuts |
| Custom CSS on Individual Blocks | Per-block styling flexibility | Creates management nightmare, inconsistent design, hard to maintain | Use block style variations + global styles | NEGATIVE - CSS bloat, specificity wars |
| Real-Time Auto-Save to Database | "Never lose work" | Excessive database writes, revision bloat | WordPress auto-save is sufficient (60s default) | NEGATIVE - database load |
| Massive Revision History | "Complete edit history" | Database bloat - every save creates new row | Limit revisions to 5-10, regular cleanup | NEGATIVE - slows queries |
| Loading All Plugin Assets Globally | "Easier to code" for developers | JavaScript/CSS loads on every page even when unused | Conditional asset loading per page/post type | VERY NEGATIVE - wasted bandwidth |
| Non-Optimized Image Uploads | "Editors shouldn't worry about images" | Oversized images are #1 WordPress performance killer | Automatic WebP/AVIF conversion + compression on upload | VERY NEGATIVE - kills LCP |
| JavaScript-Only Accordion Hiding | "Fancy animations" | Hidden content may not be crawled/indexed by Google | Use semantic HTML + progressive enhancement | NEGATIVE - SEO impact |
| Too Many Font Variations | "Brand requires it" | Each font file is HTTP request + render blocking | Limit to 2-3 font weights, use font-display: swap | NEGATIVE - delays text rendering |
| Full Site Editing Everywhere | "FSE is the future" | Still evolving, learning curve, plugin incompatibilities | Use FSE selectively where it adds value (templates, headers) | MIXED - good for structure, bad if misused |

## Feature Dependencies

```
Content Editing Foundation:
├── Gutenberg Block Editor (CORE)
│   ├──requires──> Block Patterns
│   ├──requires──> Reusable Blocks
│   └──enhances──> Custom Blocks
│
├── Global Styles System
│   ├──requires──> Block Theme (FSE-compatible)
│   └──prevents──> Per-Block Custom CSS chaos
│
└── Custom Gutenberg Blocks
    ├──requires──> React/JS development skills
    └──enables──> Domain-specific content patterns

Performance Foundation:
├── Image Optimization
│   ├──requires──> WebP/AVIF conversion
│   ├──requires──> Compression (TinyPNG/Smush)
│   └──enhances──> Lazy Loading
│
├── Asset Loading Control
│   ├──requires──> Plugin like Asset CleanUp or Perfmatricks
│   └──enables──> Page-specific CSS/JS loading
│
├── Caching Strategy
│   ├──requires──> Page Cache (server-level or WP Rocket)
│   ├──requires──> Object Cache (Redis/Memcached)
│   └──requires──> CDN (Cloudflare/BunnyCDN)
│
└── Lazy Loading
    ├──requires──> Image Optimization first
    └──conflicts with──> Lazy loading above-the-fold content (hurts LCP)

FAQ-Specific:
├── Accordion Block (Core WordPress 6.9+)
│   ├──enhances──> FAQ Schema Markup
│   └──enables──> Anchor linking to specific questions
│
└── FAQ Schema Markup
    ├──requires──> Semantic HTML structure
    └──enables──> Google rich snippets in search results
```

### Dependency Notes

- **Custom Blocks require Block Editor Foundation**: Can't build custom blocks without understanding Gutenberg's React-based architecture
- **Global Styles require Block Theme**: FSE global styles only work with block themes, not classic themes
- **Lazy Loading requires Image Optimization**: Don't lazy load unoptimized images - optimize first, then lazy load
- **Asset Loading Control prevents Plugin Bloat**: Each plugin disabled per page = 20-50KB saved
- **Object Caching requires Server Access**: Shared hosting often doesn't support Redis/Memcached
- **FAQ Schema conflicts with Pure JavaScript Accordions**: Google may not index JavaScript-hidden content properly

## MVP Definition

### Launch With (v1 - Content System Core)

Minimum viable features for editors to work efficiently with good performance.

- [x] **Gutenberg Block Editor** - Core editing foundation, no alternatives acceptable
- [x] **Block Patterns (3-5 common layouts)** - Header, FAQ section, product feature, CTA, blog post card
- [x] **Reusable Blocks** - For repeating elements (contact CTAs, social proof, disclaimers)
- [x] **Native Accordion Block** - For FAQ pages (WordPress 6.9+ built-in)
- [x] **Image Optimization Pipeline** - Automatic WebP conversion + compression on upload (Smush or Imagify)
- [x] **Page Caching** - Server-level or WP Rocket
- [x] **Lazy Loading (Selective)** - Built-in WordPress lazy loading, excluding above-fold images
- [x] **FAQ Schema Markup** - Yoast SEO or standalone FAQ Schema plugin
- [x] **Basic Global Styles** - Color palette, typography, spacing tokens for consistency

**Why these are essential:**
- Block editor + patterns = fast content creation without design chaos
- Image optimization + lazy loading = baseline performance (LCP under 2.5s)
- FAQ accordion + schema = SEO advantage in search results
- Global styles = non-technical editors can't break design

### Add After Validation (v1.x - Performance Optimization)

Features to add once core content editing is working smoothly.

- [ ] **Custom Gutenberg Blocks (2-3 domain-specific)** - Heating product comparison, installation timeline, energy savings calculator
- [ ] **Asset Loading Control** - Disable unused plugin CSS/JS per page (Asset CleanUp)
- [ ] **CDN Integration** - Cloudflare or BunnyCDN for global delivery
- [ ] **Object Caching (Redis)** - If hosting supports it, massive database performance gain
- [ ] **Advanced Block Variations** - Multiple visual styles for common blocks
- [ ] **Locked Block Patterns** - For templates shared with multiple editors
- [ ] **Command Palette Training** - Keyboard-first workflow for power users

**Triggers for adding:**
- Custom blocks: When editors create same content pattern 5+ times manually
- Asset control: When page weight exceeds 500KB on blog/FAQ pages
- CDN: When international traffic exceeds 20% of total
- Object caching: When database queries exceed 100ms on average page load
- Locked patterns: When non-technical editors accidentally break layouts

### Future Consideration (v2+ - Advanced Features)

Features to defer until content-market fit is established.

- [ ] **Full Site Editing (FSE) Templates** - Headers, footers, archive pages through block editor
- [ ] **Headless WordPress Architecture** - Decouple frontend (Next.js/React) from WordPress backend
- [ ] **AI-Powered Content Assistance** - WordPress 6.9+ experimental AI post summaries
- [ ] **Multi-Language Content (WPML/Polylang)** - If international expansion happens
- [ ] **Advanced Collaboration Workflows** - Editorial calendar, multi-step approval process
- [ ] **Real-Time Content Analytics Dashboard** - Which FAQs get most views, bounce rates per block

**Why defer:**
- FSE: Still evolving, learning curve, potential plugin conflicts - wait for maturity
- Headless: High complexity, only justified if need multi-channel delivery (apps, kiosks)
- AI: Experimental features in WordPress - let others beta test
- Multi-language: Only needed if expanding to Norwegian, Swedish markets
- Collaboration: Premature if only 1-2 content editors
- Analytics: Focus on publishing great content first, then measure

## Feature Prioritization Matrix

| Feature | User Value (Editor) | Implementation Cost | Performance Impact | Priority |
|---------|---------------------|---------------------|-------------------|----------|
| Gutenberg Block Editor | HIGH | FREE (core) | Neutral | P1 |
| Block Patterns (5 layouts) | HIGH | LOW (2-4 hours) | Positive | P1 |
| Reusable Blocks | HIGH | FREE (core) | Positive | P1 |
| Native Accordion Block | HIGH | FREE (core 6.9+) | Positive | P1 |
| Image Optimization | MEDIUM | LOW (plugin setup) | VERY POSITIVE | P1 |
| Page Caching | LOW (invisible) | LOW (plugin or server) | VERY POSITIVE | P1 |
| Lazy Loading Setup | LOW (invisible) | LOW (config) | POSITIVE | P1 |
| FAQ Schema Markup | MEDIUM | LOW (plugin) | Neutral (SEO gain) | P1 |
| Global Styles System | MEDIUM | MEDIUM (requires block theme) | Positive | P1 |
| Custom Gutenberg Blocks | VERY HIGH | HIGH (React dev) | Positive | P2 |
| Asset Loading Control | LOW (invisible) | MEDIUM (audit + config) | VERY POSITIVE | P2 |
| CDN Integration | LOW (invisible) | MEDIUM (setup + testing) | VERY POSITIVE | P2 |
| Object Caching (Redis) | LOW (invisible) | HIGH (server setup) | VERY POSITIVE | P2 |
| Advanced Block Variations | MEDIUM | MEDIUM (design + dev) | Neutral | P2 |
| Locked Block Patterns | MEDIUM | MEDIUM (design patterns) | Positive | P2 |
| Command Palette Training | LOW | LOW (documentation) | Neutral | P2 |
| Full Site Editing (FSE) | MEDIUM | HIGH (theme migration) | MIXED | P3 |
| Headless Architecture | LOW | VERY HIGH (rebuild) | VERY POSITIVE | P3 |
| AI Content Assistance | LOW | MEDIUM (experimental) | Neutral | P3 |
| Multi-Language | MEDIUM | HIGH (WPML + translation) | NEGATIVE | P3 |
| Advanced Collaboration | MEDIUM | HIGH (workflow plugins) | Neutral | P3 |

**Priority key:**
- **P1**: Must have for launch - enables core editing workflow and baseline performance
- **P2**: Should have - adds efficiency or significant performance gains after core is stable
- **P3**: Nice to have - future consideration based on growth and user feedback

## Competitor Feature Analysis

| Feature | GeneratePress (Modern WP) | Divi/Elementor (Traditional) | Our Approach |
|---------|---------------------------|------------------------------|--------------|
| Content Editing | Gutenberg + block patterns | Proprietary page builder | Gutenberg + custom blocks (standard, not vendor lock-in) |
| FAQ Management | Accordion block + patterns | Page builder accordion modules | Native accordion + FAQ schema (SEO advantage) |
| Performance Focus | Block theme optimized | Heavy (500KB+ JS/CSS) | Aggressive optimization (CDN, caching, asset control) |
| Design Consistency | Global styles system | Page builder style library | Global styles + locked patterns (prevents editor chaos) |
| Plugin Count | Minimal (5-10 focused plugins) | Moderate (15-20 with page builder ecosystem) | Ultra-lean (under 10 plugins via consolidation) |
| Asset Loading | Conditional loading | Global loading (all pages) | Page-specific asset loading (performance win) |
| Image Handling | Manual optimization | Some built-in optimization | Automatic WebP/AVIF + compression (zero editor effort) |
| Caching Strategy | Server + plugin caching | Page caching only | Multi-layer (page + object + CDN) |
| Schema Markup | Manual or plugin | Limited support | Automatic FAQ schema (SEO competitive edge) |
| Customization | Custom blocks when needed | Drag-drop everything | Custom blocks for domain-specific patterns only |

**Our Differentiation Strategy:**
1. **Performance-First**: Core Web Vitals under threshold (LCP < 2.5s, CLS < 0.1) vs competitors at 3-4s
2. **Editor Simplicity**: Locked patterns prevent chaos, global styles prevent inconsistency
3. **Lean Plugin Philosophy**: 10 plugins vs industry average 20-30
4. **SEO Built-In**: FAQ schema automatic vs manual competitor implementation
5. **Standards-Based**: Gutenberg (open standard) vs proprietary builders (vendor lock-in)

## Performance-Focused Feature Strategy

### Plugin Reduction Tactics

**Current Industry Average: 20-30 plugins per WordPress site**
**Target: 8-12 plugins maximum**

| Current Bloat Pattern | Lean Alternative | Savings |
|----------------------|------------------|---------|
| Separate plugins for slider, forms, SEO, schema, social | All-in-one like Jetpack or RankMath | 3-5 plugins consolidated |
| Page builder (Elementor/Divi) | Gutenberg + custom blocks + block theme | 1 massive plugin removed (500KB+) |
| Multiple caching plugins | Single solution (WP Rocket or server-level) | 2-3 plugins consolidated |
| Separate image optimization plugins | One comprehensive tool (Smush or Imagify) | 2 plugins consolidated |
| JavaScript animation libraries | CSS animations + progressive enhancement | 1-2 plugins removed |
| Font management plugins | Theme.json typography + Google Fonts API | 1 plugin removed |

**Ghost Plugin Audit Process:**
1. List all active plugins
2. For each, ask: "Was this for a one-time task?"
3. If unused in 90 days or not updated in 12 months → delete
4. For remaining, ask: "Could this be a code snippet instead?"
5. Consolidate overlapping functionality

### Server-Level Performance (Remove Plugin Dependencies)

**Trend for 2026: Move security and caching to server level**

| Traditional Plugin Approach | Server-Level Approach | Benefit |
|-----------------------------|----------------------|---------|
| Wordfence/Sucuri plugins | Cloudflare security + firewall | Reduces PHP load, better DDoS protection |
| WP Rocket/W3 Total Cache | Server-side page caching (Varnish/Nginx) | Faster cache serving, less overhead |
| Object caching plugin | Redis/Memcached at server level | Native performance, not PHP-based |
| Image CDN plugin | Cloudflare/BunnyCDN at DNS level | Remove plugin entirely, better global delivery |

**Note:** This requires managed hosting or VPS with control. Budget shared hosting won't support this.

### Core Web Vitals Target Metrics (2026 Standards)

| Metric | What It Measures | Target | Our Strategy |
|--------|-----------------|--------|--------------|
| **LCP** (Largest Contentful Paint) | Loading performance | < 2.5s | Optimized images, CDN, above-fold priority loading |
| **INP** (Interaction to Next Paint) | Responsiveness | < 200ms | Minimal JavaScript, defer non-critical scripts |
| **CLS** (Cumulative Layout Shift) | Visual stability | < 0.1 | Reserved space for images, no late-loading content |
| **TTFB** (Time to First Byte) | Server response | < 600ms | Object caching, quality hosting, CDN |

**Implementation Priority:**
1. **LCP (biggest user-facing impact)**: Image optimization + lazy loading + CDN
2. **CLS (easiest to fix)**: Set explicit width/height on images, avoid layout-shifting ads
3. **TTFB (requires server work)**: Object caching (Redis) + quality hosting
4. **INP (JavaScript heavy-lift)**: Asset loading control + defer non-critical scripts

## Sources

### WordPress Gutenberg & Block Editor (HIGH Confidence)
- [Jetpack: How to Use the WordPress Block Editor (2026 Tutorial)](https://jetpack.com/resources/wordpress-block-editor/)
- [WordPress Developer: Block Editor Handbook](https://developer.wordpress.org/block-editor/)
- [WPBeginner: How to Use the WordPress Block Editor](https://www.wpbeginner.com/beginners-guide/how-to-use-the-new-wordpress-block-editor/)
- [Varun Dubey: Gutenberg Blocks in 2026 - WordPress Development in the AI Era](https://vapvarun.com/gutenberg-blocks-2026-wordpress-block-editor-ai-era/)
- [Bluehost: WordPress Block Patterns in Gutenberg (2026 Guide)](https://www.bluehost.com/blog/wordpress-block-patterns/)

### Modern Content Editing Features (HIGH Confidence)
- [WordPress Developer Blog: What's New for Developers (February 2026)](https://developer.wordpress.org/news/2026/02/whats-new-for-developers-february-2026/)
- [AddWeb Solution: What's New in WordPress 6.9 (2026)](https://www.addwebsolution.com/blog/wordpress-6-9-what-you-actually-need-to-know-2026)
- [Developress: WordPress 7.0 Roadmap & 2026 Outlook](https://developress.io/wordpress-7-0-and-beyond-what-the-2026-roadmap-means-for-your-website/)
- [WordPress.com Blog: WordPress 6.9 - What's New for Site Owners and Bloggers](https://wordpress.com/blog/2025/12/02/wordpress-6-9-for-site-owners/)

### Performance Optimization (HIGH Confidence)
- [WordPress Developer: Advanced Administration - Optimization](https://developer.wordpress.org/advanced-administration/performance/optimization/)
- [Next3Offload: WordPress Performance Optimization - Ultimate 2026 Guide](https://next3offload.com/blog/wordpress-performance-optimization/)
- [CTA Flow: WordPress Performance Optimization 2026 - 7 Fixes](https://www.ctaflow.com/blog/wordpress-performance-guide-2026/)
- [WP Brigade: What Makes a High-Performance WordPress Site (2026 Standards)](https://wpbrigade.com/high-performance-wordpress-site/)
- [Pure Themes: Ultimate WordPress Speed Optimization - Complete Technical Guide](https://purethemes.net/ultimate-wordpress-speed-optimization-complete-technical-guide/)

### Custom Gutenberg Blocks Development (HIGH Confidence)
- [Kinsta: Building Custom Gutenberg Blocks - Definitive Tutorial](https://kinsta.com/blog/gutenberg-blocks/)
- [Multidots: How to Develop Custom Gutenberg Blocks for WordPress](https://www.multidots.com/blog/gutenberg-blocks-development-wordpress/)
- [Pagely: A Beginner's Guide to Custom Gutenberg Block Development](https://pagely.com/blog/a-beginners-guide-to-custom-gutenberg-block-development/)
- [WordPress GitHub: Custom Block Editor Documentation](https://github.com/WordPress/gutenberg/blob/trunk/docs/how-to-guides/platform/custom-block-editor.md)
- [Belov Digital: Creating Custom Gutenberg Blocks - Step-by-Step Tutorial](https://belovdigital.agency/blog/creating-custom-gutenberg-blocks-step-by-step-tutorial/)

### Plugin Reduction & Optimization (MEDIUM-HIGH Confidence)
- [Automator Plugin: 7 Simple Ways to Reduce WordPress Plugins](https://automatorplugin.com/7-ways-to-reduce-wordpress-plugins-and-optimize-performance/)
- [Datronix Tech: WordPress Plugins 2026 - Lean Tools to Reduce Bloat](https://datronixtech.com/wordpress-plugins-2026/)
- [WPBeginner: Which WordPress Plugins Are Slowing Down Your Site](https://www.wpbeginner.com/wp-tutorials/which-wordpress-plugins-are-slowing-down-your-site/)
- [Theme Isle: Do Plugins Affect WordPress Performance? Real Test Data](https://themeisle.com/blog/plugins-affect-wordpress-performance/)
- [Liven Creative: The Biggest WordPress Website Trends for 2026](https://livencreative.co.uk/the-biggest-wordpress-website-trends-for-2026-that-actually-matter/)

### Headless WordPress & Modern Architecture (MEDIUM Confidence)
- [Elementor: Headless WordPress in 2026 - Complete Guide](https://elementor.com/blog/headless-wordpress/)
- [Zebedee Creations: WordPress in 2026 - Traditional, Headless, Static or Hybrid?](https://www.zebedeecreations.com/blog/wordpress-in-2026-traditional-headless-static-or-hybrid/)
- [The Hawk Tech: Headless WordPress - Full Guide & Benefits 2026](https://thehawktech.com/headless-wordpress-complete-guide/)
- [Info Stans: How Headless WordPress Enhances Website Performance in 2026](https://infostans.com/how-headless-wordpress-enhances-website-performance)

### Full Site Editing (FSE) (MEDIUM Confidence)
- [Thrive Themes: WordPress FSE - Is It Time to Switch?](https://thrivethemes.com/wordpress-fse/)
- [Speckyboy: When to Choose WordPress Full Site Editing](https://speckyboy.com/when-should-you-use-wordpress-full-site-editing/)
- [Ollie WP: Is It Worth Switching to WordPress FSE? Yes, and Here's Why](https://olliewp.com/fse-wordpress-switching-worth-it/)
- [Human Made: Full Site Editing vs Leading Page Builders - Strategic Comparison](https://humanmade.com/wordpress-for-enterprise/full-site-editing-vs-leading-page-builders-a-strategic-comparison/)
- [Kinsta: Mastering WordPress Full Site Editing - Step-by-Step Tutorial](https://kinsta.com/blog/wordpress-full-site-editing/)

### FAQ & Accordion Best Practices (MEDIUM-HIGH Confidence)
- [OneUpWeb: Collapsible and Hidden Content SEO Best Practices](https://www.oneupweb.com/blog/seo-for-accordion-content/)
- [WPeka: Master User Experience with WordPress FAQ Accordion Plugins (2025)](https://wpeka.com/best-wordpress-accordion-plugins-for-faq.html)
- [Afteractive: How to Convert Yoast SEO FAQ Block into an Accordion](https://www.afteractive.com/blog/yoast-seos-faq-block-accordion)
- [SeedProd: 8 Best WordPress Accordion Plugins for FAQ 2026](https://www.seedprod.com/best-wordpress-accordion-plugins/)

### Block Themes vs Classic Themes (MEDIUM Confidence)
- [WPZOOM: WordPress Block Themes vs Classic Themes - 4 Key Differences](https://www.wpzoom.com/blog/block-themes-vs-classic-themes/)
- [Nexter: WordPress FSE Block Themes vs Classic Themes](https://nexterwp.com/blog/wordpress-fse-block-themes-vs-classic-themes/)
- [Theme Hunk: Block Themes vs Classic Themes For WordPress 2026](https://themehunk.com/block-themes-vs-classic-themes-for-wordpress/)
- [InstaWP: WordPress Block Theme vs Classic Theme - Beginner's Guide](https://instawp.com/wordpress-block-theme-vs-classic-theme/)
- [WP Poland: WordPress FSE vs Classic Themes - 2026 Guide](https://wppoland.com/en/classic-vs-block-themes-fse-guide/)

### Reusable Blocks & Patterns (MEDIUM Confidence)
- [Advanced Custom Fields: How to Use WordPress Reusable Blocks Effectively](https://www.advancedcustomfields.com/blog/wordpress-reusable-block/)
- [WPMet: Complete Guide to WordPress Reusable Blocks and Patterns](https://wpmet.com/wordpress-reusable-blocks-guide/)
- [Bizberg Themes: Reusable Blocks - Synced and Non-Synced Options](https://bizbergthemes.com/reusable-blocks-introducing-patterns-with-synced-and-non-synced-options/)
- [WordPress.com Blog: The Pattern System - Publish Faster with Reusable Layouts](https://wordpress.com/blog/2025/09/10/pattern-system-wordpress-publishing-workflow/)
- [WPBeginner: How to Create a Reusable Block in WordPress Block Editor](https://www.wpbeginner.com/beginners-guide/how-to-create-a-reusable-block-in-wordpress/)

### Core Web Vitals Optimization (HIGH Confidence)
- [WP Rocket: Google Core Web Vitals for WordPress - How to Test and Improve](https://wp-rocket.me/google-core-web-vitals-wordpress/)
- [WPBeginner: How to Optimize Core Web Vitals for WordPress - Ultimate Guide](https://www.wpbeginner.com/wp-tutorials/how-to-optimize-core-web-vitals-for-wordpress-ultimate-guide/)
- [Delicious Brains: Optimizing WordPress for Core Web Vitals](https://deliciousbrains.com/optimizing-wordpress-for-core-web-vitals/)
- [Jetpack: How to Improve Google Core Web Vitals on WordPress](https://jetpack.com/resources/wordpress-core-web-vitals/)
- [Pantheon: Developer's Guide to Optimizing WordPress Core Web Vitals](https://pantheon.io/learning-center/wordpress/core-web-vitals)

### Lazy Loading & Image Optimization (HIGH Confidence)
- [Imagify: How to Implement Image Lazy Loading on WordPress](https://imagify.io/blog/image-lazy-loading-wordpress/)
- [WP Rocket: Lazy Loading in WordPress - Images, Videos, and More](https://wp-rocket.me/blog/lazy-loading-wordpress-5-5/)
- [Hostinger: WordPress Lazy Loading - What It Is and How to Enable It](https://www.hostinger.com/tutorials/wordpress-lazy-load)
- [ShortPixel: 6 Benefits of Lazy Loading Images in WordPress](https://shortpixel.com/blog/benefits-of-lazy-loading-images-in-wordpress/)
- [Cloudinary: What Is Lazy Loading in WordPress?](https://cloudinary.com/guides/wordpress-plugin/what-is-lazy-loading-in-wordpress)

---

*Feature research for: Smartvarme WordPress/WooCommerce Content System Modernization*
*Researched: 2026-02-11*
*Next: Use this to define content editing requirements and performance optimization roadmap*
