<?php

namespace LegacyTests\Browser\ScriptTag;

use Livewire\Livewire;
use LegacyTests\Browser\TestCase;

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
