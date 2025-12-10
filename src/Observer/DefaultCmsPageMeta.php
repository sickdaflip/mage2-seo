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
 * Set default meta data for CMS pages
 */
class DefaultCmsPageMeta implements ObserverInterface
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
            $page = $observer->getEvent()->getPage();

            if (!$page) {
                return;
            }

            // Clean existing meta description if set
            if (!empty($page->getMetaDescription())) {
                $metaDesc = $page->getMetaDescription();
                $metaDesc = strip_tags($metaDesc);
                $metaDesc = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $metaDesc);
                $metaDesc = preg_replace('/\s+/', ' ', $metaDesc);
                $metaDesc = trim($metaDesc);
                $page->setMetaDescription($metaDesc);
            }

            // Clean existing meta title if set
            if (!empty($page->getMetaTitle())) {
                $metaTitle = $page->getMetaTitle();
                $metaTitle = strip_tags($metaTitle);
                $metaTitle = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $metaTitle);
                $metaTitle = preg_replace('/\s+/', ' ', $metaTitle);
                $metaTitle = trim($metaTitle);
                $page->setMetaTitle($metaTitle);
            }

            // Set default meta description if not set
            if (empty($page->getMetaDescription()) &&
                $this->seoHelper->getConfig('flipdev_seo/metadata/cms_metadesc_enabled')) {

                $template = $this->seoHelper->getConfig('flipdev_seo/metadata/cms_metadesc');
                if ($template) {
                    $metaDesc = str_replace('[title]', $page->getTitle(), $template);
                    $metaDesc = str_replace('[store]', $this->seoHelper->getConfig('general/store_information/name'), $metaDesc);

                    // Clean the meta description
                    $metaDesc = strip_tags($metaDesc);
                    $metaDesc = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $metaDesc);
                    $metaDesc = preg_replace('/\s+/', ' ', $metaDesc);
                    $metaDesc = trim($metaDesc);

                    $page->setMetaDescription($metaDesc);
                }
            }

            // Apply meta data to page config
            if ($page->getMetaTitle()) {
                $this->pageConfig->setMetaTitle($page->getMetaTitle());
            }
            if ($page->getMetaDescription()) {
                $this->pageConfig->setDescription($page->getMetaDescription());
            }

        } catch (\Exception $e) {
            $this->logger->error('FlipDev_Seo: CMS page meta data failed', [
                'exception' => $e->getMessage()
            ]);
        }
    }
}
