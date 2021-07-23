<?php

namespace Tests\Unit;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\ImplicitlyBoundMethod;
use stdClass;

class ImplicitlyBoundMethodTest extends TestCase
{
    /** @test */
    public function sequentially_bind()
    {
        $stub = new ContainerTestCallStub;
        $result = ImplicitlyBoundMethodTester::testSequentialSubstitution([$stub, 'unresolvable'], ['foo', 'bar']);
        $this->assertEqualsCanonicalizing(['foo'=>'foo', 'bar'=>'bar'], $result);

        $stub = new ContainerTestCallStub;
        $result = ImplicitlyBoundMethodTester::testSequentialSubstitution([$stub, 'unresolvable'], ['foo' => 'foo', 'bar']);
        $this->assertEqualsCanonicalizing(['foo'=>'foo', 'bar'=>'bar'], $result);

        $stub = new ContainerTestCallStub;
        $result = ImplicitlyBoundMethodTester::testSequentialSubstitution([$stub, 'unresolvable'], ['bar', 'foo' => 'foo']);
        $this->assertEqualsCanonicalizing(['foo'=>'foo', 'bar'=>'bar'], $result);

        $stub = new ContainerTestCallStub;
        $result = ImplicitlyBoundMethodTester::testSequentialSubstitution([$stub, 'unresolvable'], ['bar' => 'bar', 'foo']);
        $this->assertEqualsCanonicalizing(['foo'=>'foo', 'bar'=>'bar'], $result);

        $stub = new ContainerTestCallStub;
        $result = ImplicitlyBoundMethodTester::testSequentialSubstitution([$stub, 'unresolvable'], ['foo', 'bar' => 'bar']);
        $this->assertEqualsCanonicalizing(['foo'=>'foo', 'bar'=>'bar'], $result);


        $stub = new ContainerTestCallStub;
        $result = ImplicitlyBoundMethodTester::testSequentialSubstitution([$stub, 'inject'], ['foo']);
        $this->assertEqualsCanonicalizing(['default'=>'foo'], $result);

        $stub = new ContainerTestCallStub;
        $result = ImplicitlyBoundMethodTester::testSequentialSubstitution([$stub, 'inject'], ['foo', 'bar']);
        $this->assertEqualsCanonicalizing([1=>'bar', 'default'=>'foo'], $result);

        $stub = new ContainerTestCallStub;
        $result = ImplicitlyBoundMethodTester::testSequentialSubstitution([$stub, 'implicit'], ['foo', 'model', 'bar']);
        $this->assertEqualsCanonicalizing(['foo'=>'foo', 'model'=>'model', 'bar'=>'bar'], $result);

        $stub = new ContainerTestCallStub;
        $result = ImplicitlyBoundMethodTester::testSequentialSubstitution([$stub, 'implicit'], ['foo', 'model', 'bar', 'baz', 'more']);
        $this->assertEqualsCanonicalizing(['foo'=>'foo', 'model'=>'model', 'bar'=>'bar', 0=>'baz', 1=>'more'], $result);

        $stub = new ContainerTestCallStub;
        $result = ImplicitlyBoundMethodTester::testSequentialSubstitution([$stub, 'injectAndImplicit'], ['model', 'foo', 'bar', 'more']);
        $this->assertEqualsCanonicalizing([2=>'bar', 3=>'more', 'model'=>'model', 'bar'=>'foo'], $result);
    }

    /** @test */
    public function call_with_sequential_parameters()
    {
        $container = new Container;
        $result = ImplicitlyBoundMethod::call($container, ContainerTestCallStub::class.'@inject', ['foo']);
        $this->assertInstanceOf(ContainerCallConcreteStub::class, $result[0]);
        $this->assertSame('foo', $result[1]);

        $container = new Container;
        $result = ImplicitlyBoundMethod::call($container, ContainerTestCallStub::class.'@inject', []);
        $this->assertInstanceOf(ContainerCallConcreteStub::class, $result[0]);
        $this->assertSame('taylor', $result[1]);

        $container = new Container;
        $result = ImplicitlyBoundMethod::call($container, ContainerTestCallStub::class.'@unresolvable', ['foo', 'bar']);
        $this->assertSame(['foo', 'bar'], $result);

        $container = new Container;
        $result = ImplicitlyBoundMethod::call($container, ContainerTestCallStub::class.'@unresolvable', ['bar' => 'bar', 'foo']);
        $this->assertSame(['foo', 'bar'], $result);
    }

    /** @test */
    public function call_with_implicit_model_binding()
    {
        $container = new Container;
        $result = ImplicitlyBoundMethod::call($container, ContainerTestCallStub::class.'@implicit', ['foo', 'injected', 'bar']);
        $this->assertSame('foo', $result[0]);
        $this->assertInstanceOf(ContainerTestModel::class, $result[1]);
        $this->assertSame(['injected'], $result[1]->value);
        $this->assertSame('bar', $result[2]);
        $this->assertSame(3, count($result));

        $container = new Container;
        $result = ImplicitlyBoundMethod::call($container, ContainerTestCallStub::class.'@implicit', ['foo', new ContainerTestModel('injected'), 'bar']);
        $this->assertSame('foo', $result[0]);
        $this->assertInstanceOf(ContainerTestModel::class, $result[1]);
        $this->assertSame(['injected'], $result[1]->value);
        $this->assertSame('bar', $result[2]);
        $this->assertSame(3, count($result));

        $container = new Container;
        $result = ImplicitlyBoundMethod::call($container, ContainerTestCallStub::class.'@implicit', ['foo', 'injected', 'bar', 'more', 'params']);
        $this->assertSame('foo', $result[0]);
        $this->assertInstanceOf(ContainerTestModel::class, $result[1]);
        $this->assertSame(['injected'], $result[1]->value);
        $this->assertSame('bar', $result[2]);
        $this->assertSame('more', $result[3]);
        $this->assertSame('params', $result[4]);
        $this->assertSame(5, count($result));

        $result = ImplicitlyBoundMethod::call($container, function (ContainerTestModel $foo, $bar = []) {
            return func_get_args();
        }, ['foo', 'bar' => 'taylor']);
        $this->assertInstanceOf(ContainerTestModel::class, $result[0]);
        $this->assertSame(['foo'], $result[0]->value);
        $this->assertSame('taylor', $result[1]);

        $result = ImplicitlyBoundMethod::call($container, function (ContainerTestModel $foo, $bar = []) {
            return func_get_args();
        }, ['foo' => new ContainerTestModel('foo'), 'bar' => 'taylor']);
        $this->assertInstanceOf(ContainerTestModel::class, $result[0]);
        $this->assertSame(['foo'], $result[0]->value);
        $this->assertSame('taylor', $result[1]);

        $result = ImplicitlyBoundMethod::call($container, function (stdClass $foo, ContainerTestModel $bar) {
            return func_get_args();
        }, [ContainerTestModel::class => 'taylor']);
        $this->assertInstanceOf(stdClass::class, $result[0]);
        $this->assertInstanceOf(ContainerTestModel::class, $result[1]);
        $this->assertSame(['taylor'], $result[1]->value);
    }

    /** @test */
    public function bind_model_with_null_return()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('No query results for model [Tests\Unit\NullContainerTestModel]');

        $container = new Container;
        $result = ImplicitlyBoundMethod::call($container, ContainerTestCallStub::class.'@nullImplicit', ['foo', null, 'bar']);
    }

    /** @test */
    public function call_with_injected_dependency_and_implicity_model_binding()
    {
        $container = new Container;
        $result = ImplicitlyBoundMethod::call($container, ContainerTestCallStub::class.'@injectAndImplicit', ['injected', 'bar']);
        $this->assertInstanceOf(ContainerCallConcreteStub::class, $result[0]);
        $this->assertInstanceOf(ContainerTestModel::class, $result[1]);
        $this->assertSame(['injected'], $result[1]->value);
        $this->assertSame('bar', $result[2]);
        $this->assertSame(3, count($result));
    }

    /** @test */
    public function call_with_injected_dependency_not_first()
    {
        $container = new Container;
        $result = ImplicitlyBoundMethod::call($container, 'Tests\Unit\containerTestInjectSecond', ['foo', 'bar']);
        $this->assertSame('foo', $result[0]);
        $this->assertInstanceOf(ContainerCallConcreteStub::class, $result[1]);
        $this->assertSame('bar', $result[2]);
    }

    /** @test */
    public function call_implicitly_with_global_method_name()
    {
        $container = new Container;
        $result = ImplicitlyBoundMethod::call($container, 'Tests\Unit\containerTestImplicit');
        $this->assertInstanceOf(ContainerTestModel::class, $result[0]);
        $this->assertSame('taylor', $result[1]);
    }

    /** @test */
    public function call_implicitly_with_static_method_name_string()
    {
        $container = new Container;
        $result = ImplicitlyBoundMethod::call($container, 'Tests\Unit\ContainerStaticMethodStub::implicit');
        $this->assertInstanceOf(ContainerTestModel::class, $result[0]);
        $this->assertSame('taylor', $result[1]);
    }

    /** @test */
    public function call_implicitly_with_callable_object()
    {
        $container = new Container;
        $callable = new ContainerCallImplicitCallableStub;
        $result = ImplicitlyBoundMethod::call($container, $callable);
        $this->assertInstanceOf(ContainerTestModel::class, $result[0]);
        $this->assertSame('jeffrey', $result[1]);
    }

    /**************************************************************************
     * Everything below here is borrowed from Laravel Container testing
     * (tests/Container/ContainerCallTeset.php - with a few mods) to verify
     * ImplicitlyBoundMethod had no adverse impacts when extending BoundMethod.
     *************************************************************************/
    /** @test */
    /** @test */
    public function call_with_at_sign_based_class_references()
    {
        $container = new Container;
        $result = ImplicitlyBoundMethod::call($container, ContainerTestCallStub::class.'@work', ['foo', 'bar']);
        $this->assertEquals(['foo', 'bar'], $result);

        $container = new Container;
        $result = ImplicitlyBoundMethod::call($container, ContainerTestCallStub::class.'@inject');
        $this->assertInstanceOf(ContainerCallConcreteStub::class, $result[0]);
        $this->assertSame('taylor', $result[1]);

        $container = new Container;
        $result = ImplicitlyBoundMethod::call($container, ContainerTestCallStub::class.'@inject', ['default' => 'foo']);
        $this->assertInstanceOf(ContainerCallConcreteStub::class, $result[0]);
        $this->assertSame('foo', $result[1]);

        $container = new Container;
        $result = ImplicitlyBoundMethod::call($container, ContainerTestCallStub::class, ['foo', 'bar'], 'work');
        $this->assertEquals(['foo', 'bar'], $result);
    }

    /** @test */
    public function call_with_callable_array()
    {
        $container = new Container;
        $stub = new ContainerTestCallStub;
        $result = ImplicitlyBoundMethod::call($container, [$stub, 'work'], ['foo', 'bar']);
        $this->assertEquals(['foo', 'bar'], $result);
    }

    /** @test */
    public function call_with_static_method_name_string()
    {
        $container = new Container;
        $result = ImplicitlyBoundMethod::call($container, 'Tests\Unit\ContainerStaticMethodStub::inject');
        $this->assertInstanceOf(ContainerCallConcreteStub::class, $result[0]);
        $this->assertSame('taylor', $result[1]);
    }

    /** @test */
    public function call_with_global_method_name()
    {
        $container = new Container;
        $result = ImplicitlyBoundMethod::call($container, 'Tests\Unit\containerTestInject');
        $this->assertInstanceOf(ContainerCallConcreteStub::class, $result[0]);
        $this->assertSame('taylor', $result[1]);
    }

    /** @test */
    public function call_with_bound_method()
    {
        $container = new Container;
        $container->bindMethod(ContainerTestCallStub::class.'@unresolvable', function ($stub) {
            return $stub->unresolvable('foo', 'bar');
        });
        $result = ImplicitlyBoundMethod::call($container, ContainerTestCallStub::class.'@unresolvable');
        $this->assertEquals(['foo', 'bar'], $result);

        $container = new Container;
        $container->bindMethod(ContainerTestCallStub::class.'@unresolvable', function ($stub) {
            return $stub->unresolvable('foo', 'bar');
        });
        $result = ImplicitlyBoundMethod::call($container, [new ContainerTestCallStub, 'unresolvable']);
        $this->assertEquals(['foo', 'bar'], $result);

        $container = new Container;
        $result = ImplicitlyBoundMethod::call($container, [new ContainerTestCallStub, 'inject'], ['_stub' => 'foo', 'default' => 'bar']);
        $this->assertInstanceOf(ContainerCallConcreteStub::class, $result[0]);
        $this->assertSame('bar', $result[1]);

        $container = new Container;
        $result = ImplicitlyBoundMethod::call($container, [new ContainerTestCallStub, 'inject'], ['_stub' => 'foo']);
        $this->assertInstanceOf(ContainerCallConcreteStub::class, $result[0]);
        $this->assertSame('taylor', $result[1]);
    }

    /** @test */
    public function bind_method_accepts_an_array()
    {
        $container = new Container;
        $container->bindMethod([ContainerTestCallStub::class, 'unresolvable'], function ($stub) {
            return $stub->unresolvable('foo', 'bar');
        });
        $result = ImplicitlyBoundMethod::call($container, ContainerTestCallStub::class.'@unresolvable');
        $this->assertEquals(['foo', 'bar'], $result);

        $container = new Container;
        $container->bindMethod([ContainerTestCallStub::class, 'unresolvable'], function ($stub) {
            return $stub->unresolvable('foo', 'bar');
        });
        $result = ImplicitlyBoundMethod::call($container, [new ContainerTestCallStub, 'unresolvable']);
        $this->assertEquals(['foo', 'bar'], $result);
    }

    /** @test */
    public function call_closure_with_injected_dependency()
    {
        $container = new Container;
        $result = ImplicitlyBoundMethod::call($container, function (ContainerCallConcreteStub $stub) {
            return func_get_args();
        }, ['foo' => 'bar']);
        $this->assertInstanceOf(ContainerCallConcreteStub::class, $result[0]);
        $this->assertEquals([], $result[0]->value);

        $result = ImplicitlyBoundMethod::call($container, function (ContainerCallConcreteStub $stub) {
            return func_get_args();
        }, ['foo' => 'bar', 'stub' => new ContainerCallConcreteStub('baz')]);
        $this->assertInstanceOf(ContainerCallConcreteStub::class, $result[0]);
        $this->assertEquals(['baz'], $result[0]->value);
    }

    /** @test */
    public function call_withh_dependencies()
    {
        $container = new Container;
        $result = ImplicitlyBoundMethod::call($container, function (stdClass $foo, $bar = []) {
            return func_get_args();
        });

        $this->assertInstanceOf(stdClass::class, $result[0]);
        $this->assertEquals([], $result[1]);

        $result = ImplicitlyBoundMethod::call($container, function (stdClass $foo, $bar = []) {
            return func_get_args();
        }, ['bar' => 'taylor']);

        $this->assertInstanceOf(stdClass::class, $result[0]);
        $this->assertSame('taylor', $result[1]);

        $stub = new ContainerCallConcreteStub;
        $result = ImplicitlyBoundMethod::call($container, function (stdClass $foo, ContainerCallConcreteStub $bar) {
            return func_get_args();
        }, [ContainerCallConcreteStub::class => $stub]);

        $this->assertInstanceOf(stdClass::class, $result[0]);
        $this->assertSame($stub, $result[1]);

        /*
         * Wrap a function...
         */
        $result = $container->wrap(function (stdClass $foo, $bar = []) {
            return func_get_args();
        }, ['bar' => 'taylor']);

        $this->assertInstanceOf(Closure::class, $result);
        $result = $result();

        $this->assertInstanceOf(stdClass::class, $result[0]);
        $this->assertSame('taylor', $result[1]);
    }

    /** @test */
    public function call_with_callable_object()
    {
        $container = new Container;
        $callable = new ContainerCallCallableStub;
        $result = ImplicitlyBoundMethod::call($container, $callable);
        $this->assertInstanceOf(ContainerCallConcreteStub::class, $result[0]);
        $this->assertSame('jeffrey', $result[1]);
    }

    /** @test */
    public function call_without_required_parameters_throws_exception()
    {
        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('Unable to resolve dependency [Parameter #0 [ <required> $foo ]] in class Tests\Unit\ContainerTestCallStub');

        $container = new Container;
        ImplicitlyBoundMethod::call($container, ContainerTestCallStub::class.'@unresolvable');
    }

    /** @test */
    public function call_without_required_parameters_on_closure_throws_exception()
    {
        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('Unable to resolve dependency [Parameter #0 [ <required> $foo ]] in class Tests\Unit\ImplicitlyBoundMethodTest');

        $container = new Container;
        $foo = ImplicitlyBoundMethod::call($container, function ($foo, $bar = 'default') {
            return $foo;
        });
    }
}

class ContainerTestCallStub
{
    public function work()
    {
        return func_get_args();
    }

    public function inject(ContainerCallConcreteStub $stub, $default = 'taylor')
    {
        return func_get_args();
    }

    public function unresolvable($foo, $bar)
    {
        return func_get_args();
    }

    // added for ImplicitlyBoundMethod
    public function implicit($foo, ContainerTestModel $model, $bar, ...$params)
    {
        return func_get_args();
    }

    // added for NullImplicitlyBoundMethod
    public function nullImplicit($foo, NullContainerTestModel $model, $bar, ...$params)
    {
        return func_get_args();
    }

    public function injectAndImplicit(ContainerCallConcreteStub $stub, ContainerTestModel $model, $bar = 'taylor')
    {
        return func_get_args();
    }
}

class ImplicitlyBoundMethodTester extends ImplicitlyBoundMethod
{
    public static function testSequentialSubstitution($callback, array $parameters = [])
    {
        $paramIndex = 0;
        foreach (static::getCallReflector($callback)->getParameters() as $parameter) {
            static::substituteNameBindingForCallParameter($parameter, $parameters, $paramIndex);
        }
        return $parameters;
    }
}

class ContainerTestModel extends \Illuminate\Database\Eloquent\Model
{
    public function __construct()
    {
        $this->value = func_get_args();
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $this->value = func_get_args();
        return $this;
    }
}

class NullContainerTestModel extends \Illuminate\Database\Eloquent\Model
{
    public function __construct()
    {
        $this->value = func_get_args();
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return null;
    }
}

class ContainerCallConcreteStub
{
    public $value;
    public function __construct()
    {
        $this->value = func_get_args();
    }
}

function containerTestInject(ContainerCallConcreteStub $stub, $default = 'taylor')
{
    return func_get_args();
}

function containerTestInjectSecond($foo, ContainerCallConcreteStub $stub, $default = 'taylor')
{
    return func_get_args();
}

function containerTestImplicit(ContainerTestModel $stub, $default = 'taylor')
{
    return func_get_args();
}

class ContainerStaticMethodStub
{
    public static function inject(ContainerCallConcreteStub $stub, $default = 'taylor')
    {
        return func_get_args();
    }

    public static function implicit(ContainerTestModel $stub, $default = 'taylor')
    {
        return func_get_args();
    }
}

class ContainerCallCallableStub
{
    public function __invoke(ContainerCallConcreteStub $stub, $default = 'jeffrey')
    {
        return func_get_args();
    }
}

class ContainerCallImplicitCallableStub
{
    public function __invoke(ContainerTestModel $stub, $default = 'jeffrey')
    {
        return func_get_args();
    }
}
