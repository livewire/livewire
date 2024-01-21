<?php

namespace Livewire\Mechanisms\Tests;

use Livewire\Livewire;
use Livewire\Component;

class LoadBalancerCompatibilityUnitTest extends \Tests\TestCase
{
    /** @test */
    public function component_keys_are_deterministic_across_load_balancers()
    {
        $component = Livewire::test([new class extends Component {
            public function render()
            {
                return '<div> <livewire:child /> </div>';
            }
        },
        'child' => new class extends Component {
            public function render()
            {
                return '<div></div>';
            }
        }]);

        $firstKey = array_keys($component->snapshot['memo']['children'])[0];

        // Clear the view cache to simulate blade views being cached on two different servers...
        \Illuminate\Support\Facades\Artisan::call('view:clear');

        $component = Livewire::test([new class extends Component {
            public function render()
            {
                return '<div> <livewire:child /> </div>';
            }
        },
        'child' => new class extends Component {
            public function render()
            {
                return '<div></div>';
            }
        }]);

        $secondKey = array_keys($component->snapshot['memo']['children'])[0];

        $this->assertEquals($firstKey, $secondKey);
    }
}
