<?php

namespace Tests\Browser\SupportDateTimes;

use Livewire\Livewire;
use Tests\Browser\SupportDateTimes\Component;
use Tests\Unit\TestCase;

class Test extends TestCase
{
    /** @test */
    public function happy_path()
    {
        Livewire::test(Component::class)
            ->assertSee('native-01/01/2001')
            ->assertSee('carbon-01/01/2001')
            ->assertSee('illuminate-01/01/2001')
            ->call('addDay')
            ->assertSee('native-01/02/2001')
            ->assertSee('carbon-01/02/2001')
            ->assertSee('illuminate-01/02/2001');
    }
}
