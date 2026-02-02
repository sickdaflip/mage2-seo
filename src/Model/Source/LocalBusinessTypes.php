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

namespace FlipDev\Seo\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class LocalBusinessTypes implements OptionSourceInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'Store', 'label' => __('Store (Generic)')],
            ['value' => 'ClothingStore', 'label' => __('Clothing Store')],
            ['value' => 'ElectronicsStore', 'label' => __('Electronics Store')],
            ['value' => 'FurnitureStore', 'label' => __('Furniture Store')],
            ['value' => 'GardenStore', 'label' => __('Garden Store')],
            ['value' => 'GroceryStore', 'label' => __('Grocery Store')],
            ['value' => 'HardwareStore', 'label' => __('Hardware Store')],
            ['value' => 'HobbyShop', 'label' => __('Hobby Shop')],
            ['value' => 'HomeGoodsStore', 'label' => __('Home Goods Store')],
            ['value' => 'JewelryStore', 'label' => __('Jewelry Store')],
            ['value' => 'LiquorStore', 'label' => __('Liquor Store')],
            ['value' => 'MensClothingStore', 'label' => __('Mens Clothing Store')],
            ['value' => 'MobilePhoneStore', 'label' => __('Mobile Phone Store')],
            ['value' => 'MovieRentalStore', 'label' => __('Movie Rental Store')],
            ['value' => 'MusicStore', 'label' => __('Music Store')],
            ['value' => 'OfficeEquipmentStore', 'label' => __('Office Equipment Store')],
            ['value' => 'OutletStore', 'label' => __('Outlet Store')],
            ['value' => 'PawnShop', 'label' => __('Pawn Shop')],
            ['value' => 'PetStore', 'label' => __('Pet Store')],
            ['value' => 'ShoeStore', 'label' => __('Shoe Store')],
            ['value' => 'SportingGoodsStore', 'label' => __('Sporting Goods Store')],
            ['value' => 'TireShop', 'label' => __('Tire Shop')],
            ['value' => 'ToyStore', 'label' => __('Toy Store')],
            ['value' => 'WholesaleStore', 'label' => __('Wholesale Store')],
            ['value' => 'AutoPartsStore', 'label' => __('Auto Parts Store')],
            ['value' => 'BikeStore', 'label' => __('Bike Store')],
            ['value' => 'BookStore', 'label' => __('Book Store')],
            ['value' => 'ComputerStore', 'label' => __('Computer Store')],
            ['value' => 'ConvenienceStore', 'label' => __('Convenience Store')],
            ['value' => 'DepartmentStore', 'label' => __('Department Store')],
            ['value' => 'Florist', 'label' => __('Florist')],
        ];
    }
}
