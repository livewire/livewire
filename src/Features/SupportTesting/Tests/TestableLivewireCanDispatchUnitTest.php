<?php

namespace Livewire\Features\SupportTesting\Tests;

use Livewire\Component;
use Livewire\Livewire;

class TestableLivewireCanDispatchUnitTest extends \Tests\TestCase
{
    /** @test */
    function can_assert_dispatch()
    {
        Livewire::test(DispatchComponent::class)
            ->call('sent')
            ->assertDispatched('my-event', payload: [
                'value_1' => 1,
                'value_2' => 3.45,
                'value_3' => 40.0,
            ]);
    }
}

class DispatchComponent extends Component
{
    function sent()
    {
        $this->dispatch('my-event', payload: [
            'value_1' => 1,
            'value_2' => 3.45,
            'value_3' => 40.0,
        ]);
    }

    function render()
    {
        return '<example>Hello!</example>';
    }
}
