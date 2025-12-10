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

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Add SEO-related EAV attributes to products and categories
 */
class AddSeoAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private EavSetupFactory $eavSetupFactory;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Apply data patch
     *
     * @return void
     */
    public function apply(): void
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        
        $this->addCategoryAttributes($eavSetup);
        $this->addProductAttributes($eavSetup);
    }

    /**
     * Add category SEO attributes
     *
     * @param \Magento\Eav\Setup\EavSetup $eavSetup
     * @return void
     */
    private function addCategoryAttributes($eavSetup): void
    {
        // Custom Category H1 Heading
        $eavSetup->addAttribute(
            Category::ENTITY,
            'flipdevseo_heading',
            [
                'type' => 'varchar',
                'label' => 'Category Heading (H1)',
                'input' => 'text',
                'required' => false,
                'sort_order' => 50,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'General Information',
                'note' => 'Override the default category name as H1 heading',
                'user_defined' => true,
                'visible' => true,
            ]
        );

        // Category Meta Robots
        $eavSetup->addAttribute(
            Category::ENTITY,
            'flipdevseo_metarobots',
            [
                'type' => 'varchar',
                'label' => 'Meta Robots',
                'input' => 'select',
                'source' => \FlipDev\Seo\Model\Source\Robots::class,
                'required' => false,
                'sort_order' => 60,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'General Information',
                'note' => 'Control search engine indexing behavior',
                'user_defined' => true,
                'visible' => true,
            ]
        );
    }

    /**
     * Add product SEO attributes
     *
     * @param \Magento\Eav\Setup\EavSetup $eavSetup
     * @return void
     */
    private function addProductAttributes($eavSetup): void
    {
        $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);
        $attributeSetId = $eavSetup->getAttributeSetId($entityTypeId, 'Default');

        // Discontinued Product Redirect Options
        $eavSetup->addAttribute(
            Product::ENTITY,
            'flipdevseo_discontinued',
            [
                'type' => 'int',
                'label' => 'Discontinued',
                'input' => 'select',
                'source' => \FlipDev\Seo\Model\Source\Discontinued::class,
                'required' => true,
                'default' => '0',
                'sort_order' => 110,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Search Engine Optimization',
                'note' => 'Define redirect behavior for disabled products',
                'user_defined' => true,
                'visible' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
            ]
        );

        // Redirect to Product SKU
        $eavSetup->addAttribute(
            Product::ENTITY,
            'flipdevseo_discontinued_product',
            [
                'type' => 'varchar',
                'label' => 'Redirect to Product SKU',
                'input' => 'text',
                'required' => false,
                'sort_order' => 120,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Search Engine Optimization',
                'note' => 'Enter SKU when using "301 Redirect to Product"',
                'user_defined' => true,
                'visible' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
            ]
        );

        // Product Meta Robots
        $eavSetup->addAttribute(
            Product::ENTITY,
            'flipdevseo_metarobots',
            [
                'type' => 'varchar',
                'label' => 'Meta Robots',
                'input' => 'select',
                'source' => \FlipDev\Seo\Model\Source\Robots::class,
                'required' => false,
                'sort_order' => 130,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Search Engine Optimization',
                'note' => 'Control search engine indexing behavior',
                'user_defined' => true,
                'visible' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
            ]
        );
    }

    /**
     * Get array of patches that have to be executed prior to this
     *
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Get aliases
     *
     * @return string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}
