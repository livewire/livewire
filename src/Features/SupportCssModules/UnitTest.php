<?php

namespace Livewire\Features\SupportCssModules;

use Livewire\Mechanisms\HandleRequests\EndpointResolver;
use Tests\TestCase;

class UnitTest extends TestCase
{
    public function test_missing_css_modules_return_404()
    {
        app('livewire.finder')->addNamespace('testns', viewPath: __DIR__.'/fixtures');

        $prefix = EndpointResolver::prefix();

        $this->get("{$prefix}/css/non-existent.css")->assertNotFound();
        $this->get("{$prefix}/css/non-existent.global.css")->assertNotFound();
        $this->get("{$prefix}/css/testns---no-module.css")->assertNotFound();
        $this->get("{$prefix}/css/testns---no-module.global.css")->assertNotFound();
    }
}
