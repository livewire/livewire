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

    public function test_collection_properties_survive_json_session_serialization()
    {
        config(['session.serialization' => 'json']);

        app('session')->forgetDrivers();

        Livewire::test(ComponentWithSessionCollection::class)
            ->call('add');

        session()->save();
        session()->start();

        Livewire::test(ComponentWithSessionCollection::class)
            ->assertSet('list', fn ($list) => $list instanceof Collection && $list->all() === [1]);
    }

    public function test_synthesized_properties_survive_json_session_serialization()
    {
        config(['session.serialization' => 'json']);

        app('session')->forgetDrivers();

        $date = Carbon::parse('2026-01-15 10:00:00');

        Livewire::test(ComponentWithSynthesizedSessionProperties::class)
            ->set('when', $date)
            ->set('nested', ['list' => collect([1, 2]), 'when' => $date]);

        session()->save();
        session()->start();

        Livewire::test(ComponentWithSynthesizedSessionProperties::class)
            ->assertSet('when', fn ($when) => $when instanceof Carbon && $when->equalTo($date))
            ->assertSet('nested', fn ($nested) => $nested['list'] instanceof Collection
                && $nested['list']->all() === [1, 2]
                && $nested['when'] instanceof Carbon
                && $nested['when']->equalTo($date));
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

    public function add(): void
    {
        $this->list->push($this->list->count() + 1);
    }

    public function render()
    {
        return '<div>{{ $list->implode(", ") }}</div>';
    }
}

class ComponentWithSynthesizedSessionProperties extends Component
{
    #[Session]
    public ?Carbon $when = null;

    #[Session]
    public array $nested = [];

    public function render()
    {
        return '<div></div>';
    }
}
