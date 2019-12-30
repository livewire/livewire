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
        ], $component->lifecycles);

        $component->runAction('$refresh');

        $this->assertEquals([
            'mount' => true,
            'hydrate' => true,
            'updating' => false,
            'updated' => false,
            'updatingFoo' => false,
            'updatedFoo' => false,
        ], $component->lifecycles);

        $component->updateProperty('baz', 'bing');

        $this->assertEquals([
            'mount' => true,
            'hydrate' => true,
            'updating' => true,
            'updated' => true,
            'updatingFoo' => false,
            'updatedFoo' => false,
        ], $component->lifecycles);

        $component->updateProperty('foo', 'bar');

        $this->assertEquals([
            'mount' => true,
            'hydrate' => true,
            'updating' => true,
            'updated' => true,
            'updatingFoo' => true,
            'updatedFoo' => true,
        ], $component->lifecycles);
    }
}

class ForLifecycleHooks extends Component
{
    public $foo;
    public $baz;
    public $lifecycles = [
        'mount' => false,
        'hydrate' => false,
        'updating' => false,
        'updated' => false,
        'updatingFoo' => false,
        'updatedFoo' => false,
    ];

    public function mount()
    {
        $this->lifecycles['mount'] = true;
    }

    public function hydrate()
    {
        $this->lifecycles['hydrate'] = true;
    }

    public function updating($name, $value)
    {
        PHPUnit::assertTrue($name === 'foo' || $name === 'baz' || $name === 'bar.foo' || $name === 'bar.cocktail.soft');
        PHPUnit::assertTrue($value === 'bar' || $value === 'bing' || $value === 'baz' || $value === 'Shirley Ginger');

        $this->lifecycles['updating'] = true;
    }

    public function updated($name, $value)
    {
        PHPUnit::assertTrue($name === 'foo' || $name === 'baz' || $name === 'bar.foo' || $name === 'bar.cocktail.soft');
        PHPUnit::assertTrue($value === 'bar' || $value === 'bing' || $value === 'baz' || $value === 'Shirley Ginger');

        $this->lifecycles['updated'] = true;
    }

    public function updatingFoo($value)
    {
        PHPUnit::assertNull($this->foo);
        PHPUnit::assertSame($value, 'bar');

        $this->lifecycles['updatingFoo'] = true;
    }

    public function updatedFoo($value)
    {
        PHPUnit::assertSame($this->foo, 'bar');
        PHPUnit::assertSame($value, 'bar');

        $this->lifecycles['updatedFoo'] = true;
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
