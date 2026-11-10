<?php

namespace Tests\Feature\Api\V1;

use App\Models\MobileDemoGuestTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MobileLoginApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_login_returns_one_time_ticket_for_active_email_or_username(): void
    {
        $user = User::factory()->create([
            'email' => 'mobile.user@example.test',
            'username' => 'mobile-user',
            'password' => Hash::make('correct-password'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/mobile/login', [
            'identifier' => 'mobile-user',
            'password' => 'correct-password',
        ])->assertOk()->assertHeader('Cache-Control', 'no-store, private');

        $ticket = $response->json('data.ticket');
        $this->assertIsString($ticket);
        $this->assertSame(64, strlen($ticket));
        $record = MobileDemoGuestTicket::query()->sole();
        $this->assertSame('user', $record->mode);
        $this->assertSame($user->id, $record->user_id);
        $this->assertSame(hash('sha256', $ticket), $record->token_hash);
        $this->assertNotSame($ticket, $record->token_hash);

        $this->post('/mobile/session/exchange', ['ticket' => $ticket])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user, 'web');
        $this->assertNotNull($record->fresh()->consumed_at);
    }

    public function test_mobile_login_rejects_wrong_password_and_inactive_accounts(): void
    {
        User::factory()->create([
            'email' => 'inactive@example.test',
            'username' => 'inactive-user',
            'password' => Hash::make('correct-password'),
            'is_active' => false,
        ]);
        User::factory()->create([
            'email' => 'active@example.test',
            'username' => 'active-user',
            'password' => Hash::make('correct-password'),
            'is_active' => true,
        ]);

        $this->postJson('/api/v1/mobile/login', [
            'identifier' => 'inactive-user', 'password' => 'correct-password',
        ])->assertUnauthorized();
        $this->postJson('/api/v1/mobile/login', [
            'identifier' => 'active-user', 'password' => 'wrong-password',
        ])->assertUnauthorized();
        $this->assertDatabaseCount('mobile_demo_guest_tickets', 0);
    }

    public function test_browser_login_does_not_render_app_only_controls(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertDontSee('Base URL')
            ->assertDontSee('data-mode="demo"')
            ->assertDontSee('data-mode="user"');

        $this->withHeaders(['User-Agent' => 'ONEPOSApp/1.0 Android WebView'])
            ->get('/login')
            ->assertOk()
            ->assertSee('Base URL')
            ->assertSee('data-mode="demo"', false)
            ->assertSee('data-mode="user"', false);
    }
}
