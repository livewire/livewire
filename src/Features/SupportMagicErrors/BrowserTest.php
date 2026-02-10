<?php

namespace Livewire\Features\SupportMagicErrors;

use Livewire\Attributes\Validate;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_magic_errors_methods_all_returns_the_same_values_as_the_backend_equivalent()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                #[Validate(['min:5', 'integer'])]
                public $name;

                #[Validate(['min:5', 'integer'])]
                public $email;

                public function save() {
                    $this->validate();
                }

                public function render() { return <<<'HTML'
                <div>
                    <button wire:click="save" dusk="save">Save</button>

                    <div>
                        <div>
                            <p>messages:</p>
                            <p>Backend: <span dusk="backend-messages">{!! htmlspecialchars(json_encode($errors->messages())) !!}</span></p>
                            <p>Frontend: <span dusk="frontend-messages" x-text="JSON.stringify($wire.errors.messages())"></span></p>
                        </div>
                        <div>
                            <p>keys:</p>
                            <p>Backend: <span dusk="backend-keys">{!! htmlspecialchars(json_encode($errors->keys())) !!}</span></p>
                            <p>Frontend: <span dusk="frontend-keys" x-text="JSON.stringify($wire.errors.keys())"></span></p>
                        </div>
                        <div>
                            <p>has:</p>
                            <p>Backend: <span dusk="backend-has">{!! htmlspecialchars(json_encode($errors->has('name'))) !!}</span></p>
                            <p>Frontend: <span dusk="frontend-has" x-text="JSON.stringify($wire.errors.has('name'))"></span></p>
                        </div>
                        <div>
                            <p>hasAny:</p>
                            <p>Backend: <span dusk="backend-hasAny">{!! htmlspecialchars(json_encode($errors->hasAny(['name', 'email']))) !!}</span></p>
                            <p>Frontend: <span dusk="frontend-hasAny" x-text="JSON.stringify($wire.errors.hasAny(['name', 'email']))"></span></p>
                        </div>
                        <div>
                            <p>missing:</p>
                            <p>Backend: <span dusk="backend-missing">{!! htmlspecialchars(json_encode($errors->missing('name'))) !!}</span></p>
                            <p>Frontend: <span dusk="frontend-missing" x-text="JSON.stringify($wire.errors.missing('name'))"></span></p>
                        </div>
                        <div>
                            <p>first:</p>
                            <p>Backend: <span dusk="backend-first">{!! htmlspecialchars(json_encode($errors->first('name'))) !!}</span></p>
                            <p>Frontend: <span dusk="frontend-first" x-text="JSON.stringify($wire.errors.first('name'))"></span></p>
                        </div>
                        <div>
                            <p>get:</p>
                            <p>Backend: <span dusk="backend-get">{!! htmlspecialchars(json_encode($errors->get('name'))) !!}</span></p>
                            <p>Frontend: <span dusk="frontend-get" x-text="JSON.stringify($wire.errors.get('name'))"></span></p>
                        </div>
                        <div>
                            <p>all:</p>
                            <p>Backend: <span dusk="backend-all">{!! htmlspecialchars(json_encode($errors->all())) !!}</span></p>
                            <p>Frontend: <span dusk="frontend-all" x-text="JSON.stringify($wire.errors.all())"></span></p>
                        </div>
                        <div>
                            <p>isEmpty:</p>
                            <p>Backend: <span dusk="backend-isEmpty">{!! htmlspecialchars(json_encode($errors->isEmpty())) !!}</span></p>
                            <p>Frontend: <span dusk="frontend-isEmpty" x-text="JSON.stringify($wire.errors.isEmpty())"></span></p>
                        </div>
                        <div>
                            <p>isNotEmpty:</p>
                            <p>Backend: <span dusk="backend-isNotEmpty">{!! htmlspecialchars(json_encode($errors->isNotEmpty())) !!}</span></p>
                            <p>Frontend: <span dusk="frontend-isNotEmpty" x-text="JSON.stringify($wire.errors.isNotEmpty())"></span></p>
                        </div>
                        <div>
                            <p>any:</p>
                            <p>Backend: <span dusk="backend-any">{!! htmlspecialchars(json_encode($errors->any())) !!}</span></p>
                            <p>Frontend: <span dusk="frontend-any" x-text="JSON.stringify($wire.errors.any())"></span></p>
                        </div>
                        <div>
                            <p>count:</p>
                            <p>Backend: <span dusk="backend-count">{!! htmlspecialchars(json_encode($errors->count())) !!}</span></p>
                            <p>Frontend: <span dusk="frontend-count" x-text="JSON.stringify($wire.errors.count())"></span></p>
                        </div>
                    </div>
                </div>
                HTML; }
            }
        ])
            // We just want to assert that `$wire.$errors` returns the same values as the backend equivalent...
            ->tap(function ($browser) {
                $this->assertEquals($browser->text('@backend-messages'), $browser->text('@frontend-messages'));
                $this->assertEquals($browser->text('@backend-keys'), $browser->text('@frontend-keys'));
                $this->assertEquals($browser->text('@backend-has'), $browser->text('@frontend-has'));
                $this->assertEquals($browser->text('@backend-hasAny'), $browser->text('@frontend-hasAny'));
                $this->assertEquals($browser->text('@backend-missing'), $browser->text('@frontend-missing'));
                $this->assertEquals($browser->text('@backend-first'), $browser->text('@frontend-first'));
                $this->assertEquals($browser->text('@backend-get'), $browser->text('@frontend-get'));
                $this->assertEquals($browser->text('@backend-all'), $browser->text('@frontend-all'));
                $this->assertEquals($browser->text('@backend-isEmpty'), $browser->text('@frontend-isEmpty'));
                $this->assertEquals($browser->text('@backend-isNotEmpty'), $browser->text('@frontend-isNotEmpty'));
                $this->assertEquals($browser->text('@backend-any'), $browser->text('@frontend-any'));
                $this->assertEquals($browser->text('@backend-count'), $browser->text('@frontend-count'));
            })

            // Actually run validation and ensure they match...
            ->waitForLivewire()->click('@save')
            ->tap(function ($browser) {
                $this->assertEquals($browser->text('@backend-messages'), $browser->text('@frontend-messages'));
                $this->assertEquals($browser->text('@backend-keys'), $browser->text('@frontend-keys'));
                $this->assertEquals($browser->text('@backend-has'), $browser->text('@frontend-has'));
                $this->assertEquals($browser->text('@backend-hasAny'), $browser->text('@frontend-hasAny'));
                $this->assertEquals($browser->text('@backend-missing'), $browser->text('@frontend-missing'));
                $this->assertEquals($browser->text('@backend-first'), $browser->text('@frontend-first'));
                $this->assertEquals($browser->text('@backend-get'), $browser->text('@frontend-get'));
                $this->assertEquals($browser->text('@backend-all'), $browser->text('@frontend-all'));
                $this->assertEquals($browser->text('@backend-isEmpty'), $browser->text('@frontend-isEmpty'));
                $this->assertEquals($browser->text('@backend-isNotEmpty'), $browser->text('@frontend-isNotEmpty'));
                $this->assertEquals($browser->text('@backend-any'), $browser->text('@frontend-any'));
                $this->assertEquals($browser->text('@backend-count'), $browser->text('@frontend-count'));
            })
            ;
    }

    public function test_clear_removes_all_errors()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                #[Validate(['min:5'])]
                public $name;

                #[Validate(['min:5'])]
                public $email;

                public function save() {
                    $this->validate();
                }

                public function render() { return <<<'HTML'
                <div>
                    <button wire:click="save" dusk="save">Save</button>
                    <button x-on:click="$wire.$errors.clear()" dusk="clear-all">Clear All</button>
                    <button x-on:click="$wire.$errors.clear('name')" dusk="clear-name">Clear Name</button>

                    <span dusk="has-any" x-text="$wire.$errors.any()"></span>
                    <span dusk="has-name" x-text="$wire.$errors.has('name')"></span>
                    <span dusk="has-email" x-text="$wire.$errors.has('email')"></span>
                </div>
                HTML; }
            }
        ])
            // Trigger validation errors...
            ->waitForLivewire()->click('@save')
            ->assertSeeIn('@has-any', 'true')
            ->assertSeeIn('@has-name', 'true')
            ->assertSeeIn('@has-email', 'true')

            // Clear only the "name" field...
            ->click('@clear-name')
            ->pause(100)
            ->assertSeeIn('@has-any', 'true')
            ->assertSeeIn('@has-name', 'false')
            ->assertSeeIn('@has-email', 'true')

            // Trigger errors again...
            ->waitForLivewire()->click('@save')
            ->assertSeeIn('@has-any', 'true')

            // Clear all errors...
            ->click('@clear-all')
            ->pause(100)
            ->assertSeeIn('@has-any', 'false')
            ->assertSeeIn('@has-name', 'false')
            ->assertSeeIn('@has-email', 'false')
            ;
    }
}