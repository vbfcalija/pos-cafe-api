<?php

use Database\Seeders\DiscountSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * NativePHP's mobile builds run migrations automatically on first app
     * boot but never call Seeder classes — those only run via `db:seed`,
     * which isn't reachable on a bundled mobile install. Running the
     * existing seeders here (idempotent: firstOrCreate/updateOrCreate
     * throughout) is how a fresh tablet install gets the initial user,
     * product catalog, and discount instead of booting into an empty app.
     * Local/web dev keeps using `migrate:fresh --seed` as before.
     */
    public function up(): void
    {
        (new UserSeeder)->run();
        (new ProductSeeder)->run();
        (new DiscountSeeder)->run();
    }

    public function down(): void
    {
        // Deliberately no-op — this migration seeds baseline data, it
        // doesn't own a schema change to reverse.
    }
};
