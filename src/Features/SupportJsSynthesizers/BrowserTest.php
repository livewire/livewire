<?php

namespace Livewire\Features\SupportJsSynthesizers;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Livewire;
use Livewire\Wireable;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_a_js_synth_hydrates_backend_values_into_rich_js_values()
    {
        Livewire::visit(new class extends Component {
            public Carbon $date;

            public function mount(): void
            {
                $this->date = Carbon::parse('2021-01-01 00:00:00', 'UTC');
            }

            public function render(): string
            {
                return <<<'HTML'
                <div>
                    <script>
                        document.addEventListener('livewire:init', () => {
                            Livewire.synth('cbn', {
                                match: (value) => value instanceof Date,
                                hydrate: (value) => new Date(value),
                                dehydrate: (value) => value.toISOString(),
                            })
                        })
                    </script>

                    <button dusk="check" type="button" x-on:click="$refs.type.textContent = $wire.date instanceof Date ? 'is-date:' + $wire.date.getUTCFullYear() : 'not-date'">Check</button>

                    <span x-ref="type" dusk="type"></span>
                </div>
                HTML;
            }
        })
        ->click('@check')
        ->assertSeeIn('@type', 'is-date:2021');
    }

    public function test_assigning_a_fresh_rich_value_sends_its_dehydrated_form_to_the_server()
    {
        Livewire::visit(new class extends Component {
            public Carbon $date;

            public function mount(): void
            {
                $this->date = Carbon::parse('2021-01-01 00:00:00', 'UTC');
            }

            public function render(): string
            {
                return <<<'HTML'
                <div>
                    <script>
                        document.addEventListener('livewire:init', () => {
                            Livewire.synth('cbn', {
                                match: (value) => value instanceof Date,
                                hydrate: (value) => new Date(value),
                                dehydrate: (value) => value.toISOString(),
                            })
                        })
                    </script>

                    <span dusk="output">{{ $date->toDateString() }}</span>

                    <button dusk="mutate" type="button" x-on:click="$wire.date = new Date('2030-05-05T00:00:00Z')">Mutate</button>

                    <button dusk="refresh" type="button" wire:click="$refresh">Refresh</button>

                    <button dusk="set" type="button" x-on:click="$wire.$set('date', new Date('2032-03-03T00:00:00Z'))">Set</button>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@output', '2021-01-01')
        ->click('@mutate')
        ->waitForLivewire()->click('@refresh')
        ->assertSeeIn('@output', '2030-05-05')
        ->waitForLivewire()->click('@set')
        ->assertSeeIn('@output', '2032-03-03');
    }

    public function test_unchanged_rich_values_survive_a_round_trip_with_their_identity_preserved()
    {
        Livewire::visit(new class extends Component {
            public Carbon $date;

            public function mount(): void
            {
                $this->date = Carbon::parse('2021-01-01 00:00:00', 'UTC');
            }

            public function render(): string
            {
                return <<<'HTML'
                <div>
                    <script>
                        document.addEventListener('livewire:init', () => {
                            Livewire.synth('cbn', {
                                match: (value) => value instanceof Date,
                                hydrate: (value) => new Date(value),
                                dehydrate: (value) => value.toISOString(),
                            })
                        })
                    </script>

                    <button dusk="store" type="button" x-on:click="window.__dateRef = $wire.date">Store</button>

                    <button dusk="refresh" type="button" wire:click="$refresh">Refresh</button>

                    <button dusk="compare" type="button" x-on:click="$refs.same.textContent = window.__dateRef === $wire.date ? 'same' : 'different'">Compare</button>

                    <span x-ref="same" dusk="same"></span>
                </div>
                HTML;
            }
        })
        ->click('@store')
        ->waitForLivewire()->click('@refresh')
        ->click('@compare')
        ->assertSeeIn('@same', 'same');
    }

    public function test_rich_values_dispatched_to_server_side_listeners_are_dehydrated()
    {
        Livewire::visit(new class extends Component {
            public Carbon $date;

            public function mount(): void
            {
                $this->date = Carbon::parse('2021-01-01 00:00:00', 'UTC');
            }

            #[\Livewire\Attributes\On('date-picked')]
            public function handleDatePicked($value): void
            {
                $this->date = Carbon::parse($value);
            }

            public function render(): string
            {
                return <<<'HTML'
                <div>
                    <script>
                        document.addEventListener('livewire:init', () => {
                            Livewire.synth('cbn', {
                                match: (value) => value instanceof Date,
                                hydrate: (value) => new Date(value),
                                dehydrate: (value) => value.toISOString(),
                            })
                        })
                    </script>

                    <span dusk="output">{{ $date->toDateString() }}</span>

                    <button dusk="dispatch" type="button" x-on:click="$wire.$dispatch('date-picked', { value: new Date('2034-09-09T00:00:00Z') })">Dispatch</button>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@output', '2021-01-01')
        ->waitForLivewire()->click('@dispatch')
        ->assertSeeIn('@output', '2034-09-09');
    }

    public function test_a_custom_rich_object_supports_nested_data_binding()
    {
        Livewire::visit(new class extends Component {
            public AddressDto $address;

            public function mount(): void
            {
                $this->address = new AddressDto('123 Main St', 'Anytown');
            }

            public function render(): string
            {
                return <<<'HTML'
                <div>
                    <script>
                        class JsAddress {
                            constructor({ street, city }) {
                                this.street = street
                                this.city = city
                            }

                            get full() {
                                return this.street + ', ' + this.city
                            }
                        }

                        document.addEventListener('livewire:init', () => {
                            Livewire.synth('wrbl', {
                                match: (value) => value instanceof JsAddress,
                                hydrate: (value) => new JsAddress(value),
                                dehydrate: (value) => ({ street: value.street, city: value.city }),
                            })
                        })
                    </script>

                    <input dusk="street" type="text" wire:model.live="address.street" />

                    <span dusk="server">{{ $address->street }}</span>

                    <span dusk="full" x-text="$wire.address.full"></span>
                </div>
                HTML;
            }
        })
        ->waitForTextIn('@full', '123 Main St, Anytown')
        ->type('@street', '456 Oak Ave')
        ->waitForTextIn('@server', '456 Oak Ave')
        ->assertSeeIn('@full', '456 Oak Ave, Anytown');
    }

    public function test_rich_values_passed_as_action_parameters_are_dehydrated()
    {
        Livewire::visit(new class extends Component {
            public Carbon $date;

            public function mount(): void
            {
                $this->date = Carbon::parse('2021-01-01 00:00:00', 'UTC');
            }

            public function setDate($value): void
            {
                $this->date = Carbon::parse($value);
            }

            public function render(): string
            {
                return <<<'HTML'
                <div>
                    <script>
                        document.addEventListener('livewire:init', () => {
                            Livewire.synth('cbn', {
                                match: (value) => value instanceof Date,
                                hydrate: (value) => new Date(value),
                                dehydrate: (value) => value.toISOString(),
                            })
                        })
                    </script>

                    <span dusk="output">{{ $date->toDateString() }}</span>

                    <button dusk="call" type="button" x-on:click="$wire.setDate(new Date('2033-07-07T00:00:00Z'))">Call</button>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@output', '2021-01-01')
        ->waitForLivewire()->click('@call')
        ->assertSeeIn('@output', '2033-07-07');
    }
}

class AddressDto implements Wireable
{
    public function __construct(
        public string $street,
        public string $city,
    ) {}

    public function toLivewire()
    {
        return ['street' => $this->street, 'city' => $this->city];
    }

    public static function fromLivewire($value)
    {
        return new static($value['street'], $value['city']);
    }
}
