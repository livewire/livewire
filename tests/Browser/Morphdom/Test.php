<?php

namespace Tests\Browser\Morphdom;

use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                /**
                 * element root is DOM diffed
                 */
                ->assertAttributeMissing('@root', 'foo')
                ->waitForLivewire()->click('@foo')
                ->assertAttribute('@root', 'foo', 'true')

                /**
                 * element inserted in the middle moves subsequent elements instead of removing them
                 */
                ->tap(function ($b) { $b->script([
                    "window.elementWasRemoved = false",
                    "Livewire.hook('element.removed', () => { window.elementWasRemoved = true })",
                ]);})
                ->waitForLivewire()->click('@bar')
                ->tap(function ($b) {
                    $this->assertEquals([false], $b->script('return window.elementWasRemoved'));
                })

                /**
                 * element inserted before element with same tag name is handled as if they were different.
                 */
                ->tap(function ($b) { $b->script([
                    "window.lastAddedElement = false",
                    "Livewire.hook('element.initialized', el => { window.lastAddedElement = el })",
                ]);})
                ->waitForLivewire()->click('@baz')
                ->tap(function ($b) {
                    $this->assertEquals(['second'], $b->script('return window.lastAddedElement.innerText'));
                })

                /**
                 * elements added with keys are recognized in the custom lookahead
                 */
                ->waitForLivewire()->click('@baz')
                ->tap(function ($b) {
                    $this->assertEquals([1], $b->script('return Livewire.components.components()[0].morphChanges.added.length'));
                    $this->assertEquals([0], $b->script('return Livewire.components.components()[0].morphChanges.removed.length'));
                })
                ;
        });
    }
}
