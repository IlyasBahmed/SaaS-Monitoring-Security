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
       Schema::create('project_agents', function (Blueprint $table) {
    $table->foreignId('project_id')->constrained()->cascadeOnDelete();
    $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
    $table->string('version')->nullable();
    $table->string('status')->nullable();
    $table->string('api_key')->nullable();
    $table->timestamp('last_seen_at')->nullable();

    $table->primary(['project_id', 'agent_id']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
