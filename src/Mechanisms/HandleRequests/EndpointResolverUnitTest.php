<?php

namespace Livewire\Mechanisms\HandleRequests;

use Livewire\Mechanisms\HandleRequests\EndpointResolver;
use Tests\TestCase;

class EndpointResolverUnitTest extends TestCase
{
    public function test_endpoint_resolver_can_be_overridden()
    {
        $this->app->singleton(EndpointResolverInterface::class, CustomEndpointResolver::class);

        $this->assertSame('/custom-livewire', EndpointResolver::prefix());
        $this->assertSame('/custom-livewire/update', EndpointResolver::updatePath());
        $this->assertSame('/custom-livewire/livewire.js', EndpointResolver::scriptPath(config('app.debug')));
        $this->assertSame('/custom-livewire/livewire.min.js', EndpointResolver::scriptPath(! config('app.debug')));
        $this->assertSame('/custom-livewire/livewire.min.js.map', EndpointResolver::mapPath());
        $this->assertSame('/custom-livewire/livewire.csp.min.js.map', EndpointResolver::mapPath(csp: true));
        $this->assertSame('/custom-livewire/upload-file', EndpointResolver::uploadPath());
        $this->assertSame('/custom-livewire/preview-file/{filename}', EndpointResolver::previewPath());
        $this->assertSame('/custom-livewire/js/{component}.js', EndpointResolver::componentJsPath());
        $this->assertSame('/custom-livewire/css/{component}.css', EndpointResolver::componentCssPath());
        $this->assertSame('/custom-livewire/css/{component}.global.css', EndpointResolver::componentGlobalCssPath());
    }

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
}

class CustomEndpointResolver implements EndpointResolverInterface
{
    public function prefix(): string
    {
        return '/custom-livewire';
    }

    public function updatePath(): string
    {
        return $this->prefix() . '/update';
    }

    public function scriptPath(bool $minified = false): string
    {
        $file = $minified ? 'livewire.min.js' : 'livewire.js';

        return $this->prefix() . '/' . $file;
    }

    public function mapPath(bool $csp = false): string
    {
        $file = $csp ? 'livewire.csp.min.js.map' : 'livewire.min.js.map';

        return $this->prefix() . '/' . $file;
    }

    public function uploadPath(): string
    {
        return $this->prefix() . '/upload-file';
    }

    public function previewPath(): string
    {
        return $this->prefix() . '/preview-file/{filename}';
    }

    public function componentJsPath(): string
    {
        return $this->prefix() . '/js/{component}.js';
    }

    public function componentCssPath(): string
    {
        return $this->prefix() . '/css/{component}.css';
    }

    public function componentGlobalCssPath(): string
    {
        return $this->prefix() . '/css/{component}.global.css';
    }
}
