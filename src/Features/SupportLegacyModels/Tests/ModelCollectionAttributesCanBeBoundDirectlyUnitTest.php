<?php

namespace Livewire\Features\SupportLegacyModels\Tests;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\Features\SupportLegacyModels\CannotBindToModelDataWithoutValidationRuleException;
use Livewire\Livewire;
use Livewire\Mechanisms\HandleComponents\CorruptComponentPayloadException;
use Sushi\Sushi;

use function Livewire\invade;

class ModelCollectionAttributesCanBeBoundDirectlyUnitTest extends \Tests\TestCase
{
    use Concerns\EnableLegacyModels;

    public function setUp(): void
    {
        parent::setUp();

        Schema::create('model_for_bindings', function ($table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->timestamps();
        });

        ModelForBinding::create(['title' => 'foo']);
        ModelForBinding::create(['title' => 'bar']);
        ModelForBinding::create(['title' => 'baz']);
    }

    public function tearDown(): void
    {
        ModelForBinding::truncate();
    }

    /** @test */
    public function can_set_a_model_attribute_inside_a_models_collection_and_save()
    {
        Livewire::test(ComponentWithModelCollectionProperty::class)
            ->assertSet('models.0.title', 'foo')
            ->assertSnapshotSet('models.0.title', 'foo')
            ->set('models.0.title', 'bo')
            ->assertSet('models.0.title', 'bo')
            ->call('refreshModels')
            ->assertSet('models.0.title', 'foo')
            ->set('models.0.title', 'bo')
            ->call('save')
            ->assertHasErrors('models.0.title')
            ->set('models.0.title', 'boo')
            ->call('save')
            ->call('refreshModels')
            ->assertSet('models.0.title', 'boo');
    }

    /** @test */
    public function can_set_non_persisted_models_in_model_collection()
    {
        Livewire::test(ComponentWithModelCollectionProperty::class)
            ->assertSet('models.2.title', 'baz')
            ->assertSet('models.3', null)
            ->assertSnapshotSet('models.3', null)
            ->call('addModel')
            ->assertNotSet('models.3', null)
            ->assertSnapshotNotSet('models.3', null)
            ->set('models.3.title', 'bob')
            ->assertSet('models.3.title', 'bob')
            ->assertSnapshotSet('models.3.title', 'bob')
            ->set('models.3.title', 'bo')
            ->call('refreshModels')
            ->assertSet('models.3', null)
            ->assertSnapshotSet('models.3', null)
            ->call('addModel')
            ->set('models.3.title', 'bo')
            ->call('save')
            ->assertHasErrors('models.3.title')
            ->set('models.3.title', 'boo')
            ->call('save')
            ->call('refreshModels')
            ->assertSet('models.3.title', 'boo');
        ;
    }

    /** @test */
    public function can_set_non_persisted_models_in_empty_model_collection()
    {
        ModelForBinding::truncate();

        Livewire::test(ComponentWithModelCollectionProperty::class)
            ->call('addModelWithNullConnection')
            ->set('models.0.title', 'foo')
            ->assertOk()
        ;
    }

    /** @test */
    public function can_use_a_custom_model_collection_and_bind_to_values()
    {
        Livewire::test(ComponentWithModelCollectionProperty::class)
            ->call('setModelsToCustomCollection')
            ->assertSet('models.0.title', 'foo')
            ->assertSnapshotSet('models.0.title', 'foo')
            ->set('models.0.title', 'bo')
            ->assertSet('models.0.title', 'bo')
            ->call('refreshModels')
            ->assertSet('models.0.title', 'foo')
            ->set('models.0.title', 'bo')
            ->call('save')
            ->assertHasErrors('models.0.title')
            ->set('models.0.title', 'boo')
            ->call('save')
            ->call('refreshModels')
            ->assertSet('models.0.title', 'boo')
            ->call('getTypeOfModels')
            ->assertSet('modelsType', CustomCollection::class);
    }

    /** @test */
    public function cant_set_a_model_attribute_that_isnt_present_in_rules_array()
    {
        $this->expectException(CannotBindToModelDataWithoutValidationRuleException::class);

        Livewire::test(ComponentWithModelCollectionProperty::class)
            ->set('models.1.restricted', 'bar')
            ->assertSet('models.1.restricted', null);
    }

    /** @test */
    public function an_eloquent_models_meta_cannot_be_hijacked_by_tampering_with_data()
    {
        $this->expectException(CorruptComponentPayloadException::class);

        $component = Livewire::test(ComponentWithModelCollectionProperty::class);

        invade(invade($component)->lastState)->snapshot['data']['models'][0]['id'] = 2;

        $component->call('$refresh');
    }
}

class ModelForBinding extends Model
{
    protected $connection = 'testbench';
    protected $guarded = [];

    // New models don't have a connection name, so we need to mimic that.
    public function mimicNewModel()
    {
        $this->connection = null;
    }
}

class CustomCollection extends EloquentCollection
{
    //
}

class ModelWithCustomCollectionForBinding extends Model
{
    use Sushi;

    protected $rows = [
        ['title' => 'foo'],
        ['title' => 'bar'],
        ['title' => 'baz'],
    ];

    public function newCollection(array $models = [])
    {
        return new CustomCollection($models);
    }
}

class ComponentWithModelCollectionProperty extends Component
{
    public $models;
    public $modelsType;

    protected $rules = [
        'models.*.title' => 'required|min:3',
    ];

    public function mount()
    {
        $this->models = ModelForBinding::all();
    }

    public function addModel()
    {
        $this->models->push(new ModelForBinding);
    }

    public function addModelWithNullConnection()
    {
        $model = new ModelForBinding;
        $model->mimicNewModel();
        $this->models->push($model);
    }

    public function setModelsToCustomCollection()
    {
        $this->models = ModelWithCustomCollectionForBinding::all();
    }

    public function getTypeOfModels()
    {
        $this->modelsType = get_class($this->models);
    }

    public function save()
    {
        $this->validate();

        $this->models->each->save();
    }

    public function refreshModels()
    {
        $this->models = $this->models->filter->exists->fresh();
    }

    public function render()
    {
        return view('null-view');
    }
}
