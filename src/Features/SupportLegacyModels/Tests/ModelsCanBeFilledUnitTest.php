<?php

namespace Livewire\Features\SupportLegacyModels\Tests;

use Livewire\Livewire;
use Livewire\Component;
use Livewire\Attributes\Prop;
use Illuminate\Database\Eloquent\Model;

class ModelsCanBeFilledUnitTest extends \Tests\TestCase
{
    use Concerns\EnableLegacyModels;

    /** @test */
    public function can_fill_binded_model_properties()
    {
        $component = Livewire::test(ComponentWithFillableProperties::class, ['user' => new UserModel()]);

        $this->assertInstanceOf(UserModel::class, $component->get('user'));

        $component
            ->assertSet('user.name', null)
            ->call('callFill', [
                'user.name' => 'Caleb',
            ])
            ->assertSet('user.name', 'Caleb');
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

class ComponentWithFillableProperties extends Component
{
    #[Prop]
    public $user;

    public function callFill($values)
    {
        $this->fill($values);
    }

    public function render()
    {
        return '<div></div>';
    }
}
