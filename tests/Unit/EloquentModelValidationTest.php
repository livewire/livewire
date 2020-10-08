<?php

namespace Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Livewire;
use Livewire\ObjectPrybar;
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

    /** @test */
    public function unique_model_rule_vaildation()
    {
        // issue #1
        // the column is prefixed with the model name and would result in a unknown column name
        ComponentForEloquentModelValidation::setUniqueMethode('ignoreHardCodedId');

        $component = Livewire::test(ComponentForEloquentModelValidation::class, [
            'user' => $user = UniqueUser::firstWhere('username', 'caleb'),
        ]);

        $component->set('user.username', 'adrian')
                  ->assertHasErrors('user.username');

        // issue #2
        // model isn't hydrated - that's the real issue
        ComponentForEloquentModelValidation::setUniqueMethode('ignore');
        $component->set('user.username', 'adrian')
                  ->assertHasErrors('user.username');

        // same as issue #2 - just with another syntax
        ComponentForEloquentModelValidation::setUniqueMethode('ignoreModel');
        $component->set('user.username', 'adrian')
                  ->assertHasErrors('user.username');

        $component->set('user.username', 'calebporzio')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals('calebporzio', $user->fresh()->username);
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
            'baz' => json_encode(['zab', 'azb']),
            'bob' => json_encode(['obb']),
            'lob' => json_encode(['law' => []]),
            'zap' => json_encode([]),
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

class UniqueUser extends Model
{
    use Sushi;

    protected $connection = 'foo-connection';

    public function __construct(array $attributes = [])
    {
        /** @see ComponentHasRulesPropertyTest L141 */
        $connection = static::resolveConnection();
        $db = app('db');
        $prybar = new ObjectPrybar($db);
        $connections = $prybar->getProperty('connections');
        $connections['foo-connection'] = $connection;
        $prybar->setProperty('connections', $connections);

        parent::__construct($attributes);
    }

    protected function getRows()
    {
        return [
            ['id' => 1, 'username' => 'caleb'],
            ['id' => 2, 'username' => 'adrian'],
        ];
    }
}

class ComponentForEloquentModelValidation extends Component
{
    public $user;

    protected static $uniqueMethode = '';

    protected function rules()
    {
        return [
            'user.username' => [
                'required',
                $this->{static::$uniqueMethode}(),
            ],
        ];
    }

    public static function setUniqueMethode($methode)
    {
        static::$uniqueMethode = $methode;
    }

    public function ignoreHardCodedId()
    {
        return Rule::unique(UniqueUser::class)->ignore(1, 'id');
    }

    public function ignore()
    {
        // optional to avoid call on null - till its fixed
        return Rule::unique(UniqueUser::class)->ignore(optional($this->user)->id, 'id');
    }

    public function ignoreModel()
    {
        // optional to avoid call on null - till its fixed
        return Rule::unique(UniqueUser::class)->ignoreModel(optional($this->user)) ;
    }

    public function updated()
    {
        $this->validate();
    }

    public function save()
    {
        $this->validate();

        $this->user->save();
    }

    public function render()
    {
        return view('dump-errors');
    }
}
