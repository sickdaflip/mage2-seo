<?php
/**
 * FlipDev SEO Extension
 * 
 * @category   FlipDev
 * @package    FlipDev_Seo
 * @author     Philipp Breitsprecher <philippbreitsprecher@gmail.com>
 * @copyright  Copyright (c) 2025 FlipDev
 * @license    MIT
 */

declare(strict_types=1);

namespace FlipDev\Seo\Api;

/**
 * SEO Configuration Interface
 * 
 * Provides typed methods for accessing SEO configuration values
 */
interface ConfigInterface
{
    /**
     * Check if NOINDEX is enabled for filtered category pages
     *
     * @return bool
     */
    public function isNoIndexForFilteredCategoriesEnabled(): bool;

    /**
     * Check if NOINDEX is enabled for search results
     *
     * @return bool
     */
    public function isNoIndexForSearchEnabled(): bool;

    /**
     * Check if NOINDEX is enabled for advanced search results
     *
     * @return bool
     */
    public function isNoIndexForAdvancedSearchEnabled(): bool;

    /**
     * Check if custom category H1 headings are enabled
     *
     * @return bool
     */
    public function isCategoryH1Enabled(): bool;

    /**
     * Check if canonical product redirects are enabled
     *
     * @return bool
     */
    public function isCanonicalRedirectEnabled(): bool;

    /**
     * Check if CMS canonical tags are enabled
     *
     * @return bool
     */
    public function isCmsCanonicalEnabled(): bool;

    /**
     * Check if meta keywords should be removed
     *
     * @return bool
     */
    public function shouldRemoveMetaKeywords(): bool;

    /**
     * Check if Twitter Cards are enabled
     *
     * @return bool
     */
    public function isTwitterCardsEnabled(): bool;

    /**
     * Get Twitter site handle
     *
     * @return string|null
     */
    public function getTwitterSite(): ?string;

    /**
     * Check if default product titles are enabled
     *
     * @return bool
     */
    public function isProductTitleEnabled(): bool;

    /**
     * Get product title template
     *
     * @return string|null
     */
    public function getProductTitleTemplate(): ?string;

    /**
     * Check if default product meta descriptions are enabled
     *
     * @return bool
     */
    public function isProductMetaDescEnabled(): bool;

    /**
     * Get product meta description template
     *
     * @return string|null
     */
    public function getProductMetaDescTemplate(): ?string;

    /**
     * Check if default category titles are enabled
     *
     * @return bool
     */
    public function isCategoryTitleEnabled(): bool;

    /**
     * Get category title template
     *
     * @return string|null
     */
    public function getCategoryTitleTemplate(): ?string;

    /**
     * Check if default category meta descriptions are enabled
     *
     * @return bool
     */
    public function isCategoryMetaDescEnabled(): bool;

    /**
     * Get category meta description template
     *
     * @return string|null
     */
    public function getCategoryMetaDescTemplate(): ?string;

    /**
     * Get contact page title
     *
     * @return string|null
     */
    public function getContactPageTitle(): ?string;

    /**
     * Get contact page meta description
     *
     * @return string|null
     */
    public function getContactPageMetaDesc(): ?string;

    /**
     * Check if organization structured data is enabled
     *
     * @return bool
     */
    public function isOrganizationDataEnabled(): bool;

    /**
     * Get organization name
     *
     * @return string|null
     */
    public function getOrganizationName(): ?string;

    /**
     * Get organization logo URL
     *
     * @return string|null
     */
    public function getOrganizationLogo(): ?string;

    /**
     * Check if social structured data is enabled
     *
     * @return bool
     */
    public function isSocialDataEnabled(): bool;

    /**
     * Get social profile URLs
     *
     * @return array
     */
    public function getSocialProfiles(): array;

    /**
     * Check if Google Tag Manager is enabled
     *
     * @return bool
     */
    public function isGoogleTagManagerEnabled(): bool;

    /**
     * Get Google Tag Manager ID
     *
     * @return string|null
     */
    public function getGoogleTagManagerId(): ?string;

    /**
     * Check if Google Sitelink Search is enabled
     *
     * @return bool
     */
    public function isGoogleSitelinkSearchEnabled(): bool;

    /**
     * Check if Google Trusted Store is enabled
     *
     * @return bool
     */
    public function isGoogleTrustedStoreEnabled(): bool;

    /**
     * Get Google Trusted Store ID
     *
     * @return string|null
     */
    public function getGoogleTrustedStoreId(): ?string;
}
