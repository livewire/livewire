<?php

namespace Livewire\Mechanisms\Tests;

use Livewire\Livewire;
use Livewire\Exceptions\ComponentNotFoundException;
use Livewire\Exceptions\MethodNotFoundException;

class ComponentRegistryUnitTest extends \Tests\TestCase
{
    public function test_calling_nonexistent_method_throws_method_not_found_exception_not_component_not_found()
    {
        $this->expectException(MethodNotFoundException::class);
        $this->expectExceptionMessage('Public method [nonExistentMethod] not found on component');

        Livewire::test(ComponentRegistryTestComponent::class)
            ->call('nonExistentMethod');
    }

    public function test_component_can_be_instantiated_successfully()
    {
        $component = Livewire::test(ComponentRegistryTestComponent::class);

        $component->assertSee('Component Registry Test');
    }

    public function test_calling_existing_method_works()
    {
        $component = Livewire::test(ComponentRegistryTestComponent::class);

        $component->call('existingMethod')
            ->assertSet('called', true);
    }
}

class ComponentRegistryTestComponent extends \Livewire\Component
{
    public $called = false;

    public function existingMethod()
    {
        $this->called = true;
    }

    public function render()
    {
        return app('view')->make('show-name', [
            'name' => 'Component Registry Test',
        ]);
    }
}
