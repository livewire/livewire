<?php

namespace Livewire\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\Livewire;

class ComponentMethodBindingsUnitTest extends \Tests\TestCase
{
    /** @test */
    public function mount_method_receives_explicit_model_binding()
    {
        Livewire::test(ComponentWithBindings::class, [
            'model' => new ModelToBeBound('new model'),
        ])->assertSeeText('new model')->assertSeeText('param-default');

        Livewire::test(ComponentWithBindings::class, [
            'model' => new ModelToBeBound('new model'),
            'param' => 'foo',
        ])->assertSeeText('new model')->assertSeeText('foo');
    }

    /** @test */
    public function mount_method_receives_explicit_enum_binding()
    {
        Livewire::test(ComponentWithEnumBindings::class, [
            'enum' => EnumToBeBound::FIRST,
        ])->assertSeeText('enum-first')->assertSeeText('param-default');

        Livewire::test(ComponentWithEnumBindings::class, [
            'enum' => EnumToBeBound::FIRST,
            'param' => 'foo',
        ])->assertSeeText('enum-first')->assertSeeText('foo');
    }

    /** @test */
    public function mount_method_receives_implicit_model_binding()
    {
        Livewire::test(ComponentWithBindings::class, [
            'model' => 'new model',
        ])->assertSeeText('new model:param-default');

        Livewire::test(ComponentWithBindings::class, [
            'model' => 'new model',
            'param' => 'foo',
        ])->assertSeeText('new model:foo');

        Livewire::test(ComponentWithBindings::class, [
            'new model',
            'foo',
        ])->assertSeeText('new model:foo');

        Livewire::test(ComponentWithBindings::class, [
            'foo',
            'model' => 'new model',
        ])->assertSeeText('new model:foo');
    }

    /** @test */
    public function mount_method_receives_implicit_enum_binding()
    {
        Livewire::test(ComponentWithEnumBindings::class, [
            'enum' => 'enum-first',
        ])->assertSeeText('enum-first:param-default');

        Livewire::test(ComponentWithEnumBindings::class, [
            'enum' => 'enum-first',
            'param' => 'foo',
        ])->assertSeeText('enum-first:foo');

        Livewire::test(ComponentWithEnumBindings::class, [
            'enum' => 'enum-first',
            'foo',
        ])->assertSeeText('enum-first:foo');

        Livewire::test(ComponentWithEnumBindings::class, [
            'foo',
            'enum' => 'enum-first',
        ])->assertSeeText('enum-first:foo');
    }

    /** @test */
    public function mount_method_receives_route_and_implicit_model_binding_and_dependency_injection()
    {
        Livewire::test(ComponentWithMountInjections::class, [
            'foo',
            'model' => 'new model',
        ])->assertSeeText('http://localhost/some-url:new model:foo');

        Livewire::component(ComponentWithMountInjections::class);

        Route::get('/foo/{model}', ComponentWithMountInjections::class);

        $this->withoutExceptionHandling()->get('/foo/route-model')->assertSeeText('http://localhost/some-url:route-model:param-default');
    }

    /** @test */
    public function mount_method_receives_route_and_implicit_enum_binding_and_dependency_injection()
    {
        Livewire::test(ComponentWithEnumMountInjections::class, [
            'foo',
            'enum' => 'enum-first',
        ])->assertSeeText('http://localhost/some-url:enum-first:foo');

        Livewire::component(ComponentWithEnumMountInjections::class);

        Route::get('/foo/{enum}', ComponentWithEnumMountInjections::class);

        $this->withoutExceptionHandling()->get('/foo/enum-first')->assertSeeText('http://localhost/some-url:enum-first:param-default');
    }

    /** @test */
    public function mount_method_receives_route_and_implicit_enum_nullable_binding_and_dependency_injection()
    {
        Livewire::test(ComponentWithEnumMountInjections::class, [
            'foo',
            'enum' => null,
        ])->assertSeeText('http://localhost/some-url:foo');

        Livewire::component(ComponentWithEnumMountInjections::class);

        Route::get('/foo/{enum}', ComponentWithEnumMountInjections::class);

        $this->withoutExceptionHandling()->get('/foo/enum-first')->assertSeeText('http://localhost/some-url:enum-first:param-default');
    }

    /** @test */
    public function action_receives_implicit_model_binding()
    {
        $component = Livewire::test(ComponentWithBindings::class)
            ->assertSee('model-default');

        $component->runAction('actionWithModel', 'implicitly bound');
        $this->assertEquals('implicitly bound:param-default', $component->name);

        $component->runAction('actionWithModel', 'implicitly bound', 'foo');
        $this->assertEquals('implicitly bound:foo', $component->name);
    }

    /** @test */
    public function action_receives_implicit_model_binding_independent_of_parameter_order()
    {
        $component = Livewire::test(ComponentWithBindings::class)
            ->assertSee('model-default');

        $component->runAction('actionWithModelAsSecond', 'bar', 'implicitly bound');
        $this->assertEquals('bar:implicitly bound:param-default', $component->name);

        $component->runAction('actionWithModelAsSecond', 'bar', 'implicitly bound', 'foo');
        $this->assertEquals('bar:implicitly bound:foo', $component->name);
    }

    /** @test */
    public function action_implicit_model_binding_plays_well_with_dependency_injection()
    {
        $component = Livewire::test(ComponentWithBindings::class)
            ->assertSee('model-default');

        $component->runAction('actionWithModelAndDependency', 'implicitly bound');
        $this->assertEquals('implicitly bound:http://localhost/some-url/param-default', $component->name);

        $component->runAction('actionWithModelAndDependency', 'implicitly bound', 'foo');
        $this->assertEquals('implicitly bound:http://localhost/some-url/foo', $component->name);
    }

    /** @test */
    public function action_receives_implicit_enum_binding()
    {
        $component = Livewire::test(ComponentWithEnumBindings::class, ['enum' => EnumToBeBound::FIRST])
            ->assertSee('enum-first:param-default');

        $component->runAction('actionWithEnum', 'enum-first');
        $this->assertEquals('enum-first:param-default', $component->name);

        $component->runAction('actionWithEnum', 'enum-first', 'foo');
        $this->assertEquals('enum-first:foo', $component->name);
    }

    /** @test */
    public function action_receives_implicit_enum_binding_independent_of_parameter_order()
    {
        $component = Livewire::test(ComponentWithEnumBindings::class, ['enum' => EnumToBeBound::FIRST])
            ->assertSee('enum-first:param-default');

        $component->runAction('actionWithEnumAsSecond', 'bar', 'enum-first');
        $this->assertEquals('bar:enum-first:param-default', $component->name);

        $component->runAction('actionWithEnumAsSecond', 'bar', 'enum-first', 'foo');
        $this->assertEquals('bar:enum-first:foo', $component->name);
    }

    /** @test */
    public function action_implicit_enum_binding_plays_well_with_dependency_injection()
    {
        $component = Livewire::test(ComponentWithEnumBindings::class, ['enum' => EnumToBeBound::FIRST])
            ->assertSee('enum-first:param-default');

        $component->runAction('actionWithEnumAndDependency', 'enum-first');
        $this->assertEquals('enum-first:http://localhost/some-url/param-default', $component->name);

        $component->runAction('actionWithEnumAndDependency', 'enum-first', 'foo');
        $this->assertEquals('enum-first:http://localhost/some-url/foo', $component->name);
    }
}

class ModelToBeBound extends Model
{
    public $value;

    public function __construct($value = 'model-default')
    {
        $this->value = $value;
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $this->value = $value;
        return $this;
    }
}

enum EnumToBeBound: string
{
    case FIRST = 'enum-first';
}

class ComponentWithBindings extends Component
{
    public $name;

    public function mount(ModelToBeBound $model, $param = 'param-default')
    {
        $this->name = collect([$model->value, $param])->join(':');
    }

    public function actionWithModel(ModelToBeBound $model, $param = 'param-default')
    {
        $this->name = collect([$model->value, $param])->join(':');
    }

    public function actionWithModelAsSecond($foo, ModelToBeBound $model, $param = 'param-default')
    {
        $this->name = collect([$foo, $model->value, $param])->join(':');
    }

    public function actionWithModelAndDependency(UrlGenerator $generator, ModelToBeBound $model, $param = 'param-default')
    {
        $this->name = collect([$model->value, $generator->to('/some-url/' . $param)])->join(':');
    }

    public function render()
    {
        return app('view')->make('show-name-with-this');
    }
}

class ComponentWithEnumBindings extends Component
{
    public $name;

    public function mount(EnumToBeBound $enum, $param = 'param-default')
    {
        $this->name = collect([$enum->value, $param])->join(':');
    }

    public function actionWithEnum(EnumToBeBound $enum, $param = 'param-default')
    {
        $this->name = collect([$enum->value, $param])->join(':');
    }

    public function actionWithEnumAsSecond($foo, EnumToBeBound $enum, $param = 'param-default')
    {
        $this->name = collect([$foo, $enum->value, $param])->join(':');
    }

    public function actionWithEnumAndDependency(UrlGenerator $generator, EnumToBeBound $enum, $param = 'param-default')
    {
        $this->name = collect([$enum->value, $generator->to('/some-url/' . $param)])->join(':');
    }

    public function render()
    {
        return app('view')->make('show-name-with-this');
    }
}

class ComponentWithMountInjections extends Component
{
    public $name;

    public function mount(UrlGenerator $generator, ModelToBeBound $model, $param = 'param-default')
    {
        $this->name = collect([$generator->to('/some-url'), $model->value, $param])->join(':');
    }

    public function render()
    {
        return app('view')->make('show-name-with-this');
    }
}

class ComponentWithEnumMountInjections extends Component
{
    public $name;

    public function mount(UrlGenerator $generator, ?EnumToBeBound $enum, $param = 'param-default')
    {
        $this->name = collect([$generator->to('/some-url'), $enum?->value, $param])->filter(fn($value) => !is_null($value))->join(':');
    }

    public function render()
    {
        return app('view')->make('show-name-with-this');
    }
}
