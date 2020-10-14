<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;

class ComponentTraitsTest extends TestCase
{
    /** @test */
    public function traits_can_intercept_lifecycle_hooks()
    {
        Livewire::test(ComponentWithTraitStub::class)
            ->assertSet(
                'hooksFromTrait',
                ['initialized', 'hydrate', 'mount', 'rendering', 'rendered:show-name', 'dehydrate']
            )
            ->set('foo', 'bar')
            ->assertSet(
                'hooksFromTrait',
                ['initialized', 'hydrate', 'updating:foobar', 'updated:foobar', 'rendering', 'rendered:show-name', 'dehydrate']
            );
    }
}

trait TraitForComponent
{
    public function mountTraitForComponent()
    {
        $this->hooksFromTrait[] = 'mount';
    }

    public function hydrateTraitForComponent()
    {
        $this->hooksFromTrait[] = 'hydrate';
    }

    public function dehydrateTraitForComponent()
    {
        $this->hooksFromTrait[] = 'dehydrate';
    }

    public function updatingTraitForComponent($name, $value)
    {
        $this->hooksFromTrait[] = 'updating:'.$name.$value;
    }

    public function updatedTraitForComponent($name, $value)
    {
        $this->hooksFromTrait[] = 'updated:'.$name.$value;
    }

    public function renderingTraitForComponent()
    {
        $this->hooksFromTrait[] = 'rendering';
    }

    public function renderedTraitForComponent($view)
    {
        $this->hooksFromTrait[] = 'rendered:'.$view->getName();
    }

    public function initializeTraitForComponent()
    {
        // Reset from previous requests.
        $this->hooksFromTrait = [];

        $this->hooksFromTrait[] = 'initialized';
    }
}

class ComponentWithTraitStub extends Component
{
    use TraitForComponent;

    public $hooksFromTrait = [];

    public $foo = 'bar';

    public function render()
    {
        return view('show-name', ['name' => $this->foo]);
    }
}
