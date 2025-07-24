<?php

namespace Livewire\V4\Registry;

use Livewire\V4\Registry\Exceptions\ViewNotFoundException;
use Illuminate\Support\Facades\File;

class ComponentViewPathResolverUnitTest extends \Tests\TestCase
{
    protected $resolver;
    protected $tempPath;
    protected $testViewPaths;

    public function setUp(): void
    {
        parent::setUp();

        $this->tempPath = sys_get_temp_dir() . '/livewire_test_' . uniqid();

        // Create temp directory structure for testing...
        File::makeDirectory($this->tempPath . '/components', 0755, true);
        File::makeDirectory($this->tempPath . '/livewire', 0755, true);
        File::makeDirectory($this->tempPath . '/custom', 0755, true);

        $this->testViewPaths = [
            $this->tempPath . '/components',
            $this->tempPath . '/livewire',
        ];

        $this->resolver = new ComponentViewPathResolver($this->testViewPaths);
    }

    protected function tearDown(): void
    {
        // Clean up temp files...
        if (File::exists($this->tempPath)) {
            File::deleteDirectory($this->tempPath);
        }

        parent::tearDown();
    }

    public function test_can_register_and_resolve_aliased_component()
    {
        $viewPath = $this->tempPath . '/custom-view.livewire.php';
        File::put($viewPath, '<div>Custom View</div>');

        $this->resolver->component('custom-component', $viewPath);

        $resolved = $this->resolver->resolve('custom-component');

        $this->assertEquals($viewPath, $resolved);
    }

    public function test_throws_exception_when_aliased_component_file_does_not_exist()
    {
        $this->expectException(ViewNotFoundException::class);
        $this->expectExceptionMessage('Component view file not found: [/non-existent-path.livewire.php]');

        $this->resolver->component('custom-component', '/non-existent-path.livewire.php');
        $this->resolver->resolve('custom-component');
    }

    public function test_can_register_and_resolve_namespaced_component()
    {
        $viewPath = $this->tempPath . '/custom/some-component.livewire.php';
        File::put($viewPath, '<div>Namespaced View</div>');

        $this->resolver->namespace('admin', $this->tempPath . '/custom');

        $resolved = $this->resolver->resolve('admin::some-component');

        $this->assertEquals($viewPath, $resolved);
    }

    public function test_throws_exception_when_namespace_is_not_registered()
    {
        $this->expectException(ViewNotFoundException::class);
        $this->expectExceptionMessage('Namespace [admin] is not registered');

        $this->resolver->resolve('admin::some-component');
    }

    public function test_resolves_component_using_first_convention_direct_file()
    {
        $viewPath = $this->tempPath . '/components/some-component.livewire.php';
        File::put($viewPath, '<div>Direct File</div>');

        $resolved = $this->resolver->resolve('some-component');

        $this->assertEquals($viewPath, $resolved);
    }

    public function test_resolves_component_using_second_convention_folder_with_same_name()
    {
        File::makeDirectory($this->tempPath . '/components/some-component', 0755, true);
        $viewPath = $this->tempPath . '/components/some-component/some-component.livewire.php';
        File::put($viewPath, '<div>Folder Same Name</div>');

        $resolved = $this->resolver->resolve('some-component');

        $this->assertEquals($viewPath, $resolved);
    }

    public function test_resolves_component_using_third_convention_index_file()
    {
        File::makeDirectory($this->tempPath . '/components/some-component', 0755, true);
        $viewPath = $this->tempPath . '/components/some-component/index.livewire.php';
        File::put($viewPath, '<div>Index File</div>');

        $resolved = $this->resolver->resolve('some-component');

        $this->assertEquals($viewPath, $resolved);
    }

    public function test_resolves_nested_component_with_dots()
    {
        File::makeDirectory($this->tempPath . '/components/foo', 0755, true);
        $viewPath = $this->tempPath . '/components/foo/bar.livewire.php';
        File::put($viewPath, '<div>Nested Component</div>');

        $resolved = $this->resolver->resolve('foo.bar');

        $this->assertEquals($viewPath, $resolved);
    }

    public function test_falls_back_to_livewire_directory_when_not_found_in_components()
    {
        $viewPath = $this->tempPath . '/livewire/some-component.livewire.php';
        File::put($viewPath, '<div>Livewire Directory</div>');

        $resolved = $this->resolver->resolve('some-component');

        $this->assertEquals($viewPath, $resolved);
    }

    public function test_throws_exception_when_component_cannot_be_found_in_any_directory()
    {
        $this->expectException(ViewNotFoundException::class);
        $this->expectExceptionMessage('Unable to find component view: [non-existent-component]');

        $this->resolver->resolve('non-existent-component');
    }

    public function test_namespace_directory_path_is_normalized()
    {
        $viewPath = $this->tempPath . '/custom/some-component.livewire.php';
        File::put($viewPath, '<div>Normalized Path</div>');

        // Register with trailing slash...
        $this->resolver->namespace('admin', $this->tempPath . '/custom/');

        $resolved = $this->resolver->resolve('admin::some-component');

        $this->assertEquals($viewPath, $resolved);
    }

    public function test_prioritizes_aliases_over_default_resolution()
    {
        // Create both a default file and an aliased file...
        $defaultPath = $this->tempPath . '/components/some-component.livewire.php';
        File::put($defaultPath, '<div>Default</div>');

        $aliasedPath = $this->tempPath . '/aliased-component.livewire.php';
        File::put($aliasedPath, '<div>Aliased</div>');

        $this->resolver->component('some-component', $aliasedPath);

        $resolved = $this->resolver->resolve('some-component');

        // Should resolve to the aliased path, not the default one...
        $this->assertEquals($aliasedPath, $resolved);
    }

    public function test_resolves_multi_file_component_directory()
    {
        // Create a multi-file component directory with both .php and .blade.php files
        File::makeDirectory($this->tempPath . '/components/multi-component', 0755, true);

        $livewireFile = $this->tempPath . '/components/multi-component/multi-component.php';
        $bladeFile = $this->tempPath . '/components/multi-component/multi-component.blade.php';

        File::put($livewireFile, 'new class extends Livewire\Component { public $count = 0; }');
        File::put($bladeFile, '<div>Count: {{ $count }}</div>');

        $resolved = $this->resolver->resolve('multi-component');

        // Should resolve to the directory path for multi-file components
        $this->assertEquals($this->tempPath . '/components/multi-component', $resolved);
    }
}