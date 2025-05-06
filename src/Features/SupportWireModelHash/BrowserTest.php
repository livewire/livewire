<?php

namespace Livewire\Features\SupportWireModelHash;

use Illuminate\Support\Facades\View;
use Livewire\Livewire;
use Livewire\Wireable;
use PHPUnit\Framework\Assert;
use RuntimeException;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            View::addNamespace('test-views', __DIR__ . '/test-views');
        };
    }

    public function test_hash_wire_model_live_updates_and_hides_attribute()
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
                    <input type="text" dusk="input" wire:model.live.hash="person.age" />
                    <span>{{ $person->age }}</span>
                </div>
                HTML;
            }
        })
            ->assertAttributeMissing('@input', 'wire:model.live.hash')
            ->tap(function ($browser) {
                $value = $browser->attribute('@input', 'wire:model.live');

                Assert::assertMatchesRegularExpression(
                    '/^[a-zA-Z0-9]+$/',
                    $value,
                );
            })
            ->waitForText('42')
            ->assertSee('42')
            ->type('@input', '43')
            ->waitForText('43')
            ->assertSee('43')
            ->type('@input', '34')
            ->waitForText('34')
            ->assertSee('34')
            ;
    }

    public function test_hash_wire_model_debounce_updates_and_hides_attribute()
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
                    <input type="text" dusk="input" wire:model.live.debounce.300ms.hash="person.age" />
                    <span>{{ $person->age }}</span>
                </div>
                HTML;
            }
        })
            ->assertAttributeMissing('@input', 'wire:model.live.debounce.300ms.hash')
            ->tap(function ($browser) {
                $value = $browser->attribute('@input', 'wire:model.live.debounce.300ms');

                Assert::assertMatchesRegularExpression(
                    '/^[a-zA-Z0-9]+$/',
                    $value,
                );
            })
            ->waitForText('42')
            ->assertSee('42')
            ->type('@input', '43')
            ->waitForText('43')
            ->assertSee('43')
            ->type('@input', '34')
            ->waitForText('34')
            ->assertSee('34')
        ;
    }

    public function test_hash_wire_model_updates_and_hides_attribute_when_using_wire_set()
    {
        Livewire::visit(new class () extends \Livewire\Component {
            public int $age;

            public function mount(): void
            {
                $this->age = 42;
            }

            public function render(): string
            {
                return <<<'HTML'
                <div>
                    <input type="text" dusk="input" wire:model.hash="age" />
                    <button type="button" wire:click="$set('age', 43)" dusk="save">Save</button>
                    <span>{{ $age }}</span>
                </div>
                HTML;
            }
        })
            ->waitForText('42')
            ->assertSee('42')
            ->click('@save')
            ->assertAttributeMissing('@input', 'wire:model.hash')
            ->tap(function ($browser) {
                $value = $browser->attribute('@input', 'wire:model');

                Assert::assertMatchesRegularExpression(
                    '/^[a-zA-Z0-9]+$/',
                    $value,
                );
            })
            ->waitForText('43')
            ->assertSee('43');
    }

    public function test_call_updated_hooks_when_using_hash_wire_model()
    {
        Livewire::visit(new class () extends \Livewire\Component {
            public $age;

            public $output = '';

            public function mount(): void
            {
                $this->age = 42;
            }

            public function updatedAge(): void
            {
                $this->output = 'Updated age to ' . $this->age;
            }

            public function render(): string
            {
                return <<<'HTML'
                <div>
                    <input type="text" dusk="input" wire:model.hash="age" />
                    <button type="button" wire:click="$set('age', 43)" dusk="save">Save</button>
                    <span>{{ $output }}</span>
                </div>
                HTML;
            }
        })
            ->waitForLivewire()
            ->click('@save')
            ->assertAttributeMissing('@input', 'wire:model.hash')
            ->tap(function ($browser) {
                $value = $browser->attribute('@input', 'wire:model');

                Assert::assertMatchesRegularExpression(
                    '/^[a-zA-Z0-9]+$/',
                    $value,
                );
            })
            ->assertSee('Updated age to 43')
        ;
    }

    public function test_wire_model_binding_with_hash_modifier_in_view_components()
    {
        Livewire::visit(new class () extends \Livewire\Component {
            public $age;

            public function mount(): void
            {
                $this->age = 42;
            }

            public function render(): string
            {
                return <<<'HTML'
                <div>
                    <h3>Root</h3>
                    <input type="text" dusk="input" wire:model.live.hash="age" />
                    <span dusk="root.age">{{ $age }}</span>

                    <h3>View</h3>
                    <div>
                        {!! view('test-views::attributes-wire', [
                            'attributes' => new \Illuminate\View\ComponentAttributeBag([
                                'wire:model.hash' => 'age',
                            ]),
                        ])->render() !!}
                    </div>
                </div>
                HTML;
            }
        })
            ->waitForText('42')
            ->assertSee('42')
            ->type('@input', '43')
            ->waitForText('43')
            ->assertSee('43')
            ->assertSeeIn('@view.model','age')
            ->assertSeeIn('@root.age','43')
            ->tap(function ($browser) {
                $value = $browser->attribute('@view.input', 'wire:model.live');

                Assert::assertMatchesRegularExpression(
                    '/^[a-zA-Z0-9]+$/',
                    $value,
                );
            })
            ->type('@view.input', '34')
            ->waitForLivewire()
            ->pause(200)
            ->assertSeeIn('@root.age','34')
            ->assertSeeIn('@view.model','age')
        ;
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
