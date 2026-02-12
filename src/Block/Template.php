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

namespace FlipDev\Seo\Block {

    class Template extends \Magento\Framework\View\Element\Template
    {

        protected \FlipDev\Seo\Helper\Data $helper;

        /**
         * @param \Magento\Framework\View\Element\Template\Context $context
         * @param \FlipDev\Seo\Helper\Data $flipDevSeoHelper
         * @param array $data
         */
        public function __construct(
            \Magento\Framework\View\Element\Template\Context $context,
            \FlipDev\Seo\Helper\Data $flipDevSeoHelper,
            array $data = []
        )
        {
            $this->helper = $flipDevSeoHelper;
            parent::__construct($context, $data);
        }

        /**
         * @param string $configPath
         * @return string|null
         */
        public function getConfig(string $configPath): ?string
        {
            return $this->helper->getConfig($configPath);
        }
    }
}