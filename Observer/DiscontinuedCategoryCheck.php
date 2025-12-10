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

use Magento\Catalog\Model\Category;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;

/**
 * Handle redirects for disabled categories
 */
class DiscontinuedCategoryCheck implements ObserverInterface
{
    private HttpResponse $response;
    private UrlInterface $urlBuilder;
    private LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param HttpResponse $response
     * @param UrlInterface $urlBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        HttpResponse $response,
        UrlInterface $urlBuilder,
        LoggerInterface $logger
    ) {
        $this->response = $response;
        $this->urlBuilder = $urlBuilder;
        $this->logger = $logger;
    }

    /**
     * Check if category is disabled and redirect to homepage
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        try {
            $category = $observer->getEvent()->getCategory();
            
            if (!$category || $category->getIsActive()) {
                return;
            }

            $redirectUrl = $this->getRedirectUrl($category);
            
            if ($redirectUrl) {
                $this->response->setRedirect($redirectUrl, 301);
                
                $this->logger->info('FlipDev_Seo: Disabled category redirect', [
                    'category_id' => $category->getId(),
                    'category_name' => $category->getName(),
                    'redirect_url' => $redirectUrl
                ]);
            }
            
        } catch (\Exception $e) {
            $this->logger->error('FlipDev_Seo: Category check failed', [
                'exception' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get redirect URL for disabled category
     *
     * @param Category $category
     * @return string
     */
    private function getRedirectUrl(Category $category): string
    {
        // Try parent category first
        $parentId = $category->getParentId();
        
        if ($parentId && $parentId > 1) {
            try {
                $parentCategory = $category->getParentCategory();
                
                if ($parentCategory && $parentCategory->getIsActive()) {
                    $this->logger->debug('FlipDev_Seo: Redirecting to parent category', [
                        'category_id' => $category->getId(),
                        'parent_id' => $parentId
                    ]);
                    
                    return $parentCategory->getUrl();
                }
            } catch (\Exception $e) {
                $this->logger->warning('FlipDev_Seo: Could not load parent category', [
                    'parent_id' => $parentId,
                    'exception' => $e->getMessage()
                ]);
            }
        }

        // Fallback to homepage
        return $this->urlBuilder->getUrl('');
    }
}
