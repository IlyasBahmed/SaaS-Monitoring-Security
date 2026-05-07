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
    Schema::table('projects', function (Blueprint $table) {
        if (! Schema::hasColumn('projects', 'api_key')) {
            $table->string('api_key')->unique()->nullable();
        }

        if (! Schema::hasColumn('projects', 'api_key_hash')) {
            $table->string('api_key_hash')->nullable();
        }
    });
}

public function down(): void
{
    Schema::table('projects', function (Blueprint $table) {
        if (Schema::hasColumn('projects', 'api_key')) {
            $table->dropColumn('api_key');
        }

        if (Schema::hasColumn('projects', 'api_key_hash')) {
            $table->dropColumn('api_key_hash');
        }
    });
}
};
