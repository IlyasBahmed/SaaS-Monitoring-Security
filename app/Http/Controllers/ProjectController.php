<?php

namespace App\Http\Controllers;

use App\Models\Projects;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'  => ['required', 'exists:clients,id'],
            'name'      => ['required', 'string', 'max:255'],
            'domain'    => ['nullable', 'string', 'max:255'],
            'ip_address'=> ['nullable', 'string', 'max:255'],
            'stack'     => ['nullable', 'string', 'max:255'],
            'status'    => ['nullable', 'in:pending,active,inactive,blocked,revoked'],
        ]);

        $plainKey = 'ssa_live_' . Str::random(64);

        $project = Projects::create([
            'client_id'       => $data['client_id'],
            'name'            => $data['name'],
            'domain'          => $data['domain'] ?? null,
            'ip_address'      => $data['ip_address'] ?? null,
            'stack'           => $data['stack'] ?? 'wordpress',
            'status'          => $data['status'] ?? 'pending',
            'api_key_prefix'  => substr($plainKey, 0, 12),
            'api_key_hash'    => Hash::make($plainKey),
            'is_connected'    => false,
        ]);

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project created successfully')
            ->with('api_key', $plainKey);
    }
}