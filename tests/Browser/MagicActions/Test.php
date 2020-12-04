<?php

namespace Tests\Browser\MagicActions;

use Livewire\Livewire;
use Tests\Browser\TestCase;
use Tests\Browser\MagicActions\Component;

class Test extends TestCase
{
    public function test_magic_toggle_can_toggle_nested()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                ->assertSeeIn('@output', 'false')
                ->waitForLivewire()->click('@toggle')
                ->assertSeeIn('@output', 'true')
            ;
        });
    }
}
