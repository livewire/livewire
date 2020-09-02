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
}

class Foo extends Model
{
    use Sushi;

    protected $casts = ['baz' => 'array', 'bob' => 'array', 'lob' => 'array'];

    protected function getRows()
    {
        return [[
            'bar' => 'rab',
            'baz' => json_encode(['zab', 'azb']),
            'bob' => json_encode(['obb']),
            'lob' => json_encode(['law' => []]),
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
        'foo.lob.law.*.blog' => 'required|min:5',
    ];

    public function save()
    {
        $this->validate();

        $this->foo->save();
    }

    public function render()
    {
        return view('null-view');
    }
}
