<?php

namespace Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class DestroyCommandTest extends TestCase
{
    /** @test */
    function component_is_removed_by_destory_command()
    {
        Artisan::call('make:livewire foo');

        $classPath = $this->livewireClassesPath('Foo.php');
        $viewPath = $this->livewireViewsPath('foo.blade.php');

        $this->assertTrue(File::exists($classPath));
        $this->assertTrue(File::exists($viewPath));

        $this->artisan('livewire:destroy foo')->expectsQuestion(
            "Are you sure you want to delete the following files?\n\n{$classPath}\n{$viewPath}\n",
            true
        );

        $this->assertFalse(File::exists($classPath));
        $this->assertFalse(File::exists($viewPath));
    }

    /** @test */
    function component_is_not_removed_when_confirm_answer_is_no()
    {
        Artisan::call('make:livewire foo');

        $classPath = $this->livewireClassesPath('Foo.php');
        $viewPath = $this->livewireViewsPath('foo.blade.php');

        $this->assertTrue(File::exists($classPath));
        $this->assertTrue(File::exists($viewPath));

        $this->artisan('livewire:destroy foo')->expectsQuestion(
            "Are you sure you want to delete the following files?\n\n{$classPath}\n{$viewPath}\n",
            false
        );

        $this->assertTrue(File::exists($classPath));
        $this->assertTrue(File::exists($viewPath));
    }

    /** @test */
    function component_is_removed_without_confirmation_if_forced()
    {
        Artisan::call('make:livewire foo');

        $classPath = $this->livewireClassesPath('Foo.php');
        $viewPath = $this->livewireViewsPath('foo.blade.php');

        $this->assertTrue(File::exists($classPath));
        $this->assertTrue(File::exists($viewPath));

        Artisan::call('livewire:destroy foo --force');

        $this->assertFalse(File::exists($classPath));
        $this->assertFalse(File::exists($viewPath));
    }
}
