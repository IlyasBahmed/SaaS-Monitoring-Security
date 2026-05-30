<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property-read agents|null $agent
 * @property-read Projects|null $project
 */
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function agent()
    {
        return $this->belongsTo(agents::class, 'agent_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Projects::class, 'project_id');
    }
}