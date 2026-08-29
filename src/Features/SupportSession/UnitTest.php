<?php

namespace Livewire\Features\SupportSession;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session as FacadesSession;
use Livewire\Attributes\Session;
use Livewire\Component;
use Livewire\Mechanisms\HandleSynths\PersistedValueCodec;
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

    public function test_php_sessions_continue_to_store_values_raw()
    {
        config(['session.serialization' => 'php']);

        app('session')->forgetDrivers();

        Livewire::test(ComponentWithSessionCollection::class)
            ->call('add');

        $stored = session('collection');

        $this->assertInstanceOf(Collection::class, $stored);
        $this->assertSame([1], $stored->all());
    }

    public function test_json_sessions_continue_to_store_primitive_values_raw()
    {
        config(['session.serialization' => 'json']);

        app('session')->forgetDrivers();

        Livewire::test(ComponentWithPrimitiveSessionProperty::class)
            ->set('count', 1);

        $this->assertSame(1, session('primitive'));
    }

    public function test_legacy_tuple_shaped_arrays_are_not_treated_as_synthetic_values()
    {
        $tuple = [['one', 'two'], ['s' => 'clctn', 'class' => Collection::class]];

        FacadesSession::put('legacy-tuple', $tuple);

        Livewire::test(ComponentWithRawSessionArray::class)
            ->assertSet('value', $tuple);
    }

    public function test_session_envelopes_are_decoded_even_after_switching_to_php_serialization()
    {
        config(['session.serialization' => 'json']);

        app('session')->forgetDrivers();

        Livewire::test(ComponentWithSessionCollection::class)
            ->call('add');

        config(['session.serialization' => 'php']);

        Livewire::test(ComponentWithSessionCollection::class)
            ->assertSet('list', fn ($list) => $list instanceof Collection && $list->all() === [1]);
    }

    public function test_client_authored_synth_envelopes_are_discarded_in_json_sessions()
    {
        $this->assertClientAuthoredSynthEnvelopeIsDiscarded('json');
    }

    public function test_client_authored_synth_envelopes_are_discarded_in_php_sessions()
    {
        $this->assertClientAuthoredSynthEnvelopeIsDiscarded('php');
    }

    public function test_unreadable_persisted_envelopes_are_discarded()
    {
        config(['session.serialization' => 'json']);

        app('session')->forgetDrivers();

        Livewire::test(ComponentWithSessionCollection::class)
            ->call('add');

        $stored = session('collection');
        $stored[PersistedValueCodec::KEY]['version']++;

        FacadesSession::put('collection', $stored);

        Livewire::test(ComponentWithSessionCollection::class)
            ->assertSet('list', fn ($list) => $list instanceof Collection && $list->isEmpty());
    }

    public function test_components_can_share_an_explicit_session_key()
    {
        config(['session.serialization' => 'json']);

        app('session')->forgetDrivers();

        Livewire::test(ComponentWithSessionCollection::class)
            ->call('add');

        session()->save();
        session()->start();

        Livewire::test(ComponentReadingSharedSessionCollection::class)
            ->assertSet('items', fn ($items) => $items instanceof Collection && $items->all() === [1]);
    }

    public function test_values_for_synths_that_are_no_longer_registered_are_discarded()
    {
        config(['session.serialization' => 'json']);

        app('session')->forgetDrivers();

        $encoded = app(PersistedValueCodec::class)->encodeForStorage(
            new \Illuminate\Foundation\Auth\User,
            new TestComponent,
            'value',
            'dangerous-value',
        );

        FacadesSession::put('dangerous-value', $encoded);

        app()->instance(
            \Livewire\Mechanisms\HandleSynths\HandleSynths::class,
            new \Livewire\Mechanisms\HandleSynths\HandleSynths,
        );

        Livewire::test(ComponentWithDangerousSessionProperty::class)
            ->assertSet('value', 'safe');
    }

    public function test_json_arrays_left_by_older_versions_are_discarded_from_typed_properties()
    {
        config(['session.serialization' => 'json']);

        app('session')->forgetDrivers();
        FacadesSession::put('stale-collection', [1, 2]);

        Livewire::test(ComponentWithStaleSessionCollection::class)
            ->assertSet('list', null);
    }

    protected function assertClientAuthoredSynthEnvelopeIsDiscarded($serialization)
    {
        config(['session.serialization' => $serialization]);

        app('session')->forgetDrivers();

        Livewire::test(ComponentWithDangerousSessionProperty::class)
            ->set('value', [
                PersistedValueCodec::KEY => [
                    'version' => PersistedValueCodec::VERSION,
                    'value' => [null, [
                        's' => 'mdl',
                        'class' => \Illuminate\Foundation\Auth\User::class,
                        'key' => 1,
                    ]],
                ],
            ]);

        session()->save();
        session()->start();

        Livewire::test(ComponentWithDangerousSessionProperty::class)
            ->assertSet('value', 'safe');
    }
}

class ComponentWithSessionCollection extends Component
{
    #[Session(key: 'collection')]
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

class ComponentReadingSharedSessionCollection extends Component
{
    #[Session(key: 'collection')]
    public ?Collection $items = null;

    public function mount(): void
    {
        $this->items ??= collect();
    }

    public function render()
    {
        return '<div></div>';
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

class ComponentWithPrimitiveSessionProperty extends Component
{
    #[Session(key: 'primitive')]
    public int $count = 0;

    public function render()
    {
        return '<div></div>';
    }
}

class ComponentWithRawSessionArray extends Component
{
    #[Session(key: 'legacy-tuple')]
    public array $value = [];

    public function render()
    {
        return '<div></div>';
    }
}

class ComponentWithDangerousSessionProperty extends Component
{
    #[Session(key: 'dangerous-value')]
    public $value = 'safe';

    public function render()
    {
        return '<div></div>';
    }
}

class ComponentWithStaleSessionCollection extends Component
{
    #[Session(key: 'stale-collection')]
    public ?Collection $list = null;

    public function render()
    {
        return '<div></div>';
    }
}
