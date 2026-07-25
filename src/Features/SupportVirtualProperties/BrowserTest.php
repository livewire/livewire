<?php

namespace Livewire\Features\SupportVirtualProperties;

use Livewire\Attributes\Virtual;
use Livewire\Component;
use Livewire\Livewire;
use Livewire\Selection;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_virtual_property_state_reaches_the_browser_and_updates_sync_back()
    {
        Livewire::visit(new class extends Component {
            #[Virtual]
            public function selection(): Selection
            {
                return new Selection(keys: ['2']);
            }

            public function pick($key)
            {
                $this->selection->select($key);
            }

            public function render(): string
            {
                return <<<'HTML'
                <div>
                    <input type="checkbox" dusk="one" wire:model="selection" value="1" />
                    <input type="checkbox" dusk="two" wire:model="selection" value="2" />
                    <input type="checkbox" dusk="three" wire:model="selection" value="3" />

                    <button dusk="pick-one" type="button" wire:click="pick('1')">Pick one</button>
                    <button dusk="refresh" type="button" wire:click="$refresh">Refresh</button>

                    <span dusk="server">{{ implode(',', $selection->keys()) }}</span>
                </div>
                HTML;
            }
        })
        // The method-built state hydrated into the browser like a normal property...
        ->assertNotChecked('@one')
        ->assertChecked('@two')
        ->assertSeeIn('@server', '2')
        // A checkbox update syncs back into the method-built instance...
        ->check('@three')
        ->waitForLivewire()->click('@refresh')
        ->assertChecked('@three')
        ->assertSeeIn('@server', '2,3')
        // A server-side mutation from an action reaches the checkboxes...
        ->waitForLivewire()->click('@pick-one')
        ->assertChecked('@one')
        ->assertSeeIn('@server', '2,3,1')
        ;
    }

    public function test_select_all_virtual_config_and_server_totals_survive_round_trips()
    {
        Livewire::visit(new class extends Component {
            #[Virtual]
            public function selection(): Selection
            {
                // The total only ever lives server-side — surviving round
                // trips proves client state hydrates INTO the method-built
                // instance rather than replacing it...
                return (new Selection(keys: ['2'], mode: 'except'))->setTotal(5);
            }

            public function render(): string
            {
                return <<<'HTML'
                <div>
                    <input type="checkbox" dusk="one" wire:model="selection" value="1" />
                    <input type="checkbox" dusk="two" wire:model="selection" value="2" />

                    <button dusk="refresh" type="button" wire:click="$refresh">Refresh</button>

                    <span dusk="server">{{ $selection->isAll() ? 'all' : 'some' }}:{{ implode(',', $selection->except()) }}:{{ $selection->count() }}</span>
                </div>
                HTML;
            }
        })
        // Select-all-except-2 straight out of the method...
        ->assertChecked('@one')
        ->assertNotChecked('@two')
        ->assertSeeIn('@server', 'all:2:4')
        // Selecting the exception empties the except list...
        ->check('@two')
        ->waitForLivewire()->click('@refresh')
        ->assertSeeIn('@server', 'all::5')
        // Deselecting adds a fresh exception — mode and total intact...
        ->uncheck('@one')
        ->waitForLivewire()->click('@refresh')
        ->assertSeeIn('@server', 'all:1:4')
        ;
    }

    public function test_a_virtual_property_works_through_lazy_load()
    {
        Livewire::visit(new #[\Livewire\Attributes\Lazy] class extends Component {
            #[Virtual]
            public function selection(): Selection
            {
                return new Selection(keys: ['2']);
            }

            public function placeholder(): string
            {
                return '<div id="loading">Loading...</div>';
            }

            public function render(): string
            {
                return <<<'HTML'
                <div id="loaded">
                    <input type="checkbox" dusk="one" wire:model="selection" value="1" />
                    <input type="checkbox" dusk="two" wire:model="selection" value="2" />

                    <button dusk="refresh" type="button" wire:click="$refresh">Refresh</button>

                    <span dusk="server">{{ implode(',', $selection->keys()) }}</span>
                </div>
                HTML;
            }
        })
        ->assertSee('Loading...')
        ->waitFor('#loaded')
        // The virtual property's constructed state survives the lazy load...
        ->assertChecked('@two')
        ->assertSeeIn('@server', '2')
        // ...and updates sync back after load like a normal property...
        ->check('@one')
        ->waitForLivewire()->click('@refresh')
        ->assertSeeIn('@server', '2,1')
        ;
    }

    public function test_a_virtual_form_object_validates_and_binds_like_a_declared_one()
    {
        Livewire::visit(new class extends Component {
            #[Virtual]
            public function form(): BrowserVirtualForm
            {
                return new BrowserVirtualForm($this, 'form');
            }

            public function save()
            {
                $this->form->validate();
            }

            public function render(): string
            {
                return <<<'HTML'
                <div>
                    <input dusk="title" wire:model.live="form.title" />

                    <button dusk="save" type="button" wire:click="save">Save</button>

                    <span dusk="booted">{{ $form->booted ? 'booted' : 'not booted' }}</span>
                    <span dusk="error">@error('form.title') {{ $message }} @enderror</span>
                    <span dusk="server">{{ $form->title }}</span>
                </div>
                HTML;
            }
        })
        // boot() ran on the method-built form...
        ->assertSeeIn('@booted', 'booted')
        // #[Validate] rules registered, so saving an empty form errors...
        ->waitForLivewire()->click('@save')
        ->assertSeeIn('@error', 'The title field is required.')
        // wire:model binds through to the form and clears the error...
        ->waitForLivewire()->type('@title', 'Real title')
        ->assertSeeIn('@server', 'Real title')
        ->waitForLivewire()->click('@save')
        ->assertSeeNothingIn('@error')
        ;
    }
}

class BrowserVirtualForm extends \Livewire\Form
{
    #[\Livewire\Attributes\Validate('required')]
    public $title = '';

    public $booted = false;

    public function boot()
    {
        $this->booted = true;
    }
}
