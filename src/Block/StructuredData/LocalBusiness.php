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

class LocalBusiness extends \FlipDev\Seo\Block\Template
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
     * Get business type
     *
     * @return string
     */
    public function getBusinessType(): string
    {
        return $this->helper->getConfig('flipdev_seo/localbusiness_sd/type') ?: 'Store';
    }

    /**
     * Get business name
     *
     * @return string
     */
    public function getBusinessName(): string
    {
        $name = $this->helper->getConfig('flipdev_seo/localbusiness_sd/name');
        return $name ?: ($this->helper->getConfig('general/store_information/name')
            ?: $this->_storeManager->getStore()->getName());
    }

    /**
     * Get business description
     *
     * @return string|null
     */
    public function getBusinessDescription(): ?string
    {
        return $this->helper->getConfig('flipdev_seo/localbusiness_sd/description');
    }

    /**
     * Get address
     *
     * @return array
     */
    public function getAddress(): array
    {
        return [
            '@type' => 'PostalAddress',
            'streetAddress' => $this->helper->getConfig('flipdev_seo/organization_sd/street_address')
                ?: $this->helper->getConfig('general/store_information/street_line1'),
            'addressLocality' => $this->helper->getConfig('flipdev_seo/organization_sd/address_locality')
                ?: $this->helper->getConfig('general/store_information/city'),
            'addressRegion' => $this->helper->getConfig('flipdev_seo/organization_sd/region')
                ?: $this->helper->getConfig('general/store_information/region_id'),
            'postalCode' => $this->helper->getConfig('flipdev_seo/organization_sd/postcode')
                ?: $this->helper->getConfig('general/store_information/postcode'),
            'addressCountry' => $this->helper->getConfig('general/store_information/country_id'),
        ];
    }

    /**
     * Get telephone
     *
     * @return string|null
     */
    public function getTelephone(): ?string
    {
        return $this->helper->getConfig('general/store_information/phone');
    }

    /**
     * Get email
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->helper->getConfig('trans_email/ident_general/email');
    }

    /**
     * Get opening hours
     *
     * @return array|null
     */
    public function getOpeningHours(): ?array
    {
        $hoursConfig = $this->helper->getConfig('flipdev_seo/localbusiness_sd/opening_hours');
        if (!$hoursConfig) {
            return null;
        }

        // Format: Mo-Fr 09:00-18:00, Sa 10:00-16:00
        $hours = array_filter(array_map('trim', explode(',', $hoursConfig)));

        $specifications = [];
        foreach ($hours as $hour) {
            if (preg_match('/^([A-Za-z-]+)\s+(\d{2}:\d{2})-(\d{2}:\d{2})$/', $hour, $matches)) {
                $specifications[] = [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => $this->parseDayOfWeek($matches[1]),
                    'opens' => $matches[2],
                    'closes' => $matches[3],
                ];
            }
        }

        return !empty($specifications) ? $specifications : null;
    }

    /**
     * Parse day of week string to schema.org format
     *
     * @param string $days
     * @return array|string
     */
    protected function parseDayOfWeek(string $days)
    {
        $dayMap = [
            'Mo' => 'Monday',
            'Tu' => 'Tuesday',
            'We' => 'Wednesday',
            'Th' => 'Thursday',
            'Fr' => 'Friday',
            'Sa' => 'Saturday',
            'Su' => 'Sunday',
        ];

        // Handle ranges like Mo-Fr
        if (strpos($days, '-') !== false) {
            [$start, $end] = explode('-', $days);
            $start = $dayMap[$start] ?? $start;
            $end = $dayMap[$end] ?? $end;

            $allDays = array_values($dayMap);
            $startIndex = array_search($start, $allDays);
            $endIndex = array_search($end, $allDays);

            if ($startIndex !== false && $endIndex !== false) {
                return array_slice($allDays, $startIndex, $endIndex - $startIndex + 1);
            }
        }

        return $dayMap[$days] ?? $days;
    }

    /**
     * Get price range
     *
     * @return string|null
     */
    public function getPriceRange(): ?string
    {
        return $this->helper->getConfig('flipdev_seo/localbusiness_sd/price_range');
    }

    /**
     * Get geo coordinates
     *
     * @return array|null
     */
    public function getGeoCoordinates(): ?array
    {
        $lat = $this->helper->getConfig('flipdev_seo/localbusiness_sd/latitude');
        $lng = $this->helper->getConfig('flipdev_seo/localbusiness_sd/longitude');

        if ($lat && $lng) {
            return [
                '@type' => 'GeoCoordinates',
                'latitude' => (float)$lat,
                'longitude' => (float)$lng,
            ];
        }

        return null;
    }

    /**
     * Get logo URL
     *
     * @return string
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
            return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $path;
        }

        return '';
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
            '@type' => $this->getBusinessType(),
            'name' => $this->getBusinessName(),
            'url' => $this->_storeManager->getStore()->getBaseUrl(),
            'address' => $this->getAddress(),
        ];

        // Add optional fields
        if ($description = $this->getBusinessDescription()) {
            $data['description'] = $this->helper->cleanString($description);
        }

        if ($telephone = $this->getTelephone()) {
            $data['telephone'] = $telephone;
        }

        if ($email = $this->getEmail()) {
            $data['email'] = $email;
        }

        if ($logo = $this->getLogoUrl()) {
            $data['logo'] = $logo;
            $data['image'] = $logo;
        }

        if ($hours = $this->getOpeningHours()) {
            $data['openingHoursSpecification'] = $hours;
        }

        if ($priceRange = $this->getPriceRange()) {
            $data['priceRange'] = $priceRange;
        }

        if ($geo = $this->getGeoCoordinates()) {
            $data['geo'] = $geo;
        }

        return $data;
    }
}
