<?php

namespace Livewire\Features\SupportConsoleCommands\Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class StubCommandUnitTest extends \Tests\TestCase
{
    public function setUp(): void
    {
        \Livewire\LivewireManager::$v4 = false;

        parent::setUp();
    }

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
        $this->assertTrue(File::exists($this->stubsPath('livewire.layout.stub')));
        $this->assertTrue(File::exists($this->stubsPath('livewire-sfc.stub')));
        $this->assertTrue(File::exists($this->stubsPath('livewire-mfc-class.stub')));
        $this->assertTrue(File::exists($this->stubsPath('livewire-mfc-view.stub')));
        $this->assertTrue(File::exists($this->stubsPath('livewire-mfc-test.stub')));
        $this->assertTrue(File::exists($this->stubsPath('livewire-mfc-js.stub')));
        $this->assertTrue(File::exists($this->stubsPath('livewire-mfc-css.stub')));
    }

    public function test_component_is_created_with_view_and_class_custom_default_stubs()
    {
        Artisan::call('livewire:stubs');
        File::append($this->stubsPath('livewire.stub'), '// comment default');
        File::put($this->stubsPath('livewire.view.stub'), '<div>Default Test</div>');
        Artisan::call('livewire:make', ['name' => 'foo', '--type' => 'class']);

        $this->assertTrue(File::exists($this->livewireClassesPath('Foo.php')));
        $this->assertStringContainsString('// comment default', File::get($this->livewireClassesPath('Foo.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo.blade.php')));
        $this->assertStringContainsString('Default Test', File::get($this->livewireViewsPath('foo.blade.php')));
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

    protected function stubsPath($path = '')
    {
        return base_path('stubs'.($path ? '/'.$path : ''));
    }
}
