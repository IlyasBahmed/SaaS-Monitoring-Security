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
        if (!Schema::hasColumn('projects', 'cloudflare_nameservers')) {
            $table->json('cloudflare_nameservers')->nullable();
        }

        if (!Schema::hasColumn('projects', 'cloudflare_status')) {
            $table->string('cloudflare_status')->nullable()->default('pending');
        }
    });
}

public function down(): void
{
    Schema::table('projects', function (Blueprint $table) {
        if (Schema::hasColumn('projects', 'cloudflare_nameservers')) {
            $table->dropColumn('cloudflare_nameservers');
        }

        if (Schema::hasColumn('projects', 'cloudflare_status')) {
            $table->dropColumn('cloudflare_status');
        }
    });
}
};
