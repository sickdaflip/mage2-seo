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
 * Redirect product URLs to their canonical counterpart
 */
class CanonicalProductRedirect implements ObserverInterface
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
            
            if (!$product || !$this->seoHelper->getConfig('flipdevseo/settings/forcecanonical')) {
                return;
            }

            $request = $observer->getEvent()->getRequest();
            $response = $observer->getEvent()->getResponse();
            
            if ($request && $response) {
                $canonical = $product->getUrlModel()->getUrl($product);
                $current = $request->getRequestUri();
                
                if ($canonical && $canonical !== $current) {
                    $response->setRedirect($canonical, 301);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('FlipDev_Seo: Canonical redirect failed', [
                'exception' => $e->getMessage()
            ]);
        }
    }
}
