<?php

namespace Livewire\Features\SupportTesting\Tests;

use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestComponent;

class TestableLivewireCanDispatchOriginalParamsUnitTest extends \Tests\TestCase
{
    /** @test */
    function can_assert_dispatch_with_number_in_array_values()
    {
        Livewire::test(new class extends TestComponent {
            function sentNumbersInArrayValues()
            {
                $this->dispatch('my-event', payload: [ ['value' => 40.0] ]);
            }

            function dontSentNumbersInArrayValues()
            {
                // ...
            }

            function render() {
                return <<<'HTML'
                    <div></div>
                HTML;
            }
        })
            ->call('sentNumbersInArrayValues')
            ->assertDispatched('my-event', payload: [ ['value' => 40.0] ])
            ->call('dontSentNumbersInArrayValues')
            ->assertNotDispatched('my-event', payload: [ ['value' => 40.0] ])
        ;
    }

    /** @test */
    function can_assert_dispatch_with_datetime_in_array_values()
    {
        $date = Carbon::create(2024, 01, 1, 11, 11, 58);

        Carbon::setTestNow($date);

        Livewire::test(new class extends TestComponent {
            function sentDateTimeInArrayValues()
            {
                $this->dispatch('my-event', payload: [
                    'datetime' => Carbon::create(2024, 01, 1, 11, 11, 58)
                ]);
            }

            function dontSentDateTimeInArrayValues()
            {
                // ...
            }

            function render() {
                return <<<'HTML'
                    <div></div>
                HTML;
            }
        })
            ->call('sentDateTimeInArrayValues')
            ->assertDispatched('my-event', payload: [
                'datetime' => $date
            ])
            ->assertDispatched('my-event', payload: [
                'datetime' => '2024-01-01T11:11:58.000000Z'
            ])
        ;
    }
}
