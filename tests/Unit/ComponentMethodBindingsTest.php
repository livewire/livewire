<?php

namespace Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\Livewire;

class ComponentMethodBindingsTest extends TestCase
{
    /** @test */
    public function mount_method_receives_explicit_binding()
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
    public function mount_method_receives_implicit_binding()
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
    public function mount_method_receives_route_and_implicit_binding_and_dependency_injection()
    {
        Livewire::test(ComponentWithMountInjections::class, [
            'foo',
            'model' => 'new model',
        ])->assertSeeText('http://localhost/some-url:new model:foo');

        Livewire::component(ComponentWithMountInjections::class);

        Route::get('/foo/{model}', ComponentWithMountInjections::class);

        $this->get('/foo/route-model')->assertSeeText('http://localhost/some-url:route-model:param-default');
    }

    /** @test */
    public function action_receives_implicit_binding()
    {
        $component = Livewire::test(ComponentWithBindings::class)
            ->assertSee('model-default');

        $component->runAction('actionWithModel', 'implicitly bound');
        $this->assertEquals('implicitly bound:param-default', $component->name);

        $component->runAction('actionWithModel', 'implicitly bound', 'foo');
        $this->assertEquals('implicitly bound:foo', $component->name);
    }

    /** @test */
    public function action_receives_implicit_binding_independent_of_parameter_order()
    {
        $component = Livewire::test(ComponentWithBindings::class)
            ->assertSee('model-default');

        $component->runAction('actionWithModelAsSecond', 'bar', 'implicitly bound');
        $this->assertEquals('bar:implicitly bound:param-default', $component->name);

        $component->runAction('actionWithModelAsSecond', 'bar', 'implicitly bound', 'foo');
        $this->assertEquals('bar:implicitly bound:foo', $component->name);
    }

    /** @test */
    public function action_implicit_binding_plays_well_with_dependency_injection()
    {
        $component = Livewire::test(ComponentWithBindings::class)
            ->assertSee('model-default');

        $component->runAction('actionWithModelAndDependency', 'implicitly bound');
        $this->assertEquals('implicitly bound:http://localhost/some-url/param-default', $component->name);

        $component->runAction('actionWithModelAndDependency', 'implicitly bound', 'foo');
        $this->assertEquals('implicitly bound:http://localhost/some-url/foo', $component->name);
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
        $this->name = collect([$model->value, $generator->to('/some-url/'.$param)])->join(':');
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
