<?php

namespace Tests;

use Exception;
use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\Livewire;

class RouteParameterTest extends TestCase
{
    /** @test */
    public function can_pass_parameters_to_component_file()
    {
        Livewire::component('foo', ComponentForRouteParameter::class);

        Route::livewire('/foo/{first_name}/{last_name}', 'foo');

        try {
            $this->withoutExceptionHandling()->get('/foo/John/Doe');
        } catch (Exception $e) {
            $this->assertEquals("JohnDoe", explode(' ', $e->getMessage())[0] . explode(' ', $e->getMessage())[1]);
        }
    }

    /** @test */
    public function can_use_camel_case_parameter_for_declaring_route()
    {
        Livewire::component('foo', ComponentForRouteParameter::class);

        Route::livewire('/foo/{firstName}', 'foo');

        try {
            $this->withoutExceptionHandling()->get('/foo/John')->ass;
        } catch (Exception $e) {
            $this->assertEquals("John", explode(' ', $e->getMessage())[0]);
        }
    }

    /** @test */
    public function when_wrong_parameter_is_passed_get_error()
    {
        Livewire::component('foo', ComponentForRouteParameter::class);

        Route::livewire('/foo/{wrong_parameter}', 'foo');

        try {
            $this->withoutExceptionHandling()->get('/foo/John')->ass;
        } catch (Exception $e) {
            $this->assertNotEquals("John", explode(' ', $e->getMessage())[0]);
        }
    }
}

class ComponentForRouteParameter extends Component
{
    public $name;
    public $last_name;

    public function mount($first_name, $last_name = null)
    {
        $this->name = $first_name;
        $this->last_name = $last_name ?? null;
    }

    public function render()
    {
        throw new Exception($this->name . ' ' . $this->last_name);
    }
}
