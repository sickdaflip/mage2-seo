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
 * Set custom H1 heading for categories
 */
class CategorySeoHeading implements ObserverInterface
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
            $category = $observer->getEvent()->getCategory();
            
            if (!$category || !$this->seoHelper->getConfig('flipdevseo/settings/category_h1')) {
                return;
            }

            $customHeading = $category->getData('flipdevseo_heading');
            if ($customHeading) {
                $category->setName($customHeading);
                
                $this->logger->debug('FlipDev_Seo: Set custom category heading', [
                    'category_id' => $category->getId(),
                    'heading' => $customHeading
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('FlipDev_Seo: Category heading failed', [
                'exception' => $e->getMessage()
            ]);
        }
    }
}
