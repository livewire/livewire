<?php

namespace Livewire\Features\SupportLegacyModels\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\Features\SupportLegacyModels\CannotBindToModelDataWithoutValidationRuleException;
use Livewire\Livewire;
use Livewire\Mechanisms\HandleComponents\CorruptComponentPayloadException;

use Tests\TestComponent;
use function Livewire\invade;

class ModelAttributesCanBeBoundDirectlyUnitTest extends \Tests\TestCase
{
    use Concerns\EnableLegacyModels;

    public function setUp(): void
    {
        parent::setUp();

        Schema::create('model_for_attribute_bindings', function ($table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->timestamps();
        });
    }

    public function test_can_set_a_model_attribute_and_save()
    {
        $model = ModelForAttributeBinding::create(['id' => 1, 'title' => 'foo']);

        Livewire::test(ComponentWithModelProperty::class, ['model' => $model])
            ->assertSetStrict('model.title', 'foo')
            ->set('model.title', 'ba')
            ->assertSetStrict('model.title', 'ba')
            ->call('refreshModel')
            ->assertSetStrict('model.title', 'foo')
            ->set('model.title', 'ba')
            ->call('save')
            ->assertHasErrors('model.title')
            ->set('model.title', 'bar')
            ->call('save')
            ->call('refreshModel')
            ->assertSetStrict('model.title', 'bar');
    }


    public function test_a_non_existant_eloquent_model_can_be_set()
    {
        $model = new ModelForAttributeBinding;

        Livewire::test(ComponentWithModelProperty::class, ['model' => $model])
            ->assertNotSet('model.title', 'foo')
            ->set('model.title', 'i-exist-now')
            ->assertSetStrict('model.title', 'i-exist-now')
            ->call('save')
            ->assertSetStrict('model.title', 'i-exist-now');

        $this->assertTrue(ModelForAttributeBinding::whereTitle('i-exist-now')->exists());
    }

    public function test_cant_set_a_model_attribute_that_isnt_present_in_rules_array()
    {
        $this->expectException(CannotBindToModelDataWithoutValidationRuleException::class);

        $model = ModelForAttributeBinding::create(['id' => 1, 'title' => 'foo']);

        Livewire::test(ComponentWithModelProperty::class, ['model' => $model])
            ->set('model.id', 2)
            ->assertSetStrict('model.id', null);
    }

    public function test_an_eloquent_models_meta_cannot_be_hijacked_by_tampering_with_data()
    {
        $this->expectException(CorruptComponentPayloadException::class);

        $model = ModelForAttributeBinding::create(['id' => 1, 'title' => 'foo']);
        ModelForAttributeBinding::create(['id' => 2, 'title' => 'bar']);

        $component = Livewire::test(ComponentWithModelProperty::class, ['model' => $model]);

        invade(invade($component)->lastState)->snapshot['data']['model'][1]['key'] = 2;

        $component->call('$refresh');
    }

    public function test_an_eloquent_model_property_can_be_set_to_null()
    {
        $model = ModelForAttributeBinding::create(['id' => 1, 'title' => 'foo']);

        Livewire::test(ComponentWithModelProperty::class, ['model' => $model])
            ->set('model', null)
            ->assertSuccessful();
    }

    public function test_an_eloquent_model_property_can_be_set_to_boolean()
    {
        $model = ModelForAttributeBinding::create(['id' => 1, 'title' => 'foo']);

        Livewire::test(ComponentWithModelProperty::class, ['model' => $model])
            ->set('model', false)
            ->assertSuccessful();
    }
}

class ModelForAttributeBinding extends Model
{
    protected $connection = 'testbench';
    protected $guarded = [];
}

class ComponentWithModelProperty extends TestComponent
{
    public $model;

    protected $rules = [
        'model.title' => 'required|min:3',
    ];

    public function mount(ModelForAttributeBinding $model)
    {
        $this->model = $model;
    }

    public function save()
    {
        $this->validate();

        $this->model->save();
    }

    public function refreshModel()
    {
        $this->model->refresh();
    }
}

class ComponentWithoutRulesArray extends TestComponent
{
    public $model;

    public function mount(ModelForAttributeBinding $model)
    {
        $this->model = $model;
    }
}

class ComponentWithModelsProperty extends Component
{
    public $models;

    public function mount($models)
    {
        $this->models = $models;
    }

    public function refresh() {}

    public function render()
    {
        return <<<'HTML'
        <div>
            @foreach ($models as $model)
                {{ $model->title }}
            @endforeach
        </div>
        HTML;
    }
}
