<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/**
 * @property array|null $plugins
 * @property string|null $site_url
 */
class SiteInventory extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'site_inventories';

    protected $fillable = [
        'project_id',
        'site_url',
        'wp_version',
        'php_version',
        'plugins',
        'themes',
        'collected_at',
    ];

    protected $casts = [
        'plugins' => 'array',
        'themes' => 'array',
        'collected_at' => 'datetime',
    ];
}