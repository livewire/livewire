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

    public function test_replacing_a_rich_value_triggers_alpine_reactivity()
    {
        Livewire::visit(new class extends Component {
            public Carbon $date;

            public function mount(): void
            {
                $this->date = Carbon::parse('2021-01-01 00:00:00', 'UTC');
            }

            public function advance(): void
            {
                $this->date = $this->date->addYear();
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

                    <span dusk="text" x-text="$wire.date.getUTCFullYear()"></span>

                    <button dusk="assign" type="button" x-on:click="$wire.date = new Date('2025-06-06T00:00:00Z')">Assign</button>

                    <button dusk="advance" type="button" wire:click="advance">Advance</button>
                </div>
                HTML;
            }
        })
        // Initial hydration renders through the effect...
        ->waitForTextIn('@text', '2021')
        // A client-side assignment fires the effect without any request...
        ->click('@assign')
        ->waitForTextIn('@text', '2025')
        // A server-side change patches the reactive property and fires the effect...
        ->waitForLivewire()->click('@advance')
        ->waitForTextIn('@text', '2026');
    }

    public function test_mutating_a_date_in_place_is_not_reactive_but_is_still_sent_to_the_server()
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

                    <span dusk="text" x-text="$wire.date.getUTCFullYear()"></span>

                    <span dusk="server">{{ $date->year }}</span>

                    <button dusk="mutate" type="button" x-on:click="$wire.date.setUTCFullYear(2030)">Mutate</button>

                    <button dusk="refresh" type="button" wire:click="$refresh">Refresh</button>
                </div>
                HTML;
            }
        })
        ->waitForTextIn('@text', '2021')
        // In-place mutation doesn't replace the property, so no effect fires.
        // (Dates can't be proxied, same as Vue's documented behavior.)
        ->click('@mutate')
        ->pause(100)
        ->assertSeeIn('@text', '2021')
        // But state diffing still detects the mutation and sends it up...
        ->waitForLivewire()->click('@refresh')
        ->assertSeeIn('@server', '2030');
    }

    public function test_wire_dirty_tracks_rich_values()
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

                    <div wire:dirty wire:target="date">Unsaved changes...</div>

                    <button dusk="mutate" type="button" x-on:click="$wire.date = new Date('2030-05-05T00:00:00Z')">Mutate</button>

                    <button dusk="revert" type="button" x-on:click="$wire.date = new Date('2021-01-01T00:00:00Z')">Revert</button>
                </div>
                HTML;
            }
        })
        ->assertDontSee('Unsaved changes...')
        ->click('@mutate')
        ->waitForText('Unsaved changes...')
        // Reverting to an equal-but-different Date instance should read as clean...
        ->click('@revert')
        ->waitUntilMissingText('Unsaved changes...');
    }

    public function test_url_bound_rich_values_are_dehydrated_into_the_query_string()
    {
        Livewire::visit(new class extends Component {
            #[\Livewire\Attributes\Url]
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

                    <button dusk="change" type="button" x-on:click="$wire.$set('date', new Date('2030-05-05T00:00:00Z'))">Change</button>
                </div>
                HTML;
            }
        })
        ->waitForLivewire()->click('@change')
        ->assertSeeIn('@output', '2030-05-05')
        // The URL must carry the raw wire format, not a stringified Date object...
        ->assertScript('new URLSearchParams(window.location.search).get("date")', '2030-05-05T00:00:00.000Z');
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

    public function test_a_rich_value_can_own_wire_model_element_binding_through_its_bind_to_contract()
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
                                hydrate: (value) => {
                                    let date = new Date(value)

                                    date.bindTo = ({ el, get, set, notify }) => {
                                        Alpine.bind(el, {
                                            ['x-effect']() { el.value = String(get().getUTCFullYear()) },
                                            ['@input']() {
                                                set(new Date(Date.UTC(Number(el.value), 0, 1)))

                                                notify()
                                            },
                                        })
                                    }

                                    return date
                                },
                                dehydrate: (value) => value.toISOString(),
                            })
                        })
                    </script>

                    <input type="text" dusk="input" x-ref="year" wire:model="date" />

                    <button dusk="change" type="button" x-on:click="$refs.year.value = '2030'; $refs.year.dispatchEvent(new Event('input', { bubbles: true }))">Change</button>

                    <button dusk="refresh" type="button" wire:click="$refresh">Refresh</button>

                    <span dusk="output">{{ $date->year }}</span>
                </div>
                HTML;
            }
        })
        // The synth's bind owns the element: it renders the Date as a year
        // instead of x-model's default string binding...
        ->assertValue('@input', '2021')
        ->click('@change')
        ->waitForLivewire()->click('@refresh')
        ->assertSeeIn('@output', '2030');
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
