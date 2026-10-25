<?php

use Database\Seeders\TaskSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Same reason as 2026_09_03_000001_seed_initial_data: mobile installs
     * never run db:seed, so the default barista tasks are seeded here.
     * TaskSeeder is idempotent.
     */
    public function up(): void
    {
        (new TaskSeeder)->run();
    }

    public function down(): void
    {
        // No-op: seeds baseline data, owns no schema change.
    }
};
