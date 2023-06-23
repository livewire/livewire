<?php

namespace Livewire\Features\SupportLegacyModels\Tests;

use Sushi\Sushi;
use Livewire\Livewire;
use Livewire\Component;
use Livewire\Attributes\Prop;
use Illuminate\Database\Eloquent\Model;

class TestableLivewireCanAssertModelUnitTest extends \Tests\TestCase
{
    /** @test */
    public function can_assert_model_property_value()
    {
        Livewire::test(PropertyTestingComponent::class, [
                'model' => ModelForPropertyTesting::first(),
            ])
            ->assertSet('model.foo.bar', 'baz');
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
    #[Prop]
    public $model;

    public function render()
    {
        return '<div></div>';
    }
}
