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

    public function test_it_persists_typed_properties_through_a_json_serialised_session()
    {
        config(['session.serialization' => 'json']);

        // Rebuild the store so the new serialisation config takes effect...
        app('session')->forgetDrivers();

        $date = Carbon::parse('2026-01-15 10:00:00');

        Livewire::test(ComponentWithSessionProperties::class)
            ->call('add', 1)
            ->call('add', 2)
            ->set('when', $date)
            ->call('increment');

        // Force a real json_encode + json_decode cycle on the session...
        session()->save();
        session()->start();

        Livewire::test(ComponentWithSessionProperties::class)
            ->assertSet('list', collect([1, 2]))
            ->assertSet('when', $date)
            ->assertSet('count', 1);
    }
}

class ComponentWithSessionProperties extends Component
{
    #[Session]
    public ?Collection $list = null;

    #[Session]
    public ?Carbon $when = null;

    #[Session]
    public int $count = 0;

    public function mount(): void
    {
        $this->list ??= collect();
    }

    public function add(int $value): void
    {
        $this->list->push($value);
    }

    public function increment(): void
    {
        $this->count++;
    }

    public function render()
    {
        return '<div></div>';
    }
}
