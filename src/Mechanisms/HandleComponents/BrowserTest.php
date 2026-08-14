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

    public function test_it_does_not_send_an_invalid_root_property_path_when_the_key_order_drifts()
    {
        // A deploy can add a public property to a component that a user already
        // has open. The server snapshot then lists the properties in a different
        // ORDER than the browser's ephemeral object, which appends unknown keys
        // at the end. Previously diff() consolidated that at the root, sending
        // the entire state keyed by an empty string, which the server resolved
        // to an empty property name and rejected with a
        // PublicPropertyNotFoundException — wedging the component until reload.
        Livewire::visit(new class extends \Livewire\Component {
            public $first = 'foo';

            public $second = 'bar';

            public $third = 'baz';

            public $count = 0;

            public function increment()
            {
                $this->count++;
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <span dusk="count">{{ $count }}</span>

                    <button type="button" wire:click="increment" dusk="increment">Increment</button>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@count', '0')

        /**
         * Move the first root key to the end of the ephemeral object, so its key
         * order no longer matches the canonical (server) snapshot.
         */
        ->tap(function ($b) {
            $b->script(<<<'JS'
            let component = window.Livewire.all()[0]
            let key = Object.keys(component.ephemeral)[0]
            let value = component.ephemeral[key]

            delete component.ephemeral[key]

            component.ephemeral[key] = value
            JS);
        })

        ->waitForLivewire()->click('@increment')
        ->assertSeeIn('@count', '1')

        /**
         * The component keeps working on subsequent requests too — the bug
         * wedged every following commit, not just the first.
         */
        ->waitForLivewire()->click('@increment')
        ->assertSeeIn('@count', '2')
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

