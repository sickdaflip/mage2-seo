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
use Magento\Tax\Model\Calculation as TaxCalculation;

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
     * @var TaxCalculation
     */
    protected $taxCalculation;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \FlipDev\Seo\Helper\Data $flipDevSeoHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Bundle\Model\Product\Price $bundlePrice
     * @param PriceCurrencyInterface $priceCurrency
     * @param ReviewFactory $reviewFactory
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param CatalogHelper $catalogHelper
     * @param TaxCalculation $taxCalculation
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
        TaxCalculation $taxCalculation,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_bundlePrice = $bundlePrice;
        $this->priceCurrency = $priceCurrency;
        $this->reviewFactory = $reviewFactory;
        $this->storeManager = $context->getStoreManager();
        $this->imageHelper = $imageHelper;
        $this->catalogHelper = $catalogHelper;
        $this->taxCalculation = $taxCalculation;
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

        return $this->getPriceWithTax($product, $price);
    }

    /**
     * Calculate price with tax using direct tax rate lookup
     *
     * @param ProductInterface $product
     * @param float $price
     * @return float
     */
    protected function getPriceWithTax($product, float $price): float
    {
        try {
            $store = $this->storeManager->getStore();

            // Check if catalog prices already include tax
            $priceIncludesTax = (bool)$this->helper->getConfig(
                \Magento\Tax\Model\Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX,
                $store
            );

            if ($priceIncludesTax) {
                // Catalog prices already include tax, return as-is
                return $price;
            }

            // Get tax rate directly from tax calculation
            $taxClassId = $product->getTaxClassId();
            if (!$taxClassId) {
                return $price;
            }

            // Build request for tax rate lookup
            $request = $this->taxCalculation->getRateRequest(
                null,  // shipping address (uses default)
                null,  // billing address (uses default)
                null,  // customer tax class (uses default)
                $store
            );
            $request->setProductClassId($taxClassId);

            // Get tax rate (e.g., 19 for 19%)
            $taxRate = $this->taxCalculation->getRate($request);

            if ($taxRate > 0) {
                // Add tax to price
                return $price * (1 + ($taxRate / 100));
            }

            return $price;
        } catch (\Exception $e) {
            // Fallback: return original price if tax calculation fails
            return $price;
        }
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

        return $this->getPriceWithTax($product, $price);
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

    /**
     * Get product last modified date
     *
     * @return string|null
     */
    public function getDateModified(): ?string
    {
        $product = $this->getProduct();
        if (!$product) {
            return null;
        }

        // Try updated_at first, fallback to created_at
        $date = $product->getUpdatedAt() ?: $product->getCreatedAt();
        if ($date) {
            return date('Y-m-d', strtotime($date));
        }

        return null;
    }

    /**
     * Get energy efficiency rating for Schema.org
     *
     * @return array|null
     */
    public function getEnergyEfficiency(): ?array
    {
        $product = $this->getProduct();
        if (!$product) {
            return null;
        }

        $energyAttribute = $this->helper->getConfig('flipdev_seo/product_sd/energy_efficiency_attribute');
        if (!$energyAttribute) {
            return null;
        }

        // Try to get attribute text first (for select/dropdown attributes)
        $energyClass = $product->getAttributeText($energyAttribute);
        if (!$energyClass) {
            $energyClass = $product->getData($energyAttribute);
        }

        if (!$energyClass || is_array($energyClass)) {
            return null;
        }

        $energyClass = strtoupper(trim((string)$energyClass));

        // Map energy classes to Schema.org EUEnergyEfficiencyEnumeration
        // Old scale: A+++, A++, A+, A, B, C, D, E, F, G
        // New scale (2021+): A, B, C, D, E, F, G
        $energyMap = [
            'A+++' => 'https://schema.org/EUEnergyEfficiencyCategoryA3Plus',
            'A++' => 'https://schema.org/EUEnergyEfficiencyCategoryA2Plus',
            'A+' => 'https://schema.org/EUEnergyEfficiencyCategoryA1Plus',
            'A' => 'https://schema.org/EUEnergyEfficiencyCategoryA',
            'B' => 'https://schema.org/EUEnergyEfficiencyCategoryB',
            'C' => 'https://schema.org/EUEnergyEfficiencyCategoryC',
            'D' => 'https://schema.org/EUEnergyEfficiencyCategoryD',
            'E' => 'https://schema.org/EUEnergyEfficiencyCategoryE',
            'F' => 'https://schema.org/EUEnergyEfficiencyCategoryF',
            'G' => 'https://schema.org/EUEnergyEfficiencyCategoryG',
        ];

        if (!isset($energyMap[$energyClass])) {
            return null;
        }

        return [
            '@type' => 'EnergyConsumptionDetails',
            'hasEnergyEfficiencyCategory' => $energyMap[$energyClass],
        ];
    }

    /**
     * Check if product has a price range (configurable, grouped, bundle, or tier prices)
     *
     * @return bool
     */
    public function hasPriceRange(): bool
    {
        $product = $this->getProduct();
        if (!$product) {
            return false;
        }

        $productType = $product->getTypeId();

        // Configurable, grouped, and bundle products typically have price ranges
        if (in_array($productType, ['configurable', 'grouped', 'bundle'])) {
            $range = $this->getPriceRange();
            return $range['lowPrice'] < $range['highPrice'];
        }

        // Check for tier prices
        $tierPrices = $product->getTierPrices();
        if (!empty($tierPrices)) {
            return true;
        }

        return false;
    }

    /**
     * Get price range for products with multiple prices
     *
     * @return array ['lowPrice' => float, 'highPrice' => float, 'offerCount' => int]
     */
    public function getPriceRange(): array
    {
        $product = $this->getProduct();
        if (!$product) {
            return ['lowPrice' => 0, 'highPrice' => 0, 'offerCount' => 1];
        }

        $productType = $product->getTypeId();
        $prices = [];
        $offerCount = 1;

        switch ($productType) {
            case 'configurable':
                $prices = $this->getConfigurablePriceRange($product);
                $offerCount = count($prices) ?: 1;
                break;

            case 'grouped':
                $prices = $this->getGroupedPriceRange($product);
                $offerCount = count($prices) ?: 1;
                break;

            case 'bundle':
                $minPrice = (float)$this->_bundlePrice->getTotalPrices($product, 'min', 1);
                $maxPrice = (float)$this->_bundlePrice->getTotalPrices($product, 'max', 1);
                $prices = [$minPrice, $maxPrice];
                $offerCount = 2; // Bundle has min/max configuration
                break;

            default:
                // Simple product - check for tier prices
                $tierPrices = $this->getTierPriceRange($product);
                if (!empty($tierPrices)) {
                    $prices = $tierPrices;
                    $offerCount = count($tierPrices);
                } else {
                    $prices = [(float)$product->getFinalPrice()];
                }
                break;
        }

        if (empty($prices)) {
            $regularPrice = (float)$product->getFinalPrice();
            return [
                'lowPrice' => $this->getPriceWithTax($product, $regularPrice),
                'highPrice' => $this->getPriceWithTax($product, $regularPrice),
                'offerCount' => 1,
            ];
        }

        $lowPrice = min($prices);
        $highPrice = max($prices);

        return [
            'lowPrice' => $this->getPriceWithTax($product, $lowPrice),
            'highPrice' => $this->getPriceWithTax($product, $highPrice),
            'offerCount' => $offerCount,
        ];
    }

    /**
     * Get price range from configurable product children
     *
     * @param ProductInterface $product
     * @return array
     */
    protected function getConfigurablePriceRange($product): array
    {
        $prices = [];

        try {
            /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $typeInstance */
            $typeInstance = $product->getTypeInstance();
            $children = $typeInstance->getUsedProducts($product);

            foreach ($children as $child) {
                if ($child->isSaleable()) {
                    $prices[] = (float)$child->getFinalPrice();
                }
            }
        } catch (\Exception $e) {
            // Fallback to product price
            $prices[] = (float)$product->getFinalPrice();
        }

        return $prices;
    }

    /**
     * Get price range from grouped product children
     *
     * @param ProductInterface $product
     * @return array
     */
    protected function getGroupedPriceRange($product): array
    {
        $prices = [];

        try {
            /** @var \Magento\GroupedProduct\Model\Product\Type\Grouped $typeInstance */
            $typeInstance = $product->getTypeInstance();
            $children = $typeInstance->getAssociatedProducts($product);

            foreach ($children as $child) {
                if ($child->isSaleable()) {
                    $prices[] = (float)$child->getFinalPrice();
                }
            }
        } catch (\Exception $e) {
            // Fallback to product price
            $prices[] = (float)$product->getFinalPrice();
        }

        return $prices;
    }

    /**
     * Get tier price range for a product
     *
     * @param ProductInterface $product
     * @return array
     */
    protected function getTierPriceRange($product): array
    {
        $prices = [];
        $tierPrices = $product->getTierPrices();

        if (empty($tierPrices)) {
            return $prices;
        }

        // Add regular price
        $prices[] = (float)$product->getPrice();

        // Add all tier prices
        foreach ($tierPrices as $tierPrice) {
            $prices[] = (float)$tierPrice->getValue();
        }

        return $prices;
    }

    /**
     * Get the lowest price (for AggregateOffer)
     *
     * @return float
     */
    public function getLowPrice(): float
    {
        $range = $this->getPriceRange();
        return $range['lowPrice'];
    }

    /**
     * Get the highest price (for AggregateOffer)
     *
     * @return float
     */
    public function getHighPrice(): float
    {
        $range = $this->getPriceRange();
        return $range['highPrice'];
    }

    /**
     * Get offer count for AggregateOffer
     *
     * @return int
     */
    public function getOfferCount(): int
    {
        $range = $this->getPriceRange();
        return $range['offerCount'];
    }

    /**
     * Check if product has videos in gallery
     *
     * @return bool
     */
    public function hasVideos(): bool
    {
        $videos = $this->getProductVideos();
        return !empty($videos);
    }

    /**
     * Get product videos from gallery as VideoObject schema
     *
     * @return array
     */
    public function getProductVideos(): array
    {
        $product = $this->getProduct();
        if (!$product) {
            return [];
        }

        $videos = [];
        $mediaGallery = $product->getMediaGalleryEntries();

        if (!$mediaGallery) {
            return [];
        }

        foreach ($mediaGallery as $mediaEntry) {
            // Check if this is an external video
            if ($mediaEntry->getMediaType() !== 'external-video') {
                continue;
            }

            $videoData = $mediaEntry->getExtensionAttributes()?->getVideoContent();
            if (!$videoData) {
                continue;
            }

            $videoUrl = $videoData->getVideoUrl();
            if (!$videoUrl) {
                continue;
            }

            $video = $this->buildVideoObject($videoData, $videoUrl, $product);
            if ($video) {
                $videos[] = $video;
            }
        }

        return $videos;
    }

    /**
     * Build VideoObject schema from video data
     *
     * @param mixed $videoData
     * @param string $videoUrl
     * @param ProductInterface $product
     * @return array|null
     */
    protected function buildVideoObject($videoData, string $videoUrl, $product): ?array
    {
        $videoObject = [
            '@type' => 'VideoObject',
            'name' => $videoData->getVideoTitle() ?: $product->getName(),
            'description' => $videoData->getVideoDescription() ?: $this->getProductDescription(),
            'uploadDate' => $product->getCreatedAt() ? date('Y-m-d\TH:i:sP', strtotime($product->getCreatedAt())) : date('Y-m-d\TH:i:sP'),
        ];

        // Handle YouTube URLs
        $youtubeId = $this->extractYoutubeId($videoUrl);
        if ($youtubeId) {
            $videoObject['thumbnailUrl'] = 'https://img.youtube.com/vi/' . $youtubeId . '/maxresdefault.jpg';
            $videoObject['embedUrl'] = 'https://www.youtube.com/embed/' . $youtubeId;
            $videoObject['contentUrl'] = 'https://www.youtube.com/watch?v=' . $youtubeId;
            return $videoObject;
        }

        // Handle Vimeo URLs
        $vimeoId = $this->extractVimeoId($videoUrl);
        if ($vimeoId) {
            $videoObject['embedUrl'] = 'https://player.vimeo.com/video/' . $vimeoId;
            $videoObject['contentUrl'] = 'https://vimeo.com/' . $vimeoId;
            // Vimeo thumbnails require API call, skip for now
            return $videoObject;
        }

        // Generic video URL
        $videoObject['contentUrl'] = $videoUrl;
        return $videoObject;
    }

    /**
     * Extract YouTube video ID from URL
     *
     * @param string $url
     * @return string|null
     */
    protected function extractYoutubeId(string $url): ?string
    {
        $patterns = [
            '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/',
            '/youtu\.be\/([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/v\/([a-zA-Z0-9_-]+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Extract Vimeo video ID from URL
     *
     * @param string $url
     * @return string|null
     */
    protected function extractVimeoId(string $url): ?string
    {
        if (preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
