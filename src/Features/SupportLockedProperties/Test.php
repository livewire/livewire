<?php

namespace Livewire\Features\SupportLockedProperties;

use Livewire\Livewire;
use Livewire\Component;

class UnitTest extends \Tests\TestCase
{
    /** @test */
    function cant_update_locked_property()
    {
        $this->expectExceptionMessage(
            'Cannot update locked property: [count]'
        );

        Livewire::test(new class extends Component {
            #[Locked]
            public $count = 1;

            function increment() { $this->count++; }

            public function render() {
                return '<div></div>';
            }
        })
        ->assertSet('count', 1)
        ->set('count', 2);
    }
}
