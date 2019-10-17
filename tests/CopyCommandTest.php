<?php

namespace Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class CopyCommandTest extends TestCase
{
    /** @test */
    public function component_is_copied_by_copy_command()
    {
        Artisan::call('make:livewire bob');

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob.blade.php')));

        Artisan::call('livewire:copy bob lob');

        $this->assertTrue(File::exists($this->livewireClassesPath('Lob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('lob.blade.php')));

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob.blade.php')));
    }

    /** @test */
    public function component_is_copied_by_cp_command()
    {
        Artisan::call('make:livewire bob');

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob.blade.php')));

        Artisan::call('livewire:cp bob lob');

        $this->assertTrue(File::exists($this->livewireClassesPath('Lob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('lob.blade.php')));

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob.blade.php')));
    }

    /** @test */
    public function nested_component_is_copied_by_copy_command()
    {
        Artisan::call('make:livewire bob.lob');

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob/Lob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob/lob.blade.php')));

        Artisan::call('livewire:copy bob.lob bob.lob.law');

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob/Lob/Law.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob/lob/law.blade.php')));

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob/Lob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob/lob.blade.php')));
    }

    /** @test */
    public function multiword_component_is_copied_by_copy_command()
    {
        Artisan::call('make:livewire bob-lob');

        $this->assertTrue(File::exists($this->livewireClassesPath('BobLob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob-lob.blade.php')));

        Artisan::call('livewire:copy bob-lob lob-law');

        $this->assertTrue(File::exists($this->livewireClassesPath('/LobLaw.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('lob-law.blade.php')));

        $this->assertTrue(File::exists($this->livewireClassesPath('BobLob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob-lob.blade.php')));
    }

    /** @test */
    public function pascal_case_component_is_automatically_converted_by_copy_command()
    {
        Artisan::call('make:livewire BobLob.BobLob');

        $this->assertTrue(File::exists($this->livewireClassesPath('BobLob/BobLob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob-lob/bob-lob.blade.php')));

        Artisan::call('livewire:copy BobLob.BobLob LobLaw.LobLaw');

        $this->assertTrue(File::exists($this->livewireClassesPath('BobLob/BobLob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob-lob/bob-lob.blade.php')));

        $this->assertTrue(File::exists($this->livewireClassesPath('LobLaw/LobLaw.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('lob-law/lob-law.blade.php')));
    }

    /** @test */
    public function cannot_copy_component_to_a_name_that_already_exists()
    {
        Artisan::call('make:livewire BobLob.BobLob');
        Artisan::call('make:livewire BobLob.LobLaw');

        $this->assertTrue(File::exists($this->livewireClassesPath('BobLob/BobLob.php')));
        $this->assertTrue(File::exists($this->livewireClassesPath('BobLob/LobLaw.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob-lob/bob-lob.blade.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob-lob/lob-law.blade.php')));

        $this->artisan('livewire:copy BobLob.BobLob BobLob.LobLaw')->expectsOutput('Class already exists: app/Http/Livewire/BobLob/LobLaw.php');
    }

    /** @test */
    public function can_copy_component_to_a_name_that_already_exists_if_forced()
    {
        Artisan::call('make:livewire BobLob.BobLob');
        Artisan::call('make:livewire BobLob.LobLaw');

        $this->assertTrue(File::exists($this->livewireClassesPath('BobLob/BobLob.php')));
        $this->assertTrue(File::exists($this->livewireClassesPath('BobLob/LobLaw.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob-lob/bob-lob.blade.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob-lob/lob-law.blade.php')));

        $this->artisan('livewire:copy BobLob.BobLob BobLob.LobLaw --force')->expectsOutput('CLASS: app/Http/Livewire/BobLob/BobLob.php => app/Http/Livewire/BobLob/LobLaw.php');
    }
}
