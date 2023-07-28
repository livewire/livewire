<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers\Tests;

use Livewire\Component;
use Livewire\Livewire;
use stdClass;
use Tests\TestCase;

class DataBindingErrorUnitTest extends TestCase
{
    /** @test */
    public function update_nested_component_data_inside_non_initialized_array(): void
    {
        $this->expectExceptionMessage('Property value of "null" for property "foo.0" not supported');

        $component = Livewire::test(DataBindingErrorStub::class);
        $component->set('foo.0', new stdClass());
    }
}

class DataBindingErrorStub extends Component
{
    public $foo;

    public function render()
    {
        return app('view')->make('null-view');
    }
}
