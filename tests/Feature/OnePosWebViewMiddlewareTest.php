<?php

namespace Tests\Feature;

use Fruitcake\LaravelDebugbar\Facades\Debugbar;
use Tests\TestCase;

class OnePosWebViewMiddlewareTest extends TestCase
{
    public function test_onepos_app_requests_disable_the_laravel_debugbar(): void
    {
        Debugbar::enable();
        $this->assertTrue(Debugbar::isEnabled());

        $this->withHeader('User-Agent', 'Mozilla/5.0 ONEPOSApp/1.0')
            ->get('/privacy')
            ->assertOk();

        $this->assertFalse(Debugbar::isEnabled());
    }

    public function test_regular_browser_requests_do_not_disable_the_laravel_debugbar(): void
    {
        Debugbar::enable();

        $this->withHeader('User-Agent', 'Mozilla/5.0 Chrome/140.0')
            ->get('/privacy')
            ->assertOk();

        $this->assertTrue(Debugbar::isEnabled());
        Debugbar::disable();
    }
}
