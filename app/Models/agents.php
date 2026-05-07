<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class agents extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'category',
        'version',
        'description',
        'status',
    ];

    public function projects()
    {
        return $this->belongsToMany(
            Projects::class,
            'project_agents',
            'agent_id',
            'project_id'
        )->withPivot([
            'agent_version',
            'status',
            'last_seen_at',
        ]);
    }
}