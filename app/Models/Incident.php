<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/**
 * @property array|null $metadata
 * @property \Illuminate\Support\Carbon|null $event_created_at
 */
class Incident extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'incidents';

    protected $fillable = [
        'project_id',
        'incident_key',
        'category',
        'event',
        'severity',
        'site_url',
        'ip',
        'user_agent',
        'target',
        'metadata',
        'status',
        'assigned_user_id',
        'assigned_user_name',
        'assigned_user_email',
        'assigned_at',
        'event_created_at',
    ];

    protected $casts = [
        'project_id' => 'integer',
        'target' => 'array',
        'metadata' => 'array',
        'assigned_at' => 'datetime',
        'event_created_at' => 'datetime',
    ];
    public function project()
{
    return $this->belongsTo(\App\Models\Projects::class, 'project_id', 'id');
}
}
