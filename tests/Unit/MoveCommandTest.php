<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class MoveCommandTest extends TestCase
{
    /** @test */
    public function component_is_renamed_by_move_command()
    {
        Artisan::call('make:livewire', ['name' => 'bob']);

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob.blade.php')));

        Artisan::call('livewire:move', ['name' => 'bob', 'new-name' => 'lob']);

        $this->assertTrue(File::exists($this->livewireClassesPath('Lob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('lob.blade.php')));

        $this->assertFalse(File::exists($this->livewireClassesPath('Bob.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('bob.blade.php')));
    }

    /** @test */
    public function inline_component_is_renamed_by_move_command()
    {
        Artisan::call('make:livewire', ['name' => 'bob', '--inline' => true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('bob.blade.php')));

        Artisan::call('livewire:move', ['name' => 'bob', 'new-name' => 'lob', '--inline' => true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('Lob.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('lob.blade.php')));

        $this->assertFalse(File::exists($this->livewireClassesPath('Bob.php')));
    }
    /** @test */
    public function component_with_test_is_renamed_by_move_command()
    {
        Artisan::call('make:livewire', ['name' => 'bob','--test'=>true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('BobTest.php')));

        Artisan::call('livewire:move', ['name' => 'bob', 'new-name' => 'lob']);

        $this->assertTrue(File::exists($this->livewireClassesPath('Lob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('lob.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('LobTest.php')));

        $this->assertFalse(File::exists($this->livewireClassesPath('Bob.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('bob.blade.php')));
        $this->assertFalse(File::exists($this->livewireTestsPath('BobTest.php')));

    }
    /** @test */
    public function component_is_renamed_by_mv_command()
    {
        Artisan::call('make:livewire', ['name' => 'bob']);

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob.blade.php')));

        Artisan::call('livewire:mv', ['name' => 'bob', 'new-name' => 'lob']);

        $this->assertTrue(File::exists($this->livewireClassesPath('Lob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('lob.blade.php')));

        $this->assertFalse(File::exists($this->livewireClassesPath('Bob.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('bob.blade.php')));
    }

    /** @test */
    public function nested_component_is_renamed_by_move_command()
    {
        Artisan::call('make:livewire', ['name' => 'bob.lob','--test'=>true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob/Lob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob/lob.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('Bob/LobTest.php')));

        Artisan::call('livewire:move', ['name' => 'bob.lob', 'new-name' => 'bob.lob.law']);

        $this->assertTrue(File::exists($this->livewireClassesPath('Bob/Lob/Law.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob/lob/law.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('Bob/Lob/LawTest.php')));

        $this->assertFalse(File::exists($this->livewireClassesPath('Bob/Lob.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('bob/lob.blade.php')));
        $this->assertFalse(File::exists($this->livewireTestsPath('Bob/LobTest.php')));

    }

    /** @test */
    public function multiword_component_is_renamed_by_move_command()
    {
        Artisan::call('make:livewire', ['name' => 'bob-lob','--test'=>true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('BobLob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob-lob.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('BobLobTest.php')));

        Artisan::call('livewire:move', ['name' => 'bob-lob', 'new-name' => 'lob-law']);

        $this->assertTrue(File::exists($this->livewireClassesPath('/LobLaw.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('lob-law.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('LobLawTest.php')));

        $this->assertFalse(File::exists($this->livewireClassesPath('BobLob.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('bob-lob.blade.php')));
        $this->assertFalse(File::exists($this->livewireTestsPath('BobLobTest.php')));

    }

    /** @test */
    public function pascal_case_component_is_automatically_converted_by_move_command()
    {
        Artisan::call('make:livewire', ['name' => 'BobLob.BobLob','--test'=>true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('BobLob/BobLob.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('bob-lob/bob-lob.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('BobLob/BobLobTest.php')));

        Artisan::call('livewire:move', ['name' => 'BobLob.BobLob', 'new-name' => 'LobLaw.LobLaw']);

        $this->assertFalse(File::exists($this->livewireClassesPath('BobLob/BobLob.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('bob-lob/bob-lob.blade.php')));
        $this->assertFalse(File::exists($this->livewireTestsPath('BobLob/BobLobTest.php')));

        $this->assertTrue(File::exists($this->livewireClassesPath('LobLaw/LobLaw.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('lob-law/lob-law.blade.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('LobLaw/LobLawTest.php')));
    }
}
