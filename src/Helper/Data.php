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

namespace FlipDev\Seo\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

/**
 * SEO Helper for handling meta data and shortcode processing
 */
class Data extends AbstractHelper
{
    private const SHORTCODE_STORE = 'store';
    private const CONFIG_STORE_NAME = 'general/store_information/name';
    private const SHORTCODE_PATTERN = '/\[(.*?)\]/';
    
    private LoggerInterface $logger;
    private array $configCache = [];

    /**
     * Constructor
     *
     * @param Context $context
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->logger = $logger;
    }

    /**
     * Get configuration value with caching
     *
     * @param string $configPath
     * @return string|null
     */
    public function getConfig(string $configPath): ?string
    {
        if (!isset($this->configCache[$configPath])) {
            $this->configCache[$configPath] = $this->scopeConfig->getValue(
                $configPath,
                ScopeInterface::SCOPE_STORE
            );
        }
        
        return $this->configCache[$configPath];
    }

    /**
     * Clean string for safe output
     *
     * @param string $string
     * @return string
     */
    public function cleanString(string $string): string
    {
        // Strip HTML tags
        $string = strip_tags($string);

        // Replace newlines, carriage returns, and tabs with spaces
        $string = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $string);

        // Collapse multiple spaces into single space
        $string = preg_replace('/\s+/', ' ', $string);

        // Trim leading and trailing whitespace
        $string = trim($string);

        // Escape quotes and slashes
        return addcslashes($string, '"\\/');
    }

    /**
     * Process shortcodes in template string
     *
     * @param string $template Template with [shortcodes]
     * @param object|null $object Product or Category object
     * @return string Processed template
     */
    public function shortcode(string $template, ?object $object): string
    {
        if (!$object || empty($template)) {
            return $template;
        }

        try {
            preg_match_all(self::SHORTCODE_PATTERN, $template, $matches);
            
            foreach ($matches[1] as $index => $tag) {
                $replacement = $this->getShortcodeValue($tag, $object);
                if ($replacement !== null) {
                    $template = str_replace($matches[0][$index], (string)$replacement, $template);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error(
                'FlipDev_Seo: Shortcode processing failed',
                [
                    'exception' => $e->getMessage(),
                    'template' => $template,
                    'trace' => $e->getTraceAsString()
                ]
            );
        }

        return $template;
    }

    /**
     * Get value for a shortcode tag
     *
     * @param string $tag Shortcode tag
     * @param object $object Product or Category object
     * @return string|null
     */
    private function getShortcodeValue(string $tag, object $object): ?string
    {
        if ($tag === self::SHORTCODE_STORE) {
            return $this->getConfig(self::CONFIG_STORE_NAME);
        }

        // Check if object is a product
        if (method_exists($object, 'getTypeId') && !empty($object->getTypeId())) {
            return $this->getProductAttributeValue($object, $tag);
        }

        // Fallback to getData
        $value = $object->getData($tag);
        return $value !== null ? (string)$value : null;
    }

    /**
     * Get product attribute value safely
     *
     * @param object $product Product object
     * @param string $attributeCode Attribute code
     * @return string|null
     */
    private function getProductAttributeValue(object $product, string $attributeCode): ?string
    {
        try {
            if (!method_exists($product, 'getResource')) {
                return null;
            }

            $resource = $product->getResource();
            if (!$resource) {
                return null;
            }

            $attribute = $resource->getAttribute($attributeCode);
            if (!$attribute) {
                return null;
            }
            
            $frontend = $attribute->getFrontend();
            if (!$frontend) {
                return null;
            }

            $value = $frontend->getValue($product);
            return $value !== null ? (string)$value : null;
            
        } catch (\Exception $e) {
            $this->logger->warning(
                'FlipDev_Seo: Could not load attribute value',
                [
                    'attribute' => $attributeCode,
                    'exception' => $e->getMessage()
                ]
            );
        }

        return null;
    }

    /**
     * Check and set default meta data for products/categories
     *
     * @param object $object Product or Category object
     * @param string $type Type identifier (product, category, cms, etc.)
     * @return void
     */
    public function checkMetaData(object $object, string $type): void
    {
        if (!method_exists($object, 'getMetaTitle') || !method_exists($object, 'setMetaTitle')) {
            $this->logger->warning(
                'FlipDev_Seo: Object does not support meta data',
                ['class' => get_class($object)]
            );
            return;
        }

        $this->setDefaultMetaTitle($object, $type);
        $this->setDefaultMetaDescription($object, $type);
    }

    /**
     * Set default meta title if not set
     *
     * @param object $object
     * @param string $type
     * @return void
     */
    private function setDefaultMetaTitle(object $object, string $type): void
    {
        if (empty($object->getMetaTitle()) &&
            $this->getConfig("flipdev_seo/metadata/{$type}_title_enabled")) {

            $template = $this->getConfig("flipdev_seo/metadata/{$type}_title");
            if ($template) {
                $metaTitle = $this->shortcode($template, $object);

                // Clean the meta title: remove HTML tags, normalize whitespace
                $metaTitle = strip_tags($metaTitle);
                $metaTitle = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $metaTitle);
                $metaTitle = preg_replace('/\s+/', ' ', $metaTitle);
                $metaTitle = trim($metaTitle);

                $object->setMetaTitle($metaTitle);

                $this->logger->debug(
                    'FlipDev_Seo: Set default meta title',
                    ['type' => $type, 'title' => $metaTitle]
                );
            }
        }
    }

    /**
     * Set default meta description if not set
     *
     * @param object $object
     * @param string $type
     * @return void
     */
    private function setDefaultMetaDescription(object $object, string $type): void
    {
        if (empty($object->getMetaDescription()) &&
            $this->getConfig("flipdev_seo/metadata/{$type}_metadesc_enabled")) {

            $template = $this->getConfig("flipdev_seo/metadata/{$type}_metadesc");
            if ($template) {
                $metaDescription = $this->shortcode($template, $object);

                // Clean the meta description: remove HTML tags, normalize whitespace
                $metaDescription = strip_tags($metaDescription);
                $metaDescription = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $metaDescription);
                $metaDescription = preg_replace('/\s+/', ' ', $metaDescription);
                $metaDescription = trim($metaDescription);

                $object->setMetaDescription($metaDescription);

                $this->logger->debug(
                    'FlipDev_Seo: Set default meta description',
                    ['type' => $type, 'length' => strlen($metaDescription)]
                );
            }
        }
    }

    /**
     * Clear config cache (useful for testing)
     *
     * @return void
     */
    public function clearConfigCache(): void
    {
        $this->configCache = [];
    }
}
