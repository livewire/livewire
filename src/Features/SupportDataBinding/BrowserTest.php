<?php

namespace Livewire\Features\SupportDataBinding;

use Illuminate\Support\Facades\Blade;
use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

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
    function can_set_array_value()
    {
        Livewire::visit(new class extends Component {
            public array $filters = [];

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        @foreach(['a', 'b', 'c'] as $key)
                            <input dusk="checkbox.int.{{ $key }}" value="{{ $loop->index + 1}}" type="checkbox" wire:model.live="filters.{{ $key }}" />
                        @endforeach

                        @foreach(['d', 'e', 'f'] as $key)
                            <input dusk="checkbox.string.{{ $key }}" value="value_{{ $key }}" type="checkbox" wire:model.live="filters.{{ $key }}" />
                        @endforeach

                        <button wire:click="$set('filters', [])" dusk="filters.reset">Reset</button>

                        <div dusk="output">@json($filters)</div>
                    </div>
                BLADE;
            }
        })
            ->check('@checkbox.int.a')
            ->check('@checkbox.int.b')
            ->check('@checkbox.int.c')
            ->waitForLivewireToLoad()
            ->assertSeeIn('@output', '{"a":"1","b":"2","c":"3"}')
            ->click('@filters.reset')
            ->waitForLivewireToLoad()
            ->assertSeeIn('@output', '[]')
            ->check('@checkbox.string.d')
            ->check('@checkbox.string.e')
            ->check('@checkbox.string.f')
            ->waitForLivewireToLoad()
            ->assertSeeIn('@output', '{"d":"value_d","e":"value_e","f":"value_f"}')
        ;

    }
}
