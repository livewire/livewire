<?php

namespace Livewire\Features\SupportLegacyModels\Tests;

use Sushi\Sushi;
use Livewire\Livewire;
use Illuminate\Database\Eloquent\Model;
use Tests\TestComponent;

class TestableLivewireCanAssertModelUnitTest extends \Tests\TestCase
{
    public function test_can_assert_model_property_value()
    {
        Livewire::test(PropertyTestingComponent::class, [
                'model' => ModelForPropertyTesting::first(),
            ])
            ->assertSetStrict('model.foo.bar', 'baz');
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

class PropertyTestingComponent extends TestComponent
{
    public $model;
}
