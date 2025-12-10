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
        
        private $helper;

        /**
         * @param \Magento\Framework\View\Element\Template\Context $context
         * @param \FlipDev\Seo\Helper\Data $foxSeoHelper
         * @param array $data
         */
        public function __construct(
            \Magento\Framework\View\Element\Template\Context $context,
            \FlipDev\Seo\Helper\Data $foxSeoHelper,
            array $data = []
        )
        {
            $this->helper = $foxSeoHelper;
            parent::__construct($context, $data);
        }

        /**
         * @param $configpath
         * @return mixed
         */
        public function getConfig(string $configPath): ?string
        {
            return $this->helper->getConfig($configpath);
        }
    }
}