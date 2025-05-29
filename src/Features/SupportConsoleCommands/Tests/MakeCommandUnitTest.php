<?php

namespace Livewire\Features\SupportConsoleCommands\Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class MakeCommandUnitTest extends \Tests\TestCase
{
    public function test_component_is_created_by_make_command()
    {
        Artisan::call('make:livewire', ['name' => 'foo']);

        $this->assertTrue(File::exists($this->livewireClassesPath('Foo.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo.blade.php')));
    }

    public function test_component_is_created_without_view_by_make_command_with_inline_option()
    {
        Artisan::call('make:livewire', ['name' => 'foo', '--inline' => true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('Foo.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('foo.blade.php')));
    }

    public function test_component_test_is_created_by_make_command_with_test_option()
    {
        Artisan::call('make:livewire', ['name' => 'foo', '--test' => true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('Foo.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('FooTest.php')));
    }

    public function test_component_pest_test_is_created_by_make_command_with_pest_option()
    {
        Artisan::call('make:livewire', ['name' => 'foo', '--test' => true, '--pest' => true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('Foo.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('FooTest.php')));

        $file = File::get($this->livewireTestsPath('FooTest.php'));

        $this->assertStringContainsString('use App\Livewire\Foo;', $file);
        $this->assertStringContainsString('use Livewire\Livewire;', $file);
        $this->assertStringContainsString('it(\'renders successfully\', function () {', $file);
    }

    public function test_component_is_created_by_livewire_make_command()
    {
        Artisan::call('livewire:make', ['name' => 'foo', '--test' => true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('Foo.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('FooTest.php')));
    }

    public function test_component_is_created_by_touch_command()
    {
        Artisan::call('livewire:touch', ['name' => 'foo', '--test' => true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('Foo.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('FooTest.php')));
    }

    public function test_dot_nested_component_is_created_by_make_command()
    {
        Artisan::call('make:livewire', ['name' => 'foo.bar', '--test' => true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('Foo/Bar.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo/bar.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('Foo/BarTest.php')));
    }

    public function test_forward_slash_nested_component_is_created_by_make_command()
    {
        Artisan::call('make:livewire', ['name' => 'foo/bar', '--test' => true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('Foo/Bar.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo/bar.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('Foo/BarTest.php')));
    }

    public function test_multiword_component_is_created_by_make_command()
    {
        Artisan::call('make:livewire', ['name' => 'foo-bar', '--test' => true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('FooBar.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo-bar.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('FooBarTest.php')));
    }

    public function test_pascal_case_component_is_automatically_converted_by_make_command()
    {
        Artisan::call('make:livewire', ['name' => 'FooBar.FooBar', '--test' => true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('FooBar/FooBar.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo-bar/foo-bar.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('FooBar/FooBarTest.php')));
    }

    public function test_pascal_case_component_with_double_backslashes_is_automatically_converted_by_make_command()
    {
        Artisan::call('make:livewire', ['name' => 'FooBar\\FooBar', '--test' => true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('FooBar/FooBar.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo-bar/foo-bar.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('FooBar/FooBarTest.php')));
    }

    public function test_snake_case_component_is_automatically_converted_by_make_command()
    {
        Artisan::call('make:livewire', ['name' => 'text_replace', '--test' => true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('TextReplace.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('text-replace.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('TextReplaceTest.php')));
    }

    public function test_snake_case_component_is_automatically_converted_by_make_command_on_nested_component()
    {
        Artisan::call('make:livewire', ['name' => 'TextManager.text_replace', '--test' => true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('TextManager/TextReplace.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('text-manager/text-replace.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('TextManager/TextReplaceTest.php')));
    }

    public function test_new_component_class_view_name_reference_matches_configured_view_path()
    {
        // We can't use Artisan::call here because we need to be able to set config vars.
        $this->app['config']->set('livewire.view_path', resource_path('views/not-livewire'));
        $this->app[Kernel::class]->call('make:livewire', ['name' => 'foo']);

        $this->assertStringContainsString(
            "view('not-livewire.foo')",
            File::get($this->livewireClassesPath('Foo.php'))
        );
        $this->assertTrue(File::exists(resource_path('views/not-livewire/foo.blade.php')));
    }

    public function test_a_component_is_not_created_with_a_reserved_class_name()
    {
        Artisan::call('make:livewire', ['name' => 'component']);

        $this->assertFalse(File::exists($this->livewireClassesPath('Component.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('component.blade.php')));

        Artisan::call('make:livewire', ['name' => 'list']);

        $this->assertFalse(File::exists($this->livewireClassesPath('List.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('list.blade.php')));
    }
    public function test_a_component_is_not_created_with_a_invalid_class_name()
    {
        Artisan::call('make:livewire', ['name' => '1Class']);

        $this->assertFalse(File::exists($this->livewireClassesPath('1Class.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('1Class.blade.php')));

        Artisan::call('make:livewire', ['name' => '!Class']);

        $this->assertFalse(File::exists($this->livewireClassesPath('!Class.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('!Class.blade.php')));

    }
}
