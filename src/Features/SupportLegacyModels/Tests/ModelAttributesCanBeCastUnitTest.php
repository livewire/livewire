<?php

namespace Livewire\Features\SupportLegacyModels\Tests;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Livewire;
use Sushi\Sushi;

class ModelAttributesCanBeCastUnitTest extends \Tests\TestCase
{
    use Concerns\EnableLegacyModels;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_can_cast_normal_date_attributes_from_model_casts_definition()
    {
        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSet('model.normal_date', new \DateTime('2000-08-12'))
            ->assertSnapshotSet('model.normal_date', '2000-08-12T00:00:00.000000Z')

            ->set('model.normal_date', '2019-10-12')
            ->call('validateAttribute', 'model.normal_date')
            ->assertHasNoErrors('model.normal_date')
            ->assertSet('model.normal_date', new \DateTime('2019-10-12'))
            ->assertSnapshotSet('model.normal_date', '2019-10-12T00:00:00.000000Z');
    }

    public function test_can_cast_formatted_date_attributes_from_model_casts_definition()
    {
        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSet('model.formatted_date', new \DateTime('2020-03-03'))
            ->assertSnapshotSet('model.formatted_date', '03-03-2020')

            ->set('model.formatted_date', '03-03-1999')
            ->call('validateAttribute', 'model.formatted_date')
            ->assertHasNoErrors('model.formatted_date')
            ->assertSet('model.formatted_date', new \DateTime('1999-03-03'))
            ->assertSnapshotSet('model.formatted_date', '03-03-1999')

            ->set('model.formatted_date', '2020-03-03')
            ->call('validateAttribute', 'model.formatted_date')
            ->assertHasNoErrors('model.formatted_date')
            ->assertSet('model.formatted_date', new \DateTime('2020-03-03'))
            ->assertSnapshotSet('model.formatted_date', '03-03-2020');
    }

    public function test_can_cast_datetime_attributes_from_model_casts_definition()
    {
        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSet('model.date_with_time', new \DateTime('2015-10-21 00:00:00'))
            ->assertSnapshotSet('model.date_with_time', '2015-10-21T00:00:00.000000Z')

            ->set('model.date_with_time', '1985-10-26 01:20')
            ->call('validateAttribute', 'model.date_with_time')
            ->assertHasNoErrors('model.date_with_time')
            ->assertSet('model.date_with_time', new \DateTime('1985-10-26 01:20'))
            ->assertSnapshotSet('model.date_with_time', '1985-10-26T01:20:00.000000Z');
    }

    public function test_can_cast_timestamp_attributes_from_model_casts_definition()
    {
        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSetStrict('model.timestamped_date', 1030665600)
            ->assertSnapshotSet('model.timestamped_date', 1030665600)

            ->set('model.timestamped_date', 1538110800)
            ->call('validateAttribute', 'model.timestamped_date')
            ->assertHasNoErrors('model.timestamped_date')
            ->assertSetStrict('model.timestamped_date', 1538110800)
            ->assertSnapshotSet('model.timestamped_date', 1538110800);
    }

    public function test_can_cast_integer_attributes_from_model_casts_definition()
    {
        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSetStrict('model.integer_number', 1)
            ->assertSnapshotSet('model.integer_number', 1)

            ->set('model.integer_number', 1.9999999999999)
            ->call('validateAttribute', 'model.integer_number')
            ->assertHasNoErrors('model.integer_number')
            ->assertSetStrict('model.integer_number', 1)
            ->assertSnapshotSet('model.integer_number', 1)

            ->set('model.integer_number', '1.9999999999')
            ->call('validateAttribute', 'model.integer_number')
            ->assertHasNoErrors('model.integer_number')
            ->assertSetStrict('model.integer_number', 1)
            ->assertSnapshotSet('model.integer_number', 1);
    }

    public function test_can_cast_real_attributes_from_model_casts_definition()
    {

        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSetStrict('model.real_number', 2.0)
            ->assertSnapshotSet('model.real_number', 2.0)

            ->set('model.real_number', 2.9999999999)
            ->call('validateAttribute', 'model.real_number')
            ->assertHasNoErrors('model.real_number')
            ->assertSetStrict('model.real_number', 2.9999999999)
            ->assertSnapshotSet('model.real_number', 2.9999999999)

            ->set('model.real_number', '2.345')
            ->call('validateAttribute', 'model.real_number')
            ->assertHasNoErrors('model.real_number')
            ->assertSetStrict('model.real_number', 2.345)
            ->assertSnapshotSet('model.real_number', 2.345);
    }

    public function test_can_cast_float_attributes_from_model_casts_definition()
    {
        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSetStrict('model.float_number', 3.0)
            ->assertSnapshotSet('model.float_number', 3.0)

            ->set('model.float_number', 3.9999999998)
            ->call('validateAttribute', 'model.float_number')
            ->assertHasNoErrors('model.float_number')
            ->assertSetStrict('model.float_number', 3.9999999998)
            ->assertSnapshotSet('model.float_number', 3.9999999998)

            ->set('model.float_number', '3.399')
            ->call('validateAttribute', 'model.float_number')
            ->assertHasNoErrors('model.float_number')
            ->assertSetStrict('model.float_number', 3.399)
            ->assertSnapshotSet('model.float_number', 3.399);
    }

    public function test_can_cast_double_precision_attributes_from_model_casts_definition()
    {

        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSetStrict('model.double_precision_number', 4.0)
            ->assertSnapshotSet('model.double_precision_number', 4.0)

            ->set('model.double_precision_number', 4.9999999997)
            ->call('validateAttribute', 'model.double_precision_number')
            ->assertHasNoErrors('model.double_precision_number')
            ->assertSetStrict('model.double_precision_number', 4.9999999997)
            ->assertSnapshotSet('model.double_precision_number', 4.9999999997)

            ->set('model.double_precision_number', '4.20')
            ->call('validateAttribute', 'model.double_precision_number')
            ->assertHasNoErrors('model.double_precision_number')
            ->assertSetStrict('model.double_precision_number', 4.20)
            ->assertSnapshotSet('model.double_precision_number', 4.20);
    }

    public function test_can_cast_decimal_attributes_with_one_digit_from_model_casts_definition()
    {
        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSetStrict('model.decimal_with_one_digit', 5.0)
            ->assertSnapshotSet('model.decimal_with_one_digit', 5.0)

            ->set('model.decimal_with_one_digit', 5.120983)
            ->call('validateAttribute', 'model.decimal_with_one_digit')
            ->assertHasNoErrors('model.decimal_with_one_digit')
            ->assertSetStrict('model.decimal_with_one_digit', 5.1)
            ->assertSnapshotSet('model.decimal_with_one_digit', 5.1)

            ->set('model.decimal_with_one_digit', '5.55')
            ->call('validateAttribute', 'model.decimal_with_one_digit')
            ->assertHasNoErrors('model.decimal_with_one_digit')
            ->assertSetStrict('model.decimal_with_one_digit', 5.6)
            ->assertSnapshotSet('model.decimal_with_one_digit', 5.6);
    }

    public function test_can_cast_decimal_attributes_with_two_digits_from_model_casts_definition()
    {
        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSetStrict('model.decimal_with_two_digits', 6.0)
            ->assertSnapshotSet('model.decimal_with_two_digits', 6.0)

            ->set('model.decimal_with_two_digits', 6.4567)
            ->call('validateAttribute', 'model.decimal_with_two_digits')
            ->assertHasNoErrors('model.decimal_with_two_digits')
            ->assertSetStrict('model.decimal_with_two_digits', 6.46)
            ->assertSnapshotSet('model.decimal_with_two_digits', 6.46)

            ->set('model.decimal_with_two_digits', '6.212')
            ->call('validateAttribute', 'model.decimal_with_two_digits')
            ->assertHasNoErrors('model.decimal_with_two_digits')
            ->assertSetStrict('model.decimal_with_two_digits', 6.21)
            ->assertSnapshotSet('model.decimal_with_two_digits', 6.21);
    }

    public function test_can_cast_string_attributes_from_model_casts_definition()
    {
        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSetStrict('model.string_name', 'Gladys')
            ->assertSnapshotSet('model.string_name', 'Gladys')

            ->set('model.string_name', 'Elena')
            ->call('validateAttribute', 'model.string_name')
            ->assertHasNoErrors('model.string_name')
            ->assertSetStrict('model.string_name', 'Elena')
            ->assertSnapshotSet('model.string_name', 'Elena')

            ->set('model.string_name', 123)
            ->call('validateAttribute', 'model.string_name')
            ->assertHasNoErrors('model.string_name')
            ->assertSetStrict('model.string_name', '123')
            ->assertSnapshotSet('model.string_name', '123');
    }

    public function test_can_cast_boolean_attributes_from_model_casts_definition()
    {
        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSetStrict('model.boolean_value', false)
            ->assertSnapshotSet('model.boolean_value', false)

            ->set('model.boolean_value', true)
            ->call('validateAttribute', 'model.boolean_value')
            ->assertHasNoErrors('model.boolean_value')
            ->assertSetStrict('model.boolean_value', true)
            ->assertSnapshotSet('model.boolean_value', true)

            ->set('model.boolean_value', 0)
            ->call('validateAttribute', 'model.boolean_value')
            ->assertHasNoErrors('model.boolean_value')
            ->assertSetStrict('model.boolean_value', false)
            ->assertSnapshotSet('model.boolean_value', false)

            ->set('model.boolean_value', 1)
            ->call('validateAttribute', 'model.boolean_value')
            ->assertHasNoErrors('model.boolean_value')
            ->assertSetStrict('model.boolean_value', true)
            ->assertSnapshotSet('model.boolean_value', true)

            ->set('model.boolean_value', 'true')
            ->call('validateAttribute', 'model.boolean_value')
            ->assertHasNoErrors('model.boolean_value')
            ->assertSetStrict('model.boolean_value', true)
            ->assertSnapshotSet('model.boolean_value', true)

            ->set('model.boolean_value', '')
            ->call('validateAttribute', 'model.boolean_value')
            ->assertHasNoErrors('model.boolean_value')
            ->assertSetStrict('model.boolean_value', false)
            ->assertSnapshotSet('model.boolean_value', false);
    }

    public function test_can_cast_array_attributes_from_model_casts_definition()
    {

        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSetStrict('model.array_list', [])
            ->assertSnapshotSet('model.array_list', [])

            ->set('model.array_list', ['foo', 'bar'])
            ->call('validateAttribute', 'model.array_list')
            ->assertHasNoErrors('model.array_list')
            ->assertSetStrict('model.array_list', ['foo', 'bar'])
            ->assertSnapshotSet('model.array_list', ['foo', 'bar']);
    }

    public function test_can_cast_json_attributes_from_model_casts_definition()
    {
        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSetStrict('model.json_list', [1, 2, 3])
            ->assertSnapshotSet('model.json_list', [1, 2, 3])

            ->set('model.json_list', [4, 5, 6])
            ->call('validateAttribute', 'model.json_list')
            ->assertHasNoErrors('model.json_list')
            ->assertSetStrict('model.json_list', [4, 5, 6])
            ->assertSnapshotSet('model.json_list', [4, 5, 6]);
    }

    public function test_can_cast_collection_attributes_from_model_casts_definition()
    {

        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSet('model.collected_list', collect([true, false]))
            ->assertSnapshotSet('model.collected_list', [true, false])

            ->set('model.collected_list', [false, true])
            ->call('validateAttribute', 'model.collected_list')
            ->assertHasNoErrors('model.collected_list')
            ->assertSet('model.collected_list', collect([false, true]))
            ->assertSnapshotSet('model.collected_list', [false, true]);
    }

    public function test_can_cast_object_attributes_from_model_casts_definition()
    {

        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSet('model.object_value', (object) ['name' => 'Marian', 'email' => 'marian@likes.pizza'])
            ->assertSnapshotSet('model.object_value', (array) ['name' => 'Marian', 'email' => 'marian@likes.pizza'])

            ->set('model.object_value', (object) ['name' => 'Marian', 'email' => 'marian@my-company.rocks'])
            ->call('validateAttribute', 'model.object_value')
            ->assertHasNoErrors('model.object_value')
            ->assertSetStrict('model.object_value.name', 'Marian')
            ->assertSnapshotSet('model.object_value.name', 'Marian')
            ->assertSetStrict('model.object_value.email', 'marian@my-company.rocks')
            ->assertSnapshotSet('model.object_value.email', 'marian@my-company.rocks');
    }

    public function test_can_cast_attributes_with_custom_caster_from_model_casts_definition()
    {
        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSet('model.custom_caster', QuizAnswer::make('dumb answer'))
            ->assertSnapshotSet('model.custom_caster', 'dumb answer')

            ->set('model.custom_caster', 'e=mc2')
            ->call('validateAttribute', 'model.custom_caster')
            ->assertHasNoErrors('model.custom_caster')
            ->assertSet('model.custom_caster', QuizAnswer::make('e=mc2'))
            ->assertSnapshotSet('model.custom_caster', 'e=mc2');
    }

    public function test_can_cast_enum_attributes_from_model_casts_definition()
    {
        Livewire::test(ComponentForModelAttributeCasting::class)
            ->assertSetStrict('model.enum', null)
            ->assertSnapshotSet('model.enum', null)

            ->set('model.enum', TestingEnum::FOO->value)
            ->call('validateAttribute', 'model.enum')
            ->assertHasNoErrors('model.enum')
            ->assertSetStrict('model.enum', TestingEnum::FOO)
            ->assertSnapshotSet('model.enum', TestingEnum::FOO->value, true)

            ->set('model.enum', '')
            ->call('validateAttribute', 'model.enum')
            ->assertHasNoErrors('model.enum')
            ->assertSetStrict('model.enum', null)
            ->assertSnapshotSet('model.enum', null, true);
    }
}

class ModelForAttributeCasting extends \Illuminate\Database\Eloquent\Model
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
        'enum' => TestingEnum::class,
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
                'custom_caster' => 'dumb answer',
                'enum' => null,
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

enum TestingEnum: string
{
    case FOO = 'bar';
}

class ComponentForModelAttributeCasting extends Component
{
    public $model;

    public function rules(): array
    {
        return [
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
            'model.custom_caster' => ['required'],
            'model.enum' => ['nullable', Rule::enum(TestingEnum::class)]
        ];
    }

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
