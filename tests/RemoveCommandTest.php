<?php

namespace Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class RemoveCommandTest extends TestCase
{
    /** @test */
    public function component_is_removed_by_remove_command()
    {
        Artisan::call('make:livewire rob');

        $this->assertTrue(File::exists($this->livewireClassesPath('Rob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('rob.blade.php')));

        Artisan::call('livewire:rm rob');

        $this->assertFalse(File::exists($this->livewireClassesPath('Rob.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('rob.blade.php')));
    }

    /** @test */
    public function nested_component_is_removed_by_remove_command()
    {
        Artisan::call('make:livewire rob.lob');

        $this->assertTrue(File::exists($this->livewireClassesPath('Rob/Lob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('rob/lob.blade.php')));

        Artisan::call('livewire:rm rob.lob');

        $this->assertFalse(File::exists($this->livewireClassesPath('Rob/Lob.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('rob/lob.blade.php')));
    }

    /** @test */
    public function multiword_component_is_removed_by_remove_command()
    {
        Artisan::call('make:livewire rob-lob');

        $this->assertTrue(File::exists($this->livewireClassesPath('RobLob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('rob-lob.blade.php')));

        Artisan::call('livewire:rm rob-lob');

        $this->assertFalse(File::exists($this->livewireClassesPath('RobLob.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('rob-lob.blade.php')));
    }

    /** @test */
    public function pascal_case_component_is_automatically_converted_by_remove_command()
    {
        Artisan::call('make:livewire RobLob.RobLob');

        $this->assertTrue(File::exists($this->livewireClassesPath('RobLob/RobLob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('rob-lob/rob-lob.blade.php')));

        Artisan::call('livewire:rm RobLob.RobLob');

        $this->assertFalse(File::exists($this->livewireClassesPath('RobLob/RobLob.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('rob-lob/rob-lob.blade.php')));
    }
}
