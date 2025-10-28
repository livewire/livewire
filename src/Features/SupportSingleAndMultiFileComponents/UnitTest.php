<?php

namespace Livewire\Features\SupportSingleAndMultiFileComponents;

use Livewire\Livewire;

class UnitTest extends \Tests\TestCase
{
    public function test_can_use_single_file_component()
    {
        app('livewire.finder')->addLocation(path: __DIR__ . '/fixtures');

        Livewire::test('sfc-counter')
            ->assertSee('Count: 1')
            ->call('increment')
            ->assertSee('Count: 2');
    }

    public function test_can_use_multi_file_component()
    {
        app('livewire.finder')->addLocation(path: __DIR__ . '/fixtures');

        Livewire::test('mfc-counter')
            ->assertSee('Count: 1')
            ->call('increment')
            ->assertSee('Count: 2');
    }
}