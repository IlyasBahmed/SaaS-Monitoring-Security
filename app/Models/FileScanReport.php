<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class FileScanReport extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'file_scan_reports';

    protected $fillable = [
        'project_id',
        'site_url',
        'event',
        'severity',
        'target',
        'metadata',
        'event_created_at',
    ];

    protected $casts = [
        'target' => 'array',
        'metadata' => 'array',
        'event_created_at' => 'datetime',
    ];
}