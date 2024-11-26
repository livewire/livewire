<?php

namespace Livewire\Concerns\Tests;

use Livewire\Component;
use Livewire\Livewire;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;

class ComponentCanBeFilledUnitTest extends \Tests\TestCase
{
    public function test_can_fill_from_an_array()
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

    public function test_can_fill_from_an_object()
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

    public function test_can_fill_from_an_eloquent_model()
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

    public function test_can_fill_using_dot_notation()
    {
        Livewire::test(ComponentWithFillableProperties::class)
            ->assertSetStrict('dotProperty', [])
            ->call('callFill', [
                'dotProperty.foo' => 'bar',
                'dotProperty.bob' => 'lob',
            ])
            ->assertSetStrict('dotProperty.foo', 'bar')
            ->assertSetStrict('dotProperty.bob', 'lob');
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

    public function callFill($values)
    {
        $this->fill($values);
    }

    public function render()
    {
        return Blade::render(
            <<<'HTML'
                <div>
                    {{ $publicProperty }}
                    {{ $protectedProperty }}
                    {{ $privateProperty }}
                </div>
            HTML,
            [
                'publicProperty' => $this->publicProperty,
                'protectedProperty' => $this->protectedProperty,
                'privateProperty' => $this->privateProperty,
            ]
        );
    }
}
