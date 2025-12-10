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

class Discontinued extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('No Redirect (404)'), 'value' => 0],
                ['label' => __('301 Redirect to Category'), 'value' => 1],
                ['label' => __('301 Redirect to Homepage'), 'value' => 2],
                ['label' => __('301 Redirect to Product (Enter SKU)'), 'value' => 3]
            ];
        }
        return $this->_options;
    }
}
