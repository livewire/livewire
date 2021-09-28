<?php

namespace Tests\Unit;

use Livewire\Livewire;
use Livewire\Component;

class ComponentReadOnlyPropertyTest extends TestCase
{
    /** @test */
    public function only_readonly_properties_on_a_livewire_component_cannot_be_set_by_the_client()
    {
        Livewire::test(ComponentWithReadOnlyProperty::class)
            ->set('foo', 'Baz')
            ->set('bar', 'Foo')
            ->assertSet('foo', 'Bar')
            ->assertSet('bar', 'Foo');
    }
}

class ComponentWithReadOnlyProperty extends Component
{
    protected $readOnly = ['foo'];

    public $foo = 'Bar';

    public $bar = 'Baz';

    public function render()
    {
        return view('null-view');
    }
}
