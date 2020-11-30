<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

class ComponentTypedPropertyBindingsTest extends TestCase
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
    public function props_are_set_via_mount()
    {
        Livewire::test(ComponentWithPropBindings::class, [
            'model' => new PropBoundModel('mount-model'),
        ])->assertSeeText('prop:mount-model');
    }

    /** @test */
    public function props_are_set_via_implicit_binding()
    {
        Route::get('/foo/{model}', ComponentWithPropBindings::class);

        $this->get('/foo/route-model')->assertSeeText('prop:via-route:route-model');
    }

    /** @test */
    public function dependent_props_are_set_via_implicit_binding()
    {
        Route::get('/foo/{parent:custom}/bar/{child:custom}', ComponentWithDependentPropBindings::class);

        $this->get('/foo/robert/bar/bobby')->assertSeeText('prop:via-route:robert:via-parent:bobby');
    }

    /** @test */
    public function dependent_props_are_set_via_mount()
    {
        Route::get('/foo/{parent:custom}/bar/{child:custom}', ComponentWithDependentMountBindings::class);

        $this->get('/foo/robert/bar/bobby')->assertSeeText('prop:via-route:robert:via-parent:bobby');
    }

    /** @test */
    public function props_and_mount_work_together()
    {
        Route::get('/foo/{parent}/child/{child}', ComponentWithPropBindingsAndMountMethod::class);

        // In the case that a parent is a public property, and a child is injected via mount(),
        // the result will *not* resolve via the relationship (it's super edge-case and makes everything terrible)
        $this->withoutExceptionHandling()->get('/foo/parent-model/child/child-model')->assertSeeText('via-route:parent-model:via-route:child-model');
    }
}
