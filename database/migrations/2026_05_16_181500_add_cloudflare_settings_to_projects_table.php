<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('cloudflare_enabled')->default(false)->after('last_seen_at');
            $table->string('cloudflare_account_email')->nullable()->after('cloudflare_enabled');
            $table->string('cloudflare_account_id')->nullable()->after('cloudflare_account_email');
            $table->string('cloudflare_zone_id')->nullable()->after('cloudflare_account_id');
            $table->text('cloudflare_api_token')->nullable()->after('cloudflare_zone_id');
            $table->json('cloudflare_settings')->nullable()->after('cloudflare_api_token');
            $table->timestamp('cloudflare_connected_at')->nullable()->after('cloudflare_settings');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'cloudflare_enabled',
                'cloudflare_account_email',
                'cloudflare_account_id',
                'cloudflare_zone_id',
                'cloudflare_api_token',
                'cloudflare_settings',
                'cloudflare_connected_at',
            ]);
        });
    }
};
