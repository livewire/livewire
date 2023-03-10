<?php

namespace Tests;

use function Livewire\trigger;

class BrowserTestCase extends TestCase
{
    public static function tweakApplicationHook() {
        return function () {};
    }
    
    public function setUp(): void
    {
        parent::setUp();

        trigger('browser.testCase.setUp', $this);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        trigger('browser.testCase.tearDown', $this);
    }
}
