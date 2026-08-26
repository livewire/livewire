<?php

namespace Livewire\Features\SupportDirty;

use Tests\BrowserTestCase;
use Livewire\Livewire;
use Livewire\Component;

class BrowserTest extends BrowserTestCase
{
    function test_wire_dirty_is_reset_by_an_unrelated_roundtrip()
    {
        Livewire::visit(new class extends Component {
            public $title = '';
            public $count = 0;

            public function increment() { $this->count++; }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk="input" type="text" wire:model="title" />
                        <button type="button" dusk="unrelated" wire:click="increment">{{ $count }}</button>

                        <div dusk="dirty" wire:dirty wire:target="title">Unsaved changes</div>
                    </div>
                BLADE;
            }
        })
            ->assertNotVisible('@dirty')
            ->type('@input', 'Hello')
            ->pause(50)
            ->assertVisible('@dirty')
            // An action that saves nothing still makes the component look clean...
            ->waitForLivewire()->click('@unrelated')
            ->pause(50)
            ->assertNotVisible('@dirty')
        ;
    }

    function test_wire_dirty_persist_survives_an_unrelated_roundtrip()
    {
        Livewire::visit(new class extends Component {
            public $title = '';
            public $count = 0;

            public function increment() { $this->count++; }

            public function save() { $this->rebaseline(); }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk="input" type="text" wire:model="title" />
                        <button type="button" dusk="unrelated" wire:click="increment">{{ $count }}</button>
                        <button type="button" dusk="save" wire:click="save">Save</button>

                        <div dusk="dirty" wire:dirty.persist wire:target="title">Unsaved changes</div>
                        <div dusk="clean" wire:dirty.persist.remove wire:target="title">All saved</div>
                    </div>
                BLADE;
            }
        })
            ->assertNotVisible('@dirty')
            ->assertVisible('@clean')
            ->type('@input', 'Hello')
            ->pause(50)
            ->assertVisible('@dirty')
            // Unlike plain wire:dirty, an unrelated round-trip leaves it dirty...
            ->waitForLivewire()->click('@unrelated')
            ->pause(50)
            ->assertVisible('@dirty')
            ->assertNotVisible('@clean')
            // ...only an explicit rebaseline clears it...
            ->waitForLivewire()->click('@save')
            ->pause(50)
            ->assertNotVisible('@dirty')
            ->assertVisible('@clean')
        ;
    }

    function test_dollar_dirty_accepts_a_persist_option_and_dollar_rebaseline_clears_it()
    {
        Livewire::visit(new class extends Component {
            public $title = '';
            public $count = 0;

            public function increment() { $this->count++; }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk="input" type="text" wire:model="title" />
                        <button type="button" dusk="unrelated" wire:click="increment">{{ $count }}</button>
                        <button type="button" dusk="rebaseline" x-on:click="$wire.$rebaseline()">Rebaseline</button>

                        <div dusk="dirty" x-show="$wire.$dirty('title', { persist: true })">Unsaved changes</div>
                    </div>
                BLADE;
            }
        })
            ->assertNotVisible('@dirty')
            ->type('@input', 'Hello')
            ->pause(50)
            ->assertVisible('@dirty')
            ->waitForLivewire()->click('@unrelated')
            ->pause(50)
            ->assertVisible('@dirty')
            ->click('@rebaseline')
            ->pause(50)
            ->assertNotVisible('@dirty')
        ;
    }

    function test_persist_composes_with_the_class_modifier()
    {
        Livewire::visit(new class extends Component {
            public $title = '';
            public $count = 0;

            public function increment() { $this->count++; }

            public function save() { $this->rebaseline(); }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk="input" type="text" wire:model="title" wire:dirty.persist.class="unsaved" />
                        <button type="button" dusk="unrelated" wire:click="increment">{{ $count }}</button>
                        <button type="button" dusk="save" wire:click="save">Save</button>
                    </div>
                BLADE;
            }
        })
            ->assertClassMissing('@input', 'unsaved')
            ->type('@input', 'Hello')
            ->pause(50)
            ->assertHasClass('@input', 'unsaved')
            ->waitForLivewire()->click('@unrelated')
            ->pause(50)
            ->assertHasClass('@input', 'unsaved')
            ->waitForLivewire()->click('@save')
            ->pause(50)
            ->assertClassMissing('@input', 'unsaved')
        ;
    }
}
