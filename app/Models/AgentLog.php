<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class AgentLog extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'agent_logs';

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
];
public function project()
{
    return $this->belongsTo(\App\Models\Projects::class, 'project_id', 'id');
}
}