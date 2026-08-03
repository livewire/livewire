<?php

namespace Livewire\Features\SupportRenderless;

use Illuminate\Support\Facades\Blade;
use Livewire\Component;
use Livewire\Features\SupportEvents\BaseOn;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    public function test_dont_call_render_using_renderless_attribute()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            #[BaseRenderless]
            function renderlessAction() { }

            function render()
            {
                $this->count++;

                return Blade::render(<<<'HTML'
                <div>
                    <button wire:click="renderlessAction" dusk="button">{{ $count }}</button>
                </div>
                HTML, ['count' => $this->count]);
            }
        })
            ->assertSeeIn('@button', '1')
            ->waitForLivewire()->click('@button')
            ->assertSeeIn('@button', '1');
    }

    public function test_dont_call_render_using_base_renderless_attribute_with_event()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            #[BaseRenderless]
            #[BaseOn('some-event')]
            function renderlessAction() { }

            function render()
            {
                $this->count++;

                return Blade::render(<<<'HTML'
                <div>
                    <button wire:click="$dispatch('some-event')" dusk="button">{{ $count }}</button>
                </div>
                HTML, ['count' => $this->count]);
            }
        })
            ->assertSeeIn('@button', '1')
            ->waitForLivewire()->click('@button')
            ->assertSeeIn('@button', '1');
    }

    public function test_dont_call_render_using_renderless_modifier()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            function renderlessAction() { }

            function render()
            {
                $this->count++;

                return Blade::render(<<<'HTML'
                <div>
                    <button wire:click.renderless="renderlessAction" dusk="button">{{ $count }}</button>
                </div>
                HTML, ['count' => $this->count]);
            }
        })
            ->assertSeeIn('@button', '1')
            ->waitForLivewire()->click('@button')
            ->assertSeeIn('@button', '1');
    }

    public function test_render_is_not_skipped_when_a_renderless_action_is_batched_with_a_normal_action()
    {
        Livewire::visit(new class extends Component {
            public $name = 'foo';

            #[BaseRenderless]
            function renderlessAction() { }

            function updateName()
            {
                $this->name = 'bar';
            }

            function render()
            {
                return Blade::render(<<<'HTML'
                <div>
                    <button x-on:click="$wire.renderlessAction(); $wire.updateName()" dusk="button">{{ $name }}</button>
                </div>
                HTML, ['name' => $this->name]);
            }
        })
            ->assertSeeIn('@button', 'foo')
            ->waitForLivewire()->click('@button')
            ->assertSeeIn('@button', 'bar');
    }
}