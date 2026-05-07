<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectDemoSeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::query()->value('id');

        if (! $userId) {
            return;
        }

        DB::table('clients')->updateOrInsert(
            ['company_name' => 'Acme Security'],
            [
                'user_id' => $userId,
                'email' => 'security@acme.test',
                'phone' => '+212 600 000 000',
                'address' => 'Casablanca, Morocco',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        $clientId = DB::table('clients')
            ->where('company_name', 'Acme Security')
            ->value('id');

        $projects = [
            [
                'name' => 'Corporate Portal',
                'domain' => 'portal.acme.test',
                'ip_address' => '192.168.10.12',
                'stack' => 'Laravel',
                'status' => 'active',
                'agent_last_seen_at' => now()->subMinutes(10),
            ],
            [
                'name' => 'Public Website',
                'domain' => 'www.acme.test',
                'ip_address' => '192.168.10.20',
                'stack' => 'WordPress',
                'status' => 'active',
                'agent_last_seen_at' => now()->subMinutes(4),
            ],
            [
                'name' => 'Client API',
                'domain' => 'api.acme.test',
                'ip_address' => '192.168.10.30',
                'stack' => 'Node.js',
                'status' => 'warning',
                'agent_last_seen_at' => now()->subHours(2),
            ],
            [
                'name' => 'Admin Console',
                'domain' => 'admin.acme.test',
                'ip_address' => '192.168.10.40',
                'stack' => 'Laravel',
                'status' => 'offline',
                'agent_last_seen_at' => null,
            ],
        ];

        foreach ($projects as $project) {
            DB::table('projects')->updateOrInsert(
                ['domain' => $project['domain']],
                [
                    ...$project,
                    'client_id' => $clientId,
                    'agent_api_key_hash' => hash('sha256', $project['domain']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }
}
