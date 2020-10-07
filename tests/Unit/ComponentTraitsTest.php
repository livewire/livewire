<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;

class ComponentTraitsTest extends TestCase
{
    /** @test */
    public function trait_can_hook_into_before_render()
    {
        Livewire::test(ComponentWithTraitStub::class)
            ->assertSee('baz');
    }
}

trait TraitForComponent
{
    public function initializeTraitForComponent()
    {
        $this->beforeRender(function () {
            $this->name = 'baz';
        });
    }
}

class ComponentWithTraitStub extends Component
{
    use TraitForComponent;

    public $name = 'bar';

    public function render()
    {
        return view('show-name');
    }
}
