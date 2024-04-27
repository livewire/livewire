<?php

namespace Livewire\Features\SupportConsoleCommands\Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class DeleteCommandUnitTest extends \Tests\TestCase
{
    public function test_component_is_removed_by_delete_command()
    {
        Artisan::call('make:livewire', ['name' => 'foo']);

        $classPath = $this->livewireClassesPath('Foo.php');
        $viewPath = $this->livewireViewsPath('foo.blade.php');

        $this->assertTrue(File::exists($classPath));
        $this->assertTrue(File::exists($viewPath));

        Artisan::call('livewire:delete', ['name' => 'foo', '--force' => true]);

        $this->assertFalse(File::exists($classPath));
        $this->assertFalse(File::exists($viewPath));
    }

    public function test_inline_component_is_removed_by_delete_command()
    {
        Artisan::call('make:livewire', ['name' => 'foo', '--inline' => true]);

        $classPath = $this->livewireClassesPath('Foo.php');
        $viewPath = $this->livewireViewsPath('foo.blade.php');

        $this->assertTrue(File::exists($classPath));
        $this->assertFalse(File::exists($viewPath));

        Artisan::call('livewire:delete', ['name' => 'foo', '--force' => true, '--inline' => true]);

        $this->assertFalse(File::exists($classPath));
        $this->assertFalse(File::exists($viewPath));
    }

    public function test_component_with_test_is_removed_by_delete_command()
    {
        Artisan::call('make:livewire', ['name' => 'foo','--test'=>true]);

        $classPath = $this->livewireClassesPath('Foo.php');
        $viewPath = $this->livewireViewsPath('foo.blade.php');
        $testPath = $this->livewireTestsPath('FooTest.php');

        $this->assertTrue(File::exists($classPath));
        $this->assertTrue(File::exists($viewPath));
        $this->assertTrue(File::exists($testPath));

        Artisan::call('livewire:delete', ['name' => 'foo', '--force' => true,'--test'=>true]);

        $this->assertFalse(File::exists($classPath));
        $this->assertFalse(File::exists($viewPath));
        $this->assertFalse(File::exists($testPath));
    }
    public function test_component_is_removed_by_rm_command()
    {
        Artisan::call('make:livewire', ['name' => 'foo','--test'=>true]);

        $classPath = $this->livewireClassesPath('Foo.php');
        $viewPath = $this->livewireViewsPath('foo.blade.php');
        $testPath = $this->livewireTestsPath('FooTest.php');

        $this->assertTrue(File::exists($classPath));
        $this->assertTrue(File::exists($viewPath));
        $this->assertTrue(File::exists($testPath));

        Artisan::call('livewire:rm', ['name' => 'foo', '--force' => true,'--test'=>true]);

        $this->assertFalse(File::exists($classPath));
        $this->assertFalse(File::exists($viewPath));
        $this->assertFalse(File::exists($testPath));
    }

    public function test_component_is_removed_without_confirmation_if_forced()
    {
        Artisan::call('make:livewire', ['name' => 'foo','--test'=>true]);

        $classPath = $this->livewireClassesPath('Foo.php');
        $viewPath = $this->livewireViewsPath('foo.blade.php');
        $testPath = $this->livewireTestsPath('FooTest.php');

        $this->assertTrue(File::exists($classPath));
        $this->assertTrue(File::exists($viewPath));
        $this->assertTrue(File::exists($testPath));

        Artisan::call('livewire:delete', ['name' => 'foo', '--force' => true,'--test'=>true]);

        $this->assertFalse(File::exists($classPath));
        $this->assertFalse(File::exists($viewPath));
        $this->assertFalse(File::exists($testPath));
    }
}
