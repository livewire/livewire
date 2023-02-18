<?php

namespace Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

use function Livewire\trigger;

class BrowserTestCase extends TestCase
{
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
