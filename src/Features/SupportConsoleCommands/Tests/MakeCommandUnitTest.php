<?php

namespace Livewire\Features\SupportConsoleCommands\Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;

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
        $this->assertFalse(File::exists($this->livewireComponentsPath('⚡foo/foo.test.php')));
        $this->assertFalse(File::exists($this->livewireComponentsPath('⚡foo/foo.js')));
    }

    public function test_multi_file_component_with_javascript_when_js_flag_provided()
    {
        Artisan::call('make:livewire', ['name' => 'foo', '--mfc' => true, '--js' => true]);

        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('⚡foo')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.blade.php')));
        $this->assertFalse(File::exists($this->livewireComponentsPath('⚡foo/foo.test.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.js')));
    }

    public function test_multi_file_component_with_javascript_when_configured_in_make_command_with()
    {
        $this->app['config']->set('livewire.make_command.with.js', true);

        Artisan::call('make:livewire', ['name' => 'foo', '--mfc' => true]);

        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('⚡foo')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.blade.php')));
        $this->assertFalse(File::exists($this->livewireComponentsPath('⚡foo/foo.test.php')));
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
        Artisan::call('make:livewire', ['name' => 'test-component', '--mfc' => true, '--test' => true]);

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

    public function test_class_based_component_view_in_livewire_folder_is_not_mistaken_for_sfc()
    {
        // This test demonstrates the issue where a class-based component's view
        // in resources/views/livewire/ might be mistaken for an SFC

        // First, create a class-based component
        Artisan::call('make:livewire', ['name' => 'existing-class', '--class' => true]);
        $this->assertTrue(File::exists($this->livewireClassesPath('ExistingClass.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('existing-class.blade.php')));

        // Now try to create an SFC with the same name
        // The system should recognize the existing class-based component
        // and not mistake its view for an SFC
        $exitCode = Artisan::call('make:livewire', ['name' => 'existing-class']);

        // It should fail because component already exists (as class-based)
        $this->assertEquals(1, $exitCode);

        // The SFC should NOT have been created
        $this->assertFalse(File::exists($this->livewireComponentsPath('⚡existing-class.blade.php')));
    }

    public function test_single_file_component_is_created_in_pages_namespace()
    {
        Artisan::call('make:livewire', ['name' => 'pages::create-post']);

        $this->assertTrue(File::exists(resource_path('views/pages/⚡create-post.blade.php')));
        $this->assertFalse(File::exists($this->livewireComponentsPath('⚡create-post.blade.php')));
    }

    public function test_nested_single_file_component_is_created_in_pages_namespace()
    {
        Artisan::call('make:livewire', ['name' => 'pages::blog.create-post']);

        $this->assertTrue(File::exists(resource_path('views/pages/blog/⚡create-post.blade.php')));
    }

    public function test_multi_file_component_is_created_in_pages_namespace()
    {
        Artisan::call('make:livewire', ['name' => 'pages::dashboard', '--mfc' => true]);

        $this->assertTrue(File::isDirectory(resource_path('views/pages/⚡dashboard')));
        $this->assertTrue(File::exists(resource_path('views/pages/⚡dashboard/dashboard.php')));
        $this->assertTrue(File::exists(resource_path('views/pages/⚡dashboard/dashboard.blade.php')));
    }

    public function test_class_component_is_created_in_namespace()
    {
        File::deleteDirectory(app_path('Foo'));
        File::deleteDirectory(resource_path('views/foo'));

        Livewire::addNamespace(
            namespace: 'foo',
            classNamespace: 'App\Foo',
            classPath: app_path('Foo'),
            classViewPath: resource_path('views/foo'),
        );
        
        Artisan::call('make:livewire', ['name' => 'foo::bar', '--class' => true]);

        $this->assertTrue(File::exists(resource_path('views/foo/bar.blade.php')));
        $this->assertTrue(File::exists(app_path('Foo/Bar.php')));

        $contents = File::get(app_path('Foo/Bar.php'));
        $this->assertStringContainsString('namespace App\Foo;', $contents);
        $this->assertStringContainsString('class Bar extends Component', $contents);
        $this->assertStringContainsString("view('foo.bar')", $contents);
    }

    public function test_component_is_created_in_layouts_namespace()
    {
        Artisan::call('make:livewire', ['name' => 'layouts::sidebar']);

        $this->assertTrue(File::exists(resource_path('views/layouts/⚡sidebar.blade.php')));
    }

    public function test_custom_namespace_from_config_works()
    {
        // Set the custom namespace in config
        $adminPath = resource_path('views/admin');
        $this->app['config']->set('livewire.component_namespaces.admin', $adminPath);

        // Register the namespace with all the necessary systems (mimicking what LivewireServiceProvider does)
        app('livewire.finder')->addNamespace('admin', viewPath: $adminPath);
        app('blade.compiler')->anonymousComponentPath($adminPath, 'admin');
        app('view')->addNamespace('admin', $adminPath);

        Artisan::call('make:livewire', ['name' => 'admin::users-table']);

        $this->assertTrue(File::exists($adminPath . '/⚡users-table.blade.php'));
    }

    public function test_namespace_works_without_emoji()
    {
        $this->app['config']->set('livewire.make_command.emoji', false);

        Artisan::call('make:livewire', ['name' => 'pages::settings']);

        $this->assertTrue(File::exists(resource_path('views/pages/settings.blade.php')));
        $this->assertFalse(File::exists(resource_path('views/pages/⚡settings.blade.php')));
    }

    public function test_namespace_with_deeply_nested_components()
    {
        Artisan::call('make:livewire', ['name' => 'pages::blog.posts.create-post']);

        $this->assertTrue(File::exists(resource_path('views/pages/blog/posts/⚡create-post.blade.php')));
    }

    public function test_single_file_component_with_test_flag_creates_test_file()
    {
        Artisan::call('make:livewire', ['name' => 'foo', '--test' => true]);

        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo.blade.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo.test.php')));

        $testContent = File::get($this->livewireComponentsPath('⚡foo.test.php'));
        $this->assertStringContainsString("it('renders successfully'", $testContent);
        $this->assertStringContainsString('foo', $testContent);
    }

    public function test_single_file_component_with_test_when_configured_in_make_command_with()
    {
        $this->app['config']->set('livewire.make_command.with.test', true);

        Artisan::call('make:livewire', ['name' => 'foo']);

        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo.blade.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo.test.php')));

        $testContent = File::get($this->livewireComponentsPath('⚡foo.test.php'));
        $this->assertStringContainsString("it('renders successfully'", $testContent);
        $this->assertStringContainsString('foo', $testContent);
    }

    public function test_single_file_component_with_test_flag_without_emoji()
    {
        $this->app['config']->set('livewire.make_command.emoji', false);

        Artisan::call('make:livewire', ['name' => 'bar', '--test' => true]);

        $this->assertTrue(File::exists($this->livewireComponentsPath('bar.blade.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('bar.test.php')));
        $this->assertFalse(File::exists($this->livewireComponentsPath('⚡bar.test.php')));
    }

    public function test_single_file_component_with_test_configured_without_emoji()
    {
        $this->app['config']->set('livewire.make_command.emoji', false);
        $this->app['config']->set('livewire.make_command.with.test', true);

        Artisan::call('make:livewire', ['name' => 'bar']);

        $this->assertTrue(File::exists($this->livewireComponentsPath('bar.blade.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('bar.test.php')));
        $this->assertFalse(File::exists($this->livewireComponentsPath('⚡bar.test.php')));
    }

    public function test_nested_single_file_component_with_test_flag()
    {
        Artisan::call('make:livewire', ['name' => 'admin.users', '--test' => true]);

        $this->assertTrue(File::exists($this->livewireComponentsPath('admin/⚡users.blade.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('admin/⚡users.test.php')));
    }

    public function test_nested_single_file_component_with_test_configured_in_make_command_with()
    {
        $this->app['config']->set('livewire.make_command.with.test', true);

        Artisan::call('make:livewire', ['name' => 'admin.users']);

        $this->assertTrue(File::exists($this->livewireComponentsPath('admin/⚡users.blade.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('admin/⚡users.test.php')));
    }

    public function test_multi_file_component_with_test_when_configured_in_make_command_with()
    {
        $this->app['config']->set('livewire.make_command.with.test', true);

        Artisan::call('make:livewire', ['name' => 'foo', '--mfc' => true]);

        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('⚡foo')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.blade.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.test.php')));
    }

    public function test_multi_file_component_with_css_when_css_flag_provided()
    {
        Artisan::call('make:livewire', ['name' => 'foo', '--mfc' => true, '--css' => true]);

        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('⚡foo')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.blade.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.css')));
    }

    public function test_multi_file_component_with_css_when_configured_in_make_command_with()
    {
        $this->app['config']->set('livewire.make_command.with.css', true);

        Artisan::call('make:livewire', ['name' => 'foo', '--mfc' => true]);

        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('⚡foo')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.blade.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.css')));
    }

    public function test_multi_file_component_without_css_by_default()
    {
        Artisan::call('make:livewire', ['name' => 'foo', '--mfc' => true]);

        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('⚡foo')));
        $this->assertFalse(File::exists($this->livewireComponentsPath('⚡foo/foo.css')));
    }

    public function test_multi_file_component_with_both_test_and_js_configured_in_make_command_with()
    {
        $this->app['config']->set('livewire.make_command.with.test', true);
        $this->app['config']->set('livewire.make_command.with.js', true);

        Artisan::call('make:livewire', ['name' => 'foo', '--mfc' => true]);

        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('⚡foo')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.blade.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.test.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.js')));
    }

    public function test_multi_file_component_with_all_options_configured_in_make_command_with()
    {
        $this->app['config']->set('livewire.make_command.with.test', true);
        $this->app['config']->set('livewire.make_command.with.js', true);
        $this->app['config']->set('livewire.make_command.with.css', true);

        Artisan::call('make:livewire', ['name' => 'foo', '--mfc' => true]);

        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('⚡foo')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.blade.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.test.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.js')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.css')));
    }

    public function test_converting_single_file_to_multi_file_preserves_test_file()
    {
        // Create SFC with test
        Artisan::call('make:livewire', ['name' => 'foo', '--test' => true]);
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo.blade.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo.test.php')));

        // Convert to MFC
        Artisan::call('livewire:convert', ['name' => 'foo', '--mfc' => true]);

        // Check that test file was moved into MFC directory
        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('⚡foo')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.test.php')));
        $this->assertFalse(File::exists($this->livewireComponentsPath('⚡foo.test.php')));

        $testContent = File::get($this->livewireComponentsPath('⚡foo/foo.test.php'));
        $this->assertStringContainsString("it('renders successfully'", $testContent);
    }

    public function test_converting_multi_file_to_single_file_preserves_test_file()
    {
        // Create MFC with test
        Artisan::call('make:livewire', ['name' => 'bar', '--mfc' => true, '--test' => true]);
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡bar/bar.test.php')));

        // Convert to SFC
        Artisan::call('livewire:convert', ['name' => 'bar', '--sfc' => true]);

        // Check that test file was moved out of MFC directory
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡bar.blade.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡bar.test.php')));
        $this->assertFalse(File::isDirectory($this->livewireComponentsPath('⚡bar')));

        $testContent = File::get($this->livewireComponentsPath('⚡bar.test.php'));
        $this->assertStringContainsString("it('renders successfully'", $testContent);
    }

    public function test_converting_sfc_to_mfc_without_test_flag_preserves_existing_test()
    {
        // Create SFC with test
        Artisan::call('make:livewire', ['name' => 'baz', '--test' => true]);

        // Convert to MFC without --test flag (should still preserve test)
        Artisan::call('livewire:convert', ['name' => 'baz', '--mfc' => true]);

        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡baz/baz.test.php')));
    }

    public function test_converting_sfc_to_mfc_with_test_flag_creates_test_when_none_exists()
    {
        // Create SFC without test
        Artisan::call('make:livewire', ['name' => 'qux']);
        $this->assertFalse(File::exists($this->livewireComponentsPath('⚡qux.test.php')));

        // Convert to MFC with --test flag
        Artisan::call('livewire:convert', ['name' => 'qux', '--mfc' => true, '--test' => true]);

        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡qux/qux.test.php')));
    }

    public function test_converting_mfc_to_sfc_without_test_file_works()
    {
        // Create MFC without test
        Artisan::call('make:livewire', ['name' => 'quux', '--mfc' => true]);
        $this->assertFalse(File::exists($this->livewireComponentsPath('⚡quux/quux.test.php')));

        // Convert to SFC
        Artisan::call('livewire:convert', ['name' => 'quux', '--sfc' => true]);

        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡quux.blade.php')));
        $this->assertFalse(File::exists($this->livewireComponentsPath('⚡quux.test.php')));
    }

    public function test_test_flag_creates_test_for_existing_single_file_component()
    {
        // Create SFC without test
        Artisan::call('make:livewire', ['name' => 'existing-sfc']);
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡existing-sfc.blade.php')));
        $this->assertFalse(File::exists($this->livewireComponentsPath('⚡existing-sfc.test.php')));

        // Create test for existing component
        $exitCode = Artisan::call('make:livewire', ['name' => 'existing-sfc', '--test' => true]);

        $this->assertEquals(0, $exitCode);
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡existing-sfc.test.php')));

        $testContent = File::get($this->livewireComponentsPath('⚡existing-sfc.test.php'));
        $this->assertStringContainsString("it('renders successfully'", $testContent);
        $this->assertStringContainsString('existing-sfc', $testContent);
    }

    public function test_test_flag_creates_test_for_existing_multi_file_component()
    {
        // Create MFC without test
        Artisan::call('make:livewire', ['name' => 'existing-mfc', '--mfc' => true]);
        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('⚡existing-mfc')));
        $this->assertFalse(File::exists($this->livewireComponentsPath('⚡existing-mfc/existing-mfc.test.php')));

        // Create test for existing component
        $exitCode = Artisan::call('make:livewire', ['name' => 'existing-mfc', '--test' => true]);

        $this->assertEquals(0, $exitCode);
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡existing-mfc/existing-mfc.test.php')));

        $testContent = File::get($this->livewireComponentsPath('⚡existing-mfc/existing-mfc.test.php'));
        $this->assertStringContainsString("it('renders successfully'", $testContent);
        $this->assertStringContainsString('existing-mfc', $testContent);
    }

    public function test_test_flag_creates_test_for_existing_class_based_component()
    {
        // Create class-based component
        Artisan::call('make:livewire', ['name' => 'existing-class', '--class' => true]);
        $this->assertTrue(File::exists($this->livewireClassesPath('ExistingClass.php')));
        $this->assertFalse(File::exists($this->livewireTestsPath('ExistingClassTest.php')));

        // Create test for existing component
        $exitCode = Artisan::call('make:livewire', ['name' => 'existing-class', '--test' => true]);

        $this->assertEquals(0, $exitCode);
        $this->assertTrue(File::exists($this->livewireTestsPath('ExistingClassTest.php')));

        $testContent = File::get($this->livewireTestsPath('ExistingClassTest.php'));
        $this->assertStringContainsString("it('renders successfully'", $testContent);
        $this->assertStringContainsString('existing-class', $testContent);
    }

    public function test_test_flag_for_existing_component_errors_when_test_already_exists()
    {
        // Create SFC with test
        Artisan::call('make:livewire', ['name' => 'with-test', '--test' => true]);
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡with-test.test.php')));

        // Try to create test again
        $exitCode = Artisan::call('make:livewire', ['name' => 'with-test', '--test' => true]);

        $this->assertEquals(1, $exitCode);
    }

    public function test_test_flag_for_nested_existing_component()
    {
        // Create nested SFC without test
        Artisan::call('make:livewire', ['name' => 'admin.settings']);
        $this->assertTrue(File::exists($this->livewireComponentsPath('admin/⚡settings.blade.php')));
        $this->assertFalse(File::exists($this->livewireComponentsPath('admin/⚡settings.test.php')));

        // Create test for existing component
        $exitCode = Artisan::call('make:livewire', ['name' => 'admin.settings', '--test' => true]);

        $this->assertEquals(0, $exitCode);
        $this->assertTrue(File::exists($this->livewireComponentsPath('admin/⚡settings.test.php')));

        $testContent = File::get($this->livewireComponentsPath('admin/⚡settings.test.php'));
        $this->assertStringContainsString('admin.settings', $testContent);
    }

    public function test_test_flag_for_nested_existing_class_based_component()
    {
        // Create nested class-based component
        Artisan::call('make:livewire', ['name' => 'admin.dashboard', '--class' => true]);
        $this->assertTrue(File::exists($this->livewireClassesPath('Admin/Dashboard.php')));
        $this->assertFalse(File::exists($this->livewireTestsPath('Admin/DashboardTest.php')));

        // Create test for existing component
        $exitCode = Artisan::call('make:livewire', ['name' => 'admin.dashboard', '--test' => true]);

        $this->assertEquals(0, $exitCode);
        $this->assertTrue(File::exists($this->livewireTestsPath('Admin/DashboardTest.php')));

        $testContent = File::get($this->livewireTestsPath('Admin/DashboardTest.php'));
        $this->assertStringContainsString('admin.dashboard', $testContent);
    }

    public function test_converting_sfc_with_style_tag_to_mfc_creates_css_file()
    {
        // Create SFC
        Artisan::call('make:livewire', ['name' => 'styled-component']);
        $sfcPath = $this->livewireComponentsPath('⚡styled-component.blade.php');

        // Add style tag to SFC
        $content = File::get($sfcPath);
        $content .= "\n\n<style>\n    .my-class {\n        color: red;\n    }\n</style>";
        File::put($sfcPath, $content);

        // Convert to MFC
        Artisan::call('livewire:convert', ['name' => 'styled-component', '--mfc' => true]);

        // Check that CSS file was created
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡styled-component/styled-component.css')));

        $cssContent = File::get($this->livewireComponentsPath('⚡styled-component/styled-component.css'));
        $this->assertStringContainsString('.my-class', $cssContent);
        $this->assertStringContainsString('color: red;', $cssContent);
    }

    public function test_converting_sfc_with_global_style_tag_to_mfc_creates_global_css_file()
    {
        // Create SFC
        Artisan::call('make:livewire', ['name' => 'global-styled']);
        $sfcPath = $this->livewireComponentsPath('⚡global-styled.blade.php');

        // Add global style tag to SFC
        $content = File::get($sfcPath);
        $content .= "\n\n<style global>\n    body {\n        background: blue;\n    }\n</style>";
        File::put($sfcPath, $content);

        // Convert to MFC
        Artisan::call('livewire:convert', ['name' => 'global-styled', '--mfc' => true]);

        // Check that global CSS file was created
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡global-styled/global-styled.global.css')));

        $cssContent = File::get($this->livewireComponentsPath('⚡global-styled/global-styled.global.css'));
        $this->assertStringContainsString('body', $cssContent);
        $this->assertStringContainsString('background: blue;', $cssContent);
    }

    public function test_converting_sfc_with_both_style_tags_to_mfc_creates_both_css_files()
    {
        // Create SFC
        Artisan::call('make:livewire', ['name' => 'both-styles']);
        $sfcPath = $this->livewireComponentsPath('⚡both-styles.blade.php');

        // Add both style tags to SFC
        $content = File::get($sfcPath);
        $content .= "\n\n<style>\n    .component {\n        padding: 1rem;\n    }\n</style>";
        $content .= "\n\n<style global>\n    * {\n        margin: 0;\n    }\n</style>";
        File::put($sfcPath, $content);

        // Convert to MFC
        Artisan::call('livewire:convert', ['name' => 'both-styles', '--mfc' => true]);

        // Check that both CSS files were created
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡both-styles/both-styles.css')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡both-styles/both-styles.global.css')));

        $cssContent = File::get($this->livewireComponentsPath('⚡both-styles/both-styles.css'));
        $this->assertStringContainsString('.component', $cssContent);

        $globalCssContent = File::get($this->livewireComponentsPath('⚡both-styles/both-styles.global.css'));
        $this->assertStringContainsString('margin: 0;', $globalCssContent);
    }

    public function test_converting_mfc_with_css_file_to_sfc_creates_style_tag()
    {
        // Create MFC
        Artisan::call('make:livewire', ['name' => 'mfc-styled', '--mfc' => true]);
        $mfcDir = $this->livewireComponentsPath('⚡mfc-styled');

        // Create CSS file
        File::put($mfcDir . '/mfc-styled.css', ".test {\n    font-size: 16px;\n}");

        // Convert to SFC
        Artisan::call('livewire:convert', ['name' => 'mfc-styled', '--sfc' => true]);

        // Check that style tag was added
        $sfcContent = File::get($this->livewireComponentsPath('⚡mfc-styled.blade.php'));
        $this->assertStringContainsString('<style>', $sfcContent);
        $this->assertStringContainsString('.test', $sfcContent);
        $this->assertStringContainsString('font-size: 16px;', $sfcContent);
        $this->assertStringContainsString('</style>', $sfcContent);
    }

    public function test_converting_mfc_with_global_css_file_to_sfc_creates_global_style_tag()
    {
        // Create MFC
        Artisan::call('make:livewire', ['name' => 'mfc-global', '--mfc' => true]);
        $mfcDir = $this->livewireComponentsPath('⚡mfc-global');

        // Create global CSS file
        File::put($mfcDir . '/mfc-global.global.css', "html {\n    box-sizing: border-box;\n}");

        // Convert to SFC
        Artisan::call('livewire:convert', ['name' => 'mfc-global', '--sfc' => true]);

        // Check that global style tag was added
        $sfcContent = File::get($this->livewireComponentsPath('⚡mfc-global.blade.php'));
        $this->assertStringContainsString('<style global>', $sfcContent);
        $this->assertStringContainsString('html', $sfcContent);
        $this->assertStringContainsString('box-sizing: border-box;', $sfcContent);
        $this->assertStringContainsString('</style>', $sfcContent);
    }

    public function test_converting_mfc_with_both_css_files_to_sfc_creates_both_style_tags()
    {
        // Create MFC
        Artisan::call('make:livewire', ['name' => 'mfc-both', '--mfc' => true]);
        $mfcDir = $this->livewireComponentsPath('⚡mfc-both');

        // Create both CSS files
        File::put($mfcDir . '/mfc-both.css', ".local {\n    display: flex;\n}");
        File::put($mfcDir . '/mfc-both.global.css', "body {\n    font-family: sans-serif;\n}");

        // Convert to SFC
        Artisan::call('livewire:convert', ['name' => 'mfc-both', '--sfc' => true]);

        // Check that both style tags were added
        $sfcContent = File::get($this->livewireComponentsPath('⚡mfc-both.blade.php'));

        $this->assertStringContainsString('<style>', $sfcContent);
        $this->assertStringContainsString('.local', $sfcContent);
        $this->assertStringContainsString('display: flex;', $sfcContent);

        $this->assertStringContainsString('<style global>', $sfcContent);
        $this->assertStringContainsString('body', $sfcContent);
        $this->assertStringContainsString('font-family: sans-serif;', $sfcContent);
    }

    public function test_round_trip_conversion_preserves_styles()
    {
        // Create SFC with styles
        Artisan::call('make:livewire', ['name' => 'round-trip']);
        $sfcPath = $this->livewireComponentsPath('⚡round-trip.blade.php');

        $originalContent = File::get($sfcPath);
        $originalContent .= "\n\n<style>\n    .original {\n        width: 100%;\n    }\n</style>";
        $originalContent .= "\n\n<style global>\n    .global-original {\n        height: 100%;\n    }\n</style>";
        File::put($sfcPath, $originalContent);

        // Convert to MFC
        Artisan::call('livewire:convert', ['name' => 'round-trip', '--mfc' => true]);

        // Verify MFC has CSS files
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡round-trip/round-trip.css')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡round-trip/round-trip.global.css')));

        // Convert back to SFC
        Artisan::call('livewire:convert', ['name' => 'round-trip', '--sfc' => true]);

        // Check that styles were preserved
        $finalContent = File::get($this->livewireComponentsPath('⚡round-trip.blade.php'));
        $this->assertStringContainsString('.original', $finalContent);
        $this->assertStringContainsString('width: 100%', $finalContent);
        $this->assertStringContainsString('.global-original', $finalContent);
        $this->assertStringContainsString('height: 100%', $finalContent);
    }

    public function test_unregistered_namespace_shows_error_for_sfc()
    {
        // Try to create a component with an unregistered namespace
        $exitCode = Artisan::call('make:livewire', ['name' => 'unregistered::foo']);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('Namespace [unregistered] is not registered', Artisan::output());

        // Ensure no component was created in the fallback location
        $this->assertFalse(File::exists($this->livewireComponentsPath('⚡foo.blade.php')));
    }

    public function test_unregistered_namespace_shows_error_for_mfc()
    {
        // Try to create a multi-file component with an unregistered namespace
        $exitCode = Artisan::call('make:livewire', ['name' => 'unknown::bar', '--mfc' => true]);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('Namespace [unknown] is not registered', Artisan::output());

        // Ensure no component was created in the fallback location
        $this->assertFalse(File::isDirectory($this->livewireComponentsPath('⚡bar')));
    }

    public function test_unregistered_namespace_shows_error_for_nested_component()
    {
        // Try to create a nested component with an unregistered namespace
        $exitCode = Artisan::call('make:livewire', ['name' => 'custom::admin.users']);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('Namespace [custom] is not registered', Artisan::output());

        // Ensure no component was created in the fallback location
        $this->assertFalse(File::exists($this->livewireComponentsPath('admin/⚡users.blade.php')));
    }

    public function test_class_based_component_uses_class_namespace_from_config()
    {
        $this->app['config']->set('livewire.class_namespace', 'App\\Components');
        $this->app[Kernel::class]->call('make:livewire', ['name' => 'foo', '--class' => true]);

        // File is still created in default path, but namespace in content uses config
        $classContent = File::get($this->livewireClassesPath('Foo.php'));

        $this->assertStringContainsString('namespace App\Components;', $classContent);
    }

    public function test_class_based_component_with_views_as_root_path_has_correct_view_name()
    {
        // Set view_path to root views folder (not a subdirectory like views/livewire)
        $this->app['config']->set('livewire.view_path', resource_path('views'));
        $this->app[Kernel::class]->call('make:livewire', ['name' => 'foo', '--class' => true]);

        $classContent = File::get($this->livewireClassesPath('Foo.php'));

        // Should be view('foo') not view('.foo') or an absolute path
        $this->assertStringContainsString("view('foo')", $classContent);
        $this->assertStringNotContainsString("view('.foo')", $classContent);

        // Ensure view is created in root views folder
        $this->assertTrue(File::exists(resource_path('views/foo.blade.php')));
    }

    public function test_class_based_nested_component_with_views_as_root_path_has_correct_view_name()
    {
        // Set view_path to root views folder
        $this->app['config']->set('livewire.view_path', resource_path('views'));
        $this->app[Kernel::class]->call('make:livewire', ['name' => 'admin.dashboard', '--class' => true]);

        $classContent = File::get($this->livewireClassesPath('Admin/Dashboard.php'));

        // Should be view('admin.dashboard') not view('.admin.dashboard')
        $this->assertStringContainsString("view('admin.dashboard')", $classContent);
        $this->assertStringNotContainsString("view('.admin.dashboard')", $classContent);

        // Ensure view is created in correct location
        $this->assertTrue(File::exists(resource_path('views/admin/dashboard.blade.php')));
    }
}
