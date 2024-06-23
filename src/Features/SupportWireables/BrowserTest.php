<?php

namespace Livewire\Features\SupportWireables;

use Livewire\Livewire;
use Livewire\Wireable;
use RuntimeException;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_it_can_update_a_custom_wireable_object()
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

    public function test_it_can_update_a_custom_wireable_via_inputs()
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
