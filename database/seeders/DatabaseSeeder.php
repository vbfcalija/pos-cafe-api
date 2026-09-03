<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // Deliberately not using WithoutModelEvents: UsesUuid assigns the uuid
    // in a `creating` model event, so seeding without events would insert
    // rows that violate the uuid column's NOT NULL/unique constraint.

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ProductSeeder::class,
            DiscountSeeder::class,
        ]);
    }
}
