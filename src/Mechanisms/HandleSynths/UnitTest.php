<?php

namespace Livewire\Mechanisms\HandleSynths;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Livewire;
use Livewire\Mechanisms\HandleComponents\ComponentContext;
use Livewire\Mechanisms\HandleComponents\CorruptComponentPayloadException;
use Livewire\Mechanisms\HandleComponents\Synthesizers\ArrayShapedSynth;
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

    public function test_hydrate_property_update_validates_classes_before_skipping_array_shaped_synths()
    {
        $synths = app(HandleSynths::class);
        $context = new ComponentContext(new TestComponent);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('is not allowed to be instantiated');

        $synths->hydratePropertyUpdate([
            1,
            ['s' => 'arr', 'class' => \Symfony\Component\Process\Process::class],
        ], $context, 'evil');
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

    public function test_hydrate_for_update_leaves_collection_children_replaced_with_scalars_alone()
    {
        $synths = app(HandleSynths::class);
        $context = new ComponentContext(new TestComponent);

        $raw = ['data' => $synths->dehydrate([
            'section' => ['tags' => collect(['a']), 'other' => collect(['b'])],
        ], $context, 'data')];

        $updated = $synths->hydrateForUpdate($raw, 'data.section', [
            'tags' => 1,
            'other' => ['b'],
        ], $context);

        $this->assertSame(1, $updated['tags']);
        $this->assertInstanceOf(Collection::class, $updated['other']);
    }

    public function test_hydrate_for_update_leaves_stdclass_children_replaced_with_scalars_alone()
    {
        $synths = app(HandleSynths::class);
        $context = new ComponentContext(new TestComponent);

        $raw = ['data' => $synths->dehydrate([
            'section' => [
                'item' => (object) ['name' => 'a'],
                'other' => (object) ['name' => 'b'],
            ],
        ], $context, 'data')];

        $updated = $synths->hydrateForUpdate($raw, 'data.section', [
            'item' => 1,
            'other' => ['name' => 'b'],
        ], $context);

        $this->assertSame(1, $updated['item']);
        $this->assertInstanceOf(\stdClass::class, $updated['other']);
        $this->assertSame('b', $updated['other']->name);
    }

    public function test_hydrate_for_update_leaves_top_level_array_shaped_values_replaced_with_scalars_alone()
    {
        $synths = app(HandleSynths::class);
        $context = new ComponentContext(new TestComponent);

        $collection = ['item' => $synths->dehydrate(collect(['a']), $context, 'item')];
        $stdClass = ['item' => $synths->dehydrate((object) ['name' => 'a'], $context, 'item')];

        $this->assertSame(1, $synths->hydrateForUpdate($collection, 'item', 1, $context));
        $this->assertSame(1, $synths->hydrateForUpdate($stdClass, 'item', 1, $context));
        $this->assertNull($synths->hydrateForUpdate($collection, 'item', null, $context));
    }

    public function test_hydrate_for_update_still_applies_scalar_shaped_synths()
    {
        $synths = app(HandleSynths::class);
        $context = new ComponentContext(new TestComponent);

        $raw = ['date' => $synths->dehydrate(Carbon::parse('2026-01-01'), $context, 'date')];
        $updated = $synths->hydrateForUpdate($raw, 'date', '2026-08-29T12:00:00+00:00', $context);

        $this->assertInstanceOf(Carbon::class, $updated);
        $this->assertSame('2026-08-29T12:00:00+00:00', $updated->format(\DateTimeInterface::ATOM));
    }

    public function test_userland_array_shaped_synths_can_allow_scalar_replacements()
    {
        $synths = app(HandleSynths::class);
        $synths->registerSynth(CustomThingSynth::class);
        $context = new ComponentContext(new TestComponent);

        $raw = ['thing' => $synths->dehydrate(new CustomThing('original'), $context, 'thing')];

        $this->assertSame(1, $synths->hydrateForUpdate($raw, 'thing', 1, $context));
    }

    public function test_unmarked_userland_synths_keep_hydrating_non_array_updates()
    {
        $synths = app(HandleSynths::class);
        $synths->registerSynth(ScalarFriendlyThingSynth::class);
        $context = new ComponentContext(new TestComponent);

        $raw = ['thing' => $synths->dehydrate(new ScalarFriendlyThing('original'), $context, 'thing')];
        $updated = $synths->hydrateForUpdate($raw, 'thing', 1, $context);

        $this->assertInstanceOf(ScalarFriendlyThing::class, $updated);
        $this->assertSame(1, $updated->value);
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

    public function test_hydrate_for_update_never_trusts_synthesizer_meta_from_the_update_payload()
    {
        $synths = app(HandleSynths::class);
        $context = new ComponentContext(new TestComponent);

        $raw = ['data' => $synths->dehydrate([
            'section' => ['tags' => collect(['safe'])],
        ], $context, 'data')];

        // This value deliberately has the shape of a synthetic tuple and claims
        // a denylisted class. It came from the update payload, so both the synth
        // key and class must remain plain collection data. Only the previous,
        // authenticated Collection meta is allowed to choose the hydrator...
        $forgedTuple = [
            ['attacker-controlled'],
            ['s' => 'arr', 'class' => \Symfony\Component\Process\Process::class],
        ];

        $updated = $synths->hydrateForUpdate($raw, 'data.section', [
            'tags' => $forgedTuple,
        ], $context);

        $this->assertInstanceOf(Collection::class, $updated['tags']);
        $this->assertSame($forgedTuple, $updated['tags']->all());
    }

    public function test_nested_synthesizer_meta_in_the_previous_snapshot_cannot_be_tampered_with()
    {
        $component = Livewire::test(new class extends TestComponent {
            public $data;

            public function mount()
            {
                $this->data = ['section' => ['tags' => collect(['safe'])]];
            }
        });

        $snapshot = $component->snapshot;

        // Replace the nested Collection synth with attacker-selected metadata.
        // The request must be rejected by checksum verification before recursive
        // update hydration gets any opportunity to resolve it...
        $snapshot['data']['data'][0]['section'][0]['tags'][1] = [
            's' => 'arr',
            'class' => \Symfony\Component\Process\Process::class,
        ];

        $component->snapshot = $snapshot;

        $this->expectException(CorruptComponentPayloadException::class);

        $component->set('data.section', ['tags' => ['attacker-controlled']]);
    }
}

class CustomThing
{
    public function __construct(public string $value = 'default') {}
}

class CustomThingSynth extends Synth implements ArrayShapedSynth
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

class ScalarFriendlyThing
{
    public function __construct(public mixed $value) {}
}

class ScalarFriendlyThingSynth extends Synth
{
    public static $key = 'scalar-friendly-thing';

    public static function match($target)
    {
        return $target instanceof ScalarFriendlyThing;
    }

    public function dehydrate($target)
    {
        return [['value' => $target->value], []];
    }

    public function hydrate($value)
    {
        return new ScalarFriendlyThing($value);
    }
}
