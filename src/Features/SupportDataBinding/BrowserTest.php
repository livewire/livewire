<?php

namespace Livewire\Features\SupportDataBinding;

use Tests\BrowserTestCase;
use Livewire\Livewire;
use Livewire\Component;
use Livewire\Attributes\Computed;

class BrowserTest extends BrowserTestCase
{
    /** @test */
    function can_use_wire_dirty()
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
            ->assertDontSee('The data is in-sync')
            ->assertSee('Unsaved changes...')
            ->uncheck('@checkbox')
            ->assertSee('The data is in-sync...')
            ->assertDontSee('Unsaved changes...')
        ;
    }

    /** @test */
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
