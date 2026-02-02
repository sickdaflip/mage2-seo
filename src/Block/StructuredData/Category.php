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
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;

class Category extends \FlipDev\Seo\Block\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var LayerResolver
     */
    protected $layerResolver;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @param Context $context
     * @param SeoHelper $flipDevSeoHelper
     * @param \Magento\Framework\Registry $registry
     * @param LayerResolver $layerResolver
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        SeoHelper $flipDevSeoHelper,
        \Magento\Framework\Registry $registry,
        LayerResolver $layerResolver,
        \Magento\Catalog\Helper\Image $imageHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->layerResolver = $layerResolver;
        $this->imageHelper = $imageHelper;
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
     * Get product collection items for ItemList
     *
     * @return array
     */
    public function getProductItems(): array
    {
        $items = [];
        $position = 1;

        try {
            $layer = $this->layerResolver->get();
            $productCollection = $layer->getProductCollection();

            // Limit to configured number of items
            $maxItems = (int)($this->helper->getConfig('flipdev_seo/category_sd/max_items') ?: 12);
            $productCollection->setPageSize($maxItems);

            foreach ($productCollection as $product) {
                $items[] = [
                    '@type' => 'ListItem',
                    'position' => $position++,
                    'item' => [
                        '@type' => 'Product',
                        'name' => $this->helper->cleanString($product->getName()),
                        'url' => $product->getProductUrl(),
                        'image' => $this->imageHelper->init($product, 'product_small_image')->getUrl(),
                        'offers' => [
                            '@type' => 'Offer',
                            'price' => number_format((float)$product->getFinalPrice(), 2, '.', ''),
                            'priceCurrency' => $this->_storeManager->getStore()->getCurrentCurrencyCode(),
                            'availability' => $product->isAvailable()
                                ? 'https://schema.org/InStock'
                                : 'https://schema.org/OutOfStock',
                        ],
                    ],
                ];
            }
        } catch (\Exception $e) {
            // Layer might not be available
            return [];
        }

        return $items;
    }

    /**
     * Get structured data array
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
            'description' => $this->helper->cleanString(strip_tags($category->getDescription() ?: '')),
            'url' => $category->getUrl(),
        ];

        // Add category image if available
        if ($category->getImageUrl()) {
            $data['image'] = $category->getImageUrl();
        }

        // Add ItemList with products
        $items = $this->getProductItems();
        if (!empty($items)) {
            $data['mainEntity'] = [
                '@type' => 'ItemList',
                'numberOfItems' => count($items),
                'itemListElement' => $items,
            ];
        }

        return $data;
    }
}
