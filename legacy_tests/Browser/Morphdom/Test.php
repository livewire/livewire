<?php

namespace LegacyTests\Browser\Morphdom;

use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->markTestSkipped(); // @todo: not settled on V3 hooks yet...

        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, Component::class)
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
                    'window.elementWasRemoved = false',
                    "Livewire.hook('element.removed', () => { window.elementWasRemoved = true })",
                ]);})
                ->waitForLivewire()->click('@bar')
                ->assertScript('window.elementWasRemoved', false)

                /**
                 * element inserted before element with same tag name is handled as if they were different.
                 */
                ->tap(function ($b) { $b->script([
                    'window.lastAddedElement = false',
                    "Livewire.hook('element.init', ({ el )} => { window.lastAddedElement = el })",
                ]);})
                ->waitForLivewire()->click('@baz')
                ->assertScript('window.lastAddedElement.innerText', 'second')

                /**
                 * elements added with keys are recognized in the custom lookahead
                 */
                ->waitForLivewire()->click('@bob')
                ->assertScript('Livewire.components.components()[0].morphChanges.added.length', 1)
                ->assertScript('Livewire.components.components()[0].morphChanges.removed.length', 0)


                ->tap(function ($b) { $b->script([
                    'window.lastAddedElement = false',
                    'window.lastUpdatedElement = false',
                    "Livewire.hook('element.updated', el => { window.lastUpdatedElement = el })",
                ]);})
                ->waitForLivewire()->click('@qux')
                ->assertScript('window.lastAddedElement.innerText', 'second')
                ->assertScript('window.lastUpdatedElement.innerText', 'third')
            ;
        });
    }
}
