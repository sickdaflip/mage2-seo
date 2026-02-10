<?php
/**
 * FlipDev SEO Extension
 *
 * Alternative to DefaultProductMeta Observer - uses block approach
 * to avoid Hyvä Theme layout conflicts.
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
use Magento\Framework\View\Page\Config as PageConfig;

class ProductMeta extends Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var PageConfig
     */
    protected $pageConfig;

    /**
     * @var bool
     */
    protected $metaSet = false;

    /**
     * @param Context $context
     * @param SeoHelper $flipDevSeoHelper
     * @param \Magento\Framework\Registry $registry
     * @param PageConfig $pageConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        SeoHelper $flipDevSeoHelper,
        \Magento\Framework\Registry $registry,
        PageConfig $pageConfig,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->pageConfig = $pageConfig;
        parent::__construct($context, $flipDevSeoHelper, $data);
    }

    /**
     * Set meta data before rendering (safe timing for Hyvä)
     *
     * @return string
     */
    protected function _toHtml(): string
    {
        if ($this->metaSet) {
            return '';
        }

        $this->metaSet = true;

        try {
            $product = $this->registry->registry('product');
            if (!$product) {
                return '';
            }

            // Apply default meta data using helper
            $this->helper->checkMetaData($product, 'product');

            // Set page meta title
            if ($product->getMetaTitle()) {
                $this->pageConfig->getTitle()->set($this->cleanString($product->getMetaTitle()));
            }

            // Set page meta description
            if ($product->getMetaDescription()) {
                $this->pageConfig->setDescription($this->cleanString($product->getMetaDescription()));
            }

            // Set robots meta if configured
            $robots = $product->getData('flipdevseo_metarobots');
            if ($robots && is_string($robots) && !empty(trim($robots))) {
                $this->pageConfig->setRobots($robots);
            }

        } catch (\Throwable $e) {
            // Never break the page - just log
            $this->_logger->debug('FlipDev_Seo: ProductMeta block error', [
                'error' => $e->getMessage()
            ]);
        }

        // Return empty - this block only sets meta, doesn't output anything
        return '';
    }

    /**
     * Clean string for meta output
     *
     * @param string $string
     * @return string
     */
    protected function cleanString(string $string): string
    {
        $string = strip_tags($string);
        $string = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $string);
        $string = preg_replace('/\s+/', ' ', $string);
        return trim($string);
    }
}
