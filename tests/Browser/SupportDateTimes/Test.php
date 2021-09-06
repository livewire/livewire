<?php

namespace Tests\Browser\SupportDateTimes;

use Livewire\Livewire;
use Tests\Unit\TestCase;

class Test extends TestCase
{
    public function test()
    {
        Livewire::test(Component::class)
            ->assertSee('native-01/01/2001')
            ->assertSee('nativeImmutable-01/01/2001')
            ->assertSee('carbon-01/01/2001')
            ->assertSee('carbonImmutable-01/01/2001')
            ->assertSee('illuminate-01/01/2001')
            ->call('addDay')
            ->assertSee('native-01/02/2001')
            ->assertSee('nativeImmutable-01/02/2001')
            ->assertSee('carbon-01/02/2001')
            ->assertSee('carbonImmutable-01/02/2001')
            ->assertSee('illuminate-01/02/2001');
    }
}
