<?php

namespace Tests\Unit;

use Illuminate\Support\Stringable;
use Livewire\Component;
use Livewire\Livewire;
use PHPUnit\Framework\Assert as PHPUnit;

class LifecycleHooksTest extends TestCase
{
    /** @test */
    public function mount_hook()
    {
        $component = Livewire::test(ForLifecycleHooks::class);

        $this->assertEquals([
            'mount' => true,
            'hydrate' => 0,
            'hydrateFoo' => 0,
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

    /** @test */
    public function refresh_magic_method()
    {
        $component = Livewire::test(ForLifecycleHooks::class);

        $component->call('$refresh');

        $this->assertEquals([
            'mount' => true,
            'hydrate' => 1,
            'hydrateFoo' => 1,
            'dehydrate' => 2,
            'dehydrateFoo' => 2,
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

    /** @test */
    public function update_property()
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

    /** @test */
    public function update_nested_properties()
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

        $component->updateProperty('bar.foo', 'baz');

        $component->updateProperty('bar.cocktail.soft', 'Shirley Ginger');

        $component->updateProperty('bar.cocktail.soft', 'Shirley Cumin');

        $this->assertEquals([
            'mount' => true,
            'hydrate' => 3,
            'hydrateFoo' => 3,
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

    /** @test */
    public function update_nested_properties_with_nested_update_hook()
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

    /** @test */
    public function set_magic_method()
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
        ]);

        $component->call('$set', 'foo', 'bar');

        $this->assertEquals([
            'mount' => true,
            'hydrate' => 1,
            'hydrateFoo' => 1,
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
}

class ForLifecycleHooks extends Component
{
    public $foo;

    public $baz;

    public $bar = [];

    public $expected;

    public $lifecycles = [
        'mount' => false,
        'hydrate' => 0,
        'hydrateFoo' => 0,
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

    public function render()
    {
        return app('view')->make('null-view');
    }
}
