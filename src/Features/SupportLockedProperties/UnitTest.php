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
            #[BaseLocked]
            public $count = 1;

            function increment() { $this->count++; }

            public function render() {
                return '<div></div>';
            }
        })
        ->assertSet('count', 1)
        ->set('count', 2);
    }

    /** @test */
    function cant_deeply_update_locked_property()
    {
        $this->expectExceptionMessage(
            'Cannot update locked property: [foo]'
        );

        Livewire::test(new class extends Component {
            #[BaseLocked]
            public $foo = ['count' => 1];

            function increment() { $this->foo['count']++; }

            public function render() {
                return '<div></div>';
            }
        })
        ->assertSet('foo.count', 1)
        ->set('foo.count', 2);
    }

    /** @test */
    function can_update_locked_property_with_similar_name()
    {
        Livewire::test(new class extends Component {
            #[BaseLocked]
            public $count = 1;

            public $count2 = 1;

            public function render() {
                return '<div></div>';
            }
        })
        ->assertSet('count2', 1)
        ->set('count2', 2);
    }
}
