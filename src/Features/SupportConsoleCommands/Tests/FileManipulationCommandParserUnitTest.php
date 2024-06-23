<?php

namespace Livewire\Features\SupportConsoleCommands\Tests;

use Livewire\Features\SupportConsoleCommands\Commands\ComponentParser;
use PHPUnit\Framework\Attributes\DataProvider;

class FileManipulationCommandParserUnitTest extends \Tests\TestCase
{
    #[DataProvider('classPathProvider')]
    public function test_something($input, $component, $namespace, $classPath, $viewName, $viewPath)
    {
        $parser = new ComponentParser(
            'App\Livewire',
            resource_path('views/livewire'),
            $input
        );

        $this->assertEquals($component, $parser->component());
        $this->assertEquals($namespace, $parser->classNamespace());
        $this->assertEquals($this->normalizeDirectories(app_path($classPath)), $this->normalizeDirectories($parser->classPath()));
        $this->assertEquals($viewName, $parser->viewName());
        $this->assertEquals($this->normalizeDirectories(resource_path('views/'.$viewPath)), $this->normalizeDirectories($parser->viewPath()));
    }

    public static function classPathProvider()
    {
        return [
            [
                'foo',
                'foo',
                'App\Livewire',
                'Livewire/Foo.php',
                'livewire.foo',
                'livewire/foo.blade.php',
            ],
            [
                'foo.bar',
                'bar',
                'App\Livewire\Foo',
                'Livewire/Foo/Bar.php',
                'livewire.foo.bar',
                'livewire/foo/bar.blade.php',
            ],
            [
                'foo.bar',
                'bar',
                'App\Livewire\Foo',
                'Livewire/Foo/Bar.php',
                'livewire.foo.bar',
                'livewire/foo/bar.blade.php',
            ],
            [
                'foo.bar',
                'bar',
                'App\Livewire\Foo',
                'Livewire/Foo/Bar.php',
                'livewire.foo.bar',
                'livewire/foo/bar.blade.php',
            ],
            [
                'foo-bar',
                'foo-bar',
                'App\Livewire',
                'Livewire/FooBar.php',
                'livewire.foo-bar',
                'livewire/foo-bar.blade.php',
            ],
            [
                'foo-bar.foo-bar',
                'foo-bar',
                'App\Livewire\FooBar',
                'Livewire/FooBar/FooBar.php',
                'livewire.foo-bar.foo-bar',
                'livewire/foo-bar/foo-bar.blade.php',
            ],
            [
                'FooBar',
                'foo-bar',
                'App\Livewire',
                'Livewire/FooBar.php',
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
