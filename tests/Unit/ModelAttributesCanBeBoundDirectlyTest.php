<?php

namespace Tests\Unit;

use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Livewire\Exceptions\CorruptComponentPayloadException;
use Livewire\Exceptions\CannotBindToModelDataWithoutValidationRuleException;

class ModelAttributesCanBeBoundDirectlyTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Schema::create('model_for_attribute_bindings', function ($table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->timestamps();
        });

        Schema::create('model_for_attribute_castings', function ($table) {
            $table->bigIncrements('id');
            $table->date('castable_date');
            $table->date('non_castable_date');
            $table->timestamps();
        });
    }

    /** @test */
    public function can_set_a_model_attribute_and_save()
    {
        $model = ModelForAttributeBinding::create(['id' => 1, 'title' => 'foo']);

        Livewire::test(ComponentWithModelProperty::class, ['model' => $model])
            ->assertSet('model.title', 'foo')
            ->set('model.title', 'ba')
            ->assertSet('model.title', 'ba')
            ->call('refreshModel')
            ->assertSet('model.title', 'foo')
            ->set('model.title', 'ba')
            ->call('save')
            ->assertHasErrors('model.title')
            ->set('model.title', 'bar')
            ->call('save')
            ->call('refreshModel')
            ->assertSet('model.title', 'bar');
    }


    /** @test */
    public function a_non_existant_eloquent_model_can_be_set()
    {
        $model = new ModelForAttributeBinding;

        Livewire::test(ComponentWithModelProperty::class, ['model' => $model])
            ->assertNotSet('model.title', 'foo')
            ->set('model.title', 'i-exist-now')
            ->assertSet('model.title', 'i-exist-now')
            ->call('save')
            ->assertSet('model.title', 'i-exist-now');

        $this->assertTrue(ModelForAttributeBinding::whereTitle('i-exist-now')->exists());
    }

    /** @test */
    public function cant_set_a_model_attribute_that_isnt_present_in_rules_array()
    {
        $this->expectException(CannotBindToModelDataWithoutValidationRuleException::class);

        $model = ModelForAttributeBinding::create(['id' => 1, 'title' => 'foo']);

        Livewire::test(ComponentWithModelProperty::class, ['model' => $model])
            ->set('model.id', 2)
            ->assertSet('model.id', null);
    }

    /** @test */
    public function an_eloquent_models_meta_cannot_be_hijacked_by_tampering_with_data()
    {
        $this->expectException(CorruptComponentPayloadException::class);

        $model = ModelForAttributeBinding::create(['id' => 1, 'title' => 'foo']);
        ModelForAttributeBinding::create(['id' => 2, 'title' => 'bar']);

        $component = Livewire::test(ComponentWithModelProperty::class, ['model' => $model]);

        $component->payload['serverMemo']['dataMeta']['models']['model']['id'] = 2;

        $component->call('$refresh');
    }

    /** @test */
    public function attributes_can_be_casted_from_model_definition()
    {
        $model = ModelForAttributeCasting::create(['id' => 1, 'castable_date' => new \DateTime('2020-03-03'), 'non_castable_date' => new \DateTime('2015-10-21')]);

        $component = Livewire::test(ComponentForModelAttributeCasting::class, ['model' => $model]);

        $castable_date = $component->payload['serverMemo']['data']['model']['castable_date'];
        $this->assertIsString($castable_date);
        $this->assertSame($castable_date, '2020-03-03');

        $non_castable_date = $component->payload['serverMemo']['data']['model']['non_castable_date'];
        $this->assertIsObject($non_castable_date);
        $this->assertNotSame($non_castable_date, '2015-10-21');
    }
}

class ModelForAttributeBinding extends Model
{
    protected $connection = 'testbench';
    protected $guarded = [];
}

class ModelForAttributeCasting extends Model
{
    protected $connection = 'testbench';
    protected $guarded = [];

    protected $casts = [
        'castable_date' => 'date:Y-m-d'
    ];
}

class ComponentWithModelProperty extends Component
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

    public function render()
    {
        return view('null-view');
    }
}

class ComponentForModelAttributeCasting extends Component
{
    public $model;

    protected $rules = [
        'model.castable_date' => ['required'],
        'model.non_castable_date' => ['required']
    ];

    public function mount(ModelForAttributeCasting $model) {
        $this->model = $model;
    }

    public function render()
    {
        return view('null-view');
    }
}

class ComponentWithoutRulesArray extends Component
{
    public $model;

    public function mount(ModelForAttributeBinding $model)
    {
        $this->model = $model;
    }

    public function render()
    {
        return view('null-view');
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
        return view('foreach-models-arrow-title-view');
    }
}
