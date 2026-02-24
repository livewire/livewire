<?php

namespace Livewire\Features\SupportSync;

use Livewire\Attributes\Sync;
use Livewire\Livewire;
use Tests\TestComponent;

class UnitTest extends \Tests\TestCase
{
    public function test_sync_can_infer_int_cast_from_typed_property()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[Sync]
            public int $count = 0;
        })
            ->set('count', '42')
            ->assertSetStrict('count', 42)
            ->assertSnapshotSetStrict('count', 42);

        $this->assertSame('int', data_get($component->snapshot, 'memo.sync.count'));
    }

    public function test_sync_can_cast_untyped_property()
    {
        Livewire::test(new class extends TestComponent {
            #[Sync('bool')]
            public $isOpen = false;
        })
            ->set('isOpen', '1')
            ->assertSetStrict('isOpen', true)
            ->set('isOpen', '0')
            ->assertSetStrict('isOpen', false);
    }

    public function test_sync_supports_trait_defined_properties()
    {
        Livewire::test(new class extends TestComponent {
            use HasSyncedTraitProperty;
        })
            ->set('enabled', '1')
            ->assertSetStrict('enabled', true);
    }

    public function test_sync_supports_custom_codecs()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[Sync(MoneyCodec::class)]
            public Money $price;

            public function mount()
            {
                $this->price = new Money(1200, 'USD');
            }
        })
            ->assertSet('price', fn ($value) => $value instanceof Money && $value->amount === 1200 && $value->currency === 'USD')
            ->assertSnapshotSet('price.amount', 1200)
            ->set('price', ['amount' => 3400, 'currency' => 'EUR'])
            ->assertSet('price', fn ($value) => $value instanceof Money && $value->amount === 3400 && $value->currency === 'EUR')
            ->assertSnapshotSet('price.amount', 3400);

        $this->assertSame(MoneyCodec::class, data_get($component->snapshot, 'memo.sync.price'));
    }

    public function test_sync_disallows_deep_updates_for_custom_codecs()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('does not support deep updates');

        Livewire::test(new class extends TestComponent {
            #[Sync(MoneyCodec::class)]
            public Money $price;

            public function mount()
            {
                $this->price = new Money(1200, 'USD');
            }
        })->set('price.amount', 2000);
    }

    public function test_sync_throws_for_invalid_codec_configuration()
    {
        $this->expectException(\Illuminate\View\ViewException::class);
        $this->expectExceptionMessage('must implement');

        Livewire::test(new class extends TestComponent {
            #[Sync(InvalidMoneyCodec::class)]
            public Money $price;

            public function mount()
            {
                $this->price = new Money(1200, 'USD');
            }
        })->assertSet('price', fn ($value) => $value instanceof Money);
    }
}

trait HasSyncedTraitProperty
{
    #[Sync('bool')]
    public $enabled = false;
}

class Money
{
    public function __construct(
        public int $amount,
        public string $currency = 'USD',
    ) {}
}

class MoneyCodec implements SyncCodec
{
    public function toLivewire(mixed $value): mixed
    {
        if (! $value instanceof Money) {
            throw new \InvalidArgumentException('MoneyCodec expects a Money value.');
        }

        return [
            'amount' => $value->amount,
            'currency' => $value->currency,
        ];
    }

    public function fromLivewire(mixed $value): mixed
    {
        if (! is_array($value)) {
            throw new \InvalidArgumentException('MoneyCodec expects an array payload.');
        }

        return new Money(
            (int) ($value['amount'] ?? 0),
            (string) ($value['currency'] ?? 'USD')
        );
    }
}

class InvalidMoneyCodec
{
    //
}
