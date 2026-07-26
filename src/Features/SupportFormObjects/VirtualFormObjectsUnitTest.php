<?php

namespace Livewire\Features\SupportFormObjects;

use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Virtual;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Form;
use Livewire\Livewire;
use PHPUnit\Framework\Assert;
use Tests\TestComponent;

// A form born from a #[Virtual] method must behave exactly like a declared
// one: same boot, same #[Validate]/#[Url]/#[Locked] powers, same consolidated
// update handling. These tests pin every capability that used to silently
// fall away on the virtual birth path...
class VirtualFormObjectsUnitTest extends \Tests\TestCase
{
    function setUp(): void
    {
        parent::setUp();

        VirtualDraftFormStub::$bootCount = 0;
        VirtualDraftFormStub::$updatedHooks = [];
    }

    // --- form_boot -------------------------------------------------------

    function test_virtual_form_boot_runs_on_mount()
    {
        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function draft(): VirtualDraftFormStub
            {
                return new VirtualDraftFormStub($this, 'draft');
            }
        });

        $this->assertSame(1, VirtualDraftFormStub::$bootCount);
    }

    function test_virtual_form_boot_runs_exactly_once_per_request_including_updates()
    {
        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function draft(): VirtualDraftFormStub
            {
                return new VirtualDraftFormStub($this, 'draft');
            }
        })
        ->call('$refresh');

        // Once for the mount request, once for the update request...
        $this->assertSame(2, VirtualDraftFormStub::$bootCount);
    }

    // --- validate_rules --------------------------------------------------

    function test_virtual_form_validate_uses_attribute_rules()
    {
        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function draft(): VirtualDraftFormStub
            {
                return new VirtualDraftFormStub($this, 'draft');
            }

            public function save()
            {
                $this->draft->validate();
            }
        })
        ->call('save')
        ->assertHasErrors('draft.title')
        ->set('draft.title', 'A valid title')
        ->call('save')
        ->assertHasNoErrors('draft.title');
    }

    function test_virtual_form_rules_are_included_in_component_wide_validate()
    {
        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function draft(): VirtualDraftFormStub
            {
                return new VirtualDraftFormStub($this, 'draft');
            }

            public function save()
            {
                $this->validate();
            }
        })
        ->call('save')
        ->assertHasErrors('draft.title');
    }

    function test_virtual_form_realtime_validation_runs_on_update()
    {
        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function draft(): VirtualDraftFormStub
            {
                return new VirtualDraftFormStub($this, 'draft');
            }
        })
        ->set('draft.title', '')
        ->assertHasErrors('draft.title');
    }

    // --- url_read / url_readable_in_user_mount / url_push_effect ---------

    function test_url_query_param_lands_in_virtual_form()
    {
        Livewire::withQueryParams(['q' => 'world'])
            ->test(new class extends TestComponent {
                #[Virtual]
                public function draft(): VirtualDraftFormStub
                {
                    return new VirtualDraftFormStub($this, 'draft');
                }
            })
            ->assertSetStrict('draft.q', 'world');
    }

    function test_url_value_is_readable_from_the_virtual_form_inside_mount()
    {
        Livewire::withQueryParams(['q' => 'world'])
            ->test(new class extends TestComponent {
                public $seenInMount = '';

                public function mount()
                {
                    $this->seenInMount = $this->draft->q;
                }

                #[Virtual]
                public function draft(): VirtualDraftFormStub
                {
                    return new VirtualDraftFormStub($this, 'draft');
                }
            })
            ->assertSetStrict('seenInMount', 'world');
    }

    function test_url_values_on_plain_component_properties_are_still_readable_in_mount()
    {
        // The pre-existing ordering promise (attribute mount window before
        // user mount) — previously unpinned by any shipped test...
        Livewire::withQueryParams(['search' => 'bob'])
            ->test(new class extends TestComponent {
                #[Url]
                public $search = '';

                public $seenInMount = '';

                public function mount()
                {
                    $this->seenInMount = $this->search;
                }
            })
            ->assertSetStrict('seenInMount', 'bob');
    }

    function test_mount_writes_win_over_url_values_on_virtual_forms()
    {
        Livewire::withQueryParams(['q' => 'from-url'])
            ->test(new class extends TestComponent {
                public function mount()
                {
                    $this->draft->q = 'mount-wins';
                }

                #[Virtual]
                public function draft(): VirtualDraftFormStub
                {
                    return new VirtualDraftFormStub($this, 'draft');
                }
            })
            ->assertSetStrict('draft.q', 'mount-wins');
    }

    function test_url_effect_is_pushed_for_virtual_form_property()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function draft(): VirtualDraftFormStub
            {
                return new VirtualDraftFormStub($this, 'draft');
            }
        });

        $this->assertTrue(isset($component->effects['url']));
        $this->assertArrayHasKey('draft.q', $component->effects['url']);
    }

    // --- sibling_state_in_mount (the anti-timing test) --------------------

    function test_virtual_form_method_reads_mount_set_sibling_state_even_when_a_query_param_is_present()
    {
        // The presence of a bound query param must NOT change when the
        // method runs: it still runs after mount(), still sees mount-set
        // sibling state, and the URL value still lands in the form...
        Livewire::withQueryParams(['q' => 'from-url'])
            ->test(new class extends TestComponent {
                public $postId = null;

                public function mount()
                {
                    $this->postId = 42;
                }

                #[Virtual]
                public function draft(): VirtualDraftFormStub
                {
                    $form = new VirtualDraftFormStub($this, 'draft');

                    $form->title = 'post-'.($this->postId ?? 'MISSING');

                    return $form;
                }
            })
            ->assertSetStrict('draft.title', 'post-42')
            ->assertSetStrict('draft.q', 'from-url');
    }

    // --- locked_tamper ----------------------------------------------------

    function test_locked_property_on_virtual_form_cannot_be_updated_from_the_client()
    {
        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function draft(): VirtualDraftFormStub
            {
                return new VirtualDraftFormStub($this, 'draft');
            }
        })
        ->set('draft.id', 'hacked');
    }

    // --- consolidated_update ----------------------------------------------

    function test_consolidated_virtual_form_update_expands_per_field()
    {
        // When every form field changes, the JS diff consolidates them into a
        // single {draft: {...}} update. It must be re-expanded per-field so
        // enum casting and update hooks run — same as a declared form...
        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function draft(): VirtualDraftFormStub
            {
                return new VirtualDraftFormStub($this, 'draft');
            }

            public function check()
            {
                Assert::assertInstanceOf(VirtualFormEnumStub::class, $this->draft->status);
                Assert::assertEquals(VirtualFormEnumStub::Active, $this->draft->status);
            }
        })
        ->update(
            calls: [['method' => 'check', 'params' => [], 'path' => '']],
            updates: ['draft' => ['title' => 'A valid title', 'q' => 'hey', 'status' => 'active']],
        )
        ->assertHasNoErrors();

        $this->assertContains('title', VirtualDraftFormStub::$updatedHooks);
    }

    // --- memoized_form_reset ----------------------------------------------

    function test_reset_rebuilds_virtual_form_with_full_powers()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function draft(): VirtualDraftFormStub
            {
                return new VirtualDraftFormStub($this, 'draft');
            }

            public function resetDraft()
            {
                $this->reset('draft');
            }

            public function save()
            {
                $this->draft->validate();
            }
        })
        ->set('draft.title', 'Something')
        ->call('resetDraft')
        ->assertSetStrict('draft.title', '');

        // The replacement instance must be just as booted and validated as
        // the original — attributes belong to the path, not the instance...
        $component->call('save')->assertHasErrors('draft.title');

        $this->expectException(CannotUpdateLockedPropertyException::class);

        $component->set('draft.id', 'hacked');
    }

    function test_reset_boots_the_replacement_virtual_form()
    {
        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function draft(): VirtualDraftFormStub
            {
                return new VirtualDraftFormStub($this, 'draft');
            }

            public function resetDraft()
            {
                $this->reset('draft');
            }
        })
        ->call('resetDraft');

        // Mount request boots once. The update request boots the hydrated
        // instance once, then the reset-built replacement once more...
        $this->assertSame(3, VirtualDraftFormStub::$bootCount);
    }

    // --- update_request_correctness ----------------------------------------

    function test_granular_updates_on_virtual_forms_cast_enums_and_fire_hooks()
    {
        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function draft(): VirtualDraftFormStub
            {
                return new VirtualDraftFormStub($this, 'draft');
            }

            public function check()
            {
                Assert::assertInstanceOf(VirtualFormEnumStub::class, $this->draft->status);
            }
        })
        ->set('draft.status', 'active')
        ->call('check');

        $this->assertContains('status', VirtualDraftFormStub::$updatedHooks);
    }

    function test_virtual_form_state_survives_round_trips()
    {
        Livewire::withQueryParams(['q' => 'world'])
            ->test(new class extends TestComponent {
                #[Virtual]
                public function draft(): VirtualDraftFormStub
                {
                    return new VirtualDraftFormStub($this, 'draft');
                }
            })
            ->set('draft.title', 'kept')
            ->call('$refresh')
            ->assertSetStrict('draft.title', 'kept')
            ->assertSetStrict('draft.q', 'world');
    }

    // --- guards ------------------------------------------------------------

    function test_virtual_form_constructed_with_a_mismatched_path_fails_loudly()
    {
        $this->assertThrowsDeep(\LogicException::class, function () {
            Livewire::test(new class extends TestComponent {
                #[Virtual]
                public function draft(): VirtualDraftFormStub
                {
                    return new VirtualDraftFormStub($this, 'wrongname');
                }
            });
        });
    }

    function test_virtual_form_constructed_against_another_component_fails_loudly()
    {
        $this->assertThrowsDeep(\LogicException::class, function () {
            Livewire::test(new class extends TestComponent {
                #[Virtual]
                public function draft(): VirtualDraftFormStub
                {
                    $other = new LazyVirtualFormComponent;

                    return new VirtualDraftFormStub($other, 'draft');
                }
            });
        });
    }

    function test_anonymous_form_subclass_carrying_attributes_fails_loudly()
    {
        // Attributes are read from the declared return type. A subclass that
        // introduces its own Livewire attributes would silently lose them —
        // fail loudly instead...
        $this->assertThrowsDeep(\LogicException::class, function () {
            Livewire::test(new class extends TestComponent {
                #[Virtual]
                public function draft(): Form
                {
                    return new class($this, 'draft') extends Form {
                        #[Validate('required')]
                        public $title = '';
                    };
                }
            });
        });
    }

    function test_anonymous_form_subclass_without_attributes_works()
    {
        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function draft(): Form
            {
                return new class($this, 'draft') extends Form {
                    public $title = '';

                    public function rules()
                    {
                        return ['title' => 'required'];
                    }
                };
            }

            public function save()
            {
                $this->draft->validate();
            }
        })
        ->call('save')
        ->assertHasErrors('draft.title')
        ->set('draft.title', 'ok')
        ->call('save')
        ->assertHasNoErrors('draft.title');
    }

    // --- adversarial corners ------------------------------------------------

    function test_a_declared_and_a_virtual_form_of_the_same_class_dont_interfere()
    {
        Livewire::test(new class extends TestComponent {
            public VirtualDraftFormStub $form;

            #[Virtual]
            public function draft(): VirtualDraftFormStub
            {
                return new VirtualDraftFormStub($this, 'draft');
            }

            public function save()
            {
                $this->validate();
            }
        })
        ->set('form.title', 'declared is valid')
        ->call('save')
        ->assertHasNoErrors('form.title')
        ->assertHasErrors('draft.title');
    }

    function test_a_locked_field_inside_a_consolidated_virtual_form_update_still_throws()
    {
        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function draft(): VirtualDraftFormStub
            {
                return new VirtualDraftFormStub($this, 'draft');
            }
        })
        ->update(
            calls: [],
            updates: ['draft' => ['title' => 'x', 'q' => 'y', 'status' => 'active', 'id' => 'hacked']],
        );
    }

    function test_attribute_rules_are_visible_inside_the_forms_own_boot()
    {
        Livewire::test(new class extends TestComponent {
            #[Virtual]
            public function draft(): BootRulesSpyFormStub
            {
                return new BootRulesSpyFormStub($this, 'draft');
            }
        });

        $this->assertSame(['title' => 'required'], BootRulesSpyFormStub::$rulesSeenInBoot);
    }

    // --- lazy components ----------------------------------------------------

    function test_url_deep_link_reaches_a_virtual_form_on_a_lazy_component()
    {
        // On the placeholder request user mount() is skipped but the
        // attribute mount window runs: the #[Url] value is staged, lands
        // when the virtual form materializes during dehydration, and rides
        // the snapshot into the second request where mount() can read it...
        $component = Livewire::withQueryParams(['q' => 'world'])
            ->test(LazyVirtualFormComponent::class, ['lazy' => true]);

        $this->assertSame('world', data_get($component->snapshot, 'data.draft.0.q'));

        preg_match('/__lazyLoad\(&#039;(.*?)&#039;\)/', $component->html(), $matches);

        $component->call('__lazyLoad', $matches[1])
            ->assertSetStrict('seenInMount', 'world')
            ->assertSetStrict('draft.q', 'world');
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

    // --- declared forms: unchanged behavior guardrail ----------------------

    function test_declared_forms_still_boot_and_validate_exactly_as_before()
    {
        Livewire::test(new class extends TestComponent {
            public VirtualDraftFormStub $form;

            public function save()
            {
                $this->form->validate();
            }
        })
        ->call('save')
        ->assertHasErrors('form.title');

        $this->assertSame(2, VirtualDraftFormStub::$bootCount); // mount + update request
    }
}

class LazyVirtualFormComponent extends \Livewire\Component
{
    public $seenInMount = '';

    public function mount()
    {
        $this->seenInMount = $this->draft->q;
    }

    #[Virtual]
    public function draft(): VirtualDraftFormStub
    {
        return new VirtualDraftFormStub($this, 'draft');
    }

    public function render()
    {
        return '<div>loaded</div>';
    }
}

class VirtualDraftFormStub extends Form
{
    public static $bootCount = 0;

    public static $updatedHooks = [];

    #[Validate('required')]
    public $title = '';

    #[Url]
    public $q = '';

    #[Locked]
    public $id = 1;

    public VirtualFormEnumStub $status = VirtualFormEnumStub::Draft;

    public function boot()
    {
        static::$bootCount++;
    }

    public function updatedTitle()
    {
        static::$updatedHooks[] = 'title';
    }

    public function updatedStatus()
    {
        static::$updatedHooks[] = 'status';
    }
}

enum VirtualFormEnumStub: string
{
    case Draft = 'draft';
    case Active = 'active';
}

class BootRulesSpyFormStub extends Form
{
    public static $rulesSeenInBoot = null;

    #[Validate('required')]
    public $title = '';

    public function boot()
    {
        static::$rulesSeenInBoot = $this->getRules();
    }
}
