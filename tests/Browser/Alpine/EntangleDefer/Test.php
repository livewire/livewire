<?php

namespace Tests\Browser\Alpine\EntangleDefer;

use Livewire\Livewire;
use Tests\Browser\TestCase;
use Tests\Browser\Alpine\EntangleDefer\Component;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                /**
                 * Can defer multiple entangle changes until next server request
                 */
                ->assertSeeIn('@output.alpine', 'false')
                ->assertSeeIn('@output.livewire', 'false')
                ->click('@toggle')
                ->pause(150)
                ->assertSeeIn('@output.alpine', 'true')
                ->assertSeeIn('@output.livewire', 'false')
                ->click('@toggle')
                ->pause(150)
                ->assertSeeIn('@output.alpine', 'false')
                ->assertSeeIn('@output.livewire', 'false')
                ->waitForLivewire()->click('@refresh')
                ->assertSeeIn('@output.alpine', 'false')
                ->assertSeeIn('@output.livewire', 'false')
            ;
        });
    }
}
