<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\TaxRate;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Mirrors Brothrrs Cafe's actual printed menu.
     *
     * Iced drinks are sold at two price tiers (Budget Brew / Iced Premium)
     * — those are modeled as two separate Products, not two variants of one
     * Product, because a Product belongs to exactly one Category and each
     * tier is its own category here. Hot drinks have one tier but two
     * sizes, so those stay as two ProductVariants on a single Product, the
     * shape every other resource in this project uses.
     *
     * Menu prices are exact, not randomized. Cost is still derived as a
     * fixed margin off the menu price — there's no real cost data to seed
     * with.
     */
    private const TAX_RATE_NAME = 'VAT 12%';

    private const TAX_RATE_PERCENTAGE = 12;

    private const COST_MARGIN = 0.32;

    /**
     * One Product + one ProductVariant per drink. Keyed by category name;
     * each category holds its description, the SKU tier code appended to
     * disambiguate a drink that also appears in another tier's category
     * (e.g. "Iced Latte" in both Budget Brew and Iced Premium), the single
     * variant's size label, and drink name => price.
     */
    private const SINGLE_VARIANT_CATEGORIES = [
        'Budget brew' => [
            'description' => 'Everyday iced coffee at an everyday price.',
            'tier' => 'BB',
            'size' => '16oz',
            'items' => [
                'Iced Latte' => 49,
                'Iced Caramel Macchiato' => 59,
                'Iced Vanilla Latte' => 59,
                'Iced Spanish Latte' => 59,
                'Iced Hazelnut Latte' => 59,
                'Iced Salted Caramel Latte' => 59,
            ],
        ],
        'Iced premium' => [
            'description' => 'Premium iced coffee, 16oz.',
            'tier' => 'IP',
            'size' => '16oz',
            'items' => [
                'Long Black' => 109,
                'Iced Latte' => 129,
                'Iced Caramel Macchiato' => 149,
                'Iced Vanilla Latte' => 149,
                'Iced Spanish Latte' => 149,
                'Iced Hazelnut Latte' => 149,
                'Iced Salted Caramel Latte' => 149,
                'Iced Tiramisu Latte' => 149,
                'Biscoff Iced Latte with Seasalt Foam' => 189,
            ],
        ],
        'Matcha' => [
            'description' => 'Smooth, creamy matcha made with premium UJI Matcha.',
            'tier' => 'MC',
            'size' => '12oz',
            'items' => [
                'Iced Matcha Latte' => 199,
                'Ube Matcha Latte' => 239,
                'Biscoff Matcha Latte with Seasalt Foam' => 259,
            ],
        ],
        'Add-ons' => [
            'description' => 'Extras and upgrades.',
            'tier' => 'AO',
            'size' => 'Add-on',
            'items' => [
                'Espresso 1shot' => 30,
                'Oatside (add-on)' => 30,
            ],
        ],
        'Chips' => [
            'description' => 'Savory snacks.',
            'tier' => 'CH',
            'size' => 'Regular',
            'items' => [
                'Cheetos Crunchy' => 219,
                'Cheetos Jalapeno' => 239,
                'Doritos Cheese' => 199,
                'Lays Classic' => 219,
                'Pringles Cheese Can' => 119,
                'Pringles Original Can' => 109,
                'Pringles Sourcream Can' => 109,
                'Ruffles Original' => 219,
            ],
        ],
        'Pastries' => [
            'description' => 'Baked goods.',
            'tier' => 'PS',
            'size' => 'Regular',
            'items' => [
                'Apple Cinnamon Muffin' => 65,
                'Banana Chocolate Muffin' => 60,
            ],
        ],
        'Others' => [
            'description' => 'Bottled and canned drinks, and other extras.',
            'tier' => 'OT',
            'size' => 'Regular',
            'items' => [
                'Agave Syrup 1.02kg' => 600,
                'Bottle Water' => 20,
                'Coke Bottle 8oz' => 20,
                'Coke Zero' => 59,
                'Oatside Milk Discounted' => 150,
                'Oatside Milk' => 160,
                'Papercups (Doublewall) + Lid 16oz' => 15,
                'Pineapple Juice (canned)' => 59,
                'Purified Drinking Water (500ml)' => 30,
                'Sanmig Apple Flavor' => 49,
                'Sanmig Lemon Flavor' => 49,
                'Sanmig Light' => 49,
            ],
        ],
    ];

    /**
     * One tier, two sizes per drink — two ProductVariants on one Product,
     * unlike the single-variant categories above.
     */
    private const HOT_PREMIUM = [
        'Americano' => ['8oz' => 79, '12oz' => 99],
        'Cappuccino' => ['8oz' => 99, '12oz' => 119],
        'Caramel Latte' => ['8oz' => 109, '12oz' => 129],
        'Spanish Latte' => ['8oz' => 109, '12oz' => 129],
    ];

    private const HOT_PREMIUM_TIER = 'HP';

    private const HOT_PREMIUM_DESCRIPTION = 'Premium hot coffee, 8oz or 12oz.';

    public function run(): void
    {
        $taxRate = TaxRate::firstOrCreate(
            ['name' => self::TAX_RATE_NAME],
            ['percentage' => self::TAX_RATE_PERCENTAGE]
        );

        foreach (self::SINGLE_VARIANT_CATEGORIES as $categoryName => $config) {
            $category = Category::firstOrCreate(
                ['name' => $categoryName],
                ['description' => $config['description']]
            );

            foreach ($config['items'] as $drink => $price) {
                $this->createProductWithVariant($drink, $category, $taxRate, $config['tier'], $config['size'], $price);
            }
        }

        $hotPremium = Category::firstOrCreate(
            ['name' => 'Hot premium'],
            ['description' => self::HOT_PREMIUM_DESCRIPTION]
        );

        foreach (self::HOT_PREMIUM as $drink => $sizes) {
            $product = Product::firstOrCreate(
                ['sku' => $this->sku($drink, self::HOT_PREMIUM_TIER)],
                [
                    'name' => $drink,
                    'category_id' => $hotPremium->id,
                    'tax_rate_id' => $taxRate->id,
                ]
            );

            foreach ($sizes as $size => $price) {
                $product->variants()->firstOrCreate(
                    ['name' => $size],
                    ['price' => $price, 'cost' => $this->cost($price), 'is_active' => true]
                );
            }
        }
    }

    private function createProductWithVariant(
        string $drink,
        Category $category,
        TaxRate $taxRate,
        string $tier,
        string $size,
        float $price
    ): void {
        $product = Product::firstOrCreate(
            ['sku' => $this->sku($drink, $tier)],
            [
                'name' => $drink,
                'category_id' => $category->id,
                'tax_rate_id' => $taxRate->id,
            ]
        );

        $product->variants()->firstOrCreate(
            ['name' => $size],
            ['price' => $price, 'cost' => $this->cost($price), 'is_active' => true]
        );
    }

    private function cost(float $price): float
    {
        return round($price * self::COST_MARGIN, 2);
    }

    /**
     * A plain uppercase-with-hyphens SKU runs too long for 58mm/80mm
     * thermal receipt paper once a name is more than a couple words — keep
     * the first letter of each of the first three significant words
     * (dropping filler words like "with"), strip vowels from the rest, and
     * tag on a short tier code so the same drink name sold at a different
     * price tier (e.g. "Iced Latte" in both Budget Brew and Iced Premium)
     * still gets a unique SKU. Punctuation (parentheses, "+", ".") in a
     * name like "Papercups (Doublewall) + Lid 16oz" is stripped first so it
     * never leaks into the SKU itself.
     */
    private function sku(string $drink, string $tier): string
    {
        $stopWords = ['with', 'and', 'the', 'of', 'in', 'on'];

        $normalized = preg_replace('/[^\p{L}\p{N}\s]/u', '', $drink);

        $words = collect(preg_split('/\s+/', trim($normalized)))
            ->filter()
            ->reject(fn(string $word) => in_array(strtolower($word), $stopWords, true))
            ->take(3);

        return $words->map(fn(string $word) => $this->abbreviateWord($word))
            ->push($tier)
            ->implode('-');
    }

    private function abbreviateWord(string $word): string
    {
        $first = strtoupper($word[0]);
        $rest = strtoupper(substr($word, 1));
        $consonants = preg_replace('/[AEIOU]/', '', $rest);

        return $first . $consonants;
    }
}
