<?php

namespace Livewire\Finder;

use Livewire\Finder\Fixtures\SelfNamedComponent\SelfNamedComponent;
use Livewire\Finder\Fixtures\Nested\NestedComponent;
use Livewire\Finder\Fixtures\IndexComponent\Index;
use Livewire\Finder\Fixtures\FinderTestClassComponent;
use Livewire\Component;

class UnitTest extends \Tests\TestCase
{
    public function test_can_add_and_resolve_named_class_component()
    {
        $finder = new Finder();

        $finder->addComponent('test-component', className: FinderTestClassComponent::class);

        $name = $finder->normalizeName(FinderTestClassComponent::class);

        $this->assertEquals('test-component', $name);

        $class = $finder->resolveClassComponentClassName('test-component');

        $this->assertEquals(FinderTestClassComponent::class, $class);
    }

    public function test_can_add_and_resolve_unnamed_class_component()
    {
        $finder = new Finder();

        $finder->addComponent(className: FinderTestClassComponent::class);

        $name = $finder->normalizeName(FinderTestClassComponent::class);

        $this->assertEquals('lw' . crc32(FinderTestClassComponent::class), $name);

        $class = $finder->resolveClassComponentClassName($name);

        $this->assertEquals(FinderTestClassComponent::class, $class);
    }

    public function test_can_add_and_resolve_named_anonymous_class_component()
    {
        $finder = new Finder();

        $finder->addComponent('test-component', className: $obj = new class extends Component {
            public function render() {
                return '<div>Finder Location Test Component</div>';
            }
        });

        $name = $finder->normalizeName($obj);

        $this->assertEquals('test-component', $name);

        $class = $finder->resolveClassComponentClassName('test-component');

        $this->assertEquals($obj::class, $class);
    }

    public function test_can_add_and_resolve_unnamed_anonymous_class_component()
    {
        $finder = new Finder();

        $finder->addComponent(className: $obj = new class extends Component {
            public function render() {
                return '<div>Finder Location Test Component</div>';
            }
        });

        $name = $finder->normalizeName($obj);

        $this->assertEquals('lw' . crc32($obj::class), $name);

        $class = $finder->resolveClassComponentClassName($name);

        $this->assertEquals($obj::class, $class);
    }

    public function test_can_add_and_resolve_location_class_component()
    {
        $finder = new Finder();

        $finder->addLocation(classNamespace: 'Livewire\Finder\Fixtures');

        $name = $finder->normalizeName(FinderTestClassComponent::class);

        $this->assertEquals('finder-test-class-component', $name);

        $class = $finder->resolveClassComponentClassName($name);

        $this->assertEquals('Livewire\Finder\Fixtures\FinderTestClassComponent', $class);
    }

    public function test_can_add_and_resolve_location_class_nested_component()
    {
        $finder = new Finder();

        $finder->addLocation(classNamespace: 'Livewire\Finder\Fixtures');

        $name = $finder->normalizeName(NestedComponent::class);

        $this->assertEquals('nested.nested-component', $name);

        $class = $finder->resolveClassComponentClassName($name);

        $this->assertEquals('Livewire\Finder\Fixtures\Nested\NestedComponent', $class);
    }

    public function test_can_add_and_resolve_location_class_index_component()
    {
        $finder = new Finder();

        $finder->addLocation(classNamespace: 'Livewire\Finder\Fixtures');

        $name = $finder->normalizeName(Index::class);

        $this->assertEquals('index-component', $name);

        $class = $finder->resolveClassComponentClassName($name);

        $this->assertEquals('Livewire\Finder\Fixtures\IndexComponent\Index', $class);
    }

    public function test_can_add_and_resolve_location_class_self_named_component()
    {
        $finder = new Finder();

        $finder->addLocation(classNamespace: 'Livewire\Finder\Fixtures');

        $name = $finder->normalizeName(SelfNamedComponent::class);

        $this->assertEquals('self-named-component', $name);

        $class = $finder->resolveClassComponentClassName($name);

        $this->assertEquals('Livewire\Finder\Fixtures\SelfNamedComponent\SelfNamedComponent', $class);
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

    public function test_can_resolve_class_component_with_namespace()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', classNamespace: 'Livewire\Finder\Fixtures');

        $class = $finder->resolveClassComponentClassName('admin::finder-test-class-component');

        $this->assertEquals('Livewire\Finder\Fixtures\FinderTestClassComponent', $class);
    }

    public function test_can_resolve_nested_class_component_with_namespace()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', classNamespace: 'Livewire\Finder\Fixtures');

        $class = $finder->resolveClassComponentClassName('admin::nested.nested-component');

        $this->assertEquals('Livewire\Finder\Fixtures\Nested\NestedComponent', $class);
    }

    public function test_can_resolve_index_class_component_with_namespace()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', classNamespace: 'Livewire\Finder\Fixtures');

        $class = $finder->resolveClassComponentClassName('admin::index-component');

        $this->assertEquals('Livewire\Finder\Fixtures\IndexComponent\Index', $class);
    }

    public function test_can_resolve_self_named_class_component_with_namespace()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', classNamespace: 'Livewire\Finder\Fixtures');

        $class = $finder->resolveClassComponentClassName('admin::self-named-component');

        $this->assertEquals('Livewire\Finder\Fixtures\SelfNamedComponent\SelfNamedComponent', $class);
    }

    public function test_returns_null_for_unknown_namespace()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', classNamespace: 'Livewire\Finder\Fixtures');

        $class = $finder->resolveClassComponentClassName('unknown::some-component');

        $this->assertNull($class);
    }

    public function test_can_resolve_single_file_component_with_namespace()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', viewPath: __DIR__ . '/fixtures');

        $path = $finder->resolveSingleFileComponentPath('admin::finder-test-single-file-component');

        $this->assertEquals(__DIR__ . '/fixtures/finder-test-single-file-component.blade.php', $path);
    }

    public function test_can_resolve_index_single_file_component_with_namespace()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', viewPath: __DIR__ . '/fixtures');

        $path = $finder->resolveSingleFileComponentPath('admin::nested-view-based');

        $this->assertEquals(__DIR__ . '/fixtures/nested-view-based/index.blade.php', $path);
    }

    public function test_can_resolve_self_named_single_file_component_with_namespace()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', viewPath: __DIR__ . '/fixtures');

        $path = $finder->resolveSingleFileComponentPath('admin::self-named-component');

        $this->assertEquals(__DIR__ . '/fixtures/self-named-component/self-named-component.blade.php', $path);
    }

    public function test_returns_null_for_single_file_component_with_unknown_namespace()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', viewPath: __DIR__ . '/fixtures');

        $path = $finder->resolveSingleFileComponentPath('unknown::some-component');

        $this->assertNull($path);
    }

    public function test_can_resolve_multi_file_component_with_namespace()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', viewPath: __DIR__ . '/fixtures');

        $path = $finder->resolveMultiFileComponentPath('admin::multi-file-test-component');

        $this->assertEquals(__DIR__ . '/fixtures/multi-file-test-component', $path);
    }

    public function test_can_resolve_multi_file_index_component_with_namespace()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', viewPath: __DIR__ . '/fixtures');

        $path = $finder->resolveMultiFileComponentPath('admin::multi-file-index');

        $this->assertEquals(__DIR__ . '/fixtures/multi-file-index', $path);
    }

    public function test_can_resolve_multi_file_self_named_component_with_namespace()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', viewPath: __DIR__ . '/fixtures');

        $path = $finder->resolveMultiFileComponentPath('admin::multi-file-self-named');

        $this->assertEquals(__DIR__ . '/fixtures/multi-file-self-named', $path);
    }

    public function test_returns_null_for_multi_file_component_with_unknown_namespace()
    {
        $finder = new Finder();

        $finder->addNamespace('admin', viewPath: __DIR__ . '/fixtures');

        $path = $finder->resolveMultiFileComponentPath('unknown::some-component');

        $this->assertNull($path);
    }

    public function test_can_resolve_component_with_namespace_in_factory()
    {
        $finder = new Finder();

        $finder->addNamespace('pages', classNamespace: 'Livewire\Finder\Fixtures');

        $class = $finder->resolveClassComponentClassName('pages::finder-test-class-component');

        $this->assertEquals('Livewire\Finder\Fixtures\FinderTestClassComponent', $class);
    }
}
