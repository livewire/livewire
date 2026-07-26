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

    function test_a_form_object_from_a_virtual_property_runs_its_boot_method()
    {
        VirtualFormStub::$bootLog = [];

        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function form(): VirtualFormStub
            {
                return new VirtualFormStub($this, 'form');
            }
        });

        Assert::assertSame(['booted'], VirtualFormStub::$bootLog);
    }

    function test_a_form_object_from_a_virtual_property_runs_its_boot_method_on_subsequent_requests()
    {
        VirtualFormStub::$bootLog = [];

        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function form(): VirtualFormStub
            {
                return new VirtualFormStub($this, 'form');
            }
        })->call('$refresh');

        Assert::assertSame(['booted', 'booted'], VirtualFormStub::$bootLog);
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

    function test_a_virtual_form_object_first_accessed_inside_mount_gets_url_and_rules()
    {
        // First access happens INSIDE the user's mount() — i.e. inside an
        // open Component::__get() frame. PHP's magic-method recursion guard
        // bypasses __get on nested access, so any attribute replay that
        // writes through the component would shadow the virtual property
        // with a plain array. The write must land on the form directly...
        Livewire::withQueryParams(['q' => 'from-url'])
            ->test(new class extends TestComponent {
                public $titleAtMount;

                public function mount()
                {
                    $this->titleAtMount = $this->form->title;
                }

                #[Virtual]
                public function form(): VirtualFormStub
                {
                    return new VirtualFormStub($this, 'form');
                }
            })
            ->assertSetStrict('titleAtMount', '')
            ->assertSet('form', fn ($form) => $form instanceof VirtualFormStub)
            ->assertSetStrict('form.q', 'from-url');
    }

    function test_a_whole_form_update_on_a_virtual_property_expands_into_per_field_updates()
    {
        // Sending the entire form as one consolidated update should behave
        // exactly like setting each field individually (running per-field
        // update hooks) — the same guarantee declared form objects have...
        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function form(): VirtualFormStub
            {
                return new VirtualFormStub($this, 'form');
            }
        })
            ->set('form', ['title' => 'ab', 'q' => '', 'secret' => 'original'])
            ->assertSetStrict('form.title', 'ab')
            ->assertHasErrors('form.title');
    }

    function test_a_changed_locked_value_inside_a_whole_form_update_still_throws()
    {
        $this->expectException(\Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException::class);

        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function form(): VirtualFormStub
            {
                return new VirtualFormStub($this, 'form');
            }
        })
            ->set('form', ['title' => 'ab', 'q' => '', 'secret' => 'HACKED']);
    }

    function test_an_unchanged_echo_of_a_json_lossy_locked_field_does_not_trip_the_lock()
    {
        // The browser can't express 1.0-as-float — a JSON number round-trips
        // back as the int 1. An echoed, unchanged locked float must compare
        // equal to its snapshot value, not trip #[Locked]...
        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function form(): VirtualFormWithFloatStub
            {
                return new VirtualFormWithFloatStub($this, 'form');
            }
        })
            ->set('form', ['title' => 'changed', 'price' => 1])
            ->assertSetStrict('form.title', 'changed')
            ->assertSetStrict('form.price', 1.0);
    }

    function test_a_memoized_virtual_form_does_not_reregister_its_rules_when_reconstructed()
    {
        // A method free to memoize its instance hands back the SAME form on
        // reconstruction. Booting it again would merge its attributes a
        // second time — doubling every rule it registered...
        Livewire::test(new class extends TestComponent {
            protected $memo;

            public $ruleCount;

            public $attributeCountsMatch;

            #[Virtual]
            public function form(): VirtualFormStub
            {
                return $this->memo ??= new VirtualFormStub($this, 'form');
            }

            public function resetTwice()
            {
                $this->reset('form');
                $before = $this->getAttributes()->count();

                $this->reset('form');
                $after = $this->getAttributes()->count();

                $this->ruleCount = count($this->form->getRules());
                $this->attributeCountsMatch = $before === $after;
            }
        })
            ->call('resetTwice')
            ->assertSetStrict('ruleCount', 1)
            // A doubled merge turns the rule string into an array via
            // array_merge_recursive — assert the shape survived too...
            ->assertSet('form', fn ($form) => $form->getRules()['title'] === 'required|min:3')
            ->assertSetStrict('attributeCountsMatch', true);
    }

    function test_an_aliased_path_does_not_resolve_a_virtual_form_object()
    {
        // hasVirtualProperty() camelCases its input — the consolidated-update
        // expansion must not let [Form] reach the virtual [form]...
        $this->expectException(\Livewire\Exceptions\PublicPropertyNotFoundException::class);

        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function form(): VirtualFormStub
            {
                return new VirtualFormStub($this, 'form');
            }
        })
            ->set('Form', ['secret' => 'original']);
    }
}

class VirtualFormWithFloatStub extends \Livewire\Form
{
    public $title = '';

    #[\Livewire\Attributes\Locked]
    public float $price = 1.0;
}

class VirtualFormStub extends \Livewire\Form
{
    public static $bootLog = [];

    #[\Livewire\Attributes\Validate('required|min:3')]
    public $title = '';

    #[\Livewire\Attributes\Url]
    public $q = '';

    #[\Livewire\Attributes\Locked]
    public $secret = 'original';

    public function boot()
    {
        static::$bootLog[] = 'booted';
    }
}
