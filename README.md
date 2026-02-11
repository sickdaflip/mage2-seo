# FlipDev SEO Extension for Magento 2

[![Magento 2](https://img.shields.io/badge/Magento-2.4.8+-orange.svg)](https://magento.com/)
[![PHP](https://img.shields.io/badge/PHP-8.4+-blue.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

Professional SEO toolkit for Magento 2 built for modern PHP. Comprehensive features for structured data, meta optimization, sitemap generation, and Google integrations.

## Features

### Meta Tags & Robots Control
- **NOINDEX/FOLLOW** for filtered category pages, search results, and advanced search
- **Meta Robots Override** per product and category via custom attributes
- **Remove Meta Keywords** tag globally
- **Pagination Canonical Fix** for paginated category pages

### Canonical URL Management
- **Canonical Product Redirects** - Optional 301 redirect to product canonical URL
- **CMS Page Canonical Tags** - Automatic canonical meta tags for CMS pages
- **Pagination Canonical Handling** - Correct canonical tags on paginated categories

### Dynamic Meta Templates
Configurable title and meta description templates with placeholder support:
- **Product Title & Description** - Placeholders: `[name]`, `[sku]`, `[store]`, `[attribute_code]`
- **Category Title & Description** - Placeholders: `[name]`, `[original_name]`, `[store]`
- **CMS Page Title & Description**
- **Contact Page Title & Description**

### Category H1 Override
Separate H1 heading field (`flipdevseo_heading`) independent of the category navigation name.

### Discontinued Product Redirects
Configurable redirect behavior when products are disabled:
- No Redirect (404)
- 301 to parent category
- 301 to homepage
- 301 to specific product (via SKU)

Also handles disabled categories with automatic redirects.

### Structured Data (JSON-LD)

| Schema | Description |
|--------|-------------|
| **Organization** | Business address (street, locality, region, postal code) |
| **Social Profiles** | Link social media accounts to your organization |
| **Product** | Brand, GTIN/EAN, MPN, condition, color, material, energy efficiency, images, price, reviews, shipping info, return policy |
| **Category** | CollectionPage schema for category pages |
| **Breadcrumbs** | BreadcrumbList schema for navigation |
| **WebSite** | WebSite schema with Sitelinks Searchbox and alternate site names |
| **LocalBusiness** | Configurable business type, opening hours, price range, geo coordinates |
| **FAQ** | FAQPage schema from product/category FAQ attributes |
| **Twitter Cards** | Rich product cards for Twitter sharing |

### OpenGraph Meta Tags
- Open Graph tags for social media sharing (Facebook, LinkedIn, etc.)
- Configurable default OG image (recommended: 1200x630px)
- Automatic type detection (website, product, article)

### Hreflang Tags
- Multi-language/multi-region support via hreflang tags
- Automatic language/region link generation across store views
- Integration with XML sitemap

### Advanced XML Sitemap
- **Separate sitemaps** for products, categories, and CMS pages
- **Product images** (`image:image` tags for Google Image Search)
- **Product videos** (`video:video` tags for YouTube/Vimeo)
- **Hreflang support** within sitemaps
- **XSL stylesheet** for browser-friendly display
- **Configurable priorities and change frequencies** per content type
- **Filtering**: Exclude out-of-stock, disabled, or non-visible products
- **Pagination**: Automatic splitting when exceeding max URLs per file (default: 50,000)
- **Cron-based generation**: Configurable schedule (hour selection)
- **Manual generation**: Via CLI command or admin button

### Robots.txt Editor
- Edit robots.txt content directly in admin configuration
- Auto-append sitemap URLs
- Add custom sitemap URLs
- Generate via admin button or CLI command

### Google Integrations
- **Google Content Grouping** - Track products, categories, and CMS pages as separate content groups in Analytics
- **Google Trusted Store** - Trust badges on product and checkout pages with configurable badge position, Shopping account ID, country, language, and shipping/delivery estimates

### CLI Commands

```bash
# Generate XML sitemaps (all stores or specific store)
php bin/magento flipdev:sitemap:generate
php bin/magento flipdev:sitemap:generate -s 1

# Generate robots.txt from configuration
php bin/magento flipdev:robots:generate
```

### Admin Features
- **SEO Checklist** - Dashboard showing configuration status and recommendations
- **Robots.txt Generation Button** - One-click generation from admin
- **Comprehensive Configuration** - All features configurable under Stores > Configuration > FlipDev SEO

## Requirements

- **Magento**: 2.4.8+
- **PHP**: 8.4+

## Installation

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

## Configuration

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
   - Enable breadcrumbs, product, and category structured data

4. **Set Up XML Sitemap**
   - Enable sitemap generation for products, categories, and CMS pages
   - Configure priorities and change frequencies
   - Enable cron-based auto-generation or use CLI commands

5. **Configure Robots.txt**
   - Edit robots.txt content in admin
   - Enable auto-sitemap URL inclusion

## Usage Examples

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

### Product Structured Data

Configurable attributes for rich product schema:
- **Brand** - Map to any product attribute
- **GTIN/EAN** - Map to barcode attribute
- **MPN** - Map to manufacturer part number attribute
- **Condition** - New, Used, Refurbished, Damaged
- **Shipping** - Default rate, transit days, destination country
- **Return Policy** - Return window, fees, country

## Custom Attributes

### Products
| Attribute | Description |
|-----------|-------------|
| `flipdevseo_discontinued` | Redirect behavior for disabled products |
| `flipdevseo_discontinued_product` | Target SKU for product redirects |
| `flipdevseo_metarobots` | Meta robots override |

### Categories
| Attribute | Description |
|-----------|-------------|
| `flipdevseo_heading` | Custom H1 heading (independent of navigation name) |
| `flipdevseo_metarobots` | Meta robots override |

## Code Quality

- **PHP 8.4+** - Latest language features
- **Strict Types** - Type safety throughout
- **Null-Safe** - Comprehensive null handling
- **PSR-3 Logging** - Custom logger writing to `var/log/flipdev_seo.log`
- **Error Handling** - Graceful degradation

## Contributing

Contributions are welcome! Please read our [Contributing Guide](CONTRIBUTING.md) first.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Author

**Philipp Breitsprecher**
- GitHub: [@sickdaflip](https://github.com/sickdaflip)
- Email: philippbreitsprecher@gmail.com

## Support

- **Email**: philippbreitsprecher@gmail.com
- **Issues**: [GitHub Issues](https://github.com/sickdaflip/mage2-seo/issues)

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a list of changes.
