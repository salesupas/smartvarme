# Phase 1: Foundation & Infrastructure - Research

**Researched:** 2026-02-11
**Domain:** WordPress local development, Docker containerization, block theme architecture, build systems
**Confidence:** HIGH

## Summary

Phase 1 establishes the complete local development environment and architectural foundation for migrating Smartvarme from a classic WordPress site to a modern block theme using Full Site Editing (FSE). The foundation consists of seven core infrastructure components: Docker-based local development, block theme skeleton with theme.json v3, @wordpress/scripts build pipeline, WordPress 6.8+ on PHP 8.3 and MariaDB 10.11 LTS, database optimization targeting <800KB autoloaded data, WP-CLI-based migration toolchain, and a custom plugin for business logic separation.

The research reveals WordPress development in 2026 has converged on Docker for local environments (either raw docker-compose or @wordpress/env wrapper), @wordpress/scripts as the standard build tool, and theme.json v3 as the FSE configuration standard. Critical success factors include: proper serialization handling during database migration (WP-CLI is essential), aggressive autoload optimization from day one, and understanding FSE's paradigm shift from traditional theming.

**Primary recommendation:** Use @wordpress/env for rapid setup with automatic plugin/theme mounting, establish theme.json v3 configuration before any custom blocks, run WP-CLI search-replace with --dry-run first to verify serialization handling, and implement autoload monitoring from project start to prevent the 800KB threshold breach.

## Standard Stack

### Core

| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| @wordpress/env | Latest (npm) | Docker-based local environment | Official WordPress recommendation, zero-config setup, automatic plugin/theme mounting |
| @wordpress/scripts | Latest (npm) | Build system (webpack, Babel, ESLint) | Official WordPress toolkit, zero-config webpack, includes all dev dependencies |
| WP-CLI | 2.x | Database migration & management | Gold standard for serialization-aware search-replace, required for safe migrations |
| WordPress | 6.8+ | CMS platform | Latest major version with PHP 8.3 full support and theme.json v3 |
| PHP | 8.3 | Server runtime | Active support status, recommended by WordPress.org for new installations |
| MariaDB | 10.11 LTS | Database engine | Long-term support release, officially tested against WordPress, exceeds minimum 10.6 requirement |

### Supporting

| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| docker-compose | Latest | Manual Docker orchestration | Alternative to @wordpress/env when custom Docker config needed |
| Query Monitor | Latest WP plugin | Performance profiling | Essential for autoload monitoring and optimization validation |
| Autoload Optimizer | Latest WP plugin | Autoload cleanup | Use if autoload exceeds 800KB threshold |
| WP-CLI Doctor | Latest | Database health checks | Pre-migration validation and post-optimization verification |

### Alternatives Considered

| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| @wordpress/env | Raw docker-compose | More control but requires manual configuration, no automatic mounting |
| @wordpress/scripts | Custom webpack config | Full customization but maintenance burden, loses zero-config benefits |
| WP-CLI search-replace | Manual SQL REPLACE() | Faster but **will corrupt serialized data** - never use |
| MariaDB 10.11 | MySQL 8.0+ | Equivalent functionality, MariaDB preferred for LTS designation |

**Installation:**

```bash
# Global tools
npm install -g @wordpress/env
npm install -g @wordpress/scripts

# WP-CLI (macOS Homebrew)
brew install wp-cli

# Or via curl (Linux/macOS)
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
sudo mv wp-cli.phar /usr/local/bin/wp

# Verify installations
wp-env --version
wp-scripts --version
wp --info
```

## Architecture Patterns

### Recommended Project Structure

```
smartvarme2.0/
├── .wp-env.json                 # wp-env configuration (if needed)
├── docker-compose.yml           # Alternative to wp-env (if using raw Docker)
├── wp-content/
│   ├── themes/
│   │   └── smartvarme-theme/   # Custom block theme
│   │       ├── style.css        # Required: theme metadata + custom CSS
│   │       ├── theme.json       # Required: FSE configuration (version 3)
│   │       ├── functions.php    # Optional: theme-specific functions
│   │       ├── templates/       # Required: HTML templates
│   │       │   ├── index.html   # Required: fallback template
│   │       │   ├── single.html  # Single post template
│   │       │   ├── page.html    # Page template
│   │       │   └── ...
│   │       ├── parts/           # Template parts (header, footer, etc.)
│   │       │   ├── header.html
│   │       │   ├── footer.html
│   │       │   └── ...
│   │       ├── patterns/        # Block patterns (optional)
│   │       ├── assets/          # Compiled assets (gitignored)
│   │       │   ├── css/
│   │       │   └── js/
│   │       ├── src/             # Source files for @wordpress/scripts
│   │       │   ├── css/
│   │       │   └── js/
│   │       ├── package.json     # npm dependencies + wp-scripts commands
│   │       └── node_modules/    # npm packages (gitignored)
│   └── plugins/
│       └── smartvarme-core/     # Custom plugin for business logic
│           ├── smartvarme-core.php  # Main plugin file
│           ├── includes/         # PHP classes
│           │   ├── class-*.php
│           │   └── ...
│           ├── admin/            # Admin-specific code
│           ├── public/           # Public-facing code
│           ├── assets/           # Compiled assets
│           ├── src/              # Source files
│           ├── languages/        # Translation files
│           └── uninstall.php     # Cleanup on uninstall
├── smartvarme_wp_zmmon.sql      # Production database export
└── .planning/                    # GSD framework (not deployed)
```

### Pattern 1: wp-env Local Development Setup

**What:** Official WordPress Docker environment with zero configuration
**When to use:** Default choice for WordPress development; automatic plugin/theme mounting when running from their directories

**Example:**

```bash
# From theme directory - automatically mounts and activates theme
cd wp-content/themes/smartvarme-theme
wp-env start
# Access at http://localhost:8888 (admin/password)

# From plugin directory - automatically mounts and activates plugin
cd wp-content/plugins/smartvarme-core
wp-env start

# From project root - generic WordPress without auto-mounting
cd /path/to/smartvarme2.0
wp-env start

# Essential commands
wp-env stop                    # Stop containers
wp-env clean all              # Reset database
wp-env destroy                # Remove environment completely
wp-env run cli wp shell       # WP-CLI shell access
```

**Source:** [Official WordPress wp-env documentation](https://developer.wordpress.org/block-editor/getting-started/devenv/get-started-with-wp-env/)

### Pattern 2: @wordpress/scripts Build Configuration

**What:** Zero-config webpack toolchain for WordPress asset compilation
**When to use:** All WordPress themes and plugins requiring JavaScript/CSS build steps

**Example:**

```json
// package.json
{
  "name": "smartvarme-theme",
  "version": "1.0.0",
  "scripts": {
    "start": "wp-scripts start",
    "build": "wp-scripts build",
    "format": "wp-scripts format",
    "lint:js": "wp-scripts lint-js",
    "lint:css": "wp-scripts lint-style"
  },
  "devDependencies": {
    "@wordpress/scripts": "^27.0.0"
  }
}
```

**Directory structure:**

```
theme/
├── src/
│   ├── index.js      # Entry point (webpack will find this)
│   ├── editor.scss   # Editor styles
│   └── style.scss    # Frontend styles
└── build/            # Generated by wp-scripts (gitignore this)
    ├── index.js
    ├── index.asset.php  # Dependency array + version for wp_enqueue_script
    ├── editor.css
    └── style.css
```

**Enqueue in theme:**

```php
// functions.php
function smartvarme_enqueue_assets() {
    $asset_file = include get_template_directory() . '/build/index.asset.php';

    wp_enqueue_script(
        'smartvarme-scripts',
        get_template_directory_uri() . '/build/index.js',
        $asset_file['dependencies'],
        $asset_file['version'],
        true
    );

    wp_enqueue_style(
        'smartvarme-styles',
        get_template_directory_uri() . '/build/style.css',
        [],
        $asset_file['version']
    );
}
add_action('wp_enqueue_scripts', 'smartvarme_enqueue_assets');
```

**Source:** [Official @wordpress/scripts documentation](https://developer.wordpress.org/block-editor/getting-started/devenv/get-started-with-wp-scripts/)

### Pattern 3: theme.json v3 Block Theme Configuration

**What:** Single JSON file defining global styles, settings, and FSE capabilities
**When to use:** Required for all block themes; establishes design system before building blocks

**Example:**

```json
{
  "$schema": "https://schemas.wp.org/trunk/theme.json",
  "version": 3,
  "settings": {
    "appearanceTools": true,
    "useRootPaddingAwareAlignments": true,
    "color": {
      "palette": [
        {
          "slug": "primary",
          "color": "#1a1a1a",
          "name": "Primary"
        },
        {
          "slug": "secondary",
          "color": "#767676",
          "name": "Secondary"
        }
      ],
      "custom": true,
      "customGradient": true
    },
    "typography": {
      "fontSizes": [
        {
          "slug": "small",
          "size": "0.875rem",
          "name": "Small"
        },
        {
          "slug": "medium",
          "size": "1rem",
          "name": "Medium"
        }
      ],
      "fluid": true,
      "lineHeight": true
    },
    "spacing": {
      "units": ["px", "em", "rem", "vh", "vw", "%"],
      "padding": true,
      "margin": true
    },
    "layout": {
      "contentSize": "800px",
      "wideSize": "1200px"
    }
  },
  "styles": {
    "color": {
      "background": "var(--wp--preset--color--white)",
      "text": "var(--wp--preset--color--primary)"
    },
    "typography": {
      "fontSize": "var(--wp--preset--font-size--medium)",
      "lineHeight": "1.6"
    },
    "blocks": {
      "core/heading": {
        "typography": {
          "fontWeight": "700"
        }
      }
    }
  }
}
```

**Key behaviors:**
- Auto-generates CSS custom properties: `--wp--preset--color--primary`
- Auto-generates CSS classes: `.has-primary-color`, `.has-primary-background-color`
- Block-level settings override top-level settings
- Use `version: 3` for WordPress 6.6+ compatibility

**Source:** [Official theme.json documentation](https://developer.wordpress.org/block-editor/how-to-guides/themes/global-settings-and-styles/)

### Pattern 4: WP-CLI Serialization-Safe Database Migration

**What:** Command-line tool for safe search-replace operations that handle PHP serialized data
**When to use:** Always use for domain changes, URL updates, or any database search-replace during migrations

**Example:**

```bash
# ALWAYS run dry-run first to preview changes
wp search-replace 'https://old-domain.com' 'http://localhost:8080' \
  --dry-run \
  --all-tables \
  --precise

# If dry-run looks correct, run without --dry-run
wp search-replace 'https://old-domain.com' 'http://localhost:8080' \
  --all-tables \
  --precise \
  --skip-columns=guid

# Target specific tables only (safer)
wp search-replace 'https://old-domain.com' 'http://localhost:8080' \
  wp_posts wp_postmeta wp_options \
  --precise

# Export instead of modifying database (even safer)
wp search-replace 'old-value' 'new-value' \
  --export=modified-database.sql \
  --all-tables

# Check what will be changed with verbose logging
wp search-replace 'old-value' 'new-value' \
  --dry-run \
  --verbose \
  --log=changes.log
```

**Critical flags:**
- `--dry-run`: Preview changes without saving (ALWAYS use first)
- `--precise`: Use PHP instead of SQL for thorough serialization handling
- `--all-tables`: Process all tables regardless of prefix
- `--skip-columns=guid`: Never modify WordPress GUID column
- `--export=<file>`: Generate SQL file instead of modifying database

**Why serialization matters:**
Serialized data includes string length indicators (e.g., `s:22:"https://example.com"`). Simple SQL REPLACE() changes the string but not the length indicator, corrupting the data. WP-CLI deserializes → replaces → reserializes, maintaining data integrity.

**Source:** [Official WP-CLI search-replace documentation](https://developer.wordpress.org/cli/commands/search-replace/)

### Anti-Patterns to Avoid

- **Using SQL REPLACE() for migrations:** Will corrupt serialized data (page builders, widgets, meta). Always use WP-CLI.
- **Editing template files when templates are saved in database:** Block theme templates saved via FSE take precedence over .html files. Check database first.
- **Treating FSE like a page builder:** FSE is site-wide architecture, not per-page design. Understand template hierarchy.
- **Ignoring autoload from start:** Waiting until performance degrades to address autoload is reactive. Monitor from day one.
- **Skipping --dry-run on WP-CLI:** One command can modify thousands of rows. Always preview first.
- **Overusing global styles in theme.json:** Too many global styles make exceptions difficult. Start minimal, add as needed.

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Local WordPress environment | Custom Docker compose, LAMP stack setup | @wordpress/env | Automatic plugin/theme mounting, WordPress core updates, standard ports, zero config |
| Asset compilation | Custom webpack/Babel config | @wordpress/scripts | Maintained by WordPress core team, includes all required loaders, automatic WordPress dependency extraction |
| Database search-replace | PHP scripts, SQL REPLACE() | WP-CLI search-replace | Handles serialized data correctly, option_name preservation, GUID protection, batch processing |
| Autoload detection | Manual SQL queries in production | Query Monitor plugin | Real-time profiling, query attribution, autoload size in admin bar, no production risk |
| Theme.json generation | Manual CSS custom properties | theme.json configuration | Auto-generates CSS variables + utility classes, editor integration, consistent API |
| Plugin boilerplate | From-scratch structure | WordPress Plugin Boilerplate | WordPress Coding Standards, internationalization setup, uninstall hooks, OOP structure |

**Key insight:** WordPress ecosystem has matured; official tooling (wp-env, wp-scripts, WP-CLI) now handles edge cases that custom solutions miss. The cost of custom solutions is maintenance burden and subtle bugs (especially serialization corruption). Use official tools unless you have extraordinary requirements.

## Common Pitfalls

### Pitfall 1: Serialized Data Corruption During Migration

**What goes wrong:** Using SQL REPLACE() or manual find-replace corrupts serialized data in wp_postmeta, wp_options, and other tables. Symptoms include broken widgets, lost page builder content, and plugin configuration loss.

**Why it happens:** PHP serialized data includes string length prefixes (e.g., `s:22:"https://example.com"`). Changing the string without updating the length indicator creates invalid serialization.

**Example of corruption:**

```sql
-- BEFORE (valid serialization)
a:1:{s:3:"url";s:22:"https://old-domain.com";}

-- AFTER SQL REPLACE() (CORRUPTED - length still says 22 but string is now 26 chars)
a:1:{s:3:"url";s:22:"https://localhost:8080";}
```

**How to avoid:**
1. **Never use SQL REPLACE()** for WordPress migrations
2. Always use WP-CLI: `wp search-replace 'old' 'new' --precise --dry-run`
3. Test on database copy first
4. Use `--export` flag to generate SQL file for review before applying
5. Page builders (Elementor, Beaver Builder, Divi) are especially vulnerable - verify post content after migration

**Warning signs:**
- Widgets disappear after migration
- Page builder content shows blank
- Plugin settings reset to defaults
- PHP unserialization warnings in error log

**Sources:**
- [Managing WP: Serialized Data](https://managingwp.io/2023/03/23/search-and-replace-on-a-wordpress-database-and-dealing-with-serialized-data/)
- [WordPress Database URL Migration Guide](https://wppoland.com/en/how-to-update-urls-in-the-wordpress-database-when-the-site-is-moved-to-a-new-domain/)

### Pitfall 2: Autoloaded Data Creep (wp_options Bloat)

**What goes wrong:** Autoloaded data grows beyond 800KB-1MB, causing slow admin panel, long TTFB, and degraded performance. Every page load queries all autoloaded options, so size directly impacts performance.

**Why it happens:** Plugins store settings with `autoload='yes'` by default, transients accumulate, redirect plugins create bloat, and no monitoring exists until performance degrades.

**How to avoid:**
1. **Set baseline immediately:** Check autoload size in fresh install
2. **Monitor continuously:** Install Query Monitor from day one
3. **Set threshold alerts:** Autoload should stay under 800KB (ideal: 300-800KB)
4. **Review plugin impact:** Check autoload size before/after plugin activation
5. **Clean transients regularly:** `wp transient delete --all --expired`
6. **Audit autoload data:** Run SQL query weekly during active development

**Detection query:**

```sql
-- Total autoloaded size (WordPress 6.6+ with new autoload values)
SELECT ROUND(SUM(LENGTH(option_value)) / 1024, 2) AS autoloaded_size_kb
FROM wp_options
WHERE autoload IN ('yes', 'on', 'auto-on', 'auto');

-- Top 10 autoloaded items by size
SELECT option_name, ROUND(LENGTH(option_value) / 1024, 2) AS size_kb
FROM wp_options
WHERE autoload IN ('yes', 'on', 'auto-on', 'auto')
ORDER BY LENGTH(option_value) DESC
LIMIT 10;
```

**Warning signs:**
- Admin panel loads slowly (>2 seconds)
- High TTFB (Time To First Byte)
- Query Monitor shows wp_options query >500ms
- Database query count spikes after plugin installations

**Remediation:**

```bash
# Check current size
wp option list --autoload=yes --format=total-bytes

# Identify large options
wp db query "SELECT option_name, LENGTH(option_value) AS size FROM wp_options WHERE autoload='yes' ORDER BY size DESC LIMIT 10;"

# Disable autoload for specific option
wp option update problematic_option_name --autoload=no

# Clean expired transients
wp transient delete --all --expired
```

**Sources:**
- [Pantheon: Optimize wp_options Table](https://docs.pantheon.io/optimize-wp-options-table-autoloaded-data)
- [Kinsta: Clean up Autoloaded Data](https://kinsta.com/blog/wp-options-autoloaded-data/)
- [WordPress Performance Optimization 2026](https://next3offload.com/blog/wordpress-performance-optimization/)

### Pitfall 3: FSE Template Precedence Confusion

**What goes wrong:** Editing theme .html template files but seeing no changes, or changes appearing initially then disappearing. Templates saved via Site Editor take precedence over file system templates.

**Why it happens:** WordPress stores FSE template edits in the database (wp_posts with post_type='wp_template'). Database templates override file system templates. Developers edit files expecting changes, but database version is served.

**How to avoid:**
1. **Check database first:** Before editing template files, verify no database version exists
2. **Delete database templates:** Reset to file system version when needed
3. **Use version control for templates:** Keep .html files as source of truth
4. **Document FSE workflow:** Establish team convention for template editing

**Detection:**

```bash
# List all database-saved templates
wp post list --post_type=wp_template --format=table

# Delete specific template (resets to file system version)
wp post delete <template-id> --force

# Delete all customized templates (CAUTION: use on fresh installs only)
wp post delete $(wp post list --post_type=wp_template --format=ids) --force
```

**Warning signs:**
- File edits don't appear on frontend
- Git shows template changes but site unchanged
- Template looks different between environments
- "Modified" badge in Site Editor for templates you didn't edit

**Sources:**
- [Full Site Editing: Troubleshooting Block Themes](https://fullsiteediting.com/lessons/troubleshooting-block-themes/)
- [Dev Considerations for FSE Projects](https://www.briancoords.com/dev-considerations-for-a-new-fse-project/)

### Pitfall 4: theme.json Syntax Errors Breaking Site

**What goes wrong:** Typo in theme.json (missing comma, extra comma, unquoted string) breaks entire site styling and editor. WordPress provides minimal error feedback.

**Why it happens:** JSON is unforgiving; one syntax error invalidates entire file. Common mistakes: trailing commas after last object property, missing commas between properties, missing quotes around strings, incorrect nesting.

**Common syntax errors:**

```json
// ERROR: Trailing comma after last property
{
  "version": 3,
  "settings": {
    "color": {
      "palette": []
    }, // ← Remove this comma
  }
}

// ERROR: Missing comma between properties
{
  "version": 3
  "settings": {} // ← Missing comma after previous property
}

// ERROR: Unquoted string (except true/false)
{
  "version": 3,
  "settings": {
    "color": {
      "custom": yes // ← Should be true (no quotes) or "yes" (quoted string)
    }
  }
}
```

**How to avoid:**
1. **Use JSON validator:** Paste into jsonlint.com before saving
2. **Enable JSON linting in editor:** VS Code, PHPStorm have built-in validators
3. **Version control theme.json:** Git diff catches inadvertent changes
4. **Test in local first:** Never edit theme.json directly on production
5. **Use JSON schema:** Add `"$schema": "https://schemas.wp.org/trunk/theme.json"` for IDE autocomplete

**Warning signs:**
- Site editor won't load
- Frontend styling disappears
- Theme shows as broken in Appearance > Themes
- White screen on site editor

**Sources:**
- [Full Site Editing: Troubleshooting Block Themes](https://fullsiteediting.com/lessons/troubleshooting-block-themes/)
- [Kinsta: theme.json Customization](https://kinsta.com/blog/theme-json/)

### Pitfall 5: Port Conflicts with Existing Docker Containers

**What goes wrong:** `wp-env start` fails with "port 8888 already in use" or "port 3306 already in use" error. Docker can't bind to ports occupied by other containers or services.

**Why it happens:** Previous wp-env instances, other Docker projects, or local MySQL/Apache services occupy the default ports.

**How to avoid:**
1. **Stop previous wp-env instances:** Run `wp-env stop` in old project directories
2. **Stop all Docker containers:** `docker stop $(docker ps -q)` when unsure
3. **Check port usage:** `lsof -i :8888` or `netstat -an | grep 8888`
4. **Configure custom ports:** Use .wp-env.json to specify alternative ports
5. **Document running environments:** Keep list of active projects and their ports

**Custom port configuration:**

```json
// .wp-env.json
{
  "port": 8080,
  "testsPort": 8081,
  "mysqlPort": 3307
}
```

**Detection and resolution:**

```bash
# Check what's using port 8888 (macOS/Linux)
lsof -i :8888

# Kill process using port
kill -9 <PID>

# Stop all Docker containers
docker stop $(docker ps -q)

# Remove stopped containers
docker container prune
```

**Warning signs:**
- wp-env start fails with port error
- Can't access http://localhost:8888
- Docker shows "bind: address already in use"

**Sources:**
- [WordPress wp-env Documentation](https://developer.wordpress.org/block-editor/getting-started/devenv/get-started-with-wp-env/)
- [Plugin Machine: Using WordPress env](https://pluginmachine.com/using-wordpress-env-for-docker-based-local-development/)

## Code Examples

Verified patterns from official sources:

### Checking Autoload Size via WP-CLI

```bash
# Total autoloaded data size in bytes
wp db query "SELECT SUM(LENGTH(option_value)) as autoload_bytes FROM wp_options WHERE autoload IN ('yes', 'on', 'auto-on', 'auto');"

# Convert to KB for readability
wp db query "SELECT ROUND(SUM(LENGTH(option_value)) / 1024, 2) as autoload_kb FROM wp_options WHERE autoload IN ('yes', 'on', 'auto-on', 'auto');"

# List top 20 autoloaded options by size
wp db query "SELECT option_name, ROUND(LENGTH(option_value) / 1024, 2) AS size_kb FROM wp_options WHERE autoload IN ('yes', 'on', 'auto-on', 'auto') ORDER BY LENGTH(option_value) DESC LIMIT 20;"
```

**Success criteria verification:**

```bash
# Check if autoload is under 800KB (800000 bytes)
AUTOLOAD_SIZE=$(wp db query "SELECT SUM(LENGTH(option_value)) FROM wp_options WHERE autoload IN ('yes', 'on', 'auto-on', 'auto');" --skip-column-names)

if [ "$AUTOLOAD_SIZE" -lt 800000 ]; then
  echo "✓ Autoload size is under 800KB ($AUTOLOAD_SIZE bytes)"
else
  echo "✗ Autoload size exceeds 800KB ($AUTOLOAD_SIZE bytes)"
fi
```

### Database Import with Search-Replace

```bash
# Step 1: Import database (from wp-content or project root)
wp db import smartvarme_wp_zmmon.sql

# Step 2: Verify import
wp db check

# Step 3: Update site URL (dry-run first)
wp search-replace 'https://smartvarme.no' 'http://localhost:8080' \
  --dry-run \
  --all-tables \
  --precise \
  --report-changed-only

# Step 4: If dry-run looks good, run actual replacement
wp search-replace 'https://smartvarme.no' 'http://localhost:8080' \
  --all-tables \
  --precise \
  --skip-columns=guid

# Step 5: Flush cache and rewrite rules
wp cache flush
wp rewrite flush
```

### Minimal Block Theme Enqueue (functions.php)

```php
<?php
/**
 * Smartvarme Theme Functions
 */

// Enqueue compiled assets from @wordpress/scripts
function smartvarme_enqueue_assets() {
    // Check if asset file exists (generated by wp-scripts build)
    $asset_file = get_template_directory() . '/build/index.asset.php';

    if ( file_exists( $asset_file ) ) {
        $asset = include $asset_file;

        // Enqueue JavaScript
        wp_enqueue_script(
            'smartvarme-scripts',
            get_template_directory_uri() . '/build/index.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        // Enqueue frontend styles
        wp_enqueue_style(
            'smartvarme-styles',
            get_template_directory_uri() . '/build/style.css',
            [],
            $asset['version']
        );
    }
}
add_action( 'wp_enqueue_scripts', 'smartvarme_enqueue_assets' );

// Enqueue editor styles
function smartvarme_enqueue_editor_assets() {
    $asset_file = get_template_directory() . '/build/index.asset.php';

    if ( file_exists( $asset_file ) ) {
        $asset = include $asset_file;

        wp_enqueue_style(
            'smartvarme-editor-styles',
            get_template_directory_uri() . '/build/editor.css',
            [],
            $asset['version']
        );
    }
}
add_action( 'enqueue_block_editor_assets', 'smartvarme_enqueue_editor_assets' );
```

**Source:** [WordPress scripts documentation](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/)

### Plugin Boilerplate Main File Structure

```php
<?php
/**
 * Plugin Name: Smartvarme Core
 * Plugin URI: https://smartvarme.no
 * Description: Core business logic for Smartvarme site
 * Version: 1.0.0
 * Author: Smartvarme Team
 * Author URI: https://smartvarme.no
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: smartvarme-core
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Plugin version (for cache busting)
define( 'SMARTVARME_CORE_VERSION', '1.0.0' );

// Plugin path
define( 'SMARTVARME_CORE_PATH', plugin_dir_path( __FILE__ ) );

// Plugin URL
define( 'SMARTVARME_CORE_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_smartvarme_core() {
    require_once SMARTVARME_CORE_PATH . 'includes/class-smartvarme-core-activator.php';
    Smartvarme_Core_Activator::activate();
}
register_activation_hook( __FILE__, 'activate_smartvarme_core' );

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_smartvarme_core() {
    require_once SMARTVARME_CORE_PATH . 'includes/class-smartvarme-core-deactivator.php';
    Smartvarme_Core_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_smartvarme_core' );

/**
 * Begin plugin execution.
 */
function run_smartvarme_core() {
    require_once SMARTVARME_CORE_PATH . 'includes/class-smartvarme-core.php';
    $plugin = new Smartvarme_Core();
    $plugin->run();
}
run_smartvarme_core();
```

**Source:** [WordPress Plugin Boilerplate](https://github.com/DevinVinson/WordPress-Plugin-Boilerplate)

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| Local by Flywheel, MAMP, XAMPP | @wordpress/env (Docker-based) | ~2020-2021 | Official WordPress recommendation; Docker ensures environment consistency |
| Custom webpack configs | @wordpress/scripts | ~2019-2020 | Zero-config by default; maintained by WordPress core team |
| theme.json v1/v2 | theme.json v3 | WordPress 6.6 (2024) | Use version 3 for new themes; improved schema and features |
| Autoload any size | Autoload optimization (target <800KB) | WordPress 6.6 (2024) | WordPress 6.6+ auto-disables large autoload; proactive monitoring essential |
| SQL REPLACE() for migrations | WP-CLI search-replace | Always critical | Never use SQL REPLACE; WP-CLI is only safe method |
| Classic themes | Block themes (FSE) | WordPress 5.9+ (2022) | Block themes are future; classic themes still supported but not recommended for new projects |
| PHP 7.x | PHP 8.3+ | Current (2026) | WordPress 6.8 actively supports PHP 8.3; 7.x reaches end of security support |
| MariaDB 10.5 or older | MariaDB 10.6+ (recommend 10.11 LTS) | WordPress recommendation updated 2025 | 10.11 is LTS with extended support |

**Deprecated/outdated:**
- **Local by Flywheel:** Still works but not officially recommended; wp-env is standard
- **MAMP/XAMPP/WAMP:** Traditional stacks viable but Docker preferred for consistency
- **theme.json version 1 and 2:** Use version 3 for WordPress 6.6+
- **PHP 7.2-7.4:** End of security support; WordPress 6.8 requires 8.3 for new installs
- **MySQL 5.5-5.7:** Use MySQL 8.0+ or MariaDB 10.6+ for new installs
- **--allow-root flag for WP-CLI:** wp-env provides proper user context; avoid running WP-CLI as root

## Open Questions

### 1. Docker Environment Choice: wp-env vs. docker-compose

**What we know:**
- @wordpress/env is officially recommended and provides zero-config setup
- docker-compose offers more control for complex multi-service setups
- Project requires standard WordPress environment without additional services

**What's unclear:**
- Whether existing production environment has custom Docker requirements
- If development team has docker-compose expertise vs. wp-env preference

**Recommendation:**
Start with **@wordpress/env** for rapid setup. It handles 95% of WordPress development needs and integrates seamlessly with @wordpress/scripts. Switch to docker-compose only if specific requirements emerge (e.g., custom caching layers, additional services, specific PHP extensions). Can migrate later if needed - not a permanent decision.

### 2. Database Size and Import Performance

**What we know:**
- Database file is 255MB (smartvarme_wp_zmmon.sql)
- Large databases can have slow search-replace operations
- WP-CLI handles large databases but may require increased PHP memory

**What's unclear:**
- Whether database includes media/uploads table bloat
- If memory limits will require adjustment during import

**Recommendation:**
- Set PHP memory_limit to at least 512MB for wp-env (default usually sufficient)
- Run `wp db import` before search-replace (import is faster than piped operations)
- Use `--precise` flag on search-replace even though it's slower - correctness over speed
- Consider `--skip-tables=wp_commentmeta` if site doesn't use comments (reduce search-replace time)
- Monitor import with `time` command: `time wp db import database.sql`

### 3. Block Theme vs. Hybrid Theme Approach

**What we know:**
- Project specifies "block theme with FSE" (INFRA-02)
- Existing site is classic theme
- Block themes have steeper learning curve but are WordPress future

**What's unclear:**
- Whether team has FSE experience
- If content editors prefer visual Site Editor vs. code-based template editing

**Recommendation:**
**Go full block theme** (no hybrid). Reasons:
1. PROJECT.md specifies "Content-first rebuild" - FSE is built for content editors
2. Hybrid themes complicate architecture (two mental models)
3. Learning investment pays off; FSE is WordPress direction
4. Easier to add PHP functions later than migrate hybrid → block
5. theme.json provides design system foundation that benefits entire project

If team lacks FSE experience, budget extra time in Phase 1 for learning curve. The alternative (classic/hybrid theme) creates technical debt.

### 4. Autoload Optimization Timing

**What we know:**
- Target is <800KB autoloaded data (INFRA-05 success criteria)
- Imported database likely has existing autoload bloat
- WordPress 6.6+ improves autoload handling automatically

**What's unclear:**
- Current autoload size in production database
- Whether optimization should happen pre-import or post-import

**Recommendation:**
**Two-phase approach:**
1. **Phase 1 (Foundation):** Measure baseline autoload size post-import. Document current size.
2. **Immediate optimization if >1MB:** If autoload exceeds 1MB after import, this is blocking issue - optimize in Phase 1.
3. **Deferred optimization if 800KB-1MB:** If between thresholds, document as tech debt for Phase 2.
4. **Monitoring setup:** Install Query Monitor in Phase 1 regardless of size.

Success criteria says <800KB, but 1MB is "likely impacting performance" threshold per research. Suggest success criteria interpretation: "autoload under 800KB OR documented optimization plan if between 800KB-1MB."

### 5. Plugin Development: Monolithic vs. Modular Structure

**What we know:**
- Custom smartvarme-core plugin required (INFRA-07)
- Project mentions "business logic" separation from theme
- Plugin purpose undefined in Phase 1 scope

**What's unclear:**
- What business logic goes in plugin vs. theme
- Whether plugin will have admin UI, REST API endpoints, custom post types, etc.

**Recommendation:**
**Create minimal plugin structure in Phase 1:**
- Basic plugin boilerplate (activation/deactivation hooks)
- Placeholder includes/ folder for future classes
- No functionality yet - architecture only

**Defer functionality to later phases.** Phase 1 goal is "plugin exists and activates without errors" (success criteria). Actual business logic implementation belongs in Phase 2+ after architecture is validated.

**Plugin vs. Theme decision matrix:**
- **Plugin:** Business logic, custom post types, REST endpoints, admin functionality, anything that should persist across theme changes
- **Theme:** Presentation, block patterns, template parts, design system, anything specific to visual design

When in doubt, put it in the plugin. Themes should be swappable; plugins contain site-specific functionality.

## Sources

### Primary (HIGH confidence)

**Official WordPress Documentation:**
- [WordPress Server Requirements](https://wordpress.org/about/requirements/) - PHP, MariaDB/MySQL versions
- [WordPress Block Editor: wp-env](https://developer.wordpress.org/block-editor/getting-started/devenv/get-started-with-wp-env/) - Local environment setup
- [WordPress Block Editor: wp-scripts](https://developer.wordpress.org/block-editor/getting-started/devenv/get-started-with-wp-scripts/) - Build system configuration
- [WordPress Block Editor: theme.json](https://developer.wordpress.org/block-editor/how-to-guides/themes/global-settings-and-styles/) - FSE configuration
- [WP-CLI search-replace](https://developer.wordpress.org/cli/commands/search-replace/) - Database migration commands
- [WordPress Plugin Best Practices](https://developer.wordpress.org/plugins/plugin-basics/best-practices/) - Plugin development standards

**Official Docker Images:**
- [WordPress Docker Hub](https://hub.docker.com/_/wordpress) - Official WordPress image tags and configuration
- [MariaDB Docker Hub](https://hub.docker.com/_/mariadb) - Official MariaDB image versions
- [Docker Samples: WordPress](https://docs.docker.com/reference/samples/wordpress/) - docker-compose examples

**WordPress Hosting Team:**
- [WordPress 6.8 Server Compatibility](https://make.wordpress.org/hosting/2025/04/16/wordpress-6-8-server-compatibility/) - Official compatibility matrix
- [WordPress 6.6 Server Compatibility](https://make.wordpress.org/hosting/2024/07/10/wordpress-6-6-server-compatibility/) - PHP/MariaDB requirements

### Secondary (MEDIUM confidence)

**Performance Optimization:**
- [Pantheon: Optimize wp_options Table](https://docs.pantheon.io/optimize-wp-options-table-autoloaded-data) - Autoload optimization strategies
- [Kinsta: wp_options Autoloaded Data](https://kinsta.com/blog/wp-options-autoloaded-data/) - Autoload analysis and cleanup
- [WordPress Performance Optimization 2026](https://next3offload.com/blog/wordpress-performance-optimization/) - Current performance best practices

**Database Migration:**
- [Managing WP: Serialized Data](https://managingwp.io/2023/03/23/search-and-replace-on-a-wordpress-database-and-dealing-with-serialized-data/) - Serialization deep dive
- [WordPress Database URL Migration Guide](https://wppoland.com/en/how-to-update-urls-in-the-wordpress-database-when-the-site-is-moved-to-a-new-domain/) - Complete migration workflow

**Block Theme Development:**
- [Full Site Editing: Creating Block Themes](https://fullsiteediting.com/lessons/creating-block-based-themes/) - Block theme structure
- [Full Site Editing: Troubleshooting](https://fullsiteediting.com/lessons/troubleshooting-block-themes/) - Common FSE issues
- [Kinsta: theme.json Customization](https://kinsta.com/blog/theme-json/) - theme.json examples and guide
- [WP Poland: FSE vs Classic Themes 2026](https://wppoland.com/en/classic-vs-block-themes-fse-guide/) - FSE comparison and guidance

**Docker Development:**
- [Docker: How to Dockerize WordPress](https://www.docker.com/blog/how-to-dockerize-wordpress/) - Official Docker guidance
- [Plugin Machine: wp-env for Local Development](https://pluginmachine.com/using-wordpress-env-for-docker-based-local-development/) - wp-env tutorial
- [How to Make WordPress Local in 2026](https://awp.agency/en/blog/how-to-make-wordpress-local-in-2026-definitive-guide-and-comparison-of-environments/) - Environment comparison

**Plugin Development:**
- [WordPress Plugin Boilerplate](https://github.com/DevinVinson/WordPress-Plugin-Boilerplate) - Standard plugin structure
- [WPPB.me](https://wppb.me/) - Plugin boilerplate generator
- [ColorWhistle: Plugin Development Best Practices 2026](https://colorwhistle.com/wordpress-plugin-development-best-practices/) - Modern plugin patterns

### Tertiary (LOW confidence - verify during implementation)

**Build Tools:**
- [Kinsta: wp-scripts Development](https://kinsta.com/blog/wp-scripts-development/) - Advanced wp-scripts usage
- [Sam Hermes: Customize wp-scripts Config](https://samhermes.com/posts/customize-default-wp-scripts-config/) - Custom webpack configuration

**Community Resources:**
- [Brian Coords: FSE Project Considerations](https://www.briancoords.com/dev-considerations-for-a-new-fse-project/) - FSE development tips
- [WP Beginner: Best FSE Themes 2026](https://www.wpbeginner.com/showcase/best-wordpress-full-site-editing-themes/) - FSE theme examples

## Metadata

**Confidence breakdown:**
- **Standard stack: HIGH** - Official WordPress tools (@wordpress/env, @wordpress/scripts, WP-CLI) are documented and widely adopted
- **Architecture: HIGH** - Block theme structure is officially documented, patterns verified from WordPress.org
- **Database migration: HIGH** - WP-CLI serialization handling is verified in official docs and community consensus
- **Pitfalls: MEDIUM-HIGH** - Serialization corruption and autoload bloat confirmed by multiple authoritative sources; FSE pitfalls from practitioner experience
- **Version compatibility: HIGH** - PHP 8.3, MariaDB 10.11, WordPress 6.8 compatibility verified from official WordPress.org sources

**Research date:** 2026-02-11
**Valid until:** 2026-03-31 (stable WordPress ecosystem; revalidate if WordPress 6.9+ releases)

**Notes for planner:**
- Phase 1 is infrastructure only - no custom blocks, no content migration, no styling beyond theme.json
- Success criteria verification commands provided in Code Examples section
- Autoload threshold (800KB) may need interpretation if existing database between 800KB-1MB
- Plugin functionality undefined - Phase 1 creates structure only, defer business logic
- Team FSE experience unknown - planner should budget learning time in task estimates
