<?php

namespace Livewire\Features\SupportLazyLoading;

use Livewire\Component;
use Livewire\DuskTestCase;

class Test extends DuskTestCase
{
    /** @test */
    public function can_lazy_load_a_blade_partial()
    {
        $this->visit(new class extends Component {
            public $show = false;

            public function toggle()
            {
                sleep(.25);

                $this->show = ! $this->show;
            }

            public function render() {
                return <<<'HTML'
                <div>
                    <button wire:click="toggle" dusk="toggle">Toggle</button>

                    @eager('toggle')
                        @if ($show)
                            <div dusk="target">Hello World</div>
                        @endif
                    @endeager
                </div>
                HTML;
            }
        }, function ($browser) {
            $browser->assertNotPresent('@target');
            $browser->click('@toggle');
            $browser->pause(15);
            $browser->assertPresent('@target');
        });
    }
}
