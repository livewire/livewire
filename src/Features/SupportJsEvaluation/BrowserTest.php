<?php

namespace Livewire\Features\SupportJsEvaluation;

use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_can_toggle_a_purely_js_property_with_a_purely_js_function()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $show = false;

                #[BaseJs]
                function toggle()
                {
                    return <<<'JS'
                        $wire.show = ! $wire.show;
                    JS;
                }

                public function render() { return <<<'HTML'
                <div>
                    <button @click="$wire.toggle" dusk="toggle">Toggle</button>

                    <div dusk="target" x-show="$wire.show">
                        Toggle Me!
                    </div>
                </div>
                HTML; }
        })
        ->waitUntilMissingText('Toggle Me!')
        ->assertDontSee('Toggle Me!')
        ->click('@toggle')
        ->waitForText('Toggle Me!')
        ->assertSee('Toggle Me!')
        ->click('@toggle')
        ->waitUntilMissingText('Toggle Me!')
        ->assertDontSee('Toggle Me!')
        ;
    }

    public function test_can_evaluate_js_code_after_an_action_is_performed()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $show = false;

                function toggle()
                {
                    $this->js('$wire.show = true');
                }

                public function render() { return <<<'HTML'
                <div>
                    <button wire:click="toggle" dusk="toggle">Toggle</button>

                    <div dusk="target" x-show="$wire.show">
                        Toggle Me!
                    </div>
                </div>
                HTML; }
        })
        ->assertDontSee('Toggle Me!')
        ->waitForLivewire()->click('@toggle')
        ->waitForText('Toggle Me!')
        ;
    }

    public function test_can_define_js_actions_though_dollar_wire_on_a_component()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public function render() { return <<<'HTML'
                <div>
                    <button wire:click="$js.test" dusk="test">Test</button>
                </div>

                @script
                <script>
                    $wire.$js('test', () => {
                        window.test = 'through dollar wire'
                    })
                </script>
                @endscript
                HTML; }
            }
        )
        ->click('@test')
        ->assertScript('window.test === "through dollar wire"')
        ;
    }

    public function test_can_define_js_actions_though_dollar_js_magic_on_a_component()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public function render() { return <<<'HTML'
                <div>
                    <button wire:click="$js.test" dusk="test">Test</button>
                </div>

                @script
                <script>
                    $js('test', () => {
                        window.test = 'through dollar js'
                    })
                </script>
                @endscript
                HTML; }
            }
        )
        ->click('@test')
        ->assertScript('window.test === "through dollar js"')
        ;
    }

    public function test_can_call_a_defined_js_action_from_wire_click_without_params()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public function render() {
                    return <<<'HTML'
                        <div>
                            <button wire:click="$js.test" dusk="test">Test</button>
                        </div>

                        @script
                        <script>
                            $js('test', () => {
                                window.test = 'through wire:click'
                            })
                        </script>
                        @endscript
                    HTML;
                }
            }
        )
        ->click('@test')
        ->assertScript('window.test === "through wire:click"')
        ;
    }

    public function test_can_call_a_defined_js_action_from_wire_click_with_params()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public function render() {
                    return <<<'HTML'
                        <div>
                            <button wire:click="$js.test('foo','bar')" dusk="test">Test</button>
                        </div>

                        @script
                        <script>
                            $js('test', (param1, param2) => {
                                console.log('test', param1, param2);
                                window.test = `through wire:click with params: ${param1}, ${param2}`
                            })
                        </script>
                        @endscript
                    HTML;
                }
            }
        )
        ->click('@test')
        ->assertScript('window.test === "through wire:click with params: foo, bar"')
        ;
    }

    public function test_can_call_a_defined_js_action_from_the_backend_using_the_js_method_without_params()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public function save() {
                    $this->js('test');
                }
                public function render() {
                    return <<<'HTML'
                        <div>
                            <button wire:click="save" dusk="save">Save</button>
                        </div>

                        @script
                        <script>
                            $js('test', () => {
                                window.test = 'through backend js method'
                            })
                        </script>
                        @endscript
                    HTML;
                }
            }
        )
        ->waitForLivewire()->click('@save')
        ->assertScript('window.test === "through backend js method"')
        ;
    }

    public function test_can_call_a_defined_js_action_from_the_backend_using_the_js_method_with_params()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public function save() {
                    $this->js('test', 'foo', 'bar');
                }
                public function render() {
                    return <<<'HTML'
                        <div>
                            <button wire:click="save" dusk="save">Save</button>
                        </div>

                        @script
                        <script>
                            $js('test', (param1, param2) => {
                                window.test = `through backend js method with params: ${param1}, ${param2}`
                            })
                        </script>
                        @endscript
                    HTML;
                }
            }
        )
        ->waitForLivewire()->click('@save')
        ->assertScript('window.test === "through backend js method with params: foo, bar"')
        ;
    }
}
