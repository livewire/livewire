<?php

namespace Tests\Unit;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Livewire\Livewire;
use Sushi\Sushi;

/**
 * Try to test against all Laravel built-in cast definitions.
 *
 * @see \Illuminate\Database\Eloquent\Concerns\HasAttributes::$primitiveCastTypes
 * @see \Illuminate\Database\Eloquent\Concerns\HasAttributes::castAttribute()
 */
class ModelAttributesCanBeCasted extends TestCase
{
    private $component;

    public function setUp(): void
    {
        parent::setUp();

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
            ->assertHasNoErrors('model.timestamped_date')
            // Now, let's just verify that its properly assigned to the model...
            ->assertSet('model.timestamped_date', 1538110800)
            // And to the payload too...
            ->assertPayloadSet('model.timestamped_date', 1538110800);
    }

    /** @test */
    public function can_cast_integer_attributes_from_model_casts_definition()
    {
        $this->component
            // Our initial value is a simple integer...
            ->assertSet('model.integer_number', 1)
            // Which should have no issues when being converted in the Payload
            ->assertPayloadSet('model.integer_number', 1)

            // If we set an integer, well, Laravel will convert it to integer, as intended...
            ->set('model.integer_number', 1.9999999999999)
            // So, Livewire must not blow up...
            ->call('validateAttribute', 'model.integer_number')
            ->assertHasNoErrors('model.integer_number')
            // And, well, the new number must keep the integer part, and have removed the decimals...
            ->assertSet('model.integer_number', 1)
            // And for the payload, the same thing must've happened.
            ->assertPayloadSet('model.integer_number', 1)

            // Even if we provide a numeric string...
            ->set('model.integer_number', '1.9999999999')
            // Laravel is able to cast it to an integer number, and Livewire must not blow up...
            ->call('validateAttribute', 'model.integer_number')
            ->assertHasNoErrors('model.integer_number')
            // And the integer will be kept OK for the model...
            ->assertSet('model.integer_number', 1)
            // And it must be OK for the payload as well.
            ->assertPayloadSet('model.integer_number', 1);
    }

    /** @test */
    public function can_cast_real_attributes_from_model_casts_definition()
    {
        // Laravel treats Real casting as Float casting, so there should not be any issues...

        $this->component
            // Our initial value is a real number...
            ->assertSet('model.real_number', 2.0)
            // Which should have no issues when being converted in the Payload
            ->assertPayloadSet('model.real_number', 2.0)

            // If we provide a large decimal point, there should be no issues...
            ->set('model.real_number', 2.9999999999)
            // So, Livewire must not blow up...
            ->call('validateAttribute', 'model.real_number')
            ->assertHasNoErrors('model.real_number')
            // And, well, the new number must keep intact... No ceiling nor flooring...
            ->assertSet('model.real_number', 2.9999999999)
            // And for the payload, the same thing must've happened.
            ->assertPayloadSet('model.real_number', 2.9999999999)

            // Even if we provide a numeric string...
            ->set('model.real_number', '2.345')
            // Laravel is able to cast it to a real number, and Livewire must not blow up...
            ->call('validateAttribute', 'model.real_number')
            ->assertHasNoErrors('model.real_number')
            // And the number will be kept as is for the model...
            ->assertSet('model.real_number', 2.345)
            // And it must be kept for the payload as well.
            ->assertPayloadSet('model.real_number', 2.345);
    }

    /** @test */
    public function can_cast_float_attributes_from_model_casts_definition()
    {
        $this->component
            // Our initial value is a float number...
            ->assertSet('model.float_number', 3.0)
            // Which should have no issues when being converted in the Payload
            ->assertPayloadSet('model.float_number', 3.0)

            // If we provide a large decimal point, there should be no issues...
            ->set('model.float_number', 3.9999999998)
            // So, Livewire must not blow up...
            ->call('validateAttribute', 'model.float_number')
            ->assertHasNoErrors('model.float_number')
            // And, well, the new number must keep intact... No ceiling nor flooring...
            ->assertSet('model.float_number', 3.9999999998)
            // And for the payload, the same thing must've happened.
            ->assertPayloadSet('model.float_number', 3.9999999998)

            // Even if we provide a numeric string...
            ->set('model.float_number', '3.399')
            // Laravel is able to cast it to a float number, and Livewire must not blow up...
            ->call('validateAttribute', 'model.float_number')
            ->assertHasNoErrors('model.float_number')
            // And the number will be kept as is for the model...
            ->assertSet('model.float_number', 3.399)
            // And it must be kept for the payload as well.
            ->assertPayloadSet('model.float_number', 3.399);
    }

    /** @test */
    public function can_cast_double_precision_attributes_from_model_casts_definition()
    {
        // Laravel treats Double casting as Float casting, so there should not be any issues...

        $this->component
            // Our initial value is a double precision number...
            ->assertSet('model.double_precision_number', 4.0)
            // Which should have no issues when being converted in the Payload
            ->assertPayloadSet('model.double_precision_number', 4.0)

            // If we provide a large decimal point, there should be no issues...
            ->set('model.double_precision_number', 4.9999999997)
            // So, Livewire must not blow up...
            ->call('validateAttribute', 'model.double_precision_number')
            ->assertHasNoErrors('model.double_precision_number')
            // And, well, the new number must keep intact... No ceiling nor flooring...
            ->assertSet('model.double_precision_number', 4.9999999997)
            // And for the payload, the same thing must've happened.
            ->assertPayloadSet('model.double_precision_number', 4.9999999997)

            // Even if we provide a numeric string...
            ->set('model.double_precision_number', '4.20')
            // Laravel is able to cast it to a double precision number, and Livewire must not blow up...
            ->call('validateAttribute', 'model.double_precision_number')
            ->assertHasNoErrors('model.double_precision_number')
            // And the number will be kept as is for the model...
            ->assertSet('model.double_precision_number', 4.20)
            // And it must be kept for the payload as well.
            ->assertPayloadSet('model.double_precision_number', 4.20);
    }

    /** @test */
    public function can_cast_decimal_attributes_with_one_digit_from_model_casts_definition()
    {
        $this->component
            // Our initial value is a number with one digit...
            ->assertSet('model.decimal_with_one_digit', 5.0)
            // Which should have no issues when being converted in the Payload
            ->assertPayloadSet('model.decimal_with_one_digit', 5.0)

            // If we provide a large decimal point, Laravel will round the decimal part...
            ->set('model.decimal_with_one_digit', 5.120983)
            // So, Livewire must not blow up...
            ->call('validateAttribute', 'model.decimal_with_one_digit')
            ->assertHasNoErrors('model.decimal_with_one_digit')
            // And, well, the number was rounded, but remember, decimal <0.5 will be round down,
            // decimal >=0.5 will be round up
            ->assertSet('model.decimal_with_one_digit', 5.1)
            // And for the payload, the same thing must've happened.
            ->assertPayloadSet('model.decimal_with_one_digit', 5.1)

            // Even if we provide a numeric string...
            ->set('model.decimal_with_one_digit', '5.55')
            // Laravel is able to cast it to a number with one digit, and Livewire must not blow up...
            ->call('validateAttribute', 'model.decimal_with_one_digit')
            ->assertHasNoErrors('model.decimal_with_one_digit')
            // And the number will be rounded for the model...
            ->assertSet('model.decimal_with_one_digit', 5.6)
            // And it must be kept for the payload as well.
            ->assertPayloadSet('model.decimal_with_one_digit', 5.6);
    }

    /** @test */
    public function can_cast_decimal_attributes_with_two_digits_from_model_casts_definition()
    {
        $this->component
            // Our initial value is a number with two digits...
            ->assertSet('model.decimal_with_two_digits', 6.0)
            // Which should have no issues when being converted in the Payload
            ->assertPayloadSet('model.decimal_with_two_digits', 6.0)

            // If we provide a large decimal point, Laravel will round the decimal part...
            ->set('model.decimal_with_two_digits', 6.4567)
            // So, Livewire must not blow up...
            ->call('validateAttribute', 'model.decimal_with_two_digits')
            ->assertHasNoErrors('model.decimal_with_two_digits')
            // And, well, the number was rounded, but remember, decimal <0.5 will be round down,
            // decimal >=0.5 will be round up
            ->assertSet('model.decimal_with_two_digits', 6.46)
            // And for the payload, the same thing must've happened.
            ->assertPayloadSet('model.decimal_with_two_digits', 6.46)

            // Even if we provide a numeric string...
            ->set('model.decimal_with_two_digits', '6.212')
            // Laravel is able to cast it to a number with two digits, and Livewire must not blow up...
            ->call('validateAttribute', 'model.decimal_with_two_digits')
            ->assertHasNoErrors('model.decimal_with_two_digits')
            // And the number will be rounded for the model...
            ->assertSet('model.decimal_with_two_digits', 6.21)
            // And it must be kept for the payload as well.
            ->assertPayloadSet('model.decimal_with_two_digits', 6.21);
    }

    /** @test */
    public function can_cast_string_attributes_from_model_casts_definition()
    {
        $this->component
            // Check that our initial value is right...
            ->assertSet('model.string_name', 'Gladys')
            // Or maybe left in the Payload... Anyway, still the same value.
            ->assertPayloadSet('model.string_name', 'Gladys')

            // We should be able to change one string for another...
            ->set('model.string_name', 'Elena')
            // And, well, strings are string, no issues here.
            ->call('validateAttribute', 'model.string_name')
            ->assertHasNoErrors('model.string_name')
            // Value should be hold on both, the Model...
            ->assertSet('model.string_name', 'Elena')
            // And in the Payload without issues.
            ->assertPayloadSet('model.string_name', 'Elena')

            // Even if we want to force integers in a string attribute...
            ->set('model.string_name', 123)
            // Laravel must be able to handle with it, no issue here.
            ->call('validateAttribute', 'model.string_name')
            ->assertHasNoErrors('model.string_name')
            // And we should get a numeric string on the model,
            ->assertSet('model.string_name', '123')
            // And a numeric string in the Payload
            ->assertPayloadSet('model.string_name', '123');
    }

    /** @test */
    public function can_cast_boolean_attributes_from_model_casts_definition()
    {
        $this->component
            // Negativity flows withing this model...
            ->assertSet('model.boolean_value', false)
            // And it flows to the payload as well.
            ->assertPayloadSet('model.boolean_value', false)

            // Hey! Let's be more positive...
            ->set('model.boolean_value', true)
            // And, well, the good and the evil are two faces of the same coin.
            ->call('validateAttribute', 'model.boolean_value')
            ->assertHasNoErrors('model.boolean_value')
            // Our model must be more positive :)
            ->assertSet('model.boolean_value', true)
            // And the payload should also be positive.
            ->assertPayloadSet('model.boolean_value', true)

            // Even if we don't provide boolean values, PHP is powerful enough to make interpretations
            ->set('model.boolean_value', 0)
            ->call('validateAttribute', 'model.boolean_value')
            ->assertHasNoErrors('model.boolean_value')
            ->assertSet('model.boolean_value', false)
            ->assertPayloadSet('model.boolean_value', false)

            ->set('model.boolean_value', 1)
            ->call('validateAttribute', 'model.boolean_value')
            ->assertHasNoErrors('model.boolean_value')
            ->assertSet('model.boolean_value', true)
            ->assertPayloadSet('model.boolean_value', true)

            ->set('model.boolean_value', 'true')
            ->call('validateAttribute', 'model.boolean_value')
            ->assertHasNoErrors('model.boolean_value')
            ->assertSet('model.boolean_value', true)
            ->assertPayloadSet('model.boolean_value', true)

            ->set('model.boolean_value', '')
            ->call('validateAttribute', 'model.boolean_value')
            ->assertHasNoErrors('model.boolean_value')
            ->assertSet('model.boolean_value', false)
            ->assertPayloadSet('model.boolean_value', false);
    }

    /** @test */
    public function can_cast_array_attributes_from_model_casts_definition()
    {
        // Laravel treats Array casting as JSON casting, so there should not be any issues...

        $this->component
            // Let's start with a fresh list...
            ->assertSet('model.array_list', [])
            // Yeah, payload is still PHP-type here, it'll later be converted in the JSON response
            ->assertPayloadSet('model.array_list', [])

            // We should be able to add data,
            ->set('model.array_list', ['foo', 'bar'])
            // And we should have no issues.
            ->call('validateAttribute', 'model.array_list')
            ->assertHasNoErrors('model.array_list')
            // Our new data must be set :)
            ->assertSet('model.array_list', ['foo', 'bar'])
            ->assertPayloadSet('model.array_list', ['foo', 'bar']);
    }

    /** @test */
    public function can_cast_json_attributes_from_model_casts_definition()
    {
        $this->component
            // Let's start with a fresh list...
            ->assertSet('model.json_list', [1, 2, 3])
            // Yeah, payload is still PHP-type here, it'll later be converted in the JSON response
            ->assertPayloadSet('model.json_list', [1, 2, 3])

            // We should be able to set new data,
            ->set('model.json_list', [4, 5, 6])
            // And we should have no issues.
            ->call('validateAttribute', 'model.json_list')
            ->assertHasNoErrors('model.json_list')
            // Our new data must be set :)
            ->assertSet('model.json_list', [4, 5, 6])
            ->assertPayloadSet('model.json_list', [4, 5, 6]);
    }

    /** @test */
    public function can_cast_collection_attributes_from_model_casts_definition()
    {
        // Laravel treats Collection casting as JSON<->Collection casting, so there should not be any issues...

        $this->component
            // Let's start with a fresh collection...
            ->assertSet('model.collected_list', collect([true, false]))
            // Yeah, payload is still PHP-type here, but type of array.
            // It's prepared for the upcoming JSON response transformation.
            ->assertPayloadSet('model.collected_list', [true, false])

            // We should be able to set new data,
            ->set('model.collected_list', [false, true])
            // And we should have no issues.
            ->call('validateAttribute', 'model.collected_list')
            ->assertHasNoErrors('model.collected_list')
            // Our new data must be set :)
            ->assertSet('model.collected_list', collect([false, true]))
            ->assertPayloadSet('model.collected_list', [false, true]);
    }

    /** @test */
    public function can_cast_object_attributes_from_model_casts_definition()
    {
        // Laravel treats Collection casting as JSON<->Object casting, so there should not be any issues...

        $this->component
            // Let's make sure that our object is properly casted to PHP's stdClass.
            ->assertSet('model.object_value', (object) ['name' => 'Marian', 'email' => 'marian@likes.pizza'])
            // On our Payload, it should be the same thing, since its already ready to be JSON encoded.
            ->assertPayloadSet('model.object_value', (object) ['name' => 'Marian', 'email' => 'marian@likes.pizza'])

            // We should be able to assign a new object without problem...
            ->set('model.object_value', (object) ['name' => 'Marian', 'email' => 'marian@my-company.rocks'])
            // And our component must not blow up.
            ->call('validateAttribute', 'model.object_value')
            ->assertHasNoErrors('model.object_value')
            // Since its a traditional PHP object, we should be able to assert its properties.
            ->assertSet('model.object_value.name', 'Marian')
            ->assertPayloadSet('model.object_value.name', 'Marian')
            ->assertSet('model.object_value.email', 'marian@my-company.rocks')
            ->assertPayloadSet('model.object_value.email', 'marian@my-company.rocks');
    }

    /*
     * TODO:
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
    use Sushi;

    protected $guarded = [];

    protected $casts = [
        // Dates
        /* @see ModelAttributesCanBeCasted::can_cast_normal_date_attributes_from_model_casts_definition() */
        'normal_date' => 'date',
        /* @see ModelAttributesCanBeCasted::can_cast_formatted_date_attributes_from_model_casts_definition() */
        'formatted_date' => 'date:d-m-Y',

        // DateTime
        /* @see ModelAttributesCanBeCasted::can_cast_datetime_attributes_from_model_casts_definition() */
        'date_with_time' => 'datetime',

        // Timestamp
        /* @see ModelAttributesCanBeCasted::can_cast_timestamp_attributes_from_model_casts_definition() */
        'timestamped_date' => 'timestamp',

        // Integer
        /* @see ModelAttributesCanBeCasted::can_cast_integer_attributes_from_model_casts_definition() */
        'integer_number' => 'integer',

        // Floats
        /* @see ModelAttributesCanBeCasted::can_cast_real_attributes_from_model_casts_definition() */
        'real_number' => 'real',
        /* @see ModelAttributesCanBeCasted::can_cast_float_attributes_from_model_casts_definition() */
        'float_number' => 'float',
        /* @see ModelAttributesCanBeCasted::can_cast_double_precision_attributes_from_model_casts_definition() */
        'double_precision_number' => 'double',
        // Decimals
        /* @see ModelAttributesCanBeCasted::can_cast_decimal_attributes_with_one_digit_from_model_casts_definition() */
        'decimal_with_one_digit' => 'decimal:1',
        /* @see ModelAttributesCanBeCasted::can_cast_decimal_attributes_with_two_digits_from_model_casts_definition() */
        'decimal_with_two_digits' => 'decimal:2',

        // String
        /* @see ModelAttributesCanBeCasted::can_cast_string_attributes_from_model_casts_definition() */
        'string_name' => 'string',

        // Boolean
        /* @see ModelAttributesCanBeCasted::can_cast_boolean_attributes_from_model_casts_definition() */
        'boolean_value' => 'boolean',

        // JSON <-> Arrayable
        /* @see ModelAttributesCanBeCasted::can_cast_array_attributes_from_model_casts_definition() */
        'array_list' => 'array',
        /* @see ModelAttributesCanBeCasted::can_cast_json_attributes_from_model_casts_definition() */
        'json_list' => 'json',
        /* @see ModelAttributesCanBeCasted::can_cast_collection_attributes_from_model_casts_definition() */
        'collected_list' => 'collection',

        // JSON <-> Object
        /* @see ModelAttributesCanBeCasted::can_cast_object_attributes_from_model_casts_definition() */
        'object_value' => 'object'

//         TODO: Better custom caster
//        'numerical_string' => Number2String::class
    ];

    public function getRows()
    {
        return [
            [
                'normal_date' => new \DateTime('2000-08-12'),
                'formatted_date' => new \DateTime('2020-03-03'),
                'date_with_time' => new \DateTime('2015-10-21'),
                'timestamped_date' => new \DateTime('2002-08-30'),
                'integer_number' => 1,
                'real_number' => 2,
                'float_number' => 3,
                'double_precision_number' => 4,
                'decimal_with_one_digit' => 5,
                'decimal_with_two_digits' => 6,
                'string_name' => 'Gladys',
                'boolean_value' => false,
                'array_list' => json_encode([]),
                'json_list' => json_encode([1, 2, 3]),
                'collected_list' => json_encode([true, false]),
                'object_value' => json_encode(['name' => 'Marian', 'email' => 'marian@likes.pizza']),

//                TODO: Better Custom Caster
//                'numerical_string' => 'One'
            ]
        ];
    }
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
        'model.timestamped_date' => ['required', 'integer'],
        'model.integer_number' => ['required', 'integer'],
        'model.real_number' => ['required', 'numeric'],
        'model.float_number' => ['required', 'numeric'],
        'model.double_precision_number' => ['required', 'numeric'],
        'model.decimal_with_one_digit' => ['required', 'numeric'],
        'model.decimal_with_two_digits' => ['required', 'numeric'],
        'model.string_name' => ['required', 'string'],
        'model.boolean_value' => ['required', 'boolean'],

        'model.array_list' => ['required', 'array'],
        'model.array_list.*' => ['required', 'string'],

        'model.json_list' => ['required', 'array'],
        'model.json_list.*' => ['required', 'numeric'],

        'model.collected_list' => ['required'],
        'model.collected_list.*' => ['required', 'boolean'],

        'model.object_value' => ['required'],
        'model.object_value.name' => ['required'],
        'model.object_value.email' => ['required', 'email'],

//        TODO: Better Custom Caster
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
