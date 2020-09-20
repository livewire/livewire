<?php

namespace Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\PresenceVerifierInterface;
use Livewire\Component;
use Livewire\Livewire;
use Sushi\Sushi;

class EloquentModelValidationTest extends TestCase
{
    /** @test */
    public function standard_model_property()
    {
        Livewire::test(ComponentForEloquentModelHydrationMiddleware::class, [
            'foo' => $foo = Foo::first(),
        ])  ->set('foo.bar', '')
            ->call('save')
            ->assertHasErrors('foo.bar')
            ->set('foo.bar', 'baz')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals('baz', $foo->fresh()->bar);
    }

    /** @test */
    public function model_is_available_inside_rules_method()
    {
        Validator::setPresenceVerifier(new class implements PresenceVerifierInterface {
            public function getCount($collection, $column, $value, $excludeId = null, $idColumn = null, array $extra = [])
            {
                return Foo::where($column, '=', $value)
                    ->where('id', '!=', $excludeId)
                    ->count();
            }

            public function getMultiCount($collection, $column, array $values, array $extra = []) {}
        });

        Livewire::test(ComponentWithUniqueValidation::class, [
            'foo' => new Foo(),
        ])->set('foo.bar', 'rab')
            ->call('save')
            ->assertHasErrors('foo.bar', 'unique');

        Livewire::test(ComponentWithUniqueValidation::class, [
            'foo' => new Foo(),
        ])->set('foo.bar', 'qux')
            ->call('save')
            ->assertHasNoErrors();

        Livewire::test(ComponentWithUniqueValidation::class, [
            'foo' => Foo::first(),
        ])->set('foo.bar', 'rab')
            ->call('save')
            ->assertHasNoErrors();
    }

    /** @test */
    public function validate_message_doesnt_contain_dot_notation_if_property_is_model()
    {
        Livewire::test(ComponentForEloquentModelHydrationMiddleware::class, [
            'foo' => $foo = Foo::first(),
        ])  ->set('foo.bar', '')
            ->call('save')
            ->assertHasErrors('foo.bar', 'required')
            ->assertSee('The bar field is required.');
    }

    /** @test */
    public function validate_message_still_honors_original_custom_attributes_if_property_is_a_model()
    {
        app('translator')->addLines(['validation.required' => 'The :attribute field is required.'], 'en');
        app('translator')->addLines(['validation.attributes.foo.bar' => 'plop'], 'en');

        Livewire::test(ComponentForEloquentModelHydrationMiddleware::class, [
            'foo' => $foo = Foo::first(),
        ])  ->set('foo.bar', '')
            ->call('save')
            ->assertSee('The plop field is required.');
    }

    /** @test */
    public function validate_only_message_doesnt_contain_dot_notation_if_property_is_model()
    {
        Livewire::test(ComponentForEloquentModelHydrationMiddleware::class, [
            'foo' => $foo = Foo::first(),
        ])  ->set('foo.bar', '')
            ->call('performValidateOnly', 'foo.bar')
            ->assertHasErrors('foo.bar', 'required')
            ->assertSee('The bar field is required.');
    }

    /** @test */
    public function array_model_property()
    {
        Livewire::test(ComponentForEloquentModelHydrationMiddleware::class, [
            'foo' => $foo = Foo::first(),
        ])  ->set('foo.baz', ['bob'])
            ->call('save')
            ->assertHasErrors('foo.baz')
            ->set('foo.baz', ['bob', 'lob'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals(['bob', 'lob'], $foo->fresh()->baz);
    }

    /** @test */
    public function array_wildcard_key_model_property_validation()
    {
        Livewire::test(ComponentForEloquentModelHydrationMiddleware::class, [
            'foo' => $foo = Foo::first(),
        ])  ->set('foo.bob', ['b', 'bbo'])
            ->call('save')
            ->assertHasErrors('foo.bob.*')
            ->set('foo.bob', ['bb', 'bbo'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals(['bb', 'bbo'], $foo->fresh()->bob);
    }

    /** @test */
    public function array_wildcard_key_with_key_after_model_property_validation()
    {
        Livewire::test(ComponentForEloquentModelHydrationMiddleware::class, [
            'foo' => $foo = Foo::first(),
        ])  ->set('foo.lob.law', [['blog' => 'glob']])
            ->call('save')
            ->assertHasErrors('foo.lob.law.*.blog')
            ->set('foo.lob.law', [['blog' => 'globbbbb']])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals(['law' => [['blog' => 'globbbbb']]], $foo->fresh()->lob);
    }
}

class Foo extends Model
{
    use Sushi;

    protected $casts = ['baz' => 'array', 'bob' => 'array', 'lob' => 'array'];

    protected function getRows()
    {
        return [[
            'id' => 1,
            'bar' => 'rab',
            'baz' => json_encode(['zab', 'azb']),
            'bob' => json_encode(['obb']),
            'lob' => json_encode(['law' => []]),
        ]];
    }
}

class ComponentForEloquentModelHydrationMiddleware extends Component
{
    public $foo;

    protected $rules = [
        'foo.bar' => 'required',
        'foo.baz' => 'required|array|min:2',
        'foo.bob.*' => 'required|min:2',
        'foo.lob.law.*.blog' => 'required|min:5',
    ];

    public function save()
    {
        $this->validate();

        $this->foo->save();
    }

    public function performValidateOnly($field)
    {
       $this->validateOnly($field);
    }

    public function render()
    {
        return view('dump-errors');
    }
}

class ComponentWithUniqueValidation extends Component
{
    public $foo;

    public function rules()
    {
        return [
            'foo.bar' => 'required|unique:foo,bar,'.$this->foo->id
        ];
    }

    public function save()
    {
        $this->validate();

        $this->foo->save();
    }

    public function performValidateOnly($field)
    {
        $this->validateOnly($field);
    }

    public function render()
    {
        return view('dump-errors');
    }
}
