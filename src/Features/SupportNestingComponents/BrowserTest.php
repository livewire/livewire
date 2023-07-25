<?php

namespace Livewire\Features\SupportNestingComponents;

use Livewire\Livewire;
use Livewire\Component;

class BrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    function can_add_new_components()
    {
        Livewire::visit([
            Page::class,
            'first-component' => FirstComponent::class,
            'second-component' => SecondComponent::class,
            'third-component' => ThirdComponent::class,
        ])
            ->assertSee('Page')

            ->waitForLivewire()->click('@add-first')
            ->assertSee('First Component Rendered')
            ->assertDontSee('Second Component Rendered')
            ->assertDontSee('Third Component Rendered')

            ->waitForLivewire()->click('@add-second')
            ->assertSee('First Component Rendered')
            ->assertSee('Second Component Rendered')
            ->assertDontSee('Third Component Rendered')

            ->waitForLivewire()->click('@add-third')
            ->assertSee('First Component Rendered')
            ->assertSee('Second Component Rendered')
            ->assertSee('Third Component Rendered')

            ->waitForLivewire()->click('@remove-second')
            ->assertSee('First Component Rendered')
            ->assertDontSee('Second Component Rendered')
            ->assertSee('Third Component Rendered')
            ;
    }
}

class Page extends Component
{
    public $components = [];

    public function add($item)
    {
        $this->components[$item] = [];
    }

    public function remove($item)
    {
        unset($this->components[$item]);
    }

    public function render()
    {
        return <<<HTML
        <div>
            <div>Page</div>

            @foreach(\$components as \$component => \$params)
                @livewire(\$component, \$params, key(\$component))
            @endforeach

            <div>
                <button dusk="add-first" wire:click="add('first-component')">Add first-component</button>
                <button dusk="add-second" wire:click="add('second-component')">Add second-component</button>
                <button dusk="add-third" wire:click="add('third-component')">Add third-component</button>
            </div>

            <div>
                <button dusk="remove-first" wire:click="remove('first-component')">Remove first-component</button>
                <button dusk="remove-second" wire:click="remove('second-component')">Remove second-component</button>
                <button dusk="remove-third" wire:click="remove('third-component')">Remove third-component</button>
            </div>
        </div>
        HTML;
    }
}

class FirstComponent extends Component {
    function render() {
        return '<div>First Component Rendered</div>';
    }
}

class SecondComponent extends Component {
    function render() {
        return '<div>Second Component Rendered</div>';
    }
}

class ThirdComponent extends Component {
    function render() {
        return '<div>Third Component Rendered</div>';
    }
}
