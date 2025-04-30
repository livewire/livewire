<?php

namespace Livewire\Features\SupportAccessingParent;

use LegacyTests\Browser\TestCase;
use Livewire\Component;

class BrowserTest extends TestCase
{
    public function test_can_access_parent()
    {
        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, [ParentCounter::class, 'child-counter' => ChildCounter::class])
                ->assertSeeIn('@output', '1')
                ->waitForLivewire()->click('@button')
                ->waitForTextIn('@output', '2')
                ->assertSeeIn('@output', '2')
            ;
        });
    }
}

class ParentCounter extends Component
{
    public $count = 1;

    function increment()
    {
        $this->count++;
    }

    public function render()
    {
        return <<<'HTML'
        <div>
           <span dusk="output">{{ $count }}</span>

            <livewire:child-counter />
        </div>
        HTML;
    }
}

class ChildCounter extends Component
{
    public function render()
    {
        return <<<'HTML'
        <div>
            <button wire:click="$parent.increment()" dusk="button"></button>
        </div>
        HTML;
    }
}
