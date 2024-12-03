<?php

namespace Livewire\Features\SupportLegacyModels\Tests;

use Tests\TestComponent;
use function Livewire\invade;
use Illuminate\Database\Eloquent\Model;
use Livewire\Livewire;
use Sushi\Sushi;

class ModelValidationUnitTest extends \Tests\TestCase
{
    use Concerns\EnableLegacyModels;

    public function test_can_validate_uniqueness_on_a_model()
    {
        Livewire::test(ComponentWithRulesPropertyAndModelWithUniquenessValidation::class)
            ->set('foo.name', 'bar')
            ->call('save')
            ->assertHasErrors('foo.name')
            ->set('foo.name', 'blubber')
            ->call('save')
            ->assertHasNoErrors('foo.name');
    }

    public function test_can_validate_uniqueness_on_a_model_but_exempt_the_model_itself()
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
}

class FooModelForUniquenessValidation extends Model
{
    use Sushi;

    protected $rows = [
        ['name' => 'foo', 'email' => 'foo@example.com'],
        ['name' => 'bar', 'email' => 'bar@example.com'],
    ];
}

class ComponentWithRulesPropertyAndModelWithUniquenessValidation extends TestComponent
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
}

class ComponentWithRulesPropertyAndModelUniquenessValidationWithIdExceptions extends TestComponent
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
}
