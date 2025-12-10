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

class Robots extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['value' => '', 'label' => 'Use Config Settings'],
                ['value' => 'INDEX,FOLLOW', 'label' => 'INDEX, FOLLOW'],
                ['value' => 'NOINDEX,FOLLOW', 'label' => 'NOINDEX, FOLLOW'],
                ['value' => 'INDEX,NOFOLLOW', 'label' => 'INDEX, NOFOLLOW'],
                ['value' => 'NOINDEX,NOFOLLOW', 'label' => 'NOINDEX, NOFOLLOW']
            ];
        }
        return $this->_options;
    }
}