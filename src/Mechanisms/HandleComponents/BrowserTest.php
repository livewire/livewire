<?php

namespace Livewire\Mechanisms\HandleComponents;

use Livewire\Attributes\Computed;
use Illuminate\View\ViewException;
use Livewire\Component;
use Livewire\Livewire;
use Livewire\Wireable;
use RuntimeException;

class BrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    public function corrupt_component_payload_exception_is_no_longer_thrown_from_data_incompatible_with_javascript()
    {
        Livewire::visit(new class () extends \Livewire\Component {
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

    /** @test */
    public function it_converts_empty_strings_to_null_for_integer_properties()
    {
        Livewire::visit(new class () extends \Livewire\Component {
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

    /** @test */
    public function it_uses_the_synthesizers_for_multiple_types_property_updates()
    {
        Livewire::visit(new class () extends \Livewire\Component {
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

    /** @test */
    public function it_uses_the_synthesizers_for_enum_property_updates_when_initial_state_is_null()
    {
        Livewire::visit(new class () extends \Livewire\Component {
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

    /** @test */
    public function it_can_update_a_custom_wireable_object()
    {
        Livewire::visit(new class () extends \Livewire\Component {
            public Person $person;
            
            public function mount(): void
            {
                $this->person = new Person('Jæja', 42);
            }

            public function render(): string
            {
                return <<<'HTML'
                <div>
                    <button type="button" wire:click="$set('person', {'name': 'foo', 'age': 43})" dusk="button">Button</button>
                    <span>{{ $person->age }}</span
                </div>
                HTML;
            }
        })
        ->assertSee('42')
        ->waitForLivewire()->click('@button')
        ->assertSee('43');
    }

    /** @test */
    public function it_can_update_a_custom_wireable_via_inputs()
    {
        Livewire::visit(new class () extends \Livewire\Component {
            public Person $person;
            
            public function mount(): void
            {
                $this->person = new Person('Jæja', 42);
            }

            public function render(): string
            {
                return <<<'HTML'
                <div>
                    <input type="text" dusk="age" wire:model.live="person.age" />
                    <span>{{ $person->age }}</span>
                </div>
                HTML;
            }
        })
        ->waitForText('42')
        ->assertSee('42')
        ->type('@age', '43')
        ->waitForText('43')
        ->assertSee('43');
    }
}

enum Suit: string
{
    case Hearts = 'H';

    case Diamonds = 'D';

    case Clubs = 'C';

    case Spades = 'S';
}

class Person implements Wireable
{
    public function __construct(
        public string $name,
        public int $age
    ) {

    }
    public function toLivewire()
    {
        return ['name' => $this->name, 'age' => $this->age];
    }

    public static function fromLivewire($value)
    {
        if (! is_array($value)) {
            throw new RuntimeException("Can't fromLivewire without it being an array.");
        }

        return new self($value['name'], (int) $value['age']);
    }
}
