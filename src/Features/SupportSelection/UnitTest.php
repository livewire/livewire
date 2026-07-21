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
        Assert::assertSame([1, 2, 3], $selection->keys());
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
        Assert::assertSame([], $selection->keys());
    }

    function test_reset_returns_to_an_empty_include_mode_selection_like_clear()
    {
        $selection = new Selection([1, 2]);

        $selection->selectAll();
        $selection->deselect(3);

        $selection->reset();

        Assert::assertFalse($selection->isAll());
        Assert::assertFalse($selection->any());
        Assert::assertSame([], $selection->keys());
    }

    function test_enumerating_an_all_mode_selection_fails_loudly()
    {
        $selection = (new Selection)->selectAll();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('select-all mode');

        $selection->keys();
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

    function test_has_answers_membership_like_contains_in_both_modes()
    {
        $selection = new Selection([1, 2]);

        Assert::assertTrue($selection->has(2));
        Assert::assertTrue($selection->has('2'));
        Assert::assertFalse($selection->has(3));

        $selection->selectAll();
        $selection->deselect(3);

        Assert::assertTrue($selection->has(999));
        Assert::assertFalse($selection->has(3));
    }

    function test_select_deselect_and_toggle_accept_arrays_of_keys()
    {
        $selection = new Selection;

        $selection->select([1, 2, 3]);

        Assert::assertSame([1, 2, 3], $selection->keys());

        $selection->deselect([1, 3]);

        Assert::assertSame([2], $selection->keys());

        // Each key in a toggled array flips independently...
        $selection->toggle([2, 4]);

        Assert::assertFalse($selection->contains(2));
        Assert::assertTrue($selection->contains(4));
    }

    function test_arrays_of_keys_route_through_the_mode_branch_in_select_all_mode()
    {
        $selection = (new Selection)->selectAll();

        // Deselecting in all-mode records exceptions...
        $selection->deselect([1, 2]);

        Assert::assertSame([1, 2], $selection->except());

        // Re-selecting removes them...
        $selection->select([1, 2]);

        Assert::assertSame([], $selection->except());
        Assert::assertTrue($selection->isAllSelected());
    }

    function test_contains_uses_loose_comparison_for_checkbox_string_values()
    {
        $selection = new Selection([1, 2]);

        Assert::assertTrue($selection->contains('2'));

        $selection->select('2');

        Assert::assertSame([1, 2], $selection->keys());

        $selection->deselect('1');

        Assert::assertSame([2], $selection->keys());

        $selection->toggle('2');

        Assert::assertSame([], $selection->keys());

        $selection->toggle(3);

        Assert::assertSame([3], $selection->keys());
    }

    function test_set_total_makes_count_computable_in_all_mode()
    {
        $selection = (new Selection)->selectAll();

        $selection->setTotal(100);

        Assert::assertSame(100, $selection->count());

        $selection->deselect(5);

        Assert::assertSame(99, $selection->count());

        // An explicit total always wins over the fed one...
        Assert::assertSame(49, $selection->count(50));

        // Include mode never needs a total...
        Assert::assertSame(0, (new Selection)->count());
    }

    function test_counting_an_all_mode_selection_without_a_total_fails_loudly()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('unknowable');

        (new Selection)->selectAll()->count();
    }

    function test_set_total_reads_a_paginators_total()
    {
        $selection = new Selection;

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator([1, 2], 50, 10);

        Assert::assertSame($selection, $selection->setTotal($paginator));
        Assert::assertSame(50, $selection->total());
    }

    function test_the_total_rides_the_snapshot_meta_and_survives_round_trips()
    {
        $component = Livewire::test(new class extends TestComponent {
            public Selection $selection;

            public function mount()
            {
                $this->selection = new Selection;
            }

            public function prime()
            {
                $this->selection->selectAll();
                $this->selection->setTotal(100);
            }

            public function deselectOne()
            {
                $this->selection->deselect(1);
            }
        });

        $component->call('prime');

        [$value, $meta] = $component->snapshot['data']['selection'];

        Assert::assertSame(100, $meta['total']);

        // The next request restores the total from meta — outOf is not re-run...
        $component->call('deselectOne');

        Assert::assertSame(99, $component->get('selection')->count());
    }

    function test_where_selected_constrains_a_query_by_mode()
    {
        $include = SelectionTestModel::query()->whereSelected(new Selection([1, 2]));

        Assert::assertStringContainsString('"id" in', str_replace('`', '"', $include->toSql()));
        Assert::assertSame([1, 2], $include->getBindings());

        $except = SelectionTestModel::query()->whereSelected((new Selection)->selectAll()->deselect(3), unscoped: true);

        Assert::assertStringContainsString('"id" not in', str_replace('`', '"', $except->toSql()));
        Assert::assertSame([3], $except->getBindings());

        // It composes with (and never replaces) ownership scoping...
        $scoped = SelectionTestModel::query()->where('user_id', 7)->whereSelected(new Selection([1]));

        Assert::assertSame([7, 1], $scoped->getBindings());
    }

    function test_a_select_all_selection_refuses_an_unscoped_query()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('unscoped');

        SelectionTestModel::query()->whereSelected((new Selection)->selectAll());
    }

    function test_the_unscoped_guard_can_be_acknowledged_or_satisfied_by_scoping()
    {
        // An existing constraint satisfies the guard...
        $scoped = SelectionTestModel::query()->where('user_id', 7)->whereSelected((new Selection)->selectAll()->deselect(3));

        Assert::assertSame([7, 3], $scoped->getBindings());

        // As does an explicit acknowledgment...
        $acknowledged = SelectionTestModel::query()->whereSelected((new Selection)->selectAll()->deselect(3), unscoped: true);

        Assert::assertSame([3], $acknowledged->getBindings());

        // A global scope counts as a constraint...
        SelectionTestModel::addGlobalScope('tenant', fn ($query) => $query->where('tenant_id', 1));

        try {
            $globallyScoped = SelectionTestModel::query()->whereSelected((new Selection)->selectAll()->deselect(9));

            Assert::assertStringContainsString('not in', $globallyScoped->toSql());
        } finally {
            SelectionTestModel::clearBootedModels();
        }

        // Include mode never trips the guard — forged keys there are bounded
        // by whatever scoping the query has, same as any request input...
        $include = SelectionTestModel::query()->whereSelected(new Selection([1]));

        Assert::assertSame([1], $include->getBindings());
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

class SelectionTestModel extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'selection_test_models';
}
