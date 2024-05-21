<?php

namespace LegacyTests\Browser\Replace;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class Test extends BrowserTestCase
{
    public function test_wire_replace()
    {

        Livewire::visit(new class extends Component {
            public $foo = false;
            public $bar = false;
            public $baz = false;
            public $bob = false;
            public $lob = false;
            public $quo = 0;

            public function render()
            {
                return <<<'HTML'
                    <div>
                        <button x-data="{ __foo: $wire.foo }" wire:click="$set('foo', true)" wire:replace dusk="foo">
                          <span dusk="foo.output" x-text="__foo === $wire.foo ? 'same' : 'different'">foo.output</span>
                        </button>
                        
                        <button x-data="{ __bar: $wire.bar }" wire:click="$set('bar', true)" @if($bar) some-new-attribute="bar" @endif wire:replace dusk="bar">
                          <span dusk="bar.output" x-text="__bar === $wire.bar ? 'same' : 'different'">bar.output</span>
                        </button>
                        
                        <button x-data="{ __baz: $wire.baz }" wire:click="$set('baz', true)" wire:replace.self dusk="baz">
                          <span dusk="baz.output" x-text="__baz === $wire.baz ? 'same' : 'different'">baz.output</span>
                        </button>
                        
                        <button x-data="{ __bob: $wire.bob }" wire:click="$set('bob', true)" dusk="bob">
                          <span dusk="bob.output" x-text="__bob === $wire.bob ? 'same' : 'different'">bob.output</span>
                        </button>
                        
                        <button x-data="{ __lob: $wire.lob }" wire:click="$set('lob', true)" dusk="lob">
                          <span dusk="lob.output" x-text="__lob === $wire.lob ? 'same' : 'different'">lob.output</span>
                        </button>
                        
                        <button
                          x-data="{ __quo: $wire.quo }"
                          wire:click="$set('quo', $wire.quo + 1)"
                          @if($quo === 1) wire:replace @elseif ($quo === 2) wire:replace.self @endif
                          dusk="quo"
                        >
                          <span dusk="quo.output" x-text="__quo === $wire.quo ? 'same' : 'different'">quo.output</span>
                          <span dusk="quo.output-diffed">{{ $quo }}</span>
                          <span dusk="quo.output-ignored" wire:ignore>{{ $quo }}</span>
                        </button>
                    </div>
                HTML;
            }
        })
            /**
             * wire:replace replaces children from update
             */
            ->assertSeeIn('@foo.output', 'same')
            ->waitForLivewire()->click('@foo')
            ->assertSeeIn('@foo.output', 'different')
            ->tap(function ($b) {
                $this->assertSame(
                    [true],
                    $b->script("return document.querySelector('[dusk=\"foo\"]').__livewire_replace")
                );
            })

            /**
             * wire:replace merges attribute changes, but doesn't replace the element
             */
            ->assertSeeIn('@bar.output', 'same')
            ->assertAttributeMissing('@bar', 'some-new-attribute')
            ->waitForLivewire()->click('@bar')
            ->assertSeeIn('@bar.output', 'different')
            ->assertAttribute('@bar', 'some-new-attribute', 'bar')

            /**
             * wire:replace.self replaces the element and all children
             */
            ->assertSeeIn('@baz.output', 'same')
            ->waitForLivewire()->click('@baz')
            ->assertSeeIn('@baz.output', 'same')
            ->tap(function ($b) {
                // __livewire_replace_self is re-processed for the new element
                $this->assertSame(
                    [true],
                    $b->script("return document.querySelector('[dusk=\"baz\"]').__livewire_replace_self")
                );
            })

            /**
             * adding .__livewire_replace to element replaces children after update, but doesn't replace the element
             */
            ->tap(function ($b) { $b->script("document.querySelector('[dusk=\"bob\"]').__livewire_replace = true"); })
            ->assertSeeIn('@bob.output', 'same')
            ->waitForLivewire()->click('@bob')
            ->assertSeeIn('@bob.output', 'different')
            ->tap(function ($b) {
                // __livewire_replace hasn't been removed from the element because only the children were replaced
                $this->assertSame(
                    [true],
                    $b->script("return document.querySelector('[dusk=\"bob\"]').__livewire_replace")
                );
            })

            /**
             * adding .__livewire_replace_self to element replaces the element and all children
             */
            ->tap(function ($b) { $b->script("document.querySelector('[dusk=\"lob\"]').__livewire_replace_self = true"); })
            ->assertSeeIn('@lob.output', 'same')
            ->waitForLivewire()->click('@lob')
            ->assertSeeIn('@lob.output', 'same')
            ->tap(function ($b) {
                // __livewire_replace_self no longer exists because the element was replaced
                $this->assertSame(
                    [null],
                    $b->script("return document.querySelector('[dusk=\"lob\"]').__livewire_replace_self")
                );
            })

            /**
             * wire:replace replaces wire:ignored children
             */
            ->assertSeeIn('@quo.output', 'same')
            ->assertSeeIn('@quo.output-diffed', '0')
            ->assertSeeIn('@quo.output-ignored', '0')
            ->assertAttributeMissing('@quo', 'wire:replace')
            ->assertAttributeMissing('@quo', 'wire:replace.self')
            ->waitForLivewire()->click('@quo')

            // there was no wire:replace - wire:ignore should be respected, dom diffed
            ->assertSeeIn('@quo.output', 'different')
            ->assertSeeIn('@quo.output-diffed', '1')
            ->assertSeeIn('@quo.output-ignored', '0')
            ->assertAttribute('@quo', 'wire:replace', '')
            // this update added wire:replace to the element
            ->assertAttributeMissing('@quo', 'wire:replace.self')

            ->waitForLivewire()->click('@quo')
            // We had wire:replace - wire:ignore should be overridden, but the element itself should not be replaced
            ->assertSeeIn('@quo.output', 'different')
            ->assertSeeIn('@quo.output-diffed', '2')
            ->assertSeeIn('@quo.output-ignored', '2')
            // this update added wire:replace.self to the element
            ->assertAttributeMissing('@quo', 'wire:replace')
            ->assertAttribute('@quo', 'wire:replace.self', '')

            ->waitForLivewire()->click('@quo')
            // We had wire:replace.self - everything should be overridden
            ->assertSeeIn('@quo.output', 'same')
            ->assertSeeIn('@quo.output-diffed', '3')
            ->assertSeeIn('@quo.output-ignored', '3')
            ->assertAttributeMissing('@quo', 'wire:replace')
            ->assertAttributeMissing('@quo', 'wire:replace.self')

            ->waitForLivewire()->click('@quo')
            // We no longer have wire:replace or wire:replace.self, we should return to DOM diffing and wire:ignore
            ->assertSeeIn('@quo.output', 'different')
            ->assertSeeIn('@quo.output-diffed', '4')
            ->assertSeeIn('@quo.output-ignored', '3')
            ->assertAttributeMissing('@quo', 'wire:replace')
            ->assertAttributeMissing('@quo', 'wire:replace.self')
        ;
    }
}
