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

use Magento\Framework\Data\OptionSourceInterface;

class ReturnFees implements OptionSourceInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'FreeReturn', 'label' => __('Free Return')],
            ['value' => 'ReturnFeesCustomerResponsibility', 'label' => __('Customer Pays Return Shipping')],
            ['value' => 'ReturnShippingFees', 'label' => __('Return Shipping Fees Apply')],
        ];
    }
}
