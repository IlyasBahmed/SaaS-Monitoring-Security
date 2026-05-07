<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class AgentLog extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'agent_logs';

    protected $fillable = [
        'project_id',
        'agent_id',
        'site_url',
        'type',
        'event',
        'severity',
        'ip_address',
        'user_agent',
        'username',
        'user_id',
        'role',
        'data',
        'created_at',
    ];

    protected $casts = [
        'data' => 'array',
        'created_at' => 'datetime',
    ];
}