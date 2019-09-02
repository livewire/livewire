<?php

namespace Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class RenameCommandTest extends TestCase
{
    /** @test */
    public function component_is_renamed_by_rename_command()
    {
        Artisan::call('make:livewire rob');

        $this->assertTrue(File::exists($this->livewireClassesPath('Rob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('rob.blade.php')));

        Artisan::call('livewire:mv rob lob');

        $this->assertTrue(File::exists($this->livewireClassesPath('Lob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('lob.blade.php')));

        $this->assertFalse(File::exists($this->livewireClassesPath('Rob.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('rob.blade.php')));
    }

    /** @test */
    public function nested_component_is_renamed_by_rename_command()
    {
        Artisan::call('make:livewire rob.lob');

        $this->assertTrue(File::exists($this->livewireClassesPath('Rob/Lob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('rob/lob.blade.php')));

        Artisan::call('livewire:mv rob.lob rob.lob.law');

        $this->assertTrue(File::exists($this->livewireClassesPath('Rob/Lob/Law.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('rob/lob/law.blade.php')));

        $this->assertFalse(File::exists($this->livewireClassesPath('Rob/Lob.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('rob/lob.blade.php')));
    }

    /** @test */
    public function multiword_component_is_renamed_by_rename_command()
    {
        Artisan::call('make:livewire rob-lob');

        $this->assertTrue(File::exists($this->livewireClassesPath('RobLob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('rob-lob.blade.php')));

        Artisan::call('livewire:mv rob-lob lob-law');

        $this->assertTrue(File::exists($this->livewireClassesPath('/LobLaw.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('lob-law.blade.php')));

        $this->assertFalse(File::exists($this->livewireClassesPath('RobLob.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('rob-lob.blade.php')));
    }

    /** @test */
    public function pascal_case_component_is_automatically_converted_by_rename_command()
    {
        Artisan::call('make:livewire RobLob.RobLob');

        $this->assertTrue(File::exists($this->livewireClassesPath('RobLob/RobLob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('rob-lob/rob-lob.blade.php')));

        Artisan::call('livewire:mv RobLob.RobLob LobLaw.LobLaw');

        $this->assertFalse(File::exists($this->livewireClassesPath('RobLob/RobLob.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('rob-lob/rob-lob.blade.php')));

        $this->assertTrue(File::exists($this->livewireClassesPath('LobLaw/LobLaw.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('lob-law/lob-law.blade.php')));
    }
}
