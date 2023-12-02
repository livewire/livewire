<?php

namespace Livewire\Features\SupportDataBinding;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    /** @test */
    function can_use_wire_dirty()
    {
        Livewire::visit(new class extends Component {
            public $prop = false;

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk="checkbox" type="checkbox" wire:model="prop" value="true"  />

                        <div wire:dirty>Unsaved changes...</div>
                        <div wire:dirty.remove>The data is in-sync...</div>
                    </div>
                BLADE;
            }
        })
            ->assertSee('The data is in-sync...')
            ->check('@checkbox')
            ->assertDontSee('The data is in-sync')
            ->assertSee('Unsaved changes...')
            ->uncheck('@checkbox')
            ->assertSee('The data is in-sync...')
            ->assertDontSee('Unsaved changes...')
        ;
    }

    /** @test */
    public function can_display_correct_option_in_a_select_input_when_options_have_changed()
    {
        Livewire::visit(new class extends Component {
            public $country = 'US';
            public $state = 'TX';

            public $countries = [
                'US' => [
                    'code' => 'US',
                    'name' => 'United States',
                    'states' => [
                        'CA' => 'California',
                        'TX' => 'Texas',
                        'NY' => 'New York',
                    ],
                ],
                'AU' => [
                    'code' => 'AU',
                    'name' => 'Australia',
                    'states' => [
                        'QLD' => 'Queensland',
                        'SA' => 'South Australia',
                        'NSW' => 'New South Wales',
                    ],
                ],
            ];

            public function getStateOptionsProperty()
            {
                if ($this->country === null|| $this->country == '') {
                    return null;
                }

                return $this->countries[$this->country]['states'];
            }

            public function change()
            {
                $this->country = 'AU';
                $this->state = 'NSW';
            }

            public function render()
            {
                return <<< 'HTML'
                    <div>
                        <div>
                            Country: <span dusk="country">{{ $country }}</span>
                        </div>

                        <select wire:model.live="country" dusk="countrySelect">
                            <option value="null" disabled>Select a country</option>
                            @foreach ($this->countries as $countryOption)
                                <option value="{{ $countryOption['code'] }}">{{ $countryOption['name'] }}</option>
                            @endforeach
                        </select>

                        <div>
                            State: <span dusk="state">{{ $state }}</span>
                        </div>

                        <div>
                            @if ($this->stateOptions === null)
                                <input type="text" name="state" wire:model.live="state" />
                            @else
                                <select wire:model.live="state" dusk="stateSelect">
                                    <option value="null" disabled>Select a state</option>
                                    @foreach ($this->stateOptions as $abbreviation => $name)
                                        <option value="{{ $abbreviation }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        <button type="button" wire:click="change" dusk="change">Change</button>
                    </div>
                HTML;
            }
        })
            ->assertSeeIn('@country', 'US')
            ->assertSeeIn('@state', 'TX')
            ->assertSelected('@countrySelect', 'US')
            ->assertSelected('@stateSelect', 'TX')
            ->waitForLivewire()->click('@change')
            ->assertSeeIn('@country', 'AU')
            ->assertSeeIn('@state', 'NSW')
            ->assertSelected('@countrySelect', 'AU')
            ->assertSelected('@stateSelect', 'NSW')
            ;
    }
}
