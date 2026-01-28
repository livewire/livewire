<?php

namespace Livewire\Mechanisms\FrontendAssets;

use Livewire\Mechanisms\FrontendAssets\FrontendAssets;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;
use Tests\TestCase;

class EndpointResolverIntegrationUnitTest extends TestCase
{
    public function test_script_route_uses_endpoint_resolver_path()
    {
        $expectedPath = EndpointResolver::scriptPath(minified: !config('app.debug'));

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
        $expectedPath = EndpointResolver::updatePath();
        $actualPath = app('livewire')->getUpdateUri();

        $this->assertEquals($expectedPath, $actualPath);
    }

    public function test_all_endpoints_use_same_prefix()
    {
        $prefix = EndpointResolver::prefix();

        $this->assertStringStartsWith($prefix, EndpointResolver::updatePath());
        $this->assertStringStartsWith($prefix, EndpointResolver::scriptPath());
        $this->assertStringStartsWith($prefix, EndpointResolver::uploadPath());
    }
}
