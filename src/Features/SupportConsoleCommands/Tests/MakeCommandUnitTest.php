<?php

namespace Livewire\Features\SupportConsoleCommands\Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class MakeCommandUnitTest extends \Tests\TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Ensure components are cleared before each test...
        $this->makeACleanSlate();
    }

    public function test_single_file_component_is_created_by_default()
    {
        Artisan::call('make:livewire', ['name' => 'foo']);

        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo.blade.php')));
        $this->assertFalse(File::exists($this->livewireClassesPath('Foo.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('foo.blade.php')));
    }

    public function test_single_file_component_without_emoji_when_disabled_in_config()
    {
        $this->app['config']->set('livewire.make_command.emoji', false);

        Artisan::call('make:livewire', ['name' => 'foo']);

        $this->assertTrue(File::exists($this->livewireComponentsPath('foo.blade.php')));
        $this->assertFalse(File::exists($this->livewireComponentsPath('⚡foo.blade.php')));
    }

    public function test_single_file_component_with_sfc_flag()
    {
        Artisan::call('make:livewire', ['name' => 'foo', '--sfc' => true]);

        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo.blade.php')));
    }

    public function test_multi_file_component_is_created_with_mfc_flag()
    {
        Artisan::call('make:livewire', ['name' => 'foo', '--mfc' => true]);

        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('⚡foo')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.blade.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.test.php')));
        $this->assertFalse(File::exists($this->livewireComponentsPath('⚡foo/foo.js')));
    }

    public function test_multi_file_component_with_javascript_when_js_flag_provided()
    {
        Artisan::call('make:livewire', ['name' => 'foo', '--mfc' => true, '--js' => true]);

        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('⚡foo')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.blade.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.test.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.js')));
    }

    public function test_class_based_component_is_created_with_class_flag()
    {
        Artisan::call('make:livewire', ['name' => 'foo', '--class' => true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('Foo.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo.blade.php')));
        $this->assertFalse(File::exists($this->livewireComponentsPath('⚡foo.blade.php')));
    }

    public function test_component_type_can_be_specified_with_type_option()
    {
        Artisan::call('make:livewire', ['name' => 'foo', '--type' => 'class']);

        $this->assertTrue(File::exists($this->livewireClassesPath('Foo.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo.blade.php')));

        $this->makeACleanSlate();

        Artisan::call('make:livewire', ['name' => 'bar', '--type' => 'mfc']);

        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('⚡bar')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡bar/bar.php')));
    }

    public function test_dot_nested_component_is_created_correctly()
    {
        Artisan::call('make:livewire', ['name' => 'foo.bar']);

        $this->assertTrue(File::exists($this->livewireComponentsPath('foo/⚡bar.blade.php')));

        $this->makeACleanSlate();

        Artisan::call('make:livewire', ['name' => 'foo.bar', '--class' => true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('Foo/Bar.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo/bar.blade.php')));
    }

    public function test_forward_slash_nested_component_is_created_correctly()
    {
        Artisan::call('make:livewire', ['name' => 'foo/bar']);

        $this->assertTrue(File::exists($this->livewireComponentsPath('foo/⚡bar.blade.php')));

        $this->makeACleanSlate();

        Artisan::call('make:livewire', ['name' => 'foo/bar', '--class' => true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('Foo/Bar.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo/bar.blade.php')));
    }

    public function test_backslash_nested_component_is_created_correctly()
    {
        Artisan::call('make:livewire', ['name' => 'foo\\bar']);

        $this->assertTrue(File::exists($this->livewireComponentsPath('foo/⚡bar.blade.php')));

        $this->makeACleanSlate();

        Artisan::call('make:livewire', ['name' => 'foo\\bar', '--class' => true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('Foo/Bar.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo/bar.blade.php')));
    }

    public function test_multiword_component_is_created_with_kebab_case()
    {
        Artisan::call('make:livewire', ['name' => 'foo-bar']);

        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo-bar.blade.php')));

        $this->makeACleanSlate();

        Artisan::call('make:livewire', ['name' => 'foo-bar', '--class' => true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('FooBar.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo-bar.blade.php')));
    }

    public function test_pascal_case_component_is_automatically_converted()
    {
        Artisan::call('make:livewire', ['name' => 'FooBar']);

        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo-bar.blade.php')));

        $this->makeACleanSlate();

        Artisan::call('make:livewire', ['name' => 'FooBar.BazQux', '--class' => true]);

        $this->assertTrue(File::exists($this->livewireClassesPath('FooBar/BazQux.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo-bar/baz-qux.blade.php')));
    }

    public function test_class_based_component_view_name_reference_matches_configured_view_path()
    {
        // We can't use Artisan::call here because we need to be able to set config vars.
        $this->app['config']->set('livewire.view_path', resource_path('views/not-livewire'));
        $this->app[Kernel::class]->call('make:livewire', ['name' => 'foo', '--class' => true]);

        $this->assertStringContainsString(
            "view('not-livewire.foo')",
            File::get($this->livewireClassesPath('Foo.php'))
        );
        $this->assertTrue(File::exists(resource_path('views/not-livewire/foo.blade.php')));
    }

    public function test_class_based_component_already_exists_shows_error()
    {
        // Create initial component
        Artisan::call('make:livewire', ['name' => 'foo', '--class' => true]);
        $this->assertTrue(File::exists($this->livewireClassesPath('Foo.php')));

        // Try to create again
        $exitCode = Artisan::call('make:livewire', ['name' => 'foo', '--class' => true]);

        $this->assertEquals(1, $exitCode);
    }

    public function test_multi_file_component_already_exists_shows_error()
    {
        // Create initial component
        Artisan::call('make:livewire', ['name' => 'foo', '--mfc' => true]);
        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('⚡foo')));

        // Try to create again
        $exitCode = Artisan::call('make:livewire', ['name' => 'foo', '--mfc' => true]);

        $this->assertEquals(1, $exitCode);
    }

    public function test_single_file_component_content_structure()
    {
        Artisan::call('make:livewire', ['name' => 'test-component']);

        $content = File::get($this->livewireComponentsPath('⚡test-component.blade.php'));

        $this->assertStringContainsString('<?php', $content);
        $this->assertStringContainsString('use Livewire\Component;', $content);
        $this->assertStringContainsString('new class extends Component', $content);
        $this->assertStringContainsString('?>', $content);
        $this->assertStringContainsString('<div>', $content);
    }

    public function test_class_based_component_content_structure()
    {
        Artisan::call('make:livewire', ['name' => 'test-component', '--class' => true]);

        $classContent = File::get($this->livewireClassesPath('TestComponent.php'));
        $viewContent = File::get($this->livewireViewsPath('test-component.blade.php'));

        // Check class file
        $this->assertStringContainsString('namespace App\Livewire;', $classContent);
        $this->assertStringContainsString('use Livewire\Component;', $classContent);
        $this->assertStringContainsString('class TestComponent extends Component', $classContent);
        $this->assertStringContainsString("view('livewire.test-component')", $classContent);

        // Check view file
        $this->assertStringContainsString('<div>', $viewContent);
    }

    public function test_multi_file_component_content_structure()
    {
        Artisan::call('make:livewire', ['name' => 'test-component', '--mfc' => true]);

        $classContent = File::get($this->livewireComponentsPath('⚡test-component/test-component.php'));
        $viewContent = File::get($this->livewireComponentsPath('⚡test-component/test-component.blade.php'));
        $testContent = File::get($this->livewireComponentsPath('⚡test-component/test-component.test.php'));

        // Check class file
        $this->assertStringContainsString('use Livewire\Component;', $classContent);
        $this->assertStringContainsString('new class extends Component', $classContent);

        // Check view file
        $this->assertStringContainsString('<div>', $viewContent);

        // Check test file
        $this->assertStringContainsString("it('renders successfully'", $testContent);
        $this->assertStringContainsString('test-component', $testContent);
    }

    public function test_default_component_type_can_be_configured()
    {
        // Test default SFC
        $this->app['config']->set('livewire.make_command.type', 'sfc');
        Artisan::call('make:livewire', ['name' => 'default-sfc']);
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡default-sfc.blade.php')));

        $this->makeACleanSlate();

        // Test default MFC
        $this->app['config']->set('livewire.make_command.type', 'mfc');
        Artisan::call('make:livewire', ['name' => 'default-mfc']);
        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('⚡default-mfc')));

        $this->makeACleanSlate();

        // Test default class
        $this->app['config']->set('livewire.make_command.type', 'class');
        Artisan::call('make:livewire', ['name' => 'default-class']);
        $this->assertTrue(File::exists($this->livewireClassesPath('DefaultClass.php')));
    }
}