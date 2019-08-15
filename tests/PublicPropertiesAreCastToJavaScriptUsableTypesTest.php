<?php

namespace Tests;

use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Exceptions\CannotAddEloquentModelsAsPublicPropertyException;

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
