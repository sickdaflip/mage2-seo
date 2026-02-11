<?php
/**
 * Copyright (c) 2025 Flipdev. All rights reserved.
 */
declare(strict_types=1);

namespace FlipDev\Seo\Model\XmlSitemap;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class CategoryGenerator extends AbstractGenerator
{
    protected const CONFIG_PATH_ENABLED = 'flipdev_seo/xml_sitemap/category_enabled';
    protected const CONFIG_PATH_PRIORITY = 'flipdev_seo/xml_sitemap/category_priority';
    protected const CONFIG_PATH_CHANGEFREQ = 'flipdev_seo/xml_sitemap/category_changefreq';
    protected const CONFIG_PATH_HREFLANG = 'flipdev_seo/xml_sitemap/hreflang_enabled';

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        private CollectionFactory $categoryCollectionFactory
    ) {
        parent::__construct($scopeConfig, $storeManager);
    }

    /**
     * @inheritDoc
     */
    public function getFilename(): string
    {
        return 'category-sitemap.xml';
    }

    /**
     * @inheritDoc
     */
    public function generate(int $storeId): array
    {
        $items = [];
        $collection = $this->getCategoryCollection($storeId);
        $includeHreflang = $this->includeHreflang($storeId);
        $priority = $this->getPriority($storeId);
        $changefreq = $this->getChangefreq($storeId);

        foreach ($collection as $category) {
            // Skip root categories
            if ($category->getLevel() < 2) {
                continue;
            }

            $item = [
                'loc' => $category->getUrl(),
                'lastmod' => $this->formatDate($category->getUpdatedAt()),
                'changefreq' => $changefreq,
                'priority' => $priority,
            ];

            // Add category image if available
            $imageUrl = $this->getCategoryImageUrl($category, $storeId);
            if ($imageUrl) {
                $item['images'] = [
                    [
                        'loc' => $imageUrl,
                        'title' => $category->getName(),
                    ]
                ];
            }

            if ($includeHreflang) {
                $item['hreflang'] = $this->getHreflangLinks($category, $storeId);
            }

            $items[] = $item;
        }

        return $items;
    }

    /**
     * Get category image URL with full domain
     */
    private function getCategoryImageUrl($category, int $storeId): ?string
    {
        $image = $category->getImage();

        if (!$image || !is_string($image)) {
            return null;
        }

        // Already a full URL
        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        try {
            $store = $this->storeManager->getStore($storeId);
        } catch (\Exception $e) {
            return null;
        }

        // Relative path from root (e.g., /media/catalog/category/foo.jpg)
        if (str_starts_with($image, '/')) {
            $baseUrl = rtrim($store->getBaseUrl(UrlInterface::URL_TYPE_WEB), '/');
            return $baseUrl . $image;
        }

        // Just a filename (e.g., foo.jpg)
        $mediaUrl = rtrim($store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA), '/');
        return $mediaUrl . '/catalog/category/' . $image;
    }

    /**
     * Get category collection for sitemap
     */
    private function getCategoryCollection(int $storeId)
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addAttributeToSelect(['name', 'url_key', 'updated_at', 'image']);
        $collection->addAttributeToFilter('is_active', 1);
        $collection->addUrlRewriteToResult();

        return $collection;
    }

    /**
     * Get hreflang links for category
     */
    private function getHreflangLinks($category, int $currentStoreId): array
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
                $categoryUrl = $store->getBaseUrl() . $category->getUrlKey() . $this->getUrlSuffix($storeId);

                $hreflang[] = [
                    'hreflang' => $hreflangCode,
                    'href' => $categoryUrl,
                ];

                if ($storeId === $currentStoreId) {
                    $hreflang[] = [
                        'hreflang' => 'x-default',
                        'href' => $categoryUrl,
                    ];
                }
            }
        } catch (\Exception $e) {
            // Return empty array if error
        }

        return $hreflang;
    }

    /**
     * Get URL suffix for categories
     */
    private function getUrlSuffix(int $storeId): string
    {
        return (string) $this->scopeConfig->getValue(
            'catalog/seo/category_url_suffix',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
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
