<?php

namespace Livewire\Features\SupportCSP;

use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            config(['livewire.csp_safe' => true]);
        };
    }

    public function test_can_use_csp_safe_version()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public function render() { return <<<'HTML'
                <div>
                    <button wire:click="$refresh" dusk="refresh">Refresh</button>

                    Now: {{ now() }}
                </div>
                HTML; }
            }
        ])
            ->waitForLivewireToLoad()
            ->tinker()
            ;
    }
}
