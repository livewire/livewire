<?php

namespace Livewire\Features\SupportVirtualProperties;

use Illuminate\Support\Collection;
use Livewire\Attributes\Virtual;
use Livewire\Livewire;
use Livewire\Selection;
use PHPUnit\Framework\Assert;
use Tests\TestComponent;

class UnitTest extends \Tests\TestCase
{
    function test_a_virtual_property_method_initializes_a_property_on_mount()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function selected(): Selection
            {
                return new Selection(keys: ['bar'], mode: 'except');
            }
        });

        $selected = $component->get('selected');

        Assert::assertInstanceOf(Selection::class, $selected);
        Assert::assertTrue($selected->isAll());
        Assert::assertSame(['bar'], $selected->except());
    }

    function test_a_virtual_property_is_accessible_from_mount_and_actions()
    {
        Livewire::test(new class extends TestComponent {
            public $countAtMount;

            public function mount()
            {
                $this->countAtMount = count($this->items);
            }

            #[Virtual]
            public function items(): Collection
            {
                return collect(['a', 'b']);
            }

            public function add()
            {
                $this->items->push('c');
            }
        })
            ->assertSetStrict('countAtMount', 2)
            ->call('add')
            ->assertSet('items', fn ($items) => $items->all() === ['a', 'b', 'c']);
    }

    function test_a_virtual_property_is_available_as_a_plain_variable_in_the_view()
    {
        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function selected(): Selection
            {
                return new Selection(keys: ['bar'], mode: 'except');
            }

            public function render()
            {
                return '<div>{{ $selected->isAll() ? "all" : "some" }}</div>';
            }
        })
            ->assertSee('all');
    }

    function test_a_virtual_property_dehydrates_into_snapshot_data_like_a_normal_property()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function selected(): Selection
            {
                return new Selection(keys: ['bar'], mode: 'except');
            }
        });

        [$value, $meta] = $component->snapshot['data']['selected'];

        Assert::assertSame('except', $value['mode']);
        Assert::assertSame(['bar'], $value['keys']);
        Assert::assertSame('sel', $meta['s']);
    }

    function test_a_virtual_property_method_cannot_be_called_as_an_action()
    {
        $this->expectException(CannotCallVirtualPropertyDirectlyException::class);

        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function selected(): Selection
            {
                return new Selection;
            }
        })->call('selected');
    }

    function test_a_snake_case_virtual_property_method_cannot_be_called_as_an_action()
    {
        // The direct-call block must hold regardless of method casing — a
        // #[Virtual] method whose raw name doesn't survive camelCasing
        // (snake_case, PascalCase) must still be unreachable as an action...
        $this->expectException(\Livewire\Exceptions\MethodNotFoundException::class);

        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function select_all(): Selection
            {
                return new Selection;
            }
        })->call('select_all');
    }

    function test_a_virtual_property_method_must_declare_a_return_type()
    {
        $this->assertThrowsDeep(VirtualPropertyMissingReturnTypeException::class, function () {
            Livewire::test(new class extends TestComponent {
                #[Virtual]
                public function selected()
                {
                    return new Selection;
                }
            });
        });
    }

    function test_a_virtual_property_method_cannot_share_a_name_with_a_declared_property()
    {
        $this->assertThrowsDeep(\LogicException::class, function () {
            Livewire::test(new class extends TestComponent {
                public $selected = [];

                #[Virtual]
                public function selected(): Selection
                {
                    return new Selection;
                }
            });
        });
    }

    function test_a_virtual_property_method_must_declare_a_class_return_type()
    {
        $this->assertThrowsDeep(\LogicException::class, function () {
            Livewire::test(new class extends TestComponent {
                #[Virtual]
                public function count(): int
                {
                    return 5;
                }
            });
        });
    }

    function test_a_virtual_property_method_cannot_declare_required_parameters()
    {
        $this->assertThrowsDeep(\LogicException::class, function () {
            Livewire::test(new class extends TestComponent {
                #[Virtual]
                public function selected(string $required): Selection
                {
                    return new Selection;
                }
            });
        });
    }

    function test_two_virtual_property_methods_resolving_to_the_same_name_throw()
    {
        $this->assertThrowsDeep(\LogicException::class, function () {
            Livewire::test(new class extends TestComponent {
                #[Virtual]
                public function selected_items(): Selection
                {
                    return new Selection;
                }

                #[Virtual]
                public function selectedItems(): Selection
                {
                    return new Selection;
                }
            });
        });
    }

    function test_property_level_attributes_cannot_target_a_virtual_method()
    {
        // #[Locked] on a virtual method used to silently no-op (false
        // sense of protection). Property-level attributes are now
        // target-restricted, so it fails loudly instead...
        $this->assertThrowsDeep(\Error::class, function () {
            Livewire::test(new class extends TestComponent {
                #[\Livewire\Attributes\Locked]
                #[Virtual]
                public function selected(): Selection
                {
                    return new Selection;
                }
            });
        });
    }

    function test_validation_errors_on_a_virtual_property_survive_a_round_trip()
    {
        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function items(): Collection
            {
                return collect();
            }

            public function save()
            {
                $this->validate(['items' => 'array|min:1']);
            }
        })
            ->call('save')
            ->assertHasErrors('items')
            // The error survives dehydration into the snapshot memo and is
            // still present after a subsequent request...
            ->call('$refresh')
            ->assertHasErrors('items');
    }

    // Mount-time exceptions surface wrapped in a ViewException — walk the
    // chain so we can assert on the real one...
    protected function assertThrowsDeep($class, $callback)
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            while ($e) {
                if ($e instanceof $class) {
                    $this->assertInstanceOf($class, $e);

                    return;
                }

                $e = $e->getPrevious();
            }

            Assert::fail('Exception thrown, but none in the chain was ['.$class.'].');
        }

        Assert::fail('Expected exception ['.$class.'] was not thrown.');
    }

    function test_the_method_runs_fresh_on_every_request()
    {
        $component = new class extends TestComponent {
            public static $constructions = 0;

            #[Virtual]
            public function selected(): Selection
            {
                static::$constructions++;

                return new Selection;
            }
        };

        $component::$constructions = 0;

        Livewire::test($component)->call('$refresh');

        Assert::assertSame(2, $component::$constructions);
    }

    function test_reset_reinitializes_a_virtual_property()
    {
        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function selected(): Selection
            {
                return new Selection(keys: ['bar']);
            }

            public function mutateAndReset()
            {
                $this->selected->select('extra');

                $this->reset('selected');
            }
        })
            ->call('mutateAndReset')
            ->assertSet('selected', fn ($selected) => $selected->keys() === ['bar']);
    }

    function test_root_updates_are_hydrated_into_the_method_configured_instance()
    {
        // The method seeds a total that only exists server-side. After a
        // client update, the selection carries BOTH the client's keys and
        // the method's total — proving the update was applied onto the
        // method-built instance, not a fresh-from-wire one...
        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function selected(): Selection
            {
                return (new Selection)->setTotal(10);
            }
        })
            ->set('selected', ['a', 'b'])
            ->assertSet('selected', fn ($s) => $s->keys() === ['a', 'b'] && $s->total() === 10);
    }

    function test_update_hooks_see_the_old_value_before_a_virtual_property_is_written()
    {
        // The write lands on a clone, so during updating() the live instance
        // is still the old one — matching declared/nested-object semantics...
        Livewire::test(new class extends TestComponent {
            public $oldDuringUpdating;
            public $newDuringUpdated;

            #[Virtual]
            public function selected(): Selection
            {
                return new Selection(keys: ['old']);
            }

            public function updatingSelected($value)
            {
                $this->oldDuringUpdating = $this->selected->keys();
            }

            public function updatedSelected()
            {
                $this->newDuringUpdated = $this->selected->keys();
            }
        })
            ->set('selected', ['new'])
            ->assertSetStrict('oldDuringUpdating', ['old'])
            ->assertSetStrict('newDuringUpdated', ['new']);
    }

    function test_a_virtual_method_can_read_sibling_property_state()
    {
        // Because construction is lazy (on first access, after mount and
        // hydration), the method can depend on other properties...
        Livewire::test(new class extends TestComponent {
            public $seed = 'default';

            public function mount()
            {
                $this->seed = 'mounted';
            }

            #[Virtual]
            public function selected(): Selection
            {
                return new Selection(keys: [$this->seed]);
            }
        })
            ->assertSet('selected', fn ($s) => $s->keys() === ['mounted']);
    }

    function test_unsetting_a_virtual_property_resets_it_to_a_freshly_constructed_instance()
    {
        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function selected(): Selection
            {
                return new Selection(keys: ['bar']);
            }

            public function wipe()
            {
                $this->selected->select('extra');

                unset($this->selected);
            }
        })
            ->call('wipe')
            ->assertSet('selected', fn ($selected) => $selected->keys() === ['bar']);
    }

    function test_synths_without_hydrate_into_fall_back_to_a_plain_hydrate()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function items(): Collection
            {
                return collect(['a', 'b']);
            }
        });

        $component->call('$refresh');

        Assert::assertSame(['a', 'b'], $component->get('items')->all());

        $component->set('items', ['c']);

        Assert::assertInstanceOf(Collection::class, $component->get('items'));
        Assert::assertSame(['c'], $component->get('items')->all());
    }

    function test_a_garbage_client_update_still_lands_as_the_declared_type()
    {
        // Updates pass through the property's synthesizer (resolved from
        // server-owned snapshot meta), so a hostile payload can never
        // change the property's type out from under the component...
        $component = Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function selected(): Selection
            {
                return new Selection(keys: ['bar']);
            }
        });

        $component->set('selected', 'not-a-selection');

        Assert::assertInstanceOf(Selection::class, $component->get('selected'));
        Assert::assertSame([], $component->get('selected')->keys());
    }
}
