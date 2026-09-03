<?php

namespace Livewire\Mechanisms\HandleRequests;

use Livewire\Facades\LivewireEndpoint;
use Tests\TestCase;

class EndpointResolverUnitTest extends TestCase
{
    public function test_endpoint_resolver_can_be_overridden()
    {
        $this->app->singleton(EndpointResolverInterface::class, CustomEndpointResolver::class);

        LivewireEndpoint::clearResolvedInstance(EndpointResolverInterface::class);

        $this->assertSame('/custom-livewire', LivewireEndpoint::prefix());
        $this->assertSame('/custom-livewire/custom-update', LivewireEndpoint::updatePath());
        $this->assertSame('/custom-livewire/custom.js', LivewireEndpoint::scriptPath());
        $this->assertSame('/custom-livewire/custom.min.js', LivewireEndpoint::scriptPath(minified: true));
        $this->assertSame('/custom-livewire/custom.map', LivewireEndpoint::mapPath());
        $this->assertSame('/custom-livewire/custom.csp.map', LivewireEndpoint::mapPath(csp: true));
        $this->assertSame('/custom-livewire/custom-upload', LivewireEndpoint::uploadPath());
        $this->assertSame('/custom-livewire/custom-preview/{filename}', LivewireEndpoint::previewPath());
        $this->assertSame('/custom-livewire/custom-js/{component}.js', LivewireEndpoint::componentJsPath());
        $this->assertSame('/custom-livewire/custom-css/{component}.css', LivewireEndpoint::componentCssPath());
        $this->assertSame('/custom-livewire/custom-css/{component}.global.css', LivewireEndpoint::componentGlobalCssPath());
    }

    public function test_generates_unique_prefix_from_app_key()
    {
        $prefix = LivewireEndpoint::prefix();

        // Should start with /livewire-
        $this->assertStringStartsWith('/livewire-', $prefix);

        // Should have 8 character hash suffix
        $this->assertMatchesRegularExpression('/^\/livewire-[a-f0-9]{8}$/', $prefix);
    }

    public function test_same_app_key_generates_same_prefix()
    {
        $prefix1 = LivewireEndpoint::prefix();
        $prefix2 = LivewireEndpoint::prefix();

        $this->assertEquals($prefix1, $prefix2);
    }

    public function test_different_app_keys_generate_different_prefixes()
    {
        $originalKey = config('app.key');

        $prefix1 = LivewireEndpoint::prefix();

        config()->set('app.key', 'base64:' . base64_encode('different-key-for-testing'));

        $prefix2 = LivewireEndpoint::prefix();

        // Restore original key
        config()->set('app.key', $originalKey);

        $this->assertNotEquals($prefix1, $prefix2);
    }

    public function test_update_path_uses_prefix()
    {
        $prefix = LivewireEndpoint::prefix();
        $path = LivewireEndpoint::updatePath();

        $this->assertEquals($prefix . '/update', $path);
    }

    public function test_script_path_uses_prefix()
    {
        $prefix = LivewireEndpoint::prefix();

        $this->assertEquals($prefix . '/livewire.js', LivewireEndpoint::scriptPath(minified: false));
        $this->assertEquals($prefix . '/livewire.min.js', LivewireEndpoint::scriptPath(minified: true));
    }

    public function test_map_path_uses_prefix()
    {
        $prefix = LivewireEndpoint::prefix();

        $this->assertEquals($prefix . '/livewire.min.js.map', LivewireEndpoint::mapPath(csp: false));
        $this->assertEquals($prefix . '/livewire.csp.min.js.map', LivewireEndpoint::mapPath(csp: true));
    }

    public function test_upload_path_uses_prefix()
    {
        $prefix = LivewireEndpoint::prefix();
        $path = LivewireEndpoint::uploadPath();

        $this->assertEquals($prefix . '/upload-file', $path);
    }

    public function test_preview_path_uses_prefix()
    {
        $prefix = LivewireEndpoint::prefix();
        $path = LivewireEndpoint::previewPath();

        $this->assertEquals($prefix . '/preview-file/{filename}', $path);
    }

    public function test_component_js_path_uses_prefix()
    {
        $prefix = LivewireEndpoint::prefix();
        $path = LivewireEndpoint::componentJsPath();

        $this->assertEquals($prefix . '/js/{component}.js', $path);
    }

    public function test_all_paths_share_same_prefix()
    {
        $prefix = LivewireEndpoint::prefix();

        $this->assertStringStartsWith($prefix, LivewireEndpoint::updatePath());
        $this->assertStringStartsWith($prefix, LivewireEndpoint::scriptPath());
        $this->assertStringStartsWith($prefix, LivewireEndpoint::uploadPath());
        $this->assertStringStartsWith($prefix, LivewireEndpoint::previewPath());
        $this->assertStringStartsWith($prefix, LivewireEndpoint::componentJsPath());
    }
}

class CustomEndpointResolver implements EndpointResolverInterface
{
    public function prefix(): string
    {
        return '/custom-livewire';
    }

    public function updatePath(): string
    {
        return $this->prefix() . '/custom-update';
    }

    public function scriptPath(bool $minified = false): string
    {
        return $this->prefix() . ($minified ? '/custom.min.js' : '/custom.js');
    }

    public function mapPath(bool $csp = false): string
    {
        return $this->prefix() . ($csp ? '/custom.csp.map' : '/custom.map');
    }

    public function uploadPath(): string
    {
        return $this->prefix() . '/custom-upload';
    }

    public function previewPath(): string
    {
        return $this->prefix() . '/custom-preview/{filename}';
    }

    public function componentJsPath(): string
    {
        return $this->prefix() . '/custom-js/{component}.js';
    }

    public function componentCssPath(): string
    {
        return $this->prefix() . '/custom-css/{component}.css';
    }

    public function componentGlobalCssPath(): string
    {
        return $this->prefix() . '/custom-css/{component}.global.css';
    }
}
