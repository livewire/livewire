<?php

namespace Livewire\Mechanisms\ExtendBlade;

use Illuminate\Support\Facades\Blade;
use Livewire\Component;
use Livewire\Livewire;

class Test extends \Tests\TestCase
{
    /** @test */
    public function livewire_only_directives_apply_to_livewire_components_and_not_normal_blade()
    {
        Livewire::directive('foo', function ($expression) {
            return 'bar';
        });

        $output = Blade::render('
            <div>@foo</div>

            @livewire(\Livewire\Mechanisms\ExtendBlade\ExtendBladeTestComponent::class)

            <div>@foo</div>
        ');

        $this->assertCount(3, explode('@foo', $output));
    }

    /** @test */
    public function livewire_only_precompilers_apply_to_livewire_components_and_not_normal_blade()
    {
        Livewire::precompiler('/@foo/sm', function ($matches) {
            return 'bar';
        });

        $output = Blade::render('
            <div>@foo</div>

            @livewire(\Livewire\Mechanisms\ExtendBlade\ExtendBladeTestComponent::class)

            <div>@foo</div>
        ');

        $this->assertCount(3, explode('@foo', $output));
    }

    /** @test */
    public function this_keyword_will_reference_the_livewire_component_class()
    {
        Livewire::test(ComponentForTestingThisKeyword::class)
            ->assertSee(ComponentForTestingThisKeyword::class);
    }

    /** @test */
    public function this_directive_returns_javascript_component_object_string()
    {
        Livewire::test(ComponentForTestingDirectives::class)
            ->assertDontSee('@this')
            ->assertSee('window.livewire.find(');
    }

    /** @test */
    public function this_directive_can_be_used_in_nested_blade_component()
    {
        Livewire::test(ComponentForTestingNestedThisDirective::class)
            ->assertDontSee('@this')
            ->assertSee('window.livewire.find(');
    }
}

class ExtendBladeTestComponent extends Component
{
    public function render()
    {
        return '<div>@foo</div>';
    }
}

class ComponentForTestingThisKeyword extends Component
{
    public function render()
    {
        return '<div>{{ get_class($this) }}</div>';
    }
}

class ComponentForTestingDirectives extends Component
{
    public function render()
    {
        return view('this-directive');
    }
}

class ComponentForTestingNestedThisDirective extends Component
{
    public function render()
    {
        return view('nested-this-directive');
    }
}
