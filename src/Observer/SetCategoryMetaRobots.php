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
 * Set NOINDEX,FOLLOW for filtered category pages
 */
class SetCategoryMetaRobots implements ObserverInterface
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
            $request = $observer->getEvent()->getRequest();

            if (!$request) {
                return;
            }

            // Get configured robots value for filtered pages (empty = disabled)
            $robotsValue = $this->seoHelper->getConfig('flipdev_seo/settings/noindexparams');
            if (!$robotsValue) {
                return;
            }

            // Get the query string from the URL
            $queryString = $request->getServer('QUERY_STRING');

            if (!$queryString) {
                return;
            }

            // Parse query string into parameters
            parse_str($queryString, $queryParams);

            // Remove pagination parameter - pagination should be indexed
            unset($queryParams['p']);

            // If there are still other parameters (filters, sorting), set configured robots value
            if (!empty($queryParams)) {
                $this->pageConfig->setRobots($robotsValue);

                $this->logger->debug('FlipDev_Seo: Set robots for filtered/sorted category', [
                    'robots' => $robotsValue,
                    'params' => array_keys($queryParams)
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('FlipDev_Seo: Category robots failed', [
                'exception' => $e->getMessage()
            ]);
        }
    }
}
