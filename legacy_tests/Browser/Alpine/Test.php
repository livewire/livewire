<?php

namespace LegacyTests\Browser\Alpine;

use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, Component::class)
                /**
                 * ->dispatchBrowserEvent()
                 */
                ->assertDontSeeIn('@foo.output', 'bar')
                ->waitForLivewire()->click('@foo.button')
                ->assertSeeIn('@foo.output', 'bar')

                /**
                 * Basic counter Alpine component.
                 */
                ->assertSeeIn('@bar.output', '0')
                ->click('@bar.button')
                ->assertSeeIn('@bar.output', '1')
                ->waitForLivewire()->click('@bar.refresh')
                ->assertSeeIn('@bar.output', '1')

                /**
                 * get, set, and call to Livewire from Alpine.
                 */
                ->assertSeeIn('@baz.output', '0')
                ->assertSeeIn('@baz.get', '0')
                ->assertSeeIn('@baz.get.proxy', '0')
                ->assertSeeIn('@baz.get.proxy.magic', '0')
                ->waitForLivewire()->click('@baz.set')
                ->assertSeeIn('@baz.output', '1')
                ->waitForLivewire()->click('@baz.set.proxy')
                ->assertSeeIn('@baz.output', '2')
                ->click('@baz.set.proxy.magic')
                ->waitForLivewire()->click('@baz.call')
                ->assertSeeIn('@baz.output', '4')
                ->waitForLivewire()->click('@baz.call.proxy')
                ->assertSeeIn('@baz.output', '5')
                ->waitForLivewire()->click('@baz.call.proxy.magic')
                ->assertSeeIn('@baz.output', '6')

                /**
                 * get, set, and call with special characters
                 */
                ->assertSeeIn('@special.output', 'abc')
                ->assertSeeIn('@special.get', 'abc')
                ->assertSeeIn('@special.get.proxy', 'abc')
                ->assertSeeIn('@special.get.proxy.magic', 'abc')
                ->waitForLivewire()->click('@special.set')
                ->assertSeeIn('@special.output', 'ž')
                ->waitForLivewire()->click('@special.set.proxy')
                ->assertSeeIn('@special.output', 'žž')
                ->click('@special.set.proxy.magic')
                ->waitForLivewire()->click('@special.call')
                ->assertSeeIn('@special.output', 'žžžž')
                ->waitForLivewire()->click('@special.call.proxy')
                ->assertSeeIn('@special.output', 'žžžžž')
                ->waitForLivewire()->click('@special.call.proxy.magic')
                ->assertSeeIn('@special.output', 'žžžžžž')

                /**
                 * .call() return value
                 */
                ->assertDontSeeIn('@bob.output', '1')
                ->waitForLivewire()->click('@bob.button.await')
                ->assertSeeIn('@bob.output', '1')
                ->waitForLivewire()->click('@bob.button.promise')
                ->assertSeeIn('@bob.output', '2')

                /**
                 * $wire.entangle
                 */
                ->assertSeeIn('@lob.output', '6')
                ->waitForLivewire(function ($b) {
                    $b->click('@lob.increment');
                })
                ->assertSeeIn('@lob.output', '7')
                ->click('@lob.decrement')
                ->assertSeeIn('@lob.output', '6')

                /**
                 * $wire.entangle nested property
                 */
                ->assertSeeIn('@law.output.alpine', '0')
                ->assertSeeIn('@law.output.wire', '0')
                ->assertSeeIn('@law.output.blade', '0')
                ->waitForLivewire()->click('@law.increment.livewire')
                ->assertSeeIn('@law.output.alpine', '1')
                ->assertSeeIn('@law.output.wire', '1')
                ->assertSeeIn('@law.output.blade', '1')
                ->waitForLivewire()->click('@law.increment.alpine')
                ->assertSeeIn('@law.output.alpine', '2')
                ->assertSeeIn('@law.output.wire', '2')
                ->assertSeeIn('@law.output.blade', '2')

                /**
                 * Make sure property change from Livewire doesn't trigger an additional
                 * request because of @entangle.
                 */
                ->tap(function ($b) {
                    $b->script([
                        'window.livewireRequestCount = 0',
                        "window.Livewire.hook('request', () => { window.livewireRequestCount++ })",
                    ]);
                })
                ->assertScript('window.livewireRequestCount', 0)
                ->waitForLivewire(function ($b) {
                    $b->click('@lob.reset');
                })
                ->assertScript('window.livewireRequestCount', 1)
                ->pause(500)
                ->assertMissing('#livewire-error')
                ->assertSeeIn('@lob.output', '100')

                /**
                 * $dispatch('input', value) works with wire:model
                 */
                ->assertSeeIn('@zorp.output', 'before')
                ->waitForLivewire()->click('@zorp.button')
                ->assertSeeIn('@zorp.output', 'after')
                ->waitForLivewire()->click('@zorp.button.empty')
                ->assertSeeNothingIn('@zorp.output')
            ;
        });
    }

    public function test_alpine_still_updates_even_when_livewire_doesnt_update_html()
    {
        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, SmallComponent::class)
                ->assertSeeIn('@output', '0')
                ->waitForLivewire()->click('@button')
                ->assertSeeIn('@output', '1')
            ;
        });
    }

    public function test_morphdom_can_handle_adding_at_symbol_attributes()
    {
        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, MorphingAtSymbolAttributeComponent::class)
                ->assertAttributeMissing('@span', '@click', 'hey')
                ->waitForLivewire()->click('@button')
                ->assertAttribute('@span', '@click', 'hey')
                ->waitForLivewire()->click('@button')
                ->assertAttributeMissing('@span', '@click', 'hey')
            ;
        });
    }

    public function test_alpine_registers_click_handlers_properly_on_livewire_change()
    {
        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, ClickComponent::class)
                ->waitForLivewire()->click('@show')
                ->click('@click')
                ->assertSeeIn('@alpineClicksFired', 1)
                ->click('@click')
                ->assertSeeIn('@alpineClicksFired', 2)
                ->click('@click')
                ->assertSeeIn('@alpineClicksFired', 3)
                ->click('@componentClick')
                ->assertSeeIn('@alpineComponentClicksFired', 1)
                ->click('@componentClick')
                ->assertSeeIn('@alpineComponentClicksFired', 2)
                ->click('@componentClick')
                ->assertSeeIn('@alpineComponentClicksFired', 3)
            ;
        });
    }

    public function test_alpine_handles_responses_from_multiple_simultaneous_calls_to_livewire()
    {
        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, SimultaneousCallsComponent::class)
                ->assertDontSeeIn('@foo', 'foo')
                ->assertDontSeeIn('@bar', 'bar')
                ->waitForLivewire()->click('@update-foo-and-bar')
                ->assertSeeIn('@foo', 'foo')
                ->assertSeeIn('@bar', 'bar')
            ;
        });
    }
}
