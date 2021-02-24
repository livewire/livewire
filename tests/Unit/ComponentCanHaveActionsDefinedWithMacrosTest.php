<?php

namespace Tests\Unit;

use Livewire\Livewire;
use Livewire\Component;

class ComponentCanHaveActionsDefinedWithMacrosTest extends TestCase
{
    /** @test */
    public function a_livewire_component_can_use_a_macro_closure_without_parameters_as_a_callable_action()
    {
        ComponentWithMacroAction::macro('foo', function(){
            $this->bar = 'New Bar';
        });

        Livewire::test(ComponentWithMacroAction::class)
            ->call('foo')
            ->assertSet('bar', 'New Bar');
    }

    /** @test */
    public function a_livewire_component_can_use_a_macro_closure_with_parameters_as_a_callable_action()
    {
        ComponentWithMacroAction::macro('foo', function($param){
            $this->bar = $param;
        });

        Livewire::test(ComponentWithMacroAction::class)
            ->call('foo', 'New Bar')
            ->assertSet('bar', 'New Bar');
    }

    /** @test */
    public function a_livewire_component_can_use_a_mixin_class_to_set_callable_actions()
    {
        ComponentWithMacroAction::mixin(new TestComponentMixin());

        Livewire::test(ComponentWithMacroAction::class)
            ->call('foo', 'New Bar')
            ->assertSet('bar', 'New Bar');
    }
}

class ComponentWithMacroAction extends Component
{
    public $bar = 'Bar';

    public function render()
    {
        return view('null-view');
    }
}

class TestComponentMixin {

    public function foo()
    {
        return function ($param) {
            $this->bar = $param;
        };
    }
}

