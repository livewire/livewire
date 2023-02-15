<?php

namespace Livewire\Features\SupportReactiveProps;

use Livewire\Livewire;
use Livewire\Component;

class Test extends \Tests\DuskTestCase
{
    /** @test */
    public function can_()
    {
        $this->dusk(new class extends Component {
            public $count = 0;

            public function inc() { $this->count++; }

            public function render() {
                return '<h1>Hello world! {{ $count }} <button wire:click="inc">inc</button></h1>';
            }
        })->assertSee('Hello world!');
    }

    public function dusk($component) {
        if (config('something')) {
            throw new class ($component) extends \Exception {
                public $component;
                public $isDuskShortcircuit = true;
                public function __construct($component) {
                    $this->component = $component;
                }
            };
        }

        $trace = debug_backtrace(options: DEBUG_BACKTRACE_IGNORE_ARGS, limit: 2);

        $class = $trace[1]['class'];
        $method = $trace[1]['function'];

        $url = '/foo?test='.base64_encode($class.':'.$method);

        $rescue = null;

        $browser = $this->newBrowser($this->createWebDriver());

        return $browser->visit($url);
    }
}
