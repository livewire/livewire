<?php

namespace Livewire\Features\SupportConsoleCommands\Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class FormCommandUnitTest extends \Tests\TestCase
{
    public function test_form_object_is_created_by_form_command()
    {
        Artisan::call('livewire:form', ['name' => 'SampleForm']);

        $filePath = $this->livewireClassesPath('Forms/SampleForm.php');

        $this->assertTrue(File::exists($filePath));

        $this->assertTrue(str(File::get($filePath))->contains('namespace App\Livewire\Forms;'));
    }


    public function test_form_object_is_created_in_subdirectory_by_form_command()
    {
        Artisan::call('livewire:form', ['name' => 'Auth/SampleForm']);

        $filePath = $this->livewireClassesPath('Forms/Auth/SampleForm.php');

        $this->assertTrue(File::exists($filePath));

        $this->assertTrue(str(File::get($filePath))->contains('namespace App\Livewire\Forms\Auth;'));
    }
}
