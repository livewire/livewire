<?php

namespace Livewire\Mechanisms\FrontendAssets;

use Livewire\Facades\LivewireEndpoint;
use Livewire\Mechanisms\FrontendAssets\FrontendAssets;
use Tests\TestCase;

class EndpointResolverIntegrationUnitTest extends TestCase
{
    public function test_script_route_uses_endpoint_resolver_path()
    {
        $expectedPath = LivewireEndpoint::scriptPath(minified: !config('app.debug'));

        $frontendAssets = app(FrontendAssets::class);
        $actualPath = '/' . ltrim($frontendAssets->javaScriptRoute->uri, '/');

        $this->assertEquals($expectedPath, $actualPath);
    }

    public function test_script_url_in_html_matches_registered_route()
    {
        $frontendAssets = app(FrontendAssets::class);
        $routeUri = '/' . ltrim($frontendAssets->javaScriptRoute->uri, '/');

        $html = FrontendAssets::scripts();

        // Extract src from script tag (now a full URL, so parse the path)
        preg_match('/src="([^"?]+)/', $html, $matches);
        $srcUrl = $matches[1] ?? '';
        $srcPath = parse_url($srcUrl, PHP_URL_PATH);

        $this->assertEquals($routeUri, $srcPath);
    }

    public function test_update_uri_uses_endpoint_resolver_path()
    {
        $expectedPath = LivewireEndpoint::updatePath();
        $actualPath = app('livewire')->getUpdateUri();

        $this->assertEquals($expectedPath, $actualPath);
    }

    public function test_all_endpoints_use_same_prefix()
    {
        $prefix = LivewireEndpoint::prefix();

        $this->assertStringStartsWith($prefix, LivewireEndpoint::updatePath());
        $this->assertStringStartsWith($prefix, LivewireEndpoint::scriptPath());
        $this->assertStringStartsWith($prefix, LivewireEndpoint::uploadPath());
    }
}
