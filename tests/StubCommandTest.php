<?php

namespace Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class StubCommandTest extends TestCase
{
    /** @test */
    public function view_stub_is_created_by_stub_command()
    {
        Artisan::call('livewire:stub Pizza');

        $this->assertTrue(File::exists($this->livewireViewStubsPath('pizza.stub')));
    }

    /** @test */
    public function class_stub_is_created_by_stub_command()
    {
        Artisan::call('livewire:stub Pizza');

        $this->assertTrue(File::exists($this->livewireClassStubsPath('Pizza.stub')));
    }

    protected function livewireClassStubsPath($path = '')
    {
        return app_path('Http/Livewire/Stubs'.($path ? '/'.$path : ''));
    }

    protected function livewireViewStubsPath($path = '')
    {
        return resource_path('views').'/livewire/stubs'.($path ? '/'.$path : '');
    }
}
