<?php

namespace Livewire\Features\SupportTeleporting;

use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

class Test extends TestCase
{
    /** @test */
    public function can_teleport_dom_via_blade_directive()
    {
        Livewire::visit(new class extends Component {
            public function render() { return <<<'HTML'
            <div>
                <div>
                    <span id="foo"></span>
                </div>

                @teleport('#foo')
                    <span>bar</span>
                @endteleport
            </div>
            HTML; }
        })->assertSeeIn('#foo', 'bar');
    }
}
