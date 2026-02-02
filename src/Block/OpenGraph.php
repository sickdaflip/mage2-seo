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
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Store\Model\StoreManagerInterface;

class OpenGraph extends Template
{
    /**
     * @var PageConfig
     */
    protected $pageConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var string
     */
    protected $pageType = 'website';

    /**
     * @param Context $context
     * @param SeoHelper $flipDevSeoHelper
     * @param PageConfig $pageConfig
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        SeoHelper $flipDevSeoHelper,
        PageConfig $pageConfig,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Image $imageHelper,
        array $data = []
    ) {
        $this->pageConfig = $pageConfig;
        $this->registry = $registry;
        $this->imageHelper = $imageHelper;
        parent::__construct($context, $flipDevSeoHelper, $data);
    }

    /**
     * Set page type
     *
     * @param string $type
     * @return $this
     */
    public function setPageType(string $type): self
    {
        $this->pageType = $type;
        return $this;
    }

    /**
     * Get page type
     *
     * @return string
     */
    public function getPageType(): string
    {
        return $this->pageType;
    }

    /**
     * Get OG title
     *
     * @return string
     */
    public function getOgTitle(): string
    {
        $title = $this->pageConfig->getTitle()->get();
        return $this->helper->cleanString($title);
    }

    /**
     * Get OG description
     *
     * @return string
     */
    public function getOgDescription(): string
    {
        $description = $this->pageConfig->getDescription();
        return $this->helper->cleanString($description ?: '');
    }

    /**
     * Get OG URL
     *
     * @return string
     */
    public function getOgUrl(): string
    {
        return $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
    }

    /**
     * Get OG image
     *
     * @return string
     */
    public function getOgImage(): string
    {
        // Check for product image
        $product = $this->registry->registry('product');
        if ($product) {
            return $this->imageHelper->init($product, 'product_page_image_large')->getUrl();
        }

        // Check for category image
        $category = $this->registry->registry('current_category');
        if ($category && $category->getImageUrl()) {
            return $category->getImageUrl();
        }

        // Fallback to configured default image or logo
        $defaultImage = $this->helper->getConfig('flipdev_seo/opengraph/default_image');
        if ($defaultImage) {
            $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            return $mediaUrl . 'flipdev/seo/' . $defaultImage;
        }

        // Fallback to store logo
        return $this->getLogoUrl();
    }

    /**
     * Get store logo URL
     *
     * @return string
     */
    protected function getLogoUrl(): string
    {
        $folderName = \Magento\Config\Model\Config\Backend\Image\Logo::UPLOAD_DIR;
        $storeLogoPath = $this->_scopeConfig->getValue(
            'design/header/logo_src',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($storeLogoPath) {
            $path = $folderName . '/' . $storeLogoPath;
            return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $path;
        }

        return '';
    }

    /**
     * Get site name
     *
     * @return string
     */
    public function getSiteName(): string
    {
        $storeName = $this->helper->getConfig('general/store_information/name');
        return $storeName ?: $this->_storeManager->getStore()->getName();
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale(): string
    {
        $locale = $this->_scopeConfig->getValue(
            'general/locale/code',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return str_replace('_', '-', $locale ?: 'en-US');
    }

    /**
     * Get product data for OG (if on product page)
     *
     * @return array|null
     */
    public function getProductData(): ?array
    {
        $product = $this->registry->registry('product');
        if (!$product) {
            return null;
        }

        return [
            'price' => number_format((float)$product->getFinalPrice(), 2, '.', ''),
            'currency' => $this->_storeManager->getStore()->getCurrentCurrencyCode(),
            'availability' => $product->isAvailable() ? 'instock' : 'outofstock',
            'brand' => $product->getAttributeText('manufacturer') ?: '',
        ];
    }
}
