<?php

namespace Tests\Browser\ScriptTag;

use Laravel\Dusk\Browser;
use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{

    public function test()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                ->assertScript('window.scriptTagWasCalled === undefined')
                ->waitForLivewire()->click('@button')
                ->assertScript('window.scriptTagWasCalled === true')
            ;
        });
    }
}
