<?php

namespace Livewire\Features\SupportDataBinding;

use Tests\BrowserTestCase;
use Livewire\Livewire;
use Livewire\Component;
use Livewire\Attributes\Computed;

class BrowserTest extends BrowserTestCase
{
    function test_can_use_wire_dirty()
    {
        Livewire::visit(new class extends Component {
            public $prop = false;

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk="checkbox" type="checkbox" wire:model="prop" value="true"  />

                        <div wire:dirty>Unsaved changes...</div>
                        <div wire:dirty.remove>The data is in-sync...</div>
                    </div>
                BLADE;
            }
        })
            ->assertSee('The data is in-sync...')
            ->check('@checkbox')
            ->pause(50)
            ->assertDontSee('The data is in-sync')
            ->assertSee('Unsaved changes...')
            ->uncheck('@checkbox')
            ->assertSee('The data is in-sync...')
            ->assertDontSee('Unsaved changes...')
        ;
    }

    function test_can_use_dollar_dirty_to_check_if_component_is_dirty()
    {
        Livewire::visit(new class extends Component {
            public $title = '';

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk="input" type="text" wire:model="title" />

                        <div x-show="$wire.$dirty()" dusk="dirty-indicator">Component is dirty</div>
                    </div>
                BLADE;
            }
        })
            ->assertNotVisible('@dirty-indicator')
            ->type('@input', 'Hello')
            ->pause(50)
            ->assertVisible('@dirty-indicator');
        ;
    }

    function test_can_use_dollar_dirty_to_check_if_specific_property_is_dirty()
    {
        Livewire::visit(new class extends Component {
            public $title = '';
            public $description = '';

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk="title" type="text" wire:model="title" />
                        <input dusk="description" type="text" wire:model="description" />

                        <div x-show="$wire.$dirty('title')" dusk="title-dirty">Title is dirty</div>
                        <div x-show="$wire.$dirty('description')" dusk="description-dirty">Description is dirty</div>
                    </div>
                BLADE;
            }
        })
            ->assertNotVisible('@title-dirty')
            ->assertNotVisible('@description-dirty')
            ->type('@title', 'Hello')
            ->pause(50)
            ->assertVisible('@title-dirty')
            ->assertNotVisible('@description-dirty')
            ->type('@description', 'World')
            ->pause(50)
            ->assertVisible('@title-dirty')
            ->assertVisible('@description-dirty')
        ;
    }

    function test_dollar_dirty_clears_after_network_request()
    {
        Livewire::visit(new class extends Component {
            public $title = '';

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk="input" type="text" wire:model="title" />

                        <button dusk="commit" type="button" wire:click="$commit">Commit</button>

                        <div x-show="$wire.$dirty()" dusk="dirty-indicator">Component is dirty</div>
                    </div>
                BLADE;
            }
        })
            ->assertNotVisible('@dirty-indicator')
            ->type('@input', 'Hello')
            ->pause(50)
            ->assertVisible('@dirty-indicator')
            ->waitForLivewire()->click('@commit')
            ->pause(50)
            ->assertNotVisible('@dirty-indicator')
        ;
    }

    function test_can_update_bound_value_from_lifecyle_hook()
    {
        Livewire::visit(new class extends Component {
            public $foo = null;

            public $bar = null;

            public function updatedFoo(): void
            {
                $this->bar = null;
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <select wire:model.live="foo" dusk="fooSelect">
                            <option value=""></option>
                            <option value="one">One</option>
                            <option value="two">Two</option>
                            <option value="three">Three</option>
                        </select>

                        <select wire:model="bar" dusk="barSelect">
                            <option value=""></option>
                            <option value="one">One</option>
                            <option value="two">Two</option>
                            <option value="three">Three</option>
                        </select>
                    </div>
                BLADE;
            }
        })
            ->select('@barSelect', 'one')
            ->waitForLivewire()->select('@fooSelect', 'one')
            ->assertSelected('@barSelect', '')
        ;
    }

    public function updates_dependent_select_options_correctly_when_wire_key_is_applied()
    {
        Livewire::visit(new class extends Component {
            public $parent = 'foo';

            public $child = 'bar';

            protected $options = [
                'foo' => [
                    'bar',
                ],
                'baz' => [
                    'qux',
                ],
            ];

            #[Computed]
            public function parentOptions(): array
            {
                return array_keys($this->options);
            }

            #[Computed]
            public function childOptions(): array
            {
                return $this->options[$this->parent];
            }

            public function render(): string
            {
                return <<<'blade'
                    <div>
                        <select wire:model.live="parent" dusk="parent">
                            @foreach($this->parentOptions as $value)
                                <option value="{{ $value }}">{{ $value }}</option>
                            @endforeach
                        </select>

                        <select wire:model="child" dusk="child" wire:key="{{ $parent }}">
                            <option value>Select</option>
                            @foreach($this->childOptions as $value)
                                <option value="{{ $value }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                blade;
            }
        })
            ->waitForLivewire()->select('@parent', 'baz')
            ->assertSelected('@child', '')
            ->waitForLivewire()->select('@parent', 'foo')
            ->assertSelected('@child', 'bar');
    }
}
