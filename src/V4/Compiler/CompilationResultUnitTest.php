<?php

namespace Livewire\V4\Compiler;

class CompilationResultUnitTest extends \Tests\TestCase
{
    public function test_can_create_inline_compilation_result()
    {
        $result = new CompilationResult(
            className: 'Livewire\\Compiled\\Counter_abc123',
            classPath: '/path/to/Counter_abc123.php',
            viewName: 'livewire-compiled::counter_abc123',
            viewPath: '/path/to/counter_abc123.blade.php',
            isExternal: false,
            externalClass: null,
            hash: 'abc123'
        );

        $this->assertEquals('Livewire\\Compiled\\Counter_abc123', $result->className);
        $this->assertEquals('/path/to/Counter_abc123.php', $result->classPath);
        $this->assertEquals('livewire-compiled::counter_abc123', $result->viewName);
        $this->assertEquals('/path/to/counter_abc123.blade.php', $result->viewPath);
        $this->assertFalse($result->isExternal);
        $this->assertNull($result->externalClass);
        $this->assertEquals('abc123', $result->hash);
    }

    public function test_can_create_external_compilation_result()
    {
        $result = new CompilationResult(
            className: 'App\\Livewire\\Counter',
            classPath: '/path/to/Counter.php',
            viewName: 'livewire-compiled::counter_abc123',
            viewPath: '/path/to/counter_abc123.blade.php',
            isExternal: true,
            externalClass: 'App\\Livewire\\Counter',
            hash: 'abc123'
        );

        $this->assertTrue($result->isExternal);
        $this->assertEquals('App\\Livewire\\Counter', $result->externalClass);
    }

    public function test_should_generate_class_returns_true_for_inline_component()
    {
        $result = new CompilationResult(
            className: 'Livewire\\Compiled\\Counter_abc123',
            classPath: '/path/to/Counter_abc123.php',
            viewName: 'livewire-compiled::counter_abc123',
            viewPath: '/path/to/counter_abc123.blade.php',
            isExternal: false
        );

        $this->assertTrue($result->shouldGenerateClass());
    }

    public function test_should_generate_class_returns_false_for_external_component()
    {
        $result = new CompilationResult(
            className: 'App\\Livewire\\Counter',
            classPath: '/path/to/Counter.php',
            viewName: 'livewire-compiled::counter_abc123',
            viewPath: '/path/to/counter_abc123.blade.php',
            isExternal: true,
            externalClass: 'App\\Livewire\\Counter'
        );

        $this->assertFalse($result->shouldGenerateClass());
    }

    public function test_get_class_namespace_returns_correct_namespace()
    {
        $result = new CompilationResult(
            className: 'Livewire\\Compiled\\Counter_abc123',
            classPath: '/path/to/Counter_abc123.php',
            viewName: 'livewire-compiled::counter_abc123',
            viewPath: '/path/to/counter_abc123.blade.php'
        );

        $this->assertEquals('Livewire\\Compiled', $result->getClassNamespace());
    }

    public function test_get_short_class_name_returns_class_name_without_namespace()
    {
        $result = new CompilationResult(
            className: 'Livewire\\Compiled\\Counter_abc123',
            classPath: '/path/to/Counter_abc123.php',
            viewName: 'livewire-compiled::counter_abc123',
            viewPath: '/path/to/counter_abc123.blade.php'
        );

        $this->assertEquals('Counter_abc123', $result->getShortClassName());
    }

    public function test_handles_deeply_nested_namespace()
    {
        $result = new CompilationResult(
            className: 'App\\Livewire\\Components\\Forms\\Input_def456',
            classPath: '/path/to/Input_def456.php',
            viewName: 'livewire-compiled::input_def456',
            viewPath: '/path/to/input_def456.blade.php'
        );

        $this->assertEquals('App\\Livewire\\Components\\Forms', $result->getClassNamespace());
        $this->assertEquals('Input_def456', $result->getShortClassName());
    }

    public function test_handles_class_without_namespace()
    {
        $result = new CompilationResult(
            className: 'Counter',
            classPath: '/path/to/Counter.php',
            viewName: 'livewire-compiled::counter',
            viewPath: '/path/to/counter.blade.php'
        );

        $this->assertEquals('', $result->getClassNamespace());
        $this->assertEquals('Counter', $result->getShortClassName());
    }
}