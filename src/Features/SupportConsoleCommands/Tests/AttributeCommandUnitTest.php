<?php

namespace Livewire\Features\SupportConsoleCommands\Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class AttributeCommandUnitTest extends \Tests\TestCase
{
    public function test_attribute_is_created_by_attribute_command()
    {
        Artisan::call('livewire:attribute', ['name' => 'SampleAttribute']);

        $filePath = $this->livewireClassesPath('Attributes/SampleAttribute.php');

        $this->assertTrue(File::exists($filePath));

        $this->assertTrue(str(File::get($filePath))->contains('namespace App\Livewire\Attributes;'));
    }


    public function test_attribute_is_created_in_subdirectory_by_attribute_command()
    {
        Artisan::call('livewire:attribute', ['name' => 'Auth/SampleAttribute']);

        $filePath = $this->livewireClassesPath('Attributes/Auth/SampleAttribute.php');

        $this->assertTrue(File::exists($filePath));

        $this->assertTrue(str(File::get($filePath))->contains('namespace App\Livewire\Attributes\Auth;'));
    }
}
