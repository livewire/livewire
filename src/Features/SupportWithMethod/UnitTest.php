<?php

namespace Livewire\Features\SupportWithMethod;

use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

class UnitTest extends TestCase
{
    public function test_with_method_provides_data_to_view()
    {
        $component = Livewire::test(ComponentWithWith::class);

        $component->assertSee('bar');
        $component->assertSee('Hello World');
    }

    public function test_component_without_with_method_works_normally()
    {
        $component = Livewire::test(ComponentWithoutWith::class);

        $component->assertSee('Normal component');
    }

    public function test_with_method_returning_non_array_is_ignored()
    {
        $component = Livewire::test(ComponentWithInvalidWith::class);

        $component->assertSee('Invalid component');
        $component->assertDontSee('invalid data');
    }

    public function test_with_method_data_overrides_component_properties()
    {
        $component = Livewire::test(ComponentWithOverridingWith::class);

        // The 'foo' from the with() method should override the component property
        $component->assertSee('from with method');
        $component->assertDontSee('from property');
    }
}

class ComponentWithWith extends Component
{
    public function with()
    {
        return [
            'foo' => 'bar',
            'message' => 'Hello World',
        ];
    }

    public function render()
    {
        return <<<'HTML'
        <div>
            <span>{{ $foo }}</span>
            <span>{{ $message }}</span>
        </div>
        HTML;
    }
}

class ComponentWithoutWith extends Component
{
    public function render()
    {
        return '<div>Normal component</div>';
    }
}

class ComponentWithInvalidWith extends Component
{
    public function with()
    {
        return 'invalid data'; // Not an array
    }

    public function render()
    {
        return '<div>Invalid component</div>';
    }
}

class ComponentWithOverridingWith extends Component
{
    public $foo = 'from property';

    public function with()
    {
        return [
            'foo' => 'from with method',
        ];
    }

    public function render()
    {
        return '<div>{{ $foo }}</div>';
    }
}