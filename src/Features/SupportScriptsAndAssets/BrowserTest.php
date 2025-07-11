<?php

namespace Livewire\Features\SupportScriptsAndAssets;

use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use Livewire\Drawer\Utils;
use Illuminate\Support\Facades\Route;

class BrowserTest extends \Tests\BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            Route::get('/non-livewire-asset.js', function () {
                return Utils::pretendResponseIsFile(__DIR__.'/non-livewire-asset.js');
            });

            Route::get('/non-livewire-assets', function () {
                return Blade::render(<<< BLADE
                <html>
                    <head>
                    </head>
                    <body>
                        <div>
                            <h1 dusk="foo"></h1>
                        </div>
                        @assets
                        <script src="/non-livewire-asset.js" defer></script>
                        @endassets
                    </body>
                </html>
                BLADE);
            });
        };
    }
    public function test_can_evaluate_a_script_inside_a_component()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public $message = 'original';

            public function render() { return <<<'HTML'
            <div>
                <h1 dusk="foo"></h1>
                <h2 dusk="bar" x-text="$wire.message"></h2>
            </div>

            @script
            <script>
                document.querySelector('[dusk="foo"]').textContent = 'evaluated'
                $wire.message = 'changed'
            </script>
            @endscript
            HTML; }
        })
        ->waitForText('evaluated')
        ->assertSeeIn('@foo', 'evaluated')
        ->assertSeeIn('@bar', 'changed')
        ;
    }

    public function test_can_register_an_alpine_component_inside_a_script_tag()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public $message = 'original';

            public function render() { return <<<'HTML'
            <div>
                <h1 dusk="foo" x-dusk-test x-init="console.log('init')"></h1>
            </div>

            @script
            <script>
                console.log('hi')
                Alpine.directive('dusk-test', (el) => {
                    el.textContent = 'evaluated'
                })
            </script>
            @endscript
            HTML; }
        })
        ->waitForText('evaluated')
        ->assertSeeIn('@foo', 'evaluated')
        ;
    }

    public function test_multiple_scripts_can_be_evaluated()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public function render() { return <<<'HTML'
            <div>
                <h1 dusk="foo"></h1>
                <h2 dusk="bar"></h2>
            </div>

            @script
            <script>
                document.querySelector('[dusk="foo"]').textContent = 'evaluated-first'
            </script>
            @endscript
            @script
            <script>
                document.querySelector('[dusk="bar"]').textContent = 'evaluated-second'
            </script>
            @endscript
            HTML; }
        })
        ->waitForText('evaluated-first')
        ->assertSeeIn('@foo', 'evaluated-first')
        ->assertSeeIn('@bar', 'evaluated-second')
        ;
    }

    public function test_scripts_can_be_added_conditionally()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public $show = false;

            public function render() { return <<<'HTML'
            <div>
                <button dusk="button" wire:click="$set('show', true)">refresh</button>
                <h1 dusk="foo" wire:ignore></h1>
            </div>

            @if($show)
                @script
                <script>
                    document.querySelector('[dusk="foo"]').textContent = 'evaluated-second'
                </script>
                @endscript
            @endif

            @script
            <script>
                document.querySelector('[dusk="foo"]').textContent = 'evaluated-first'
            </script>
            @endscript
            HTML; }
        })
        ->assertSeeIn('@foo', 'evaluated-first')
        ->waitForLivewire()->click('@button')
        ->assertSeeIn('@foo', 'evaluated-second')
        ;
    }

    public function test_assets_can_be_loaded()
    {
        Route::get('/test.js', function () {
            return Utils::pretendResponseIsFile(__DIR__.'/test.js');
        });

        Livewire::visit(new class extends \Livewire\Component {
            public function render() { return <<<'HTML'
            <div>
                <h1 dusk="foo" wire:ignore></h1>
            </div>

            @assets
            <script src="/test.js" defer></script>
            @endassets
            HTML; }
        })
        ->assertSeeIn('@foo', 'evaluated')
        ;
    }

    public function test_remote_assets_can_be_loaded()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public function render() { return <<<'HTML'
            <div>
                <input type="text" data-picker>

                <span dusk="output" x-text="'foo'"></span>
            </div>

            @assets
                <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js" defer></script>
                <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
            @endassets

            @script
                <script>
                    window.datePicker = new Pikaday({ field: $wire.$el.querySelector('[data-picker]') });
                </script>
            @endscript
            HTML; }
        })
        ->waitForTextIn('@output', 'foo')
        ->assertScript('!! window.datePicker')
        ;
    }

    public function test_remote_assets_can_be_loaded_lazily()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public $load = false;

            public function render() { return <<<'HTML'
            <div>
                <input type="text" data-picker>

                <button wire:click="$toggle('load')" dusk="button">Load assets</button>

                <span dusk="output" x-text="'foo'"></span>
            </div>

            @if ($load)
                @assets
                    <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js" defer></script>
                    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
                @endassets

                @script
                    <script>
                        window.datePicker = new Pikaday({ field: $wire.$el.querySelector('[data-picker]') });
                    </script>
                @endscript
            @endif
            HTML; }
        })
        ->waitForTextIn('@output', 'foo')
        ->waitForLivewire()->click('@button')
        ->waitUntil('!! window.datePicker === true')
        ;
    }

    public function test_remote_assets_can_be_loaded_from_a_deferred_nested_component()
    {
        Livewire::visit([new class extends \Livewire\Component {
            public $load = false;

            public function render() { return <<<'HTML'
            <div>
                <button wire:click="$toggle('load')" dusk="button">Load assets</button>

                <span dusk="output" x-text="'foo'"></span>

                @if ($load)
                    <livewire:child />
                @endif
            </div>
            HTML; }
        },
        'child' => new class extends \Livewire\Component {
            public function render() { return <<<'HTML'
            <div>
                <input type="text" data-picker>
            </div>

            @assets
                <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
                <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
            @endassets

            @script
                <script>
                    window.datePicker = new Pikaday({ field: $wire.$el.querySelector('[data-picker]') });
                </script>
            @endscript
            HTML; }
        },
        ])
        ->waitForTextIn('@output', 'foo')
        ->waitForLivewire()->click('@button')
        ->waitUntil('!! window.datePicker === true')
        ;
    }

    public function test_assets_directive_can_be_used_outside_of_a_livewire_compoentn_and_can_be_loaded()
    {
        // See the `tweakApplicationHook` method for the route definition.
        $this->browse(function ($browser) {
            $browser->visit('/non-livewire-assets')
                ->assertSeeIn('@foo', 'non livewire evaluated');
        });
    }

    public function test_remote_inline_scripts_can_be_loaded_from_a_deferred_nested_component()
    {
        Livewire::visit([new class extends \Livewire\Component {
            public $load = false;

            public function render() { return <<<'HTML'
            <div>
                <button wire:click="$toggle('load')" dusk="button">Load assets</button>

                <span dusk="output" x-text="'foo'"></span>

                @if ($load)
                    <livewire:child />
                @endif
            </div>
            HTML; }
        },
        'child' => new class extends \Livewire\Component {
            public function render() { return <<<'HTML'
            <div>
                <input type="text" data-picker>
            </div>

            @assets
                <script>
                    window.Pikaday = function (options) {
                        // ...

                        return this
                    }
                </script>
            @endassets

            @script
                <script>
                    window.datePicker = new Pikaday({ field: $wire.$el.querySelector('[data-picker]') });
                </script>
            @endscript
            HTML; }
        },
        ])
        ->waitForTextIn('@output', 'foo')
        ->waitForLivewire()->click('@button')
        ->waitUntil('!! window.datePicker === true')
        ;
    }

    public function test_can_listen_for_initial_dispatches_inside_script()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public function render() {
                $this->dispatch('test')->self();

                return <<<'HTML'
                <div>
                    <h1 dusk="foo"></h1>
                </div>

                @script
                <script>
                    $wire.on('test', () => {
                        $wire.el.querySelector('h1').textContent = 'received'
                    })
                </script>
                @endscript
                HTML;
            }
        })
        ->waitForTextIn('@foo', 'received')
        ;
    }

    public function test_functions_loaded_in_scripts_are_not_auto_evaluated()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public function render() { return <<<'HTML'
            <div>
                <div dusk="output"></div>
            </div>

            @script
                <script>
                    function run() {
                        document.querySelector('[dusk="output"]').textContent = 'evaluated';
                    }

                    document.querySelector('[dusk="output"]').textContent = 'initialized';
                </script>
            @endscript
            HTML; }
        })
            ->waitForText('initialized')
            ->assertSeeIn('@output', 'initialized')
            ->assertDontSeeIn('@output', 'evaluated')
        ;
    }

    public function test_why_alpine_data_automatically_syncing_with_form_variable()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public array $options = [];

            public function render() {
                return <<<'BLADE'
                    <div x-data="testing">
                        <div dusk="backendOption">@json($options)</div>

                        <pre x-text="JSON.stringify($wire.options, null, 2)" dusk="livewireOptions"></pre>
                        <pre x-text="JSON.stringify(options, null, 2)" dusk="options"></pre>
                        <button x-on:click="addOption" dusk="addOption">Add</button>
                        <button x-on:click="$wire.$refresh" dusk="refresh">Refresh</button>
                    </div>

                    @script
                        <script>
                            Alpine.data('testing', () => {
                                return {
                                    options: [],
                                    init() {
                                        this.options = this.$wire.options
                                    },
                                    addOption() {
                                        this.options.push({
                                            text: '',
                                            explanation: '',
                                            is_correct: false,
                                        });
                                    },
                                };
                            });
                        </script>
                    @endscript
                BLADE;
            }
        })
        ->click('@addOption')
        ->pause(1000)
        ->click('@refresh')
        ->pause(1000)
        ->assertSeeIn("@backendOption", '[{"text":"","explanation":"","is_correct":false}]')
        ;
    }

    public function test_introduce_clone_magic_property()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public array $options = [];

            public function render() {
                return <<<'BLADE'
                    <div x-data="testing">
                        <div dusk="backendOption">@json($options)</div>

                        <pre x-text="JSON.stringify($wire.options, null, 2)" dusk="livewireOptions"></pre>
                        <pre x-text="JSON.stringify(options, null, 2)" dusk="options"></pre>
                        <button x-on:click="addOption" dusk="addOption">Add</button>
                        <button x-on:click="$wire.$refresh" dusk="refresh">Refresh</button>
                    </div>

                    @script
                        <script>
                            Alpine.data('testing', () => {
                                return {
                                    options: [],
                                    init() {
                                        this.options = this.$wire.$clone(this.$wire.options)
                                    },
                                    addOption() {
                                        this.options.push({
                                            text: '',
                                            explanation: '',
                                            is_correct: false,
                                        });
                                    },
                                };
                            });
                        </script>
                    @endscript
                BLADE;
            }
        })
        ->click('@addOption')
        ->pause(1000)
        ->click('@refresh')
        ->pause(1000)
        ->assertDontSeeIn("@backendOption", '[{"text":"","explanation":"","is_correct":false}]')
        ;
    }

    public function test_why_alpine_string_not_syncing_automatically_with_form_variable()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public string $name = '';

            public function render() {
                return <<<'BLADE'
                    <div x-data="testing">
                        <div dusk="backendName">{{ $name }}</div>

                        <pre x-text="$wire.name" dusk="livewireName"></pre>
                        <input type="text" x-model="name" dusk="input">
                        <button x-on:click="$wire.$refresh" dusk="refresh">Refresh</button>
                    </div>

                    @script
                        <script>
                            Alpine.data('testing', () => {
                                return {
                                    name: '',
                                    init() {
                                        this.name = this.$wire.name
                                    },
                                };
                            });
                        </script>
                    @endscript
                BLADE;
            }
        })
        ->type('@input', 'foo')
        ->pause(1000)
        ->click('@refresh')
        ->pause(1000)
        ->assertDontSeeIn("@backendName", '"foo"')
        ;
    }
}
