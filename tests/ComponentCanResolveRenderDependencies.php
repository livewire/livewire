<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;

class ComponentCanResolveRenderDependencies extends TestCase
{
    /** @test */
    public function public_name_property_is_set()
    {
        $component = app(LivewireManager::class)->test(DummyComponent::class);

        $component->assertSee('bar');
    }

}

class Dummy
{
    public $foo = 'bar';
}

class DummyComponent extends Component
{
    public function render(Dummy $dummy)
    {
        return view('dummy-view', ['dummy' => $dummy]);
    }
}
