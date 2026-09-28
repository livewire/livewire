<?php

namespace Livewire\Features\SupportCssModules;

use Livewire\Livewire;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;
use Tests\TestCase;

class UnitTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Livewire::flushState();

        app('livewire.finder')->addNamespace('testns', viewPath: __DIR__.'/fixtures');
    }

    public function test_non_existent_component_css_module_returns_404()
    {
        $prefix = EndpointResolver::prefix();

        $this->get("{$prefix}/css/non-existent.css")->assertNotFound();
    }

    public function test_non_existent_component_global_css_module_returns_404()
    {
        $prefix = EndpointResolver::prefix();

        $this->get("{$prefix}/css/non-existent.global.css")->assertNotFound();
    }

    public function test_component_without_scoped_css_module_returns_404()
    {
        $prefix = EndpointResolver::prefix();

        $this->get("{$prefix}/css/testns---no-module.css")->assertNotFound();
    }

    public function test_component_without_global_css_module_returns_404()
    {
        $prefix = EndpointResolver::prefix();

        $this->get("{$prefix}/css/testns---no-module.global.css")->assertNotFound();
    }
}
