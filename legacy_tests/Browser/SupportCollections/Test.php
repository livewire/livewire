<?php

namespace LegacyTests\Browser\SupportCollections;

use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, Component::class)
                ->assertSeeIn('@things', 'foo')
                ->assertDontSeeIn('@things', 'bar')
                ->assertSeeIn('@unordered', 'foo')
                ->assertSeeIn('@unordered', 'bar')
                ->waitForLivewire()->click('@add-bar')
                ->assertSeeIn('@things', 'bar')
                ->assertSeeIn('@unordered', 'foo')
                ->assertSeeIn('@unordered', 'bar')
                ->assertSeeIn('@unordered', 'baz')
            ;
        });
    }
}
