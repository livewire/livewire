<?php

namespace Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class DeleteCommandTest extends TestCase
{
    /** @test */
    public function component_is_removed_by_delete_command()
    {
        Artisan::call('make:livewire foo');

        $classPath = $this->livewireClassesPath('Foo.php');
        $viewPath = $this->livewireViewsPath('foo.blade.php');

        $this->assertTrue(File::exists($classPath));
        $this->assertTrue(File::exists($viewPath));

        Artisan::call('livewire:delete foo --force');

        $this->assertFalse(File::exists($classPath));
        $this->assertFalse(File::exists($viewPath));
    }

    /** @test */
    public function component_is_removed_by_rm_command()
    {
        Artisan::call('make:livewire foo');

        $classPath = $this->livewireClassesPath('Foo.php');
        $viewPath = $this->livewireViewsPath('foo.blade.php');

        $this->assertTrue(File::exists($classPath));
        $this->assertTrue(File::exists($viewPath));

        Artisan::call('livewire:rm foo --force');

        $this->assertFalse(File::exists($classPath));
        $this->assertFalse(File::exists($viewPath));
    }

    /** @test */
    public function component_is_removed_without_confirmation_if_forced()
    {
        Artisan::call('make:livewire foo');

        $classPath = $this->livewireClassesPath('Foo.php');
        $viewPath = $this->livewireViewsPath('foo.blade.php');

        $this->assertTrue(File::exists($classPath));
        $this->assertTrue(File::exists($viewPath));

        Artisan::call('livewire:delete foo --force');

        $this->assertFalse(File::exists($classPath));
        $this->assertFalse(File::exists($viewPath));
    }
}
