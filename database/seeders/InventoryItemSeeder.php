<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use Illuminate\Database\Seeder;

class InventoryItemSeeder extends Seeder
{
    /**
     * The Brthrs Café daily inventory list, in sheet order (item => unit).
     */
    private const ITEMS = [
        'Beans (Opened)' => 'grams',
        'Beans (Unopened)' => 'pack (1kg)',
        'Matcha powder' => 'grams',
        'Espresso ready mix (Unopened)' => 'pack (200g)',
        'Espresso (Opened)' => 'grams',
        'Fullcream milk' => 'pcs (1L)',
        'Powdered Non-Dairy Milk (Unopened)' => 'pack (1kg)',
        'Powdered Non-Dairy Milk (Opened)' => 'grams',
        'Oatside' => 'pcs (1L)',
        'Agave syrup' => 'bottle (1kg)',
        'Caramel sauce' => 'grams',
        'Chocolate sauce' => 'grams',
        'Ube sauce' => 'grams',
        'White chocolate sauce' => 'grams',
        'Budget - Caramel syrup (Unopened)' => 'bottle',
        'Budget - Caramel syrup (Opened)' => 'grams',
        'Premium - Caramel syrup (Unopened)' => 'bottle',
        'Premium - Caramel syrup (Opened)' => 'grams',
        'Budget - Hazelnut syrup (Unopened)' => 'bottle',
        'Budget - Hazelnut syrup (Opened)' => 'grams',
        'Premium - Hazelnut syrup (Unopened)' => 'bottle',
        'Premium - Hazelnut syrup (Opened)' => 'grams',
        'Budget - Vanilla syrup (Unopened)' => 'bottle',
        'Budget - Vanilla syrup (Opened)' => 'grams',
        'Premium - Vanilla syrup (Unopened)' => 'bottle',
        'Premium - Vanilla syrup (Opened)' => 'grams',
        'Budget - Salted caramel syrup (Unopened)' => 'bottle',
        'Budget - Salted caramel syrup (Opened)' => 'grams',
        'Premium - Salted caramel syrup (Unopened)' => 'bottle',
        'Premium - Salted caramel syrup (Opened)' => 'grams',
        'Budget - Tiramisu syrup (Unopened)' => 'bottle',
        'Budget - Tiramisu syrup (Opened)' => 'grams',
        'Premium - Tiramisu syrup (Unopened)' => 'bottle',
        'Premium - Tiramisu syrup (Opened)' => 'grams',
        'Condensed milk' => 'grams',
        'All-purpose cream' => 'pcs (250ml)',
        'Evaporated milk (Unopened)' => 'grams',
        'Evaporated milk (Opened - Ref)' => 'grams',
        'Sea salt foam (Ref)' => 'grams',
        'Premium - 16oz plastic cups' => 'pcs',
        'Premium - 12oz plastic cups' => 'pcs',
        'Frappe - Dome lid' => 'pcs',
        'Strawless lid' => 'pcs',
        'Budget - 16oz flat lid' => 'pcs',
        'Budget - 16oz plastic cups' => 'pcs',
        '8oz cup (Hot)' => 'pcs',
        '12oz cup (Hot)' => 'pcs',
        '8oz cup lid (Hot)' => 'pcs',
        '12oz cup lid (Hot)' => 'pcs',
        'Straw (Thin)' => 'pcs',
        '8oz cup (Hot) - Cup sleeves' => 'pcs',
        '12oz cup (Hot) - Cup sleeves' => 'pcs',
    ];

    public function run(): void
    {
        foreach (self::ITEMS as $name => $unit) {
            InventoryItem::query()->firstOrCreate(['name' => $name], ['unit' => $unit, 'is_active' => true]);
        }
    }
}
