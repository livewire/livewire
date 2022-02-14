<?php

namespace Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Livewire\Livewire;
use Livewire\Component;
use function Livewire\invade;
use Livewire\Exceptions\MissingRulesException;
use Sushi\Sushi;

class ComponentHasRulesPropertyTest extends TestCase
{
    /** @test */
    public function validate_with_rules_property()
    {
        Livewire::test(ComponentWithRulesProperty::class)
            ->set('foo', '')
            ->call('save')
            ->assertHasErrors(['foo' => 'required']);
    }

    /** @test */
    public function validate_only_with_rules_property()
    {
        Livewire::test(ComponentWithRulesProperty::class)
            ->set('bar', '')
            ->assertHasErrors(['bar' => 'required']);
    }

    /** @test */
    public function validate_without_rules_property_and_no_args_throws_exception()
    {
        $this->expectException(MissingRulesException::class);

        Livewire::test(ComponentWithoutRulesProperty::class)->call('save');
    }

    /** @test */
    public function can_validate_uniqueness_on_a_model()
    {
        Livewire::test(ComponentWithRulesPropertyAndModelWithUniquenessValidation::class)
            ->set('foo.name', 'bar')
            ->call('save')
            ->assertHasErrors('foo.name')
            ->set('foo.name', 'blubber')
            ->call('save')
            ->assertHasNoErrors('foo.name');
    }

    /** @test */
    public function can_validate_uniqueness_on_a_model_but_exempt_the_model_itself()
    {
        Livewire::test(ComponentWithRulesPropertyAndModelUniquenessValidationWithIdExceptions::class)
            ->set('foo.email', 'baz@example.com')
            ->call('save')
            ->assertHasNoErrors('foo.email')
            ->set('foo.email', 'baz@example.com')
            ->call('save')
            ->assertHasNoErrors('foo.email')
            ->set('foo.email', 'bar@example.com')
            ->call('save')
            ->assertHasErrors('foo.email');
    }

    /** @test */
    public function can_validate_collection_properties()
    {
        Livewire::test(ComponentWithRulesProperty::class)
            ->set('foo', 'filled')
            ->call('save')
            ->assertHasErrors('baz.*.foo')
            ->set('baz.0.foo', 123)
            ->set('baz.1.foo', 456)
            ->call('save')
            ->assertHasNoErrors('baz.*.foo');
    }
}

class ComponentWithRulesProperty extends Component
{
    public $foo;
    public $bar = 'baz';
    public $baz;

    protected $rules = [
        'foo' => 'required',
        'bar' => 'required',
        'baz.*.foo' => 'numeric',
    ];

    public function mount()
    {
        $this->baz = collect([
            ['foo' => 'a'],
            ['foo' => 'b'],
        ]);
    }

    public function updatedBar()
    {
        $this->validateOnly('bar');
    }

    public function save()
    {
        $this->validate();
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}

class ComponentWithoutRulesProperty extends Component
{
    public $foo;

    public function save()
    {
        $this->validate();
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}

class FooModelForUniquenessValidation extends Model
{
    use Sushi;

    protected $rows = [
        ['name' => 'foo', 'email' => 'foo@example.com'],
        ['name' => 'bar', 'email' => 'bar@example.com'],
    ];
}

class ComponentWithRulesPropertyAndModelWithUniquenessValidation extends Component
{
    public $foo;

    protected $rules = [
        'foo.name' => 'required|unique:foo-connection.foo_model_for_uniqueness_validations,name',
    ];

    public function mount()
    {
        $this->foo = FooModelForUniquenessValidation::first();
    }

    public function save()
    {
        // Sorry about this chunk of ridiculousness. It's Sushi's fault.
        $connection = $this->foo::resolveConnection();
        $db = app('db');
        
        $connections = invade($db)->connections;
        $connections['foo-connection'] = $connection;
        invade($db)->connections = $connections;

        $this->validate();
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}

class ComponentWithRulesPropertyAndModelUniquenessValidationWithIdExceptions extends Component
{
    public $foo;

    protected function rules() {
        return [
            'foo.email' => 'unique:foo-connection.foo_model_for_uniqueness_validations,email,'.$this->foo->id
        ];
    }

    public function mount()
    {
        $this->foo = FooModelForUniquenessValidation::first();
    }

    public function save()
    {
        // Sorry about this chunk of ridiculousness. It's Sushi's fault.
        $connection = $this->foo::resolveConnection();
        $db = app('db');
        $connections = invade($db)->connections;
        $connections['foo-connection'] = $connection;
        invade($db)->connections = $connections;

        $this->validate();
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
