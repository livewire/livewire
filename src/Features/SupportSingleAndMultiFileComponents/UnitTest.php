<?php

namespace Livewire\Features\SupportSingleAndMultiFileComponents;

use Livewire\Livewire;

class UnitTest extends \Tests\TestCase
{
    public function test_can_use_single_file_component()
    {
        app('livewire.finder')->addLocation(viewPath: __DIR__ . '/fixtures');

        Livewire::test('sfc-counter')
            ->assertSee('Count: 1')
            ->call('increment')
            ->assertSee('Count: 2');
    }

    public function test_can_use_multi_file_component()
    {
        app('livewire.finder')->addLocation(viewPath: __DIR__ . '/fixtures');

        Livewire::test('mfc-counter')
            ->assertSee('Count: 1')
            ->call('increment')
            ->assertSee('Count: 2');
    }

    public function test_sfc_component_includes_the_view_method_and_data_is_passed_to_the_view()
    {
        app('livewire.finder')->addLocation(viewPath: __DIR__ . '/fixtures');

        Livewire::test('sfc-component-with-render-and-data')
            ->assertSee('Message: Hello World');
    }
}
