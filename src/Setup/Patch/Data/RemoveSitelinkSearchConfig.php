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

namespace FlipDev\Seo\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Remove deprecated Google Sitelink Search configuration
 *
 * Google deprecated the Sitelinks Search Box feature as of November 21, 2024
 */
class RemoveSitelinkSearchConfig implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        // Delete all config values for google_sitelink_search
        $this->moduleDataSetup->getConnection()->delete(
            $this->moduleDataSetup->getTable('core_config_data'),
            ['path LIKE ?' => 'flipdev_seo/google_sitelink_search/%']
        );

        $this->moduleDataSetup->getConnection()->endSetup();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
