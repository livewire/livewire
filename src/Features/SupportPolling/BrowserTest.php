<?php

namespace Livewire\Features\SupportPolling;

use Tests\BrowserTestCase;
use Livewire\Livewire;
use Livewire\Component;

class BrowserTest extends BrowserTestCase
{
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

}
