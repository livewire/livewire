<?php

namespace LegacyTests\Browser\Prefetch;

use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->markTestSkipped(); // @todo: Considering leaving this feature out of V3 at least initially. Not many use it...

        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, Component::class)
                ->assertSeeIn('@count', '1')
                ->mouseover('@button')
                ->pause(250) // We have to pause because prefetching doesn't call normal response hooks.
                ->assertSeeIn('@count', '1')
                ->click('@button')
                ->assertSeeIn('@count', '2');
        });
    }
}
