<?php

namespace Livewire\Features\SupportLazyLoading;

use Tests\TestCase;
use Livewire\Livewire;
use Livewire\Component;

class Test extends TestCase
{
    /** @test */
    public function can_lazy_load_a_component()
    {
        Livewire::visit([new class extends Component {
            public function render() { return <<<HTML
            <div>
                <livewire:child lazy />
            </div>
            HTML; }
        }, 'child' => new class extends Component {
            public function mount() { sleep(1); }
            public function render() { return <<<HTML
            <div id="child">
                Child!
            </div>
            HTML; }
        }])
        ->assertDontSee('Child!')
        ->waitFor('#child')
        ->assertSee('Child!')
        ;
    }

    /** @test */
    public function can_lazy_load_a_component_with_a_placeholder()
    {
        Livewire::visit([new class extends Component {
            public function render() { return <<<HTML
            <div>
                <livewire:child lazy />
            </div>
            HTML; }
        }, 'child' => new class extends Component {
            public function mount() { sleep(1); }
            public static function placeholder() { return <<<HTML
                <div id="loading">
                    Loading...
                </div>
                HTML; }
            public function render() { return <<<HTML
            <div id="child">
                Child!
            </div>
            HTML; }
        }])
        ->assertSee('Loading...')
        ->assertDontSee('Child!')
        ->waitFor('#child')
        ->assertDontSee('Loading...')
        ->assertSee('Child!')
        ;
    }

    /** @test */
    public function can_pass_props_to_lazyilly_loaded_component()
    {
        Livewire::visit([new class extends Component {
            public $count = 1;
            public function render() { return <<<'HTML'
            <div>
                <livewire:child :$count lazy />
            </div>
            HTML; }
        }, 'child' => new class extends Component {
            public $count;
            public function mount() { sleep(1); }
            public function render() { return <<<'HTML'
            <div id="child">
                Count: {{ $count }}
            </div>
            HTML; }
        }])
        ->waitFor('#child')
        ->assertSee('Count: 1')
        ;
    }

    /** @test */
    public function can_pass_reactive_props_to_lazyilly_loaded_component()
    {
        Livewire::visit([new class extends Component {
            public $count = 1;
            public function inc() { $this->count++; }
            public function render() { return <<<'HTML'
            <div>
                <livewire:child :$count lazy />
                <button wire:click="inc" dusk="button">+</button>
            </div>
            HTML; }
        }, 'child' => new class extends Component {
            #[\Livewire\Features\SupportReactiveProps\Prop]
            public $count;
            public function mount() { sleep(1); }
            public function render() { return <<<'HTML'
            <div id="child">
                Count: {{ $count }}
            </div>
            HTML; }
        }])->tinker()
        ->waitFor('#child')
        ->assertSee('Count: 1')
        ->waitForLivewire()->click('@button')
        ->assertSee('Count: 2')
        ;
    }
}
