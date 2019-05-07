<?php

namespace Tests;

use Livewire\Commands\LivewireMakeCommandParser;

class LivewireMakeCommandParserTest extends TestCase
{
    /**
     * @test
     * @dataProvider classPathProvider
     */
    function something($input, $component, $namespace, $classPath, $viewName, $viewPath)
    {
        $parser = new LivewireMakeCommandParser(
            '', // App directory path.
            '', // Views directory path.
            $input
        );

        $this->assertEquals($component, $parser->component());
        $this->assertEquals($namespace, $parser->classNamespace());
        $this->assertEquals($classPath, $parser->classPath());
        $this->assertEquals($viewName, $parser->viewName());
        $this->assertEquals($viewPath, $parser->viewPath());
    }

    function classPathProvider()
    {
        return [
            [
                'foo',
                'foo',
                'App\Http\Livewire',
                '/Http/Livewire/Foo.php',
                'livewire.foo',
                '/livewire/foo.blade.php',
            ],
            [
                'foo.bar',
                'bar',
                'App\Http\Livewire\Foo',
                '/Http/Livewire/Foo/Bar.php',
                'livewire.foo.bar',
                '/livewire/foo/bar.blade.php',
            ],
            [
                'foo.bar',
                'bar',
                'App\Http\Livewire\Foo',
                '/Http/Livewire/Foo/Bar.php',
                'livewire.foo.bar',
                '/livewire/foo/bar.blade.php',
            ],
            [
                'foo.bar',
                'bar',
                'App\Http\Livewire\Foo',
                '/Http/Livewire/Foo/Bar.php',
                'livewire.foo.bar',
                '/livewire/foo/bar.blade.php',
            ],
            [
                'foo-bar',
                'foo-bar',
                'App\Http\Livewire',
                '/Http/Livewire/FooBar.php',
                'livewire.foo-bar',
                '/livewire/foo-bar.blade.php',
            ],
            [
                'foo-bar.foo-bar',
                'foo-bar',
                'App\Http\Livewire\FooBar',
                '/Http/Livewire/FooBar/FooBar.php',
                'livewire.foo-bar.foo-bar',
                '/livewire/foo-bar/foo-bar.blade.php',
            ],
        ];
    }


}
