<?php

namespace Livewire\Features\SupportSelection;

use Livewire\Component;
use Livewire\Livewire;
use Livewire\Selection;
use PHPUnit\Framework\Assert;
use Tests\TestComponent;

class UnitTest extends \Tests\TestCase
{
    function test_a_selection_can_be_set_from_the_client_and_survives_a_round_trip()
    {
        $component = Livewire::test(new class extends TestComponent {
            public Selection $selection;

            public function mount()
            {
                $this->selection = new Selection;
            }
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

            public function mount()
            {
                $this->selection = new Selection;
            }

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

            public function mount()
            {
                $this->selection = new Selection;
            }
        });

        $component->set('selection', [5]);

        [$value, $meta] = $component->snapshot['data']['selection'];

        Assert::assertSame(['mode' => 'include', 'keys' => [5]], $value);
        Assert::assertSame('sel', $meta['s']);
        Assert::assertSame(Selection::class, $meta['class']);
    }

    function test_select_all_flips_into_except_mode_and_flips_every_semantic()
    {
        $selection = new Selection([1, 2]);

        $selection->selectAll();

        Assert::assertTrue($selection->isAll());
        Assert::assertTrue($selection->isAllSelected());
        Assert::assertTrue($selection->any());
        Assert::assertTrue($selection->contains(999));

        // Deselecting in all-mode records an exception...
        $selection->deselect(3);

        Assert::assertFalse($selection->contains(3));
        Assert::assertFalse($selection->isAllSelected());
        Assert::assertTrue($selection->isAll());
        Assert::assertSame([3], $selection->except());

        // Re-selecting removes the exception...
        $selection->select(3);

        Assert::assertTrue($selection->contains(3));
        Assert::assertSame([], $selection->except());

        // Clearing returns to an empty include-mode selection...
        $selection->clear();

        Assert::assertFalse($selection->isAll());
        Assert::assertFalse($selection->any());
        Assert::assertSame([], $selection->all());
    }

    function test_enumerating_an_all_mode_selection_fails_loudly()
    {
        $selection = (new Selection)->selectAll();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('select-all mode');

        $selection->all();
    }

    function test_all_mode_survives_a_round_trip_through_the_wire_format()
    {
        $component = Livewire::test(new class extends TestComponent {
            public Selection $selection;

            public function mount()
            {
                $this->selection = new Selection;
            }

            public function selectAll()
            {
                $this->selection->selectAll();
            }

            public function deselectOne()
            {
                $this->selection->deselect(1);
            }
        });

        $component->call('selectAll');

        [$value] = $component->snapshot['data']['selection'];

        Assert::assertSame(['mode' => 'except', 'keys' => []], $value);

        $component->call('deselectOne');

        $selection = $component->get('selection');

        Assert::assertTrue($selection->isAll());
        Assert::assertSame([1], $selection->except());
        Assert::assertFalse($selection->contains(1));
        Assert::assertTrue($selection->contains(2));
    }

    function test_contains_uses_loose_comparison_for_checkbox_string_values()
    {
        $selection = new Selection([1, 2]);

        Assert::assertTrue($selection->contains('2'));

        $selection->select('2');

        Assert::assertSame([1, 2], $selection->all());

        $selection->deselect('1');

        Assert::assertSame([2], $selection->all());

        $selection->toggle('2');

        Assert::assertSame([], $selection->all());

        $selection->toggle(3);

        Assert::assertSame([3], $selection->all());
    }

    function test_a_hydrated_class_must_be_a_selection()
    {
        $this->expectExceptionMessage('Livewire: Invalid selection class.');

        $component = Livewire::test(new class extends TestComponent {
            public Selection $selection;

            public function mount()
            {
                $this->selection = new Selection;
            }
        });

        $synth = new SelectionSynth(
            new \Livewire\Mechanisms\HandleComponents\ComponentContext($component->instance()),
            'selection'
        );

        $synth->hydrate([1], ['class' => \stdClass::class]);
    }
}
