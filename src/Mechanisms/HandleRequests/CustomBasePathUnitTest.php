<?php

namespace Livewire\Mechanisms\HandleRequests;

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Livewire\Mechanisms\FrontendAssets\FrontendAssets;
use Tests\TestCase;
use Tests\TestComponent;

class CustomBasePathUnitTest extends TestCase
{
    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);

        $app['config']->set('livewire.custom_base_path', '/custom/livewire');
    }

    public function test_routes_are_registered_under_the_custom_base_path()
    {
        $routeUris = collect(Route::getRoutes()->getRoutes())->map->uri()->all();
        $script = config('app.debug') ? 'livewire.js' : 'livewire.min.js';

        foreach ([
            'update',
            $script,
            'livewire.min.js.map',
            'livewire.csp.min.js.map',
            'upload-file',
            'preview-file/{filename}',
            'js/{component}.js',
            'css/{component}.css',
            'css/{component}.global.css',
        ] as $endpoint) {
            $this->assertContains('custom/livewire/' . $endpoint, $routeUris);
        }
    }

    public function test_generated_asset_html_uses_the_custom_base_path()
    {
        $html = FrontendAssets::scripts();
        $script = config('app.debug') ? 'livewire.js' : 'livewire.min.js';

        preg_match('/src="([^"?]+)/', $html, $matches);

        $this->assertSame('/custom/livewire/' . $script, parse_url($matches[1] ?? '', PHP_URL_PATH));
        $this->assertStringContainsString('data-module-url="' . url('/custom/livewire') . '"', $html);
        $this->assertStringContainsString('data-update-uri="' . url('/custom/livewire/update') . '"', $html);
        $this->assertSame('/custom/livewire/update', Livewire::getUpdateUri());
    }

    public function test_update_requests_work_under_the_custom_base_path()
    {
        $component = Livewire::test(new class extends TestComponent {});

        $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson('/custom/livewire/update', ['components' => [
                ['snapshot' => json_encode($component->snapshot), 'updates' => [], 'calls' => []],
            ]])
            ->assertOk()
            ->assertJsonCount(1, 'components');
    }
}
