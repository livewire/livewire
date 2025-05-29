<?php

namespace LegacyTests\Browser\Hooks;

use Laravel\Dusk\Browser;
use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->markTestSkipped(); // @todo: Caleb needs to think more deeply about JS hooks for V3...

        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, Component::class)
                ->tap(function ($b) {
                    $b->script([
                        "window.livewire.hook('message.received', () => {
                            document.querySelector('[dusk=\"output\"]').value = 'before';
                        })",
                        "window.livewire.hook('message.processed', () => {
                            document.querySelector('[dusk=\"output\"]').value += '_after';
                        })",
                    ]);
                })
                ->tap(function ($b) { $this->assertEquals('', $b->value('@output')); })
                ->waitForLivewire()->click('@button')
                ->tap(function ($b) { $this->assertEquals('before_after', $b->value('@output')); })
            ;
        });
    }
}
