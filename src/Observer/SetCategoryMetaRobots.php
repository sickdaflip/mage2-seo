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

            // Check if NOINDEX for filtered pages is enabled
            if (!$this->seoHelper->getConfig('flipdev_seo/settings/noindexparams')) {
                return;
            }

            $requestUrl = $request->getRequestUri();

            // Only apply NOINDEX if there are query parameters
            if (!stristr($requestUrl, '?')) {
                return;
            }

            // Parse query parameters
            $params = $request->getParams();

            // Remove pagination parameter - pagination should be indexed
            unset($params['p']);

            // If there are still other parameters (filters, sorting), set NOINDEX
            if (!empty($params)) {
                $this->pageConfig->setRobots('NOINDEX,FOLLOW');

                $this->logger->debug('FlipDev_Seo: Set NOINDEX for filtered/sorted category', [
                    'params' => array_keys($params)
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('FlipDev_Seo: Category robots failed', [
                'exception' => $e->getMessage()
            ]);
        }
    }
}
