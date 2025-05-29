<?php

namespace Livewire\Features\SupportLegacyModels\Tests;

use Livewire\Livewire;
use Illuminate\Database\Eloquent\Model;
use Tests\TestComponent;

class ModelsCanBeFilledUnitTest extends \Tests\TestCase
{
    use Concerns\EnableLegacyModels;

    public function test_can_fill_binded_model_properties()
    {
        $component = Livewire::test(ComponentWithFillableProperties::class, ['user' => new UserModel()]);

        $this->assertInstanceOf(UserModel::class, $component->get('user'));

        $component
            ->assertSetStrict('user.name', null)
            ->call('callFill', [
                'user.name' => 'Caleb',
            ])
            ->assertSetStrict('user.name', 'Caleb');
    }
}

class UserModel extends Model
{
    public $appends = [
        'publicProperty',
        'protectedProperty',
        'privateProperty',
    ];

    public function getPublicPropertyAttribute()
    {
        return 'Caleb';
    }

    public function getProtectedPropertyAttribute()
    {
        return 'protected';
    }

    public function getPrivatePropertyAttribute()
    {
        return 'private';
    }
}

class ComponentWithFillableProperties extends TestComponent
{
    public $user;

    public function callFill($values)
    {
        $this->fill($values);
    }
}
