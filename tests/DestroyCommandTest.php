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
        $this->assertTrue(File::exists($this->livewireClassesPath('Foo.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo.blade.php')));

        $this->artisan('livewire:destroy foo')->expectsQuestion(
            "Are you sure you want to delete the following files?\n\n/Users/calebporzio/Documents/Code/sites/livewire/vendor/orchestra/testbench-core/src/Concerns/../../laravel/app/Http/Livewire/Foo.php\n/Users/calebporzio/Documents/Code/sites/livewire/tests/views/livewire/foo.blade.php\n",
            true
        );

        $this->assertFalse(File::exists($this->livewireClassesPath('Foo.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('foo.blade.php')));
    }

    /** @test */
    function component_is_not_removed_when_confirm_answer_is_no()
    {
        Artisan::call('make:livewire foo');
        $this->assertTrue(File::exists($this->livewireClassesPath('Foo.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo.blade.php')));

        $this->artisan('livewire:destroy foo')->expectsQuestion(
            "Are you sure you want to delete the following files?\n\n/Users/calebporzio/Documents/Code/sites/livewire/vendor/orchestra/testbench-core/src/Concerns/../../laravel/app/Http/Livewire/Foo.php\n/Users/calebporzio/Documents/Code/sites/livewire/tests/views/livewire/foo.blade.php\n",
            false
        );

        $this->assertTrue(File::exists($this->livewireClassesPath('Foo.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo.blade.php')));
    }

    /** @test */
    function component_is_removed_without_confirmation_if_forced()
    {
        Artisan::call('make:livewire foo');
        $this->assertTrue(File::exists($this->livewireClassesPath('Foo.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo.blade.php')));

        Artisan::call('livewire:destroy foo --force');

        $this->assertFalse(File::exists($this->livewireClassesPath('Foo.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('foo.blade.php')));
    }
}
