<?php

namespace LegacyTests\Browser\Extensions;

use Laravel\Dusk\Browser;
use Livewire\Livewire;
use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, Component::class)
                ->tap(function (Browser $browser) {
                    $browser->script([
                        'window.renameMe = false',
                        "window.Livewire.directive('foo', (el, directive, component) => {
                            window.renameMe = true
                        })",
                    ]);
                })
                ->assertScript('window.renameMe', false)
                ->waitForLivewire()->click('@refresh')
                ->assertScript('window.renameMe', true)
            ;
        });
    }
}
