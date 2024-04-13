<?php

namespace Livewire\Features\SupportConsoleCommands\Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;

class LayoutCommandUnitTest extends \Tests\TestCase
{
    #[Test]
    public function layout_is_created_by_layout_command()
    {
        Artisan::call('livewire:layout');

        $this->assertTrue(File::exists($this->livewireLayoutsPath('app.blade.php')));
    }

    protected function livewireLayoutsPath($path = '')
    {
        return resource_path('views').'/components/layouts'.($path ? '/'.$path : '');
    }
}
