<?php

namespace Livewire\Features\SupportNestingComponents;

use Livewire\Component;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    public function can_add_new_components()
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

    /** @test */
    public function nested_components_do_not_error_with_empty_elements_on_page()
    {
        Livewire::visit([
            new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <div>
                        </div>

                        <button type="button" wire:click="$refresh" dusk="refresh">
                            Refresh
                        </button>

                        <livewire:child />

                        <div>
                        </div>
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div dusk="child">
                        Child
                    </div>
                    HTML;
                }
            },
        ])
        ->assertPresent('@child')
        ->assertSeeIn('@child', 'Child')
        ->waitForLivewire()->click('@refresh')
        ->pause(500)
        ->assertPresent('@child')
        ->assertSeeIn('@child', 'Child')
        ->waitForLivewire()->click('@refresh')
        ->pause(500)
        ->assertPresent('@child')
        ->assertSeeIn('@child', 'Child')
        ;
    }

    /** @test */
    public function nested_components_do_not_error_when_child_deleted()
    {
        Livewire::visit([
            new class extends Component {
                public $children = [
                    'one',
                    'two'
                ];

                public function deleteChild($name) {
                    unset($this->children[array_search($name, $this->children)]);
                }

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <div>
                        </div>

                        @foreach($this->children as $key => $name)
                            <livewire:child wire:key="{{ $key }}" :name="$name" />
                        @endforeach

                        <div>
                        </div>
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                public $name = '';

                public function render()
                {
                    return <<<'HTML'
                    <div dusk="child-{{ $name }}">
                        {{ $name }}

                        <button dusk="delete-{{ $name }}" wire:click="$parent.deleteChild('{{ $name }}')">Delete</button>
                    </div>
                    HTML;
                }
            },
        ])
        ->assertPresent('@child-one')
        ->assertSeeIn('@child-one', 'one')
        ->waitForLivewire()->click('@delete-one')
        ->assertNotPresent('@child-one');
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
        return <<<'HTML'
        <div>
            <div>Page</div>

            @foreach($components as $component => $params)
                @livewire($component, $params, key($component))
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

class FirstComponent extends Component
{
    public function render()
    {
        return '<div>First Component Rendered</div>';
    }
}

class SecondComponent extends Component
{
    public function render()
    {
        return '<div>Second Component Rendered</div>';
    }
}

class ThirdComponent extends Component
{
    public function render()
    {
        return '<div>Third Component Rendered</div>';
    }
}
