<?php

namespace Livewire\Finder;

use Livewire\Finder\Fixtures\SelfNamedComponent\SelfNamedComponent;
use Livewire\Finder\Fixtures\NestedSelfNamed\SelfNamedViewComponent\SelfNamedViewComponent;
use Livewire\Finder\Fixtures\NestedIndex\IndexViewComponent\Index as IndexViewComponent;
use Livewire\Finder\Fixtures\Nested\NestedComponent;
use Livewire\Finder\Fixtures\IndexComponent\Index;
use Livewire\Finder\Fixtures\FinderTestClassComponent;
use Livewire\Component;
use Livewire\Livewire;

class UnitTest extends \Tests\TestCase
{
    public function test_can_add_and_resolve_component_named_class()
    {
        $finder = new Finder();

        $finder->addComponent('test-component', class: FinderTestClassComponent::class);

        $name = $finder->normalizeName(FinderTestClassComponent::class);

        $this->assertEquals('test-component', $name);

        $class = $finder->resolveClassComponentClassName('test-component');

        $this->assertEquals(FinderTestClassComponent::class, $class);
    }

    public function test_can_add_and_resolve_component_unnamed_class()
    {
        $finder = new Finder();

        $finder->addComponent(class: FinderTestClassComponent::class);

        $name = $finder->normalizeName(FinderTestClassComponent::class);

        $this->assertEquals('lw' . crc32(FinderTestClassComponent::class), $name);

        $class = $finder->resolveClassComponentClassName($name);

        $this->assertEquals(FinderTestClassComponent::class, $class);
    }

    public function test_can_add_and_resolve_component_named_anonymous_class()
    {
        $finder = new Finder();

        $finder->addComponent('test-component', class: $obj = new class extends Component {
            public function render() {
                return '<div>Finder Location Test Component</div>';
            }
        });

        $name = $finder->normalizeName($obj);

        $this->assertEquals('test-component', $name);

        $class = $finder->resolveClassComponentClassName('test-component');

        $this->assertEquals($obj::class, $class);
    }

    public function test_can_add_and_resolve_component_unnamed_anonymous_class()
    {
        $finder = new Finder();

        $finder->addComponent(class: $obj = new class extends Component {
            public function render() {
                return '<div>Finder Location Test Component</div>';
            }
        });

        $name = $finder->normalizeName($obj);

        $this->assertEquals('lw' . crc32($obj::class), $name);

        $class = $finder->resolveClassComponentClassName($name);

        $this->assertEquals($obj::class, $class);
    }

    public function test_can_add_and_resolve_component_named_view_based()
    {
        $finder = new Finder();

        $finder->addComponent('test-component', viewPath: __DIR__ . '/Fixtures/finder-test-single-file-component.blade.php');

        $name = $finder->normalizeName('test-component');

        $this->assertEquals('test-component', $name);

        $path = $finder->resolveSingleFileComponentPath('test-component');

        $this->assertEquals(__DIR__ . '/Fixtures/finder-test-single-file-component.blade.php', $path);
    }

    public function test_can_add_and_resolve_component_named_view_based_with_zap()
    {
        $finder = new Finder();

        $finder->addComponent('test-component-with-zap', viewPath: __DIR__ . '/Fixtures/⚡︎finder-test-single-file-component-with-zap.blade.php');

        $name = $finder->normalizeName('test-component-with-zap');

        $this->assertEquals('test-component-with-zap', $name);

        $path = $finder->resolveSingleFileComponentPath('test-component-with-zap');

        $this->assertEquals(__DIR__ . '/Fixtures/⚡︎finder-test-single-file-component-with-zap.blade.php', $path);
    }

    public function test_can_add_and_resolve_component_named_multi_file()
    {
        $finder = new Finder();

        $finder->addComponent('multi-file-test', viewPath: __DIR__ . '/Fixtures/multi-file-test-component');

        $name = $finder->normalizeName('multi-file-test');

        $this->assertEquals('multi-file-test', $name);

        $path = $finder->resolveMultiFileComponentPath('multi-file-test');

        $this->assertEquals(__DIR__ . '/Fixtures/multi-file-test-component', $path);
    }

    public function test_can_add_and_resolve_component_named_multi_file_with_zap()
    {
        $finder = new Finder();

        $finder->addComponent('multi-file-zap', viewPath: __DIR__ . '/Fixtures/⚡︎multi-file-zap-component');

        $name = $finder->normalizeName('multi-file-zap');

        $this->assertEquals('multi-file-zap', $name);

        $path = $finder->resolveMultiFileComponentPath('multi-file-zap');

        $this->assertEquals(__DIR__ . '/Fixtures/⚡︎multi-file-zap-component', $path);
    }

    public function test_can_resolve_location_class_component()
    {
        $finder = new Finder();

        $finder->addLocation(classNamespace: 'Livewire\Finder\Fixtures');

        $name = $finder->normalizeName(FinderTestClassComponent::class);

        $this->assertEquals('finder-test-class-component', $name);

        $class = $finder->resolveClassComponentClassName($name);

        $this->assertEquals('Livewire\Finder\Fixtures\FinderTestClassComponent', $class);
    }

    public function test_can_resolve_location_class_nested_component()
    {
        $finder = new Finder();

        $finder->addLocation(classNamespace: 'Livewire\Finder\Fixtures');

        $name = $finder->normalizeName(NestedComponent::class);

        $this->assertEquals('nested.nested-component', $name);

        $class = $finder->resolveClassComponentClassName($name);

        $this->assertEquals('Livewire\Finder\Fixtures\Nested\NestedComponent', $class);
    }

    public function test_can_resolve_location_class_index_component()
    {
        $finder = new Finder();

        $finder->addLocation(classNamespace: 'Livewire\Finder\Fixtures');

        $name = $finder->normalizeName(Index::class);

        $this->assertEquals('index-component', $name);

        $class = $finder->resolveClassComponentClassName($name);

        $this->assertEquals('Livewire\Finder\Fixtures\IndexComponent\Index', $class);
    }

    public function test_can_resolve_location_class_self_named_component()
    {
        $finder = new Finder();

        $finder->addLocation(classNamespace: 'Livewire\Finder\Fixtures');

        $name = $finder->normalizeName(SelfNamedComponent::class);

        // Self-named class components keep their full name (matching v3 behavior)...
        $this->assertEquals('self-named-component.self-named-component', $name);

        $class = $finder->resolveClassComponentClassName($name);

        $this->assertEquals('Livewire\Finder\Fixtures\SelfNamedComponent\SelfNamedComponent', $class);
    }

    public function test_can_resolve_location_single_file_component()
    {
        $finder = new Finder();

        $finder->addLocation(viewPath: __DIR__ . '/Fixtures');

        $name = $finder->normalizeName('finder-test-single-file-component');

        $this->assertEquals('finder-test-single-file-component', $name);

        $path = $finder->resolveSingleFileComponentPath('finder-test-single-file-component');

        $this->assertEquals(__DIR__ . '/Fixtures/finder-test-single-file-component.blade.php', $path);
    }

    public function test_can_resolve_location_single_file_component_with_zap()
    {
        $finder = new Finder();

        $finder->addLocation(viewPath: __DIR__ . '/Fixtures');

        $name = $finder->normalizeName('finder-test-single-file-component-with-zap');

        $this->assertEquals('finder-test-single-file-component-with-zap', $name);

        $path = $finder->resolveSingleFileComponentPath('finder-test-single-file-component-with-zap');

        $this->assertEquals(__DIR__ . '/Fixtures/⚡finder-test-single-file-component-with-zap.blade.php', $path);
    }

    public function test_can_resolve_location_index_single_file_component()
    {
        $finder = new Finder();

        $finder->addLocation(viewPath: __DIR__ . '/Fixtures');

        $name = $finder->normalizeName('nested-view-based');

        $this->assertEquals('nested-view-based', $name);

        $path = $finder->resolveSingleFileComponentPath('nested-view-based');

        $this->assertEquals(__DIR__ . '/Fixtures/nested-view-based/index.blade.php', $path);
    }

    public function test_can_resolve_location_self_named_single_file_component()
    {
        $finder = new Finder();

        $finder->addLocation(viewPath: __DIR__ . '/Fixtures');

        $name = $finder->normalizeName('self-named-component');

        $this->assertEquals('self-named-component', $name);

        $path = $finder->resolveSingleFileComponentPath('self-named-component');

        $this->assertEquals(__DIR__ . '/Fixtures/self-named-component/self-named-component.blade.php', $path);
    }

    public function test_can_resolve_location_nested_index_single_file_component()
    {
        $finder = new Finder();

        $finder->addLocation(viewPath: __DIR__ . '/Fixtures');

        $name = $finder->normalizeName('nested-view-based.index-named-component');

        $this->assertEquals('nested-view-based.index-named-component', $name);

        $path = $finder->resolveSingleFileComponentPath('nested-view-based.index-named-component');

        $this->assertEquals(__DIR__ . '/Fixtures/nested-view-based/index-named-component/index.blade.php', $path);
    }

    public function test_can_resolve_location_nested_self_named_single_file_component()
    {
        $finder = new Finder();

        $finder->addLocation(viewPath: __DIR__ . '/Fixtures');

        $name = $finder->normalizeName('nested-view-based.self-named-component');

        $this->assertEquals('nested-view-based.self-named-component', $name);

        $path = $finder->resolveSingleFileComponentPath('nested-view-based.self-named-component');

        $this->assertEquals(__DIR__ . '/Fixtures/nested-view-based/self-named-component/self-named-component.blade.php', $path);
    }

    public function test_can_resolve_location_multi_file_component()
    {
        $finder = new Finder();

        $finder->addLocation(viewPath: __DIR__ . '/Fixtures');

        $name = $finder->normalizeName('multi-file-test-component');

        $this->assertEquals('multi-file-test-component', $name);

        $path = $finder->resolveMultiFileComponentPath('multi-file-test-component');

        $this->assertEquals(__DIR__ . '/Fixtures/multi-file-test-component', $path);
    }

    public function test_can_resolve_location_multi_file_index_component()
    {
        $finder = new Finder();

        $finder->addLocation(viewPath: __DIR__ . '/Fixtures');

        $name = $finder->normalizeName('multi-file-index');

        $this->assertEquals('multi-file-index', $name);

        $path = $finder->resolveMultiFileComponentPath('multi-file-index');

        $this->assertEquals(__DIR__ . '/Fixtures/multi-file-index', $path);
    }

    public function test_can_resolve_location_multi_file_self_named_component()
    {
        $finder = new Finder();

        $finder->addLocation(viewPath: __DIR__ . '/Fixtures');

        $name = $finder->normalizeName('multi-file-self-named');

        $this->assertEquals('multi-file-self-named', $name);

        $path = $finder->resolveMultiFileComponentPath('multi-file-self-named');

        $this->assertEquals(__DIR__ . '/Fixtures/multi-file-self-named', $path);
    }

    public function test_can_resolve_location_multi_file_component_with_zap()
    {
        $finder = new Finder();

        $finder->addLocation(viewPath: __DIR__ . '/Fixtures');

        $name = $finder->normalizeName('multi-file-zap-component');

        $this->assertEquals('multi-file-zap-component', $name);

        $path = $finder->resolveMultiFileComponentPath('multi-file-zap-component');

        $this->assertEquals(__DIR__ . '/Fixtures/⚡multi-file-zap-component', $path);
    }

    public function test_it_does_not_resolve_a_multi_file_component_for_a_nested_single_file_self_named_component()
    {
        $finder = new Finder();

        $finder->addLocation(viewPath: __DIR__ . '/Fixtures');

        $name = $finder->normalizeName('nested-view-based.self-named-component');

        $this->assertEquals('nested-view-based.self-named-component', $name);

        $path = $finder->resolveMultiFileComponentPath('nested-view-based.self-named-component');

        $this->assertEquals('', $path);
    }

    public function test_can_resolve_namespace_class_component()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', classNamespace: 'Livewire\Finder\Fixtures');

        $class = $finder->resolveClassComponentClassName('admin::finder-test-class-component');

        $this->assertEquals('Livewire\Finder\Fixtures\FinderTestClassComponent', $class);
    }

    public function test_can_resolve_namespace_class_nested_component()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', classNamespace: 'Livewire\Finder\Fixtures');

        $class = $finder->resolveClassComponentClassName('admin::nested.nested-component');

        $this->assertEquals('Livewire\Finder\Fixtures\Nested\NestedComponent', $class);
    }

    public function test_can_resolve_namespace_class_index_component()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', classNamespace: 'Livewire\Finder\Fixtures');

        $class = $finder->resolveClassComponentClassName('admin::index-component');

        $this->assertEquals('Livewire\Finder\Fixtures\IndexComponent\Index', $class);
    }

    public function test_can_resolve_namespace_class_self_named_component()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', classNamespace: 'Livewire\Finder\Fixtures');

        $class = $finder->resolveClassComponentClassName('admin::self-named-component');

        $this->assertEquals('Livewire\Finder\Fixtures\SelfNamedComponent\SelfNamedComponent', $class);
    }

    public function test_returns_null_for_namespace_unknown_component()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', classNamespace: 'Livewire\Finder\Fixtures');

        $class = $finder->resolveClassComponentClassName('unknown::some-component');

        $this->assertNull($class);
    }

    public function test_can_resolve_namespace_single_file_component()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', viewPath: __DIR__ . '/Fixtures');

        $path = $finder->resolveSingleFileComponentPath('admin::finder-test-single-file-component');

        $this->assertEquals(__DIR__ . '/Fixtures/finder-test-single-file-component.blade.php', $path);
    }

    public function test_can_resolve_namespace_single_file_component_with_zap()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', viewPath: __DIR__ . '/Fixtures');

        $path = $finder->resolveSingleFileComponentPath('admin::finder-test-single-file-component-with-zap');

        $this->assertEquals(__DIR__ . '/Fixtures/⚡finder-test-single-file-component-with-zap.blade.php', $path);
    }

    public function test_can_resolve_namespace_index_single_file_component()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', viewPath: __DIR__ . '/Fixtures');

        $path = $finder->resolveSingleFileComponentPath('admin::nested-view-based');

        $this->assertEquals(__DIR__ . '/Fixtures/nested-view-based/index.blade.php', $path);
    }

    public function test_can_resolve_namespace_self_named_single_file_component()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', viewPath: __DIR__ . '/Fixtures');

        $path = $finder->resolveSingleFileComponentPath('admin::self-named-component');

        $this->assertEquals(__DIR__ . '/Fixtures/self-named-component/self-named-component.blade.php', $path);
    }

    public function test_can_resolve_namespace_nested_index_single_file_component()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', viewPath: __DIR__ . '/Fixtures');

        $path = $finder->resolveSingleFileComponentPath('admin::nested-view-based.index-named-component');

        $this->assertEquals(__DIR__ . '/Fixtures/nested-view-based/index-named-component/index.blade.php', $path);
    }

    public function test_can_resolve_namespace_nested_self_named_single_file_component()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', viewPath: __DIR__ . '/Fixtures');

        $path = $finder->resolveSingleFileComponentPath('admin::nested-view-based.self-named-component');

        $this->assertEquals(__DIR__ . '/Fixtures/nested-view-based/self-named-component/self-named-component.blade.php', $path);
    }

    public function test_returns_null_for_namespace_unknown_single_file_component()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', viewPath: __DIR__ . '/Fixtures');

        $path = $finder->resolveSingleFileComponentPath('unknown::some-component');

        $this->assertNull($path);
    }

    public function test_can_resolve_namespace_multi_file_component()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', viewPath: __DIR__ . '/Fixtures');

        $path = $finder->resolveMultiFileComponentPath('admin::multi-file-test-component');

        $this->assertEquals(__DIR__ . '/Fixtures/multi-file-test-component', $path);
    }

    public function test_can_resolve_namespace_multi_file_component_with_zap()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', viewPath: __DIR__ . '/Fixtures');

        $path = $finder->resolveMultiFileComponentPath('admin::multi-file-zap-component');

        $this->assertEquals(__DIR__ . '/Fixtures/⚡multi-file-zap-component', $path);
    }

    public function test_can_resolve_namespace_multi_file_index_component()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', viewPath: __DIR__ . '/Fixtures');

        $path = $finder->resolveMultiFileComponentPath('admin::multi-file-index');

        $this->assertEquals(__DIR__ . '/Fixtures/multi-file-index', $path);
    }

    public function test_can_resolve_namespace_multi_file_self_named_component()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', viewPath: __DIR__ . '/Fixtures');

        $path = $finder->resolveMultiFileComponentPath('admin::multi-file-self-named');

        $this->assertEquals(__DIR__ . '/Fixtures/multi-file-self-named', $path);
    }

    public function test_returns_null_for_namespace_unknown_multi_file_component()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', viewPath: __DIR__ . '/Fixtures');

        $path = $finder->resolveMultiFileComponentPath('unknown::some-component');

        $this->assertNull($path);
    }

    public function test_returns_null_for_unregistered_namespace_sfc_creation()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', viewPath: __DIR__ . '/Fixtures');

        // Try to resolve a path for creation with an unregistered namespace
        $path = $finder->resolveSingleFileComponentPathForCreation('unknown::some-component');

        $this->assertNull($path);
    }

    public function test_returns_null_for_unregistered_namespace_mfc_creation()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', viewPath: __DIR__ . '/Fixtures');

        // Try to resolve a path for creation with an unregistered namespace
        $path = $finder->resolveMultiFileComponentPathForCreation('unknown::some-component');

        $this->assertNull($path);
    }

    public function test_resolves_path_for_registered_namespace_sfc_creation()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', viewPath: __DIR__ . '/Fixtures');

        $path = $finder->resolveSingleFileComponentPathForCreation('admin::new-component');

        $this->assertNotNull($path);
        $this->assertStringContainsString('Fixtures', $path);
        $this->assertStringContainsString('new-component', $path);
    }

    public function test_resolves_path_for_registered_namespace_mfc_creation()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', viewPath: __DIR__ . '/Fixtures');

        $path = $finder->resolveMultiFileComponentPathForCreation('admin::new-component');

        $this->assertNotNull($path);
        $this->assertStringContainsString('Fixtures', $path);
        $this->assertStringContainsString('new-component', $path);
    }

    public function test_can_resolve_single_segment_class_name()
    {
        $finder = new Finder();

        $finder->addComponent(class: SingleSegmentComponent::class);

        $name = $finder->normalizeName(SingleSegmentComponent::class);

        $this->assertEquals('lw' . crc32(SingleSegmentComponent::class), $name);
    }

    public function test_self_named_class_component_resolves_view_from_self_named_path()
    {
        config()->set('livewire.view_path', __DIR__ . '/Fixtures/views');

        app('livewire.finder')->addLocation(classNamespace: 'Livewire\Finder\Fixtures');

        Livewire::test(SelfNamedViewComponent::class)
            ->assertSee('Self-named view component rendered');
    }

    public function test_index_class_component_resolves_view_from_collapsed_path()
    {
        config()->set('livewire.view_path', __DIR__ . '/Fixtures/views');

        app('livewire.finder')->addLocation(classNamespace: 'Livewire\Finder\Fixtures');

        Livewire::test(IndexViewComponent::class)
            ->assertSee('Index view component rendered');
    }
}

class SingleSegmentComponent extends Component
{
    public function render()
    {
        return '<div>Single segment component</div>';
    }
}
