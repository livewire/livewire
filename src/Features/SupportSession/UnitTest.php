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

    public function test_non_primitive_properties_survive_json_session_serialization()
    {
        config(['session.serialization' => 'json']);

        // Rebuild the session store so the serialization config takes effect...
        app('session')->forgetDrivers();

        $date = Carbon::parse('2026-01-15 10:00:00');

        Livewire::test(ComponentWithTypedSessionProperties::class)
            ->call('add')
            ->call('add')
            ->set('when', $date)
            ->call('increment');

        // Force a real json_encode/json_decode round-trip on the session,
        // like what happens between two real requests...
        session()->save();
        session()->start();

        Livewire::test(ComponentWithTypedSessionProperties::class)
            ->assertSet('list', fn ($list) => $list instanceof Collection && count($list) === 2)
            ->assertSet('when', fn ($when) => $when instanceof Carbon && $when->equalTo($date))
            ->assertSet('count', 1);
    }

    public function test_non_primitive_properties_are_stored_raw_when_using_php_session_serialization()
    {
        Livewire::test(ComponentWithTypedSessionProperties::class)
            ->call('add');

        // Under the default "php" serialization, values are stored as-is...
        $stored = collect(session()->all())->first(fn ($value, $key) => str_starts_with($key, 'lw') && $value instanceof Collection);

        $this->assertInstanceOf(Collection::class, $stored);

        session()->save();
        session()->start();

        Livewire::test(ComponentWithTypedSessionProperties::class)
            ->assertSet('list', fn ($list) => $list instanceof Collection && count($list) === 1);
    }
}

class ComponentWithTypedSessionProperties extends Component
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

    public function add(): void
    {
        $this->list->push(random_int(0, 100));
    }

    public function increment(): void
    {
        $this->count++;
    }

    public function render()
    {
        return '<div>{{ $list->implode(", ") }}</div>';
    }
}
