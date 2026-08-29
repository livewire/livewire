<?php

namespace Livewire\Mechanisms\HandleSynths;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
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

    public function test_nested_synthesized_values_round_trip_through_json_storage()
    {
        $codec = app(PersistedValueCodec::class);
        $component = new TestComponent;
        $date = Carbon::parse('2026-01-15 10:00:00');
        $value = ['items' => collect([1, 2]), 'createdAt' => $date];

        $encoded = $codec->encodeForStorage($value, $component, 'state', 'session.state');
        $stored = json_decode(json_encode($encoded), true);
        $decoded = $codec->decodeFromStorage($stored, $component, 'state', 'session.state');

        $this->assertInstanceOf(Collection::class, $decoded['items']);
        $this->assertSame([1, 2], $decoded['items']->all());
        $this->assertInstanceOf(Carbon::class, $decoded['createdAt']);
        $this->assertTrue($decoded['createdAt']->equalTo($date));
    }

    public function test_registered_custom_synths_round_trip_through_json_storage()
    {
        app(HandleSynths::class)->registerSynth(PersistedValueSynth::class);

        $codec = app(PersistedValueCodec::class);
        $component = new TestComponent;

        $encoded = $codec->encodeForStorage(new PersistedValue('stored'), $component, 'value', 'session.value');
        $stored = json_decode(json_encode($encoded), true);
        $decoded = $codec->decodeFromStorage($stored, $component, 'value', 'session.value');

        $this->assertInstanceOf(PersistedValue::class, $decoded);
        $this->assertSame('stored', $decoded->value);
    }

    public function test_bare_synth_tuples_are_not_decoded()
    {
        $codec = app(PersistedValueCodec::class);
        $component = new TestComponent;
        $tuple = [['one', 'two'], ['s' => 'clctn', 'class' => Collection::class]];

        $this->assertSame($tuple, $codec->decodeFromStorage($tuple, $component, 'items', 'session.items'));
    }

    public function test_envelopes_with_sibling_application_data_are_not_decoded()
    {
        $codec = app(PersistedValueCodec::class);
        $component = new TestComponent;
        $encoded = $codec->encodeForStorage(collect([1, 2]), $component, 'items', 'session.items');
        $withSiblingData = [...$encoded, 'application' => 'value'];

        $this->assertSame($withSiblingData, $codec->decodeFromStorage($withSiblingData, $component, 'items', 'session.items'));
    }

    public function test_unsupported_envelope_versions_are_rejected()
    {
        $codec = app(PersistedValueCodec::class);
        $component = new TestComponent;
        $encoded = $codec->encodeForStorage(collect([1, 2]), $component, 'items', 'session.items');
        $encoded[PersistedValueCodec::KEY]['version']++;

        $this->expectException(CorruptPersistedValueException::class);

        $codec->decodeFromStorage($encoded, $component, 'items', 'session.items');
    }

    public function test_tampered_envelopes_are_rejected_before_hydration()
    {
        $codec = app(PersistedValueCodec::class);
        $component = new TestComponent;
        $encoded = $codec->encodeForStorage(collect([1, 2]), $component, 'items', 'session.items');
        $encoded[PersistedValueCodec::KEY]['value'][0][] = 3;

        $this->expectException(CorruptPersistedValueException::class);

        $codec->decodeFromStorage($encoded, $component, 'items', 'session.items');
    }

    public function test_signatures_are_bound_to_the_storage_key()
    {
        $codec = app(PersistedValueCodec::class);
        $component = new TestComponent;
        $encoded = $codec->encodeForStorage(collect([1, 2]), $component, 'items', 'session.items');

        $this->expectException(CorruptPersistedValueException::class);

        $codec->decodeFromStorage($encoded, $component, 'items', 'session.other');
    }

    public function test_a_shared_storage_key_can_be_decoded_by_another_component_property()
    {
        $codec = app(PersistedValueCodec::class);
        $encoded = $codec->encodeForStorage(collect([1, 2]), new TestComponent, 'items', 'shared');

        $decoded = $codec->decodeFromStorage($encoded, new OtherTestComponent, 'renamed', 'shared');

        $this->assertInstanceOf(Collection::class, $decoded);
        $this->assertSame([1, 2], $decoded->all());
    }

    public function test_hydration_failures_are_reported_as_corrupt_persisted_values()
    {
        app(HandleSynths::class)->registerSynth(PersistedValueSynth::class);

        $component = new TestComponent;
        $encoded = app(PersistedValueCodec::class)
            ->encodeForStorage(new PersistedValue('stored'), $component, 'value', 'session.value');
        $codecWithoutCustomSynth = new PersistedValueCodec(new HandleSynths);

        try {
            $codecWithoutCustomSynth->decodeFromStorage($encoded, $component, 'value', 'session.value');
        } catch (CorruptPersistedValueException $e) {
            $this->assertStringContainsString('No synthesizer found', $e->getPrevious()->getMessage());

            return;
        }

        $this->fail('The outdated persisted value was hydrated.');
    }
}

class OtherTestComponent extends TestComponent
{
    //
}

class PersistedValue
{
    public function __construct(public string $value) {}
}

class PersistedValueSynth extends Synth
{
    public static $key = 'persisted-value';

    public static function match($target)
    {
        return $target instanceof PersistedValue;
    }

    public function dehydrate($target)
    {
        return [$target->value, []];
    }

    public function hydrate($value)
    {
        return new PersistedValue($value);
    }
}
