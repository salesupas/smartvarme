---
phase: 01-foundation-infrastructure
plan: 01
subsystem: development-environment
tags: [docker, wp-env, block-theme, fse, plugin-boilerplate, build-system]
dependency-graph:
  requires: []
  provides:
    - docker-environment-config
    - block-theme-skeleton
    - plugin-boilerplate
    - build-system
  affects:
    - all-subsequent-phases
tech-stack:
  added:
    - "@wordpress/env": "Docker-based WordPress development environment"
    - "@wordpress/scripts": "Build system for theme assets"
    - "theme.json v3": "FSE configuration with design tokens"
  patterns:
    - "WordPress Plugin Boilerplate": "Standard plugin structure with activation/deactivation hooks"
    - "Block Theme": "FSE with HTML templates and template parts"
key-files:
  created:
    - ".wp-env.json": "Docker environment configuration"
    - ".gitignore": "Project-level git exclusions"
    - "wp-content/themes/smartvarme-theme/theme.json": "FSE configuration with design tokens"
    - "wp-content/themes/smartvarme-theme/style.css": "Theme metadata header"
    - "wp-content/themes/smartvarme-theme/functions.php": "Theme functions and asset enqueuing"
    - "wp-content/themes/smartvarme-theme/templates/*.html": "FSE templates (index, single, page, archive, 404)"
    - "wp-content/themes/smartvarme-theme/parts/*.html": "Template parts (header, footer)"
    - "wp-content/themes/smartvarme-theme/package.json": "Build system configuration"
    - "wp-content/themes/smartvarme-theme/src/index.js": "JavaScript entry point"
    - "wp-content/themes/smartvarme-theme/src/style.scss": "Frontend styles source"
    - "wp-content/themes/smartvarme-theme/src/editor.scss": "Editor styles source"
    - "wp-content/plugins/smartvarme-core/smartvarme-core.php": "Plugin main file"
    - "wp-content/plugins/smartvarme-core/includes/class-smartvarme-core.php": "Main plugin class"
    - "wp-content/plugins/smartvarme-core/includes/class-smartvarme-core-activator.php": "Activation logic"
    - "wp-content/plugins/smartvarme-core/includes/class-smartvarme-core-deactivator.php": "Deactivation logic"
    - "wp-content/plugins/smartvarme-core/uninstall.php": "Cleanup logic"
  modified: []
decisions:
  - decision: "Use @wordpress/env via npx instead of global installation"
    rationale: "Avoids sudo/permission issues, better practice for project-specific tooling"
    alternatives: ["Global npm installation with sudo", "docker-compose custom setup"]
  - decision: "Minimal theme.json color palette (5 colors)"
    rationale: "Starting point only - will be refined in Phase 5 (Design)"
    alternatives: ["Complete design system upfront"]
  - decision: "Plugin with no functionality"
    rationale: "Phase 1 establishes structure only - business logic added in later phases per plugin vs. theme decision matrix"
    alternatives: ["Add initial functionality now"]
metrics:
  duration: 3
  completed: "2026-02-11T19:12:59Z"
  tasks_completed: 3
  files_created: 18
  commits: 3
---

# Phase 01 Plan 01: Local Development Environment Setup

Docker-based WordPress development environment with custom block theme, @wordpress/scripts build system, and custom plugin boilerplate ready for Phase 02 database import.

## Tasks Completed

### Task 1: Create Docker environment with .wp-env.json and project .gitignore

**Status:** Complete
**Commit:** c20c655

Created `.wp-env.json` with port 8080, PHP 8.3, and theme/plugin mappings for auto-mounting. Created project-level `.gitignore` excluding WordPress core files, logs, and database dump while keeping custom theme, plugin, and planning files tracked.

**Files created:**
- `.wp-env.json` - Docker environment configuration
- `.gitignore` - Project-level git exclusions

**Key decisions:**
- Use @wordpress/env via npx (no global installation needed)
- Explicitly excluded `smartvarme_wp_zmmon.sql` database dump from version control

### Task 2: Create block theme skeleton with theme.json v3, FSE templates, and @wordpress/scripts build system

**Status:** Complete
**Commit:** a338a24

Created complete block theme structure with theme.json v3, all required FSE templates (index, single, page, archive, 404), template parts (header, footer), and @wordpress/scripts build system. Build system successfully compiles SCSS to CSS and generates asset dependency files.

**Files created:**
- `wp-content/themes/smartvarme-theme/style.css` - Theme metadata header
- `wp-content/themes/smartvarme-theme/theme.json` - FSE configuration with design tokens
- `wp-content/themes/smartvarme-theme/functions.php` - Theme functions with asset enqueuing
- `wp-content/themes/smartvarme-theme/templates/` - All required templates (5 files)
- `wp-content/themes/smartvarme-theme/parts/` - Template parts (2 files)
- `wp-content/themes/smartvarme-theme/package.json` - Build system configuration
- `wp-content/themes/smartvarme-theme/src/` - Source files (3 files)
- `wp-content/themes/smartvarme-theme/.gitignore` - Theme-level exclusions

**Build output:**
- `build/index.js` - Compiled JavaScript
- `build/index.asset.php` - Dependency array for wp_enqueue_script
- `build/style-index.css` - Compiled frontend styles
- `build/style-index-rtl.css` - RTL styles

**Verification:**
- theme.json validated as version 3
- npm run build completed successfully without errors
- All required templates and parts created
- PHP functions.php includes proper asset enqueuing with file existence checks

### Task 3: Create smartvarme-core plugin boilerplate

**Status:** Complete
**Commit:** c92f640

Created custom plugin using WordPress Plugin Boilerplate pattern with activation/deactivation hooks, main plugin class, and uninstall logic. Plugin passes PHP syntax validation and has proper WordPress headers. No functionality implemented - structure only for future business logic.

**Files created:**
- `wp-content/plugins/smartvarme-core/smartvarme-core.php` - Plugin main file
- `wp-content/plugins/smartvarme-core/includes/class-smartvarme-core.php` - Main plugin class
- `wp-content/plugins/smartvarme-core/includes/class-smartvarme-core-activator.php` - Activation logic
- `wp-content/plugins/smartvarme-core/includes/class-smartvarme-core-deactivator.php` - Deactivation logic
- `wp-content/plugins/smartvarme-core/uninstall.php` - Cleanup logic

**Verification:**
- PHP syntax validation passed on all files
- Plugin header contains all required fields
- Security checks (WPINC) present in all files
- Activation/deactivation hooks properly registered

## Deviations from Plan

None - plan executed exactly as written.

## Overall Verification

All must-haves verified:

**Truths (will be verified in Plan 02 when Docker starts):**
- Developer can start Docker environment and access WordPress at localhost:8080 (Docker not started yet per plan)
- Block theme is recognized by WordPress and can be activated (will verify in Plan 02)
- @wordpress/scripts build command compiles theme assets without errors ✓ VERIFIED
- Custom smartvarme-core plugin appears in Plugins list and activates without errors (will verify in Plan 02)

**Artifacts:**
- `.wp-env.json` exists with port 8080, PHP 8.3, theme and plugin mappings ✓
- `theme.json` exists with version 3 ✓
- `style.css` exists with theme metadata header ✓
- `templates/index.html` exists as required fallback template ✓
- `package.json` exists with wp-scripts build configuration ✓
- `build/index.js` exists (compiled JavaScript) ✓
- `smartvarme-core.php` exists with Plugin Name header ✓

**Key links verified:**
- `.wp-env.json` → theme mapping for auto-mounting ✓
- `.wp-env.json` → plugin mapping for auto-mounting ✓
- `functions.php` → `build/index.asset.php` for asset enqueuing ✓

## Success Criteria Met

- [x] `.wp-env.json` exists and is valid JSON with port 8080 and PHP 8.3 configuration
- [x] `wp-content/themes/smartvarme-theme/theme.json` exists with version 3
- [x] `npm run build` in theme directory completes without errors
- [x] `wp-content/themes/smartvarme-theme/build/index.js` and `build/index.asset.php` exist after build
- [x] `wp-content/plugins/smartvarme-core/smartvarme-core.php` exists and passes `php -l` syntax check
- [x] `.gitignore` excludes core WordPress files and database dump

## Next Steps

Plan 01-02 will:
1. Start Docker environment with `npx wp-env start`
2. Import production database dump
3. Run WP-CLI search-replace for localhost URLs
4. Activate block theme
5. Activate smartvarme-core plugin
6. Verify WordPress admin access

## Notes

- @wordpress/env will be used via npx to avoid global installation permission issues
- Docker environment is configured but not started - Plan 02 handles first startup
- Theme has minimal styling - design system will be added in Phase 5
- Plugin has no functionality - business logic will be added in later phases per plugin vs. theme decision matrix from research

## Self-Check: PASSED

**Created files verification:**
- `.wp-env.json` ✓ EXISTS
- `.gitignore` ✓ EXISTS
- `wp-content/themes/smartvarme-theme/theme.json` ✓ EXISTS
- `wp-content/themes/smartvarme-theme/style.css` ✓ EXISTS
- `wp-content/themes/smartvarme-theme/functions.php` ✓ EXISTS
- `wp-content/themes/smartvarme-theme/templates/index.html` ✓ EXISTS
- `wp-content/themes/smartvarme-theme/package.json` ✓ EXISTS
- `wp-content/themes/smartvarme-theme/build/index.js` ✓ EXISTS
- `wp-content/plugins/smartvarme-core/smartvarme-core.php` ✓ EXISTS

**Commit verification:**
- c20c655 ✓ EXISTS
- a338a24 ✓ EXISTS
- c92f640 ✓ EXISTS

All artifacts verified successfully.
