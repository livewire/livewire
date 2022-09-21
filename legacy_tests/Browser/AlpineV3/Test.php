<?php

namespace LegacyTests\Browser\AlpineV3;

use LegacyTests\Browser\Alpine\Test as V2Test;

class Test extends V2Test
{
    public function setUp(): void
    {
        static::$useAlpineV3 = true;

        parent::setUp();
    }
}
