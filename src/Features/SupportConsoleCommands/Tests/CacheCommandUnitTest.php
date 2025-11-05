<?php

namespace Livewire\Features\SupportConsoleCommands\Tests;


use Illuminate\Support\Facades\Artisan;
use Livewire\LivewireServiceProvider;
use Illuminate\Support\Facades\File;

class CacheCommandUnitTest extends \Tests\TestCase
{
    public function setUp(): void
    {
        // Create a livewire v4 components folder before laravel registers service provider
        parent::setUp();

        // Ensure components are cleared before each test...
        $this->makeACleanSlate();
    }

    public function test_view_cache_command_is_working_when_livewire_v3_views_folder_does_not_exist()
    {
        // Clear instances which were booted in LivewireServiceProvider
        $this->app->instance('livewire.finder', null);
        $this->app->instance('blade.compiler', null);
        $this->app->instance('view', null);

        // Create the Livewire v4 components folder
        File::ensureDirectoryExists($this->livewireComponentsPath());

        // Boot LivewireServiceProvider again
        $provider = $this->app->getProvider(LivewireServiceProvider::class);
        $provider->boot();

        $exitCode = Artisan::call('view:cache');
        $this->assertEquals(0, $exitCode);
    }
}
