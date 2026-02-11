<?php
/**
 * Copyright (c) 2025 Flipdev. All rights reserved.
 */
declare(strict_types=1);

namespace FlipDev\Seo\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class FilterRobots implements OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => '', 'label' => __('Disabled')],
            ['value' => 'NOINDEX,FOLLOW', 'label' => __('NOINDEX, FOLLOW')],
            ['value' => 'NOINDEX,NOFOLLOW', 'label' => __('NOINDEX, NOFOLLOW')],
        ];
    }
}
