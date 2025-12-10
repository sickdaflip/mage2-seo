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

use Magento\Framework\Pricing\PriceCurrencyInterface;

class TwitterCards extends \Magento\Framework\View\Element\Template
{
    
    private $helper;

    /**
     * @var \Magento\Bundle\Model\Product\Price
     */
    protected $_bundlePrice;

    /**
     * @var Product
     */
    protected $_product = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \FlipDev\Seo\Helper\Data $flipDevSeoHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Bundle\Model\Product\Price $bundlePrice
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \FlipDev\Seo\Helper\Data $flipDevSeoHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Bundle\Model\Product\Price $bundlePrice,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    )
    {
        $this->helper = $flipDevSeoHelper;
        $this->_coreRegistry = $registry;
        $this->_bundlePrice = $bundlePrice;
        $this->_priceCurrency = $priceCurrency;
        parent::__construct($context, $data);
    }

    public function cleanString($string)
    {
        return $this->helper->cleanString($string);
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = $this->_coreRegistry->registry('product');
        }
        return $this->_product;
    }

    public function getConfig(string $configPath): ?string
    {
        return $this->helper->getConfig($configPath);
    }

    public function getStartingPrice($product)
    {
        if($product->getTypeId() === 'bundle')
        {
            $price = $this->_bundlePrice->getTotalPrices($product, 'min', 1);
        } else {
            $price = $product->getFinalPrice();
        }

        return $this->_priceCurrency->format($price, false);

    }

    /**
     * Get product image URL
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductImageUrl($product)
    {
        $imageHelper = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Catalog\Helper\Image::class);

        return $imageHelper->init($product, 'product_page_image_large')->getUrl();
    }
}