<?php

namespace Tests;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Livewire\Component;
use Livewire\Exceptions\CannotAddEloquentModelsAsPublicPropertyException;
use Livewire\Livewire;

class PublicPropertiesAreCastToJavaScriptUsableTypesTest extends TestCase
{
    /** @test */
    public function collection_properties_are_cast_to_array()
    {
        Livewire::test(ComponentWithPropertiesStub::class, collect(['foo' => 'bar']))
            ->assertDontSee('class Illuminate\Support\Collection')
            ->assertSee('array(1)')
            ->assertSee('foo')
            ->assertSee('bar');
    }

    /** @test */
    public function exception_is_thrown_if_user_tries_to_set_public_property_to_model()
    {
        $this->expectException(CannotAddEloquentModelsAsPublicPropertyException::class);

        Livewire::test(ComponentWithPropertiesStub::class, new ModelStub);
    }

    /** @test */
    public function exception_is_thrown_if_user_tries_to_set_public_property_to_collection_of_models()
    {
        $this->expectException(CannotAddEloquentModelsAsPublicPropertyException::class);

        $collection = new Collection([new ModelStub]);
        Livewire::test(ComponentWithPropertiesStub::class, $collection);
    }

    /** @test */
    public function exception_is_thrown_and_not_caught_by()
    {
        $this->expectException(CannotAddEloquentModelsAsPublicPropertyException::class);
        Livewire::component('foo', ComponentWithPropertiesStub::class);

        View::make('render-component', ['component' => 'foo', 'params' => [new ModelStub]])->render();
    }
}

class ComponentWithPropertiesStub extends Component
{
    public $foo;

    public function mount($foo)
    {
        $this->foo = $foo;
    }

    public function render()
    {
        return app('view')->make('var-dump-foo');
    }
}

class ModelStub extends Model
{
    protected $guarded = [];
}
