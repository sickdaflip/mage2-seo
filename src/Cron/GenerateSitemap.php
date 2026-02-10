<?php
/**
 * Copyright (c) 2025 Flipdev. All rights reserved.
 */
declare(strict_types=1);

namespace FlipDev\Seo\Cron;

use FlipDev\Seo\Model\XmlSitemap\SitemapBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

class GenerateSitemap
{
    private const CONFIG_CRON_ENABLED = 'flipdev_seo/xml_sitemap/cron_enabled';

    public function __construct(
        private SitemapBuilder $sitemapBuilder,
        private ScopeConfigInterface $scopeConfig,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Execute cron job to generate sitemaps
     */
    public function execute(): void
    {
        // Check if cron is enabled
        if (!$this->scopeConfig->isSetFlag(self::CONFIG_CRON_ENABLED)) {
            return;
        }

        $this->logger->info('FlipDev_Seo: Starting sitemap generation via cron');

        try {
            $results = $this->sitemapBuilder->generateForAllStores();

            foreach ($results as $storeId => $result) {
                if ($result['success']) {
                    $this->logger->info('FlipDev_Seo: Sitemap generated successfully', [
                        'store_id' => $storeId,
                        'files' => $result['files']
                    ]);
                } else {
                    $this->logger->error('FlipDev_Seo: Sitemap generation failed', [
                        'store_id' => $storeId,
                        'error' => $result['error']
                    ]);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('FlipDev_Seo: Critical error during sitemap generation', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
