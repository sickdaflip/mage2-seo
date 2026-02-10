<?php
/**
 * Copyright (c) 2025 Flipdev. All rights reserved.
 */
declare(strict_types=1);

namespace FlipDev\Seo\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CronHour implements OptionSourceInterface
{
    /**
     * Get options for cron hour dropdown
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $label = sprintf('%02d:00', $hour);
            $options[] = [
                'value' => $hour,
                'label' => $label
            ];
        }
        return $options;
    }
}
