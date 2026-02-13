<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers;

use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestComponent;

class UnitTest extends \Tests\TestCase
{
    public function test_collection_synth_hydrates_a_collection()
    {
        Livewire::test(ComponentWithCollectionProperty::class)
            ->assertSet('items', collect(['foo', 'bar']))
            ->call('$refresh')
            ->assertSet('items', collect(['foo', 'bar']));
    }

    public function test_collection_synth_hydrates_a_custom_collection_subclass()
    {
        Livewire::test(ComponentWithCustomCollectionProperty::class)
            ->call('$refresh')
            ->assertSet('items', new CustomCollection(['foo', 'bar']));
    }

    public function test_collection_synth_rejects_non_collection_class()
    {
        $synth = new CollectionSynth(
            new \Livewire\Mechanisms\HandleComponents\ComponentContext(
                new ComponentWithCollectionProperty,
                mounting: false,
            ),
            'items',
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Livewire: Class [stdClass] is not a valid Collection type.');

        $synth->hydrate([], ['class' => 'stdClass'], fn ($key, $child) => $child);
    }

    public function test_collection_synth_rejects_arbitrary_class_with_array_constructor()
    {
        $synth = new CollectionSynth(
            new \Livewire\Mechanisms\HandleComponents\ComponentContext(
                new ComponentWithCollectionProperty,
                mounting: false,
            ),
            'items',
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('is not a valid Collection type');

        $synth->hydrate([], ['class' => \ArrayObject::class], fn ($key, $child) => $child);
    }

    public function test_collection_synth_allows_collection_subclass()
    {
        $synth = new CollectionSynth(
            new \Livewire\Mechanisms\HandleComponents\ComponentContext(
                new ComponentWithCustomCollectionProperty,
                mounting: false,
            ),
            'items',
        );

        $result = $synth->hydrate(['foo', 'bar'], ['class' => CustomCollection::class], fn ($key, $child) => $child);

        $this->assertInstanceOf(CustomCollection::class, $result);
        $this->assertEquals(['foo', 'bar'], $result->all());
    }
}

class ComponentWithCollectionProperty extends TestComponent
{
    public Collection $items;

    public function mount()
    {
        $this->items = collect(['foo', 'bar']);
    }
}

class ComponentWithCustomCollectionProperty extends TestComponent
{
    public CustomCollection $items;

    public function mount()
    {
        $this->items = new CustomCollection(['foo', 'bar']);
    }
}

class CustomCollection extends Collection
{
    //
}
