<?php

namespace Livewire\Features\SupportBladeAttributes;

use Livewire\Livewire;
use Livewire\Component;

class BrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    function can_use_wire_poll()
    {
        $initialVisit = now();

        Livewire::visit([
            Page::class,
        ])
            ->assertSee($initialVisit)
            ->pause(1000)
            ->assertSee($initialVisit->addSecond(1))
            ;
    }
}

class Page extends Component
{
    public function render()
    {
        return <<<HTML
        <div wire:poll.1000ms>
            <div>{{ now() }}</div>
        </div>
        HTML;
    }
}
