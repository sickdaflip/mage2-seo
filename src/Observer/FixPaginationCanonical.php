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
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;

/**
 * Fix pagination canonical URLs
 *
 * Sets canonical to page 1 (without ?p= parameter) on paginated pages
 * This prevents self-referencing canonicals on NOINDEX pages
 */
class FixPaginationCanonical implements ObserverInterface
{
    private PageConfig $pageConfig;
    private RequestInterface $request;
    private UrlInterface $url;
    private LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param PageConfig $pageConfig
     * @param RequestInterface $request
     * @param UrlInterface $url
     * @param LoggerInterface $logger
     */
    public function __construct(
        PageConfig $pageConfig,
        RequestInterface $request,
        UrlInterface $url,
        LoggerInterface $logger
    ) {
        $this->pageConfig = $pageConfig;
        $this->request = $request;
        $this->url = $url;
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

            // Get current URL without pagination parameter
            $currentUrl = $this->url->getCurrentUrl();

            // Remove ?p=X or &p=X parameter
            $canonicalUrl = preg_replace('/([?&])p=\d+(&|$)/', '$1', $currentUrl);

            // Clean up trailing ? or &
            $canonicalUrl = rtrim($canonicalUrl, '?&');

            // Remove double && or ?&
            $canonicalUrl = preg_replace('/[?&]{2,}/', '?', $canonicalUrl);

            // Remove existing canonical tags first
            $assetCollection = $this->pageConfig->getAssetCollection();
            foreach ($assetCollection->getAll() as $asset) {
                if ($asset->getContentType() === 'canonical') {
                    $assetCollection->remove($asset->getUrl());
                }
            }

            // Set the canonical URL to page 1
            $this->pageConfig->addRemotePageAsset(
                $canonicalUrl,
                'canonical',
                ['attributes' => ['rel' => 'canonical']]
            );

            $this->logger->debug(
                'FlipDev_Seo: Fixed pagination canonical',
                [
                    'page' => $page,
                    'original' => $currentUrl,
                    'canonical' => $canonicalUrl
                ]
            );

        } catch (\Exception $e) {
            $this->logger->error('FlipDev_Seo: Pagination canonical fix failed', [
                'exception' => $e->getMessage()
            ]);
        }
    }
}
