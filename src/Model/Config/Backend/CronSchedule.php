<?php
/**
 * Copyright (c) 2025 Flipdev. All rights reserved.
 */
declare(strict_types=1);

namespace FlipDev\Seo\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class CronSchedule extends Value
{
    private const CRON_STRING_PATH = 'crontab/default/jobs/flipdev_seo_generate_sitemap/schedule/cron_expr';

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        private ValueFactory $configValueFactory,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * After save, update cron expression
     *
     * @return $this
     */
    public function afterSave(): static
    {
        $hour = $this->getValue();

        // Build cron expression: "0 {hour} * * *" (daily at specified hour)
        $cronExpr = sprintf('0 %d * * *', (int) $hour);

        try {
            $this->configValueFactory->create()->load(
                self::CRON_STRING_PATH,
                'path'
            )->setValue(
                $cronExpr
            )->setPath(
                self::CRON_STRING_PATH
            )->save();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Failed to save cron expression: %1', $e->getMessage())
            );
        }

        return parent::afterSave();
    }
}
