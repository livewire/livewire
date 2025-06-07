<?php

namespace Livewire\v4\Compiler;

class ParsedComponentUnitTest extends \Tests\TestCase
{
    public function test_can_create_inline_component()
    {
        $frontmatter = 'new class extends Livewire\Component { public $count = 0; }';
        $viewContent = '<div>{{ $count }}</div>';

        $parsed = new ParsedComponent($frontmatter, $viewContent);

        $this->assertEquals($frontmatter, $parsed->frontmatter);
        $this->assertEquals($viewContent, $parsed->viewContent);
        $this->assertFalse($parsed->isExternal);
        $this->assertNull($parsed->externalClass);
    }

    public function test_can_create_external_component()
    {
        $viewContent = '<div>{{ $count }}</div>';
        $externalClass = 'App\\Livewire\\Counter';

        $parsed = new ParsedComponent('', $viewContent, true, $externalClass);

        $this->assertEquals('', $parsed->frontmatter);
        $this->assertEquals($viewContent, $parsed->viewContent);
        $this->assertTrue($parsed->isExternal);
        $this->assertEquals($externalClass, $parsed->externalClass);
    }

    public function test_has_inline_class_returns_true_for_non_external_with_frontmatter()
    {
        $parsed = new ParsedComponent('new class extends Component {}', '<div></div>');

        $this->assertTrue($parsed->hasInlineClass());
    }

    public function test_has_inline_class_returns_false_for_external_component()
    {
        $parsed = new ParsedComponent('', '<div></div>', true, 'App\\Component');

        $this->assertFalse($parsed->hasInlineClass());
    }

    public function test_has_inline_class_returns_false_for_empty_frontmatter()
    {
        $parsed = new ParsedComponent('', '<div></div>');

        $this->assertFalse($parsed->hasInlineClass());
    }

    public function test_has_external_class_returns_true_for_external_with_class()
    {
        $parsed = new ParsedComponent('', '<div></div>', true, 'App\\Component');

        $this->assertTrue($parsed->hasExternalClass());
    }

    public function test_has_external_class_returns_false_for_inline_component()
    {
        $parsed = new ParsedComponent('new class {}', '<div></div>');

        $this->assertFalse($parsed->hasExternalClass());
    }

    public function test_has_external_class_returns_false_for_external_without_class()
    {
        $parsed = new ParsedComponent('', '<div></div>', true, null);

        $this->assertFalse($parsed->hasExternalClass());
    }

    public function test_get_class_definition_returns_frontmatter_for_inline()
    {
        $frontmatter = 'new class extends Component { public $count = 0; }';
        $parsed = new ParsedComponent($frontmatter, '<div></div>');

        $this->assertEquals($frontmatter, $parsed->getClassDefinition());
    }

    public function test_get_class_definition_returns_external_class_for_external()
    {
        $externalClass = 'App\\Livewire\\Counter';
        $parsed = new ParsedComponent('', '<div></div>', true, $externalClass);

        $this->assertEquals($externalClass, $parsed->getClassDefinition());
    }
}