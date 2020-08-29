<?php

namespace Tests\Browser\Hooks;

use Livewire\Livewire;
use Tests\Browser\TestCase;
use Laravel\Dusk\Browser;

class Test extends TestCase
{
    /** @test */
    public function happy_path()
    {
        $this->browse(function (Browser $browser) {
            Livewire::visit($browser, Component::class)
                ->tap(function (Browser $browser) {
                    $browser->script(["window.livewire.hook('beforeDomUpdate', () => {
                        document.getElementById('output').value = 'before';
                    })",
                    "window.livewire.hook('afterDomUpdate', () => {
                        document.getElementById('output').value += '_after';
                    })"]);
                    $this->assertEquals('', $browser->value('@output'));
                    $browser->click('@button')->waitForLivewire();
                    $this->assertEquals('before_after', $browser->value('@output'));
                });
        });
    }
}
