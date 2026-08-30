<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('uuid')->constrained()->restrictOnDelete();
            $table->boolean('is_open')->default(true)->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
            $table->dropColumn('is_open');
        });
    }
};
