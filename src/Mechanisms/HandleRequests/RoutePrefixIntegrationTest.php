<?php

namespace Livewire\Mechanisms\HandleRequests;

use Illuminate\Support\Facades\Route;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;
use Livewire\Mechanisms\FrontendAssets\FrontendAssets;
use Tests\TestCase;

class RoutePrefixIntegrationTest extends TestCase
{
    public function test_custom_route_prefix_affects_all_registered_routes()
    {
        config()->set('livewire.route_prefix', 'legacy');

        // Force re-registration by clearing the route collection
        Route::getRoutes()->refreshNameLookups();

        $prefix = EndpointResolver::prefix();

        // Verify prefix includes custom prefix
        $this->assertStringStartsWith('/legacy/livewire-', $prefix);

        // Verify all endpoint methods return prefixed paths
        $this->assertStringStartsWith('/legacy/livewire-', EndpointResolver::updatePath());
        $this->assertStringStartsWith('/legacy/livewire-', EndpointResolver::scriptPath());
        $this->assertStringStartsWith('/legacy/livewire-', EndpointResolver::uploadPath());
        $this->assertStringStartsWith('/legacy/livewire-', EndpointResolver::previewPath());
        $this->assertStringStartsWith('/legacy/livewire-', EndpointResolver::componentJsPath());
        $this->assertStringStartsWith('/legacy/livewire-', EndpointResolver::componentCssPath());
        $this->assertStringStartsWith('/legacy/livewire-', EndpointResolver::componentGlobalCssPath());
    }

    public function test_nested_route_prefix_works_correctly()
    {
        config()->set('livewire.route_prefix', 'api/v1');

        $prefix = EndpointResolver::prefix();

        $this->assertStringStartsWith('/api/v1/livewire-', $prefix);
        $this->assertStringStartsWith('/api/v1/livewire-', EndpointResolver::updatePath());
    }

    public function test_update_path_reflects_custom_prefix()
    {
        config()->set('livewire.route_prefix', 'custom');

        $updatePath = EndpointResolver::updatePath();

        $this->assertStringStartsWith('/custom/livewire-', $updatePath);
        $this->assertStringContainsString('/update', $updatePath);
    }

    public function test_frontend_assets_script_path_uses_custom_prefix()
    {
        config()->set('livewire.route_prefix', 'prefixed');

        // Get the script path from EndpointResolver
        $expectedPath = EndpointResolver::scriptPath(minified: !config('app.debug'));

        // Verify it includes the custom prefix
        $this->assertStringStartsWith('/prefixed/livewire-', $expectedPath);
    }

    public function test_empty_prefix_maintains_default_behavior()
    {
        config()->set('livewire.route_prefix', '');

        $prefix = EndpointResolver::prefix();

        // Should start with /livewire- (no custom prefix)
        $this->assertMatchesRegularExpression('/^\/livewire-[a-f0-9]{8}$/', $prefix);
        $this->assertStringStartsWith('/livewire-', $prefix);
        $this->assertStringNotContainsString('//', $prefix);
    }

    public function test_null_prefix_maintains_default_behavior()
    {
        config()->set('livewire.route_prefix', null);

        $prefix = EndpointResolver::prefix();

        $this->assertMatchesRegularExpression('/^\/livewire-[a-f0-9]{8}$/', $prefix);
        $this->assertStringStartsWith('/livewire-', $prefix);
    }

    public function test_prefix_with_multiple_slashes_is_normalized()
    {
        config()->set('livewire.route_prefix', '///legacy///');

        $prefix = EndpointResolver::prefix();

        // Should normalize to /legacy/livewire-{hash} (no multiple slashes)
        $this->assertStringStartsWith('/legacy/livewire-', $prefix);
        $this->assertStringNotContainsString('//', $prefix);
    }

    public function test_all_route_types_use_consistent_prefix()
    {
        config()->set('livewire.route_prefix', 'consistent');

        $updatePath = EndpointResolver::updatePath();
        $scriptPath = EndpointResolver::scriptPath();
        $uploadPath = EndpointResolver::uploadPath();
        $previewPath = EndpointResolver::previewPath();
        $jsPath = EndpointResolver::componentJsPath();
        $cssPath = EndpointResolver::componentCssPath();
        $globalCssPath = EndpointResolver::componentGlobalCssPath();

        // All should start with the same prefix
        $allPaths = [
            $updatePath,
            $scriptPath,
            $uploadPath,
            $previewPath,
            $jsPath,
            $cssPath,
            $globalCssPath,
        ];

        $prefix = EndpointResolver::prefix();

        foreach ($allPaths as $path) {
            $this->assertStringStartsWith($prefix, $path, "Path {$path} does not start with prefix {$prefix}");
        }
    }

    public function test_prefix_works_with_special_characters_in_config()
    {
        config()->set('livewire.route_prefix', 'api-v2');

        $prefix = EndpointResolver::prefix();

        $this->assertStringStartsWith('/api-v2/livewire-', $prefix);
    }

    public function test_deeply_nested_prefix_works()
    {
        config()->set('livewire.route_prefix', 'api/v1/admin/legacy');

        $prefix = EndpointResolver::prefix();

        $this->assertStringStartsWith('/api/v1/admin/legacy/livewire-', $prefix);
        $this->assertMatchesRegularExpression('/^\/api\/v1\/admin\/legacy\/livewire-[a-f0-9]{8}$/', $prefix);
    }
}
