<?php

namespace Livewire\Tests;

use Livewire\Component;
use Livewire\Livewire;
use Illuminate\Routing\UrlGenerator;
use Tests\TestComponent;

class ComponentDependencyInjectionUnitTest extends \Tests\TestCase
{
    public function test_component_mount_action_with_dependency()
    {
        $component = Livewire::test(ComponentWithDependencyInjection::class, ['id' => 123]);

        $this->assertEquals('http://localhost/some-url/123', $component->foo);
        $this->assertEquals(123, $component->bar);
    }

    public function test_component_action_with_dependency()
    {
        $component = Livewire::test(ComponentWithDependencyInjection::class);

        $component->runAction('injection', 'foobar');

        // $this->assertEquals('http://localhost', $component->foo);
        $this->assertEquals('foobar', $component->bar);
    }

    public function test_component_action_with_spread_operator()
    {
        $component = Livewire::test(ComponentWithDependencyInjection::class);

        $component->runAction('spread', 'foo', 'bar', 'baz');

        $this->assertEquals(['foo', 'bar', 'baz'], $component->foo);
    }

    public function test_component_action_with_paramter_name_that_matches_a_container_registration_name()
    {
        $component = Livewire::test(ComponentWithDependencyInjection::class);

        app()->bind('foo', \stdClass::class);

        $component->runAction('actionWithContainerBoundNameCollision', 'bar');

        $this->assertEquals('bar', $component->foo);
    }

    public function test_component_action_with_primitive()
    {
        $component = Livewire::test(ComponentWithDependencyInjection::class);

        $component->runAction('primitive', 1);

        $this->assertEquals(1, $component->foo);
    }

    public function test_component_action_with_default_value()
    {
        $component = Livewire::test(ComponentWithDependencyInjection::class);

        $component->runAction('primitiveWithDefault', 10, 'foo');
        $this->assertEquals(10, $component->foo);
        $this->assertEquals('foo', $component->bar);

        $component->runAction('primitiveWithDefault', 100);
        $this->assertEquals(100, $component->foo);
        $this->assertEquals('default', $component->bar);

        $component->runAction('primitiveWithDefault');
        $this->assertEquals(1, $component->foo);
        $this->assertEquals('default', $component->bar);

        $component->runAction('primitiveWithDefault', null, 'foo');
        $this->assertEquals(null, $component->foo);
        $this->assertEquals('foo', $component->bar);
    }

    public function test_component_action_with_dependency_and_primitive()
    {
        $component = Livewire::test(ComponentWithDependencyInjection::class);

        $component->runAction('mixed', 1);

        $this->assertEquals('http://localhost/some-url/1', $component->foo);
        $this->assertEquals(1, $component->bar);
    }

    public function test_component_action_with_dependency_and_optional_primitive()
    {
        $component = Livewire::test(ComponentWithDependencyInjection::class);

        $component->runAction('mixedWithDefault', 10);
        $this->assertEquals('http://localhost/some-url', $component->foo);
        $this->assertEquals(10, $component->bar);

        $component->runAction('mixedWithDefault');
        $this->assertEquals('http://localhost/some-url', $component->foo);
        $this->assertEquals(1, $component->bar);

        $component->runAction('mixedWithDefault', null);
        $this->assertEquals('http://localhost/some-url', $component->foo);
        $this->assertNull($component->bar);
    }

    public function test_component_render_method_with_dependency()
    {
        $component = Livewire::test(CustomComponent::class);

        $component->assertSee('Results from the service');
    }

    public function test_component_mount_action_with_primitive_union_types()
    {
        $component = Livewire::test(ComponentWithUnionTypes::class);

        $this->assertEquals('http://localhost/some-url/123', $component->foo);
        $this->assertEquals(123, $component->bar);

        $component->runAction('injection', 'foobar');

        $this->assertEquals('http://localhost', $component->foo);
        $this->assertEquals('foobar', $component->bar);
    }
}

class ComponentWithDependencyInjection extends TestComponent
{
    public $foo;
    public $bar;

    public function mount(UrlGenerator $generator, $id = 123)
    {
        $this->foo = $generator->to('/some-url', $id);
        $this->bar = $id;
    }

    public function injection(UrlGenerator $generator, $bar)
    {
        $this->foo = $generator->to('/');
        $this->bar = $bar;
    }

    public function spread(...$params)
    {
        $this->foo = $params;
    }

    public function primitive(int $foo)
    {
        $this->foo = $foo;
    }

    public function primitiveWithDefault(?int $foo = 1, $bar = 'default')
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }

    public function mixed(UrlGenerator $generator, int $id)
    {
        $this->foo = $generator->to('/some-url', $id);
        $this->bar = $id;
    }

    public function mixedWithDefault(UrlGenerator $generator, ?int $id = 1)
    {
        $this->foo = $generator->to('/some-url');
        $this->bar = $id;
    }

    public function actionWithContainerBoundNameCollision($foo)
    {
        $this->foo = $foo;
    }
}

class CustomComponent extends Component
{
    public function render(CustomService $service)
    {
        return view('show-property-value', [
            'message' => $service->results()
        ]);
    }
}

class CustomService
{
    public function results()
    {
        return 'Results from the service';
    }
}

class ComponentWithUnionTypes extends TestComponent
{
    public $foo;
    public $bar;

    public function mount(UrlGenerator $generator, string|int $id = 123)
    {
        $this->foo = $generator->to('/some-url', $id);
        $this->bar = $id;
    }

    public function injection(UrlGenerator $generator, $bar)
    {
        $this->foo = $generator->to('/');
        $this->bar = $bar;
    }
}
