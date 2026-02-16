---
phase: 02-content-system-migration
plan: 05
subsystem: faq-cpt
tags: [gap-closure, custom-post-type, content-migration, schema-update]
dependency_graph:
  requires:
    - "02-02 (FAQ page foundation)"
    - "WordPress custom post type API"
    - "Block theme template hierarchy"
  provides:
    - "FAQ custom post type (faq)"
    - "FAQ category taxonomy (faq_category)"
    - "FAQ archive template (archive-faq.html)"
    - "FAQ single template (single-faq.html)"
    - "Updated FAQPage schema for archive"
  affects:
    - "FAQ content structure (page → custom post type)"
    - "FAQ URLs (/faq/ archive, /faq/{slug}/ singles)"
    - "Schema markup generation (Details blocks → CPT archive)"
tech_stack:
  added:
    - "FAQ custom post type registration"
    - "FAQ category taxonomy"
    - "Block template hierarchy (archive-faq, single-faq)"
  patterns:
    - "Custom post type with archive support"
    - "Taxonomy for content organization"
    - "Block-based templates (FSE)"
    - "FAQPage schema from CPT query"
key_files:
  created:
    - "wp-content/plugins/smartvarme-core/includes/class-smartvarme-faq-cpt.php"
    - "wp-content/themes/smartvarme-theme/templates/archive-faq.html"
    - "wp-content/themes/smartvarme-theme/templates/single-faq.html"
  modified:
    - "wp-content/plugins/smartvarme-core/includes/class-smartvarme-core.php"
    - "wp-content/plugins/smartvarme-core/includes/class-smartvarme-faq-schema.php"
    - "wp-content/themes/smartvarme-theme/src/style.scss"
decisions:
  - decision: "FAQ as custom post type with archive"
    rationale: "User feedback revealed need for FAQ overview (short answers) with drill-down to full articles"
    alternatives: "Keep page-based Details blocks (rejected: no individual FAQ URLs)"
  - decision: "Post title = question, excerpt = short answer, content = full answer"
    rationale: "Natural WordPress content model; excerpt for archive display, content for single pages"
    alternatives: "Custom fields (rejected: unnecessary complexity)"
  - decision: "FAQ category taxonomy for future organization"
    rationale: "Enables grouping FAQs by topic (products, services, technical) as content grows"
    alternatives: "No taxonomy (rejected: limits scalability)"
  - decision: "Schema from custom post type query instead of Details blocks"
    rationale: "FAQ structure changed; schema must generate from CPT archive"
    alternatives: "Remove schema (rejected: SEO value)"
metrics:
  duration: "7m 39s"
  tasks_completed: 2
  files_created: 3
  files_modified: 3
  commits: 2
  completed_at: "2026-02-12T07:14:07Z"
---

# Phase 2 Plan 5: FAQ Custom Post Type Migration Summary

**One-liner:** Converted FAQ from page-based Details blocks to custom post type with archive (short answers) and single posts (full answers).

## What Was Built

### FAQ Custom Post Type Infrastructure
- **Custom post type registration** with Norwegian labels and Gutenberg support
- **FAQ category taxonomy** for organizing questions by topic
- **Archive support** at `/faq/` URL showing all FAQ items
- **Single post support** at `/faq/{slug}/` URLs for individual FAQs
- **Template hierarchy** integration with block theme templates

### Content Migration
- **5 FAQ posts created** from existing Details blocks:
  1. Hvordan foregår levering ved kjøp på nett?
  2. Hvor raskt leveres varen ved kjøp på nett?
  3. Hvor kan jeg få hjelp til montering?
  4. Hva er regler for montering av ildsted?
  5. Jeg er bedrift – kan jeg få andre betingelser?
- **Enhanced content** with expanded full answers (original had only short text)
- **Excerpts as short answers** for archive display
- **Old FAQ page updated** to show query loop of all FAQ posts

### Block Templates
- **archive-faq.html**: Overview page showing questions with short answers and "Les hele svaret →" links
- **single-faq.html**: Individual FAQ post with breadcrumb navigation and "Fikk du ikke svar?" help section

### Schema & Styling
- **FAQPage schema updated** to generate from custom post type archive query
- **FAQ list card styles** with hover effects and gold accent colors
- **Breadcrumb and help section styles** for single FAQ pages
- **Theme rebuild** compiled new styles successfully

## User Impact

**Before:** FAQ content was on a single page with Details accordion blocks. Each question/answer lived in a Details block with no individual URLs.

**After:** Each FAQ is now a separate post with:
- **Individual URLs** for sharing and SEO
- **Archive overview** showing short answers with "Read more" links
- **Full detail pages** for expanded content
- **Better SEO** with FAQPage schema from archive
- **Future scalability** with category taxonomy for organizing questions

## Deviations from Plan

None - plan executed exactly as written.

## Technical Architecture

### Custom Post Type Registration
```php
// FAQ custom post type with:
- Norwegian labels (Legg til nytt spørsmål, etc.)
- Archive support (has_archive: true)
- Gutenberg editor (show_in_rest: true)
- Post title = question
- Post excerpt = short answer
- Post content = full detailed answer
- URL structure: /faq/ (archive), /faq/{slug}/ (single)
```

### Template Hierarchy
```
WordPress template hierarchy:
1. archive-faq.html → /faq/ (archive)
2. single-faq.html → /faq/{slug}/ (single posts)

Both templates use:
- wp:query for FAQ post loop
- wp:post-template for individual items
- wp:post-title, wp:post-excerpt, wp:post-content blocks
```

### Schema Generation
**Old approach:** Parse Details blocks from FAQ page content
**New approach:** Query all FAQ posts and generate FAQPage schema from archive

```php
// Schema structure:
{
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "Post title",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Post excerpt (short answer)"
      }
    }
  ]
}
```

## Quality Gates Passed

### Verification Checks
- ✅ FAQ custom post type registered (`wp post-type list | grep faq`)
- ✅ 5 FAQ posts created (`wp post list --post_type=faq --format=count`)
- ✅ Archive page accessible at http://localhost:8080/faq/
- ✅ Single FAQ posts accessible at /faq/{slug}/ URLs
- ✅ FAQPage schema outputs on archive page
- ✅ Archive shows short answers with "Les hele svaret →" links
- ✅ Single pages show full content with breadcrumb navigation
- ✅ Theme styles compiled successfully
- ✅ All PHP syntax checks passed

### Success Criteria
- ✅ FAQ custom post type registered with Norwegian labels
- ✅ 5+ FAQ posts migrated from existing content
- ✅ Archive template (archive-faq.html) shows overview with short answers
- ✅ Single template (single-faq.html) shows full detailed answers
- ✅ FAQ schema class updated to work with custom post type archive
- ✅ All FAQ URLs work: /faq/ (archive), /faq/{slug}/ (single posts)
- ✅ Styles compiled successfully with FAQ list and single page styling
- ✅ No 404 errors on FAQ URLs

## Files Modified

### Created
- `wp-content/plugins/smartvarme-core/includes/class-smartvarme-faq-cpt.php` (100 lines) - FAQ CPT registration
- `wp-content/themes/smartvarme-theme/templates/archive-faq.html` (37 lines) - FAQ overview template
- `wp-content/themes/smartvarme-theme/templates/single-faq.html` (33 lines) - Single FAQ template

### Modified
- `wp-content/plugins/smartvarme-core/includes/class-smartvarme-core.php` (+3 lines) - Wired FAQ CPT into plugin
- `wp-content/plugins/smartvarme-core/includes/class-smartvarme-faq-schema.php` (-83, +25 lines) - Schema from CPT archive
- `wp-content/themes/smartvarme-theme/src/style.scss` (+75 lines) - FAQ list and single page styles

## Commits

| Commit | Message | Files |
|--------|---------|-------|
| be182b5d | feat(02-05): register FAQ custom post type and migrate content | 2 files, 100+ insertions |
| 7273a842 | feat(02-05): add FAQ archive and single templates with updated schema | 4 files, 168+ insertions, 83- deletions |

## Performance Notes

**Execution time:** 7m 39s (automated)

**Build metrics:**
- Theme build: 568ms (webpack)
- 2 tasks completed atomically
- 5 FAQ posts created via WP-CLI
- Zero manual intervention

## Next Steps

**Phase 2 Status:** Gap closure complete. FAQ system now aligns with user feedback.

**Future enhancements** (not in current scope):
- Populate FAQ category taxonomy with groups (Produkter, Installasjon, Levering)
- Add FAQ search/filter functionality
- Consider FAQ ordering by popularity or manual ordering

## Self-Check: PASSED

**Created files verified:**
```
✅ FOUND: wp-content/plugins/smartvarme-core/includes/class-smartvarme-faq-cpt.php
✅ FOUND: wp-content/themes/smartvarme-theme/templates/archive-faq.html
✅ FOUND: wp-content/themes/smartvarme-theme/templates/single-faq.html
```

**Commits verified:**
```
✅ FOUND: be182b5d (feat(02-05): register FAQ custom post type and migrate content)
✅ FOUND: 7273a842 (feat(02-05): add FAQ archive and single templates with updated schema)
```

**Functionality verified:**
```
✅ FAQ archive accessible at /faq/
✅ FAQ single posts accessible at /faq/{slug}/
✅ FAQPage schema outputs on archive
✅ 5 FAQ posts migrated successfully
```
