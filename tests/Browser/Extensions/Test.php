<?php

namespace Tests\Browser\Extensions;

use Laravel\Dusk\Browser;
use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                ->tap(function (Browser $browser) {
                    $browser->script('window.renameMe = false');
                    $browser->script([
                        "window.livewire.directive('foo', (el, directive, component) => {
                            window.renameMe = true
                        })",
                    ]);
                    $this->assertTrue($browser->driver->executeScript('return window.renameMe === false'));
                    $browser->waitForLivewire()->click('@refresh');
                    $this->assertTrue($browser->driver->executeScript('return window.renameMe === true'));
                })
            ;
        });
    }
}
