<?php

namespace Livewire\Features\SupportSingleAndMultiFileComponents;

use Livewire\Livewire;

class UnitTest extends \Tests\TestCase
{
    public function test_can_use_single_file_component()
    {
        $this->markTestSkipped();

        // ComponentFactory
        // ComponentFinder

        // addNamespace
        // getFinder
        // flushFinderCache

        app('livewire.finder')->addLocation(__DIR__ . '/fixtures');
        app('livewire.finder')->addNamespace('test', __DIR__ . '/fixtures');

        Livewire::test('sfc-counter')
            ->assertSee('Count: 1')
            ->call('increment')
            ->assertSee('Count: 2');
    }
}