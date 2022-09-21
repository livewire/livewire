<?php

namespace LegacyTests\Browser\AlpineV3\Transition;

use LegacyTests\Browser\Alpine\Transition\Test as V2Test;

class Test extends V2Test
{
    public function setUp(): void
    {
        static::$useAlpineV3 = true;

        parent::setUp();
    }
}
