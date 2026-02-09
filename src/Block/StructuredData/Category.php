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

namespace FlipDev\Seo\Block\StructuredData;

use Magento\Framework\View\Element\Template\Context;
use FlipDev\Seo\Helper\Data as SeoHelper;
use Magento\Store\Model\StoreManagerInterface;

class Category extends \FlipDev\Seo\Block\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Context $context
     * @param SeoHelper $flipDevSeoHelper
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        SeoHelper $flipDevSeoHelper,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->storeManager = $context->getStoreManager();
        parent::__construct($context, $flipDevSeoHelper, $data);
    }

    /**
     * Get current category
     *
     * @return \Magento\Catalog\Model\Category|null
     */
    public function getCategory()
    {
        return $this->registry->registry('current_category');
    }

    /**
     * Check if we're on a category page (not product page)
     *
     * @return bool
     */
    public function isCategoryPage(): bool
    {
        return $this->getCategory() && !$this->registry->registry('product');
    }

    /**
     * Get structured data array
     *
     * Simple CollectionPage schema without ItemList.
     * ItemList with products is intentionally NOT included because:
     * 1. It can conflict with ElasticSuite/Toolbar
     * 2. Categories often have 100+ products, making ItemList incomplete
     * 3. Google crawls product pages individually anyway
     *
     * @return array
     */
    public function getStructuredData(): array
    {
        $category = $this->getCategory();
        if (!$category) {
            return [];
        }

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $this->helper->cleanString($category->getName()),
            'url' => $category->getUrl(),
        ];

        // Add description if available
        $description = $category->getDescription();
        if ($description) {
            $data['description'] = $this->helper->cleanString(strip_tags($description));
        }

        // Add category image if available
        $imageUrl = $this->getCategoryImageUrl($category);
        if ($imageUrl) {
            $data['image'] = $imageUrl;
        }

        return $data;
    }

    /**
     * Get category image URL with full domain
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return string|null
     */
    private function getCategoryImageUrl($category): ?string
    {
        $imageUrl = $category->getImageUrl();

        if (!$imageUrl) {
            return null;
        }

        // If URL already has http/https, return as is
        if (str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://')) {
            return $imageUrl;
        }

        // Prepend base URL if relative path
        try {
            $baseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
            return rtrim($baseUrl, '/') . '/' . ltrim($imageUrl, '/');
        } catch (\Exception $e) {
            return $imageUrl;
        }
    }
}
