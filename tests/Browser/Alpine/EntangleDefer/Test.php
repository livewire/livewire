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
                ->type('@input', 's')
                ->waitForLivewire()->click('@submit')
                ->assertSeeIn('@output.alpine', 's')
                ->assertSeeIn('@output.livewire', 's')
                ->append('@input', 's')
                ->waitForLivewire()->click('@submit')
                ->assertSeeIn('@output.alpine', 'ss')
                ->assertSeeIn('@output.livewire', 'ss')
            ;
        });
    }
}
