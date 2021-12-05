<?php

namespace Tests\Browser\Morphdom;

use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class, '?showChild=false')
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
                ->assertScript('window.elementWasRemoved', false)

                /**
                 * element inserted before element with same tag name is handled as if they were different.
                 */
                ->tap(function ($b) { $b->script([
                    "window.lastAddedElement = false",
                    "Livewire.hook('element.initialized', el => { window.lastAddedElement = el })",
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
                    "window.lastAddedElement = false",
                    "window.lastUpdatedElement = false",
                    "Livewire.hook('element.updated', el => { window.lastUpdatedElement = el })",
                ]);})
                ->waitForLivewire()->click('@qux')
                ->assertScript('window.lastAddedElement.innerText', 'second')
                ->assertScript('window.lastUpdatedElement.innerText', 'third')
            ;
        });
    }

    /** @test */
    public function it_keeps_wire_end_comments_of_children_if_parent_updates()
    {    
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class, '?showChild=true')
            ->tap(function ($b) { 
                $parentId = $b->script('return document.querySelector("[dusk=\'root\']").getAttribute("wire:id")')[0];
                $child1Id = $b->script('return document.querySelectorAll("[dusk=\'child\']")[0].getAttribute("wire:id")')[0];
                $child2Id = $b->script('return document.querySelectorAll("[dusk=\'child\']")[1].getAttribute("wire:id")')[0];

                $b->assertSourceHas('<!--[if false]><![endif] Livewire Component wire-end:'.$parentId.' -->');
                $b->assertSourceHas('<!--[if false]><![endif] Livewire Component wire-end:'.$child1Id.' -->');
                $b->assertSourceHas('<!--[if false]><![endif] Livewire Component wire-end:'.$child2Id.' -->');

            })
            /**
             * Update parent element
             */
            ->waitForLivewire()->click('@foo')
            /**
             * Validate that children comments are still there
             */
            ->tap(function ($b) { 
                $parentId = $b->script('return document.querySelector("[dusk=\'root\']").getAttribute("wire:id")')[0];
                $child1Id = $b->script('return document.querySelectorAll("[dusk=\'child\']")[0].getAttribute("wire:id")')[0];
                $child2Id = $b->script('return document.querySelectorAll("[dusk=\'child\']")[1].getAttribute("wire:id")')[0];

                $b->assertSourceHas('<!--[if false]><![endif] Livewire Component wire-end:'.$parentId.' -->');
                $b->assertSourceHas('<!--[if false]><![endif] Livewire Component wire-end:'.$child1Id.' -->');
                $b->assertSourceHas('<!--[if false]><![endif] Livewire Component wire-end:'.$child2Id.' -->');
            });
        });
    }
}
