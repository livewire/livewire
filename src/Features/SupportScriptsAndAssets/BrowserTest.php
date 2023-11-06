<?php

namespace Livewire\Features\SupportScriptsAndAssets;

use Livewire\Livewire;
use Livewire\Drawer\Utils;
use Illuminate\Support\Facades\Route;

class BrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    public function can_evaluate_a_script_inside_a_component()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public $message = 'original';

            public function render() { return <<<'HTML'
            <div>
                <h1 dusk="foo"></h1>
                <h2 dusk="bar" x-text="$wire.message"></h2>
            </div>

            @script
            <script>
                document.querySelector('[dusk="foo"]').textContent = 'evaluated'
                $wire.message = 'changed'
            </script>
            @endscript
            HTML; }
        })
        ->waitForText('evaluated')
        ->assertSeeIn('@foo', 'evaluated')
        ->assertSeeIn('@bar', 'changed')
        ;
    }

    /** @test */
    public function can_register_an_alpine_component_inside_a_script_tag()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public $message = 'original';

            public function render() { return <<<'HTML'
            <div>
                <h1 dusk="foo" x-dusk-test></h1>
            </div>

            @script
            <script>
                Alpine.directive('dusk-test', (el) => {
                    el.textContent = 'evaluated'
                })
            </script>
            @endscript
            HTML; }
        })
        ->waitForText('evaluated')
        ->assertSeeIn('@foo', 'evaluated')
        ;
    }

    /** @test */
    public function multiple_scripts_can_be_evaluated()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public function render() { return <<<'HTML'
            <div>
                <h1 dusk="foo"></h1>
                <h2 dusk="bar"></h2>
            </div>

            @script
            <script>
                document.querySelector('[dusk="foo"]').textContent = 'evaluated-first'
            </script>
            @endscript
            @script
            <script>
                document.querySelector('[dusk="bar"]').textContent = 'evaluated-second'
            </script>
            @endscript
            HTML; }
        })
        ->waitForText('evaluated-first')
        ->assertSeeIn('@foo', 'evaluated-first')
        ->assertSeeIn('@bar', 'evaluated-second')
        ;
    }

    /** @test */
    public function scripts_can_be_added_conditionally()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public $show = false;

            public function render() { return <<<'HTML'
            <div>
                <button dusk="button" wire:click="$set('show', true)">refresh</button>
                <h1 dusk="foo" wire:ignore></h1>
            </div>

            @if($show)
                @script
                <script>
                    document.querySelector('[dusk="foo"]').textContent = 'evaluated-second'
                </script>
                @endscript
            @endif

            @script
            <script>
                document.querySelector('[dusk="foo"]').textContent = 'evaluated-first'
            </script>
            @endscript
            HTML; }
        })
        ->assertSeeIn('@foo', 'evaluated-first')
        ->waitForLivewire()->click('@button')
        ->assertSeeIn('@foo', 'evaluated-second')
        ;
    }

    /** @test */
    public function assets_can_be_loaded()
    {
        Route::get('/test.js', function () {
            return Utils::pretendResponseIsFile(__DIR__.'/test.js');
        });

        Livewire::visit(new class extends \Livewire\Component {
            public function render() { return <<<'HTML'
            <div>
                <h1 dusk="foo" wire:ignore></h1>
            </div>

            @assets
            <script src="/test.js"></script>
            @endassets
            HTML; }
        })
        ->assertSeeIn('@foo', 'evaluated')
        ;
    }
}
