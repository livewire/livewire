<?php

namespace Livewire\Features\SupportLegacyModels\Tests;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Livewire\Features\SupportLegacyModels\CannotBindToModelDataWithoutValidationRuleException;
use Livewire\Livewire;
use Livewire\Mechanisms\HandleComponents\CorruptComponentPayloadException;
use Sushi\Sushi;

use Tests\TestComponent;
use function Livewire\invade;

class ModelCollectionAttributesCanBeBoundDirectlyUnitTest extends \Tests\TestCase
{
    use Concerns\EnableLegacyModels;

    public function test_can_set_a_model_attribute_inside_a_models_collection_and_save()
    {
        // Reset Sushi model.
        (new ModelForBinding)->resolveConnection()->getSchemaBuilder()->drop((new ModelForBinding)->getTable());
        (new ModelForBinding)->migrate();

        Livewire::test(ComponentWithModelCollectionProperty::class)
            ->assertSetStrict('models.0.title', 'foo')
            ->assertSnapshotSetStrict('models.0.title', 'foo')
            ->set('models.0.title', 'bo')
            ->assertSetStrict('models.0.title', 'bo')
            ->call('refreshModels')
            ->assertSetStrict('models.0.title', 'foo')
            ->set('models.0.title', 'bo')
            ->call('save')
            ->assertHasErrors('models.0.title')
            ->set('models.0.title', 'boo')
            ->call('save')
            ->call('refreshModels')
            ->assertSetStrict('models.0.title', 'boo');
    }

    public function test_can_set_non_persisted_models_in_model_collection()
    {
        // Reset Sushi model.
        (new ModelForBinding)->resolveConnection()->getSchemaBuilder()->drop((new ModelForBinding)->getTable());
        (new ModelForBinding)->migrate();

        Livewire::test(ComponentWithModelCollectionProperty::class)
            ->assertSetStrict('models.2.title', 'baz')
            ->assertSetStrict('models.3', null)
            ->assertSnapshotSetStrict('models.3', null)
            ->call('addModel')
            ->assertNotSet('models.3', null)
            ->assertSnapshotNotSet('models.3', null)
            ->set('models.3.title', 'bob')
            ->assertSetStrict('models.3.title', 'bob')
            ->assertSnapshotSetStrict('models.3.title', 'bob')
            ->set('models.3.title', 'bo')
            ->call('refreshModels')
            ->assertSetStrict('models.3', null)
            ->assertSnapshotSetStrict('models.3', null)
            ->call('addModel')
            ->set('models.3.title', 'bo')
            ->call('save')
            ->assertHasErrors('models.3.title')
            ->set('models.3.title', 'boo')
            ->call('save')
            ->call('refreshModels')
            ->assertSetStrict('models.3.title', 'boo');
        ;
    }

    public function test_can_use_a_custom_model_collection_and_bind_to_values()
    {
        // Reset Sushi model.
        (new ModelWithCustomCollectionForBinding)->resolveConnection()->getSchemaBuilder()->drop((new ModelWithCustomCollectionForBinding)->getTable());
        (new ModelWithCustomCollectionForBinding)->migrate();

        Livewire::test(ComponentWithModelCollectionProperty::class)
            ->call('setModelsToCustomCollection')
            ->assertSetStrict('models.0.title', 'foo')
            ->assertSnapshotSetStrict('models.0.title', 'foo')
            ->set('models.0.title', 'bo')
            ->assertSetStrict('models.0.title', 'bo')
            ->call('refreshModels')
            ->assertSetStrict('models.0.title', 'foo')
            ->set('models.0.title', 'bo')
            ->call('save')
            ->assertHasErrors('models.0.title')
            ->set('models.0.title', 'boo')
            ->call('save')
            ->call('refreshModels')
            ->assertSetStrict('models.0.title', 'boo')
            ->call('getTypeOfModels')
            ->assertSetStrict('modelsType', CustomCollection::class);
    }

    public function test_cant_set_a_model_attribute_that_isnt_present_in_rules_array()
    {
        // Reset Sushi model.
        (new ModelForBinding)->resolveConnection()->getSchemaBuilder()->drop((new ModelForBinding)->getTable());
        (new ModelForBinding)->migrate();

        $this->expectException(CannotBindToModelDataWithoutValidationRuleException::class);

        Livewire::test(ComponentWithModelCollectionProperty::class)
            ->set('models.1.restricted', 'bar')
            ->assertSetStrict('models.1.restricted', null);
    }

    public function test_an_eloquent_models_meta_cannot_be_hijacked_by_tampering_with_data()
    {
        // Reset Sushi model.
        (new ModelForBinding)->resolveConnection()->getSchemaBuilder()->drop((new ModelForBinding)->getTable());
        (new ModelForBinding)->migrate();

        $this->expectException(CorruptComponentPayloadException::class);

        $component = Livewire::test(ComponentWithModelCollectionProperty::class);

        invade(invade($component)->lastState)->snapshot['data']['models'][0]['id'] = 2;

        $component->call('$refresh');
    }
}

class ModelForBinding extends Model
{
    use Sushi;

    protected $rows = [
        ['title' => 'foo'],
        ['title' => 'bar'],
        ['title' => 'baz'],
    ];
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

class ComponentWithModelCollectionProperty extends TestComponent
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
}
