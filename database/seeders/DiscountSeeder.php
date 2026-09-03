<?php

namespace Database\Seeders;

use App\Enums\DiscountType;
use App\Models\Discount;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    public function run(): void
    {
        Discount::query()->firstOrCreate(
            ['name' => 'Senior/PWD'],
            [
                'type' => DiscountType::Percentage,
                'value' => 20,
            ]
        );
    }
}
