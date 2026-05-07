<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectAgent extends Model
{
    protected $table = 'project_agents';

    public $timestamps = true;

    protected $fillable = [
        'project_id',
        'agent_id',
        'site_url',
        'wp_version',
        'php_version',
        'version',
        'status',
        'api_key',
        'connected_at',
        'last_seen_at',
        'meta',
    ];

    protected $casts = [
        'connected_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'meta' => 'array',
    ];

    public function agent()
    {
        return $this->belongsTo(agents::class, 'agent_id');
    }

    public function project()
    {
        return $this->belongsTo(Projects::class, 'project_id');
    }
}