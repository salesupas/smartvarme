# Smartvarme WordPress Website

Custom WordPress website for Smartvarme, built with WooCommerce for e-commerce functionality.

**Developed by:** [SalesUp AS](https://salesup.no) - SalesUp Team

## 🎯 Project Overview

Smartvarme is a Norwegian e-commerce platform specializing in heating products, stoves, and energy-efficient solutions. This repository contains the custom-developed code including themes and plugins.

## 🛠️ Technology Stack

- **CMS:** WordPress 6.x
- **E-commerce:** WooCommerce
- **Base Theme:** Astra (parent theme - not in repo)
- **Custom Themes:**
  - `smartvarme-theme` - Block-based main theme
  - `astra-child` - Child theme with custom styling
- **Custom Plugin:** `smartvarme-core` - WooCommerce extensions and custom features
- **Build Tools:**
  - Webpack (via @wordpress/scripts)
  - SASS/SCSS
  - npm

## 📁 Repository Structure

```
wp-content/
├── themes/
│   ├── smartvarme-theme/     # Main custom block theme
│   │   ├── src/              # SCSS source files
│   │   ├── templates/        # Block templates
│   │   ├── patterns/         # Block patterns
│   │   └── parts/            # Template parts
│   │
│   └── astra-child/          # Child theme
│       ├── assets/sass/      # SCSS source files
│       ├── inc/              # PHP includes
│       ├── woocommerce/      # WooCommerce template overrides
│       └── build/            # Compiled assets (gitignored)
│
└── plugins/
    └── smartvarme-core/      # Custom plugin
        ├── includes/         # Core functionality
        ├── blocks/           # Custom Gutenberg blocks
        └── woocommerce/      # WooCommerce integrations

.planning/                    # Project documentation and planning
```

## 🚀 Quick Start (Development)

### Prerequisites

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Node.js 18+ and npm
- Composer
- WordPress 6.x
- WooCommerce 8.x

### Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/salesupas/smartvarme.git
   cd smartvarme
   ```

2. **Install WordPress core:**
   ```bash
   # Download WordPress (if not already installed)
   wp core download --locale=nb_NO

   # Create wp-config.php
   wp config create --dbname=your_db --dbuser=your_user --dbpass=your_pass

   # Install WordPress
   wp core install --url=your-url --title="Smartvarme" --admin_user=admin --admin_email=admin@example.com
   ```

3. **Install required plugins:**
   ```bash
   # Install WooCommerce
   wp plugin install woocommerce --activate

   # Install Astra parent theme
   wp theme install astra

   # Install other required plugins (see Required Plugins section)
   ```

4. **Build custom themes:**
   ```bash
   # Build smartvarme-theme
   cd wp-content/themes/smartvarme-theme
   npm install
   npm run build

   # Build astra-child
   cd ../astra-child
   npm install
   npm run build
   ```

5. **Activate themes:**
   ```bash
   wp theme activate astra-child
   ```

6. **Import content and settings:**
   - Import database dump (if available)
   - Configure WooCommerce settings
   - Set up payment gateways

## 📦 Required Third-Party Plugins

These plugins are **not** included in the repository and must be installed separately:

### Essential:
- **WooCommerce** - E-commerce functionality
- **Astra** - Parent theme
- **Advanced Custom Fields PRO** - Custom fields management
- **WP Rocket** - Caching and performance
- **Formidable Forms PRO** - Contact forms

### Optional but Recommended:
- **Wordfence Security** - Security
- **WP Mail SMTP** - Email delivery
- **Yoast SEO** - SEO optimization
- **WooCommerce Product Bundles** - Product bundling
- **DIBS Easy for WooCommerce** - Payment gateway (Norwegian)

## 🔧 Development Workflow

### Working with Themes

**smartvarme-theme:**
```bash
cd wp-content/themes/smartvarme-theme
npm run start    # Start development mode with watch
npm run build    # Build for production
```

**astra-child:**
```bash
cd wp-content/themes/astra-child
npm run watch    # Watch and compile SCSS
npm run build    # Build for production
```

### Working with Custom Plugin

The `smartvarme-core` plugin includes:
- Custom Gutenberg blocks (Energy Calculator, Product Comparison)
- WooCommerce extensions (Energy labels, Custom product fields)
- Performance optimizations
- Custom post types (FAQs)

Build blocks:
```bash
cd wp-content/plugins/smartvarme-core/blocks/energy-calculator
npm install
npm run build
```

## 🎨 Key Features

### Custom Features:
- **Energy Efficiency Labels** - Display energy ratings on products
- **Product Datasheets** - Link to product specification PDFs
- **Bundle Accessories** - Custom product bundling system
- **Responsive Product Grid** - Mobile-optimized (1 column on mobile)
- **Custom Energy Calculator** - Interactive energy savings calculator
- **FAQ System** - Custom post type with schema markup
- **Performance Optimizations** - WP Rocket integration

### WooCommerce Customizations:
- Custom product display layout
- Modified cart and checkout flow
- Energy label integration
- Custom stock and delivery info
- Product comparison functionality

## 🌐 Deployment to Production

### Option 1: Manual Deployment

1. **Upload custom code:**
   ```bash
   rsync -avz wp-content/themes/smartvarme-theme/ user@server:/path/to/wp-content/themes/smartvarme-theme/
   rsync -avz wp-content/themes/astra-child/ user@server:/path/to/wp-content/themes/astra-child/
   rsync -avz wp-content/plugins/smartvarme-core/ user@server:/path/to/wp-content/plugins/smartvarme-core/
   ```

2. **Install dependencies on server:**
   ```bash
   # SSH into server
   cd /path/to/wordpress

   # Install WordPress plugins
   wp plugin install woocommerce astra advanced-custom-fields-pro --activate

   # Build themes
   cd wp-content/themes/smartvarme-theme && npm install && npm run build
   cd ../astra-child && npm install && npm run build
   ```

3. **Configure environment:**
   - Set up wp-config.php with production database credentials
   - Configure WP Rocket caching
   - Set up SSL certificate
   - Configure payment gateways

### Option 2: Using CI/CD

Create `.github/workflows/deploy.yml` for automated deployment (example workflow available on request).

## 🔐 Environment Configuration

### Development
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### Production
```php
define('WP_DEBUG', false);
define('WP_CACHE', true);
define('DISALLOW_FILE_EDIT', true);
```

## 📝 Plugin Configuration

### Required ACF Field Groups:
- **Energy Efficiency Options** (Options page)
  - `energi_efficiency_options` - Repeater with energy label icons

- **Product Fields:**
  - `energi_selection` - Energy rating selection
  - `energi_link` - Link to energy label documentation
  - `produktdatablad` - PDF datasheet URL

### WooCommerce Settings:
- Currency: NOK (Norwegian Krone)
- Tax: 25% Norwegian VAT
- Shipping: Norway only (default)
- Payment Gateways: DIBS Easy, Nets, etc.

## 🐛 Troubleshooting

### Build Issues
```bash
# Clear npm cache and rebuild
rm -rf node_modules package-lock.json
npm install
npm run build
```

### Plugin Conflicts
Disable all plugins except WooCommerce and custom plugins, then re-enable one by one.

### Cache Issues
Clear WP Rocket cache:
```bash
wp rocket clean --confirm
```

## 📚 Documentation

Additional documentation available in `.planning/` directory:
- Project requirements
- Feature specifications
- Development roadmap
- Architecture decisions

## 🤝 Contributing

This is a private project. For internal development:

1. Create a feature branch
2. Make your changes
3. Build and test locally
4. Commit with clear messages (following conventional commits)
5. Push and create pull request

## 📄 License

Proprietary - All rights reserved by Smartvarme (client) and SalesUp AS (developer)

## 🆘 Support

For technical issues or questions:
- **Development Agency:** [SalesUp AS](https://salesup.no)
- **Client:** Smartvarme
- **Lead Developer:** SalesUp Team
- **AI Assistant:** Claude Sonnet 4.5

**Note:** Plugin is named "Smartvarme Core" after the client, as per standard practice. SalesUp AS is the development agency and maintains the codebase.

---

**Last Updated:** February 2026
**WordPress Version:** 6.x
**WooCommerce Version:** 8.x
**PHP Version:** 8.0+
