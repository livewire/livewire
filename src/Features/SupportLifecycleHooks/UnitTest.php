<?php

namespace Livewire\Features\SupportLifecycleHooks;

use Illuminate\Support\Stringable;
use Livewire\Component;
use Livewire\Livewire;
use PHPUnit\Framework\Assert as PHPUnit;
use Tests\TestComponent;

class UnitTest extends \Tests\TestCase
{
    public function test_cant_call_protected_lifecycle_hooks()
    {
        $this->assertTrue(
            collect([
                'mount',
                'hydrate',
                'hydrateFoo',
                'dehydrate',
                'dehydrateFoo',
                'updating',
                'updatingFoo',
                'updated',
                'updatedFoo',
            ])->every(function ($method) {
                return $this->cannotCallMethod($method);
            })
        );
    }

    protected function cannotCallMethod($method)
    {
        try {
            Livewire::test(ForProtectedLifecycleHooks::class)->call($method);
        } catch (DirectlyCallingLifecycleHooksNotAllowedException $e) {
            return true;
        }

        return false;
    }

    public function test_boot_method_is_called_on_mount_and_on_subsequent_updates()
    {
        Livewire::test(ComponentWithBootMethod::class)
            ->assertSetStrict('memo', 'bootmountbooted')
            ->call('$refresh')
            ->assertSetStrict('memo', 'boothydratebooted');
    }

    public function test_boot_method_can_be_added_to_trait()
    {
        Livewire::test(ComponentWithBootTrait::class)
            ->assertSetStrict('memo', 'boottraitboottraitinitializemountbootedtraitbooted')
            ->call('$refresh')
            ->assertSetStrict('memo', 'boottraitboottraitinitializehydratebootedtraitbooted');
    }

    public function test_boot_method_supports_dependency_injection()
    {
        Livewire::test(ComponentWithBootMethodDI::class)
            ->assertSetStrict('memo', 'boottraitbootbootedtraitbooted')
            ->call('$refresh')
            ->assertSetStrict('memo', 'boottraitbootbootedtraitbooted');
    }

    public function test_it_resolves_the_mount_parameters()
    {
        $component = Livewire::test(ComponentWithOptionalParameters::class);
        $this->assertSame(null, $component->foo);
        $this->assertSame([], $component->bar);

        $component = Livewire::test(ComponentWithOptionalParameters::class, ['foo' => 'caleb']);
        $this->assertSame('caleb', $component->foo);
        $this->assertSame([], $component->bar);

        $component = Livewire::test(ComponentWithOptionalParameters::class, ['bar' => 'porzio']);
        $this->assertSame(null, $component->foo);
        $this->assertSame('porzio', $component->bar);

        $component = Livewire::test(ComponentWithOptionalParameters::class, ['foo' => 'caleb', 'bar' => 'porzio']);
        $this->assertSame('caleb', $component->foo);
        $this->assertSame('porzio', $component->bar);

        $component = Livewire::test(ComponentWithOptionalParameters::class, ['foo' => null, 'bar' => null]);
        $this->assertSame(null, $component->foo);
        $this->assertSame(null, $component->bar);
    }

    public function test_it_sets_missing_dynamically_passed_in_parameters_to_null()
    {
        $fooBar = ['foo' => 10, 'bar' => 5];
        $componentWithFooBar = Livewire::test(ComponentWithOptionalParameters::class, $fooBar);
        $componentWithOnlyFoo = Livewire::test(ComponentWithOnlyFooParameter::class, $fooBar);

        $this->assertSame(10, $componentWithFooBar->foo);
        $this->assertSame(10, $componentWithOnlyFoo->foo);

        $this->assertSame(5, $componentWithFooBar->bar);
        $this->assertSame(null, data_get($componentWithOnlyFoo->instance(), 'bar'));
    }
    public function test_mount_hook()
    {
        $component = Livewire::test(ForLifecycleHooks::class);

        $this->assertEquals([
            'mount' => true,
            'hydrate' => 0,
            'hydrateFoo' => 0,
            'rendering' => 1,
            'rendered' => 1,
            'dehydrate' => 1,
            'dehydrateFoo' => 1,
            'updating' => false,
            'updated' => false,
            'updatingFoo' => false,
            'updatedFoo' => false,
            'updatingBar' => false,
            'updatingBarBaz' => false,
            'updatedBar' => false,
            'updatedBarBaz' => false,
        ], $component->lifecycles);
    }

    public function test_update_property()
    {
        $component = Livewire::test(ForLifecycleHooks::class, [
            'expected' => [
                'updating' => [[
                    'foo' => 'bar',
                ]],
                'updated' => [[
                    'foo' => 'bar',
                ]],
                'updatingFoo' => ['bar'],
                'updatedFoo' => ['bar'],
            ]
        ])->set('foo', 'bar');

        $this->assertEquals([
            'mount' => true,
            'hydrate' => 1,
            'hydrateFoo' => 1,
            'rendering' => 2,
            'rendered' => 2,
            'dehydrate' => 2,
            'dehydrateFoo' => 2,
            'updating' => true,
            'updated' => true,
            'updatingFoo' => true,
            'updatedFoo' => true,
            'updatingBar' => false,
            'updatingBarBaz' => false,
            'updatedBar' => false,
            'updatedBarBaz' => false,
        ], $component->lifecycles);
    }

    public function test_update_nested_properties()
    {
        $component = Livewire::test(ForLifecycleHooks::class, [
            'expected' => [
                'updating' => [
                    ['bar.foo' => 'baz',],
                    ['bar.cocktail.soft' => 'Shirley Ginger'],
                    ['bar.cocktail.soft' => 'Shirley Cumin']
                ],
                'updated' => [
                    ['bar.foo' => 'baz',],
                    ['bar.cocktail.soft' => 'Shirley Ginger'],
                    ['bar.cocktail.soft' => 'Shirley Cumin']
                ],
                'updatingBar' => [
                    ['foo' => [null, 'baz']],
                    ['cocktail.soft' => [null, 'Shirley Ginger']],
                    ['cocktail.soft' => ['Shirley Ginger', 'Shirley Cumin']]
                ],
                'updatedBar' => [
                    ['foo' => 'baz'],
                    ['cocktail.soft' => 'Shirley Ginger'],
                    ['cocktail.soft' => 'Shirley Cumin']
                ],
            ],
        ]);

        $component->set('bar.foo', 'baz');

        $component->set('bar.cocktail.soft', 'Shirley Ginger');

        $component->set('bar.cocktail.soft', 'Shirley Cumin');

        $this->assertEquals([
            'mount' => true,
            'hydrate' => 3,
            'hydrateFoo' => 3,
            'rendering' => 4,
            'rendered' => 4,
            'dehydrate' => 4,
            'dehydrateFoo' => 4,
            'updating' => true,
            'updated' => true,
            'updatingFoo' => false,
            'updatedFoo' => false,
            'updatingBar' => true,
            'updatingBarBaz' => false,
            'updatedBar' => true,
            'updatedBarBaz' => false,
        ], $component->lifecycles);
    }

    public function test_update_nested_properties_with_nested_update_hook()
    {
        $component = Livewire::test(ForLifecycleHooks::class, [
            'expected' => [
                'updating' => [
                    ['bar.baz' => 'bop'],
                ],
                'updated' => [
                    ['bar.baz' => 'bop'],
                ],
                'updatingBar' => [
                    ['baz' => [null, 'bop']],
                ],
                'updatedBar' => [
                    ['baz' => 'bop'],
                ],
                'updatingBarBaz' => [
                    ['baz' => [null, 'bop']],
                ],
                'updatedBarBaz' => [
                    ['baz' => 'bop'],
                ],
            ]
        ]);

        $component->set('bar.baz', 'bop');

        $this->assertEquals([
            'mount' => true,
            'hydrate' => true,
            'hydrateFoo' => true,
            'rendering' => true,
            'rendered' => true,
            'dehydrate' => true,
            'dehydrateFoo' => true,
            'updating' => true,
            'updated' => true,
            'updatingFoo' => false,
            'updatedFoo' => false,
            'updatingBar' => true,
            'updatingBarBaz' => true,
            'updatedBar' => true,
            'updatedBarBaz' => true,
        ], $component->lifecycles);
    }
}

class ForProtectedLifecycleHooks extends TestComponent
{
    public function mount()
    {
        //
    }

    public function hydrate()
    {
        //
    }

    public function hydrateFoo()
    {
        //
    }

    public function dehydrate()
    {
        //
    }

    public function dehydrateFoo()
    {
        //
    }

    public function updating($name, $value)
    {
        //
    }

    public function updated($name, $value)
    {
        //
    }

    public function updatingFoo($value)
    {
        //
    }

    public function updatedFoo($value)
    {
        //
    }
}

class ComponentWithBootMethod extends Component
{
    // Use protected property to record all memo's
    // as hydrating memo wipes out changes from boot
    protected $_memo = '';
    public $memo = '';

    public function boot()
    {
        $this->_memo .= 'boot';
    }

    public function mount()
    {
        $this->_memo .= 'mount';
    }

    public function hydrate()
    {
        $this->_memo .= 'hydrate';
    }

    public function booted()
    {
        $this->_memo .= 'booted';
    }

    public function render()
    {
        $this->memo = $this->_memo;

        return view('null-view');
    }
}

class ComponentWithBootTrait extends Component
{
    use BootMethodTrait;

    // Use protected property to record all memo's
    // as hydrating memo wipes out changes from boot
    protected $_memo = '';
    public $memo = '';

    public function boot()
    {
        $this->_memo .= 'boot';
    }

    public function mount()
    {
        $this->_memo .= 'mount';
    }

    public function hydrate()
    {
        $this->_memo .= 'hydrate';
    }

    public function booted()
    {
        $this->_memo .= 'booted';
    }

    public function render()
    {
        $this->memo = $this->_memo;

        return view('null-view');
    }
}

trait BootMethodTrait
{
    public function bootBootMethodTrait()
    {
        $this->_memo .= 'traitboot';
    }

    public function initializeBootMethodTrait()
    {
        $this->_memo .= 'traitinitialize';
    }

    public function bootedBootMethodTrait()
    {
        $this->_memo .= 'traitbooted';
    }
}

trait BootMethodTraitWithDI
{
    public function bootBootMethodTraitWithDI(Stringable $string)
    {
        $this->_memo .= $string->append('traitboot');
    }

    public function bootedBootMethodTraitWithDI(Stringable $string)
    {
        $this->_memo .= $string->append('traitbooted');
    }
}

class ComponentWithBootMethodDI extends Component
{
    use BootMethodTraitWithDI;

    // Use protected property to record all memo's
    // as hydrating memo wipes out changes from boot
    protected $_memo = '';
    public $memo = '';

    public function boot(Stringable $string)
    {
        $this->_memo .= $string->append('boot');
    }

    public function booted(Stringable $string)
    {
        $this->_memo .= $string->append('booted');
    }

    public function render()
    {
        $this->memo = $this->_memo;

        return view('null-view');
    }
}

class ComponentWithOptionalParameters extends TestComponent
{
    public $foo;
    public $bar;

    public function mount($foo = null, $bar = [])
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}

class ComponentWithOnlyFooParameter extends TestComponent
{
    public $foo;

    public function mount($foo = null)
    {
        $this->foo = $foo;
    }
}

class ComponentWithoutMount extends TestComponent
{
    public $foo = 0;
}

class ForLifecycleHooks extends TestComponent
{
    public $foo;

    public $baz;

    public $bar = [];

    public $expected;

    public $lifecycles = [
        'mount' => false,
        'hydrate' => 0,
        'hydrateFoo' => 0,
        'rendering' => 0,
        'rendered' => 0,
        'dehydrate' => 0,
        'dehydrateFoo' => 0,
        'updating' => false,
        'updated' => false,
        'updatingFoo' => false,
        'updatedFoo' => false,
        'updatingBar' => false,
        'updatingBarBaz' => false,
        'updatedBar' => false,
        'updatedBarBaz' => false,
    ];

    public function mount(array $expected = [])
    {
        $this->expected = $expected;

        $this->lifecycles['mount'] = true;
    }

    public function hydrate()
    {
        $this->lifecycles['hydrate']++;
    }

    public function hydrateFoo()
    {
        $this->lifecycles['hydrateFoo']++;
    }

    public function dehydrate()
    {
        $this->lifecycles['dehydrate']++;
    }

    public function dehydrateFoo()
    {
        $this->lifecycles['dehydrateFoo']++;
    }

    public function updating($name, $value)
    {
        PHPUnit::assertEquals(array_shift($this->expected['updating']), [$name => $value]);

        $this->lifecycles['updating'] = true;
    }

    public function updated($name, $value)
    {
        PHPUnit::assertEquals(array_shift($this->expected['updated']), [$name => $value]);

        $this->lifecycles['updated'] = true;
    }

    public function updatingFoo($value)
    {
        PHPUnit::assertEquals(array_shift($this->expected['updatingFoo']), $value);

        $this->lifecycles['updatingFoo'] = true;
    }

    public function updatedFoo($value)
    {
        PHPUnit::assertEquals(array_shift($this->expected['updatedFoo']), $value);

        $this->lifecycles['updatedFoo'] = true;
    }

    public function updatingBar($value, $key)
    {
        $expected = array_shift($this->expected['updatingBar']);
        $expected_key = array_keys($expected)[0];
        $expected_value = $expected[$expected_key];
        [$before, $after] = $expected_value;

        PHPUnit::assertNotInstanceOf(Stringable::class, $key);
        PHPUnit::assertEquals($expected_key, $key);
        PHPUnit::assertEquals($before, data_get($this->bar, $key));
        PHPUnit::assertEquals($after, $value);

        $this->lifecycles['updatingBar'] = true;
    }

    public function updatedBar($value, $key)
    {
        $expected = array_shift($this->expected['updatedBar']);
        $expected_key = array_keys($expected)[0];
        $expected_value = $expected[$expected_key];

        PHPUnit::assertNotInstanceOf(Stringable::class, $key);
        PHPUnit::assertEquals($expected_key, $key);
        PHPUnit::assertEquals($expected_value, $value);
        PHPUnit::assertEquals($expected_value, data_get($this->bar, $key));

        $this->lifecycles['updatedBar'] = true;
    }

    public function updatingBarBaz($value, $key)
    {
        $expected = array_shift($this->expected['updatingBarBaz']);
        $expected_key = array_keys($expected)[0];
        $expected_value = $expected[$expected_key];
        [$before, $after] = $expected_value;

        PHPUnit::assertNotInstanceOf(Stringable::class, $key);
        PHPUnit::assertEquals($expected_key, $key);
        PHPUnit::assertEquals($before, data_get($this->bar, $key));
        PHPUnit::assertEquals($after, $value);

        $this->lifecycles['updatingBarBaz'] = true;
    }

    public function updatedBarBaz($value, $key)
    {
        $expected = array_shift($this->expected['updatedBarBaz']);
        $expected_key = array_keys($expected)[0];
        $expected_value = $expected[$expected_key];

        PHPUnit::assertNotInstanceOf(Stringable::class, $key);
        PHPUnit::assertEquals($expected_key, $key);
        PHPUnit::assertEquals($expected_value, $value);
        PHPUnit::assertEquals($expected_value, data_get($this->bar, $key));

        $this->lifecycles['updatedBarBaz'] = true;
    }

    public function rendering()
    {
        $this->lifecycles['rendering']++;
    }

    public function rendered()
    {
        $this->lifecycles['rendered']++;
    }
}
