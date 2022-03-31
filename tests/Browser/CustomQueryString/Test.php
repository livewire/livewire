<?php

namespace Tests\Browser\CustomQueryString;

use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    /** @test */
    public function can_change_query_string() {
        $this->browse(function ($browser) {
            $queryString = '?' . http_build_query([
                'a' => '1,2,3,4',
                'b' => 'test'
            ]);

            Livewire::visit($browser, Component::class, $queryString)
                /**
                 * Basic action (click).
                 */
                ->waitForLivewire()->click('@setA')
                ->assertSeeIn('@output', '1,2,3,4')
                ->assertDontSeeIn('@output', ',1,2,3,4')
                ->assertDontSeeIn('@output', '1,2,3,4,')
                ->assertDontSeeIn('@output', ',1,2,3,4,')
                ->assertDontSeeIn('@output', '')

                /**
                 * Basic action (click).
                 */
                ->waitForLivewire()->click('@setB')
                ->assertSeeIn('@output', 'test')
                ->assertDontSeeIn('@output', '');
        });
    }
}
