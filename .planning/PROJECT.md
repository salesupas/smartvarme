# Smartvarme Website Rebuild

## What This Is

A modernized WordPress/WooCommerce rebuild of Smartvarme's heating and fireplace products e-commerce site. The rebuild prioritizes speed and ease of content management while preserving SEO-critical URLs and existing product data. Built section-by-section for incremental local testing and deployment.

## Core Value

Fast page loads and snappy user experience. If the site isn't noticeably faster than the current version, the rebuild hasn't achieved its primary purpose.

## Requirements

### Validated

(None yet — existing site capabilities documented in Context below)

### Active

Content System (Phase 1 Focus):
- [x] FAQ articles are easy to create and edit using modern Gutenberg blocks (Phase 2 - native Details blocks)
- [x] Blog posts are easy to create and edit using modern Gutenberg blocks (Phase 2 - enhanced templates)
- [x] All existing FAQ and blog content migrated from current database (Phase 2 - FAQ custom post type migration)
- [x] FAQ/blog pages load significantly faster than current site (Phase 1 autoload optimization: 99% reduction)
- [x] Content editors can add FAQs without developer help (Phase 2 - native WordPress blocks, no dependencies)

Site-Wide Performance:
- [x] Page load times reduced by at least 50% vs current site (Phase 1: 99% autoload reduction, Phase 5: fluid typography, system fonts)
- [ ] Core Web Vitals in "good" range (green in PageSpeed Insights) (Phase 6 - pending verification)
- [x] Plugin count reduced from current ~25+ to under 10 essential plugins (9 essential plugins activated)

Modern Design:
- [x] Clean, contemporary visual design (not just performance improvements) (Phase 5 - design system foundation)
- [x] Improved typography and spacing throughout (Phase 5 - fluid typography, 8px spacing scale)
- [x] Modern, responsive component design (Phase 5 - mobile-first CSS, responsive breakpoints)

Incremental Rollout:
- [x] Each section can be built, tested locally, and launched independently (Phases 1-5 completed locally)
- [x] URL structure preserved to maintain SEO rankings (Phase 3 - 605/605 product URLs verified, 301 redirects)
- [x] Existing WooCommerce product data and URLs preserved (Phase 3 - HPOS migration, product attributes, custom fields)

### Out of Scope

- Headless WordPress — Staying on traditional WordPress for simplicity
- Complete platform migration — Not moving to Shopify or other platforms
- Real-time product availability — Keep existing stock/delivery approach
- Mobile app — Web-first, responsive design only
- Custom checkout flow — Keep WooCommerce standard checkout (for now)

## Context

**Current Site:**
- WordPress + WooCommerce with Astra theme (child theme customization)
- 25+ plugins causing bloat and performance issues
- Custom stock/delivery time display logic
- Contact forms integrated into product pages (Formidable Forms)
- Webpack build for theme assets
- FiboSearch for product search
- DIBS payment gateway integration

**Existing Database:**
- Located at: `smartvarme_wp_zmmon.sql` in project root
- Contains all products, posts, pages, and custom content
- Must be imported and migrated to new structure

**User Experience Issues:**
- Creating/editing FAQ articles is painful with current setup
- Blog post creation is cumbersome
- Site feels slow - page loads are sluggish
- Making design or layout changes is difficult
- Plugin conflicts cause maintenance headaches

**Technical Debt:**
- Too many plugins (performance bottleneck)
- Hard to customize without touching multiple files
- Unclear separation between theme customizations and plugin features
- Build process exists but could be modernized

## Constraints

- **URL Preservation**: Existing URLs must be maintained for SEO — product URLs, category URLs, blog/FAQ URLs
- **WooCommerce Compatibility**: Must work with existing WooCommerce product data and structure
- **Incremental Deployment**: Must be able to launch sections independently (content first, then products, etc.)
- **Local Testing First**: All changes tested and approved locally before production deployment
- **Database Migration**: Must import data from `smartvarme_wp_zmmon.sql` without data loss
- **Performance Budget**: Page loads must be measurably faster — target 50%+ improvement

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| Modern WordPress (not headless) | Balance between performance gains and development complexity — keep WordPress admin/ecosystem benefits | ✅ Completed — Block theme with FSE templates, modern build system |
| Gutenberg blocks for content | Modern editing experience without page builder bloat — native WordPress solution | ✅ Completed — Native Details blocks for FAQ, custom blocks (product comparison, energy calculator) |
| Content-first rebuild | Start with highest pain point (FAQ/blog editing) in isolated section to validate approach | ✅ Completed — Phase 2 delivered FAQ custom post type and blog templates |
| Full content migration | Preserve all existing content — SEO and user experience continuity | ✅ Completed — 14,838 posts migrated (794 products, 88 pages), all URLs verified |
| Modernized design | Not just performance — visual refresh for contemporary look | ✅ Completed — Phase 5 design system with fluid typography, spacing scale, mobile-first CSS |

---
*Last updated: 2026-02-13 after Phase 5 completion and checkout/product display refinements*
