<?php

namespace Livewire\Finder;

use Livewire\Finder\Fixtures\Nested\NestedComponent;
use Livewire\Finder\Fixtures\SelfNamedComponent\SelfNamedComponent;
use Livewire\Finder\Fixtures\IndexComponent\Index;
use Livewire\Finder\Fixtures\FinderTestClassComponent;
use Livewire\Component;

class UnitTest extends \Tests\TestCase
{
    public function test_can_add_and_resolve_named_class_component()
    {
        $finder = new Finder();

        $finder->addComponent('test-component', FinderTestComponent::class);

        $name = $finder->normalizeName(FinderTestComponent::class);

        $this->assertEquals('test-component', $name);

        $class = $finder->resolveClassName('test-component');

        $this->assertEquals(FinderTestComponent::class, $class);
    }

    public function test_can_add_and_resolve_unnamed_class_component()
    {
        $finder = new Finder();

        $finder->addComponent(className: FinderTestComponent::class);

        $name = $finder->normalizeName(FinderTestComponent::class);

        $this->assertEquals(crc32(FinderTestComponent::class), $name);

        $class = $finder->resolveClassName($name);

        $this->assertEquals(FinderTestComponent::class, $class);
    }

    public function test_can_add_and_resolve_location_class_component()
    {
        $finder = new Finder();

        $finder->addLocation(classNamespace: 'Livewire\Finder\Fixtures');

        $name = $finder->normalizeName(FinderTestClassComponent::class);

        $this->assertEquals('finder-test-class-component', $name);

        $class = $finder->resolveClassName($name);

        $this->assertEquals('\Livewire\Finder\Fixtures\FinderTestClassComponent', $class);
    }

    public function test_can_add_and_resolve_location_class_nested_component()
    {
        $finder = new Finder();

        $finder->addLocation(classNamespace: 'Livewire\Finder\Fixtures');

        $name = $finder->normalizeName(NestedComponent::class);

        $this->assertEquals('nested.nested-component', $name);

        $class = $finder->resolveClassName($name);

        $this->assertEquals('\Livewire\Finder\Fixtures\Nested\NestedComponent', $class);
    }

    public function test_can_add_and_resolve_location_class_index_component()
    {
        $finder = new Finder();

        $finder->addLocation(classNamespace: 'Livewire\Finder\Fixtures');

        $name = $finder->normalizeName(Index::class);

        $this->assertEquals('index-component', $name);

        $class = $finder->resolveClassName($name);

        $this->assertEquals('\Livewire\Finder\Fixtures\IndexComponent\Index', $class);
    }

    public function test_can_add_and_resolve_location_class_self_named_component()
    {
        $finder = new Finder();

        $finder->addLocation(classNamespace: 'Livewire\Finder\Fixtures');

        $name = $finder->normalizeName(SelfNamedComponent::class);

        $this->assertEquals('self-named-component', $name);

        $class = $finder->resolveClassName($name);

        $this->assertEquals('\Livewire\Finder\Fixtures\SelfNamedComponent\SelfNamedComponent', $class);
    }

    public function test_can_add_and_resolve_named_view_based_component()
    {
        $finder = new Finder();

        $finder->addComponent('test-component', viewPath: __DIR__ . '/fixtures/finder-test-single-file-component.blade.php');

        $name = $finder->normalizeName('test-component');

        $this->assertEquals('test-component', $name);

        $path = $finder->resolveSingleFileComponentPath('test-component');

        $this->assertEquals(__DIR__ . '/fixtures/finder-test-single-file-component.blade.php', $path);
    }

    public function test_can_add_and_resolve_named_view_based_component_with_zap()
    {
        $finder = new Finder();

        $finder->addComponent('test-component-with-zap', viewPath: __DIR__ . '/fixtures/⚡︎finder-test-single-file-component-with-zap.blade.php');

        $name = $finder->normalizeName('test-component-with-zap');

        $this->assertEquals('test-component-with-zap', $name);

        $path = $finder->resolveSingleFileComponentPath('test-component-with-zap');

        $this->assertEquals(__DIR__ . '/fixtures/⚡︎finder-test-single-file-component-with-zap.blade.php', $path);
    }

    public function test_can_add_and_resolve_named_view_based_directory_component()
    {
        $finder = new Finder();

        $finder->addLocation(viewPath: __DIR__ . '/fixtures');

        $name = $finder->normalizeName('finder-test-single-file-component');

        $this->assertEquals('finder-test-single-file-component', $name);

        $path = $finder->resolveSingleFileComponentPath('finder-test-single-file-component');

        $this->assertEquals(__DIR__ . '/fixtures/finder-test-single-file-component.blade.php', $path);
    }

    public function test_can_add_and_resolve_named_view_based_directory_component_with_zap()
    {
        $finder = new Finder();

        $finder->addComponent('test-component-with-zap', viewPath: __DIR__ . '/fixtures/⚡︎finder-test-single-file-component-with-zap.blade.php');

        $name = $finder->normalizeName('test-component-with-zap');

        $this->assertEquals('test-component-with-zap', $name);

        $path = $finder->resolveSingleFileComponentPath('test-component-with-zap');

        $this->assertEquals(__DIR__ . '/fixtures/⚡︎finder-test-single-file-component-with-zap.blade.php', $path);
    }

    public function test_can_add_and_resolve_index_single_file_component()
    {
        $finder = new Finder();

        $finder->addLocation(viewPath: __DIR__ . '/fixtures');

        $name = $finder->normalizeName('nested-view-based');

        $this->assertEquals('nested-view-based', $name);

        $path = $finder->resolveSingleFileComponentPath('nested-view-based');

        $this->assertEquals(__DIR__ . '/fixtures/nested-view-based/index.blade.php', $path);
    }

    public function test_can_add_and_resolve_self_named_single_file_component()
    {
        $finder = new Finder();

        $finder->addLocation(viewPath: __DIR__ . '/fixtures');

        $name = $finder->normalizeName('self-named-component');

        $this->assertEquals('self-named-component', $name);

        $path = $finder->resolveSingleFileComponentPath('self-named-component');

        $this->assertEquals(__DIR__ . '/fixtures/self-named-component/self-named-component.blade.php', $path);
    }

    public function test_can_add_and_resolve_named_multi_file_component()
    {
        $finder = new Finder();

        $finder->addComponent('multi-file-test', viewPath: __DIR__ . '/fixtures/multi-file-test-component');

        $name = $finder->normalizeName('multi-file-test');

        $this->assertEquals('multi-file-test', $name);

        $path = $finder->resolveMultiFileComponentPath('multi-file-test');

        $this->assertEquals(__DIR__ . '/fixtures/multi-file-test-component', $path);
    }

    public function test_can_add_and_resolve_named_multi_file_component_with_zap()
    {
        $finder = new Finder();

        $finder->addComponent('multi-file-zap', viewPath: __DIR__ . '/fixtures/⚡︎multi-file-zap-component');

        $name = $finder->normalizeName('multi-file-zap');

        $this->assertEquals('multi-file-zap', $name);

        $path = $finder->resolveMultiFileComponentPath('multi-file-zap');

        $this->assertEquals(__DIR__ . '/fixtures/⚡︎multi-file-zap-component', $path);
    }

    public function test_can_add_and_resolve_location_multi_file_component()
    {
        $finder = new Finder();

        $finder->addLocation(viewPath: __DIR__ . '/fixtures');

        $name = $finder->normalizeName('multi-file-test-component');

        $this->assertEquals('multi-file-test-component', $name);

        $path = $finder->resolveMultiFileComponentPath('multi-file-test-component');

        $this->assertEquals(__DIR__ . '/fixtures/multi-file-test-component', $path);
    }

    public function test_can_add_and_resolve_location_multi_file_index_component()
    {
        $finder = new Finder();

        $finder->addLocation(viewPath: __DIR__ . '/fixtures');

        $name = $finder->normalizeName('multi-file-index');

        $this->assertEquals('multi-file-index', $name);

        $path = $finder->resolveMultiFileComponentPath('multi-file-index');

        $this->assertEquals(__DIR__ . '/fixtures/multi-file-index', $path);
    }

    public function test_can_add_and_resolve_location_multi_file_self_named_component()
    {
        $finder = new Finder();

        $finder->addLocation(viewPath: __DIR__ . '/fixtures');

        $name = $finder->normalizeName('multi-file-self-named');

        $this->assertEquals('multi-file-self-named', $name);

        $path = $finder->resolveMultiFileComponentPath('multi-file-self-named');

        $this->assertEquals(__DIR__ . '/fixtures/multi-file-self-named', $path);
    }

    public function test_can_add_and_resolve_location_multi_file_component_with_zap()
    {
        $finder = new Finder();

        $finder->addLocation(viewPath: __DIR__ . '/fixtures');

        $name = $finder->normalizeName('multi-file-zap-component');

        $this->assertEquals('multi-file-zap-component', $name);

        $path = $finder->resolveMultiFileComponentPath('multi-file-zap-component');

        $this->assertEquals(__DIR__ . '/fixtures/⚡︎multi-file-zap-component', $path);
    }

    public function test_memoization_caches_results_within_request()
    {
        $finder = new Finder();

        $finder->addLocation(viewPath: __DIR__ . '/fixtures');

        // First call - should populate cache
        $name1 = $finder->normalizeName('finder-test-single-file-component');
        $class1 = $finder->resolveClassName('finder-test-single-file-component');
        $singlePath1 = $finder->resolveSingleFileComponentPath('finder-test-single-file-component');
        $multiPath1 = $finder->resolveMultiFileComponentPath('multi-file-test-component');

        // Second call - should use cache (results should be identical)
        $name2 = $finder->normalizeName('finder-test-single-file-component');
        $class2 = $finder->resolveClassName('finder-test-single-file-component');
        $singlePath2 = $finder->resolveSingleFileComponentPath('finder-test-single-file-component');
        $multiPath2 = $finder->resolveMultiFileComponentPath('multi-file-test-component');

        // Results should be identical, proving memoization is working
        $this->assertEquals($name1, $name2);
        $this->assertEquals($class1, $class2);
        $this->assertEquals($singlePath1, $singlePath2);
        $this->assertEquals($multiPath1, $multiPath2);

        // Test that null results are also cached
        $nonExistent1 = $finder->resolveClassName('non-existent-component');
        $nonExistent2 = $finder->resolveClassName('non-existent-component');
        $this->assertEquals($nonExistent1, $nonExistent2);
        $this->assertNull($nonExistent1);
    }
}

class FinderTestComponent extends Component
{
    public function render()
    {
        return '<div>Test Component</div>';
    }
}
