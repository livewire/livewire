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

    public function test_can_fill_with_property_aliases_from_array()
    {
        Livewire::test(ComponentWithAliasedFillableProperties::class)
            ->assertSetStrict('category', null)
            ->assertSetStrict('title', null)
            ->call('callFill', [
                'category_id' => 5,
                'post_title' => 'Hello World',
            ], [
                'category_id' => 'category',
                'post_title' => 'title',
            ])
            ->assertSetStrict('category', 5)
            ->assertSetStrict('title', 'Hello World');
    }

    public function test_can_fill_with_property_aliases_from_eloquent_model()
    {
        Livewire::test(ComponentWithAliasedFillableProperties::class)
            ->assertSetStrict('category', null)
            ->assertSetStrict('title', null)
            ->call('callFill', PostWithCategoryModel::first(), [
                'category_id' => 'category',
            ])
            ->assertSetStrict('category', 10)
            ->assertSetStrict('title', 'Aliased Title'); // unaliased key still works
    }

    public function test_can_fill_with_dot_notation_aliases()
    {
        Livewire::test(ComponentWithAliasedFillableProperties::class)
            ->assertSetStrict('meta', [])
            ->call('callFill', [
                'meta_foo' => 'bar',
            ], [
                'meta_foo' => 'meta.foo',
            ])
            ->assertSetStrict('meta.foo', 'bar');
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

class PostWithCategoryModel extends Model
{
    use \Sushi\Sushi;

    protected $rows = [
        [
            'category_id' => 10,
            'title' => 'Aliased Title',
        ],
    ];
}

class ComponentWithAliasedFillableProperties extends Component
{
    public ?int $category = null;
    public ?string $title = null;
    public array $meta = [];

    public function callFill($values, array $aliases = [])
    {
        $this->fill($values, $aliases);
    }

    public function render()
    {
        return '<div></div>';
    }
}
