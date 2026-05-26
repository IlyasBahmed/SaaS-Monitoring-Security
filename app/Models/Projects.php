<?php

namespace App\Models;
use App\Models\Alert;
use App\Models\Incident;
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
        'cloudflare_enabled',
        'cloudflare_account_email',
        'cloudflare_account_id',
        'cloudflare_zone_id',
        'cloudflare_api_token',
        'cloudflare_settings',
        'cloudflare_connected_at',
        'cloudflare_nameservers',
'cloudflare_status',
    ];

    protected $hidden = [
        'cloudflare_api_token',
        'cloudflare_nameservers' => 'array',
    ];

    protected $casts = [
    'is_connected' => 'boolean',
    'connected_at' => 'datetime',
    'last_seen_at' => 'datetime',
    'cloudflare_enabled' => 'boolean',
    'cloudflare_api_token' => 'encrypted',
    'cloudflare_settings' => 'array',
    'cloudflare_nameservers' => 'array',
    'cloudflare_connected_at' => 'datetime',
    'cloudflare_status' => 'string',
];

    public function client()
    {
        return $this->belongsTo(clients::class, 'client_id');
    }

    public function alerts()
{
    return $this->hasMany(Alert::class, 'project_id');
}

public function incidents()
{
    return $this->hasMany(Incident::class, 'project_id');
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
