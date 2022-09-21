<?php

namespace LegacyTests\Browser\AlpineV3\Emit;

use LegacyTests\Browser\Alpine\Emit\Test as V2Test;

class Test extends V2Test
{
    public function setUp(): void
    {
        static::$useAlpineV3 = true;

        parent::setUp();
    }
}
