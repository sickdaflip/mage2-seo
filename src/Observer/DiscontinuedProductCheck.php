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

namespace FlipDev\Seo\Observer;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;

/**
 * Handle redirects for discontinued/disabled products
 */
class DiscontinuedProductCheck implements ObserverInterface
{
    private const REDIRECT_NONE = '0';
    private const REDIRECT_CATEGORY = '1';
    private const REDIRECT_HOMEPAGE = '2';
    private const REDIRECT_PRODUCT = '3';

    private HttpResponse $response;
    private UrlInterface $urlBuilder;
    private LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param HttpResponse $response
     * @param UrlInterface $urlBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        HttpResponse $response,
        UrlInterface $urlBuilder,
        LoggerInterface $logger
    ) {
        $this->response = $response;
        $this->urlBuilder = $urlBuilder;
        $this->logger = $logger;
    }

    /**
     * Check if product is discontinued and handle redirect
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        try {
            $product = $observer->getEvent()->getProduct();
            
            if (!$product || $product->getStatus() == Product\Attribute\Source\Status::STATUS_ENABLED) {
                return;
            }

            $discontinuedType = $product->getData('flipdevseo_discontinued');
            if (!$discontinuedType) {
                return;
            }

            $redirectUrl = $this->getRedirectUrl($product, (string)$discontinuedType);
            
            if ($redirectUrl) {
                $this->response->setRedirect($redirectUrl, 301);
                
                $this->logger->info('FlipDev_Seo: Discontinued product redirect', [
                    'product_id' => $product->getId(),
                    'product_sku' => $product->getSku(),
                    'redirect_type' => $discontinuedType,
                    'redirect_url' => $redirectUrl
                ]);
            }
            
        } catch (\Exception $e) {
            $this->logger->error('FlipDev_Seo: Discontinued product check failed', [
                'exception' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get redirect URL based on discontinued type
     *
     * @param Product $product
     * @param string $discontinuedType
     * @return string|null
     */
    private function getRedirectUrl(Product $product, string $discontinuedType): ?string
    {
        switch ($discontinuedType) {
            case self::REDIRECT_CATEGORY:
                return $this->getCategoryRedirectUrl($product);
                
            case self::REDIRECT_HOMEPAGE:
                return $this->urlBuilder->getUrl('');
                
            case self::REDIRECT_PRODUCT:
                return $this->getProductRedirectUrl($product);
                
            case self::REDIRECT_NONE:
            default:
                return null;
        }
    }

    /**
     * Get parent category redirect URL
     *
     * @param Product $product
     * @return string
     */
    private function getCategoryRedirectUrl(Product $product): string
    {
        $categoryIds = $product->getCategoryIds();
        
        if (empty($categoryIds)) {
            $this->logger->warning('FlipDev_Seo: No category found for redirect', [
                'product_id' => $product->getId()
            ]);
            return $this->urlBuilder->getUrl('');
        }

        $categoryId = reset($categoryIds);
        $category = $product->getCategoryCollection()
            ->addAttributeToSelect('url_key')
            ->addIdFilter($categoryId)
            ->getFirstItem();

        if ($category && $category->getId()) {
            return $category->getUrl();
        }

        return $this->urlBuilder->getUrl('');
    }

    /**
     * Get alternative product redirect URL
     *
     * @param Product $product
     * @return string
     */
    private function getProductRedirectUrl(Product $product): string
    {
        $targetSku = $product->getData('flipdevseo_discontinued_product');
        
        if (!$targetSku) {
            $this->logger->warning('FlipDev_Seo: No target SKU specified', [
                'product_id' => $product->getId()
            ]);
            return $this->urlBuilder->getUrl('');
        }

        try {
            $productRepository = $product->getResource();
            $targetProductId = $productRepository->getIdBySku($targetSku);
            
            if ($targetProductId) {
                $targetProduct = $product->load($targetProductId);
                if ($targetProduct && $targetProduct->getId()) {
                    return $targetProduct->getProductUrl();
                }
            }
            
        } catch (\Exception $e) {
            $this->logger->error('FlipDev_Seo: Failed to load target product', [
                'target_sku' => $targetSku,
                'exception' => $e->getMessage()
            ]);
        }

        return $this->urlBuilder->getUrl('');
    }
}
