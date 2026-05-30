<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/**
 * @property string|null $event
 * @property string|null $category
 * @property string|null $severity
 * @property string|null $ip
 * @property array|null $metadata
 * @property \Illuminate\Support\Carbon|null $event_created_at
 */
class AuditLog extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'audit_logs';

    protected $fillable = [
        'project_id',
        'category',
        'event',
        'severity',
        'site_url',
        'ip',
        'user_agent',
        'actor',
        'target',
        'before',
        'after',
        'metadata',
        'event_created_at',
    ];

    protected $casts = [
    'actor' => 'array',
    'target' => 'array',
    'before' => 'array',
    'after' => 'array',
    'metadata' => 'array',
    'event_created_at' => 'datetime',
];
}