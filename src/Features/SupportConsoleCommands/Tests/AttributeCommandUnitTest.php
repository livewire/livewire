<?php

namespace Livewire\Features\SupportConsoleCommands\Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;

class AttributeCommandUnitTest extends \Tests\TestCase
{
    #[Test]
    public function attribute_is_created_by_attribute_command()
    {
        Artisan::call('livewire:attribute', ['name' => 'SampleAttribute']);

        $filePath = $this->livewireClassesPath('Attributes/SampleAttribute.php');

        $this->assertTrue(File::exists($filePath));

        $this->assertTrue(str(File::get($filePath))->contains('namespace App\Livewire\Attributes;'));
    }


    #[Test]
    public function attribute_is_created_in_subdirectory_by_attribute_command()
    {
        Artisan::call('livewire:attribute', ['name' => 'Auth/SampleAttribute']);

        $filePath = $this->livewireClassesPath('Attributes/Auth/SampleAttribute.php');

        $this->assertTrue(File::exists($filePath));

        $this->assertTrue(str(File::get($filePath))->contains('namespace App\Livewire\Attributes\Auth;'));
    }
}
