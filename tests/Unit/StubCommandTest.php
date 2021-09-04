<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class StubCommandTest extends TestCase
{
    /** @test */
    public function default_view_stub_is_created()
    {
        Artisan::call('livewire:stubs');

        $this->assertTrue(File::exists($this->stubsPath('livewire.stub')));
        $this->assertTrue(File::exists($this->stubsPath('livewire.inline.stub')));
        $this->assertTrue(File::exists($this->stubsPath('livewire.view.stub')));
        $this->assertTrue(File::exists($this->stubsPath('livewire.test.stub')));
    }

    /** @test */
    public function component_is_created_with_view_and_class_custom_default_stubs()
    {
        Artisan::call('livewire:stubs');
        File::append($this->stubsPath('livewire.stub'), '// comment default');
        File::put($this->stubsPath('livewire.view.stub'), '<div>Default Test</div>');
        Artisan::call('make:livewire', ['name' => 'foo']);

        $this->assertTrue(File::exists($this->livewireClassesPath('Foo.php')));
        $this->assertStringContainsString('// comment default', File::get($this->livewireClassesPath('Foo.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo.blade.php')));
        $this->assertStringContainsString('Default Test', File::get($this->livewireViewsPath('foo.blade.php')));
    }

    protected function stubsPath($path = '')
    {
        return base_path('stubs'.($path ? '/'.$path : ''));
    }
}
