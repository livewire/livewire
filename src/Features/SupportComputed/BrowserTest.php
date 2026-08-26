<?php

namespace Livewire\Features\SupportComputed;

use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // The cache key for #[Computed(cache: true)] is derived from the
        // component name, so cached values from previous test runs in the
        // same process can poison the initial state of these tests.
        Cache::flush();
    }

    public function test_can_persist_computed_between_requests_and_bust_them()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            protected $thing = 'hey';

            #[Computed(persist: true)]
            public function foo() {
                $this->count++;

                return 'bar';
            }

            function unset()
            {
                unset($this->foo);
            }

            function render()
            {
                $noop = $this->foo;

                return <<<'HTML'
                <div>
                    <button wire:click="$refresh" dusk="refresh">refresh</button>
                    <button wire:click="unset" dusk="unset">unset</button>

                    <div dusk="count">{{ $count }}</div>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@count', '1')
        ->waitForLivewire()->click('@refresh')
        ->assertSeeIn('@count', '1')
        ->waitForLivewire()->click('@unset')
        ->assertSeeIn('@count', '2')
        ->waitForLivewire()->click('@refresh')
        ->assertSeeIn('@count', '2');
    }

    public function test_can_cache_computed_properties_for_all_components_and_bust_them()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            #[Computed(cache: true)]
            public function foo() {
                return $this->count;
            }

            function increment()
            {
                $this->count++;
                unset($this->foo);
            }

            function render()
            {
                $noop = $this->foo;

                return <<<'HTML'
                <div>
                    <button wire:click="$refresh" dusk="refresh">refresh</button>
                    <button wire:click="increment" dusk="increment">unset</button>

                    <div dusk="count">{{ $this->foo }}</div>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@count', '0')
        ->waitForLivewire()->click('@increment')
        ->assertSeeIn('@count', '1')
        ->refresh()
        ->assertSeeIn('@count', '1');
    }

    public function test_computed_properties_cannot_be_set_on_front_end()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            #[Computed]
            public function foo() {
                return 'bar';
            }

            function render()
            {
                return <<<'HTML'
                <div>
                    <p>Foo: <span dusk="foo">{{ $this->foo }}</span></p>
                    <button wire:click="$set('foo', 'other')" dusk="change-foo">Change Foo</button>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@foo', 'bar')
        ->waitForLivewire()->click('@change-foo')
        ->assertSeeIn('@foo', 'bar')
        ;
    }

    public function test_exception_from_computed_properties_during_action_stops_further_execution()
    {
        Livewire::visit(new class extends Component {
            public bool $handled = false;
            public string $label = 'initial';

            #[Computed]
            public function failingValue(): string
            {
                throw new \RuntimeException('computed failed in action');
            }

            public function save()
            {
                $value = $this->failingValue;

                // Must not run when stopPropagation() is called.
                $this->label = $value;
            }

            public function exception($e, $stopPropagation): void
            {
                if ($e instanceof \RuntimeException) {
                    $this->handled = true;
                    $stopPropagation();
                }
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="save" dusk="save">Save</button>
                    <div dusk="label">{{ $label }}</div>
                    <div dusk="handled">{{ $handled ? 'yes' : 'no' }}</div>
                </div>
                HTML;
            }
        })
            ->assertSeeIn('@label', 'initial')
            ->assertSeeIn('@handled', 'no')
            ->waitForLivewire()->click('@save')
            ->assertSeeIn('@label', 'initial')
            ->assertSeeIn('@handled', 'yes');
    }

    public function test_exception_from_computed_properties_during_mount_still_renders_after_stop()
    {
        Livewire::visit(new class extends Component {
            public bool $handled = false;
            public string $label = 'initial';

            public function mount()
            {
                $value = $this->failingValue;

                $this->label = is_string($value) ? $value : 'unchanged';
            }

            #[Computed]
            public function failingValue(): string
            {
                throw new \RuntimeException('computed failed in mount');
            }

            public function exception($e, $stopPropagation): void
            {
                if ($e instanceof \RuntimeException) {
                    $this->handled = true;
                    $stopPropagation();
                }
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <div dusk="label">{{ $label }}</div>
                    <div dusk="handled">{{ $handled ? 'yes' : 'no' }}</div>
                </div>
                HTML;
            }
        })
            ->assertSeeIn('@label', 'initial')
            ->assertSeeIn('@handled', 'yes');
    }
}
