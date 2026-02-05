<?php
/**
 * Copyright (c) 2025 Flipdev. All rights reserved.
 */
declare(strict_types=1);

namespace FlipDev\Seo\Model\XmlSitemap;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Model\Stock\Status as StockStatus;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class ProductGenerator extends AbstractGenerator
{
    protected const CONFIG_PATH_ENABLED = 'flipdev_seo/xml_sitemap/product_enabled';
    protected const CONFIG_PATH_PRIORITY = 'flipdev_seo/xml_sitemap/product_priority';
    protected const CONFIG_PATH_CHANGEFREQ = 'flipdev_seo/xml_sitemap/product_changefreq';
    protected const CONFIG_PATH_IMAGES = 'flipdev_seo/xml_sitemap/product_images';
    protected const CONFIG_PATH_EXCLUDE_OOS = 'flipdev_seo/xml_sitemap/exclude_out_of_stock';
    protected const CONFIG_PATH_EXCLUDE_DISABLED = 'flipdev_seo/xml_sitemap/exclude_disabled';
    protected const CONFIG_PATH_EXCLUDE_NOT_VISIBLE = 'flipdev_seo/xml_sitemap/exclude_not_visible';
    protected const CONFIG_PATH_HREFLANG = 'flipdev_seo/xml_sitemap/hreflang_enabled';

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        private CollectionFactory $productCollectionFactory,
        private Visibility $visibility
    ) {
        parent::__construct($scopeConfig, $storeManager);
    }

    /**
     * @inheritDoc
     */
    public function getFilename(): string
    {
        return 'product-sitemap.xml';
    }

    /**
     * @inheritDoc
     */
    public function generate(int $storeId): array
    {
        $items = [];
        $collection = $this->getProductCollection($storeId);
        $includeImages = $this->includeImages($storeId);
        $includeHreflang = $this->includeHreflang($storeId);
        $priority = $this->getPriority($storeId);
        $changefreq = $this->getChangefreq($storeId);

        foreach ($collection as $product) {
            $item = [
                'loc' => $product->getProductUrl(),
                'lastmod' => $this->formatDate($product->getUpdatedAt()),
                'changefreq' => $changefreq,
                'priority' => $priority,
            ];

            if ($includeImages) {
                $item['images'] = $this->getProductImages($product);
            }

            if ($includeHreflang) {
                $item['hreflang'] = $this->getHreflangLinks($product, $storeId);
            }

            $items[] = $item;
        }

        return $items;
    }

    /**
     * Get product collection for sitemap
     */
    private function getProductCollection(int $storeId)
    {
        $collection = $this->productCollectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addStoreFilter($storeId);
        $collection->addAttributeToSelect(['name', 'updated_at', 'image', 'small_image', 'thumbnail']);
        $collection->addUrlRewrite();

        // Exclude disabled products
        if ($this->scopeConfig->isSetFlag(self::CONFIG_PATH_EXCLUDE_DISABLED, ScopeInterface::SCOPE_STORE, $storeId)) {
            $collection->addAttributeToFilter('status', Status::STATUS_ENABLED);
        }

        // Exclude not visible products
        if ($this->scopeConfig->isSetFlag(self::CONFIG_PATH_EXCLUDE_NOT_VISIBLE, ScopeInterface::SCOPE_STORE, $storeId)) {
            $collection->setVisibility($this->visibility->getVisibleInSiteIds());
        }

        // Exclude out of stock products
        if ($this->scopeConfig->isSetFlag(self::CONFIG_PATH_EXCLUDE_OOS, ScopeInterface::SCOPE_STORE, $storeId)) {
            $collection->joinField(
                'stock_status',
                'cataloginventory_stock_status',
                'stock_status',
                'product_id=entity_id',
                ['stock_status' => StockStatus::STATUS_IN_STOCK],
                'inner'
            );
        }

        return $collection;
    }

    /**
     * Get product images for sitemap
     */
    private function getProductImages($product): array
    {
        $images = [];
        $mediaGallery = $product->getMediaGalleryImages();

        if ($mediaGallery && $mediaGallery->getSize() > 0) {
            foreach ($mediaGallery as $image) {
                if ($image->getUrl()) {
                    $images[] = [
                        'loc' => $image->getUrl(),
                        'title' => $product->getName(),
                    ];
                }
            }
        } elseif ($product->getImage() && $product->getImage() !== 'no_selection') {
            try {
                $store = $this->storeManager->getStore($product->getStoreId());
                $mediaUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                $images[] = [
                    'loc' => $mediaUrl . 'catalog/product' . $product->getImage(),
                    'title' => $product->getName(),
                ];
            } catch (\Exception $e) {
                // Skip image if can't get URL
            }
        }

        return $images;
    }

    /**
     * Get hreflang links for product
     */
    private function getHreflangLinks($product, int $currentStoreId): array
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

                // Convert locale to hreflang format (en_US -> en-us)
                $hreflangCode = str_replace('_', '-', strtolower($localeCode));

                // Get product URL for this store
                $productUrl = $store->getBaseUrl() . $product->getUrlKey() . $this->getUrlSuffix($storeId);

                $hreflang[] = [
                    'hreflang' => $hreflangCode,
                    'href' => $productUrl,
                ];

                // Add x-default for the current store
                if ($storeId === $currentStoreId) {
                    $hreflang[] = [
                        'hreflang' => 'x-default',
                        'href' => $productUrl,
                    ];
                }
            }
        } catch (\Exception $e) {
            // Return empty array if error
        }

        return $hreflang;
    }

    /**
     * Get URL suffix for products
     */
    private function getUrlSuffix(int $storeId): string
    {
        return (string) $this->scopeConfig->getValue(
            'catalog/seo/product_url_suffix',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if images should be included
     */
    private function includeImages(int $storeId): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_PATH_IMAGES,
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
