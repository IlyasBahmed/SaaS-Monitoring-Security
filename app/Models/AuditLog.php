<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class AuditLog extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'audit_logs';

    protected $fillable = [
        'project_id',
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
    ];
}