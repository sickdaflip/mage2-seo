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

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'FlipDev_Seo',
    __DIR__ . '/src'
);