<?php
/**
 * Copyright (c) 2025 Flipdev. All rights reserved.
 */
declare(strict_types=1);

namespace FlipDev\Seo\Cron;

use FlipDev\Seo\Model\XmlSitemap\SitemapBuilder;
use Psr\Log\LoggerInterface;

class GenerateSitemap
{
    public function __construct(
        private SitemapBuilder $sitemapBuilder,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Execute cron job to generate sitemaps
     */
    public function execute(): void
    {
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
