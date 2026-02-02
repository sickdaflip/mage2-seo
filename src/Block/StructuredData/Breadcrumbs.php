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

use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Framework\View\Element\Template\Context;
use FlipDev\Seo\Helper\Data as SeoHelper;

class Breadcrumbs extends \FlipDev\Seo\Block\Template
{
    /**
     * @var CatalogHelper
     */
    protected $catalogHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param Context $context
     * @param SeoHelper $flipDevSeoHelper
     * @param CatalogHelper $catalogHelper
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        SeoHelper $flipDevSeoHelper,
        CatalogHelper $catalogHelper,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->catalogHelper = $catalogHelper;
        $this->registry = $registry;
        parent::__construct($context, $flipDevSeoHelper, $data);
    }

    /**
     * Get breadcrumb items for structured data
     *
     * @return array
     */
    public function getBreadcrumbItems(): array
    {
        $items = [];
        $position = 1;

        // Add home
        $items[] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => __('Home')->render(),
            'item' => $this->_storeManager->getStore()->getBaseUrl(),
        ];

        // Get breadcrumb path from catalog helper
        $path = $this->catalogHelper->getBreadcrumbPath();

        if (!empty($path)) {
            foreach ($path as $key => $crumb) {
                // Skip home (already added) and current page (product/category)
                if ($key === 'home') {
                    continue;
                }

                $item = [
                    '@type' => 'ListItem',
                    'position' => $position++,
                    'name' => $this->helper->cleanString($crumb['label'] ?? ''),
                ];

                // Add URL if available (not for current page)
                if (isset($crumb['link']) && $crumb['link']) {
                    $item['item'] = $crumb['link'];
                } else {
                    // For the last item (current page), use current URL
                    $item['item'] = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
                }

                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * Check if breadcrumbs should be rendered
     *
     * @return bool
     */
    public function hasBreadcrumbs(): bool
    {
        $path = $this->catalogHelper->getBreadcrumbPath();
        return !empty($path) && count($path) > 1;
    }

    /**
     * Get structured data array
     *
     * @return array
     */
    public function getStructuredData(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $this->getBreadcrumbItems(),
        ];
    }
}
