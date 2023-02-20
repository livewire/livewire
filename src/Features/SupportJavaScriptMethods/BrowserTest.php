<?php

namespace Livewire\Features\SupportJavaScriptMethods;

use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    public function can_toggle_a_purely_js_property_with_a_purely_js_function()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $show = false;

                #[JavaScript]
                function toggle()
                {
                    return <<<JS
                        this.show = ! this.show;
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
        ->assertDontSee('Toggle Me!')
        ->click('@toggle')
        ->pause(100)
        ->assertSee('Toggle Me!')
        ->click('@toggle')
        ->pause(100)
        ->assertDontSee('Toggle Me!')
        ;
    }
}
