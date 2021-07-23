<?php

namespace Tests\Browser\AlpineV3;

use Tests\Browser\Alpine\Test as V2Test;

class Test extends V2Test
{
    public function setUp(): void
    {
        static::$useAlpineV3 = true;

        parent::setUp();
    }
}
