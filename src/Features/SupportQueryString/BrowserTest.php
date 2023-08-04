<?php

namespace Livewire\Features\SupportQueryString;

use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    public function it_does_not_add_null_values_to_the_query_string_array()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                #[Url]
                public ?array $tableFilters = null;

                public function mount()
                {
                    $this->tableFilters = [
                        'filter_1' => [
                            'value' => null,
                        ],
                        'filter_2' => [
                            'value' => null,
                        ],
                        'filter_3' => [
                            'value' => null,
                        ]
                    ];
                }

                public function render() { return <<<'HTML'
                <div>
                    <input wire:model.live="tableFilters.filter_1.value" type="text" dusk="filter_1" />

                    <input wire:model.live="tableFilters.filter_2.value" type="text" dusk="filter_2" />

                    <input wire:model.live="tableFilters.filter_3.value" type="text" dusk="filter_3" />
                </div>
                HTML; }
            },
        ])
        ->assertInputValue('@filter_1', '')
        ->assertInputValue('@filter_2', '')
        ->assertInputValue('@filter_3', '')
        ->assertQueryStringMissing('tableFilters')
        ->type('@filter_1', 'test')
        ->waitForLivewire()
        // This assertQueryStringMissing is here purposely to make this test fail
        // to see the query string in the error message. I don't know of a way to test
        // nested arrays in the query string. 
        // The expected query string should JUST be ?tableFilters[filter_1][value]=test
        // however when you look at the failing test message you'll see that the query string
        // is ?tableFilters[filter_1][value]=test&tableFilters[filter_2][value]=null&tableFilters[filter_3][value]=null.
        ->assertQueryStringMissing('tableFilters');
    }
}
