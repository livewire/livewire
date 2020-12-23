<?php

namespace Tests\Browser\Hooks;

use Livewire\Livewire;
use Laravel\Dusk\Browser;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function (Browser $browser) {
            Livewire::visit($browser, Component::class)
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
