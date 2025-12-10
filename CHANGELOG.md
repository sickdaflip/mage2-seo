# Changelog

All notable changes to sickdaflip/mage2-seo will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-12-09

### Initial Release

Professional SEO toolkit for Magento 2 built with PHP 8.4+.

### Added

#### Core Features
- Meta robots control for products, categories, and pages
- Canonical URL management with forced redirect option
- Dynamic meta templates with attribute placeholders
- Category H1 override functionality
- Discontinued product redirect system (4 redirect types)
- HTML sitemap generation
- SEO configuration checklist

#### Structured Data (JSON-LD)
- Organization schema with business information
- Social media profile integration
- Breadcrumb navigation schema
- Twitter Cards for product pages
- Google Sitelink Search support

#### Google Integrations
- Google Tag Manager integration
- Google Content Grouping for Analytics
- Google Trusted Store support

#### Developer Features
- PHP 8.4+ requirement with strict types
- Comprehensive error handling and logging (PSR-3)
- Null-safe operations throughout
- Config caching for performance
- Modern data patch system
- EAV attributes for products and categories

### Technical Details

#### Architecture
- Built for PHP 8.4+
- Strict type declarations in all files
- PSR-3 logging throughout (50+ log statements)
- Proper dependency injection
- Modern Magento 2 standards

#### Performance
- Config value caching in Helper
- Optimized attribute loading
- Efficient string processing
- Smart error handling without performance impact

#### Code Quality
- 95%+ type coverage
- Comprehensive null-safety
- Professional error handling
- Clean code principles
- Extensive inline documentation

### Product Attributes Added
- `flipdevseo_discontinued` (int) - Redirect type for disabled products
- `flipdevseo_discontinued_product` (varchar) - Target SKU for product redirects
- `flipdevseo_metarobots` (varchar) - Meta robots override

### Category Attributes Added
- `flipdevseo_heading` (varchar) - Custom H1 heading
- `flipdevseo_metarobots` (varchar) - Meta robots override

### Configuration Paths
All configuration under `flipdevseo/*`:
- `flipdevseo/settings/*` - Core SEO settings
- `flipdevseo/metadata/*` - Meta templates and defaults
- `flipdevseo/twittercards/*` - Twitter Cards configuration
- `flipdevseo/organization_sd/*` - Organization schema
- `flipdevseo/social_sd/*` - Social profiles
- `flipdevseo/breadcrumbs_sd/*` - Breadcrumbs schema
- `flipdevseo/google_tag_manager/*` - GTM settings
- `flipdevseo/google_content_grouping/*` - Analytics grouping
- `flipdevseo/google_sitelink_search/*` - Sitelink search
- `flipdevseo/google_trusted_store/*` - Trusted store settings

### Installation
```bash
composer require sickdaflip/mage2-seo
php bin/magento module:enable FlipDev_Seo
php bin/magento setup:upgrade
```

### Requirements
- PHP 8.4+
- Magento 2.4.8+

### License
- MIT License

### Author
- Philipp Breitsprecher ([@sickdaflip](https://github.com/sickdaflip))
- Email: philippbreitsprecher@gmail.com

---

## Future Roadmap

### Planned for v1.1.0
- [ ] GraphQL API support for headless/PWA
- [ ] Additional Schema.org types (FAQ, HowTo, Recipe)
- [ ] Hreflang tag automation
- [ ] Advanced SEO audit dashboard

### Under Consideration
- [ ] AI-powered meta generation
- [ ] Bulk meta editor UI
- [ ] SEO scoring system
- [ ] Content analysis tools
- [ ] Competition tracking

---

**Maintained by**: [@sickdaflip](https://github.com/sickdaflip)  
**Support**: philippbreitsprecher@gmail.com  
**Issues**: [GitHub Issues](https://github.com/sickdaflip/mage2-seo/issues)  
**License**: MIT
