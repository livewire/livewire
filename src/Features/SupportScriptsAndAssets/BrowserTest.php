<?php

namespace Livewire\Features\SupportScriptsAndAssets;

use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    public function can_evaluate_a_script_inside_a_component()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public function render() { return <<<'HTML'
            <div>
                <h1 id="foo"></h1>
            </div>

            @script
            <script>
                document.getElementById('foo').textContent = 'Hello world!'
            </script>
            @endscript
            HTML; }
        })
        ->waitForText('Hello world!')
        ->assertSee('Hello world!')
        ;
    }
}
