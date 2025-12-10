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

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Remove NOINDEX from pagination pages
 *
 * According to Google's current guidelines (2024+):
 * - Pagination pages should have self-referencing canonical URLs
 * - Pagination pages should be INDEX,FOLLOW (not NOINDEX)
 * - NOINDEX should only be used for filters/sorting, not regular pagination
 *
 * This observer removes the NOINDEX that Magento sets on pagination pages,
 * allowing them to be indexed with their self-referencing canonicals.
 */
class FixPaginationCanonical implements ObserverInterface
{
    private PageConfig $pageConfig;
    private RequestInterface $request;
    private LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param PageConfig $pageConfig
     * @param RequestInterface $request
     * @param LoggerInterface $logger
     */
    public function __construct(
        PageConfig $pageConfig,
        RequestInterface $request,
        LoggerInterface $logger
    ) {
        $this->pageConfig = $pageConfig;
        $this->request = $request;
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
            $page = $this->request->getParam('p');

            // Only process if we're on a paginated page (p > 1)
            if (!$page || (int)$page <= 1) {
                return;
            }

            // Remove NOINDEX from pagination pages
            // Magento sets NOINDEX,FOLLOW by default, but according to Google's
            // current guidelines, pagination should be INDEX,FOLLOW
            $this->pageConfig->setRobots('INDEX,FOLLOW');

            $this->logger->debug(
                'FlipDev_Seo: Removed NOINDEX from pagination page',
                ['page' => $page]
            );

        } catch (\Exception $e) {
            $this->logger->error('FlipDev_Seo: Pagination robots fix failed', [
                'exception' => $e->getMessage()
            ]);
        }
    }
}
