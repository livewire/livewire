<?php

namespace LegacyTests\Browser\ScriptTag;

use LegacyTests\Browser\TestCase;

class Test extends TestCase
{

    public function test()
    {
        $this->markTestSkipped(); // @todo: should we support this in V3?

        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, Component::class)
                ->assertScript('window.scriptTagWasCalled === undefined')
                ->waitForLivewire()->click('@button')
                ->assertScript('window.scriptTagWasCalled === true')
            ;
        });
    }
}
