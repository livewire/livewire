<?php

namespace Livewire\Features\SupportUnitTesting\Tests;

use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Livewire\Livewire;
use Sushi\Sushi;

class TestableLivewireCanAssertPropertiesTest extends \Tests\TestCase
{
    /** @test */
    public function can_assert_basic_property_value()
    {
        Livewire::test(PropertyTestingComponent::class)
            ->assertSet('foo', 'bar')
            ->set('foo', 'baz')
            ->assertSet('foo', 'baz');
    }

    /** @test */
    public function can_assert_model_property_value()
    {
        Livewire::test(PropertyTestingComponent::class, [
                'model' => ModelForPropertyTesting::first(),
            ])
            ->assertSet('model.foo.bar', 'baz');
    }

    /** @test */
    public function can_assert_computed_property_value()
    {
        Livewire::test(PropertyTestingComponent::class)
            ->assertSet('bob', 'lob');
    }

    /** @test */
    public function swallows_property_not_found_exceptions()
    {
        Livewire::test(PropertyTestingComponent::class)
            ->assertSet('nonExistentProperty', null);
    }

    /** @test */
    public function throws_non_property_not_found_exceptions()
    {
        $this->markTestSkipped('In V2 computed properties are "LAZY", what should we do in V3?');

        $this->expectException(\Exception::class);

        Livewire::test(ComputedPropertyWithExceptionTestingComponent::class)
            ->assertSet('throwsException', null);
    }
}

class ModelForPropertyTesting extends Model
{
    use Sushi;

    protected $casts = ['foo' => 'array'];

    protected function getRows()
    {
        return [
            ['foo' => json_encode(['bar' => 'baz'])],
        ];
    }
}

class PropertyTestingComponent extends Component
{
    public $foo = 'bar';
    public $model;

    public function getBobProperty()
    {
        return 'lob';
    }

    public function render()
    {
        return view('null-view');
    }
}

class ComputedPropertyWithExceptionTestingComponent extends Component
{
    public function getThrowsExceptionProperty()
    {
        throw new \Exception('Test exception');
    }

    public function render()
    {
        return view('null-view');
    }
}
