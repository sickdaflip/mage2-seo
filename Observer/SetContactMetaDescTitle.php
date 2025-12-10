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
 * Set custom title and meta description for contact page
 */
class SetContactMetaDescTitle implements ObserverInterface
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
            $metaDescription = $this->seoHelper->getConfig('flipdevseo/metadata/contact_metadesc');
            if ($metaDescription) {
                $this->pageConfig->setDescription($metaDescription);
            }

            $pageTitle = $this->seoHelper->getConfig('flipdevseo/metadata/contact_title');
            if ($pageTitle) {
                $this->pageConfig->getTitle()->set($pageTitle);
            }
            
            $this->logger->debug('FlipDev_Seo: Set contact page meta');
        } catch (\Exception $e) {
            $this->logger->error('FlipDev_Seo: Contact meta failed', [
                'exception' => $e->getMessage()
            ]);
        }
    }
}
