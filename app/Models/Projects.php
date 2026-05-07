<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Projects extends Model
{
    public const PROJECT_TYPES = [
        'WordPress',
        'Node.js',
        'Laravel',
    ];

    protected $fillable = [
        'client_id',
        'name',
        'domain',
        'ip_address',
        'stack',
        'status',
        'api_key',
        'is_connected',
        'connected_at',
        'last_seen_at',
    ];

    public function client()
    {
        return $this->belongsTo(clients::class, 'client_id');
    }

    public function alerts()
    {
        return $this->hasMany(alerts::class, 'project_id');
    }

    public function incidents()
    {
        return $this->hasMany(incidents::class, 'project_id');
    }

    public function agents()
    {
        return $this->belongsToMany(agents::class, 'project_agents', 'project_id', 'agent_id')
            ->withPivot(['version', 'status', 'api_key', 'last_seen_at']);
    }

    public static function normalizeProjectType(?string $type): string
    {
        $type = strtolower(trim((string) $type));

        if (str_contains($type, 'wordpress')) {
            return 'WordPress';
        }

        if (str_contains($type, 'node')) {
            return 'Node.js';
        }

        if (str_contains($type, 'laravel')) {
            return 'Laravel';
        }

        return 'Laravel';
    }
}