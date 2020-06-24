<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\Livewire;

class ComponentRouteBindingsParameterNameCaseTest extends TestCase
{
    public function setUp() : void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    /** @test */
    public function can_use_camel_route_parameter_to_snake_or_camel_method_parameter()
    {
        // Laravel behavior
        Route::get('/camel/{firstName}/{lastName}', function ($firstName, $last_name) {
            return response()->json(func_get_args());
        });

        $this->get('/camel/first/last')->assertExactJson(['first', 'last']);

        // Matching Livewire behaviour
        Livewire::component('foo', ComponentWithMixedCaseMount::class);

        Route::livewire('/component/camel/{firstName}/{lastName}', 'foo');

        $this->get('/component/camel/first/last')->assertSeeText('first last');
    }

    /** @test */
    public function can_use_snake_route_parameter_to_snake_or_camel_method_parameter()
    {
        // Laravel behavior
        Route::get('/snake/{first_name}/{last_name}', function ($firstName, $last_name) {
            return response()->json(func_get_args());
        });

        $this->get('/snake/first/last')->assertExactJson(['first', 'last']);

        // Matching Livewire behaviour
        Livewire::component('foo', ComponentWithMixedCaseMount::class);

        Route::livewire('/component/snake/{first_name}/{last_name}', 'foo');

        $this->get('/component/snake/first/last')->assertSeeText('first last');
    }

    /** @test */
    public function can_do_camel_route_parameter_to_camel_method_parmeter_implicit_model_binding()
    {
        // Laravel behavior
        Route::middleware('bindings')->get('/controller/camel/{firstName}/{lastName}', ParameterCamelBindingController::class);

        $this->get('/controller/camel/first/last')->assertExactJson(['first', 'last']);

        // Matching Livewire behaviour
        Livewire::component('foo', ComponentWithCamelMountBinding::class);

        Route::livewire('/component/snake/{firstName}/{lastName}', 'foo');

        $this->get('/component/snake/first/last')->assertSeeText('first last');
    }

    /** @test */
    public function can_do_snake_route_parameter_to_snake_method_parameter_implicit_model_binding()
    {
        // Laravel behavior
        Route::middleware('bindings')->get('/controller/snake/{first_name}/{last_name}', ParameterSnakeBindingController::class);

        $this->get('/controller/snake/first/last')->assertExactJson(['first', 'last']);

        // Matching Livewire behaviour
        Livewire::component('foo', ComponentWithSnakeMountBinding::class);

        Route::livewire('/component/snake/{first_name}/{last_name}', 'foo');

        $this->get('/component/snake/first/last')->assertSeeText('first last');
    }

    /** @test */
    public function can_do_snake_route_parameter_to_camel_method_parameter_implicit_model_binding()
    {
        // Laravel behavior
        Route::middleware('bindings')->get('/mixed/snake/camel/{first_name}/{last_name}', ParameterCamelBindingController::class);

        $this->get('/mixed/snake/camel/first/last')->assertExactJson(['first', 'last']);

        // Matching Livewire behaviour
        Livewire::component('foo', ComponentWithCamelMountBinding::class);

        Route::livewire('/component/snake/{first_name}/{last_name}', 'foo');

        $this->get('/component/snake/first/last')->assertSeeText('first last');
    }

    /** @test */
    public function unlike_laravel_can_do_camel_route_parameter_to_snake_method_parameter_implicit_model_binding()
    {
        // Laravel behavior
        Route::middleware('bindings')->get('/mixed/camel/snake/{firstName}/{lastName}', ParameterSnakeBindingController::class);

        $this->get('/mixed/camel/snake/first/last')->assertExactJson([null, null]);

        // Matching Livewire behaviour
        Livewire::component('foo', ComponentWithSnakeMountBinding::class);

        Route::livewire('/component/snake/{firstName}/{lastName}', 'foo');

        $this->get('/component/snake/first/last')->assertSeeText('first last');
    }

    /** @test */
    public function can_not_use_kebab_route_parameter()
    {
        // Laravel behavior
        Route::get('/kebab/{first-name}/{last-name}', function ($firstName, $last_name) {
            return response()->json(func_get_args());
        });

        $this->withExceptionHandling()->get('/kebab/first/last')->assertNotFound();

        // Matching Livewire behaviour
        Livewire::component('foo', ComponentWithMixedCaseMount::class);

        Route::livewire('/component/kebab/{first-name}/{last-name}', 'foo');

        $this->withExceptionHandling()->get('/component/kebab/first/last')->assertNotFound();
    }

    /** @test */
    public function can_not_use_kebab_route_parameter_for_implicit_model_binding()
    {
        // Laravel behavior
        Route::middleware('bindings')->get('/controller/kebab/{first-name}/{last-name}', ParameterCamelBindingController::class);

        $this->withExceptionHandling()->get('/controller/kebab/first/last')->assertNotFound();

        // Matching Livewire behaviour
        Livewire::component('foo', ComponentWithCamelMountBinding::class);

        Route::livewire('/component/kebab/{first-name}/{last-name}', 'foo');

        $this->withExceptionHandling()->get('/component/kebab/first/last')->assertNotFound();
    }
}

class ComponentWithMixedCaseMount extends Component
{
    public $name;

    public function mount($firstName, $last_name)
    {
        $this->name = collect(func_get_args())->join(' ');
    }

    public function render()
    {
        return view('show-name');
    }
}

class ComponentWithSnakeMount extends Component
{
    public $name;

    public function mount($first_name, $last_name)
    {
        $this->name = collect(func_get_args())->join(' ');
    }

    public function render()
    {
        return view('show-name');
    }
}

class ComponentWithCamelMountBinding extends Component
{
    public $name;

    public function mount(ParamterNameCaseTestModel $firstName, ParamterNameCaseTestModel2 $lastName)
    {
        $this->name = collect(func_get_args())->map(function ($m) { return $m->value; })->join(' ');
    }

    public function render()
    {
        return view('show-name');
    }
}

class ComponentWithSnakeMountBinding extends Component
{
    public $name;

    public function mount(ParamterNameCaseTestModel $first_name, ParamterNameCaseTestModel2 $last_name)
    {
        $this->name = collect(func_get_args())->map(function ($m) { return $m->value; })->join(' ');
    }

    public function render()
    {
        return view('show-name');
    }
}

class ParameterSnakeBindingController extends Controller
{
    public function __invoke(ParamterNameCaseTestModel $first_name, ParamterNameCaseTestModel2 $last_name)
    {
        return response()->json([$first_name->value, $last_name->value]);
    }
}

class ParameterCamelBindingController extends Controller
{
    public function __invoke(ParamterNameCaseTestModel $firstName, ParamterNameCaseTestModel2 $lastName)
    {
        return response()->json([$firstName->value, $lastName->value]);
    }
}

class ParamterNameCaseTestModel extends Model
{
    public $value;

    public function resolveRouteBinding($value, $field = null)
    {
        $this->value = $value;
        return $this;
    }
}

class ParamterNameCaseTestModel2 extends ParamterNameCaseTestModel {}
