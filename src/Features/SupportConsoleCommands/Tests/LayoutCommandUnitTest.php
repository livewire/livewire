<?php

namespace Livewire\Features\SupportConsoleCommands\Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class LayoutCommandUnitTest extends \Tests\TestCase
{
    public function test_layout_is_created_by_layout_command()
    {
        Artisan::call('livewire:layout');

        $this->assertTrue(File::exists($this->livewireLayoutsPath('app.blade.php')));
    }

    protected function livewireLayoutsPath($path = '')
    {
        return resource_path('views').'/components/layouts'.($path ? '/'.$path : '');
    }
}
