<?php

namespace Livewire\Features\SupportTesting\Tests;

use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\Livewire;

class TestableLivewireCanDispatchUnitTest extends \Tests\TestCase
{
    /** @test */
    function can_assert_dispatch_with_number_in_array_values()
    {
        Livewire::test(DispatchComponent::class)
            ->call('sentNumbersInArrayValues')
            ->assertDispatched('my-event', payload: [
                [
                    'id' => 1,
                    'value' => 10
                ],
                [
                    'id' => 1,
                    'value' => 34.5
                ],
                [
                    'id' => 1,
                    'value' => 40.0000
                ],
            ]);
    }

    /** @test */
    function can_assert_dispatch_with_datetime_in_array_values()
    {
        $date = Carbon::create(2023, 12, 1, 10, 10, 59);

        Carbon::setTestNow($date);

        Livewire::test(DispatchComponent::class)
            ->call('sentDateTimeInArrayValues')
            ->assertDispatched('my-event', payload: [
                'datetime' => $date
            ]);
    }
}

class DispatchComponent extends Component
{
    function sentNumbersInArrayValues()
    {
        $this->dispatch('my-event', payload: [
            [
                'id' => 1,
                'value' => 10
            ],
            [
                'id' => 1,
                'value' => 34.5
            ],
            [
                'id' => 1,
                'value' => 40.0000
            ],
        ]);
    }

    function sentDateTimeInArrayValues()
    {
        $this->dispatch('my-event', payload: [
            'datetime' => Carbon::create(2023, 12, 1, 10, 10, 59)
        ]);
    }

    function render()
    {
        return '<example>Hello!</example>';
    }
}
