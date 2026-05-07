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
        Schema::table('project_agents', function (Blueprint $table) {
            // Add version column if it doesn't exist
            if (!Schema::hasColumn('project_agents', 'version')) {
                $table->string('version')->nullable()->after('agent_id');
            }
            // Add api_key column if it doesn't exist
            if (!Schema::hasColumn('project_agents', 'api_key')) {
                $table->string('api_key')->nullable()->unique()->after('api_key_hash');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_agents', function (Blueprint $table) {
            if (Schema::hasColumn('project_agents', 'version')) {
                $table->dropColumn('version');
            }
            if (Schema::hasColumn('project_agents', 'api_key')) {
                $table->dropColumn('api_key');
            }
        });
    }
};
