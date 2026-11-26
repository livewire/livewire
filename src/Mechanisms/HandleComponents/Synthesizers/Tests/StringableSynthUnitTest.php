<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers\Tests;

use Illuminate\Support\Stringable;
use Livewire\Livewire;
use Tests\TestComponent;

class StringableSynthUnitTest extends \Tests\TestCase
{
    public function test_setting_a_stringable_property_to_an_array_aborts_with_419()
    {
        config()->set('app.debug', false);

        Livewire::test(new class extends TestComponent {
            public Stringable $title;

            public function mount()
            {
                $this->title = str('Hello');
            }
        })
            ->set('title', [])
            ->assertStatus(419);
    }
}
