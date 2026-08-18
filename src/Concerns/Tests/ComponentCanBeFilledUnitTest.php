<?php

namespace Livewire\Concerns\Tests;

use Livewire\Component;
use Livewire\Livewire;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
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

    public function test_can_fill_from_eloquent_model_with_enum_cast()
    {
        $component = Livewire::test(ComponentWithTypedEnumProperty::class);

        $component->assertSetStrict('title', '');
        $component->assertSetStrict('status', PostEnumStub::Draft);

        $component->call('callFill', PostWithCasting::first());

        $component->assertSetStrict('title', 'A Title');
        $component->assertSetStrict('status', PostEnumStub::Active);

        $component->assertSee('A Title');
        $component->assertSee('active');
    }

    public function test_can_fill_from_eloquent_model_with_datetime_cast()
    {
        $component = Livewire::test(ComponentWithTypedDateProperty::class);

        $component->assertSetStrict('title', '');
        $component->assertSetStrict('published_at', null);

        $component->call('callFill', PostWithCasting::first());

        $component->assertSetStrict('title', 'A Title');
        $component->assertSetStrict('published_at', function ($value) {
            return $value instanceof Carbon
                && $value->eq(Carbon::parse('2024-06-15 10:30:00'));
        });

        $component->assertSee('A Title');
        $component->assertSee('2024-06-15 10:30:00');
    }

    public function test_untyped_datetime_property_stays_string()
    {
        $component = Livewire::test(ComponentWithUntypedDateProperty::class);

        $component->assertSetStrict('title', '');
        $component->assertSetStrict('published_at', null);

        $component->call('callFill', PostWithoutCasting::first());

        $component->assertSetStrict('title', 'A Title');
        $component->assertSetStrict('published_at', '2024-06-15 10:30:00');

        $component->assertSee('A Title');
        $component->assertSee('2024-06-15 10:30:00');
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

enum PostEnumStub: string
{
    case Draft = 'draft';
    case Active = 'active';
}

class PostWithCasting extends Model
{
    use \Sushi\Sushi;

    protected $rows = [
        ['title' => 'A Title', 'status' => 'active', 'published_at' => '2024-06-15 10:30:00'],
    ];

    protected function casts(): array
    {
        return [
            'status' => PostEnumStub::class,
            'published_at' => 'datetime'
        ];
    }
}

class PostWithoutCasting extends Model
{
    use \Sushi\Sushi;

    protected $rows = [
        ['title' => 'A Title', 'published_at' => '2024-06-15 10:30:00'],
    ];
}

class ComponentWithTypedEnumProperty extends Component
{
    public string $title = '';
    public PostEnumStub $status = PostEnumStub::Draft;

    public function callFill($values)
    {
        $this->fill($values);
    }

    public function render()
    {
        return Blade::render(
            <<<'HTML'
                <div>
                    {{ $title }}
                    {{ $status }}
                </div>
            HTML,
            [
                'title' => $this->title,
                'status' => $this->status,
            ]
        );
    }
}

class ComponentWithTypedDateProperty extends Component
{
    public string $title = '';
    public ?Carbon $published_at = null;

    public function callFill($values)
    {
        $this->fill($values);
    }

    public function render()
    {
        return Blade::render(
            <<<'HTML'
                <div>
                    {{ $title }}
                    {{ $published_at }}
                </div>
            HTML,
            [
                'title' => $this->title,
                'published_at' => $this->published_at,
            ]
        );
    }
}

class ComponentWithUntypedDateProperty extends Component
{
    public string $title = '';
    public $published_at;

    public function callFill($values)
    {
        $this->fill($values);
    }

    public function render()
    {
        return Blade::render(
            <<<'HTML'
                <div>
                    {{ $title }}
                    {{ $published_at }}
                </div>
            HTML,
            [
                'title' => $this->title,
                'published_at' => $this->published_at,
            ]
        );
    }
}
