<?php
/**
 * Copyright (c) 2025 Flipdev. All rights reserved.
 */
declare(strict_types=1);

namespace FlipDev\Seo\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Changefreq implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'always', 'label' => __('Always')],
            ['value' => 'hourly', 'label' => __('Hourly')],
            ['value' => 'daily', 'label' => __('Daily')],
            ['value' => 'weekly', 'label' => __('Weekly')],
            ['value' => 'monthly', 'label' => __('Monthly')],
            ['value' => 'yearly', 'label' => __('Yearly')],
            ['value' => 'never', 'label' => __('Never')],
        ];
    }
}
