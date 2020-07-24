<?php

namespace Tests\Unit;

use Livewire\Commands\ComponentParser;

class FileManipulationCommandParserTest extends TestCase
{
    /**
     * @test
     * @dataProvider classPathProvider
     */
    public function something($input, $component, $namespace, $classPath, $viewName, $viewPath)
    {
        $parser = new ComponentParser(
            'App\Http\Livewire',
            resource_path('views/livewire'),
            $input
        );

        $this->assertEquals($component, $parser->component());
        $this->assertEquals($namespace, $parser->classNamespace());
        $this->assertEquals($this->normalizeDirectories(app_path($classPath)), $this->normalizeDirectories($parser->classPath()));
        $this->assertEquals($viewName, $parser->viewName());
        $this->assertEquals($this->normalizeDirectories(resource_path('views/'.$viewPath)), $this->normalizeDirectories($parser->viewPath()));
    }

    public function classPathProvider()
    {
        return [
            [
                'foo',
                'foo',
                'App\Http\Livewire',
                'Http/Livewire/Foo.php',
                'livewire.foo',
                'livewire/foo.blade.php',
            ],
            [
                'foo.bar',
                'bar',
                'App\Http\Livewire\Foo',
                'Http/Livewire/Foo/Bar.php',
                'livewire.foo.bar',
                'livewire/foo/bar.blade.php',
            ],
            [
                'foo.bar',
                'bar',
                'App\Http\Livewire\Foo',
                'Http/Livewire/Foo/Bar.php',
                'livewire.foo.bar',
                'livewire/foo/bar.blade.php',
            ],
            [
                'foo.bar',
                'bar',
                'App\Http\Livewire\Foo',
                'Http/Livewire/Foo/Bar.php',
                'livewire.foo.bar',
                'livewire/foo/bar.blade.php',
            ],
            [
                'foo-bar',
                'foo-bar',
                'App\Http\Livewire',
                'Http/Livewire/FooBar.php',
                'livewire.foo-bar',
                'livewire/foo-bar.blade.php',
            ],
            [
                'foo-bar.foo-bar',
                'foo-bar',
                'App\Http\Livewire\FooBar',
                'Http/Livewire/FooBar/FooBar.php',
                'livewire.foo-bar.foo-bar',
                'livewire/foo-bar/foo-bar.blade.php',
            ],
            [
                'FooBar',
                'foo-bar',
                'App\Http\Livewire',
                'Http/Livewire/FooBar.php',
                'livewire.foo-bar',
                'livewire/foo-bar.blade.php',
            ],
        ];
    }

    private function normalizeDirectories($subject)
    {
        return str_replace(DIRECTORY_SEPARATOR, '/', $subject);
    }
}
