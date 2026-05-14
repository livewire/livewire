<?php

namespace Livewire\Mechanisms\HandleSynths;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Mechanisms\HandleComponents\ComponentContext;
use Livewire\Mechanisms\HandleComponents\Synthesizers\CollectionSynth;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
use Tests\TestComponent;

class UnitTest extends \Tests\TestCase
{
    public function test_dehydrate_passes_primitives_through_unchanged()
    {
        $synths = app(HandleSynths::class);
        $context = new ComponentContext(new TestComponent);

        $this->assertSame(42, $synths->dehydrate(42, $context, ''));
        $this->assertSame('foo', $synths->dehydrate('foo', $context, ''));
        $this->assertTrue($synths->dehydrate(true, $context, ''));
        $this->assertNull($synths->dehydrate(null, $context, ''));
    }

    public function test_dehydrate_returns_a_synthetic_tuple_for_non_primitives()
    {
        $synths = app(HandleSynths::class);
        $context = new ComponentContext(new TestComponent);

        [$data, $meta] = $synths->dehydrate(collect([1, 2, 3]), $context, '');

        $this->assertSame([1, 2, 3], $data);
        $this->assertSame(CollectionSynth::$key, $meta['s']);
        $this->assertSame(Collection::class, $meta['class']);
    }

    public function test_hydrate_round_trips_a_collection()
    {
        $synths = app(HandleSynths::class);
        $context = new ComponentContext(new TestComponent);

        $original = collect([1, 2, 3]);

        $tuple = $synths->dehydrate($original, $context, '');
        $hydrated = $synths->hydrate($tuple, $context, '');

        $this->assertInstanceOf(Collection::class, $hydrated);
        $this->assertSame([1, 2, 3], $hydrated->all());
    }

    public function test_hydrate_passes_non_tuple_values_through_unchanged()
    {
        $synths = app(HandleSynths::class);
        $context = new ComponentContext(new TestComponent);

        $this->assertSame(42, $synths->hydrate(42, $context, ''));
        $this->assertSame('foo', $synths->hydrate('foo', $context, ''));
        $this->assertSame(['plain', 'array'], $synths->hydrate(['plain', 'array'], $context, ''));
    }

    public function test_find_resolves_a_synth_by_key()
    {
        $synths = app(HandleSynths::class);

        $synth = $synths->find(CollectionSynth::$key, new TestComponent);

        $this->assertInstanceOf(CollectionSynth::class, $synth);
    }

    public function test_find_resolves_a_synth_by_target_value()
    {
        $synths = app(HandleSynths::class);

        $synth = $synths->find(collect([1, 2, 3]), new TestComponent);

        $this->assertInstanceOf(CollectionSynth::class, $synth);
    }

    public function test_find_returns_null_for_an_unknown_key()
    {
        $synths = app(HandleSynths::class);

        $this->assertNull($synths->find('not-a-real-synth-key', new TestComponent));
    }

    public function test_register_synth_adds_a_synth_to_the_registry()
    {
        $synths = app(HandleSynths::class);

        $synths->registerSynth(CustomThingSynth::class);

        $synth = $synths->find(new CustomThing, new TestComponent);

        $this->assertInstanceOf(CustomThingSynth::class, $synth);
    }

    public function test_is_removal_recognises_the_removal_sentinel()
    {
        $synths = app(HandleSynths::class);

        $this->assertTrue($synths->isRemoval('__rm__'));
        $this->assertFalse($synths->isRemoval('rm'));
        $this->assertFalse($synths->isRemoval(null));
        $this->assertFalse($synths->isRemoval(''));
    }
}

class CustomThing
{
    public function __construct(public string $value = 'default') {}
}

class CustomThingSynth extends Synth
{
    public static $key = 'custom-thing';

    public static function match($target)
    {
        return $target instanceof CustomThing;
    }

    public function dehydrate($target)
    {
        return [['value' => $target->value], []];
    }

    public function hydrate($value)
    {
        return new CustomThing($value['value']);
    }
}
