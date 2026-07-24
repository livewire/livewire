<?php

namespace Livewire\Features\SupportPropertyFactories;

use Illuminate\Support\Collection;
use Livewire\Attributes\Factory;
use Livewire\Livewire;
use Livewire\Selection;
use PHPUnit\Framework\Assert;
use Tests\TestComponent;

class UnitTest extends \Tests\TestCase
{
    function test_a_factory_method_initializes_a_property_on_mount()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[Factory]
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

    function test_a_factory_property_is_accessible_from_mount_and_actions()
    {
        Livewire::test(new class extends TestComponent {
            public $countAtMount;

            public function mount()
            {
                $this->countAtMount = count($this->items);
            }

            #[Factory]
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

    function test_a_factory_property_is_available_as_a_plain_variable_in_the_view()
    {
        Livewire::test(new class extends TestComponent {
            #[Factory]
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

    function test_a_factory_property_dehydrates_into_snapshot_data_like_a_normal_property()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[Factory]
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

    function test_a_factory_method_cannot_be_called_as_an_action()
    {
        $this->expectException(CannotCallFactoryDirectlyException::class);

        Livewire::test(new class extends TestComponent {
            #[Factory]
            public function selected(): Selection
            {
                return new Selection;
            }
        })->call('selected');
    }

    function test_a_factory_method_must_declare_a_return_type()
    {
        $this->assertThrowsDeep(FactoryMissingReturnTypeException::class, function () {
            Livewire::test(new class extends TestComponent {
                #[Factory]
                public function selected()
                {
                    return new Selection;
                }
            });
        });
    }

    function test_a_factory_method_cannot_share_a_name_with_a_declared_property()
    {
        $this->assertThrowsDeep(\LogicException::class, function () {
            Livewire::test(new class extends TestComponent {
                public $selected = [];

                #[Factory]
                public function selected(): Selection
                {
                    return new Selection;
                }
            });
        });
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

    function test_root_updates_are_hydrated_into_the_live_instance_rather_than_replacing_it()
    {
        Livewire::test(new class extends TestComponent {
            public $hydratedId;

            public $sameInstance;

            #[Factory]
            public function selected(): Selection
            {
                return new Selection;
            }

            public function hydrate()
            {
                $this->hydratedId = spl_object_id($this->selected);
            }

            public function updatedSelected()
            {
                $this->sameInstance = spl_object_id($this->selected) === $this->hydratedId;
            }
        })
            ->set('selected', ['a', 'b'])
            ->assertSetStrict('sameInstance', true)
            ->assertSet('selected', fn ($selected) => $selected->keys() === ['a', 'b']);
    }

    function test_unsetting_a_factory_property_resets_it_to_a_fresh_factory_instance()
    {
        Livewire::test(new class extends TestComponent {
            #[Factory]
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
            #[Factory]
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

    function test_a_garbage_client_update_still_lands_as_the_factory_type()
    {
        // Updates pass through the property's synthesizer (resolved from
        // server-owned snapshot meta), so a hostile payload can never
        // change the property's type out from under the component...
        $component = Livewire::test(new class extends TestComponent {
            #[Factory]
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
