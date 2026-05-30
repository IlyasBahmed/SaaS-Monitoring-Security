<?php

namespace Tests\Feature\Auth;

use App\Models\clients;
use App\Models\Projects;
use App\Models\ReportRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_invited_soc_analyst_becomes_active_after_defining_password(): void
    {
        $user = User::factory()->create([
            'role' => 'SOC Analyst',
            'status' => ' pending ',
        ]);

        $token = Password::createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertSame('Active', $user->fresh()->status);
        $this->assertAuthenticatedAs($user);
    }

    public function test_active_status_with_extra_spaces_can_login(): void
    {
        $user = User::factory()->create([
            'role' => 'SOC Analyst',
            'status' => ' Active ',
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_soc_analyst_can_open_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'SOC Analyst',
            'status' => 'Active',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_soc_analyst_can_open_cloudflare_page(): void
    {
        $user = User::factory()->create([
            'role' => 'SOC Analyst',
            'status' => 'Active',
        ]);

        $this->actingAs($user)
            ->get(route('cloudflare.index'))
            ->assertOk();
    }

    public function test_soc_analyst_cannot_open_admin_users_screen(): void
    {
        $user = User::factory()->create([
            'role' => 'SOC Analyst',
            'status' => 'Active',
        ]);

        $this->actingAs($user)
            ->get(route('users.roles'))
            ->assertForbidden();
    }

    public function test_client_user_can_open_client_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'Client',
            'status' => 'active',
        ]);

        clients::create([
            'user_id' => $user->id,
            'company_name' => 'Acme Security',
            'email' => $user->email,
            'phone' => null,
            'address' => null,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('Client Dashboard')
            ->assertSee('Acme Security');
    }

    public function test_client_user_can_open_only_their_projects_page(): void
    {
        $user = User::factory()->create([
            'role' => 'Client',
            'status' => 'active',
        ]);
        $otherUser = User::factory()->create([
            'role' => 'Client',
            'status' => 'active',
        ]);

        $client = clients::create([
            'user_id' => $user->id,
            'company_name' => 'Acme Security',
            'email' => $user->email,
            'phone' => null,
            'address' => null,
            'status' => 'active',
        ]);
        $otherClient = clients::create([
            'user_id' => $otherUser->id,
            'company_name' => 'Other Client',
            'email' => $otherUser->email,
            'phone' => null,
            'address' => null,
            'status' => 'active',
        ]);

        Projects::create([
            'client_id' => $client->id,
            'name' => 'Client Portal',
            'domain' => 'portal.example.test',
            'ip_address' => '10.0.0.10',
            'stack' => 'Laravel',
            'status' => 'active',
        ]);
        Projects::create([
            'client_id' => $otherClient->id,
            'name' => 'Hidden Site',
            'domain' => 'hidden.example.test',
            'ip_address' => '10.0.0.20',
            'stack' => 'WordPress',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('client.projects'))
            ->assertOk()
            ->assertSee('Projects / Sites')
            ->assertSee('Client Portal')
            ->assertSee('portal.example.test')
            ->assertDontSee('Hidden Site')
            ->assertDontSee('hidden.example.test');
    }

    public function test_client_user_can_open_only_their_alerts_page(): void
    {
        $user = User::factory()->create([
            'role' => 'Client',
            'status' => 'active',
        ]);
        $otherUser = User::factory()->create([
            'role' => 'Client',
            'status' => 'active',
        ]);

        $client = clients::create([
            'user_id' => $user->id,
            'company_name' => 'Acme Security',
            'email' => $user->email,
            'phone' => null,
            'address' => null,
            'status' => 'active',
        ]);
        $otherClient = clients::create([
            'user_id' => $otherUser->id,
            'company_name' => 'Other Client',
            'email' => $otherUser->email,
            'phone' => null,
            'address' => null,
            'status' => 'active',
        ]);

        Projects::create([
            'client_id' => $client->id,
            'name' => 'Client Portal',
            'domain' => 'portal.example.test',
            'ip_address' => '10.0.0.10',
            'stack' => 'Laravel',
            'status' => 'active',
        ]);
        Projects::create([
            'client_id' => $otherClient->id,
            'name' => 'Hidden Site',
            'domain' => 'hidden.example.test',
            'ip_address' => '10.0.0.20',
            'stack' => 'WordPress',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('client.alerts'))
            ->assertOk()
            ->assertSee('Alerts')
            ->assertSee('Acme Security')
            ->assertSee('Client Portal')
            ->assertSee('portal.example.test')
            ->assertDontSee('Hidden Site')
            ->assertDontSee('hidden.example.test');
    }

    public function test_client_user_is_redirected_from_platform_dashboard_to_client_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'Client',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('client.dashboard'));
    }

    public function test_client_user_cannot_open_admin_users_screen(): void
    {
        $user = User::factory()->create([
            'role' => 'Client',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('users.roles'))
            ->assertForbidden();
    }

    public function test_client_user_can_request_report(): void
{
    $user = User::factory()->create([
        'role' => 'Client',
        'status' => 'active',
    ]);

    $client = clients::create([
        'user_id' => $user->id,
        'company_name' => 'Acme Security',
        'email' => $user->email,
        'status' => 'active',
    ]);

    $project = $client->projects()->create([
        'name' => 'Test Project',
        'status' => 'active',
    ]);

    $this->actingAs($user)
        ->post(route('client.reports.store'), [
            'project_id' => $project->id,
            'type' => 'project_security',
            'period' => 'last_30_days',
            'note' => 'Please include executive summary.',
        ])
        ->assertRedirect(route('client.reports.index'));

    $this->assertDatabaseHas('report_requests', [
        'client_id' => $client->id,
        'user_id' => $user->id,
        'project_id' => $project->id,
        'type' => 'project_security',
        'period' => 'last_30_days',
        'status' => 'ready',
    ]);
}

    public function test_client_user_cannot_open_reports_page(): void
    {
        $user = User::factory()->create([
            'role' => 'Client',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('reports.index'))
            ->assertForbidden();
    }

    public function test_client_user_cannot_open_platform_alerts_page(): void
    {
        $user = User::factory()->create([
            'role' => 'Client',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('alerts.index'))
            ->assertForbidden();
    }
}
