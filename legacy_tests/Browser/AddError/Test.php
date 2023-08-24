<?php

namespace Tests\Tests;

use LegacyTests\Browser\TestCase;
use Livewire\Livewire;

class Test extends TestCase
{
    /** @test */
    public function working()
    {
        Livewire::test(Component::class)
            ->call('addErrors')
            ->assertSee('first error')
            ->assertSee('second error')
            ->assertSee('third error')
            ->assertHasErrors(['first', 'second', 'third'])
            ->call('addFilterErrors')
            ->assertSee('first error')
            ->assertSee('second error')
            ->assertHasErrors(['first', 'second']);
    }

    /** @test */
    public function not_working()
    {
        Livewire::test(Component::class)
            ->assertSee('first error')
            ->assertSee('second error')
            ->assertSee('third error')
            ->assertHasErrors(['first', 'second', 'third'])
            ->call('addFilterErrors')
            ->assertSee('first error')
            ->assertSee('second error')
            ->assertHasErrors(['first', 'second']);
    }
}
