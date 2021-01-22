<?php

namespace Tests\Unit;

use Illuminate\Database\Eloquent\Model;
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
    public function validate_message_doesnt_contain_dot_notation_if_property_is_model_with_snake_cased_attribute()
    {
        Livewire::test(ComponentForEloquentModelHydrationMiddleware::class, [
            'foo' => $foo = Foo::first(),
        ])  ->set('foo.bar_baz', '')
            ->call('save')
            ->assertHasErrors('foo.bar_baz', 'required')
            ->assertSee('The bar baz field is required.');
    }

    /** @test */
    public function validate_message_doesnt_contain_dot_notation_if_property_is_camelcased_model()
    {
        Livewire::test(ComponentWithCamelCasedModelProperty::class, [
            'camelFoo' => $foo = CamelFoo::first(),
        ])  ->set('camelFoo.bar', '')
            ->call('save')
            ->assertHasErrors('camelFoo.bar', 'required')
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
    public function array_index_key_model_property_validation()
    {
        Livewire::test(ComponentForEloquentModelHydrationMiddleware::class, [
            'foo' => $foo = Foo::first(),
        ])  ->set('foo.bob.0', 'b')
            ->call('save')
            ->assertHasErrors('foo.bob.*')
            ->set('foo.bob.0', 'bbo')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals(['bbo'], $foo->fresh()->bob);
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

    /** @test */
    public function array_with_numerical_index_key_model_property_validation()
    {
        Livewire::test(ComponentForEloquentModelHydrationMiddleware::class, [
            'foo' => $foo = Foo::first(),
        ])  ->set('foo.lob.law.0', ['blog' => 'glob'])
            ->call('save')
            ->assertHasErrors(['foo.lob.law.*', 'foo.lob.law.*.blog'])
            ->set('foo.lob.law.0', ['blog' => 'globbbbb'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals(['law' => [['blog' => 'globbbbb']]], $foo->fresh()->lob);
    }

    /** @test */
    public function array_wildcard_key_with_numeric_index_model_property_validation()
    {
        Livewire::test(ComponentForEloquentModelHydrationMiddleware::class, [
            'foo' => $foo = Foo::first(),
        ])  ->set('foo.lob.law.0.blog', 'glob')
            ->call('save')
            ->assertHasErrors('foo.lob.law.*.blog')
            ->set('foo.lob.law.0.blog', 'globbbbb')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals(['law' => [['blog' => 'globbbbb']]], $foo->fresh()->lob);
    }

    /** @test */
    public function array_wildcard_key_with_deep_numeric_index_model_property_validation()
    {
        Livewire::test(ComponentForEloquentModelHydrationMiddleware::class, [
            'foo' => $foo = Foo::first(),
        ])  ->set('foo.zap.0.0.name', 'ar')
            ->call('save')
            ->assertHasErrors('foo.zap.*.*.name')
            ->set('foo.zap.0.0.name', 'arise')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals([[['name' => 'arise']]], $foo->fresh()->zap);
    }
}

class Foo extends Model
{
    use Sushi;

    protected $casts = ['baz' => 'array', 'bob' => 'array', 'lob' => 'array', 'zap' => 'array'];

    protected function getRows()
    {
        return [[
            'bar' => 'rab',
            'bar_baz' => 'zab_rab',
            'baz' => json_encode(['zab', 'azb']),
            'bob' => json_encode(['obb']),
            'lob' => json_encode(['law' => []]),
            'zap' => json_encode([]),
        ]];
    }
}

class CamelFoo extends Model
{
    use Sushi;

    protected function getRows()
    {
        return [[
            'bar' => 'baz'
        ]];
    }
}

class ComponentWithCamelCasedModelProperty extends Component
{
    public $camelFoo;

    protected $rules = [
        'camelFoo.bar' => 'required'
    ];

    public function save()
    {
        $this->validate();
    }

    public function render()
    {
        return view('dump-errors');
    }
}

class ComponentForEloquentModelHydrationMiddleware extends Component
{
    public $foo;
    protected $rules = [
        'foo.bar' => 'required',
        'foo.bar_baz' => 'required',
        'foo.baz' => 'required|array|min:2',
        'foo.bob.*' => 'required|min:2',
        'foo.lob.law.*' => 'required|array',
        'foo.lob.law.*.blog' => 'required|min:5',
        'foo.zap.*.*.name' => 'required|min:3',
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
