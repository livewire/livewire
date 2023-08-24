<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers\Tests;

use Livewire\Component;
use Livewire\Livewire;

class IntSynthUnitTest extends \Tests\TestCase
{
    /**
     * @test
     */
    public function null_value_hydrated_returns_null()
    {
        Livewire::test(ComponentWithNullableInt::class)
            ->set('intField', null)
            ->assertSet('intField', null, true); // Use strict mode
    }
}

class ComponentWithNullableInt extends Component
{
    public ?int $intField = null;


    public function render()
    {
        return view('null-view');
    }
}
