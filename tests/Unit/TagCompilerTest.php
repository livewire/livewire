<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Exceptions\ComponentAttributeMissingOnDynamicComponentException;
use Livewire\Livewire;
use Livewire\LivewireTagCompiler;

class TagCompilerTest extends TestCase
{
    /**
     * @var \Illuminate\View\Compilers\BladeCompiler
     */
    protected $compiler;

    public function setUp(): void
    {
        $this->compiler = new LivewireTagCompiler();
        parent::setUp();
    }

    /** @test */
    public function it_compiles_livewire_self_closing_tags()
    {
        $alertComponent = '<livewire:alert />';
        $result = $this->compiler->compile($alertComponent);

        $this->assertEquals("@livewire('alert', [])", $result);
    }

    /** @test */
    public function it_compiles_livewire_styles_tag()
    {
        $alertComponent = '<livewire:styles />';
        $result = $this->compiler->compile($alertComponent);

        $this->assertEquals('@livewireStyles', $result);
    }

    /** @test */
    public function it_compiles_livewire_scripts_tag()
    {
        $alertComponent = '<livewire:scripts />';
        $result = $this->compiler->compile($alertComponent);

        $this->assertEquals('@livewireScripts', $result);
    }

    /** @test */
    public function it_compiles_livewire_self_closing_tags_with_attributes()
    {
        $alertComponent = '<livewire:alert type="danger" />';
        $result = $this->compiler->compile($alertComponent);

        $this->assertEquals("@livewire('alert', ['type' => 'danger'])", $result);
    }

    /** @test */
    public function it_converts_kebab_attribute_names_to_camel_case()
    {
        $alertComponent = '<livewire:alert alert-type="success" />';
        $result = $this->compiler->compile($alertComponent);

        $this->assertEquals("@livewire('alert', ['alertType' => 'success'])", $result);
    }

    /** @test */
    public function it_converts_snake_cased_attribute_names_to_camel_case()
    {
        $alertComponent = '<livewire:alert alert_type="success" />';
        $result = $this->compiler->compile($alertComponent);

        $this->assertEquals("@livewire('alert', ['alertType' => 'success','alert_type' => 'success'])", $result);
    }

    /** @test */
    public function it_compiles_livewire_dynamic_component_self_closing_tags()
    {
        $alertComponent = '<livewire:dynamic-component component="alert" type="warning" />';
        $result = $this->compiler->compile($alertComponent);

        $this->assertEquals("@livewire('alert', ['type' => 'warning'])", $result);
    }

    /** @test */
    public function it_compiles_livewire_dynamic_component_self_closing_tags_with_component_attribute_as_expression()
    {
        $alertComponent = '<livewire:dynamic-component :component="\'alert\'" type="warning" />';
        $result = $this->compiler->compile($alertComponent);

        $this->assertEquals("@livewire('alert', ['type' => 'warning'])", $result);
    }

    /** @test */
    public function it_throws_exception_if_livewire_dynamic_component_is_missing_component_name_attribute()
    {
        $this->expectException(ComponentAttributeMissingOnDynamicComponentException::class);

        $alertComponent = '<livewire:dynamic-component />';
        $this->compiler->compile($alertComponent);
    }

    /** @test */
    public function it_compiles_livewire_dynamic_component_self_closing_tags_using_is_syntax()
    {
        $alertComponent = '<livewire:is component="alert" type="warning" />';
        $result = $this->compiler->compile($alertComponent);

        $this->assertEquals("@livewire('alert', ['type' => 'warning'])", $result);
    }

    /** @test */
    public function it_uses_existing_dynamic_component_if_one_exists()
    {
        Livewire::component('dynamic-component', DynamicComponent::class);

        $alertComponent = '<livewire:dynamic-component />';
        $result = $this->compiler->compile($alertComponent);

        $this->assertEquals("@livewire('dynamic-component', [])", $result);
    }
}

class DynamicComponent extends Component {

}
