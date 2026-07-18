<?php

namespace Livewire\Features\SupportSelection;

use Livewire\Component;
use Livewire\Livewire;
use Livewire\Selection;
use PHPUnit\Framework\Assert;
use Tests\TestComponent;

class UnitTest extends \Tests\TestCase
{
    function test_a_typed_selection_property_is_automatically_initialized()
    {
        Livewire::test(new class extends TestComponent {
            public Selection $selection;
        })
        ->assertSet('selection', fn ($selection) => $selection instanceof Selection && $selection->isEmpty())
        ;
    }

    function test_a_selection_can_be_set_from_the_client_and_survives_a_round_trip()
    {
        $component = Livewire::test(new class extends TestComponent {
            public Selection $selection;
        });

        $component->set('selection', [1, 2, 3]);

        $selection = $component->get('selection');

        Assert::assertInstanceOf(Selection::class, $selection);
        Assert::assertSame([1, 2, 3], $selection->all());
    }

    function test_selection_methods_are_usable_from_component_actions()
    {
        Livewire::test(new class extends TestComponent {
            public Selection $selection;

            public $result;

            public function inspect()
            {
                $this->result = [
                    'any' => $this->selection->any(),
                    'count' => $this->selection->count(),
                    'containsTwo' => $this->selection->contains(2),
                ];
            }

            public function clear()
            {
                $this->selection->clear();
            }
        })
        ->set('selection', [1, 2])
        ->call('inspect')
        ->assertSetStrict('result', ['any' => true, 'count' => 2, 'containsTwo' => true])
        ->call('clear')
        ->assertSet('selection', fn ($selection) => $selection->isEmpty())
        ;
    }

    function test_selection_dehydrates_with_its_synth_key_and_class()
    {
        $component = Livewire::test(new class extends TestComponent {
            public Selection $selection;
        });

        $component->set('selection', [5]);

        [$value, $meta] = $component->snapshot['data']['selection'];

        Assert::assertSame([5], $value);
        Assert::assertSame('sel', $meta['s']);
        Assert::assertSame(Selection::class, $meta['class']);
    }

    function test_contains_uses_loose_comparison_for_checkbox_string_values()
    {
        $selection = new Selection([1, 2]);

        Assert::assertTrue($selection->contains('2'));

        $selection->select('2');

        Assert::assertSame([1, 2], $selection->all());

        $selection->deselect('1');

        Assert::assertSame([2], $selection->all());
    }

    function test_a_hydrated_class_must_be_a_selection()
    {
        $this->expectExceptionMessage('Livewire: Invalid selection class.');

        $component = Livewire::test(new class extends TestComponent {
            public Selection $selection;
        });

        $synth = new SelectionSynth(
            new \Livewire\Mechanisms\HandleComponents\ComponentContext($component->instance()),
            'selection'
        );

        $synth->hydrate([1], ['class' => \stdClass::class]);
    }
}
