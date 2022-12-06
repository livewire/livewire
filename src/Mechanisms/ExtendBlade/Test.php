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

    /** @test */
    public function public_property_is_accessible_in_view_via_this()
    {
        Livewire::test(PublicPropertiesInViewWithThisStub::class)
            ->assertSee('Caleb');
    }

    /** @test */
    public function public_properties_are_accessible_in_view_without_this()
    {
        Livewire::test(PublicPropertiesInViewWithoutThisStub::class)
            ->assertSee('Caleb');
    }

    /** @test */
    public function protected_property_is_accessible_in_view_via_this()
    {
        Livewire::test(ProtectedPropertiesInViewWithThisStub::class)
            ->assertSee('Caleb');
    }

    /** @test */
    public function protected_properties_are_not_accessible_in_view_without_this()
    {
        Livewire::test(ProtectedPropertiesInViewWithoutThisStub::class)
            ->assertDontSee('Caleb');
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
        return '<div>@this</div>';
    }
}

class ComponentForTestingNestedThisDirective extends Component
{
    public function render()
    {
        return "<div>@component('this-directive')@endcomponent</div>";
    }
}

class PublicPropertiesInViewWithThisStub extends Component
{
    public $name = 'Caleb';

    public function render()
    {
        return app('view')->make('show-name-with-this');
    }
}

class PublicPropertiesInViewWithoutThisStub extends Component
{
    public $name = 'Caleb';

    public function render()
    {
        return app('view')->make('show-name');
    }
}


class ProtectedPropertiesInViewWithThisStub extends Component
{
    protected $name = 'Caleb';

    public function render()
    {
        return app('view')->make('show-name-with-this');
    }
}

class ProtectedPropertiesInViewWithoutThisStub extends Component
{
    protected $name = 'Caleb';

    public function render()
    {
        return app('view')->make('show-name');
    }
}
