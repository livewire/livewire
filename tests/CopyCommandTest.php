<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class CopyCommandTest extends TestCase
{
    /** @test */
    public function component_is_copied_by_copy_command()
    {
        Artisan::call('make:livewire', ['name' => 'bob']);

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob.blade.php')));

        Artisan::call('livewire:copy', ['name' => 'bob', 'new-name' => 'lob']);

        $this->assertTrue(File::exists($this->livewireClassesPath('Lob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('lob.blade.php')));

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob.blade.php')));
    }

    /** @test */
    public function inline_component_is_copied_by_copy_command()
    {
        Artisan::call('make:livewire', ['name' => 'bob', '--inline' => true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('bob.blade.php')));

        Artisan::call('livewire:copy', ['name' => 'bob', 'new-name' => 'lob', '--inline' => true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('Lob.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('lob.blade.php')));

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('bob.blade.php')));
    }

    /** @test */
    public function component_is_copied_by_cp_command()
    {
        Artisan::call('make:livewire', ['name' => 'bob']);

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob.blade.php')));

        Artisan::call('livewire:cp', ['name' => 'bob', 'new-name' => 'lob']);

        $this->assertTrue(File::exists($this->livewireClassesPath('Lob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('lob.blade.php')));

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob.blade.php')));
    }

    /** @test */
    public function nested_component_is_copied_by_copy_command()
    {
        Artisan::call('make:livewire', ['name' => 'bob.lob']);

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob/Lob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob/lob.blade.php')));

        Artisan::call('livewire:copy', ['name' => 'bob.lob', 'new-name' => 'bob.lob.law']);

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob/Lob/Law.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob/lob/law.blade.php')));

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob/Lob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob/lob.blade.php')));
    }

    /** @test */
    public function multiword_component_is_copied_by_copy_command()
    {
        Artisan::call('make:livewire', ['name' => 'bob-lob']);

        $this->assertTrue(File::exists($this->livewireClassesPath('BobLob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob-lob.blade.php')));

        Artisan::call('livewire:copy', ['name' => 'bob-lob', 'new-name' => 'lob-law']);

        $this->assertTrue(File::exists($this->livewireClassesPath('/LobLaw.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('lob-law.blade.php')));

        $this->assertTrue(File::exists($this->livewireClassesPath('BobLob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob-lob.blade.php')));
    }

    /** @test */
    public function pascal_case_component_is_automatically_converted_by_copy_command()
    {
        Artisan::call('make:livewire', ['name' => 'BobLob.BobLob']);

        $this->assertTrue(File::exists($this->livewireClassesPath('BobLob/BobLob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob-lob/bob-lob.blade.php')));

        Artisan::call('livewire:copy', ['name' => 'BobLob.BobLob', 'new-name' => 'LobLaw.LobLaw']);

        $this->assertTrue(File::exists($this->livewireClassesPath('BobLob/BobLob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob-lob/bob-lob.blade.php')));

        $this->assertTrue(File::exists($this->livewireClassesPath('LobLaw/LobLaw.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('lob-law/lob-law.blade.php')));
    }

    /** @test */
    public function cannot_copy_component_to_a_name_that_already_exists()
    {
        if (version_compare(Application::VERSION, '5.7', '<')) {
            $this->markTestSkipped('Console testing not available prior to Laravel 5.7');
        }

        Artisan::call('make:livewire', ['name' => 'BobLob.BobLob']);
        Artisan::call('make:livewire', ['name' => 'BobLob.LobLaw']);

        $this->assertTrue(File::exists($this->livewireClassesPath('BobLob/BobLob.php')));
        $this->assertTrue(File::exists($this->livewireClassesPath('BobLob/LobLaw.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob-lob/bob-lob.blade.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob-lob/lob-law.blade.php')));

        $this->artisan('livewire:copy', ['name' => 'BobLob.BobLob', 'new-name' => 'BobLob.LobLaw'])
            ->expectsOutput('Class already exists: app/Http/Livewire/BobLob/LobLaw.php');
    }

    /** @test */
    public function can_copy_component_to_a_name_that_already_exists_if_forced()
    {
        if (version_compare(Application::VERSION, '5.7', '<')) {
            $this->markTestSkipped('Console testing not available prior to Laravel 5.7');
        }

        Artisan::call('make:livewire', ['name' => 'BobLob.BobLob']);
        Artisan::call('make:livewire', ['name' => 'BobLob.LobLaw']);

        $this->assertTrue(File::exists($this->livewireClassesPath('BobLob/BobLob.php')));
        $this->assertTrue(File::exists($this->livewireClassesPath('BobLob/LobLaw.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob-lob/bob-lob.blade.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob-lob/lob-law.blade.php')));

        $this->artisan('livewire:copy', ['name' => 'BobLob.BobLob', 'new-name' => 'BobLob.LobLaw', '--force' => true])
            ->expectsOutput('CLASS: app/Http/Livewire/BobLob/BobLob.php => app/Http/Livewire/BobLob/LobLaw.php');
    }
}
