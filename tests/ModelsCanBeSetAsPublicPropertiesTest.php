<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\Exceptions\CannotBindDataToEloquentModelException;
use Livewire\Exceptions\CorruptComponentPayloadException;
use Livewire\Livewire;

class ModelsCanBeSetAsPublicPropertiesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Schema::create('model_for_serializations', function ($table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->timestamps();
        });
    }

    /** @test */
    public function an_eloquent_model_can_be_set_as_a_public_property()
    {
        $model = ModelForSerialization::create(['id' => 1, 'title' => 'foo']);

        Livewire::test(ComponentWithModelPublicProperty::class, ['model' => $model])
            ->assertSee('foo')
            ->call('refresh')
            ->assertSee('foo');
    }

    /** @test */
    public function an_eloquent_model_cannot_be_hijacked_by_binding_to_id_data()
    {
        $this->expectException(CannotBindDataToEloquentModelException::class);

        $model = ModelForSerialization::create(['id' => 1, 'title' => 'foo']);
        ModelForSerialization::create(['id' => 2, 'title' => 'bar']);

        Livewire::test(ComponentWithModelPublicProperty::class, ['model' => $model])
            ->set('model.id', 2)
            ->call('refresh')
            ->assertSee('foo')
            ->assertSee('bar');
    }

    /** @test */
    public function an_eloquent_model_cannot_be_hijacked_by_tampering_with_data()
    {
        $this->expectException(CorruptComponentPayloadException::class);

        $model = ModelForSerialization::create(['id' => 1, 'title' => 'foo']);
        ModelForSerialization::create(['id' => 2, 'title' => 'bar']);

        $component = Livewire::test(ComponentWithModelPublicProperty::class, ['model' => $model]);

        $component->payload['data']['model']['id'] = 2;

        $component->call('refresh');
    }

    /** @test */
    public function an_eloquent_model_collection_can_be_set_as_a_public_property()
    {
        ModelForSerialization::create(['id' => 1, 'title' => 'foo']);
        ModelForSerialization::create(['id' => 2, 'title' => 'bar']);

        $models = ModelForSerialization::all();

        Livewire::test(ComponentWithModelsPublicProperty::class, ['models' => $models])
            ->assertSee('foo')
            ->assertSee('bar')
            ->call('refresh')
            ->assertSee('foo')
            ->assertSee('bar');
    }

    /** @test */
    public function a_sorted_eloquent_model_collection_can_be_set_as_a_public_property()
    {
        ModelForSerialization::create(['id' => 1, 'title' => 'foo']);
        ModelForSerialization::create(['id' => 2, 'title' => 'bar']);

        $models = ModelForSerialization::all()->sortKeysDesc();

        $component = Livewire::test(ComponentWithModelsPublicProperty::class, ['models' => $models]);

        $this->assertEquals([2, 1], $component->payload['data']['models']['id']);

        $component ->call('refresh');

        $this->assertEquals([2, 1], $component->payload['data']['models']['id']);
    }
}

class ModelForSerialization extends Model
{
    protected $connection = 'testbench';
    protected $guarded = [];
}

class ComponentWithModelPublicProperty extends Component
{
    public $model;

    public function mount(ModelForSerialization $model)
    {
        $this->model = $model;
    }

    public function refresh() {}

    public function render()
    {
        return view('model-arrow-title-view');
    }
}

class ComponentWithModelsPublicProperty extends Component
{
    public $models;

    public function mount($models)
    {
        $this->models = $models;
    }

    public function refresh() {}

    public function render()
    {
        return view('foreach-models-arrow-title-view');
    }
}
