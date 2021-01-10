<?php

namespace Tests\Unit;

use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Support\Facades\View;
use Illuminate\Database\Eloquent\Model;
use Livewire\Exceptions\PublicPropertyTypeNotAllowedException;

class PublicPropertiesAreCastToJavaScriptUsableTypesTest extends TestCase
{
    /** @test */
    public function exception_is_thrown_and_not_caught_by_view_error_handler()
    {
        $this->expectException(PublicPropertyTypeNotAllowedException::class);
        Livewire::component('foo', ComponentWithPropertiesStub::class);

        View::make('render-component', ['component' => 'foo', 'params' => ['foo' => new \StdClass]])->render();
    }

    /** @test */
    public function unordered_numeric_keyed_arrays_are_reordered_so_javascript_doesnt_do_it_for_us()
    {
        $orderedNumericArray = [
            1 => 'foo',
            0 => 'bar',
        ];

        $foo = Livewire::test(ComponentWithPropertiesStub::class, ['foo' => $orderedNumericArray])->foo;

        $this->assertSame([
            0 => 'bar',
            1 => 'foo',
        ], $foo);
    }

    /** @test */
    public function unordered_string_keyed_arrays_are_reordered_so_javascript_doesnt_do_it_for_us()
    {
        $orderedNumericArray = [
            '20' => 'baz',
            '19' => 'bar',
            '0' => 'foo',
        ];

        $foo = Livewire::test(ComponentWithPropertiesStub::class, ['foo' => $orderedNumericArray])->foo;

        $this->assertSame([
            '0' => 'foo',
            '19' => 'bar',
            '20' => 'baz',
        ], $foo);
    }

    /** @test */
    public function numeric_keys_are_ordered_before_string_keys_so_javascript_doesnt_do_it_for_us()
    {
        $orderedNumericArray = [
            1 => 'foo',
            'bob' => 'lob',
            0 => 'bar',
            'abob' => 'lob',
        ];

        $foo = Livewire::test(ComponentWithPropertiesStub::class, ['foo' => $orderedNumericArray])
            ->foo;

        $this->assertSame([
            0 => 'bar',
            1 => 'foo',
            'bob' => 'lob',
            'abob' => 'lob',
        ], $foo);
    }

    /** @test */
    public function ordered_numeric_arrays_are_reindexed_deeply()
    {
        $orderedNumericArray = [
            [
                1 => 'foo',
                0 => 'bar',
            ],
        ];

        Livewire::test(ComponentWithPropertiesStub::class, ['foo' => $orderedNumericArray])
            ->assertSet('foo', [[0 => 'bar', 1 => 'foo']]);
    }
}

class ComponentWithPropertiesStub extends Component
{
    public $foo;

    public function mount($foo)
    {
        $this->foo = $foo;
    }

    public function render()
    {
        return app('view')->make('var-dump-foo');
    }
}

class ModelStub extends Model
{
    protected $guarded = [];
}
