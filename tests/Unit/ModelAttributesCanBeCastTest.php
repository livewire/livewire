<?php

namespace Tests\Unit;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Livewire\Livewire;
use Sushi\Sushi;

class ModelAttributesCanBeCastTest extends TestCase
{
    /** @test */
    public function can_cast_normal_date_attributes_from_model_casts_definition()
    {
        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSet('model.normal_date', new \DateTime('2000-08-12'))
            ->assertPayloadSet('model.normal_date', '2000-08-12T00:00:00.000000Z')

            ->set('model.normal_date', '2019-10-12')
            ->call('validateAttribute', 'model.normal_date')
            ->assertHasNoErrors('model.normal_date')
            ->assertSet('model.normal_date', new \DateTime('2019-10-12'))
            ->assertPayloadSet('model.normal_date', '2019-10-12T00:00:00.000000Z');
    }

    /** @test */
    public function can_cast_formatted_date_attributes_from_model_casts_definition()
    {
        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSet('model.formatted_date', new \DateTime('2020-03-03'))
            ->assertPayloadSet('model.formatted_date', '03-03-2020')

            ->set('model.formatted_date', '03-03-1999')
            ->call('validateAttribute', 'model.formatted_date')
            ->assertHasNoErrors('model.formatted_date')
            ->assertSet('model.formatted_date', new \DateTime('1999-03-03'))
            ->assertPayloadSet('model.formatted_date', '03-03-1999')

            ->set('model.formatted_date', '2020-03-03')
            ->call('validateAttribute', 'model.formatted_date')
            ->assertHasNoErrors('model.formatted_date')
            ->assertSet('model.formatted_date', new \DateTime('2020-03-03'))
            ->assertPayloadSet('model.formatted_date', '03-03-2020');
    }

    /** @test */
    public function can_cast_datetime_attributes_from_model_casts_definition()
    {
        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSet('model.date_with_time', new \DateTime('2015-10-21 00:00:00'))
            ->assertPayloadSet('model.date_with_time', '2015-10-21T00:00:00.000000Z')

            ->set('model.date_with_time', '1985-10-26 01:20')
            ->call('validateAttribute', 'model.date_with_time')
            ->assertHasNoErrors('model.date_with_time')
            ->assertSet('model.date_with_time', new \DateTime('1985-10-26 01:20'))
            ->assertPayloadSet('model.date_with_time', '1985-10-26T01:20:00.000000Z');
    }

    /** @test */
    public function can_cast_timestamp_attributes_from_model_casts_definition()
    {
        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSet('model.timestamped_date', 1030665600)
            ->assertPayloadSet('model.timestamped_date', 1030665600)

            ->set('model.timestamped_date', 1538110800)
            ->call('validateAttribute', 'model.timestamped_date')
            ->assertHasNoErrors('model.timestamped_date')
            ->assertSet('model.timestamped_date', 1538110800)
            ->assertPayloadSet('model.timestamped_date', 1538110800);
    }

    /** @test */
    public function can_cast_integer_attributes_from_model_casts_definition()
    {
        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSet('model.integer_number', 1)
            ->assertPayloadSet('model.integer_number', 1)

            ->set('model.integer_number', 1.9999999999999)
            ->call('validateAttribute', 'model.integer_number')
            ->assertHasNoErrors('model.integer_number')
            ->assertSet('model.integer_number', 1)
            ->assertPayloadSet('model.integer_number', 1)

            ->set('model.integer_number', '1.9999999999')
            ->call('validateAttribute', 'model.integer_number')
            ->assertHasNoErrors('model.integer_number')
            ->assertSet('model.integer_number', 1)
            ->assertPayloadSet('model.integer_number', 1);
    }

    /** @test */
    public function can_cast_real_attributes_from_model_casts_definition()
    {

        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSet('model.real_number', 2.0)
            ->assertPayloadSet('model.real_number', 2.0)

            ->set('model.real_number', 2.9999999999)
            ->call('validateAttribute', 'model.real_number')
            ->assertHasNoErrors('model.real_number')
            ->assertSet('model.real_number', 2.9999999999)
            ->assertPayloadSet('model.real_number', 2.9999999999)

            ->set('model.real_number', '2.345')
            ->call('validateAttribute', 'model.real_number')
            ->assertHasNoErrors('model.real_number')
            ->assertSet('model.real_number', 2.345)
            ->assertPayloadSet('model.real_number', 2.345);
    }

    /** @test */
    public function can_cast_float_attributes_from_model_casts_definition()
    {
        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSet('model.float_number', 3.0)
            ->assertPayloadSet('model.float_number', 3.0)

            ->set('model.float_number', 3.9999999998)
            ->call('validateAttribute', 'model.float_number')
            ->assertHasNoErrors('model.float_number')
            ->assertSet('model.float_number', 3.9999999998)
            ->assertPayloadSet('model.float_number', 3.9999999998)

            ->set('model.float_number', '3.399')
            ->call('validateAttribute', 'model.float_number')
            ->assertHasNoErrors('model.float_number')
            ->assertSet('model.float_number', 3.399)
            ->assertPayloadSet('model.float_number', 3.399);
    }

    /** @test */
    public function can_cast_double_precision_attributes_from_model_casts_definition()
    {

        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSet('model.double_precision_number', 4.0)
            ->assertPayloadSet('model.double_precision_number', 4.0)

            ->set('model.double_precision_number', 4.9999999997)
            ->call('validateAttribute', 'model.double_precision_number')
            ->assertHasNoErrors('model.double_precision_number')
            ->assertSet('model.double_precision_number', 4.9999999997)
            ->assertPayloadSet('model.double_precision_number', 4.9999999997)

            ->set('model.double_precision_number', '4.20')
            ->call('validateAttribute', 'model.double_precision_number')
            ->assertHasNoErrors('model.double_precision_number')
            ->assertSet('model.double_precision_number', 4.20)
            ->assertPayloadSet('model.double_precision_number', 4.20);
    }

    /** @test */
    public function can_cast_decimal_attributes_with_one_digit_from_model_casts_definition()
    {
        $component = Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSet('model.decimal_with_one_digit', 5.0)
            ->assertPayloadSet('model.decimal_with_one_digit', 5.0)

            ->set('model.decimal_with_one_digit', 5.120983)
            ->call('validateAttribute', 'model.decimal_with_one_digit')
            ->assertHasNoErrors('model.decimal_with_one_digit')
            ->assertSet('model.decimal_with_one_digit', 5.1)
            ->assertPayloadSet('model.decimal_with_one_digit', 5.1)

            ->set('model.decimal_with_one_digit', '5.55')
            ->call('validateAttribute', 'model.decimal_with_one_digit')
            ->assertHasNoErrors('model.decimal_with_one_digit');

        if (version_compare(app()->version(), '9.46.0', '>=') && version_compare(app()->version(), '9.48.0', '<')) {
                // Laravel 9.46 changed how decimal property rounding is handled,
                // where rounding up will no longer be applied laravel/framework PR#45492
            $component
                ->assertSet('model.decimal_with_one_digit', 5.5)
                ->assertPayloadSet('model.decimal_with_one_digit', 5.5);
        } else {
            $component
                ->assertSet('model.decimal_with_one_digit', 5.6)
                ->assertPayloadSet('model.decimal_with_one_digit', 5.6);
        }
    }

    /** @test */
    public function can_cast_decimal_attributes_with_two_digits_from_model_casts_definition()
    {
        $component = Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSet('model.decimal_with_two_digits', 6.0)
            ->assertPayloadSet('model.decimal_with_two_digits', 6.0)

            ->set('model.decimal_with_two_digits', 6.4567)
            ->call('validateAttribute', 'model.decimal_with_two_digits')
            ->assertHasNoErrors('model.decimal_with_two_digits');

        if (version_compare(app()->version(), '9.46.0', '>=') && version_compare(app()->version(), '9.48.0', '<')) {
                // Laravel 9.46 changed how decimal property rounding is handled,
                // where rounding up will no longer be applied laravel/framework PR#45492
            $component
                ->assertSet('model.decimal_with_two_digits', 6.45)
                ->assertPayloadSet('model.decimal_with_two_digits', 6.45);
        } else {
            $component
                ->assertSet('model.decimal_with_two_digits', 6.46)
                ->assertPayloadSet('model.decimal_with_two_digits', 6.46);
        }

        $component
            ->set('model.decimal_with_two_digits', '6.212')
            ->call('validateAttribute', 'model.decimal_with_two_digits')
            ->assertHasNoErrors('model.decimal_with_two_digits')
            ->assertSet('model.decimal_with_two_digits', 6.21)
            ->assertPayloadSet('model.decimal_with_two_digits', 6.21);
    }

    /** @test */
    public function can_cast_string_attributes_from_model_casts_definition()
    {
        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSet('model.string_name', 'Gladys')
            ->assertPayloadSet('model.string_name', 'Gladys')

            ->set('model.string_name', 'Elena')
            ->call('validateAttribute', 'model.string_name')
            ->assertHasNoErrors('model.string_name')
            ->assertSet('model.string_name', 'Elena')
            ->assertPayloadSet('model.string_name', 'Elena')

            ->set('model.string_name', 123)
            ->call('validateAttribute', 'model.string_name')
            ->assertHasNoErrors('model.string_name')
            ->assertSet('model.string_name', '123')
            ->assertPayloadSet('model.string_name', '123');
    }

    /** @test */
    public function can_cast_boolean_attributes_from_model_casts_definition()
    {
        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSet('model.boolean_value', false)
            ->assertPayloadSet('model.boolean_value', false)

            ->set('model.boolean_value', true)
            ->call('validateAttribute', 'model.boolean_value')
            ->assertHasNoErrors('model.boolean_value')
            ->assertSet('model.boolean_value', true)
            ->assertPayloadSet('model.boolean_value', true)

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

        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSet('model.array_list', [])
            ->assertPayloadSet('model.array_list', [])

            ->set('model.array_list', ['foo', 'bar'])
            ->call('validateAttribute', 'model.array_list')
            ->assertHasNoErrors('model.array_list')
            ->assertSet('model.array_list', ['foo', 'bar'])
            ->assertPayloadSet('model.array_list', ['foo', 'bar']);
    }

    /** @test */
    public function can_cast_json_attributes_from_model_casts_definition()
    {
        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSet('model.json_list', [1, 2, 3])
            ->assertPayloadSet('model.json_list', [1, 2, 3])

            ->set('model.json_list', [4, 5, 6])
            ->call('validateAttribute', 'model.json_list')
            ->assertHasNoErrors('model.json_list')
            ->assertSet('model.json_list', [4, 5, 6])
            ->assertPayloadSet('model.json_list', [4, 5, 6]);
    }

    /** @test */
    public function can_cast_collection_attributes_from_model_casts_definition()
    {

        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSet('model.collected_list', collect([true, false]))
            ->assertPayloadSet('model.collected_list', [true, false])

            ->set('model.collected_list', [false, true])
            ->call('validateAttribute', 'model.collected_list')
            ->assertHasNoErrors('model.collected_list')
            ->assertSet('model.collected_list', collect([false, true]))
            ->assertPayloadSet('model.collected_list', [false, true]);
    }

    /** @test */
    public function can_cast_object_attributes_from_model_casts_definition()
    {

        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSet('model.object_value', (object) ['name' => 'Marian', 'email' => 'marian@likes.pizza'])
            ->assertPayloadSet('model.object_value', (object) ['name' => 'Marian', 'email' => 'marian@likes.pizza'])

            ->set('model.object_value', (object) ['name' => 'Marian', 'email' => 'marian@my-company.rocks'])
            ->call('validateAttribute', 'model.object_value')
            ->assertHasNoErrors('model.object_value')
            ->assertSet('model.object_value.name', 'Marian')
            ->assertPayloadSet('model.object_value.name', 'Marian')
            ->assertSet('model.object_value.email', 'marian@my-company.rocks')
            ->assertPayloadSet('model.object_value.email', 'marian@my-company.rocks');
    }

    /** @test */
    public function can_cast_attributes_with_custom_caster_from_model_casts_definition()
    {
        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSet('model.custom_caster', QuizAnswer::make('dumb answer'))
            ->assertPayloadSet('model.custom_caster', 'dumb answer')

            ->set('model.custom_caster', 'e=mc2')
            ->call('validateAttribute', 'model.custom_caster')
            ->assertHasNoErrors('model.custom_caster')
            ->assertSet('model.custom_caster', QuizAnswer::make('e=mc2'))
            ->assertPayloadSet('model.custom_caster', 'e=mc2');
    }
}

class ModelForAttributeCasting extends Model
{
    use Sushi;

    protected $guarded = [];

    protected $casts = [
        'normal_date' => 'date',
        'formatted_date' => 'date:d-m-Y',
        'date_with_time' => 'datetime',
        'timestamped_date' => 'timestamp',
        'integer_number' => 'integer',
        'real_number' => 'real',
        'float_number' => 'float',
        'double_precision_number' => 'double',
        'decimal_with_one_digit' => 'decimal:1',
        'decimal_with_two_digits' => 'decimal:2',
        'string_name' => 'string',
        'boolean_value' => 'boolean',
        'array_list' => 'array',
        'json_list' => 'json',
        'collected_list' => 'collection',
        'object_value' => 'object',
        'custom_caster' => QuizAnswerCaster::class,
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
                'custom_caster' => 'dumb answer'
            ]
        ];
    }
}

class QuizAnswer
{
    protected $answer;

    public static function make(string $answer): self
    {
        $new = new static();
        $new->answer = $answer;

        return $new;
    }

    public function getAnswer(): string
    {
        return $this->answer;
    }

    public function matches($givenAnswer): bool
    {
        return $this->answer === $givenAnswer;
    }

    public function __toString()
    {
        return $this->getAnswer();
    }
}

class QuizAnswerCaster implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        return QuizAnswer::make((string) $value);
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if ($value instanceof QuizAnswer) {
            $value = $value->getAnswer();
        }

        return $value;
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
        'model.custom_caster' => ['required']
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
