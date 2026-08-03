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

    public function test_hydrate_blocks_denylisted_classes_via_the_security_policy()
    {
        $synths = app(HandleSynths::class);
        $context = new ComponentContext(new TestComponent);

        // A forged tuple whose meta declares a denylisted gadget class. The
        // SecurityPolicy denylist must fire before any synth instantiates it.
        $tuple = [['x' => 1], ['s' => 'arr', 'class' => \Symfony\Component\Process\Process::class]];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('is not allowed to be instantiated');

        $synths->hydrate($tuple, $context, 'evil');
    }

    public function test_hydrate_property_update_blocks_denylisted_classes_via_the_security_policy()
    {
        $synths = app(HandleSynths::class);
        $context = new ComponentContext(new TestComponent);

        $tuple = [['x' => 1], ['s' => 'arr', 'class' => \Symfony\Component\Process\Process::class]];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('is not allowed to be instantiated');

        $synths->hydratePropertyUpdate($tuple, $context, 'evil');
    }

    /*
     * An update sends values without synthesizer meta — the browser strips
     * it. When the updated path sits ABOVE a synthesized value (updating
     * `data.section` when `data.section.row.tags` is a collection), the
     * nested values can only be reconstructed from the meta in the previous,
     * checksum-verified snapshot. See hydratePropertyUpdate().
     */

    public function test_hydrate_for_update_recursively_hydrates_nested_synthetic_values()
    {
        $synths = app(HandleSynths::class);
        $context = new ComponentContext(new TestComponent);

        $raw = ['data' => $synths->dehydrate([
            'section' => [
                'row' => ['tags' => collect(['a']), 'title' => 'Original'],
            ],
        ], $context, 'data')];

        // The browser sends the whole section back with meta stripped...
        $updated = $synths->hydrateForUpdate($raw, 'data.section', [
            'row' => ['tags' => ['a', 'b'], 'title' => 'Updated'],
        ], $context);

        $this->assertInstanceOf(Collection::class, $updated['row']['tags']);
        $this->assertSame(['a', 'b'], $updated['row']['tags']->all());
        $this->assertSame('Updated', $updated['row']['title']);
    }

    public function test_hydrate_for_update_leaves_children_the_snapshot_has_no_meta_for_alone()
    {
        $synths = app(HandleSynths::class);
        $context = new ComponentContext(new TestComponent);

        $raw = ['data' => $synths->dehydrate([
            'section' => ['tags' => collect(['a'])],
        ], $context, 'data')];

        $updated = $synths->hydrateForUpdate($raw, 'data.section', [
            'tags' => ['a'],
            'brandNew' => ['some' => 'array'],
        ], $context);

        $this->assertInstanceOf(Collection::class, $updated['tags']);
        $this->assertSame(['some' => 'array'], $updated['brandNew']);
    }

    public function test_hydrate_for_update_passes_nested_removals_through_untouched()
    {
        $synths = app(HandleSynths::class);
        $context = new ComponentContext(new TestComponent);

        $raw = ['data' => $synths->dehydrate([
            'section' => ['tags' => collect(['a']), 'other' => collect(['b'])],
        ], $context, 'data')];

        $updated = $synths->hydrateForUpdate($raw, 'data.section', [
            'tags' => '__rm__',
            'other' => ['b'],
        ], $context);

        $this->assertSame('__rm__', $updated['tags']);
        $this->assertInstanceOf(Collection::class, $updated['other']);
    }

    public function test_hydrate_for_update_leaves_already_hydrated_children_alone()
    {
        $synths = app(HandleSynths::class);
        $context = new ComponentContext(new TestComponent);

        $raw = ['data' => $synths->dehydrate([
            'section' => ['tags' => collect(['a'])],
        ], $context, 'data')];

        // Updates coming off the wire are JSON, but Livewire's testing
        // helpers set real PHP values. Those are already in their final
        // form and mustn't be run back through a synthesizer...
        $tags = collect(['a', 'b']);

        $updated = $synths->hydrateForUpdate($raw, 'data.section', ['tags' => $tags], $context);

        $this->assertSame($tags, $updated['tags']);
    }

    public function test_hydrate_for_update_runs_the_security_policy_over_nested_meta()
    {
        $synths = app(HandleSynths::class);
        $context = new ComponentContext(new TestComponent);

        // Meta declaring a denylisted gadget class, nested a level below the
        // updated path. Recursion must not skip the denylist check...
        $raw = ['data' => [[
            'section' => [[
                'nested' => [['x' => 1], ['s' => 'arr', 'class' => \Symfony\Component\Process\Process::class]],
            ], ['s' => 'arr']],
        ], ['s' => 'arr']]];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('is not allowed to be instantiated');

        $synths->hydrateForUpdate($raw, 'data.section', ['nested' => ['x' => 2]], $context);
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
