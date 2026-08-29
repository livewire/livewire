<?php

namespace Livewire\Mechanisms\HandleSynths;

use Illuminate\Support\Collection;
use Tests\TestComponent;

class PersistedValueCodecUnitTest extends \Tests\TestCase
{
    public function test_a_collection_round_trips_through_json_storage()
    {
        $codec = app(PersistedValueCodec::class);
        $component = new TestComponent;

        $encoded = $codec->encodeForStorage(collect([1, 2, 3]), $component, 'items', 'session.items');
        $stored = json_decode(json_encode($encoded), true);
        $decoded = $codec->decodeFromStorage($stored, $component, 'items', 'session.items');

        $this->assertInstanceOf(Collection::class, $decoded);
        $this->assertSame([1, 2, 3], $decoded->all());
    }

    public function test_json_safe_values_are_not_encoded()
    {
        $codec = app(PersistedValueCodec::class);
        $component = new TestComponent;
        $value = ['search' => '', 'tags' => [1, 2], 'enabled' => true];

        $this->assertSame($value, $codec->encodeForStorage($value, $component, 'filters', 'session.filters'));
        $this->assertSame($value, $codec->decodeFromStorage($value, $component, 'filters', 'session.filters'));
    }

    public function test_an_unsigned_envelope_is_never_hydrated()
    {
        $codec = app(PersistedValueCodec::class);
        $component = new TestComponent;
        $forged = [
            PersistedValueCodec::KEY => [
                'version' => PersistedValueCodec::VERSION,
                'value' => [['secret'], ['s' => 'clctn', 'class' => Collection::class]],
            ],
        ];

        $this->expectException(CorruptPersistedValueException::class);

        $codec->decodeFromStorage($forged, $component, 'items', 'session.items');
    }
}
