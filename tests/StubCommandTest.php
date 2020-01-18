<?php

namespace Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class StubCommandTest extends TestCase
{
    /** @test */
    public function default_view_stub_is_created()
    {
        Artisan::call('livewire:stub');

        $this->assertTrue(File::exists($this->livewireViewStubsPath('default.stub')));
        $this->assertTrue(File::exists($this->livewireClassStubsPath('Default.stub')));
    }

    /** @test */
    public function custom_stub_is_created_by_stub_command()
    {
        Artisan::call('livewire:stub', ['name' => 'pizza']);

        $this->assertTrue(File::exists($this->livewireViewStubsPath('pizza.stub')));
        $this->assertTrue(File::exists($this->livewireClassStubsPath('Pizza.stub')));
    }

    /** @test */
    public function component_is_created_with_view_and_class_stubs()
    {
        Artisan::call('livewire:stub', ['name' => 'modal']);
        File::put($this->livewireViewsPath('stubs/modal.stub'), '<div>Modal Test</div>');
        File::append($this->livewireClassesPath('Stubs/Modal.stub'), '// comment');
        Artisan::call('make:livewire', ['name' => 'foo', '--stub' => 'modal']);

        $this->assertTrue(File::exists($this->livewireClassesPath('Foo.php')));
        $this->assertStringContainsString('// comment', File::get($this->livewireClassesPath('Foo.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo.blade.php')));
        $this->assertStringContainsString('Modal Test', File::get($this->livewireViewsPath('foo.blade.php')));
    }

    /** @test */
    public function component_is_created_with_view_and_class_custom_default_stubs()
    {
        Artisan::call('livewire:stub');
        File::put($this->livewireViewsPath('stubs/default.stub'), '<div>Default Test</div>');
        File::append($this->livewireClassesPath('Stubs/Default.stub'), '// comment default');
        Artisan::call('make:livewire', ['name' => 'foo']);

        $this->assertTrue(File::exists($this->livewireClassesPath('Foo.php')));
        $this->assertStringContainsString('// comment default', File::get($this->livewireClassesPath('Foo.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo.blade.php')));
        $this->assertStringContainsString('Default Test', File::get($this->livewireViewsPath('foo.blade.php')));
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
