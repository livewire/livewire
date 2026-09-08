<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers\Tests;

use Livewire\Livewire;

class StringableSynthUnitTest extends \Tests\TestCase
{
    public function test_malformed_stringable_update_returns_419()
    {
        config()->set('app.debug', false);

        Livewire::test(new class extends \Tests\TestComponent {
            public \Illuminate\Support\Stringable $value;

            public function mount()
            {
                $this->value = str('hello');
            }
        })
            ->set('value', [])
            ->assertStatus(419);
    }
}
