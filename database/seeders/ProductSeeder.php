<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\TaxRate;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Each drink is one Product; every drink gets four ProductVariants —
     * Iced/Hot crossed with 8oz/12oz. Each variant gets its own random price
     * between ₱100–249 (stored as centavos); cost is derived as a fixed
     * margin off that price rather than randomized separately.
     *
     * Categories group by drink family, not by temperature — Iced/Hot is
     * already a variant-level distinction on every drink here, so it can't
     * also be the category axis without splitting each drink into two
     * separate products (one per temperature).
     */
    private const DRINKS = [
        'Caramel Macchiato' => 'Milk-Based Coffee',
        'Spanish Latte' => 'Milk-Based Coffee',
        'Cappucino' => 'Milk-Based Coffee',
        'Long Black' => 'Black Coffee',
        'Americano' => 'Black Coffee',
        'Matcha Latte' => 'Tea & Matcha',
        'Frappucino' => 'Blended',
    ];

    private const CATEGORY_DESCRIPTIONS = [
        'Milk-Based Coffee' => 'Espresso drinks built on steamed or textured milk',
        'Black Coffee' => 'Straight espresso-based drinks with no milk',
        'Tea & Matcha' => 'Tea and matcha-based drinks',
        'Blended' => 'Ice-blended drinks',
    ];

    private const TEMPERATURES = ['Iced', 'Hot'];

    private const SIZES = ['8oz', '12oz'];

    private const MIN_PRICE_PESOS = 100;

    private const MAX_PRICE_PESOS = 249;

    private const COST_MARGIN = 0.32;

    public function run(): void
    {
        $categories = collect(self::CATEGORY_DESCRIPTIONS)->mapWithKeys(
            fn ($description, $name) => [$name => Category::firstOrCreate(
                ['name' => $name],
                ['description' => $description]
            )]
        );

        $taxRate = TaxRate::firstOrCreate(
            ['name' => 'VAT 12%'],
            ['percentage' => 12]
        );

        foreach (self::DRINKS as $drink => $categoryName) {
            [$price, $cost] = $this->randomPriceAndCost();

            $product = Product::firstOrCreate(
                ['sku' => $this->sku($drink)],
                [
                    'name' => $drink,
                    'price' => $price,
                    'cost' => $cost,
                    'category_id' => $categories[$categoryName]->id,
                    'tax_rate_id' => $taxRate->id,
                ]
            );

            foreach (self::TEMPERATURES as $temperature) {
                foreach (self::SIZES as $size) {
                    [$variantPrice, $variantCost] = $this->randomPriceAndCost();

                    $product->variants()->firstOrCreate(
                        ['name' => "{$temperature} {$size}"],
                        [
                            'price' => $variantPrice,
                            'cost' => $variantCost,
                            'is_active' => true,
                        ]
                    );
                }
            }
        }
    }

    /**
     * @return array{0: int, 1: int} [priceInCentavos, costInCentavos]
     */
    private function randomPriceAndCost(): array
    {
        $pricePesos = rand(self::MIN_PRICE_PESOS, self::MAX_PRICE_PESOS);
        $price = $pricePesos * 100;
        $cost = (int) round($price * self::COST_MARGIN);

        return [$price, $cost];
    }

    /**
     * A plain uppercase-with-hyphens SKU ("SPANISH-LATTE") runs too long for
     * 58mm/80mm thermal receipt paper once names get longer than one word —
     * and which width is even in the printer is out of our control. Keep the
     * first letter of each word and strip the vowels from the rest instead
     * ("Spanish Latte" -> "SPNSH-LTT"), so the SKU stays short regardless of
     * the product name's length.
     */
    private function sku(string $drink): string
    {
        return collect(preg_split('/\s+/', trim($drink)))
            ->map(fn (string $word) => $this->abbreviateWord($word))
            ->implode('-');
    }

    private function abbreviateWord(string $word): string
    {
        $first = strtoupper($word[0]);
        $rest = strtoupper(substr($word, 1));
        $consonants = preg_replace('/[AEIOU]/', '', $rest);

        return $first.$consonants;
    }
}
