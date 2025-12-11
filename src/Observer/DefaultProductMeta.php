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

use FlipDev\Seo\Helper\Data as SeoHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Page\Config as PageConfig;
use Psr\Log\LoggerInterface;

/**
 * Set default meta data for products
 */
class DefaultProductMeta implements ObserverInterface
{
    private SeoHelper $seoHelper;
    private PageConfig $pageConfig;
    private LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param SeoHelper $seoHelper
     * @param PageConfig $pageConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        SeoHelper $seoHelper,
        PageConfig $pageConfig,
        LoggerInterface $logger
    ) {
        $this->seoHelper = $seoHelper;
        $this->pageConfig = $pageConfig;
        $this->logger = $logger;
    }

    /**
     * Execute observer logic
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        // ULTRA-SAFE version that catches EVERYTHING and never breaks the page
        try {
            $product = $observer->getEvent()->getProduct();

            if (!$product || !is_object($product)) {
                return;
            }

            // Wrap EVERY operation in its own try-catch to isolate failures

            // Step 1: Clean meta description
            try {
                if (!empty($product->getMetaDescription())) {
                    $metaDesc = $product->getMetaDescription();
                    $metaDesc = strip_tags($metaDesc);
                    $metaDesc = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $metaDesc);
                    $metaDesc = preg_replace('/\s+/', ' ', $metaDesc);
                    $metaDesc = trim($metaDesc);
                    $product->setMetaDescription($metaDesc);
                }
            } catch (\Throwable $e) {
                $this->logger->debug('FlipDev_Seo: Could not clean meta description', [
                    'error' => $e->getMessage()
                ]);
            }

            // Step 2: Clean meta title
            try {
                if (!empty($product->getMetaTitle())) {
                    $metaTitle = $product->getMetaTitle();
                    $metaTitle = strip_tags($metaTitle);
                    $metaTitle = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $metaTitle);
                    $metaTitle = preg_replace('/\s+/', ' ', $metaTitle);
                    $metaTitle = trim($metaTitle);
                    $product->setMetaTitle($metaTitle);
                }
            } catch (\Throwable $e) {
                $this->logger->debug('FlipDev_Seo: Could not clean meta title', [
                    'error' => $e->getMessage()
                ]);
            }

            // Step 3: Set default meta data (MOST LIKELY TO FAIL)
            try {
                $this->seoHelper->checkMetaData($product, 'product');
            } catch (\Throwable $e) {
                $this->logger->debug('FlipDev_Seo: checkMetaData failed', [
                    'error' => $e->getMessage()
                ]);
                // Continue anyway - this is optional
            }

            // Step 4: Apply to page config (POTENTIAL COMPATIBILITY ISSUE)
            try {
                if ($product->getMetaTitle()) {
                    $this->pageConfig->setMetaTitle($product->getMetaTitle());
                }
            } catch (\Throwable $e) {
                $this->logger->debug('FlipDev_Seo: Could not set page meta title', [
                    'error' => $e->getMessage()
                ]);
            }

            try {
                if ($product->getMetaDescription()) {
                    $this->pageConfig->setDescription($product->getMetaDescription());
                }
            } catch (\Throwable $e) {
                $this->logger->debug('FlipDev_Seo: Could not set page description', [
                    'error' => $e->getMessage()
                ]);
            }

            // Step 5: Set robots meta (OPTIONAL - ATTRIBUTE MAY NOT EXIST)
            try {
                $robots = $product->getData('flipdevseo_metarobots');
                if ($robots && is_string($robots) && !empty(trim($robots))) {
                    $this->pageConfig->setRobots($robots);
                }
            } catch (\Throwable $e) {
                $this->logger->debug('FlipDev_Seo: Could not set robots meta', [
                    'error' => $e->getMessage()
                ]);
            }

        } catch (\Throwable $e) {
            // ULTIMATE FAILSAFE - Log but NEVER throw
            $this->logger->error('FlipDev_Seo: DefaultProductMeta completely failed', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Absolutely do not throw - just return
            return;
        }
    }
}
