<?php

namespace Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Livewire\Livewire;

function testArray()
{
    return [
        'name' => 'Caleb',
        'aliases' => [
            0 => ['name' => 'Treehugger'],
            1 => ['name' => 'something else'],
        ],
        'bar' => [
            'baz' => 'bob'
        ],
    ];
}

class EloquentModelHydrationMiddlewareTest extends TestCase
{
    /** @test */
    public function rename_me()
    {
        $component = Livewire::test(ComponentForEloquentModelHydrationMiddleware::class);
        $this->assertEqualsCanonicalizing(testArray(), $component->get('foo'));
        $component->set('foo.name', 'Adrian');
        $component->set('foo.aliases.1.name', 'Tester');
        $component->set('foo.aliases.2.name', 'Role model');
        $this->assertEqualsCanonicalizing(array_merge(testArray(), ['name' => 'Adrian', 'aliases' => [['name' => 'Treehugger'], ['name' => 'Tester'], ['name' => 'Role model']]]), $component->get('foo'));
        $component->call('runValidation')->assertHasNoErrors();

        $component->set('foo.aliases', [['name' => 'foo'], ['name' => 'bar'], ['name' => 'baz']]);
        $this->assertEqualsCanonicalizing(array_merge(testArray(), ['name' => 'Adrian', 'aliases' => [['name' => 'foo'], ['name' => 'bar'], ['name' => 'baz']]]), $component->get('foo'));
        $component->call('runValidation')->assertHasNoErrors();

        $component->set('foo.name', '')->call('runValidation')->assertHasErrors(['foo.name' => 'required']);
        $component->set('foo.name', 'Adrian')->call('runValidation')->assertHasNoErrors();

        $component->set('foo.aliases', [['bad' => 'name']])->call('runValidation')->assertHasErrors(['foo.aliases.0.name' => 'required']);
    }
}

class FooModel extends Model
{
    protected $guarded = [];
}

class ComponentForEloquentModelHydrationMiddleware extends Component
{
    public $foo;

    protected $rules = [
        'foo.name' => 'required',
        'foo.aliases.*.name' => 'required',
        'foo.bar.baz' => 'required',
    ];

    public function mount()
    {
        $this->foo = new FooModel(testArray());
    }

    public function runValidation()
    {
        $this->validate();
    }

    public function render()
    {
        return view('null-view');
    }
}
