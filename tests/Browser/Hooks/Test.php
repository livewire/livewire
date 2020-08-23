<?php

namespace Tests\Browser\Hooks;

use Livewire\Livewire;
use Tests\Browser\TestCase;
use Tests\Browser\Hooks\Component;
use Laravel\Dusk\Browser;

class Test extends TestCase
{
    /** @test */
    public function happy_path()
    {
        $this->browse(function (Browser $browser) {
            Livewire::visit($browser, Component::class)
                ->tap(function (\Laravel\Dusk\Browser $browser) {
                    $browser->script(["window.livewire.hook('beforeDomUpdate', () => {
                        document.getElementById('output-before').value = 'bar';
                    })",
                    "window.livewire.hook('afterDomUpdate', () => {
                        document.getElementById('output-after').value = 'baz';
                    })"]);
                    $this->assertEquals('', $browser->value('@output.before'));
                    $this->assertEquals('', $browser->value('@output.after'));
                    $browser->click('@button');
                    $browser->pause(35);
                    $this->assertEquals('bar', $browser->value('@output.before'));
                    $this->assertEquals('baz', $browser->value('@output.after'));
                });
        });
    }
}
