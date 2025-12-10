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

namespace FlipDev\Seo\Model;

use FlipDev\Seo\Api\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * SEO Configuration Implementation
 */
class Config implements ConfigInterface
{
    private const XML_PATH_SETTINGS = 'flipdev_seo/settings/';
    private const XML_PATH_TWITTER = 'flipdev_seo/twittercards/';
    private const XML_PATH_METADATA = 'flipdev_seo/metadata/';
    private const XML_PATH_ORGANIZATION = 'flipdev_seo/organization_sd/';
    private const XML_PATH_SOCIAL = 'flipdev_seo/social_sd/';
    private const XML_PATH_GTM = 'flipdev_seo/google_tag_manager/';
    private const XML_PATH_GTS = 'flipdev_seo/google_trusted_store/';

    private ScopeConfigInterface $scopeConfig;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get configuration value as boolean
     *
     * @param string $path
     * @return bool
     */
    private function isEnabled(string $path): bool
    {
        return $this->scopeConfig->isSetFlag(
            $path,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get configuration value as string
     *
     * @param string $path
     * @return string|null
     */
    private function getValue(string $path): ?string
    {
        $value = $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE
        );
        
        return $value !== null ? (string)$value : null;
    }

    /**
     * @inheritDoc
     */
    public function isNoIndexForFilteredCategoriesEnabled(): bool
    {
        return $this->isEnabled(self::XML_PATH_SETTINGS . 'noindexparams');
    }

    /**
     * @inheritDoc
     */
    public function isNoIndexForSearchEnabled(): bool
    {
        return $this->isEnabled(self::XML_PATH_SETTINGS . 'noindexparamssearch');
    }

    /**
     * @inheritDoc
     */
    public function isNoIndexForAdvancedSearchEnabled(): bool
    {
        return $this->isEnabled(self::XML_PATH_SETTINGS . 'noindexparamsadvsearch');
    }

    /**
     * @inheritDoc
     */
    public function isCategoryH1Enabled(): bool
    {
        return $this->isEnabled(self::XML_PATH_SETTINGS . 'category_h1');
    }

    /**
     * @inheritDoc
     */
    public function isCanonicalRedirectEnabled(): bool
    {
        return $this->isEnabled(self::XML_PATH_SETTINGS . 'forcecanonical');
    }

    /**
     * @inheritDoc
     */
    public function isCmsCanonicalEnabled(): bool
    {
        return $this->isEnabled(self::XML_PATH_SETTINGS . 'enablecmscanonical');
    }

    /**
     * @inheritDoc
     */
    public function shouldRemoveMetaKeywords(): bool
    {
        return $this->isEnabled(self::XML_PATH_SETTINGS . 'removekeywords');
    }

    /**
     * @inheritDoc
     */
    public function isTwitterCardsEnabled(): bool
    {
        return $this->isEnabled(self::XML_PATH_TWITTER . 'enabled');
    }

    /**
     * @inheritDoc
     */
    public function getTwitterSite(): ?string
    {
        return $this->getValue(self::XML_PATH_TWITTER . 'twittersite');
    }

    /**
     * @inheritDoc
     */
    public function isProductTitleEnabled(): bool
    {
        return $this->isEnabled(self::XML_PATH_METADATA . 'product_title_enabled');
    }

    /**
     * @inheritDoc
     */
    public function getProductTitleTemplate(): ?string
    {
        return $this->getValue(self::XML_PATH_METADATA . 'product_title');
    }

    /**
     * @inheritDoc
     */
    public function isProductMetaDescEnabled(): bool
    {
        return $this->isEnabled(self::XML_PATH_METADATA . 'product_metadesc_enabled');
    }

    /**
     * @inheritDoc
     */
    public function getProductMetaDescTemplate(): ?string
    {
        return $this->getValue(self::XML_PATH_METADATA . 'product_metadesc');
    }

    /**
     * @inheritDoc
     */
    public function isCategoryTitleEnabled(): bool
    {
        return $this->isEnabled(self::XML_PATH_METADATA . 'category_title_enabled');
    }

    /**
     * @inheritDoc
     */
    public function getCategoryTitleTemplate(): ?string
    {
        return $this->getValue(self::XML_PATH_METADATA . 'category_title');
    }

    /**
     * @inheritDoc
     */
    public function isCategoryMetaDescEnabled(): bool
    {
        return $this->isEnabled(self::XML_PATH_METADATA . 'category_metadesc_enabled');
    }

    /**
     * @inheritDoc
     */
    public function getCategoryMetaDescTemplate(): ?string
    {
        return $this->getValue(self::XML_PATH_METADATA . 'category_metadesc');
    }

    /**
     * @inheritDoc
     */
    public function getContactPageTitle(): ?string
    {
        return $this->getValue(self::XML_PATH_METADATA . 'contact_title');
    }

    /**
     * @inheritDoc
     */
    public function getContactPageMetaDesc(): ?string
    {
        return $this->getValue(self::XML_PATH_METADATA . 'contact_metadesc');
    }

    /**
     * @inheritDoc
     */
    public function isOrganizationDataEnabled(): bool
    {
        return $this->isEnabled(self::XML_PATH_ORGANIZATION . 'enabled');
    }

    /**
     * @inheritDoc
     */
    public function getOrganizationName(): ?string
    {
        return $this->getValue(self::XML_PATH_ORGANIZATION . 'name');
    }

    /**
     * @inheritDoc
     */
    public function getOrganizationLogo(): ?string
    {
        return $this->getValue(self::XML_PATH_ORGANIZATION . 'logo');
    }

    /**
     * @inheritDoc
     */
    public function isSocialDataEnabled(): bool
    {
        return $this->isEnabled(self::XML_PATH_SOCIAL . 'enabled');
    }

    /**
     * @inheritDoc
     */
    public function getSocialProfiles(): array
    {
        $profiles = $this->getValue(self::XML_PATH_SOCIAL . 'social_profiles');

        if (!$profiles) {
            return [];
        }

        return array_filter(
            array_map('trim', explode("\n", $profiles))
        );
    }

    /**
     * @inheritDoc
     */
    public function isGoogleTagManagerEnabled(): bool
    {
        return $this->isEnabled(self::XML_PATH_GTM . 'enabled');
    }

    /**
     * @inheritDoc
     */
    public function getGoogleTagManagerId(): ?string
    {
        return $this->getValue(self::XML_PATH_GTM . 'gtm_id');
    }

    /**
     * @inheritDoc
     */
    public function isGoogleTrustedStoreEnabled(): bool
    {
        return $this->isEnabled(self::XML_PATH_GTS . 'enabled');
    }

    /**
     * @inheritDoc
     */
    public function getGoogleTrustedStoreId(): ?string
    {
        return $this->getValue(self::XML_PATH_GTS . 'trusted_store_id');
    }
}
