<?php

namespace Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class MakeCommandTest extends TestCase
{
    /** @test */
    function component_is_created_by_make_command()
    {
        Artisan::call('make:livewire foo');

        $this->assertTrue(File::exists($this->livewireClassesPath('Foo.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo.blade.php')));
    }

    /** @test */
    function nested_component_is_created_by_make_command()
    {
        Artisan::call('make:livewire foo.bar');

        $this->assertTrue(File::exists($this->livewireClassesPath('Foo/Bar.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo/bar.blade.php')));
    }

    /** @test */
    function multiword_component_is_created_by_make_command()
    {
        Artisan::call('make:livewire foo-bar');

        $this->assertTrue(File::exists($this->livewireClassesPath('FooBar.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo-bar.blade.php')));
    }
}
