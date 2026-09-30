<?php

namespace Livewire\Tests;

use Illuminate\Container\Container;
use Illuminate\Contracts\Routing\UrlRoutable;
use Livewire\ImplicitlyBoundMethod;

enum StatusEnum: string {
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}

class TestRoutableModel implements UrlRoutable {
    public function getRouteKey() { return '123'; }
    public function getRouteKeyName() { return 'id'; }
    public function resolveRouteBinding($value, $field = null) {
        $model = new self;
        $model->id = $value;
        return $model;
    }
    public function resolveChildRouteBinding($childType, $value, $field) {}
    public $id;
}

class BoundMethodTarget {
    public $called = false;

    public function zeroArgAction(): string
    {
        $this->called = true;
        return 'zero_arg';
    }

    public function withDependency(Container $container): string
    {
        return 'container_injected';
    }

    public function withDefault($value = 'default_value'): string
    {
        return $value;
    }

    public function withVariadic(...$items): array
    {
        return $items;
    }

    public function withEnum(StatusEnum $status): string
    {
        return $status->value;
    }

    public function withModel(TestRoutableModel $model): string
    {
        return 'model:' . $model->id;
    }

    protected function protectedAction(): string
    {
        return 'protected';
    }
}

class InvokableTarget {
    public function __invoke(): string
    {
        return 'invoked';
    }
}

class ImplicitlyBoundMethodUnitTest extends \Tests\TestCase
{
    public function test_zero_argument_public_action_fast_path()
    {
        $container = Container::getInstance();
        $target = new BoundMethodTarget;

        $result = ImplicitlyBoundMethod::call($container, [$target, 'zeroArgAction']);

        $this->assertSame('zero_arg', $result);
        $this->assertTrue($target->called);
    }

    public function test_container_method_binding_takes_precedence_over_zero_arg_fast_path()
    {
        $container = new Container;
        $target = new BoundMethodTarget;

        $container->bindMethod(get_class($target).'@zeroArgAction', function () {
            return 'bound_by_container';
        });

        $result = ImplicitlyBoundMethod::call($container, [$target, 'zeroArgAction']);

        $this->assertSame('bound_by_container', $result);
    }

    public function test_action_with_container_dependency_is_resolved()
    {
        $container = Container::getInstance();
        $target = new BoundMethodTarget;

        $result = ImplicitlyBoundMethod::call($container, [$target, 'withDependency']);

        $this->assertSame('container_injected', $result);
    }

    public function test_action_with_default_parameter()
    {
        $container = Container::getInstance();
        $target = new BoundMethodTarget;

        $result = ImplicitlyBoundMethod::call($container, [$target, 'withDefault']);
        $this->assertSame('default_value', $result);

        $resultCustom = ImplicitlyBoundMethod::call($container, [$target, 'withDefault'], ['custom']);
        $this->assertSame('custom', $resultCustom);
    }

    public function test_action_with_variadics()
    {
        $container = Container::getInstance();
        $target = new BoundMethodTarget;

        $result = ImplicitlyBoundMethod::call($container, [$target, 'withVariadic'], ['a', 'b', 'c']);

        $this->assertSame(['a', 'b', 'c'], $result);
    }

    public function test_action_with_enum_binding()
    {
        $container = Container::getInstance();
        $target = new BoundMethodTarget;

        $result = ImplicitlyBoundMethod::call($container, [$target, 'withEnum'], ['active']);

        $this->assertSame('active', $result);
    }

    public function test_action_with_model_binding()
    {
        $container = Container::getInstance();
        $container->bind(TestRoutableModel::class, fn () => new TestRoutableModel);
        $target = new BoundMethodTarget;

        $result = ImplicitlyBoundMethod::call($container, [$target, 'withModel'], ['42']);

        $this->assertSame('model:42', $result);
    }

    public function test_invokable_callback_bypasses_array_fast_path()
    {
        $container = Container::getInstance();
        $target = new InvokableTarget;

        $result = ImplicitlyBoundMethod::call($container, $target);

        $this->assertSame('invoked', $result);
    }

    public function test_closure_callback_bypasses_array_fast_path()
    {
        $container = Container::getInstance();

        $result = ImplicitlyBoundMethod::call($container, fn () => 'closure_result');

        $this->assertSame('closure_result', $result);
    }

    public function test_flush_cache_resets_method_param_count_cache()
    {
        $container = Container::getInstance();
        $target = new BoundMethodTarget;

        ImplicitlyBoundMethod::call($container, [$target, 'zeroArgAction']);

        ImplicitlyBoundMethod::flushCache();

        $result = ImplicitlyBoundMethod::call($container, [$target, 'zeroArgAction']);
        $this->assertSame('zero_arg', $result);
    }
}
