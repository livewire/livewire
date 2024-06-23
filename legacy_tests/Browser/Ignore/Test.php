<?php

namespace LegacyTests\Browser\Ignore;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class Test extends BrowserTestCase
{
    public function test_wire_ignore()
    {
        Livewire::visit(new class extends Component {
            public $foo = false;
            public $bar = false;
            public $baz = false;
            public $bob = false;
            public $lob = false;

            public function render()
            {
                return <<<'HTML'
                    <div>
                        <button wire:click="$set('foo', true)" @if ($foo) some-new-attribute="true" @endif wire:ignore dusk="foo">Foo</button>

                        <button wire:click="$set('bar', true)" wire:ignore dusk="bar">
                             <span dusk="bar.output">{{ $bar ? 'new' : 'old' }}</span>
                        </button>

                        <button wire:click="$set('baz', true)" @if ($baz) some-new-attribute="true" @endif wire:ignore dusk="baz">Baz</button>
                             <span dusk="baz.output">{{ $baz ? 'new' : 'old' }}</span>
                        </button>

                        <button wire:click="$set('bob', true)" wire:ignore dusk="bob">
                             <span dusk="bob.output">{{ $bob ? 'new' : 'old' }}</span>
                        </button>

                        <button wire:click="$set('lob', true)" @if ($lob) some-new-attribute="true" @endif wire:ignore dusk="lob">lob</button>
                             <span dusk="lob.output">{{ $lob ? 'new' : 'old' }}</span>
                        </button>
                    </div>
                HTML;
            }
        })
            /**
             * wire:ignore doesnt modify element or children after update
             */
            ->assertAttributeMissing('@foo', 'some-new-attribute')
            ->waitForLivewire()->click('@foo')
            ->assertAttributeMissing('@foo', 'some-new-attribute')

            /**
             * wire:ignore ignores updates to children
             */
            ->assertSeeIn('@bar.output', 'old')
            ->waitForLivewire()->click('@bar')
            ->assertSeeIn('@bar.output', 'old')

            /**
             * wire:ignore.self ignores updates to self, but not children
             */
            ->assertSeeIn('@baz.output', 'old')
            ->assertAttributeMissing('@baz', 'some-new-attribute')
            ->waitForLivewire()->click('@baz')
            ->assertAttributeMissing('@baz', 'some-new-attribute')
            ->assertSeeIn('@baz.output', 'new')

            /**
             * adding .__livewire_ignore to element ignores updates to children
             */
            ->tap(function ($b) { $b->script("document.querySelector('[dusk=\"bob\"]').__livewire_ignore = true"); })
            ->assertSeeIn('@bob.output', 'old')
            ->waitForLivewire()->click('@bob')
            ->assertSeeIn('@bob.output', 'old')

            /**
             * adding .__livewire_ignore_self to element ignores updates to self, but not children
             */
            ->tap(function ($b) { $b->script("document.querySelector('[dusk=\"lob\"]').__livewire_ignore_self = true"); })
            ->assertSeeIn('@lob.output', 'old')
            ->assertAttributeMissing('@lob', 'some-new-attribute')
            ->waitForLivewire()->click('@lob')
            ->assertAttributeMissing('@lob', 'some-new-attribute')
            ->assertSeeIn('@lob.output', 'new')
        ;
    }
}
