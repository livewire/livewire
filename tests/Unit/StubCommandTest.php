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

    /** @test */
    public function default_view_stub_is_created_with_subdirectory()
    {
        Artisan::call('livewire:stubs', ['--subDirectory' => 'mySub']);

        $this->assertTrue(File::exists($this->stubsPath('mySub/livewire.stub')));
        $this->assertTrue(File::exists($this->stubsPath('mySub/livewire.inline.stub')));
        $this->assertTrue(File::exists($this->stubsPath('mySub/livewire.view.stub')));
        $this->assertTrue(File::exists($this->stubsPath('mySub/livewire.test.stub')));
    }

    /** @test */
    public function component_is_created_with_view_and_class_custom_default_stubs_with_subdirectory()
    {
        Artisan::call('livewire:stubs', ['--subDirectory' => 'mySub']);
        File::append($this->stubsPath('mySub/livewire.stub'), '// comment default');
        File::put($this->stubsPath('mySub/livewire.view.stub'), '<div>Default Test</div>');
        Artisan::call('make:livewire', ['name' => 'foo','--stub' => 'mySub']);

        $this->assertTrue(File::exists($this->livewireClassesPath('Foo.php')));
        $this->assertStringContainsString('// comment default', File::get($this->livewireClassesPath('Foo.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo.blade.php')));
        $this->assertStringContainsString('Default Test', File::get($this->livewireViewsPath('foo.blade.php')));
    }

    /** @test */
    public function component_is_created_with_view_and_class_custom_default_stubs_with_extern_directory()
    {
        Artisan::call('livewire:stubs', ['--subDirectory' => 'mySub']);
        File::append($this->stubsPath('mySub/livewire.stub'), '// comment default');
        File::put($this->stubsPath('mySub/livewire.view.stub'), '<div>Default Test</div>');

        File::move($this->stubsPath('mySub/livewire.stub'), base_path('vendor/packages/stubs/mySub/livewire.stub'));
        File::move($this->stubsPath('mySub/livewire.view.stub'),base_path('vendor/packages/stubs/mySub/livewire.view.stub'));

        Artisan::call('make:livewire', ['name' => 'foo','--stub' => '../vendor/packages/stubs/mySub']);

        $this->assertTrue(File::exists($this->livewireClassesPath('Foo.php')));
        $this->assertStringContainsString('// comment default', File::get($this->livewireClassesPath('Foo.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo.blade.php')));
        $this->assertStringContainsString('Default Test', File::get($this->livewireViewsPath('foo.blade.php')));
    }
}
