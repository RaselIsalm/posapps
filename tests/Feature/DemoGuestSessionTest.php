<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class DemoGuestSessionTest extends TestCase
{
    private ?string $demoDatabasePath = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->demoDatabasePath = tempnam(sys_get_temp_dir(), 'onepos-demo-');
        config([
            'database.connections.demo.driver' => 'sqlite',
            'database.connections.demo.database' => $this->demoDatabasePath,
            'onepos.demo.enabled' => true,
            'onepos.demo.database_configured' => true,
            'onepos.demo.email' => 'demo-guest@pos.onetech.bd',
        ]);
        DB::purge('demo');

        Schema::connection('sqlite')->create('mobile_demo_guest_tickets', function (Blueprint $table) {
            $table->id();
            $table->char('token_hash', 64)->unique();
            $table->string('mode', 16)->default('demo');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();
        });
        Schema::connection('demo')->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('username')->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::connection('demo')->create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });
        Schema::connection('demo')->create('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        $roleId = DB::connection('demo')->table('roles')->insertGetId([
            'name' => 'Demo-Guest',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $user = User::withoutEvents(fn () => User::on('demo')->create([
            'name' => 'ONEPOS Demo Guest',
            'email' => config('onepos.demo.email'),
            'username' => 'onepos-demo-guest',
            'password' => Str::random(96),
            'is_active' => true,
        ]));
        DB::connection('demo')->table('model_has_roles')->insert([
            'role_id' => $roleId,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);

        Route::middleware('web')->get('/__tests/demo-context', fn () => response()->json([
            'connection' => DB::getDefaultConnection(),
            'email' => auth()->user()?->email,
            'guest' => session()->get('onepos_demo_guest') === true,
        ]));
        Route::middleware('web')->post('/__tests/demo-write', fn () => response('write allowed'));
    }

    protected function tearDown(): void
    {
        DB::purge('demo');
        if ($this->demoDatabasePath !== null && is_file($this->demoDatabasePath)) {
            unlink($this->demoDatabasePath);
        }
        parent::tearDown();
    }

    public function test_guest_ticket_exchanges_once_for_read_only_demo_web_session(): void
    {
        $ticket = $this->postJson('/api/v1/demo/guest-session')->assertOk()->json('data.ticket');
        $this->assertIsString($ticket);
        $this->assertSame(64, strlen($ticket));
        $this->assertDatabaseMissing('mobile_demo_guest_tickets', ['token_hash' => $ticket]);
        $this->assertDatabaseHas('mobile_demo_guest_tickets', [
            'token_hash' => hash('sha256', $ticket),
            'consumed_at' => null,
        ]);

        $this->post('/demo/session/exchange', ['ticket' => $ticket])
            ->assertRedirect(route('dashboard'));
        $this->assertNotNull(DB::table('mobile_demo_guest_tickets')
            ->where('token_hash', hash('sha256', $ticket))
            ->value('consumed_at'));

        $this->getJson('/__tests/demo-context')
            ->assertOk()
            ->assertJsonPath('connection', 'demo')
            ->assertJsonPath('email', config('onepos.demo.email'))
            ->assertJsonPath('guest', true);

        $this->postJson('/__tests/demo-write')
            ->assertForbidden()
            ->assertJsonPath('message', 'Demo access is read-only.');
    }

    public function test_replayed_demo_ticket_cannot_create_another_session(): void
    {
        $ticket = $this->postJson('/api/v1/demo/guest-session')->assertOk()->json('data.ticket');
        $this->post('/demo/session/exchange', ['ticket' => $ticket])->assertRedirect(route('dashboard'));

        $this->withSession(['onepos_demo_guest' => false])
            ->postJson('/demo/session/exchange', ['ticket' => $ticket])
            ->assertUnauthorized();
    }
}
