<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class HealthReport extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'health_reports';

    protected $fillable = [
        'project_id',
        'site_url',
        'score',
        'risk_level',
        'issues',
        'reports',
        'metadata',
        'event_created_at',
    ];

    protected $casts = [
        'score' => 'array',
        'issues' => 'array',
        'reports' => 'array',
        'metadata' => 'array',
        'event_created_at' => 'datetime',
    ];
}