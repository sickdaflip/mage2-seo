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
        try {
            $product = $observer->getEvent()->getProduct();

            if (!$product) {
                return;
            }

            // Clean existing meta description if set
            if (!empty($product->getMetaDescription())) {
                $metaDesc = $product->getMetaDescription();
                $metaDesc = strip_tags($metaDesc);
                $metaDesc = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $metaDesc);
                $metaDesc = preg_replace('/\s+/', ' ', $metaDesc);
                $metaDesc = trim($metaDesc);
                $product->setMetaDescription($metaDesc);
            }

            // Clean existing meta title if set
            if (!empty($product->getMetaTitle())) {
                $metaTitle = $product->getMetaTitle();
                $metaTitle = strip_tags($metaTitle);
                $metaTitle = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $metaTitle);
                $metaTitle = preg_replace('/\s+/', ' ', $metaTitle);
                $metaTitle = trim($metaTitle);
                $product->setMetaTitle($metaTitle);
            }

            // Set default meta data if not set
            $this->seoHelper->checkMetaData($product, 'product');

            // Apply meta data to page config
            if ($product->getMetaTitle()) {
                $this->pageConfig->setMetaTitle($product->getMetaTitle());
            }
            if ($product->getMetaDescription()) {
                $this->pageConfig->setDescription($product->getMetaDescription());
            }

            $robots = $product->getData('flipdevseo_metarobots');
            if ($robots) {
                $this->pageConfig->setRobots($robots);
            }
        } catch (\Exception $e) {
            $this->logger->error('FlipDev_Seo: Product meta data failed', [
                'exception' => $e->getMessage()
            ]);
        }
    }
}
