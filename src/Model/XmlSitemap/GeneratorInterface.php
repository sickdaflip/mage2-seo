<?php
/**
 * Copyright (c) 2025 Flipdev. All rights reserved.
 */
declare(strict_types=1);

namespace FlipDev\Seo\Model\XmlSitemap;

interface GeneratorInterface
{
    /**
     * Generate sitemap items for a specific store
     *
     * @param int $storeId
     * @return array Array of sitemap items with 'loc', 'lastmod', 'changefreq', 'priority', 'images', 'hreflang'
     */
    public function generate(int $storeId): array;

    /**
     * Get the sitemap filename
     *
     * @return string
     */
    public function getFilename(): string;

    /**
     * Check if this generator is enabled
     *
     * @param int $storeId
     * @return bool
     */
    public function isEnabled(int $storeId): bool;
}
