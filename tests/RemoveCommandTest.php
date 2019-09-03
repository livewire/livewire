<?php

namespace Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class RemoveCommandTest extends TestCase
{
    /** @test */
    public function component_is_removed_by_remove_command()
    {
        Artisan::call('make:livewire bob');

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob.blade.php')));

        Artisan::call('livewire:rm bob --force');

        $this->assertFalse(File::exists($this->livewireClassesPath('Bob.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('bob.blade.php')));
    }

    /** @test */
    public function nested_component_is_removed_by_remove_command()
    {
        Artisan::call('make:livewire bob.lob');

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob/Lob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob/lob.blade.php')));

        Artisan::call('livewire:rm bob.lob --force');

        $this->assertFalse(File::exists($this->livewireClassesPath('Bob/Lob.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('bob/lob.blade.php')));
    }

    /** @test */
    public function multiword_component_is_removed_by_remove_command()
    {
        Artisan::call('make:livewire bob-lob');

        $this->assertTrue(File::exists($this->livewireClassesPath('BobLob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob-lob.blade.php')));

        Artisan::call('livewire:rm bob-lob --force');

        $this->assertFalse(File::exists($this->livewireClassesPath('BobLob.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('bob-lob.blade.php')));
    }

    /** @test */
    public function pascal_case_component_is_automatically_converted_by_remove_command()
    {
        Artisan::call('make:livewire BobLob.BobLob');

        $this->assertTrue(File::exists($this->livewireClassesPath('BobLob/BobLob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob-lob/bob-lob.blade.php')));

        Artisan::call('livewire:rm BobLob.BobLob --force');

        $this->assertFalse(File::exists($this->livewireClassesPath('BobLob/BobLob.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('bob-lob/bob-lob.blade.php')));
    }
}
