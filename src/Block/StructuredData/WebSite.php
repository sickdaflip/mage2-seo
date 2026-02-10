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

namespace FlipDev\Seo\Block\StructuredData;

use Magento\Framework\View\Element\Template\Context;
use FlipDev\Seo\Helper\Data as SeoHelper;

class WebSite extends \FlipDev\Seo\Block\Template
{
    /**
     * @param Context $context
     * @param SeoHelper $flipDevSeoHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        SeoHelper $flipDevSeoHelper,
        array $data = []
    ) {
        parent::__construct($context, $flipDevSeoHelper, $data);
    }

    /**
     * Get site name
     *
     * @return string
     */
    public function getSiteName(): string
    {
        $storeName = $this->helper->getConfig('general/store_information/name');
        return $storeName ?: $this->_storeManager->getStore()->getName();
    }

    /**
     * Get site URL
     *
     * @return string
     */
    public function getSiteUrl(): string
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * Get search action URL template
     *
     * @return string
     */
    public function getSearchActionUrl(): string
    {
        return $this->getUrl('catalogsearch/result') . '?q={search_term_string}';
    }

    /**
     * Check if sitelinks searchbox is enabled
     *
     * @return bool
     */
    public function isSitelinksSearchboxEnabled(): bool
    {
        return (bool)$this->helper->getConfig('flipdev_seo/website_sd/sitelinks_searchbox');
    }

    /**
     * Get alternate site names
     *
     * @return array
     */
    public function getAlternateNames(): array
    {
        $alternateNames = $this->helper->getConfig('flipdev_seo/website_sd/alternate_names');
        if ($alternateNames) {
            return array_filter(array_map('trim', explode("\n", $alternateNames)));
        }
        return [];
    }

    /**
     * Get structured data array
     *
     * @return array
     */
    public function getStructuredData(): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $this->getSiteName(),
            'url' => $this->getSiteUrl(),
        ];

        // Add alternate names if configured
        $alternateNames = $this->getAlternateNames();
        if (!empty($alternateNames)) {
            $data['alternateName'] = count($alternateNames) === 1 ? $alternateNames[0] : $alternateNames;
        }

        // Add sitelinks searchbox if enabled
        if ($this->isSitelinksSearchboxEnabled()) {
            $data['potentialAction'] = [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => $this->getSearchActionUrl(),
                ],
                'query-input' => 'required name=search_term_string',
            ];
        }

        return $data;
    }
}
