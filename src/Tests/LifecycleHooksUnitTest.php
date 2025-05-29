<?php

namespace Livewire\Tests;

use Livewire\Livewire;
use Illuminate\Support\Stringable;
use PHPUnit\Framework\Assert as PHPUnit;
use Tests\TestComponent;

class CustomException extends \Exception {};

class LifecycleHooksUnitTest extends \Tests\TestCase
{

    public function test_refresh_magic_method()
    {
        $component = Livewire::test(ForMagicMethods::class);

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
            'caughtException' => false,
        ], $component->lifecycles);
    }

    public function test_set_magic_method()
    {
        $component = Livewire::test(ForMagicMethods::class, [
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
            'caughtException' => false,
        ], $component->lifecycles);


        $component->call('testExceptionInterceptor');
        $this->assertTrue($component->lifecycles['caughtException']);

    }
}

class ForMagicMethods extends TestComponent
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
        'caughtException' => false,
    ];

    public function mount(array $expected = [])
    {
        $this->expected = $expected;
        $this->lifecycles['mount'] = true;
    }

    public function exception($e, $stopPropagation)
    {
        if ($e instanceof CustomException) {
            $this->lifecycles['caughtException'] = true;
            $stopPropagation();
        }
    }

    public function testExceptionInterceptor()
    {
        throw new CustomException;
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
}
