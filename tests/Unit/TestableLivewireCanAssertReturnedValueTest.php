<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;
use Livewire\LivewireManager;

class TestableLivewireCanAssertReturnedValueTest extends TestCase
{
    /** @test */
    public function can_assert_return_value_of_called_method()
    {
        $component = Livewire::test(AssertReturnedValueOfMethodComponent::class);

        $component->call('someMethod')->assertReturned('foo');
    }

    /** @test */
    public function can_assert_invalid_return_value_of_called_method()
    {
        $component = Livewire::test(AssertReturnedValueOfMethodComponent::class);

        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);

        $component->call('someMethod')->assertReturned('bar');
    }

    /** @test */
    public function can_assert_return_value_of_called_method_using_closure()
    {
        $component = Livewire::test(AssertReturnedValueOfMethodComponent::class);

        $component->call('someMethod')->assertReturned(fn ($value) => $value === 'foo');
    }

    /** @test */
    public function can_assert_invalid_return_value_of_called_method_using_closure()
    {
        $component = Livewire::test(AssertReturnedValueOfMethodComponent::class);

        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);

        $component->call('someMethod')->assertReturned(fn ($value) => $value !== 'foo');
    }
}

class AssertReturnedValueOfMethodComponent extends Component
{
    public function someMethod()
    {
        return 'foo';
    }

    public function render()
    {
        return view('null-view');
    }
}
