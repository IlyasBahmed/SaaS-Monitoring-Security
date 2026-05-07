<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
     Schema::create('projects', function (Blueprint $table) {
    $table->id();

    $table->foreignId('client_id')->constrained()->cascadeOnDelete();

    // BASIC INFO
    $table->string('name')->nullable();

    $table->string('domain')
        ->nullable()
        ->index();

    $table->string('ip_address')->nullable();
    $table->string('stack')->nullable();

    // SECURITY
    $table->string('api_key_prefix')
        ->nullable()
        ->index();

    $table->text('api_key_hash')->nullable();

    // CONNECTION
    $table->boolean('is_connected')->default(false);

    $table->timestamp('connected_at')->nullable();
    $table->timestamp('last_seen_at')->nullable();

    // STATUS
    $table->string('status')
        ->default('pending')
        ->index();

    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
