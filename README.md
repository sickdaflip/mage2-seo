# FlipDev SEO Extension for Magento 2

[![Magento 2](https://img.shields.io/badge/Magento-2.4.8+-orange.svg)](https://magento.com/)
[![PHP](https://img.shields.io/badge/PHP-8.4+-blue.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

Professional SEO toolkit for Magento 2 built for modern PHP. Comprehensive features for structured data, meta optimization, and Google integrations.

## 🚀 Features

### Core SEO Features
- ✅ **Meta Robots Control** - Granular indexing control for products, categories, and pages
- ✅ **Canonical URLs** - Automatic canonical management with optional forced redirects
- ✅ **Dynamic Meta Templates** - Page titles and descriptions using attribute placeholders
- ✅ **Category H1 Override** - Separate heading field independent of navigation labels
- ✅ **Discontinued Product Redirects** - 301 redirects for disabled products

### Structured Data (JSON-LD)
- ✅ **Organization Schema** - Business information and contact details
- ✅ **Social Profiles** - Link social media accounts to your website
- ✅ **Breadcrumbs** - Enhanced navigation with structured data
- ✅ **Twitter Cards** - Rich product cards for Twitter sharing
- ✅ **Sitelink Search** - Help Google understand your site search

### Google Integrations
- ✅ **Google Tag Manager** - Easy GTM integration
- ✅ **Google Content Grouping** - Track page types in Analytics
- ✅ **Google Trusted Store** - Support for Google's trusted store program

### Developer Features
- ✅ **HTML Sitemap** - Auto-generated sitemap for categories and CMS pages
- ✅ **SEO Checklist** - Admin dashboard showing configuration recommendations
- ✅ **Modern Architecture** - Built with PHP 8.4, strict types, comprehensive error handling

## 📋 Requirements

- **Magento**: 2.4.8+
- **PHP**: 8.4+ (minimum)

## 📦 Installation

### Via Composer

```bash
composer config repositories.flipdev/mage2-seo vcs https://github.com/sickdaflip/mage2-seo.git
composer require sickdaflip/mage2-seo
php bin/magento module:enable FlipDev_Seo
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
php bin/magento cache:flush
```

### Manual Installation

1. Create directory: `app/code/FlipDev/Seo`
2. Copy module files to this directory
3. Run installation commands:

```bash
php bin/magento module:enable FlipDev_Seo
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
php bin/magento cache:flush
```

## ⚙️ Configuration

Navigate to **Stores > Configuration > FlipDev SEO** or **Stores > FlipDev SEO > Settings**

### Quick Start

1. **Enable Core Features**
   - Set NOINDEX,FOLLOW for filtered pages and search results
   - Enable canonical redirects if needed
   - Configure CMS canonical tags

2. **Configure Meta Templates**
   ```
   Template: [name] - Buy Online from [store]
   Result: Professional Widget - Buy Online from My Store
   
   Available placeholders: [name], [sku], [store], [attribute_code]
   ```

3. **Add Structured Data**
   - Enable Organization data with business details
   - Add social profile URLs (one per line)
   - Enable breadcrumbs structured data

## 💡 Usage Examples

### Dynamic Page Titles

**Configuration:**
```
Default Product Title: [name], [sku] - [store]
```

**Result:**
```html
<title>Professional Widget, WDG-12345 - My Store</title>
```

### Discontinued Products

When a product is disabled, choose redirect behavior:
- **No Redirect (404)** - Return 404 error
- **301 to Category** - Redirect to parent category
- **301 to Homepage** - Redirect to store homepage
- **301 to Product** - Redirect to another product (enter SKU)

### Category SEO Heading

```
Category Name: "Kitchen Equipment" (used in navigation)
Category Heading: "Professional Kitchen Equipment & Supplies" (used as H1)
```

## 📊 Added Attributes

### Products
- `flipdevseo_discontinued` - Redirect behavior for disabled products
- `flipdevseo_discontinued_product` - Target SKU for product redirects
- `flipdevseo_metarobots` - Meta robots override

### Categories
- `flipdevseo_heading` - Custom H1 heading
- `flipdevseo_metarobots` - Meta robots override

## 🎯 Code Quality

Built with modern PHP practices:
- **PHP 8.4+** - Latest language features
- **Strict Types** - Type safety throughout
- **Null-Safe** - Comprehensive null handling
- **PSR-3 Logging** - Professional error tracking
- **Error Handling** - Graceful degradation

## 🤝 Contributing

Contributions are welcome! Please read our [Contributing Guide](CONTRIBUTING.md) first.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👤 Author

**Philipp Breitsprecher**
- GitHub: [@sickdaflip](https://github.com/sickdaflip)
- Email: philippbreitsprecher@gmail.com

## 💬 Support

- **Email**: philippbreitsprecher@gmail.com
- **Issues**: [GitHub Issues](https://github.com/sickdaflip/mage2-seo/issues)

## 🎉 Changelog

See [CHANGELOG.md](CHANGELOG.md) for a list of changes.

---

**Built with ❤️ by [@sickdaflip](https://github.com/sickdaflip)**

If you find this extension useful, please ⭐ star the repo!
