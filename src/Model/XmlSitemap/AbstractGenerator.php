<?php
/**
 * Copyright (c) 2025 Flipdev. All rights reserved.
 */
declare(strict_types=1);

namespace FlipDev\Seo\Model\XmlSitemap;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

abstract class AbstractGenerator implements GeneratorInterface
{
    protected const CONFIG_PATH_ENABLED = '';
    protected const CONFIG_PATH_PRIORITY = '';
    protected const CONFIG_PATH_CHANGEFREQ = '';

    public function __construct(
        protected ScopeConfigInterface $scopeConfig,
        protected StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(int $storeId): bool
    {
        if (empty(static::CONFIG_PATH_ENABLED)) {
            return true;
        }

        return $this->scopeConfig->isSetFlag(
            static::CONFIG_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get priority from config
     */
    protected function getPriority(int $storeId): string
    {
        if (empty(static::CONFIG_PATH_PRIORITY)) {
            return '0.5';
        }

        return (string) $this->scopeConfig->getValue(
            static::CONFIG_PATH_PRIORITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ?: '0.5';
    }

    /**
     * Get change frequency from config
     */
    protected function getChangefreq(int $storeId): string
    {
        if (empty(static::CONFIG_PATH_CHANGEFREQ)) {
            return 'weekly';
        }

        return (string) $this->scopeConfig->getValue(
            static::CONFIG_PATH_CHANGEFREQ,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ?: 'weekly';
    }

    /**
     * Format date for sitemap
     */
    protected function formatDate(?string $date): string
    {
        if (!$date) {
            return date('c');
        }

        try {
            return (new \DateTime($date))->format('c');
        } catch (\Exception $e) {
            return date('c');
        }
    }

    /**
     * Get base URL for store
     */
    protected function getBaseUrl(int $storeId): string
    {
        try {
            $store = $this->storeManager->getStore($storeId);
            return rtrim($store->getBaseUrl(), '/');
        } catch (\Exception $e) {
            return '';
        }
    }
}
