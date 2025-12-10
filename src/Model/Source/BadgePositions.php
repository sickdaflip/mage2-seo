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

namespace FlipDev\Seo\Model\Source;

class BadgePositions implements \Magento\Framework\Option\ArrayInterface
{
    
    public function toOptionArray()
    {
        return [
            ['value' => 'BOTTOM_RIGHT', 'label' => __('Bottom Right (Default)')],
            ['value' => 'BOTTOM_LEFT', 'label' => __('Bottom Left')],
            ['value' => 'USER_DEFINED', 'label' => __('User Defined')]
        ];
    }
}
