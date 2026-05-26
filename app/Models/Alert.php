<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Alert extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'alerts';

    protected $fillable = [
        'project_id',
        'type',
        'severity',
        'title',
        'summary',
        'evidence',
        'recommendations',
        'ai_score',
        'resolved',
        'detected_at',
    ];

    protected $casts = [
        'project_id' => 'integer',
        'evidence' => 'array',
        'recommendations' => 'array',
        'ai_score' => 'integer',
        'resolved' => 'boolean',
        'detected_at' => 'datetime',
    ];
    public function project()
{
    return $this->belongsTo(\App\Models\Projects::class, 'project_id', 'id');
}
}