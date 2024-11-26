<?php

namespace LegacyTests\Browser\Events;

use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, [Component::class, 'component-a' => NestedComponentA::class, 'component-b' => NestedComponentB::class])
                /**
                 * receive event from global fire
                 */
                ->waitForLivewire()->tap(function ($browser) { $browser->script('window.Livewire.dispatch("foo", { value: "bar" })'); })
                ->waitUsing(5, 75, function () use ($browser) {
                    return $browser->assertSeeIn('@lastEventForParent', 'bar')
                             ->assertSeeIn('@lastEventForChildA', 'bar')
                             ->assertSeeIn('@lastEventForChildB', 'bar');
                })

                /**
                 * receive event from action fire
                 */
                ->waitForLivewire()->click('@dispatch.baz')
                ->waitUsing(5, 75, function () use ($browser) {
                    return $browser->assertSeeIn('@lastEventForParent', 'baz')
                                   ->assertSeeIn('@lastEventForChildA', 'baz')
                                   ->assertSeeIn('@lastEventForChildB', 'baz');
                })

                /**
                 * receive event from component fire, and make sure global listener receives event too
                 */
                ->tap(function ($b) { $b->script([
                    "window.lastFooEventValue = ''",
                    "window.Livewire.on('foo', ({ value }) => { lastFooEventValue = value })",
                ]);})
                ->waitForLivewire()->click('@dispatch.bob')
                ->waitUsing(5, 75, function () use ($browser) {
                    return $browser->assertScript('window.lastFooEventValue', 'bob');
                })

                /**
                 * receive event from action fired only to ancestors, and make sure global listener doesnt receive it
                 */
                ->waitForLivewire()->click('@dispatch.law')
                ->waitUsing(5, 75, function () use ($browser) {
                    return $browser->assertSeeIn('@lastEventForParent', 'law')
                                   ->assertSeeIn('@lastEventForChildA', 'bob')
                                   ->assertSeeIn('@lastEventForChildB', 'bob')
                                   ->assertScript('window.lastFooEventValue', 'bob');
                })

                /**
                 * receive event from action fired only to component name, and make sure global listener doesnt receive it
                 */
                ->waitForLivewire()->click('@dispatch.blog')
                ->waitUsing(5, 75, function () use ($browser) {
                    return $browser->assertSeeIn('@lastEventForParent', 'law')
                                   ->assertSeeIn('@lastEventForChildA', 'bob')
                                   ->assertSeeIn('@lastEventForChildB', 'blog')
                                   ->assertScript('window.lastFooEventValue', 'bob');
                })
            ;
        });
    }
}
