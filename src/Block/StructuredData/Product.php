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
use Magento\Catalog\Helper\Data as CatalogHelper;

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
     * @var CatalogHelper
     */
    protected $catalogHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \FlipDev\Seo\Helper\Data $flipDevSeoHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Bundle\Model\Product\Price $bundlePrice
     * @param PriceCurrencyInterface $priceCurrency
     * @param ReviewFactory $reviewFactory
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param CatalogHelper $catalogHelper
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
        CatalogHelper $catalogHelper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_bundlePrice = $bundlePrice;
        $this->priceCurrency = $priceCurrency;
        $this->reviewFactory = $reviewFactory;
        $this->storeManager = $context->getStoreManager();
        $this->imageHelper = $imageHelper;
        $this->catalogHelper = $catalogHelper;
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
     * Get product price (always including tax for Schema.org)
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
            $price = (float)$this->_bundlePrice->getTotalPrices($product, 'min', 1);
        } else {
            // Use final price (considers special price, tier prices, etc.)
            $price = (float)$product->getFinalPrice();
        }

        // Always return price INCLUDING tax for Schema.org structured data
        // Third parameter = true forces including tax regardless of store config
        return (float)$this->catalogHelper->getTaxPrice(
            $product,
            $price,
            true // includingTax = true
        );
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

        // Default to "condition" attribute if not configured
        $conditionAttribute = $this->helper->getConfig('flipdev_seo/product_sd/condition_attribute') ?: 'condition';

        // Try to get attribute text first (for select/dropdown attributes)
        $condition = $product->getAttributeText($conditionAttribute);

        // Fallback to raw data if getAttributeText returns false/empty
        if (!$condition) {
            $condition = $product->getData($conditionAttribute);
        }

        if ($condition && !is_array($condition)) {
            $conditionMap = [
                'new' => 'https://schema.org/NewCondition',
                'neu' => 'https://schema.org/NewCondition',
                'used' => 'https://schema.org/UsedCondition',
                'gebraucht' => 'https://schema.org/UsedCondition',
                'refurbished' => 'https://schema.org/RefurbishedCondition',
                'generalüberholt' => 'https://schema.org/RefurbishedCondition',
                'damaged' => 'https://schema.org/DamagedCondition',
                'beschädigt' => 'https://schema.org/DamagedCondition',
            ];
            return $conditionMap[strtolower((string)$condition)] ?? 'https://schema.org/NewCondition';
        }

        return 'https://schema.org/NewCondition';
    }

    /**
     * Get all product gallery images
     *
     * @return array
     */
    public function getProductImages(): array
    {
        $product = $this->getProduct();
        if (!$product) {
            return [];
        }

        $images = [];
        $mediaGallery = $product->getMediaGalleryImages();

        if ($mediaGallery && $mediaGallery->getSize() > 0) {
            foreach ($mediaGallery as $image) {
                $images[] = $image->getUrl();
            }
        }

        // Fallback to main image if no gallery images
        if (empty($images)) {
            $mainImage = $this->getProductImageUrl();
            if ($mainImage) {
                $images[] = $mainImage;
            }
        }

        return $images;
    }

    /**
     * Get seller/store information
     *
     * @return array
     */
    public function getSeller(): array
    {
        $storeName = $this->helper->getConfig('general/store_information/name');
        $storeUrl = $this->storeManager->getStore()->getBaseUrl();

        return [
            '@type' => 'Organization',
            'name' => $storeName ?: $this->storeManager->getStore()->getName(),
            'url' => $storeUrl,
        ];
    }

    /**
     * Get product weight
     *
     * @return array|null
     */
    public function getProductWeight(): ?array
    {
        $product = $this->getProduct();
        if (!$product) {
            return null;
        }

        $weight = $product->getWeight();
        if ($weight && $weight > 0) {
            $weightUnit = $this->helper->getConfig('general/locale/weight_unit') ?: 'kgs';
            $unitCode = $weightUnit === 'lbs' ? 'LBR' : 'KGM';

            return [
                '@type' => 'QuantitativeValue',
                'value' => (float)$weight,
                'unitCode' => $unitCode,
            ];
        }

        return null;
    }

    /**
     * Get product color
     *
     * @return string|null
     */
    public function getColor(): ?string
    {
        $product = $this->getProduct();
        if (!$product) {
            return null;
        }

        $colorAttribute = $this->helper->getConfig('flipdev_seo/product_sd/color_attribute') ?: 'color';
        $color = $product->getAttributeText($colorAttribute);

        if ($color && !is_array($color)) {
            return $this->helper->cleanString((string)$color);
        }

        return null;
    }

    /**
     * Get product material
     *
     * @return string|null
     */
    public function getMaterial(): ?string
    {
        $product = $this->getProduct();
        if (!$product) {
            return null;
        }

        $materialAttribute = $this->helper->getConfig('flipdev_seo/product_sd/material_attribute');
        if ($materialAttribute) {
            $material = $product->getAttributeText($materialAttribute);
            if ($material && !is_array($material)) {
                return $this->helper->cleanString((string)$material);
            }
        }

        return null;
    }

    /**
     * Get product category name
     *
     * @return string|null
     */
    public function getCategoryName(): ?string
    {
        $product = $this->getProduct();
        if (!$product) {
            return null;
        }

        $categoryIds = $product->getCategoryIds();
        if (!empty($categoryIds)) {
            $categoryId = end($categoryIds);
            try {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $category = $objectManager->get(\Magento\Catalog\Api\CategoryRepositoryInterface::class)
                    ->get($categoryId, $this->storeManager->getStore()->getId());
                return $this->helper->cleanString($category->getName());
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Check if product has active special price
     *
     * @return bool
     */
    public function hasSpecialPrice(): bool
    {
        $product = $this->getProduct();
        if (!$product) {
            return false;
        }

        $specialPrice = $product->getSpecialPrice();
        $regularPrice = $product->getPrice();

        if (!$specialPrice || $specialPrice >= $regularPrice) {
            return false;
        }

        // Check date range
        $now = new \DateTime();
        $fromDate = $product->getSpecialFromDate();
        $toDate = $product->getSpecialToDate();

        if ($fromDate) {
            $from = new \DateTime($fromDate);
            if ($now < $from) {
                return false; // Special price not yet active
            }
        }

        if ($toDate) {
            $to = new \DateTime($toDate);
            $to->setTime(23, 59, 59); // End of day
            if ($now > $to) {
                return false; // Special price expired
            }
        }

        return true;
    }

    /**
     * Get regular price (for comparison when special price exists) - always including tax
     *
     * @return float|null
     */
    public function getRegularPrice(): ?float
    {
        $product = $this->getProduct();
        if (!$product) {
            return null;
        }

        $price = (float)$product->getPrice();

        // Always return price INCLUDING tax for Schema.org structured data
        return (float)$this->catalogHelper->getTaxPrice(
            $product,
            $price,
            true // includingTax = true
        );
    }

    /**
     * Get special price from date
     *
     * @return string|null
     */
    public function getSpecialPriceFromDate(): ?string
    {
        $product = $this->getProduct();
        if (!$product) {
            return null;
        }

        $fromDate = $product->getSpecialFromDate();
        if ($fromDate) {
            return date('Y-m-d', strtotime($fromDate));
        }

        return null;
    }

    /**
     * Get special price to date
     *
     * @return string|null
     */
    public function getSpecialPriceToDate(): ?string
    {
        $product = $this->getProduct();
        if (!$product) {
            return null;
        }

        $toDate = $product->getSpecialToDate();
        if ($toDate) {
            return date('Y-m-d', strtotime($toDate));
        }

        return null;
    }

    /**
     * Get shipping details for structured data
     *
     * @return array|null
     */
    public function getShippingDetails(): ?array
    {
        $enabled = $this->helper->getConfig('flipdev_seo/product_sd/shipping_enabled');
        if (!$enabled) {
            return null;
        }

        $shippingRate = $this->helper->getConfig('flipdev_seo/product_sd/shipping_rate');
        $shippingDays = $this->helper->getConfig('flipdev_seo/product_sd/shipping_days');
        $shippingCountry = $this->helper->getConfig('flipdev_seo/product_sd/shipping_country')
            ?: $this->helper->getConfig('general/country/default');

        if (!$shippingRate && !$shippingDays) {
            return null;
        }

        $shippingDetails = [
            '@type' => 'OfferShippingDetails',
            'shippingDestination' => [
                '@type' => 'DefinedRegion',
                'addressCountry' => $shippingCountry,
            ],
        ];

        if ($shippingRate !== null && $shippingRate !== '') {
            $shippingDetails['shippingRate'] = [
                '@type' => 'MonetaryAmount',
                'value' => number_format((float)$shippingRate, 2, '.', ''),
                'currency' => $this->getCurrencyCode(),
            ];
        }

        if ($shippingDays) {
            $days = explode('-', $shippingDays);
            $minDays = (int)($days[0] ?? 1);
            $maxDays = (int)($days[1] ?? $minDays);

            $shippingDetails['deliveryTime'] = [
                '@type' => 'ShippingDeliveryTime',
                'handlingTime' => [
                    '@type' => 'QuantitativeValue',
                    'minValue' => 0,
                    'maxValue' => 1,
                    'unitCode' => 'DAY',
                ],
                'transitTime' => [
                    '@type' => 'QuantitativeValue',
                    'minValue' => $minDays,
                    'maxValue' => $maxDays,
                    'unitCode' => 'DAY',
                ],
            ];
        }

        return $shippingDetails;
    }

    /**
     * Get return policy for structured data
     *
     * @return array|null
     */
    public function getReturnPolicy(): ?array
    {
        $enabled = $this->helper->getConfig('flipdev_seo/product_sd/return_enabled');
        if (!$enabled) {
            return null;
        }

        $returnDays = $this->helper->getConfig('flipdev_seo/product_sd/return_days') ?: 14;
        $returnCountry = $this->helper->getConfig('flipdev_seo/product_sd/return_country')
            ?: $this->helper->getConfig('general/country/default');
        $returnFees = $this->helper->getConfig('flipdev_seo/product_sd/return_fees') ?: 'FreeReturn';

        $feesMap = [
            'FreeReturn' => 'https://schema.org/FreeReturn',
            'ReturnFeesCustomerResponsibility' => 'https://schema.org/ReturnFeesCustomerResponsibility',
            'ReturnShippingFees' => 'https://schema.org/ReturnShippingFees',
        ];

        return [
            '@type' => 'MerchantReturnPolicy',
            'applicableCountry' => $returnCountry,
            'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
            'merchantReturnDays' => (int)$returnDays,
            'returnMethod' => 'https://schema.org/ReturnByMail',
            'returnFees' => $feesMap[$returnFees] ?? 'https://schema.org/FreeReturn',
        ];
    }
}
