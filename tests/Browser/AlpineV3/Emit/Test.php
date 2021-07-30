<?php

namespace Tests\Browser\AlpineV3\Emit;

use Tests\Browser\Alpine\Emit\Test as V2Test;

class Test extends V2Test
{
    public function setUp(): void
    {
        static::$useAlpineV3 = true;

        parent::setUp();
    }
}
