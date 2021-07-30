<?php

namespace Tests\Browser\AlpineV3\Entangle;

use Tests\Browser\Alpine\Entangle\Test as V2Test;

class Test extends V2Test
{
    public function setUp(): void
    {
        static::$useAlpineV3 = true;

        parent::setUp();
    }
}
