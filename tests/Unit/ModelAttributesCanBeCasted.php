<?php

namespace Tests\Unit;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\Livewire;

class ModelAttributesCanBeCasted extends TestCase
{
    private $component;

    public function setUp(): void
    {
        parent::setUp();

        Schema::create('model_for_attribute_castings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('normal_date');
            $table->dateTime('date_with_time');
            $table->unsignedTinyInteger('numerical_string');
            $table->timestamps();
        });

        $model = ModelForAttributeCasting::create([
            'id' => 1,
            'normal_date' => new \DateTime('2020-03-03'),
            'date_with_time' => new \DateTime('2015-10-21'),
            'numerical_string' => 'One'
        ]);

        $this->component = Livewire::test(
            ComponentForModelAttributeCasting::class,
            compact('model')
        );
    }

    /** @test */
    public function dates_can_be_casted_from_model_casts_definition()
    {
        $this->component
            // Assert is converted to Model's defined d-m-Y Format.
            ->assertPayloadSet('model.normal_date', '03-03-2020')
            // Get a 'Today in 1999' notice. How nostalgic...
            ->set('model.normal_date', '03-03-1999')
            // Check that the nostalgic time is properly represented as an Object.
            ->assertSet('model.normal_date', new \DateTime('1999-03-03'))
            // And also check that this nostalgic date is still converted to d-m-Y Format for the payload.
            ->assertPayloadSet('model.normal_date', '03-03-1999')
            // Now, let's focus to our original date, but in a wrong format.
            ->set('model.normal_date', '2020-03-03')
            // The system shouldn't explode with a different format. DateTime::__constructor() is pretty powerful.
            ->assertHasNoErrors('model.normal_date')
            // And, well, we must still have an object available.
            ->assertSet('model.normal_date', new \DateTime('2020-03-03'));
    }

    /** @test */
    public function date_times_can_be_casted_from_model_casts_definition()
    {
        $this->component
            // Assert is converted to JSON-Format (Aka, ISO Date).
            ->assertPayloadSet('model.date_with_time', '2015-10-21T00:00:00.000000Z')
            // Travel a little bit in time.
            ->set('model.date_with_time', '1985-10-26 01:20')
            // Check that our time travel is successfully represented as an Object.
            ->assertSet('model.date_with_time', new \DateTime('1985-10-26 01:20'))
            // And check that our time travel is properly converted into JSON-Format as well in the payload.
            ->assertPayloadSet('model.date_with_time', '1985-10-26T01:20:00.000000Z');
    }

    /** @test */
    public function custom_casters_kick_in_from_model_casts_definition()
    {
        $this->component
            // Assert that our Custom Caster is indeed working for the payload.
            ->assertPayloadSet('model.numerical_string', 'One')
            // Did anyone say "Livewire Counter Button"?
            ->set('model.numerical_string', 'Two')
            // Check that our Custom Caster kicks in when calling the Model directly.
            ->assertSet('model.numerical_string', 'Two')
            // And finally, check that our Custom Caster still kicks in for the payload.
            ->assertPayloadSet('model.numerical_string', 'Two');
    }
}

class ModelForAttributeCasting extends Model
{
    protected $connection = 'testbench';
    protected $guarded = [];

    protected $casts = [
        'normal_date' => 'date:d-m-Y',
        'date_with_time' => 'datetime',
        'numerical_string' => Number2String::class
    ];
}

class Number2String implements CastsAttributes {
    const CONVERSION_MAP = [
        1 => 'One',
        2 => 'Two'
    ];

    public function get($model, string $key, $value, array $attributes)
    {
        throw_unless(
            is_integer($value),
            new \InvalidArgumentException('Invalid underlying value: ' . $value)
        );

        throw_unless(
            isset(self::CONVERSION_MAP[$value]),
            new \InvalidArgumentException('Cannot convert number to string')
        );

        return self::CONVERSION_MAP[$value];
    }

    public function set($model, string $key, $value, array $attributes)
    {
        throw_unless(
            is_string($value),
            new \InvalidArgumentException('String must be either "One" or "Two"')
        );

        throw_if(
            ($index = array_search($value, self::CONVERSION_MAP)) === false,
            new \InvalidArgumentException('Cannot convert string to number')
        );

        return $index;
    }
}

class ComponentForModelAttributeCasting extends Component
{
    public $model;

    protected $rules = [
        'model.normal_date' => ['required', 'date'],
        'model.date_with_time' => ['required', 'datetime'],
        'model.numerical_string' => ['required', 'string']
    ];

    public function mount(ModelForAttributeCasting $model) {
        $this->model = $model;
    }

    public function render()
    {
        return view('null-view');
    }
}
