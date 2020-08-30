<?php

namespace Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\Livewire;

class ComponentPropertyBindingsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        if (PHP_VERSION_ID < 70400) {
            $this->markTestSkipped('Only applies to PHP 7.4 and above.');
            return;
        }

        // Only load the stubs if we're on PHP 7.4 or above, as the syntax is invalid otherwise
        require_once __DIR__.'/ComponentPropertyBindingsStubs.php';
    }

    /** @test */
    public function props_are_set_via_implicit_binding()
    {
        Livewire::component(ComponentWithPropBindings::class);

        Route::get('/foo/{model}', ComponentWithPropBindings::class);

        $this->get('/foo/route-model')->assertSeeText('prop:route-model');
    }

    /** @test */
    public function props_and_mount_work_together()
    {
        Livewire::component(ComponentWithPropBindingsAndMountMethod::class);

        Route::get('/foo/{parent}/child/{child}', ComponentWithPropBindingsAndMountMethod::class);

        $this->get('/foo/parent-model/child/child-model')->assertSeeText('parent-model:child-model');
    }
}
