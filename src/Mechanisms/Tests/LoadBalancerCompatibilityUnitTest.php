<?php

namespace Livewire\Mechanisms\Tests;

use Livewire\Livewire;
use Livewire\Component;
use Tests\TestComponent;

class LoadBalancerCompatibilityUnitTest extends \Tests\TestCase
{
    public function test_component_keys_are_deterministic_across_load_balancers()
    {
        $component = Livewire::test([new class extends Component {
            public function render()
            {
                return '<div> <livewire:child /> </div>';
            }
        },
        'child' => new class extends TestComponent {}
        ]);

        $firstKey = array_keys($component->snapshot['memo']['children'])[0];

        // Clear the view cache to simulate blade views being cached on two different servers...
        \Illuminate\Support\Facades\Artisan::call('view:clear');

        $component = Livewire::test([new class extends Component {
            public function render()
            {
                return '<div> <livewire:child /> </div>';
            }
        },
        'child' => new class extends TestComponent {}
        ]);

        $secondKey = array_keys($component->snapshot['memo']['children'])[0];

        $this->assertEquals($firstKey, $secondKey);
    }

    public function test_deterministic_keys_can_still_be_generated_from_blade_strings_not_files()
    {
        $contentsA = app('blade.compiler')->compileString(<<<'HTML'
        <div>
            <livewire:the-child />
        </div>
        HTML);

        // Reset any internal key counters...
        app('livewire')->flushState();

        $contentsB = app('blade.compiler')->compileString(<<<'HTML'
        <div>
            <livewire:the-child />
        </div>
        HTML);

        $this->assertStringContainsString('lw-540987236-0', $contentsA);
        $this->assertStringContainsString('lw-540987236-0', $contentsB);
    }
}
