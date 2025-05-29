<?php

namespace Livewire\Features\SupportConsoleCommands\Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class StubCommandUnitTest extends \Tests\TestCase
{
    public function test_default_view_stub_is_created()
    {
        Artisan::call('livewire:stubs');

        $this->assertTrue(File::exists($this->stubsPath('livewire.stub')));
        $this->assertTrue(File::exists($this->stubsPath('livewire.inline.stub')));
        $this->assertTrue(File::exists($this->stubsPath('livewire.view.stub')));
        $this->assertTrue(File::exists($this->stubsPath('livewire.test.stub')));
        $this->assertTrue(File::exists($this->stubsPath('livewire.pest.stub')));
        $this->assertTrue(File::exists($this->stubsPath('livewire.form.stub')));
        $this->assertTrue(File::exists($this->stubsPath('livewire.attribute.stub')));
    }

    public function test_component_is_created_with_view_and_class_custom_default_stubs()
    {
        Artisan::call('livewire:stubs');
        File::append($this->stubsPath('livewire.stub'), '// comment default');
        File::put($this->stubsPath('livewire.view.stub'), '<div>Default Test</div>');
        Artisan::call('livewire:make', ['name' => 'foo']);

        $this->assertTrue(File::exists($this->livewireClassesPath('Foo.php')));
        $this->assertStringContainsString('// comment default', File::get($this->livewireClassesPath('Foo.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo.blade.php')));
        $this->assertStringContainsString('Default Test', File::get($this->livewireViewsPath('foo.blade.php')));
    }

    public function test_inline_component_is_created_with_class_custom_default_stubs()
    {
        Artisan::call('livewire:stubs');
        File::append($this->stubsPath('livewire.inline.stub'), '// inline stub');
        Artisan::call('livewire:make', ['name' => 'foo', '--inline' => true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('Foo.php')));
        $this->assertStringContainsString('// inline stub', File::get($this->livewireClassesPath('Foo.php')));
    }

    public function test_form_is_created_with_class_custom_default_stubs()
    {
        Artisan::call('livewire:stubs');
        File::append($this->stubsPath('livewire.form.stub'), '// form stub');
        Artisan::call('livewire:form', ['name' => 'Foo']);

        $this->assertTrue(File::exists($this->livewireClassesPath('Forms/Foo.php')));
        $this->assertStringContainsString('// form stub', File::get($this->livewireClassesPath('Forms/Foo.php')));
    }

    public function test_attribute_is_created_with_class_custom_default_stubs()
    {
        Artisan::call('livewire:stubs');
        File::append($this->stubsPath('livewire.attribute.stub'), '// attribute stub');
        Artisan::call('livewire:attribute', ['name' => 'Foo']);

        $this->assertTrue(File::exists($this->livewireClassesPath('Attributes/Foo.php')));
        $this->assertStringContainsString('// attribute stub', File::get($this->livewireClassesPath('Attributes/Foo.php')));
    }

    public function test_test_is_created_with_class_custom_default_stubs()
    {
        Artisan::call('livewire:stubs');
        File::append($this->stubsPath('livewire.test.stub'), '// test stub');
        Artisan::call('livewire:make', ['name' => 'foo', '--test' => true]);

        $this->assertTrue(File::exists($this->livewireTestsPath('FooTest.php')));
        $this->assertStringContainsString('// test stub', File::get($this->livewireTestsPath('FooTest.php')));
    }

    public function test_pest_is_created_with_class_custom_default_stubs()
    {
        Artisan::call('livewire:stubs');
        File::append($this->stubsPath('livewire.pest.stub'), '// pest stub');
        Artisan::call('livewire:make', ['name' => 'foo', '--pest' => true]);

        $this->assertTrue(File::exists($this->livewireTestsPath('FooTest.php')));
        $this->assertStringContainsString('// pest stub', File::get($this->livewireTestsPath('FooTest.php')));
    }

    protected function stubsPath($path = '')
    {
        return base_path('stubs'.($path ? '/'.$path : ''));
    }
}
