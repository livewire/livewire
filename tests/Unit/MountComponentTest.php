<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;

class MountComponentTest extends TestCase
{
    /** @test */
    public function it_resolves_the_mount_parameters()
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

    /** @test */
    public function it_sets_missing_dynamically_passed_in_parameters_to_null()
    {
        $fooBar = ['foo' => 10, 'bar' => 5];
        $componentWithFooBar = Livewire::test(ComponentWithOptionalParameters::class, $fooBar);
        $componentWithOnlyFoo = Livewire::test(ComponentWithOnlyFooParameter::class, $fooBar);

        $this->assertSame(10, $componentWithFooBar->foo);
        $this->assertSame(10, $componentWithOnlyFoo->foo);

        $this->assertSame(5, $componentWithFooBar->bar);
        $this->assertSame(null, data_get($componentWithOnlyFoo->instance(), 'bar'));
    }
}

class ComponentWithOptionalParameters extends Component
{
    public $foo;
    public $bar;

    public function mount($foo = null, $bar = [])
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }

    public function render()
    {
        return view('null-view');
    }
}

class ComponentWithOnlyFooParameter extends Component
{
    public $foo;

    public function mount($foo = null)
    {
        $this->foo = $foo;
    }

    public function render()
    {
        return view('null-view');
    }
}

class ComponentWithoutMount extends Component
{
    public $foo = 0;

    public function render()
    {
        return view('null-view');
    }
}
