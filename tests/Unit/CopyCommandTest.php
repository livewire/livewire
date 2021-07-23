<?php

namespace Tests\Unit;

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
    public function component_with_test_is_copied_by_copy_command()
    {
        Artisan::call('make:livewire', ['name' => 'bob','--test'=>true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('BobTest.php')));

        Artisan::call('livewire:copy', ['name' => 'bob', 'new-name' => 'lob','--test'=>true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('Lob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('lob.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('LobTest.php')));

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('BobTest.php')));
    }
    /** @test */
    public function component_is_copied_by_cp_command()
    {
        Artisan::call('make:livewire', ['name' => 'bob','--test'=>true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('BobTest.php')));

        Artisan::call('livewire:cp', ['name' => 'bob', 'new-name' => 'lob','--test'=>true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('Lob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('lob.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('LobTest.php')));

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('BobTest.php')));

    }

    /** @test */
    public function nested_component_is_copied_by_copy_command()
    {
        Artisan::call('make:livewire', ['name' => 'bob.lob','--test'=>true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob/Lob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob/lob.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('Bob/LobTest.php')));

        Artisan::call('livewire:copy', ['name' => 'bob.lob', 'new-name' => 'bob.lob.law','--test'=>true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob/Lob/Law.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob/lob/law.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('Bob/Lob/LawTest.php')));

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob/Lob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob/lob.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('Bob/LobTest.php')));

    }

    /** @test */
    public function multiword_component_is_copied_by_copy_command()
    {
        Artisan::call('make:livewire', ['name' => 'bob-lob','--test'=>true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('BobLob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob-lob.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('BobLobTest.php')));

        Artisan::call('livewire:copy', ['name' => 'bob-lob', 'new-name' => 'lob-law','--test'=>true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('/LobLaw.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('lob-law.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('LobLawTest.php')));

        $this->assertTrue(File::exists($this->livewireClassesPath('BobLob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob-lob.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('BobLobTest.php')));

    }

    /** @test */
    public function pascal_case_component_is_automatically_converted_by_copy_command()
    {
        Artisan::call('make:livewire', ['name' => 'BobLob.BobLob','--test'=>true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('BobLob/BobLob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob-lob/bob-lob.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('BobLob/BobLobTest.php')));

        Artisan::call('livewire:copy', ['name' => 'BobLob.BobLob', 'new-name' => 'LobLaw.LobLaw','--test'=>true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('BobLob/BobLob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob-lob/bob-lob.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('BobLob/BobLobTest.php')));

        $this->assertTrue(File::exists($this->livewireClassesPath('LobLaw/LobLaw.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('lob-law/lob-law.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('LobLaw/LobLawTest.php')));
    }

    /** @test */
    public function cannot_copy_component_to_a_name_that_already_exists()
    {
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
