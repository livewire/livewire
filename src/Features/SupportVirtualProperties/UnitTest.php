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

    function test_a_virtual_method_runs_before_mount_and_before_parameters_land()
    {
        // A virtual method is a constructor, not a callback: it runs
        // alongside the synths that initialize declared properties — before
        // parameters are assigned and before mount() can mutate anything.
        // That's what puts the objects it returns in place early enough to
        // take part in the lifecycle hooks that follow (see the form object
        // tests below). Configure them from mount() instead of building them
        // there, exactly as you would a declared form object...
        Livewire::test(new class extends TestComponent {
            public $seed = 'default';

            public $mutated = 'default';

            public function mount()
            {
                $this->mutated = 'mounted';
            }

            #[Virtual]
            public function selected(): Selection
            {
                return new Selection(keys: [$this->seed, $this->mutated]);
            }
        }, ['seed' => 'from-parameter'])
            ->assertSetStrict('seed', 'from-parameter')
            ->assertSet('selected', fn ($s) => $s->keys() === ['default', 'default']);
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

    function test_a_form_object_from_a_virtual_property_runs_its_boot_method_once_per_request()
    {
        VirtualFormStub::$boots = 0;

        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function form(): VirtualFormStub
            {
                return new VirtualFormStub($this, 'form');
            }

            public function touch()
            {
                $this->form->title;
                $this->form->title;
            }
        })
            ->call('touch')
            ->call('$refresh');

        Assert::assertSame(3, VirtualFormStub::$boots);
    }

    function test_validate_attributes_on_a_form_object_from_a_virtual_property_are_registered()
    {
        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function form(): VirtualFormStub
            {
                return new VirtualFormStub($this, 'form');
            }

            public function save()
            {
                $this->form->validate();
            }
        })
            ->call('save')
            ->assertHasErrors('form.title');
    }

    function test_validate_attributes_on_a_form_object_from_a_virtual_property_validate_on_update()
    {
        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function form(): VirtualFormStub
            {
                return new VirtualFormStub($this, 'form');
            }
        })
            ->set('form.title', 'ab')
            ->assertHasErrors('form.title');
    }

    function test_locked_properties_on_a_form_object_from_a_virtual_property_cannot_be_updated()
    {
        $this->expectException(\Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException::class);

        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function form(): VirtualFormStub
            {
                return new VirtualFormStub($this, 'form');
            }
        })
            ->set('form.secret', 'HACKED');
    }

    function test_url_attributes_on_a_form_object_from_a_virtual_property_read_the_query_string()
    {
        Livewire::withQueryParams(['q' => 'from-url'])
            ->test(new class extends TestComponent {
                #[Virtual]
                public function form(): VirtualFormStub
                {
                    return new VirtualFormStub($this, 'form');
                }
            })
            ->assertSetStrict('form.q', 'from-url');
    }

    function test_url_attributes_on_a_form_object_from_a_virtual_property_register_a_url_effect()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function form(): VirtualFormStub
            {
                return new VirtualFormStub($this, 'form');
            }
        });

        Assert::assertArrayHasKey('form.q', $component->effects['url'] ?? []);
    }

    function test_a_whole_form_update_on_a_virtual_property_expands_into_per_field_updates()
    {
        // Sending the entire form as one consolidated update runs the same
        // per-field update hooks as setting each field individually — the
        // guarantee declared form objects already have...
        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function form(): VirtualFormStub
            {
                return new VirtualFormStub($this, 'form');
            }
        })
            ->set('form', ['title' => 'ab', 'q' => 'searching'])
            ->assertSetStrict('form.title', 'ab')
            ->assertSetStrict('form.q', 'searching')
            ->assertHasErrors('form.title');
    }

    function test_a_reset_virtual_form_object_is_booted_again()
    {
        // reset() reconstructs a virtual property, so the form that comes
        // back is a different instance — it has to arrive booted even though
        // the component's boot hook has long since run...
        Livewire::test(new class extends TestComponent {
            public $errorAfterReset;

            public $bootsDuringReset;

            #[Virtual]
            public function form(): VirtualFormStub
            {
                return new VirtualFormStub($this, 'form');
            }

            public function resetAndValidate()
            {
                $before = VirtualFormStub::$boots;

                $this->reset('form');

                $this->bootsDuringReset = VirtualFormStub::$boots - $before;

                try {
                    $this->form->validate();
                } catch (\Illuminate\Validation\ValidationException $e) {
                    $this->errorAfterReset = $e->validator->errors()->first('form.title');
                }
            }
        })
            ->call('resetAndValidate')
            ->assertSetStrict('bootsDuringReset', 1)
            ->assertSetStrict('errorAfterReset', 'The title field is required.');
    }

    function test_resetting_a_virtual_form_object_does_not_accumulate_attributes()
    {
        // Each reconstruction replaces the previous form's attributes rather
        // than stacking a new set on top of them...
        Livewire::test(new class extends TestComponent {
            public $countsMatch;

            #[Virtual]
            public function form(): VirtualFormStub
            {
                return new VirtualFormStub($this, 'form');
            }

            public function resetTwice()
            {
                $this->reset('form');
                $after = $this->getAttributes()->count();

                $this->reset('form');
                $this->countsMatch = $after === $this->getAttributes()->count();
            }
        })
            ->call('resetTwice')
            ->assertSetStrict('countsMatch', true);
    }

    function test_a_virtual_method_that_memoizes_its_form_object_does_not_reregister_rules()
    {
        // A method free to return the same instance twice must not boot it
        // twice — the rule would land in the form's rule set again...
        Livewire::test(new class extends TestComponent {
            protected $memo;

            public $ruleCount;

            #[Virtual]
            public function form(): VirtualFormStub
            {
                return $this->memo ??= new VirtualFormStub($this, 'form');
            }

            public function resetAndCount()
            {
                $this->reset('form');

                $this->ruleCount = count($this->form->getRules());
            }
        })
            ->call('resetAndCount')
            ->assertSetStrict('ruleCount', 1)
            ->assertSet('form', fn ($form) => $form->getRules()['title'] === 'required|min:3');
    }

    function test_a_form_object_swapped_onto_a_virtual_property_is_booted()
    {
        // Assignment is a construction path too — the replacement has to
        // arrive booted or it silently loses its rules...
        Livewire::test(new class extends TestComponent {
            public $ruleCount;

            #[Virtual]
            public function form(): VirtualFormStub
            {
                return new VirtualFormStub($this, 'form');
            }

            public function swap()
            {
                $this->fill(['form' => new VirtualFormStub($this, 'form')]);

                $this->ruleCount = count($this->form->getRules());
            }
        })
            ->call('swap')
            ->assertSetStrict('ruleCount', 1);
    }

    function test_a_form_object_constructed_under_the_wrong_name_throws()
    {
        // Attributes are registered under the name the form was constructed
        // with, so a mismatch would quietly disarm #[Locked] and #[Validate]
        // rather than protecting anything...
        $this->assertThrowsDeep(\LogicException::class, function () {
            Livewire::test(new class extends TestComponent {
                #[Virtual]
                public function form(): VirtualFormStub
                {
                    return new VirtualFormStub($this, 'frm');
                }
            });
        });
    }

    function test_a_virtual_method_that_reads_an_uninitialized_sibling_names_itself_in_the_error()
    {
        // The raw PHP error mentions neither the method nor the fact that
        // virtual methods run before mount()...
        $this->assertThrowsDeep(VirtualPropertyConstructionException::class, function () {
            Livewire::test(new class extends TestComponent {
                public VirtualSiblingStub $sibling;

                public function mount()
                {
                    $this->sibling = new VirtualSiblingStub;
                }

                #[Virtual]
                public function copied(): Selection
                {
                    return new Selection(keys: [$this->sibling->key]);
                }
            });
        });
    }

    function test_a_form_object_assigned_to_a_declared_property_still_boots()
    {
        // The synth path is untouched by all of the above...
        VirtualFormStub::$boots = 0;

        Livewire::test(new class extends TestComponent {
            public VirtualFormStub $form;

            public function save()
            {
                $this->form->validate();
            }
        })
            ->call('save')
            ->assertHasErrors('form.title');

        Assert::assertSame(2, VirtualFormStub::$boots);
    }
}

class VirtualSiblingStub
{
    public $key = 'sibling';
}

class VirtualFormStub extends \Livewire\Form
{
    public static $boots = 0;

    #[\Livewire\Attributes\Validate('required|min:3')]
    public $title = '';

    #[\Livewire\Attributes\Url]
    public $q = '';

    #[\Livewire\Attributes\Locked]
    public $secret = 'original';

    public function boot()
    {
        static::$boots++;
    }
}
