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

namespace FlipDev\Seo\Block;

use Magento\Framework\View\Element\Template\Context;
use FlipDev\Seo\Helper\Data as SeoHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Cms\Api\PageRepositoryInterface;

class Hreflang extends Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var PageRepositoryInterface
     */
    protected $pageRepository;

    /**
     * @param Context $context
     * @param SeoHelper $flipDevSeoHelper
     * @param \Magento\Framework\Registry $registry
     * @param ProductRepositoryInterface $productRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param PageRepositoryInterface $pageRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        SeoHelper $flipDevSeoHelper,
        \Magento\Framework\Registry $registry,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        PageRepositoryInterface $pageRepository,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->pageRepository = $pageRepository;
        parent::__construct($context, $flipDevSeoHelper, $data);
    }

    /**
     * Get hreflang links for all store views
     *
     * @return array
     */
    public function getHreflangLinks(): array
    {
        $links = [];
        $stores = $this->_storeManager->getStores();
        $currentStoreId = $this->_storeManager->getStore()->getId();

        // Get current entity
        $product = $this->registry->registry('product');
        $category = $this->registry->registry('current_category');
        $cmsPage = $this->registry->registry('cms_page');

        foreach ($stores as $store) {
            if (!$store->isActive()) {
                continue;
            }

            $url = $this->getUrlForStore($store, $product, $category, $cmsPage);
            if ($url) {
                $locale = $this->getStoreLocale($store);
                $links[] = [
                    'hreflang' => $locale,
                    'href' => $url,
                    'is_current' => $store->getId() == $currentStoreId,
                ];
            }
        }

        // Add x-default (usually the default store)
        $defaultStore = $this->_storeManager->getDefaultStoreView();
        if ($defaultStore) {
            $defaultUrl = $this->getUrlForStore($defaultStore, $product, $category, $cmsPage);
            if ($defaultUrl) {
                $links[] = [
                    'hreflang' => 'x-default',
                    'href' => $defaultUrl,
                    'is_current' => false,
                ];
            }
        }

        return $links;
    }

    /**
     * Get URL for a specific store
     *
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @param mixed $product
     * @param mixed $category
     * @param mixed $cmsPage
     * @return string|null
     */
    protected function getUrlForStore($store, $product, $category, $cmsPage): ?string
    {
        try {
            if ($product) {
                // Check if product exists in this store
                $storeProduct = $this->productRepository->getById($product->getId(), false, $store->getId());
                if ($storeProduct->getStatus() == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
                    return $storeProduct->setStoreId($store->getId())->getProductUrl();
                }
            } elseif ($category && !$product) {
                // Category page (not product page with category)
                $storeCategory = $this->categoryRepository->get($category->getId(), $store->getId());
                if ($storeCategory->getIsActive()) {
                    return $storeCategory->getUrl();
                }
            } elseif ($cmsPage) {
                // CMS page - check if same identifier exists in store
                return $store->getBaseUrl() . $cmsPage->getIdentifier();
            } else {
                // Homepage or other pages
                return $store->getBaseUrl();
            }
        } catch (\Exception $e) {
            // Entity doesn't exist in this store
            return null;
        }

        return null;
    }

    /**
     * Get locale code for store (format: en-US)
     *
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @return string
     */
    protected function getStoreLocale($store): string
    {
        $locale = $this->_scopeConfig->getValue(
            'general/locale/code',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store->getId()
        );

        // Convert from en_US to en-US format, then simplify to just language code
        $locale = str_replace('_', '-', $locale ?: 'en-US');

        // For hreflang, we typically use language-region format
        return strtolower(substr($locale, 0, 2)) . '-' . strtoupper(substr($locale, 3, 2));
    }

    /**
     * Check if there are multiple stores (hreflang makes sense)
     *
     * @return bool
     */
    public function hasMultipleStores(): bool
    {
        $activeStores = 0;
        foreach ($this->_storeManager->getStores() as $store) {
            if ($store->isActive()) {
                $activeStores++;
            }
        }
        return $activeStores > 1;
    }
}
