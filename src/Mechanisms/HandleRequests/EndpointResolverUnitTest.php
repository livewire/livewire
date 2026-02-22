<?php

namespace Livewire\Mechanisms\HandleRequests;

use Livewire\Mechanisms\HandleRequests\EndpointResolver;
use Tests\TestCase;

class EndpointResolverUnitTest extends TestCase
{
    public function test_generates_unique_prefix_from_app_key()
    {
        $prefix = EndpointResolver::prefix();

        // Should start with /livewire-
        $this->assertStringStartsWith('/livewire-', $prefix);

        // Should have 8 character hash suffix
        $this->assertMatchesRegularExpression('/^\/livewire-[a-f0-9]{8}$/', $prefix);
    }

    public function test_same_app_key_generates_same_prefix()
    {
        $prefix1 = EndpointResolver::prefix();
        $prefix2 = EndpointResolver::prefix();

        $this->assertEquals($prefix1, $prefix2);
    }

    public function test_different_app_keys_generate_different_prefixes()
    {
        $originalKey = config('app.key');

        $prefix1 = EndpointResolver::prefix();

        config()->set('app.key', 'base64:' . base64_encode('different-key-for-testing'));

        $prefix2 = EndpointResolver::prefix();

        // Restore original key
        config()->set('app.key', $originalKey);

        $this->assertNotEquals($prefix1, $prefix2);
    }

    public function test_update_path_uses_prefix()
    {
        $prefix = EndpointResolver::prefix();
        $path = EndpointResolver::updatePath();

        $this->assertEquals($prefix . '/update', $path);
    }

    public function test_script_path_uses_prefix()
    {
        $prefix = EndpointResolver::prefix();

        $this->assertEquals($prefix . '/livewire.js', EndpointResolver::scriptPath(minified: false));
        $this->assertEquals($prefix . '/livewire.min.js', EndpointResolver::scriptPath(minified: true));
    }

    public function test_map_path_uses_prefix()
    {
        $prefix = EndpointResolver::prefix();

        $this->assertEquals($prefix . '/livewire.min.js.map', EndpointResolver::mapPath(csp: false));
        $this->assertEquals($prefix . '/livewire.csp.min.js.map', EndpointResolver::mapPath(csp: true));
    }

    public function test_upload_path_uses_prefix()
    {
        $prefix = EndpointResolver::prefix();
        $path = EndpointResolver::uploadPath();

        $this->assertEquals($prefix . '/upload-file', $path);
    }

    public function test_preview_path_uses_prefix()
    {
        $prefix = EndpointResolver::prefix();
        $path = EndpointResolver::previewPath();

        $this->assertEquals($prefix . '/preview-file/{filename}', $path);
    }

    public function test_component_js_path_uses_prefix()
    {
        $prefix = EndpointResolver::prefix();
        $path = EndpointResolver::componentJsPath();

        $this->assertEquals($prefix . '/js/{component}.js', $path);
    }

    public function test_all_paths_share_same_prefix()
    {
        $prefix = EndpointResolver::prefix();

        $this->assertStringStartsWith($prefix, EndpointResolver::updatePath());
        $this->assertStringStartsWith($prefix, EndpointResolver::scriptPath());
        $this->assertStringStartsWith($prefix, EndpointResolver::uploadPath());
        $this->assertStringStartsWith($prefix, EndpointResolver::previewPath());
        $this->assertStringStartsWith($prefix, EndpointResolver::componentJsPath());
    }

    public function test_custom_route_prefix_is_prepended()
    {
        config()->set('livewire.route_prefix', 'legacy');

        $prefix = EndpointResolver::prefix();

        $this->assertStringStartsWith('/legacy/livewire-', $prefix);
        $this->assertMatchesRegularExpression('/^\/legacy\/livewire-[a-f0-9]{8}$/', $prefix);
    }

    public function test_custom_route_prefix_with_nested_path()
    {
        config()->set('livewire.route_prefix', 'api/v1');

        $prefix = EndpointResolver::prefix();

        $this->assertStringStartsWith('/api/v1/livewire-', $prefix);
        $this->assertMatchesRegularExpression('/^\/api\/v1\/livewire-[a-f0-9]{8}$/', $prefix);
    }

    public function test_custom_route_prefix_with_leading_trailing_slashes()
    {
        config()->set('livewire.route_prefix', '/legacy/');

        $prefix = EndpointResolver::prefix();

        $this->assertStringStartsWith('/legacy/livewire-', $prefix);
        $this->assertMatchesRegularExpression('/^\/legacy\/livewire-[a-f0-9]{8}$/', $prefix);
    }

    public function test_empty_custom_route_prefix_returns_default()
    {
        config()->set('livewire.route_prefix', '');

        $prefix = EndpointResolver::prefix();

        $this->assertMatchesRegularExpression('/^\/livewire-[a-f0-9]{8}$/', $prefix);
        $this->assertStringStartsWith('/livewire-', $prefix);
    }

    public function test_null_custom_route_prefix_returns_default()
    {
        config()->set('livewire.route_prefix', null);

        $prefix = EndpointResolver::prefix();

        $this->assertMatchesRegularExpression('/^\/livewire-[a-f0-9]{8}$/', $prefix);
        $this->assertStringStartsWith('/livewire-', $prefix);
    }

    public function test_custom_prefix_applies_to_all_paths()
    {
        config()->set('livewire.route_prefix', 'legacy');

        $this->assertStringStartsWith('/legacy/livewire-', EndpointResolver::updatePath());
        $this->assertStringStartsWith('/legacy/livewire-', EndpointResolver::scriptPath());
        $this->assertStringStartsWith('/legacy/livewire-', EndpointResolver::uploadPath());
        $this->assertStringStartsWith('/legacy/livewire-', EndpointResolver::previewPath());
        $this->assertStringStartsWith('/legacy/livewire-', EndpointResolver::componentJsPath());
        $this->assertStringStartsWith('/legacy/livewire-', EndpointResolver::componentCssPath());
        $this->assertStringStartsWith('/legacy/livewire-', EndpointResolver::componentGlobalCssPath());
    }
}
