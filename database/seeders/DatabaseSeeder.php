<?php

namespace Database\Seeders;

use App\Models\User;
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
        // User::factory(10)->create();

        User::factory()->create([
            'firstname' => 'Test',
            'lastname' => 'User',
            'email' => 'test@example.com',
        ]);
    }
}
