<?php

namespace Livewire\Mechanisms\HandleComponents;

use Livewire\Attributes\Computed;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_corrupt_component_payload_exception_is_no_longer_thrown_from_data_incompatible_with_javascript()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public $subsequentRequest = false;

            public $negativeZero = -0;

            public $associativeArrayWithStringAndNumericKeys = [
                '2' => 'two',
                'three' => 'three',
                1 => 'one',
            ];

            public $unorderedNumericArray = [
                3 => 'three',
                1 => 'one',
                2 => 'two'
            ];

            public $integerLargerThanJavascriptsMaxSafeInteger = 999_999_999_999_999_999;

            public $unicodeString = 'â';

            public $arrayWithUnicodeString = ['â'];

            public $recursiveArrayWithUnicodeString = ['â', ['â']];

            public function hydrate()
            {
                $this->subsequentRequest = true;
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <div>
                        @if ($subsequentRequest)
                            Subsequent request
                        @else
                            Initial request
                        @endif
                    </div>

                    <div>
                        <span dusk="negativeZero">{{ $negativeZero }}</span>
                    </div>

                    <button type="button" wire:click="$refresh" dusk="refresh">Refresh</button>
                </div>
                HTML;
            }
        })
        ->assertSee('Initial request')
        ->waitForLivewire()->click('@refresh')
        ->assertSee('Subsequent request')
        ;
    }

    public function test_it_converts_empty_strings_to_null_for_integer_properties()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public ?int $number = 5;

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input type="text" wire:model.live="number" dusk="numberInput" />
                    <div dusk="number">{{ $number }}</div>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@number', 5)
        ->waitForLivewire()->keys('@numberInput', '{backspace}')
        ->assertSeeNothingIn('@number', '')
        ;
    }

    public function test_it_uses_the_synthesizers_for_multiple_types_property_updates()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public string|int $localValue = 15;

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input type="text" dusk="localInput" wire:model.live.debounce.100ms="localValue">

                    <span dusk="localValue">{{ $localValue }}</span>
                </div>
                HTML;
            }
        })
        ->waitForText(15)
        ->assertSee(15)

        ->type('@localInput', 25)
        ->waitForText(25)
        ->assertSee(25)
        ;
    }

    public function test_it_uses_the_synthesizers_for_enum_property_updates_when_initial_state_is_null()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public Suit $selected;

            #[Computed]
            public function cases()
            {
                return Suit::cases();
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <select wire:model.live="selected" dusk="selectInput">
                        @foreach($this->cases() as $suit)
                            <option value="{{ $suit->value }}">{{ $suit }}</option>
                        @endforeach
                    </select>

                    <span dusk="selected">{{ $selected }}</span>
                </div>
                HTML;
            }
        })
        ->assertSeeNothingIn('@selected')
        ->waitForLivewire()->select('@selectInput', 'D')
        ->assertSeeIn('@selected', 'D')
        ;
    }
}

enum Suit: string
{
    case Hearts = 'H';

    case Diamonds = 'D';

    case Clubs = 'C';

    case Spades = 'S';
}

