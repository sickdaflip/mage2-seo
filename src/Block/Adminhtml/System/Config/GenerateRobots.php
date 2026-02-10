<?php
/**
 * Copyright (c) 2025 Flipdev. All rights reserved.
 */
declare(strict_types=1);

namespace FlipDev\Seo\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class GenerateRobots extends Field
{
    protected $_template = 'FlipDev_Seo::system/config/generate_robots.phtml';

    /**
     * Remove scope label
     */
    public function render(AbstractElement $element): string
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for generate button
     */
    public function getAjaxUrl(): string
    {
        return $this->getUrl('flipdev_seo/robots/generate');
    }

    /**
     * Generate button html
     */
    public function getButtonHtml(): string
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData([
            'id' => 'flipdev_seo_generate_robots',
            'label' => __('Generate robots.txt Now'),
        ]);

        return $button->toHtml();
    }
}
