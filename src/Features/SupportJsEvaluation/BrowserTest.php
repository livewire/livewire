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
}
