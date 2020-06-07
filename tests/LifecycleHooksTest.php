<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;
use PHPUnit\Framework\Assert as PHPUnit;

class LifecycleHooksTest extends TestCase
{
    /** @test */
    public function mount_hook()
    {
        $component = app(LivewireManager::class)->test(ForLifecycleHooks::class);

        $this->assertEquals([
            'mount' => true,
            'hydrate' => false,
            'updating' => false,
            'updated' => false,
            'updatingFoo' => false,
            'updatedFoo' => false,
            'updatingBar' => false,
            'updatedBar' => false,
        ], $component->lifecycles);
    }

    /** @test */
    public function refresh_magic_method()
    {
        $component = app(LivewireManager::class)->test(ForLifecycleHooks::class);

        $component->call('$refresh');

        $this->assertEquals([
            'mount' => true,
            'hydrate' => true,
            'updating' => false,
            'updated' => false,
            'updatingFoo' => false,
            'updatedFoo' => false,
            'updatingBar' => false,
            'updatedBar' => false,
        ], $component->lifecycles);
    }

    /** @test */
    public function update_property()
    {
        $component = app(LivewireManager::class)->test(ForLifecycleHooks::class, [
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
            'hydrate' => true,
            'updating' => true,
            'updated' => true,
            'updatingFoo' => true,
            'updatedFoo' => true,
            'updatingBar' => false,
            'updatedBar' => false,
        ], $component->lifecycles);
    }

    /** @test */
    public function update_nested_properties()
    {
        $component = app(LivewireManager::class)->test(ForLifecycleHooks::class, [
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
                ]
            ]
        ]);

        $component->updateProperty('bar.foo', 'baz');

        $component->updateProperty('bar.cocktail.soft', 'Shirley Ginger');

        $component->updateProperty('bar.cocktail.soft', 'Shirley Cumin');

        $this->assertEquals([
            'mount' => true,
            'hydrate' => true,
            'updating' => true,
            'updated' => true,
            'updatingFoo' => false,
            'updatedFoo' => false,
            'updatingBar' => true,
            'updatedBar' => true,
        ], $component->lifecycles);
    }

    /** @test */
    public function set_magic_method()
    {
        $component = app(LivewireManager::class)->test(ForLifecycleHooks::class, [
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
            'hydrate' => true,
            'updating' => true,
            'updated' => true,
            'updatingFoo' => true,
            'updatedFoo' => true,
            'updatingBar' => false,
            'updatedBar' => false,
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
        'hydrate' => false,
        'updating' => false,
        'updated' => false,
        'updatingFoo' => false,
        'updatedFoo' => false,
        'updatingBar' => false,
        'updatedBar' => false,
    ];

    public function mount(array $expected = [])
    {
        $this->expected = $expected;

        $this->lifecycles['mount'] = true;
    }

    public function hydrate()
    {
        $this->lifecycles['hydrate'] = true;
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

        PHPUnit::assertEquals($expected_key, $key);
        PHPUnit::assertEquals($expected_value, $value);
        PHPUnit::assertEquals($expected_value, data_get($this->bar, $key));

        $this->lifecycles['updatedBar'] = true;
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
