<?php

namespace Tests\Browser\GlobalLivewire;

use Livewire\Livewire;
use Tests\Browser\TestCase;
use Tests\Browser\GlobalLivewire\Component;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                /**
                 * Event listeners are removed on teardown.
                 **/
                ->pause(250)
                ->tap(function ($b) { $b->script('window.livewire.stop()'); })
                ->click('@foo')
                ->pause(100)
                ->assertDontSeeIn('@output', 'foo')
                ->refresh()

                /**
                 * New components are discovered in the dom with rescan.
                 **/
                ->tap(function ($b) { $this->assertEquals([1], $b->script("return livewire.components.components().length")); })
                ->tap(function ($b) { $b->script("window.node = document.querySelector('div')"); })
                ->tap(function ($b) { $b->script("window.newNode = window.node.cloneNode()"); })
                ->tap(function ($b) { $b->script("window.newNode.setAttribute('wire:id', 'foo')"); })
                ->tap(function ($b) { $b->script("window.node.parentElement.appendChild(window.newNode)"); })
                ->tap(function ($b) { $b->script("livewire.rescan()"); })
                ->tap(function ($b) { $this->assertEquals([2], $b->script("return livewire.components.components().length")); })
                ->refresh()

                /**
                 * Rescanned components dont register twice.
                 **/
                ->tap(function ($b) { $b->script("livewire.rescan()"); })
                ->waitForLivewire()->click('@foo')
                ->assertSeeIn('@output', 'foo')
            ;
        });
    }
}
