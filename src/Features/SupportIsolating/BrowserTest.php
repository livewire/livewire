<?php

namespace Livewire\Features\SupportIsolating;

use Tests\BrowserTestCase;
use Livewire\Livewire;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Isolate;

class BrowserTest extends BrowserTestCase
{
    public function test_components_can_be_marked_as_isolated()
    {
        Livewire::visit([new class extends Component {
            public function render() { return <<<HTML
            <div>
                <livewire:child num="1" />
                <livewire:child num="2" />
                <livewire:child num="3" />

                <button wire:click="\$dispatch('trigger')" dusk="trigger">Dispatch trigger</button>
            </div>
            HTML; }
        }, 'child' => new #[Isolate] class extends Component {
            public $num;
            public $time;
            public function mount() {
                $this->time = LARAVEL_START;
            }
            #[On('trigger')]
            public function react() {
                $this->time = LARAVEL_START;
            }
            public function render() { return <<<'HTML'
            <div id="child">
                Child {{ $num }}

                <span dusk="time-{{ $num }}">{{ $time }}</span>
            </div>
            HTML; }
        }])
        ->waitForText('Child 1')
        ->waitForText('Child 2')
        ->waitForText('Child 3')
        ->tap(function ($b) {
            $time1 = (float) $b->text('@time-1');
            $time2 = (float) $b->text('@time-2');
            $time3 = (float) $b->text('@time-3');

            $this->assertEquals($time1, $time2);
            $this->assertEquals($time2, $time3);
        })
        ->waitForLivewire()->click('@trigger')
        ->tap(function ($b) {
            $time1 = (float) $b->waitFor('@time-1')->text('@time-1');
            $time2 = (float) $b->waitFor('@time-2')->text('@time-2');
            $time3 = (float) $b->waitFor('@time-3')->text('@time-3');

            $this->assertNotEquals($time1, $time2);
            $this->assertNotEquals($time2, $time3);
        })
        ;
    }

    public function test_lazy_requests_are_isolated_by_default()
    {
        Livewire::visit([new class extends Component {
            public function render() { return <<<HTML
            <div>
                <livewire:child num="1" />
                <livewire:child num="2" />
                <livewire:child num="3" />
            </div>
            HTML; }
        }, 'child' => new #[\Livewire\Attributes\Lazy] class extends Component {
            public $num;
            public $time;
            public function mount() {
                $this->time = LARAVEL_START;
            }
            public function render() { return <<<'HTML'
            <div id="child">
                Child {{ $num }}

                <span dusk="time-{{ $num }}">{{ $time }}</span>
            </div>
            HTML; }
        }])
        ->waitForText('Child 1')
        ->waitForText('Child 2')
        ->waitForText('Child 3')
        ->tap(function ($b) {
            $time1 = (float) $b->text('@time-1');
            $time2 = (float) $b->text('@time-2');
            $time3 = (float) $b->text('@time-3');

            $this->assertNotEquals($time1, $time2);
            $this->assertNotEquals($time2, $time3);
        })
        ;
    }

    public function test_lazy_requests_are_isolated_by_default_but_bundled_on_next_request_when_polling()
    {
        Livewire::visit([new class extends Component {
            public function render() { return <<<HTML
            <div>
                <livewire:child num="1" />
                <livewire:child num="2" />
                <livewire:child num="3" />
            </div>
            HTML; }
        }, 'child' => new #[\Livewire\Attributes\Lazy] class extends Component {
            public $num;
            public $time;
            public function boot()
            {
                $this->time = LARAVEL_START;
            }

            public function render() { return <<<'HTML'
            <div wire:poll.500ms id="child">
                Child {{ $num }}

                <span dusk="time-{{ $num }}">{{ $time }}</span>
            </div>
            HTML; }
        }])
        ->waitForText('Child 1')
        ->waitForText('Child 2')
        ->waitForText('Child 3')
        ->tap(function ($b) {
            $time1 = (float) $b->text('@time-1');
            $time2 = (float) $b->text('@time-2');
            $time3 = (float) $b->text('@time-3');

            $this->assertNotEquals($time1, $time2);
            $this->assertNotEquals($time2, $time3);
        })
        // Wait for a poll to have happened
        ->pause(500)
        ->tap(function ($b) {
            $time1 = (float) $b->text('@time-1');
            $time2 = (float) $b->text('@time-2');
            $time3 = (float) $b->text('@time-3');

            // Times should now all be equal
            $this->assertEquals($time1, $time2);
            $this->assertEquals($time2, $time3);
        })
        ;
    }

    public function test_lazy_requests_can_be_bundled_with_attribute_parameter()
    {
        Livewire::visit([new class extends Component {
            public function render() { return <<<HTML
            <div>
                <livewire:child num="1" />
                <livewire:child num="2" />
                <livewire:child num="3" />
            </div>
            HTML; }
        }, 'child' => new #[\Livewire\Attributes\Lazy(isolate: false)] class extends Component {
            public $num;
            public $time;
            public function mount() {
                $this->time = LARAVEL_START;
            }
            public function render() { return <<<'HTML'
            <div id="child">
                Child {{ $num }}

                <span dusk="time-{{ $num }}">{{ $time }}</span>
            </div>
            HTML; }
        }])
        ->waitForText('Child 1')
        ->waitForText('Child 2')
        ->waitForText('Child 3')
        ->tap(function ($b) {
            $time1 = (float) $b->text('@time-1');
            $time2 = (float) $b->text('@time-2');
            $time3 = (float) $b->text('@time-3');

            $this->assertEquals($time1, $time2);
            $this->assertEquals($time2, $time3);
        })
        ;
    }
}
