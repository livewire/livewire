<?php

namespace Tests\Unit;

use Livewire\Livewire;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Artisan;

class LivewireRoutePrefixTest extends TestCase
{
    public function setUp(): void
    {
        // Before the application is created, config() is not available. After it's created,
        // routes are already registered, so it's too late to use config().
        // Instead, we'll need to manually "publish" the config file so Laravel picks it up automatically
        $this->configFilePath = 'vendor/orchestra/testbench-core/laravel/config/livewire.php';

        $config = Str::replaceFirst(
            "'route_prefix'  => '/livewire'",
            "'route_prefix'  => '/{tenant}/foo'",
            file_get_contents('config/livewire.php')
        );

        file_put_contents($this->configFilePath, $config);

        parent::setUp();
    }

    public function tearDown(): void
    {
        // lets' make sure to clean up the configuration file afterwards
        unlink($this->configFilePath);
    }

    /** @test */
    public function livewire_prefix_can_be_customized()
    {
        // Set a default so that the route param does not need to be passed to the `route()` helper.
        // In a real application, this would need to be done anyway.
        URL::defaults(['tenant' => 'acme']);

        $this->assertEquals('/acme/foo', route('livewire.base', null, false));

        $this->assertStringContainsString(
            '<script src="/acme/foo/livewire.js?',
            Livewire::scripts()
        );

        $this->assertStringContainsString(
            "window.livewire_prefix = '/acme/foo';",
            Livewire::scripts()
        );
    }
}
