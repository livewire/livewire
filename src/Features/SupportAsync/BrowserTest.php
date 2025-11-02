<?php

namespace Livewire\Features\SupportAsync;

use Livewire\Attributes\Async;
use Tests\BrowserTestCase;
use Livewire\Livewire;
use Livewire\Component;
use Livewire\Attributes\On;

class BrowserTest extends BrowserTestCase
{
    public function test_parallel_async_requests_are_not_bundled()
    {
        Livewire::visit([new class extends Component {
            public function render() { return <<<'HTML'
            <div>
                <livewire:child />
                <livewire:child-async />

                <button x-on:click="$dispatch('trigger')" dusk="trigger">Dispatch trigger</button>
            </div>
            HTML; }
        }, 'child' => new class extends Component {
            public $time;
            public function mount() {
                $this->time = LARAVEL_START;
            }
            public function react() {
                $this->time = LARAVEL_START;
            }
            public function render() { return <<<'HTML'
            <div x-on:trigger.window="$wire.react()">
                Child 1

                <span dusk="time-1">{{ $time }}</span>
            </div>
            HTML; }
        }, 'child-async' => new class extends Component {
            public $time;
            public function mount() {
                $this->time = LARAVEL_START;
            }
            #[Async]
            public function react() {
                $this->time = LARAVEL_START;
            }
            public function render() { return <<<'HTML'
            <div x-on:trigger.window="$wire.react()">
                Child 2

                <span dusk="time-2">{{ $time }}</span>
            </div>
            HTML; }
        },])
        ->waitForText('Child 1')
        ->waitForText('Child 2')
        ->tap(function ($b) {
            $time1 = (float) $b->text('@time-1');
            $time2 = (float) $b->text('@time-2');

            $this->assertEquals($time1, $time2);
        })
        ->waitForLivewire()->click('@trigger')
        ->tap(function ($b) {
            $time1 = (float) $b->waitFor('@time-1')->text('@time-1');
            $time2 = (float) $b->waitFor('@time-2')->text('@time-2');

            $this->assertNotEquals($time1, $time2);
        })
        ;
    }
}
