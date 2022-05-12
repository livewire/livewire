<?php

namespace Tests\Unit;

use Livewire\Livewire;

class TypedPropertiesTest extends TestCase
{
    /** @test */
    public function can_set_uninitialized_typed_properties()
    {
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $this->markTestSkipped('Typed Property Initialization not supported prior to PHP 7.4');
        }

        require_once __DIR__.'/TypedPropertiesStubs.php';

        $testMessage = 'hello world';

        $component = new ComponentWithUninitializedTypedProperty();
        $component->syncInput('message', $testMessage);
        $this->assertEquals($testMessage, $component->message);
    }
}
