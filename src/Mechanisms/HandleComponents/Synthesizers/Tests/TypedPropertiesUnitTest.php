<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers\Tests;

use Livewire\Component;
use Livewire\Livewire;

class TypedPropertiesUnitTest extends \Tests\TestCase
{
    /** @test */
    public function can_set_uninitialized_typed_properties()
    {
        $testMessage = 'hello world';

        Livewire::test(ComponentWithUninitializedTypedProperty::class)
            ->set('message', $testMessage)
            ->assertSet('message', $testMessage);
    }
}

class ComponentWithUninitializedTypedProperty extends Component
{
    public string $message;

    public function render()
    {
        return <<<'HTML'
        <div>
            {{ var_dump(isset($this->message)) }}
        </div>
        HTML;
    }
}
