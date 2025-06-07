<?php

namespace Livewire\V4;

use Tests\TestCase;

class WireTagCompilerTest extends TestCase
{
    protected WireTagCompiler $compiler;

    public function setUp(): void
    {
        parent::setUp();
        $this->compiler = new WireTagCompiler();
    }

    public function test_compiles_self_closing_wire_components()
    {
        $template = '<wire:button />';

        $compiled = $this->compiler->__invoke($template);

        $this->assertEquals("@livewire('button', [])", $compiled);
    }

    public function test_compiles_wire_components_with_attributes()
    {
        $template = '<wire:button color="red" size="lg" />';

        $compiled = $this->compiler->__invoke($template);

        $this->assertEquals("@livewire('button', ['color' => 'red','size' => 'lg'])", $compiled);
    }

    public function test_compiles_wire_components_with_key()
    {
        $template = '<wire:button wire:key="my-button" />';

        $compiled = $this->compiler->__invoke($template);

        $this->assertEquals("@livewire('button', [], key('my-button'))", $compiled);
    }

    public function test_compiles_wire_styles_and_scripts()
    {
        $template = '<wire:styles /> <wire:scripts />';

        $compiled = $this->compiler->__invoke($template);

        $this->assertEquals('@livewireStyles @livewireScripts', $compiled);
    }

    public function test_compiles_dynamic_components()
    {
        $template = '<wire:dynamic-component component="$componentName" />';

        $compiled = $this->compiler->__invoke($template);

        $this->assertEquals("@livewire('\$componentName', [])", $compiled);
    }

    public function test_compiles_wire_component_with_default_slot()
    {
        $template = '<wire:modal>
            <h1>My Modal</h1>
            <p>Modal content here</p>
        </wire:modal>';

        $compiled = $this->compiler->__invoke($template);

        $expectedCompiled = "<?php \$__slots = []; ?>\n" .
                           "@wireSlot('default')\n" .
                           "<h1>My Modal</h1>\n            <p>Modal content here</p>\n" .
                           "@endWireSlot\n" .
                           "@livewire('modal', [], null, \$__slots ?? [])";

        $this->assertEquals($expectedCompiled, $compiled);
    }

    public function test_compiles_wire_component_with_named_slots()
    {
        $template = '<wire:modal>
            <wire:slot name="header">
                <h1>Modal Header</h1>
            </wire:slot>

            <p>Modal body content</p>
        </wire:modal>';

        $compiled = $this->compiler->__invoke($template);

        $expectedCompiled = "<?php \$__slots = []; ?>\n" .
                           "@wireSlot('header')\n" .
                           "\n                <h1>Modal Header</h1>\n            " .
                           "\n@endWireSlot\n" .
                           "@wireSlot('default')\n" .
                           "<p>Modal body content</p>\n" .
                           "@endWireSlot\n" .
                           "@livewire('modal', [], null, \$__slots ?? [])";

        $this->assertEquals($expectedCompiled, $compiled);
    }

    public function test_compiles_wire_component_with_slot_attributes()
    {
        $template = '<wire:modal>
            <wire:slot name="header" class="text-lg font-bold">
                <h1>Modal Header</h1>
            </wire:slot>
        </wire:modal>';

        $compiled = $this->compiler->__invoke($template);

        $this->assertStringContainsString("@wireSlot('header', ['class' => 'text-lg font-bold'])", $compiled);
    }

    public function test_compiles_wire_component_with_multiple_named_slots()
    {
        $template = '<wire:modal>
            <wire:slot name="header">Header Content</wire:slot>
            <wire:slot name="footer">Footer Content</wire:slot>
            <p>Default slot content</p>
        </wire:modal>';

        $compiled = $this->compiler->__invoke($template);

        $this->assertStringContainsString("@wireSlot('header')", $compiled);
        $this->assertStringContainsString("@wireSlot('footer')", $compiled);
        $this->assertStringContainsString("@wireSlot('default')", $compiled);
        $this->assertStringContainsString("@livewire('modal', [], null, \$__slots ?? [])", $compiled);
    }

    public function test_compiles_wire_component_with_attributes_and_slots()
    {
        $template = '<wire:modal size="lg" wire:key="main-modal">
            <wire:slot name="header">Header</wire:slot>
            <p>Body content</p>
        </wire:modal>';

        $compiled = $this->compiler->__invoke($template);

        $this->assertStringContainsString("@wireSlot('header')", $compiled);
        $this->assertStringContainsString("@wireSlot('default')", $compiled);
        $this->assertStringContainsString("@livewire('modal', ['size' => 'lg'], key('main-modal'), \$__slots ?? [])", $compiled);
    }

    public function test_handles_empty_wire_component_content()
    {
        $template = '<wire:modal></wire:modal>';

        $compiled = $this->compiler->__invoke($template);

        $this->assertEquals("@livewire('modal', [])", $compiled);
    }

    public function test_handles_wire_component_with_only_whitespace()
    {
        $template = '<wire:modal>

        </wire:modal>';

        $compiled = $this->compiler->__invoke($template);

        $this->assertEquals("@livewire('modal', [])", $compiled);
    }

    public function test_preserves_kebab_case_to_camel_case_conversion()
    {
        $template = '<wire:button data-color="red" some-prop="value" />';

        $compiled = $this->compiler->__invoke($template);

        $this->assertStringContainsString("dataColor", $compiled);
        $this->assertStringContainsString("someProp", $compiled);
    }

    public function test_handles_snake_case_attributes()
    {
        $template = '<wire:button snake_case="value" />';

        $compiled = $this->compiler->__invoke($template);

        $this->assertStringContainsString("snake_case", $compiled);
        $this->assertStringContainsString("snakeCase", $compiled);
    }

    public function test_handles_boolean_attributes()
    {
        $template = '<wire:button disabled required />';

        $compiled = $this->compiler->__invoke($template);

        $this->assertStringContainsString("'disabled' => true", $compiled);
        $this->assertStringContainsString("'required' => true", $compiled);
    }

    public function test_handles_numeric_attributes()
    {
        $template = '<wire:slider min="0" max="100" />';

        $compiled = $this->compiler->__invoke($template);

        $this->assertStringContainsString("'min' => '0'", $compiled);
        $this->assertStringContainsString("'max' => '100'", $compiled);
    }

    public function test_compiles_nested_components()
    {
        $template = '<wire:card><wire:button>Click me</wire:button></wire:card>';

        $compiled = $this->compiler->__invoke($template);

        $this->assertStringContainsString("@livewire('card'", $compiled);
        $this->assertStringContainsString("@livewire('button'", $compiled);
    }

    public function test_handles_complex_slot_content()
    {
        $template = '<wire:modal>
            <wire:slot name="header">
                <div class="flex justify-between">
                    <h1>{{ $title }}</h1>
                    <button wire:click="close">×</button>
                </div>
            </wire:slot>

            <form wire:submit.prevent="save">
                <input wire:model="name" />
                <button type="submit">Save</button>
            </form>
        </wire:modal>';

        $compiled = $this->compiler->__invoke($template);

        $this->assertStringContainsString("wire:click", $compiled);
        $this->assertStringContainsString("wire:submit.prevent", $compiled);
        $this->assertStringContainsString("wire:model", $compiled);
        $this->assertStringContainsString("{{ \$title }}", $compiled);
    }

    public function test_handles_slot_without_name_attribute()
    {
        $template = '<wire:modal>
            <wire:slot>
                Default slot via explicit wire:slot tag
            </wire:slot>
        </wire:modal>';

        $compiled = $this->compiler->__invoke($template);

        $this->assertStringContainsString("@wireSlot('default')", $compiled);
    }
}