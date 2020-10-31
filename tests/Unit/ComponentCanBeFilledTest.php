<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;
use Illuminate\Database\Eloquent\Model;

class ComponentCanBeFilledTest extends TestCase
{
    /** @test */
    public function can_fill_from_an_array()
    {
        $component = Livewire::test(ComponentWithFillableProperties::class);

        $component->assertSee('public');
        $component->assertSee('protected');
        $component->assertSee('private');

        $component->call('callFill', [
            'publicProperty' => 'Caleb',
            'protectedProperty' => 'Caleb',
            'privateProperty' => 'Caleb',
        ]);

        $component->assertSee('Caleb');
        $component->assertSee('protected');
        $component->assertSee('private');
    }

    /** @test */
    public function can_fill_from_an_object()
    {
        $component = Livewire::test(ComponentWithFillableProperties::class);

        $component->assertSee('public');
        $component->assertSee('protected');
        $component->assertSee('private');

        $component->call('callFill', new User());

        $component->assertSee('Caleb');
        $component->assertSee('protected');
        $component->assertSee('private');
    }

    /** @test */
    public function can_fill_from_an_eloquent_model()
    {
        $component = Livewire::test(ComponentWithFillableProperties::class);

        $component->assertSee('public');
        $component->assertSee('protected');
        $component->assertSee('private');

        $component->call('callFill', new UserModel());

        $component->assertSee('Caleb');
        $component->assertSee('protected');
        $component->assertSee('private');
    }

    /** @test */
    public function can_fill_using_dot_notation()
    {
        Livewire::test(ComponentWithFillableProperties::class)
            ->assertSet('dotProperty', [])
            ->call('callFill', [
                'dotProperty.foo' => 'bar',
                'dotProperty.bob' => 'lob',
            ])
            ->assertSet('dotProperty.foo', 'bar')
            ->assertSet('dotProperty.bob', 'lob');
    }

    /** @test */
    public function can_fill_binded_model_properties()
    {
        $component = Livewire::test(ComponentWithFillableProperties::class, ['user' => new UserModel()]);

        $this->assertInstanceOf(UserModel::class, $component->get('user'));

        $component
            ->assertSet('user.name', null)
            ->call('callFill', [
                'user.name' => 'Caleb'
            ])
            ->assertSet('user.name', 'Caleb');
    }
}

class User {
    public $publicProperty = 'Caleb';
    public $protectedProperty = 'Caleb';
    public $privateProperty = 'Caleb';
}

class UserModel extends Model {
    public $appends = [
        'publicProperty',
        'protectedProperty',
        'privateProperty'
    ];

    public function getPublicPropertyAttribute() {
        return 'Caleb';
    }

    public function getProtectedPropertyAttribute() {
        return 'protected';
    }

    public function getPrivatePropertyAttribute() {
        return 'private';
    }
}

class ComponentWithFillableProperties extends Component
{
    public $publicProperty = 'public';
    protected $protectedProperty = 'protected';
    private $privateProperty = 'private';

    public $dotProperty = [];

    public $user;

    public function callFill($values)
    {
        $this->fill($values);
    }

    public function render()
    {
        return view('fillable-view', [
            'publicProperty' => $this->publicProperty,
            'protectedProperty' => $this->protectedProperty,
            'privateProperty' => $this->privateProperty,
        ]);
    }
}
