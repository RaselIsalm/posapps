<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;

class MobileBootstrapApiTest extends TestCase
{
    public function test_mobile_bootstrap_is_public_and_identifies_onepos_compatibility(): void
    {
        $this->getJson('/api/v1/mobile/bootstrap')
            ->assertOk()
            ->assertJsonPath('data.application', 'ONEPOS')
            ->assertJsonPath('data.api_version', 1)
            ->assertJsonPath('data.web_login', true)
            ->assertJsonPath('data.demo_guest', false);
    }

    public function test_demo_guest_ticket_is_unavailable_until_demo_is_configured(): void
    {
        config([
            'onepos.demo.enabled' => false,
            'onepos.demo.database_configured' => false,
        ]);

        $this->postJson('/api/v1/demo/guest-session')
            ->assertNotFound();
    }
}
