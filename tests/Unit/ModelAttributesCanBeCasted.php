<?php

namespace Tests\Unit;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\Livewire;

/**
 * Try to test against all Laravel built-in cast definitions.
 *
 * @see \Illuminate\Database\Eloquent\Concerns\HasAttributes::$primitiveCastTypes
 */
class ModelAttributesCanBeCasted extends TestCase
{
    private $component;

    public function setUp(): void
    {
        parent::setUp();

        Schema::create('model_for_attribute_castings', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->date('normal_date');
            $table->date('formatted_date');
            $table->dateTime('date_with_time');
            $table->timestamp('timestamped_date');

            $table->timestamps();
        });

        $model = ModelForAttributeCasting::create([
            'normal_date' => new \DateTime('2000-08-12'),
            'formatted_date' => new \DateTime('2020-03-03'),
            'date_with_time' => new \DateTime('2015-10-21'),
            'timestamped_date' => new \DateTime('2002-08-30'),

//            'numerical_string' => 'One'
        ]);

        $this->component = Livewire::test(ComponentForModelAttributeCasting::class);
    }

    /** @test */
    public function can_cast_normal_date_attributes_from_model_casts_definition()
    {
        $this->component
            // Assert that initial date value is an instance of DateTime for PHP code.
            ->assertSet('model.normal_date', new \DateTime('2000-08-12'))
            // Also, assert that the same value is represented as a date string for the payload.
            ->assertPayloadSet('model.normal_date', '2000-08-12T00:00:00.000000Z')

            // Let's change dates a little bit...
            ->set('model.normal_date', '2019-10-12')
            // There should be absolutely no issues when changing dates.
            ->call('validateAttribute', 'model.normal_date')
            ->assertHasNoErrors('model.normal_date')
            // This date change should be instantiated as a DateTime object for PHP...
            ->assertSet('model.normal_date', new \DateTime('2019-10-12'))
            // And still be converted to a traditional date string for the payload.
            ->assertPayloadSet('model.normal_date', '2019-10-12T00:00:00.000000Z');
    }

    /** @test */
    public function can_cast_formatted_date_attributes_from_model_casts_definition()
    {
        $this->component
            // Assert that initial value is a proper Object for PHP.
            ->assertSet('model.formatted_date', new \DateTime('2020-03-03'))
            // Assert is converted to Model's defined d-m-Y Format.
            ->assertPayloadSet('model.formatted_date', '03-03-2020')

            // Get a 'Today in 1999' notice. How nostalgic...
            ->set('model.formatted_date', '03-03-1999')
            // There should be no errors for this, only good memories...
            ->call('validateAttribute', 'model.formatted_date')
            ->assertHasNoErrors('model.formatted_date')
            // Check that the nostalgic time is properly represented as an Object.
            ->assertSet('model.formatted_date', new \DateTime('1999-03-03'))
            // And also check that this nostalgic date is still converted to d-m-Y Format for the payload.
            ->assertPayloadSet('model.formatted_date', '03-03-1999')

            // Now, let's focus on our original date, but in a wrong format.
            ->set('model.formatted_date', '2020-03-03')
            // The system shouldn't explode with a different format. DateTime::__constructor() is pretty powerful.
            ->call('validateAttribute', 'model.formatted_date')
            ->assertHasNoErrors('model.formatted_date')
            // And, well, we must still have an object available.
            ->assertSet('model.formatted_date', new \DateTime('2020-03-03'))
            // Oh... And a properly formatted payload.
            ->assertPayloadSet('model.formatted_date', '03-03-2020');
    }

    /** @test */
    public function can_cast_datetime_attributes_from_model_casts_definition()
    {
        $this->component
            // Assert it is a proper Object.
            ->assertSet('model.date_with_time', new \DateTime('2015-10-21 00:00:00'))
            // Assert is converted to JSON-Format (Aka, ISO Date).
            ->assertPayloadSet('model.date_with_time', '2015-10-21T00:00:00.000000Z')

            // Travel a little bit in time.
            ->set('model.date_with_time', '1985-10-26 01:20')
            // Check that our systems are working properly...
            ->call('validateAttribute', 'model.date_with_time')
            ->assertHasNoErrors('model.date_with_time')
            // Check that our time travel is successfully represented as an Object.
            ->assertSet('model.date_with_time', new \DateTime('1985-10-26 01:20'))
            // And check that our time travel is properly converted into JSON-Format as well in the payload.
            ->assertPayloadSet('model.date_with_time', '1985-10-26T01:20:00.000000Z');
    }

    /** @test */
    public function can_cast_timestamp_attributes_from_model_casts_definition()
    {
        $this->component
            // Check that our timestamp is an integer for PHP.
            ->assertSet('model.timestamped_date', 1030665600)
            // And this timestamp should be an integer for the Payload as well.
            ->assertPayloadSet('model.timestamped_date', 1030665600)

            // Technically speaking, timestamps work like integers...
            ->set('model.timestamped_date', 1538110800)
            // That a new timestamp in integer format should be a valid property value
            ->call('validateAttribute', 'model.timestamped_date')
            ->assertHasNoErrors()
            // Now, let's just verify that its properly assigned to the model...
            ->assertSet('model.timestamped_date', 1538110800)
            // And to the payload too...
            ->assertPayloadSet('model.timestamped_date', 1538110800);
    }

    /*
     * TODO:
     * - Numeric values: integer, real, float, double, decimal:<digits>
     * - String values
     * - Boolean values
     * - Array values: array, collection
     * - Object values (Literally, objects... Maybe "(object) array()"?)
     * - Better custom caster implementation
     */

    /** @test */
    public function can_use_custom_casters_from_model_casts_definition()
    {
        $this->markTestIncomplete('Maybe we could provide a better example for custom casters');

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
        'normal_date' => 'date',
        'formatted_date' => 'date:d-m-Y',
        'date_with_time' => 'datetime',
        'timestamped_date' => 'timestamp',

//         TODO: Better custom caster
//        'numerical_string' => Number2String::class
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
        'model.formatted_date' => ['required', 'date'],
        'model.date_with_time' => ['required', 'date'],
        'model.timestamped_date' => ['required', 'integer']

//        'model.numerical_string' => ['required', 'string']
    ];

    public function mount() {
        $this->model = ModelForAttributeCasting::first();
    }

    public function validateAttribute(string $attribute)
    {
        $this->validateOnly($attribute);
    }

    public function render()
    {
        return view('null-view');
    }
}
