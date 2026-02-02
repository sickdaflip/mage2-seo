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
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Review\Model\ReviewFactory;
use Magento\Store\Model\StoreManagerInterface;

class Product extends \FlipDev\Seo\Block\Template
{
    /**
     * @var \Magento\Bundle\Model\Product\Price
     */
    protected $_bundlePrice;

    /**
     * @var ProductInterface|null
     */
    protected $_product = null;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \FlipDev\Seo\Helper\Data $flipDevSeoHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Bundle\Model\Product\Price $bundlePrice
     * @param PriceCurrencyInterface $priceCurrency
     * @param ReviewFactory $reviewFactory
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \FlipDev\Seo\Helper\Data $flipDevSeoHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Bundle\Model\Product\Price $bundlePrice,
        PriceCurrencyInterface $priceCurrency,
        ReviewFactory $reviewFactory,
        \Magento\Catalog\Helper\Image $imageHelper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_bundlePrice = $bundlePrice;
        $this->priceCurrency = $priceCurrency;
        $this->reviewFactory = $reviewFactory;
        $this->storeManager = $context->getStoreManager();
        $this->imageHelper = $imageHelper;
        parent::__construct($context, $flipDevSeoHelper, $data);
    }

    /**
     * Get current product
     *
     * @return ProductInterface|null
     */
    public function getProduct(): ?ProductInterface
    {
        if (!$this->_product) {
            $this->_product = $this->_coreRegistry->registry('product');
        }
        return $this->_product;
    }

    /**
     * Get product name
     *
     * @return string
     */
    public function getProductName(): string
    {
        return $this->helper->cleanString($this->getProduct()?->getName() ?? '');
    }

    /**
     * Get product description
     *
     * @return string
     */
    public function getProductDescription(): string
    {
        $product = $this->getProduct();
        $description = $product?->getShortDescription() ?: $product?->getDescription() ?: '';
        return $this->helper->cleanString(strip_tags((string)$description));
    }

    /**
     * Get product SKU
     *
     * @return string
     */
    public function getProductSku(): string
    {
        return $this->getProduct()?->getSku() ?? '';
    }

    /**
     * Get product image URL
     *
     * @return string
     */
    public function getProductImageUrl(): string
    {
        $product = $this->getProduct();
        if (!$product) {
            return '';
        }
        return $this->imageHelper->init($product, 'product_page_image_large')->getUrl();
    }

    /**
     * Get product URL
     *
     * @return string
     */
    public function getProductUrl(): string
    {
        return $this->getProduct()?->getProductUrl() ?? '';
    }

    /**
     * Get product price
     *
     * @return float
     */
    public function getProductPrice(): float
    {
        $product = $this->getProduct();
        if (!$product) {
            return 0.0;
        }

        if ($product->getTypeId() === 'bundle') {
            return (float)$this->_bundlePrice->getTotalPrices($product, 'min', 1);
        }

        return (float)$product->getFinalPrice();
    }

    /**
     * Get currency code
     *
     * @return string
     */
    public function getCurrencyCode(): string
    {
        return $this->storeManager->getStore()->getCurrentCurrencyCode();
    }

    /**
     * Get product availability
     *
     * @return string
     */
    public function getAvailability(): string
    {
        $product = $this->getProduct();
        if (!$product) {
            return 'https://schema.org/OutOfStock';
        }

        return $product->isAvailable()
            ? 'https://schema.org/InStock'
            : 'https://schema.org/OutOfStock';
    }

    /**
     * Get product brand
     *
     * @return string|null
     */
    public function getBrand(): ?string
    {
        $product = $this->getProduct();
        if (!$product) {
            return null;
        }

        $brandAttribute = $this->helper->getConfig('flipdev_seo/product_sd/brand_attribute');
        if ($brandAttribute) {
            $brand = $product->getAttributeText($brandAttribute);
            if ($brand) {
                return $this->helper->cleanString((string)$brand);
            }
        }

        $manufacturer = $product->getAttributeText('manufacturer');
        if ($manufacturer) {
            return $this->helper->cleanString((string)$manufacturer);
        }

        return null;
    }

    /**
     * Get product GTIN (EAN/UPC)
     *
     * @return string|null
     */
    public function getGtin(): ?string
    {
        $product = $this->getProduct();
        if (!$product) {
            return null;
        }

        $gtinAttribute = $this->helper->getConfig('flipdev_seo/product_sd/gtin_attribute');
        if ($gtinAttribute) {
            $gtin = $product->getData($gtinAttribute);
            if ($gtin) {
                return (string)$gtin;
            }
        }

        return null;
    }

    /**
     * Get MPN (Manufacturer Part Number)
     *
     * @return string|null
     */
    public function getMpn(): ?string
    {
        $product = $this->getProduct();
        if (!$product) {
            return null;
        }

        $mpnAttribute = $this->helper->getConfig('flipdev_seo/product_sd/mpn_attribute');
        if ($mpnAttribute) {
            $mpn = $product->getData($mpnAttribute);
            if ($mpn) {
                return (string)$mpn;
            }
        }

        return null;
    }

    /**
     * Check if product has reviews
     *
     * @return bool
     */
    public function hasReviews(): bool
    {
        $product = $this->getProduct();
        if (!$product) {
            return false;
        }

        $this->reviewFactory->create()->getEntitySummary($product, $this->storeManager->getStore()->getId());
        $ratingSummary = $product->getRatingSummary();

        return $ratingSummary && $ratingSummary->getReviewsCount() > 0;
    }

    /**
     * Get rating value (1-5 scale)
     *
     * @return float|null
     */
    public function getRatingValue(): ?float
    {
        $product = $this->getProduct();
        if (!$product) {
            return null;
        }

        $ratingSummary = $product->getRatingSummary();
        if ($ratingSummary && $ratingSummary->getRatingSummary()) {
            return round($ratingSummary->getRatingSummary() / 20, 1);
        }

        return null;
    }

    /**
     * Get review count
     *
     * @return int|null
     */
    public function getReviewCount(): ?int
    {
        $product = $this->getProduct();
        if (!$product) {
            return null;
        }

        $ratingSummary = $product->getRatingSummary();
        if ($ratingSummary) {
            return (int)$ratingSummary->getReviewsCount();
        }

        return null;
    }

    /**
     * Get price valid until date
     *
     * @return string
     */
    public function getPriceValidUntil(): string
    {
        $product = $this->getProduct();
        if ($product) {
            $specialToDate = $product->getSpecialToDate();
            if ($specialToDate) {
                return date('Y-m-d', strtotime($specialToDate));
            }
        }

        return date('Y-m-d', strtotime('+1 year'));
    }

    /**
     * Get product condition
     *
     * @return string
     */
    public function getCondition(): string
    {
        $product = $this->getProduct();
        if (!$product) {
            return 'https://schema.org/NewCondition';
        }

        $conditionAttribute = $this->helper->getConfig('flipdev_seo/product_sd/condition_attribute');
        if ($conditionAttribute) {
            $condition = $product->getData($conditionAttribute);
            if ($condition) {
                $conditionMap = [
                    'new' => 'https://schema.org/NewCondition',
                    'used' => 'https://schema.org/UsedCondition',
                    'refurbished' => 'https://schema.org/RefurbishedCondition',
                    'damaged' => 'https://schema.org/DamagedCondition',
                ];
                return $conditionMap[strtolower($condition)] ?? 'https://schema.org/NewCondition';
            }
        }

        return 'https://schema.org/NewCondition';
    }
}
