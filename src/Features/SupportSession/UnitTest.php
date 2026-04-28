<?php

namespace Livewire\Features\SupportSession;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session as FacadesSession;
use Livewire\Attributes\Session;
use Livewire\Component;
use Tests\TestCase;
use Livewire\Livewire;
use Tests\TestComponent;

class UnitTest extends TestCase
{
    public function test_it_creates_a_session_key()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[Session]
            public $count = 0;

            function render() {
                return <<<'HTML'
                    <div>foo{{ $count }}</div>
                HTML;
            }
        });

        $this->assertTrue(FacadesSession::has('lw'.crc32($component->instance()->getName().'count')));
    }

    public function test_it_creates_a_dynamic_session_id()
    {
        Livewire::test(new class extends TestComponent {
            public $post = ['id' => 2];

            #[Session(key: 'baz.{post.id}')]
            public $count = 0;

            function render() {
                return <<<'HTML'
                    <div>foo{{ $count }}</div>
                HTML;
            }
        });

        $this->assertTrue(FacadesSession::has('baz.2'));
    }

    public function test_it_persists_a_collection_property_through_a_json_serialised_session()
    {
        $this->forceJsonSessionSerialization();

        Livewire::test(ComponentWithSessionCollection::class)
            ->call('add', 1)
            ->call('add', 2);

        $this->roundTripSession();

        Livewire::test(ComponentWithSessionCollection::class)
            ->assertSet('list', collect([1, 2]))
            ->call('add', 3)
            ->assertSet('list', collect([1, 2, 3]));
    }

    public function test_it_persists_a_carbon_property_through_a_json_serialised_session()
    {
        $this->forceJsonSessionSerialization();

        $date = Carbon::parse('2026-01-15 10:00:00');

        Livewire::test(ComponentWithSessionCarbon::class)
            ->set('when', $date);

        $this->roundTripSession();

        Livewire::test(ComponentWithSessionCarbon::class)
            ->assertSet('when', $date);
    }

    public function test_it_persists_a_primitive_property_through_a_json_serialised_session()
    {
        $this->forceJsonSessionSerialization();

        Livewire::test(ComponentWithSessionCount::class)
            ->call('increment')
            ->call('increment');

        $this->roundTripSession();

        Livewire::test(ComponentWithSessionCount::class)
            ->assertSet('count', 2);
    }

    protected function forceJsonSessionSerialization(): void
    {
        config(['session.serialization' => 'json']);

        // Rebuild the store so the new serialisation config takes effect...
        app('session')->forgetDrivers();
    }

    protected function roundTripSession(): void
    {
        // Force a real json_encode + json_decode cycle on the session...
        session()->save();
        session()->start();
    }
}

class ComponentWithSessionCollection extends Component
{
    #[Session]
    public ?Collection $list = null;

    public function mount(): void
    {
        $this->list ??= collect();
    }

    public function add(int $value): void
    {
        $this->list->push($value);
    }

    public function render()
    {
        return '<div>{{ $list->implode(",") }}</div>';
    }
}

class ComponentWithSessionCarbon extends Component
{
    #[Session]
    public ?Carbon $when = null;

    public function render()
    {
        return '<div>{{ $when }}</div>';
    }
}

class ComponentWithSessionCount extends Component
{
    #[Session]
    public int $count = 0;

    public function increment(): void
    {
        $this->count++;
    }

    public function render()
    {
        return '<div>{{ $count }}</div>';
    }
}
