<?php
/**
 * Copyright (c) 2025 Flipdev. All rights reserved.
 */
declare(strict_types=1);

namespace FlipDev\Seo\Model\XmlSitemap;

use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class CmsGenerator extends AbstractGenerator
{
    protected const CONFIG_PATH_ENABLED = 'flipdev_seo/xml_sitemap/cms_enabled';
    protected const CONFIG_PATH_PRIORITY = 'flipdev_seo/xml_sitemap/cms_priority';
    protected const CONFIG_PATH_CHANGEFREQ = 'flipdev_seo/xml_sitemap/cms_changefreq';
    protected const CONFIG_PATH_HREFLANG = 'flipdev_seo/xml_sitemap/hreflang_enabled';

    /**
     * Pages to exclude from sitemap
     */
    private const EXCLUDED_PAGES = [
        'no-route',
        'enable-cookies',
        'privacy-policy-cookie-restriction-mode',
    ];

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        private CollectionFactory $pageCollectionFactory
    ) {
        parent::__construct($scopeConfig, $storeManager);
    }

    /**
     * @inheritDoc
     */
    public function getFilename(): string
    {
        return 'cms-sitemap.xml';
    }

    /**
     * @inheritDoc
     */
    public function generate(int $storeId): array
    {
        $items = [];
        $collection = $this->getPageCollection($storeId);
        $includeHreflang = $this->includeHreflang($storeId);
        $priority = $this->getPriority($storeId);
        $changefreq = $this->getChangefreq($storeId);
        $baseUrl = $this->getBaseUrl($storeId);

        foreach ($collection as $page) {
            // Skip excluded pages
            if (in_array($page->getIdentifier(), self::EXCLUDED_PAGES)) {
                continue;
            }

            // Build URL
            $url = $page->getIdentifier() === 'home'
                ? $baseUrl . '/'
                : $baseUrl . '/' . $page->getIdentifier();

            $item = [
                'loc' => $url,
                'lastmod' => $this->formatDate($page->getUpdateTime()),
                'changefreq' => $changefreq,
                'priority' => $page->getIdentifier() === 'home' ? '1.0' : $priority,
            ];

            if ($includeHreflang) {
                $item['hreflang'] = $this->getHreflangLinks($page, $storeId);
            }

            $items[] = $item;
        }

        return $items;
    }

    /**
     * Get CMS page collection for sitemap
     */
    private function getPageCollection(int $storeId)
    {
        $collection = $this->pageCollectionFactory->create();
        $collection->addStoreFilter($storeId);
        $collection->addFieldToFilter('is_active', 1);

        return $collection;
    }

    /**
     * Get hreflang links for CMS page
     */
    private function getHreflangLinks($page, int $currentStoreId): array
    {
        $hreflang = [];

        try {
            $stores = $this->storeManager->getStores();

            foreach ($stores as $store) {
                if (!$store->getIsActive()) {
                    continue;
                }

                $storeId = (int) $store->getId();
                $localeCode = $this->scopeConfig->getValue(
                    'general/locale/code',
                    ScopeInterface::SCOPE_STORE,
                    $storeId
                );

                $hreflangCode = str_replace('_', '-', strtolower($localeCode));
                $pageUrl = $page->getIdentifier() === 'home'
                    ? rtrim($store->getBaseUrl(), '/') . '/'
                    : rtrim($store->getBaseUrl(), '/') . '/' . $page->getIdentifier();

                $hreflang[] = [
                    'hreflang' => $hreflangCode,
                    'href' => $pageUrl,
                ];

                if ($storeId === $currentStoreId) {
                    $hreflang[] = [
                        'hreflang' => 'x-default',
                        'href' => $pageUrl,
                    ];
                }
            }
        } catch (\Exception $e) {
            // Return empty array if error
        }

        return $hreflang;
    }

    /**
     * Check if hreflang should be included
     */
    private function includeHreflang(int $storeId): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_PATH_HREFLANG,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
