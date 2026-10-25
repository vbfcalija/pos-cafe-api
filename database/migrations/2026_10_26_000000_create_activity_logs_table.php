<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An append-only trail of who did what, from where. Rows are never
     * edited or deleted through the app.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            // Null for a failed login with an unknown email. Kept (as null) if a user is ever deleted.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event', 50);            // login, login_failed, logout, created, updated, deleted, refunded…
            $table->string('description');
            $table->nullableMorphs('subject');       // the record acted on, if any
            $table->json('properties')->nullable();  // changes (old/new) or login details

            // Where the request came from.
            $table->string('ip_address', 45)->nullable();
            $table->string('device_id', 64)->nullable(); // the app's own ID for the device (browsers can't expose a MAC address)
            $table->string('device_type', 20)->nullable(); // desktop, tablet, mobile
            $table->string('device_model', 100)->nullable();
            $table->string('platform', 50)->nullable();   // operating system + version
            $table->string('browser', 50)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['event', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
