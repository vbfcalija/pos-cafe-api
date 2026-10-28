<?php

use Database\Seeders\InventoryItemSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Mobile installs never run db:seed, so the starting item list is seeded
     * here (same approach as the checklist tasks). The seeder is idempotent.
     */
    public function up(): void
    {
        (new InventoryItemSeeder)->run();
    }

    public function down(): void
    {
        // No-op: seeds baseline data, owns no schema change.
    }
};
