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
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class Organization extends \FlipDev\Seo\Block\Template
{
    public function __construct(
        Context $context,
        SeoHelper $flipDevSeoHelper,
        private Filesystem $filesystem,
        array $data = []
    ) {
        parent::__construct($context, $flipDevSeoHelper, $data);
    }

    /**
     * Get logo URL
     */
    public function getLogoUrl(): string
    {
        $folderName = \Magento\Config\Model\Config\Backend\Image\Logo::UPLOAD_DIR;
        $storeLogoPath = $this->_scopeConfig->getValue(
            'design/header/logo_src',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($storeLogoPath) {
            $path = $folderName . '/' . $storeLogoPath;
            $mediaDir = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            if ($mediaDir->isFile($path)) {
                return $this->_urlBuilder
                    ->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $path;
            }
        }

        return $this->getViewFileUrl('images/logo.svg');
    }

    /**
     * @return array
     */
    public function getSocialProfiles(): array
    {
        return array_filter(explode("\n", $this->helper->getConfig('flipdev_seo/social_sd/social_profiles') ?? ''));
    }

    /**
     * Get structured data array
     */
    public function getStructuredData(): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'url' => $this->getUrl(''),
            'logo' => $this->getLogoUrl(),
            'name' => $this->helper->getConfig('general/store_information/name') ?? '',
            'telephone' => $this->helper->getConfig('general/store_information/phone') ?? '',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $this->helper->getConfig('flipdev_seo/organization_sd/street_address') ?? '',
                'addressLocality' => $this->helper->getConfig('flipdev_seo/organization_sd/address_locality') ?? '',
                'addressRegion' => $this->helper->getConfig('flipdev_seo/organization_sd/region') ?? '',
                'postalCode' => $this->helper->getConfig('flipdev_seo/organization_sd/postcode') ?? '',
                'addressCountry' => $this->helper->getConfig('general/store_information/country_id') ?? '',
            ],
        ];

        // Add social profiles if enabled
        if ($this->helper->getConfig('flipdev_seo/social_sd/enabled')) {
            $profiles = $this->getSocialProfiles();
            if (!empty($profiles)) {
                $data['sameAs'] = array_values(array_map('trim', $profiles));
            }
        }

        return $data;
    }
}
