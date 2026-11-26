<?php

namespace Livewire\Features\SupportPolling;

use Tests\BrowserTestCase;
use Livewire\Livewire;
use Livewire\Component;

class BrowserTest extends BrowserTestCase
{
    public function test_cancelled_poll_does_not_trigger_an_unhandled_promise_rejection()
    {
        Livewire::visit(new class extends Component {
            public function render() {
                usleep(3 * 1000 * 1000);

                return <<<'HTML'
                <div wire:poll>
                    Polling
                </div>
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->pause(4500)
            ->assertConsoleLogHasNoErrors()
            ;
    }

    public function test_poll_duration_can_be_in_minutes()
    {
        Livewire::visit(new class extends Component {
            public $pollCount = 0;
            public $polling = false;

            public function startPolling()
            {
                $this->polling = true;
            }

            public function poll()
            {
                $this->pollCount++;
            }

            public function render() { return <<<'HTML'
            <div>
                <button wire:click="startPolling" dusk="start-polling">Start polling</button>
                <span dusk="poll-count">{{ $pollCount }}</span>

                @if ($polling)
                    <div wire:poll.1m="poll"></div>
                @endif
            </div>
            HTML; }
        })
        ->tap(fn ($b) => $b->script(<<<'JS'
            window.originalSetInterval = window.setInterval
            window.setInterval = (callback, duration) => {
                window.pollDuration = duration
                window.setInterval = window.originalSetInterval

                return window.setTimeout(callback)
            }
            JS))
        ->waitForLivewire()->click('@start-polling')
        ->assertScript('window.pollDuration', 60000)
        ->waitForTextIn('@poll-count', '1')
        ;
    }

    public function test_polling_requests_are_batched_by_default()
    {
        Livewire::visit([new class extends Component {
            public function render() { return <<<HTML
            <div>
                <livewire:child num="1" />
                <livewire:child num="2" />
                <livewire:child num="3" />
            </div>
            HTML; }
        }, 'child' => new class extends Component {
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

            // Times should all be equal
            $this->assertEquals($time1, $time2);
            $this->assertEquals($time2, $time3);
        })
        // Wait for a poll to have happened
        ->pause(500)
        ->tap(function ($b) {
            $time1 = (float) $b->text('@time-1');
            $time2 = (float) $b->text('@time-2');
            $time3 = (float) $b->text('@time-3');

            // Times should all be equal
            $this->assertEquals($time1, $time2);
            $this->assertEquals($time2, $time3);
        })
        ;
    }

    public function test_changing_the_poll_duration_stops_the_previous_poll()
    {
        Livewire::visit(new class extends Component {
            public $pollCount = 0;
            public $slow = false;

            public function slowDown()
            {
                $this->slow = true;
            }

            public function poll()
            {
                $this->pollCount++;
            }

            public function render() { return <<<'HTML'
            <div>
                <button wire:click="slowDown" dusk="slow-down">Slow down</button>
                <span dusk="poll-count">{{ $pollCount }}</span>

                <div dusk="poller" @if ($slow) wire:poll.10s="poll" @else wire:poll.250ms="poll" @endif></div>
            </div>
            HTML; }
        })
        ->waitForTextIn('@poll-count', '2')
        ->waitForLivewire()->click('@slow-down')
        ->assertAttribute('@poller', 'wire:poll.10s', 'poll')
        ->tap(function ($b) {
            $pollCount = $b->text('@poll-count');

            $b->pause(1000)->assertSeeIn('@poll-count', $pollCount);
        })
        ;
    }

    public function test_backoff_slows_a_poll_down_while_nothing_changes()
    {
        Livewire::visit(new class extends Component {
            public function render() { return <<<'HTML'
            <div wire:poll.200ms.backoff>
                Nothing new
            </div>

            @script
            <script>
                window.requestCount = 0

                this.interceptMessage(({ onSend }) => onSend(() => window.requestCount++))
            </script>
            @endscript
            HTML; }
        })
        ->waitForLivewireToLoad()
        ->pause(3000)
        ->tap(function ($b) {
            // Every 200ms is 15 polls in three seconds. Backing off, they go out at 0.2s, 0.4s, 0.8s, and 1.6s...
            $requestCount = $b->script('return window.requestCount')[0];

            $this->assertGreaterThanOrEqual(3, $requestCount);
            $this->assertLessThan(8, $requestCount);
        })
        ;
    }

    public function test_backoff_returns_to_the_interval_when_a_poll_brings_something_new()
    {
        Livewire::visit(new class extends Component {
            public $newsArrivesAt;

            public function mount()
            {
                $this->newsArrivesAt = microtime(true) + 2.5;
            }

            public function render() { return <<<'HTML'
            <div wire:poll.200ms.backoff dusk="news">
                {{ microtime(true) < $newsArrivesAt ? 'Nothing new' : 'Something new' }}
            </div>

            @script
            <script>
                window.requestCount = 0

                this.interceptMessage(({ onSend }) => onSend(() => window.requestCount++))
            </script>
            @endscript
            HTML; }
        })
        ->waitForTextIn('@news', 'Something new')
        ->tap(function ($b) {
            $requestCount = $b->script('return window.requestCount')[0];

            $this->assertLessThan(8, $requestCount);

            // Back at 200ms, at least two polls go out within 1.5 seconds. Still backed off, at most one would...
            $b->pause(1500);

            $this->assertGreaterThanOrEqual($requestCount + 2, $b->script('return window.requestCount')[0]);
        })
        ;
    }

    public function test_backoff_returns_to_the_interval_when_the_component_is_used()
    {
        Livewire::visit(new class extends Component {
            public function render() { return <<<'HTML'
            <div wire:poll.200ms.backoff>
                <button wire:click="$refresh" dusk="refresh">Refresh</button>
            </div>

            @script
            <script>
                window.requestCount = 0

                this.interceptMessage(({ onSend }) => onSend(() => window.requestCount++))
            </script>
            @endscript
            HTML; }
        })
        ->waitForLivewireToLoad()
        ->pause(2500)
        ->tap(fn ($b) => $this->assertLessThan(8, $b->script('return window.requestCount')[0]))
        ->waitForLivewire()->click('@refresh')
        ->tap(function ($b) {
            $requestCount = $b->script('return window.requestCount')[0];

            // Back at 200ms, at least two polls go out within 1.5 seconds. Still backed off, at most one would...
            $b->pause(1500);

            $this->assertGreaterThanOrEqual($requestCount + 2, $b->script('return window.requestCount')[0]);
        })
        ;
    }

    public function test_backoff_slows_a_failing_poll_down()
    {
        Livewire::visit(new class extends Component {
            public function check()
            {
                throw new \Exception('The server is down');
            }

            public function render() { return <<<'HTML'
            <div wire:poll.200ms.backoff="check"></div>

            @script
            <script>
                window.requestCount = 0

                this.interceptMessage(({ onSend }) => onSend(() => window.requestCount++))
            </script>
            @endscript
            HTML; }
        })
        ->waitForLivewireToLoad()
        ->pause(3000)
        ->tap(function ($b) {
            // Every 200ms is 15 polls in three seconds. Backing off, they go out at 0.2s, 0.6s, 1.4s, and 3s...
            $requestCount = $b->script('return window.requestCount')[0];

            $this->assertGreaterThanOrEqual(3, $requestCount);
            $this->assertLessThan(8, $requestCount);
        })
        ;
    }

    public function test_the_duration_after_backoff_is_the_limit_and_not_the_interval()
    {
        Livewire::visit(new class extends Component {
            public $polling = false;

            public function startPolling()
            {
                $this->polling = true;
            }

            public function render() { return <<<'HTML'
            <div>
                <button wire:click="startPolling" dusk="start-polling">Start polling</button>

                @if ($polling)
                    <div wire:poll.backoff.1m></div>
                @endif
            </div>
            HTML; }
        })
        ->tap(fn ($b) => $b->script(<<<'JS'
            window.originalSetInterval = window.setInterval
            window.setInterval = (callback, duration) => {
                window.pollDuration = duration
                window.setInterval = window.originalSetInterval

                return window.originalSetInterval(callback, duration)
            }
            JS))
        ->waitForLivewire()->click('@start-polling')
        ->assertScript('window.pollDuration', 2000)
        ;
    }
}
